<?php
/*********************************************************************
 *
 * $Id: FileServer.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * VirtualHub4web file server and files handling code
 *
 * - - - - - - - - - License information: - - - - - - - - -
 *
 *  Copyright (C) 2022 and beyond by Yoctopuce Sarl, Switzerland.
 *
 *  Yoctopuce Sarl (hereafter Licensor) grants to you a perpetual
 *  non-exclusive license to use, modify, copy and integrate http
 *  file into your software for the sole purpose of interfacing
 *  with Yoctopuce products.
 *
 *  You may reproduce and distribute copies of this file in
 *  source or object form, as long as the sole purpose of this
 *  code is to interface with Yoctopuce products. You must retain
 *  this notice in the distributed source file.
 *
 *  You should refer to Yoctopuce General Terms and Conditions
 *  for additional information regarding your rights and
 *  obligations.
 *
 *  THE SOFTWARE AND DOCUMENTATION ARE PROVIDED "AS IS" WITHOUT
 *  WARRANTY OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING
 *  WITHOUT LIMITATION, ANY WARRANTY OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE, TITLE AND NON-INFRINGEMENT. IN NO
 *  EVENT SHALL LICENSOR BE LIABLE FOR ANY INCIDENTAL, SPECIAL,
 *  INDIRECT OR CONSEQUENTIAL DAMAGES, LOST PROFITS OR LOST DATA,
 *  COST OF PROCUREMENT OF SUBSTITUTE GOODS, TECHNOLOGY OR
 *  SERVICES, ANY CLAIMS BY THIRD PARTIES (INCLUDING BUT NOT
 *  LIMITED TO ANY DEFENSE THEREOF), ANY CLAIMS FOR INDEMNITY OR
 *  CONTRIBUTION, OR OTHER SIMILAR COSTS, WHETHER ASSERTED ON THE
 *  BASIS OF CONTRACT, TORT (INCLUDING NEGLIGENCE), BREACH OF
 *  WARRANTY, OR OTHERWISE.
 *
 *********************************************************************/
declare(strict_types=1);

const TARHEADER_PATH_OFS = 0;
const TARHEADER_MODESTR_OFS = 100;
const TARHEADER_UIDSTR_OFS = 108;
const TARHEADER_GIDSTR_OFS = 116;
const TARHEADER_SIZESTR_OFS = 124;
const TARHEADER_UNIXTIMESTR_OFS = 136;
const TARHEADER_CHECKSUMSTR_OFS = 148;
const TARHEADER_TYPEFLAG_OFS = 156;
const TARHEADER_LINKNAME_OFS = 157;
const TARHEADER_MAGIC_OFS = 257;
const TARHEADER_MAGICVER_OFS = 263;
const TARHEADER_PAD_OFS = 265;

const TAROP_LOAD_FILE = 0;      // first functions require shared Read-only access
const TAROP_LIST_FILES = 1;
const TAROP_WORKON_FILES = 2;   // must stay the first TAR op requiring Read-write access
const TAROP_UPDATE_FILE = 3;    // must stay the first TAR op requiring Read-write access and causing file rewrite
const TAROP_REPLACE_FILE = 4;
const TAROP_DELETE_FILE = 5;

/*
 * Binary (little-endian) encoding/decoding
 */

function decodeUint(string $buf, int $ofs, int $size): float
{
    $res = 0;
    for($i = $size-1; $i >= 0; $i--) {
        $res = ($res << 8) + ord($buf[$ofs+$i]);
    }
    return $res;
}

function decodeFloat(string $buf, int $ofs, bool $flipBit): float
{
    $intVal = ord($buf[$ofs]) + 0x100*ord($buf[$ofs+1]) + 0x10000*ord($buf[$ofs+2]) + 0x1000000*ord($buf[$ofs+3]);
    if($flipBit) {
        if($intVal == 0xffffffff) {
            return NAN;
        }
        $intVal ^= 0x80000000;
    }
    if($intVal > 0x7fffffff) {
        $intVal -= 0x100000000;
    }
    return $intVal / 1000.0;
}

function encodeUint(int $value, int $size): string
{
    $data = chr($value & 0xff);
    for($i = 1; $i < $size; $i++) {
        $value = $value >> 8;
        $data .= chr($value & 0xff);
    }
    return $data;
}

function encodeFloat(float $value, bool $flipBit): string
{
    if($flipBit) {
        if(is_nan($value)) {
            $intVal = 0xffffffff;
        } else {
            $intVal = intval(round($value * 1000));
            $intVal ^= 0x80000000;
        }
    } else {
        $intVal = intval(round($value * 1000));
    }
    $intVal &= 0xffffffff;
    return chr($intVal & 0xff).chr(($intVal >> 8) & 0xff)
        .chr(($intVal >> 16) & 0xff).chr(($intVal >> 24) & 0xff);
}

/*
 * Octal decoding (for Tar file support)
 */
function parseOctal(string $buffer, int $ofs, int $maxlen): int
{
    for($len = 0; $len < $maxlen; $len++) {
        if(ord($buffer[$ofs+$len]) == 0) break;
    }
    $octalStr = substr($buffer, $ofs, $len);
    return intval(base_convert($octalStr, 8, 10));
}

class TarObject
{
    public string $path;
    public string $header;
    public string $content;
    public int $contentSize;
    public int $storageSize;
    public int $modifTime;
    public int $tarOffset;
    public int $crc;
    public bool $gzipEncoded;

    public function __construct(VHubServerHTTPRequest $httpReq, int $tarOffset, int $fileSize, string $header)
    {
        $headerlen = strlen($header);
        $maxpathlen = min($headerlen, 99);
        for($pathlen = 0; $pathlen < $maxpathlen; $pathlen++) {
            if(ord($header[$pathlen]) == 0) break;
        }
        $this->path = substr($header, 0, $pathlen);
        $this->contentSize = $fileSize;
        $this->storageSize = ($fileSize + 511) & ~0x1ff;
        $this->gzipEncoded = false;
        if($headerlen >= 256) {
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Found ".$this->path." (size=".$this->contentSize.")");
            $this->header = $header;
            $this->modifTime = parseOctal($header, TARHEADER_UNIXTIMESTR_OFS, 12);
        } else {
            $this->header = str_repeat(chr(0), 512);
            $this->modifTime = time();
            $this->safecopyz($this->path, TARHEADER_PATH_OFS, TARHEADER_MODESTR_OFS);
            $this->safecopyz('0100777', TARHEADER_MODESTR_OFS, TARHEADER_UIDSTR_OFS);
            $this->safecopyz('0000000', TARHEADER_UIDSTR_OFS, TARHEADER_GIDSTR_OFS);
            $this->safecopyz('0000000', TARHEADER_GIDSTR_OFS, TARHEADER_SIZESTR_OFS);
            $this->header[TARHEADER_TYPEFLAG_OFS] = '0';
            $this->safecopyz('ustar', TARHEADER_MAGIC_OFS, TARHEADER_MAGICVER_OFS);
            $this->header[TARHEADER_MAGICVER_OFS] = '0';
            $this->header[TARHEADER_MAGICVER_OFS+1] = '0';
        }
        $this->tarOffset = $tarOffset;
    }

    public function u32toOctal(int $number, int $headerOffset, int $ndigits): void
    {
        $octal = base_convert(strval($number), 10, 8);
        $octal = str_repeat('0', $ndigits-strlen($octal)).$octal;
        for($i = 0; $i < $ndigits; $i++) {
            $this->header[$headerOffset+$i] = $octal[$i];
        }
        $this->header[$headerOffset+$i] = chr(0);
    }

    public function memset(string $char, int $headerOffset, int $rep): void
    {
        for($i = 0; $i < $rep; $i++) {
            $this->header[$headerOffset+$i] = $char[0];
        }
    }

    public function safecopyz(string $data, int $headerOffset, int $endOffset): void
    {
        $len = strlen($data);
        if($headerOffset + $len >= $endOffset) {
            $len = $endOffset - $headerOffset - 1;
        }
        for($i = 0; $i < $len; $i++) {
            $this->header[$headerOffset+$i] = $data[$i];
        }
        $this->header[$headerOffset+$len] = chr(0);
    }

    public function updateTarHeader(): void
    {
        $this->u32toOctal($this->contentSize, TARHEADER_SIZESTR_OFS, 11);
        $this->u32toOctal($this->modifTime, TARHEADER_UNIXTIMESTR_OFS, 11);
        $this->memset(' ', TARHEADER_CHECKSUMSTR_OFS, 8);
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord($this->header[$i]);
        }
        $this->u32toOctal($checksum, TARHEADER_CHECKSUMSTR_OFS, 7);
    }
}

class TarFile
{
    protected VHubServer $server;
    protected string $tarfile;
    protected string $blankbuf;
    protected array $userFiles;
    protected mixed $workfd;
    protected int $tarfilesize;

    public function __construct(VHubServer $parent, string $tarname)
    {
        $this->server = $parent;
        $this->tarfile = $tarname;
        $this->blankbuf = str_repeat(chr(0), 1024);
        $this->userFiles = [];
        $this->workfd = null;
        $this->tarfilesize = 0;
    }

    public function formatTarFile(VHubServerHTTPRequest $httpReq): void
    {
        $fp = $this->server->frewrite($httpReq, $this->tarfile);
        fwrite($fp, $this->blankbuf, 1024);
        $this->server->fclose($httpReq, $fp, $this->tarfile);
    }

    public function searchTarFile(VHubServerHTTPRequest $httpReq, string $path): ?TarObject
    {
        $obj = $this->processTarFile($httpReq, $path, TAROP_LOAD_FILE);
        return $obj;
    }

    public function knownFile(string $path): ?TarObject
    {
        foreach($this->userFiles as $ufile) {
            if($ufile->path == $path) return $ufile;
        }
        return null;
    }

    public function knownFilesCount(): int
    {
        return sizeof($this->userFiles);
    }

    public function knownFilesMatching(string $pattern): array
    {
        $res = [];
        foreach($this->userFiles as $ufile) {
            if(fnmatch($pattern, $ufile->path, 0)) {
                $res[] = $ufile;
            }
        }
        return $res;
    }

    public function tarSize(): int
    {
        if($this->tarfilesize > 0) {
            return $this->tarfilesize;
        }
        if (!$this->server->fexists($this->tarfile)) {
            return 0;
        }
        return $this->server->filesize($this->tarfile);
    }

    /*
     *  Scan TAR file for search/update
     *  Possible values for <operation>:
     *     TAROP_LOAD_FILE - no change, search for <targetpath> and load it
     *     TAROP_LIST_FILES - no change, list all files matching <targetpath> and compute CRC
     *     TAROP_WORKON_FILES - get file positions matching <targetpath>, keep the exclusive lock
     *     TAROP_UPDATE_FILE - add file named <targetpath> with content <newContent>
     *     TAROP_REPLACE_FILE - put a file named <targetpath[2]> with content <newContent> in place of <targetpath[1]>
     *     TAROP_DELETE_FILE - delete file named <targetpath>
     *  Returns the target file record
     */
    public function processTarFile(VHubServerHTTPRequest $httpReq, string $targetPath, int $operation, string $newContent = ''): mixed
    {
        VHubServer::Log($httpReq, LOG_TARFILE, 5, "processTarFile ".$this->tarfile." for ".$targetPath.", op=".$operation);
        $res = ($operation == TAROP_LIST_FILES || $operation == TAROP_WORKON_FILES ? [] : null);
        if(!$this->server->fexists($this->tarfile)) {
            VHubServer::Log($httpReq, LOG_TARFILE, 3, "User container file does not yet exist ({$this->tarfile})");
            $this->formatTarFile($httpReq);
            return $res;
        }
        if($operation < TAROP_WORKON_FILES) {
            // non-exclusive access is required
            $fp = $this->server->fopen_ro($httpReq, $this->tarfile);
            $newfile = null;
        } else {
            // exclusive read-write access for update
            if($operation == TAROP_REPLACE_FILE) {
                $names = explode('|', $targetPath);
                $targetPath = $names[0];
                $newPath = $names[1];
                $operation = TAROP_UPDATE_FILE;
            } else {
                $newPath = $targetPath;
            }
            if($operation == TAROP_UPDATE_FILE) {
                $newfile = new TarObject($httpReq, -1, strlen($newContent), $newPath);
                $newfile->content = $newContent;
                $newfile->crc = crc32($newfile->content);
            } else {
                $newfile = null;
            }
            $fp = $this->server->fopen_rw($httpReq, $this->tarfile);
        }
        $rewriteFrom = -1;
        $this->userFiles = [];
        $tarOffset = 0;
        while(($rec = fread($fp, 512)) !== false) {
            // end of file is marked by a zero block
            if (ord($rec[0]) == 0) {
                if($operation >= TAROP_UPDATE_FILE) {
                    fseek($fp, $tarOffset, SEEK_SET); // rewind prior to zero block
                }
                break;
            }
            // skip over directories silently
            if ($rec[TARHEADER_TYPEFLAG_OFS] == '5') {
                $tarOffset += 512;
                continue;
            }
            // make sure this is a plain file
            if ($rec[TARHEADER_TYPEFLAG_OFS] != 0 && $rec[TARHEADER_TYPEFLAG_OFS] != '0') {
                VHubServer::Log($httpReq, LOG_TARFILE, 2, "Unexpected record type in .tar file header at {$tarOffset}, ignoring end of file");
                break;
            }
            // verify checksum to make sure we are not out of sync
            $checkstr = substr($rec, TARHEADER_CHECKSUMSTR_OFS, 8);
            $checksum = parseOctal($rec, TARHEADER_CHECKSUMSTR_OFS, 8);
            for($i = 0; $i < 8; $i++) {
                $rec[TARHEADER_CHECKSUMSTR_OFS+$i] = ' ';
            }
            $checkcheck = 0;
            for ($i = 0; $i < 512; $i++) {
                $checkcheck += ord($rec[$i]);
            }
            //VHubServer::Log($httpReq, LOG_TARFILE, 5, "Checksums: $checksum vs $checkcheck");
            if ($checksum != $checkcheck) {
                VHubServer::Log($httpReq, LOG_TARFILE, 2, "Checksum error in .tar file header at {$tarOffset}, ignoring end of file");
                break;
            }
            for($i = 0; $i < 8; $i++) {
                $rec[TARHEADER_CHECKSUMSTR_OFS+$i] = $checkstr[$i];
            }
            // make sure the file size makes sense (not more than half the "flash" size)
            $fsize = parseOctal($rec, TARHEADER_SIZESTR_OFS, 12);
            if ($fsize >= USERFILE_MAX_SIZE) {
                VHubServer::Log($httpReq, LOG_TARFILE, 2, "File in .tar file at {$tarOffset} is too large, ignoring end of file");
                break;
            }
            // all checks OK, we can now load the file into our list
            $obj = new TarObject($httpReq, $tarOffset, $fsize, $rec);
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Tar object at {$tarOffset}: {$obj->path}, size={$fsize} ({$obj->storageSize})");
            if($obj->path == $targetPath || ($obj->path == $targetPath.'.gz' && $operation == TAROP_LOAD_FILE)) {
                // this is the target path (load or update operation)
                if ($operation == TAROP_LOAD_FILE) {
                    // load the complete file
                    $obj->content = fread($fp, $obj->contentSize);
                    $obj->crc = crc32($obj->content);
                    $obj->gzipEncoded = ($obj->path == $targetPath.'.gz');
                    if($obj->storageSize > $obj->contentSize) {
                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);
                    }
                    $res = $obj;
                } else if($operation == TAROP_UPDATE_FILE) {
                    // must update this file
                    if ($obj->storageSize == $newfile->storageSize) {
                        // same storage size, update on the fly
                        VHubServer::Log($httpReq, LOG_TARFILE, 5, "Same storage size, updating on the fly");
                        $obj = $newfile;
                        $obj->tarOffset = $tarOffset;
                        $obj->updateTarHeader();
                        fseek($fp, $tarOffset, SEEK_SET); // rewind to header
                        fwrite($fp, $obj->header);
                        fwrite($fp, $obj->content);
                        if($obj->storageSize > $obj->contentSize) {
                            fwrite($fp, $this->blankbuf, $obj->storageSize - $obj->contentSize);
                        }
                        $res = $obj;
                        $newfile = null;
                    } else {
                        // different size, prepare to move file to the end (skip over current content)
                        VHubServer::Log($httpReq, LOG_TARFILE, 4, "New version of {$obj->path} has a different storage size, must rewrite tar file from $tarOffset");
                        $rewriteFrom = sizeof($this->userFiles);
                        fseek($fp, $obj->storageSize, SEEK_CUR);
                        continue;
                    }
                } else if($operation == TAROP_DELETE_FILE) {
                    // must remove this file (skip over current content)
                    VHubServer::Log($httpReq, LOG_TARFILE, 4, "Deleting {$obj->path}, must rewrite tar file from $tarOffset");
                    $rewriteFrom = sizeof($this->userFiles);
                    fseek($fp, $obj->storageSize, SEEK_CUR);
                    continue;
                } else if($operation == TAROP_LIST_FILES) {
                    // compute CRC of all files matching targetpath pattern
                    $content = fread($fp, $obj->contentSize);
                    if($obj->storageSize > $obj->contentSize) {
                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);
                    }
                    $obj->crc = crc32($content);
                    $res[] = $obj;
                }
            } else if($rewriteFrom >= 0) {
                // about to move a file to the end, load remaining content
                $obj->tarOffset = $tarOffset;
                if($obj->contentSize) {
                    $obj->content = fread($fp, $obj->contentSize);
                } else {
                    $obj->content = '';
                }
                if($obj->storageSize > $obj->contentSize) {
                    fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);
                }
            } else if($operation == TAROP_WORKON_FILES) {
                // compute CRC of all files matching targetpath pattern
                if(fnmatch($targetPath, $obj->path, 0)) {
                    $res[] = $obj;
                }
                // skip over content
                fseek($fp, $obj->storageSize, SEEK_CUR);
            } else if($operation == TAROP_LIST_FILES) {
                // compute CRC of all files matching targetpath pattern
                if(fnmatch($targetPath, $obj->path, 0)) {
                    $content = fread($fp, $obj->contentSize);
                    if($obj->storageSize > $obj->contentSize) {
                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);
                    }
                    $obj->crc = crc32($content);
                    $res[] = $obj;
                } else {
                    // skip over content
                    fseek($fp, $obj->storageSize, SEEK_CUR);
                }
            } else {
                // skip over content
                fseek($fp, $obj->storageSize, SEEK_CUR);
            }
            $this->userFiles[] = $obj;
            // prepare to handle next record in .tar file
            $tarOffset += 512 + $obj->storageSize;
        }
        if($operation >= TAROP_UPDATE_FILE) {
            // append updated file at the end if not updated on the file
            if($operation == TAROP_UPDATE_FILE && !is_null($newfile)) {
                if($tarOffset + $newfile->storageSize > FILES_MAX_SIZE) {
                    VHubServer::Log($httpReq, LOG_TARFILE, 2, "TAR file is too big to add a new file");
                } else {
                    if ($rewriteFrom < 0) {
                        $rewriteFrom = sizeof($this->userFiles);
                    }
                    $newfile->tarOffset = $tarOffset;
                    $newfile->updateTarHeader();
                    $this->userFiles[] = $newfile;
                    $res = $newfile;
                }
            }
            // rewrite part of the archive if a file is beeing moved
            if ($rewriteFrom >= 0) {
                if(isset($this->userFiles[$rewriteFrom])) {
                    // rewrite archive from first moved file
                    $obj = $this->userFiles[$rewriteFrom];
                    fseek($fp, $obj->tarOffset, SEEK_SET);
                    VHubServer::Log($httpReq, LOG_TARFILE, 5, "Rewriting tar file starting at {$obj->path} at {$obj->tarOffset}");
                    for ($i = $rewriteFrom; $i < sizeof($this->userFiles); $i++) {
                        $obj = $this->userFiles[$i];
                        fwrite($fp, $obj->header);
                        fwrite($fp, $obj->content);
                        if($obj->storageSize > $obj->contentSize) {
                            fwrite($fp, $this->blankbuf, $obj->storageSize - $obj->contentSize);
                        }
                    }
                }
            }
            // append terminal block in any case
            fwrite($fp, $this->blankbuf, 1024);
            // truncate file at current position
            ftruncate($fp, ftell($fp));
        }
        if($operation == TAROP_WORKON_FILES) {
            $this->workfd = $fp;
        } else {
            $this->server->fclose($httpReq, $fp, $this->tarfile);
        }
        return $res;
    }

    public function tarWorkRead(TarObject $obj, int $relofs, int $size): string
    {
        if($relofs >= $obj->contentSize) {
            return '';
        }
        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);
        if($relofs + $size > $obj->contentSize) {
            $size = $obj->contentSize - $relofs;
        }
        return fread($this->workfd, $size);
    }

    public function tarWorkReadUint(TarObject $obj, int $relofs, int $size): int
    {
        if($relofs >= $obj->contentSize) {
            return -1;
        }
        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);
        if($relofs + $size > $obj->contentSize) {
            $size = $obj->contentSize - $relofs;
        }
        $res = 0;
        $data = fread($this->workfd, $size);
        for($i = $size-1; $i >= 0; $i--) {
            $res = ($res << 8) + ord($data[$i]);
        }
        return $res;
    }

    public function tarWorkWrite(TarObject $obj, int $relofs, string $data): void
    {
        if($relofs >= $obj->contentSize) {
            return;
        }
        $absofs = $obj->tarOffset + 512 + $relofs;
        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);
        $size = strlen($data);
        if($relofs + $size > $obj->contentSize) {
            $size = $obj->contentSize - $relofs;
        }
        try {
            fwrite($this->workfd, $data, $size);
        } catch(Throwable $err) {
            VHubServer::Log($httpReq, LOG_DATALOGGER, 2, "Error writing to file {$this->workfd} in tarWorkWrite: ".$err->getMessage());
            VHubServer::Log($httpReq, LOG_DATALOGGER, 2, "   while writing {$size}/".strlen($data)." bytes at offset {$absofs} ({$relofs})");
        }
    }

    public function tarWorkWriteUint(TarObject $obj, int $relofs, int $value, int $size): void
    {
        if($relofs >= $obj->contentSize) {
            return;
        }
        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);
        if($relofs + $size > $obj->contentSize) {
            $size = $obj->contentSize - $relofs;
        }
        $data = chr($value & 0xff);
        for($i = 1; $i < $size; $i++) {
            $value = $value >> 8;
            $data .= chr($value & 0xff);
        }
        fwrite($this->workfd, $data, $size);
    }

    public function tarWorkDone(VHubServerHTTPRequest $httpReq): void
    {
        if(!is_null($this->workfd)) {
            $this->server->fclose($httpReq, $this->workfd, $this->tarfile);
        }
    }
}

class YfsObject
{
    public string $path;
    public string $header;
    public string $content;
    public int $contentSize;
    public int $crc;
    public bool $gzipEncoded;

    public function __construct(string $header, mixed $fd)
    {
        $nameLen = ord($header[8]);
        $this->path = substr($header, 9, $nameLen);
        $this->header = $header;
        $this->contentSize = ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]);
        $this->gzipEncoded = ((ord($header[3]) & 0x80) != 0);
        $this->crc = ord($header[4]) + 0x100*ord($header[5]) + 0x10000*ord($header[6]) + 0x1000000*ord($header[7]);
        if($this->contentSize > 0) {
            $prefix = ($this->gzipEncoded ? "\x1f\x8b\x08\x00\x00\x00\x00\x00" : '');
            $this->content = $prefix.fread($fd, $this->contentSize);
        } else {
            $this->content = '';
        }
    }
}

class YfsFile
{
    protected VHubServer $server;
    protected string $yfspath;
    protected mixed $fd;
    protected int $nFiles;
    protected int $pageSize;
    protected array $index;

    public function __construct(VHubServer $parent, string $yfspath)
    {
        $this->server = $parent;
        $this->yfspath = $yfspath;
        $this->fd = null;
        $this->nFiles = 0;
        $this->pageSize = 0;
        $this->index = [];
    }

    protected function loadIndex(VHubServerHTTPRequest $httpReq): void
    {
        if(substr($this->yfspath, 0, 5) == 'data:') {
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Open YFS image from memory, size=".(strlen($this->yfspath)-5));
            $this->fd = fopen('php://memory', 'r+b');
            fwrite($this->fd, substr($this->yfspath, 5));
            rewind($this->fd);
        } else {
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Open YFS image from disk, path len=".strlen($this->yfspath));
            $this->fd = fopen($this->yfspath, 'rb');
        }
        if($this->fd === false) {
            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Cannot open YFS image");
            return;
        }
        $header = fread($this->fd, 12);
        if(substr($header, 0, 4) != 'YFS3') {
            VHubServer::Log($httpReq, LOG_TARFILE, 2, "YFS image is corrupt");
            fclose($this->fd);
            $this->fd = false;
            return;
        }
        $nfiles = ord($header[10]) + 256 * ord($header[11]);
        $tocBuff = fread($this->fd, 6 * $nfiles);
        // determine the YFS page size by looking at the length of the first file wrapping page zero
        $prevPage = 0;
        $prevOfs = ftell($this->fd);
        for($i = 0; $i < $nfiles; $i++) {
            $ofs = 2*$nfiles + 4*$i;
            $dataPage = ord($tocBuff[$ofs+0]) + 256 * ord($tocBuff[$ofs+1]);
            $dataOfs = ord($tocBuff[$ofs+2]) + 256 * ord($tocBuff[$ofs+3]);
            if($dataPage > 0) break;
            $prevPage = $dataPage;
            $prevOfs = $dataOfs;
        }
        // read header of previous file
        fseek($this->fd, $prevOfs, SEEK_SET);
        $header = fread($this->fd, 10);
        $pathlen = ord($header[8]);
        $hdrlen = ($pathlen + 10) & ~1;
        $contentStorage = (ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]) + 1) & ~1;
        $pageSize = ($prevOfs + $hdrlen + $contentStorage - $dataOfs) / ($dataPage - $prevPage);
        $this->pageSize = intVal(round($pageSize/2)*2); // round to 2, just in case
        // now parse the complete index
        for($i = 0; $i < $nfiles; $i++) {
            $nameHash = ord($tocBuff[2*$i]) + 256 * ord($tocBuff[2*$i+1]);
            $ofs = 2*$nfiles + 4*$i;
            $dataPage = ord($tocBuff[$ofs+0]) + 256 * ord($tocBuff[$ofs+1]);
            $dataOfs = ord($tocBuff[$ofs+2]) + 256 * ord($tocBuff[$ofs+3]) + $this->pageSize * $dataPage;
            if(isset($this->index[$nameHash])) {
                $this->index[$nameHash][] = $dataOfs;
            } else {
                $this->index[$nameHash] = [ $dataOfs ];
            }
        }
        $this->nFiles = $nfiles;
    }

    protected function nameHash(string $name): int
    {
        $hash = 0;
        $nameLen = strlen($name);
        for($i = 0; $i < $nameLen; $i++) {
            $hash = (($hash << 1) + ord($name[$i])) & 0xffff;
        }
        if($hash == 0xffff) {
            // 0xffff is a reserved value
            $hash--;
        }
        return $hash;
    }

    public function search(VHubServerHTTPRequest $httpReq, string $path): ?YfsObject
    {
        if(is_null($this->fd)) {
            // preload index
            $this->loadIndex($httpReq);
        }
        if($this->fd === false) {
            // failed to preload index, fail every file
            return null;
        }

        // compute hash, lookup in index, seek in file at dataOfs, verify filename
        $hash = $this->nameHash($path);
        if(!isset($this->index[$hash])) {
            return null;
        }
        $pathlen = strlen($path);
        $candidates = $this->index[$hash];
        foreach($candidates as $dataOfs) {
            // load file header
            $hdrlen = ($pathlen + 10) & ~1;
            fseek($this->fd, $dataOfs, SEEK_SET);
            $filehdr = fread($this->fd, $hdrlen);
            // verify that file name len matches
            if(ord($filehdr[8]) != $pathlen) {
                continue;
            }
            // verify that file name matches
            if(substr($filehdr, 9, $pathlen) != $path) {
                continue;
            }
            return new YfsObject($filehdr, $this->fd);
        }
        return null;
    }

    public function loadAll(VHubServerHTTPRequest $httpReq): array
    {
        if(is_null($this->fd)) {
            // preload index
            $this->loadIndex($httpReq);
        }
        // default to no file found if we failed to load index
        $result = [];
        if($this->fd === false) {
            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Failed to load YFS file index");
        } else {
            foreach($this->index as $hash => $filelist) {
                foreach ($filelist as $dataOfs) {
                    // load file header
                    fseek($this->fd, $dataOfs, SEEK_SET);
                    $filehdr = fread($this->fd, 10);
                    if(strlen($filehdr) > 8) {
                        $pathlen = ord($filehdr[8]);
                        VHubServer::Log($httpReq, LOG_TARFILE, 4, "YFS object at {$dataOfs}: size={$pathlen}");
                        $filehdr .= fread($this->fd, $pathlen & ~1);
                        $result[] = new YfsObject($filehdr, $this->fd);
                    } else {
                        VHubServer::Log($httpReq, LOG_TARFILE, 2, "YFS object at {$dataOfs}: bad header");
                    }
                }
            }
        }
        return $result;
    }
}

class FileServer
{
    protected VHubServer $server;
    protected YfsFile $yfsFiles;
    protected TarFile $ownFiles;
    protected array $deviceFiles;

    public string $specialUploadFiles = '~^(txdata|logs\.txt|sendSMS)|((rgb|hsl|(layer[0-9])):.*)$~';
    public array $specialDownloadFiles = [
        'display.gif', 'rgb.bin'
    ];

    public function __construct(VHubServer $parent)
    {
        $this->server = $parent;
        $this->yfsFiles = new YfsFile($parent, UIFILE);
        $this->ownFiles = new TarFile($parent, 'VHUB4WEB-files.tar');
        $this->deviceFiles = [];
    }

    public function sendContentHeader(VHubServerHTTPRequest $httpReq, string $extension): void
    {
        switch(strtolower($extension)) {
            case 'json':
            case 'jzon':
                $mimetype = 'application/json; charset=iso-8859-1';
                break;
            case 'html':
            case '':
                $mimetype = 'text/html';
                break;
            case 'js':
                $mimetype = 'application/javascript';
                break;
            case 'xml':
                $mimetype = 'text/xml';
                break;
            case 'txt':
                $mimetype = 'text/plain';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'css':
                $mimetype = 'text/css';
                break;
            case 'jpeg':
            case 'jpg':
                $mimetype = 'image/jpeg';
                break;
            case 'svg':
                $mimetype = 'image/svg+xml';
                break;
            case 'byn':
            case 'bin':
                $mimetype = 'text/plain; charset=x-user-defined';
                break;
            default:
                $mimetype = 'application/'.$extension;
        }
        $httpReq->putHeader('Content-Type: '.$mimetype);
    }

    public function accessDeviceFiles(VHubServerHTTPRequest $httpReq, string $serial): TarFile
    {
        if(!isset($this->deviceFiles[$serial])) {
            $this->deviceFiles[$serial] = new TarFile($this->server, $serial.'.tar');
        }
        return $this->deviceFiles[$serial];
    }

    public function loadDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile): ?string
    {
        $tarfile = $this->accessDeviceFiles($httpReq, $serial);
        $obj = $tarfile->searchTarFile($httpReq, $subfile);
        if(is_null($obj)) {
            return null;
        }
        return $obj->content;
    }

    public function isKnownDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile): bool
    {
        $tarfile = $this->accessDeviceFiles($httpReq, $serial);
        $existing = $tarfile->knownFile($subfile);
        if(is_null($existing)) {
            $existing = $tarfile->knownFile($subfile.'.gz');
            if(is_null($existing)) {
                return false;
            }
        }
        return true;
    }

    public function saveDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile, string $content): void
    {
        $tarfile = $this->accessDeviceFiles($httpReq, $serial);
        if(str_ends_with($subfile, '.json') || str_ends_with($subfile, '.trace')) {
            $existing = $tarfile->knownFile($subfile);
            if(is_null($existing)) {
                // Reserve extra space for future growth
                $padsize = (str_contains($serial, 'HUB') ? 8192 : 1024);
                $padsize += strlen($content) >> 1;
            } else {
                // Keep allocated size unchanged, unless growth is really needed
                $padsize = $existing->storageSize - strlen($content) - 1;
                if($padsize < 0) {
                    $padsize = $existing->storageSize >> 1;
                }
            }
            $content .= str_repeat(' ', $padsize);
        }
        if($subfile != 'api.json') {
            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Archiving file {$subfile} for {$serial}");
        }
        $tarfile->processTarFile($httpReq, $subfile, TAROP_UPDATE_FILE, $content);
    }

    public function saveAllDeviceFiles(VHubServerHTTPRequest $httpReq, string $serial, string $fscontent): void
    {
        if(substr($fscontent, 0, 2) == 'S3') {
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "New _FS file format found in {$serial}");
            $yfs = new YfsFile($this->server, 'data:YF' . $fscontent);
        } else if(substr($fscontent, 0, 3) == 'FS3') {
            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Old _FS file format found in {$serial}");
            $yfs = new YfsFile($this->server, 'data:Y' . $fscontent);
        } else {
            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Bad _FS file content for {$serial}");
            return;
        }
        $files = $yfs->loadAll($httpReq);
        VHubServer::Log($httpReq, LOG_TARFILE, 5, "Number of files found: ".sizeof($files));
        foreach($files as $yfsfile) {
            VHubServer::Log($httpReq, LOG_TARFILE, 5, "YFS file found: {$yfsfile->path} (size={$yfsfile->contentSize})");
            $savepath = 'yfs/'.$yfsfile->path;
            if($yfsfile->gzipEncoded) {
                $savepath .= '.gz';
            }
            $this->saveDeviceFile($httpReq, $serial, $savepath, $yfsfile->content);
        }
    }

    public function filesCmd(VHubServerHTTPRequest $httpReq, string $action, string $fname): void
    {
        $res = [];
        switch($action) {
            case 'dir':
                $objs = $this->ownFiles->processTarFile($httpReq, $fname, TAROP_LIST_FILES);
                $res = [];
                foreach($objs as $obj) {
                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);
                    $res[] = ['name' => $obj->path, 'size' => $obj->contentSize, 'crc' => $crc];
                }
                break;
            case 'stat':
                $objs = $this->ownFiles->processTarFile($httpReq, $fname, TAROP_LIST_FILES);
                if(sizeof($objs) == 0) {
                    $res = ['stat' => 'absent', 'size' => 0, 'crc' => 0];
                } else {
                    $obj = $objs[0];
                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);
                    $res = ['stat' => 'present', 'size' => $obj->contentSize, 'crc' => $crc];
                }
                break;
            case 'del':
                $this->ownFiles->processTarFile($httpReq, $fname, TAROP_DELETE_FILE);
                $res = ['res' => 'ok'];
                break;
            case 'format':
                $this->ownFiles->formatTarFile($httpReq);
                $res = ['res' => 'ok'];
                break;
        }
        $this->server->apiroot->api->files->updateStats($httpReq, $this->ownFiles->knownFilesCount(), $this->ownFiles->tarSize());
        $this->sendContentHeader($httpReq, 'json');
        $httpReq->putStr(json_encode($res));
    }

    public function deviceFilesCmd(VHubServerHTTPRequest $httpReq, string $serial, string $action, string $fname): void
    {
        $tarfile = $this->accessDeviceFiles($httpReq, $serial);
        $res = [];
        switch($action) {
            case 'dir':
                $objs = $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_LIST_FILES);
                $res = [];
                foreach($objs as $obj) {
                    // all results are expected to be in 'files/' subdirectory
                    $devpath = $obj->path;
                    if(substr($devpath, 0, 6) != 'files/') continue;
                    $devpath = substr($devpath, 6);
                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);
                    $res[] = ['name' => $devpath, 'size' => $obj->contentSize, 'crc' => $crc];
                }
                break;
            case 'stat':
                $objs = $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_LIST_FILES);
                if(sizeof($objs) == 0) {
                    $res = ['stat' => 'absent', 'size' => 0, 'crc' => 0];
                } else {
                    $obj = $objs[0];
                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);
                    $res = ['stat' => 'present', 'size' => $obj->contentSize, 'crc' => $crc];
                }
                break;
            case 'del':
                // schedule deletion on device
                $apinode = $this->server->apiroot->bySerial->subnode($serial);
                $apinode->fileList->deleteOnDevice($httpReq, $fname);
                // remove from tarball
                $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_DELETE_FILE);
                $res = ['res' => 'ok'];
                break;
            case 'format':
                // schedule format on device
                $apinode = $this->server->apiroot->bySerial->subnode($serial);
                $apinode->fileList->formatOnDevice();
                // remove all user files from tarball
                $objs = $tarfile->processTarFile($httpReq, 'files/*', TAROP_LIST_FILES);
                for($i = 0; $i < sizeof($objs); $i++) {
                    $tarfile->processTarFile($httpReq, 'files/'.$objs[$i]->path, TAROP_DELETE_FILE);
                }
                $res = ['res' => 'ok'];
                break;
        }
        $this->sendContentHeader($httpReq, 'json');
        $httpReq->putStr(json_encode($res));
    }

    public function filesUpload(VHubServerHTTPRequest $httpReq, string $path, string $content): void
    {
        $this->ownFiles->processTarFile($httpReq, $path, TAROP_UPDATE_FILE, $content);
        $this->server->apiroot->api->files->updateStats($httpReq, $this->ownFiles->knownFilesCount(), $this->ownFiles->tarSize());
    }

    public function deviceFilesUpload(VHubServerHTTPRequest $httpReq, string $serial, string $path, string $content): void
    {
        // For other special upload files, put in -pending req only and exit
        if(preg_match($this->specialUploadFiles, $path) !== FALSE) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Upload to special file for {$serial}: {$path}");
            $this->server->scheduleUploadOnDevice($httpReq, $serial, $path, $content);
            return;
        }

        // Firmware update is handled in a special way
        if($path == 'firmware' || $path == 'firmwareConf' || $path == 'Xfirmw') {
            return;
        }

        // For regular user files, put content in tarball and update filelist for synchronization
        $tarfile = $this->accessDeviceFiles($httpReq, $serial);
        $tarfile->processTarFile($httpReq, 'files/'.$path, TAROP_UPDATE_FILE, $content);
        $newfile = $tarfile->knownFile('files/'.$path);
        $apinode = $this->server->apiroot->bySerial->subnode($serial);
        $apinode->fileList->uploadToDevice($httpReq, $path, $newfile->contentSize, $newfile->crc);
    }

    public function sendFileContent(VHubServerHTTPRequest $httpReq, string $content, string $extension, ?int $crc = null): void
    {
        if(is_null($crc)) {
            $crc = crc32($content);
        }
        $this->sendContentHeader($httpReq, $extension);
        $httpReq->putHeader('Content-Length: '.strlen($content));
        $httpReq->putHeader('Cache-Control: no-cache');
        $httpReq->putHeader('ETag: '.dechex($crc));
        $httpReq->putBin($content);
    }

    public function sendFile(VHubServerHTTPRequest $httpReq, string $path, string $extension): void
    {
        // if a local mount override is in place, search it first
        if(defined('MOUNT_SERVER_FILES')) {
            foreach(MOUNT_SERVER_FILES as $mountDir) {
                $fullPath = $mountDir.'/'.$path;
                if(file_exists($fullPath)) {
                    $content = file_get_contents($fullPath);
                    if(str_ends_with($fullPath, '.html') && strpos($content, ' rel="icon"') !== FALSE) {
                        $favicon = false;
                        foreach(MOUNT_SERVER_FILES as $mountDirAgain) {
                            $faviconPath = $mountDirAgain . '/favicon.svg';
                            if(file_exists($faviconPath)) {
                                $favicon = base64_encode(file_get_contents($faviconPath));
                                break;
                            }
                        }
                        if($favicon) {
                            $content = preg_replace('~(rel="icon" id="favicon" type="image/svg[+]xml" href="data:image/svg[+]xml;base64,)[^"]*~', '$1'.$favicon, $content);
                        }
                    }
                    // use special e-tag to identify mounted file
                    $crc = 0xFF00000000 + crc32($content);
                    $this->sendFileContent($httpReq, $content, $extension, $crc);
                    return;
                }
            }
        }
        // search in embedded UI files
        $obj = $this->yfsFiles->search($httpReq, $path);
        if(is_null($obj)) {
            // search in user files
            $obj = $this->ownFiles->searchTarFile($httpReq, $path);
            if(is_null($obj)) {
                // not found neither
                $httpReq->putStatus(404);
                Print("Sorry, the requested file ".htmlspecialchars($path)." does not exist on server");
                return;
            }
        }
        $content = $obj->content;
        $crc = $obj->crc;
        if($obj->gzipEncoded) {
            $httpReq->putHeader('Content-Encoding: gzip');
        }
        $this->sendFileContent($httpReq, $content, $extension, $crc);
    }

    public function sendDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile, string $extension): void
    {
        if(!isset($this->deviceFiles[$serial])) {
            $this->deviceFiles[$serial] = new TarFile($this->server, $serial.'.tar');
        }
        $tarfile = $this->deviceFiles[$serial];
        if(array_search($subfile, $this->specialDownloadFiles) !== FALSE) {
            // special files are in root directory
            $obj = $tarfile->searchTarFile($httpReq, $subfile);
        } else {
            // search for regular files in yfs/, then files/, then standard EmbeddedUI
            VHubServer::Log($httpReq, LOG_TARFILE, 4, "Search for ".'yfs/'.$subfile);
            $obj = $tarfile->searchTarFile($httpReq, 'yfs/'.$subfile);
            if(is_null($obj)) {
                VHubServer::Log($httpReq, LOG_TARFILE, 4, "Search for ".'files/'.$subfile);
                $obj = $tarfile->searchTarFile($httpReq, 'files/'.$subfile);
            }
            if(is_null($obj)) {
                // fallback to standard EmbeddedUI file if available
                $subpath = $subfile;
                $obj = $this->yfsFiles->search($httpReq, $subpath);
            }
        }
        if(is_null($obj)) {
            // file not found
            $httpReq->putStatus(404);
            Print("Sorry, the requested device file ".htmlspecialchars($subfile)." does not exist on ".htmlspecialchars($serial)." [vhub4web]\r\n");
            return;
        }
        $content = $obj->content;
        $crc = $obj->crc;
        if($obj->gzipEncoded) {
            $httpReq->putHeader('Content-Encoding: gzip');
        }
        $this->sendFileContent($httpReq, $content, $extension, $crc);
    }

}