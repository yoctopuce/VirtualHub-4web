<?php
/*********************************************************************
 *
 * $Id: NotifStream.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * Notification stream emulation for VirtualHub4web
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

define('NOTIF_FILE', 'VHUB4WEB-YN*.byn');   // Name pattern for the notification buffers files

const NOTIF_FILE_ENDSIZE = 32768;
const NOTIF_POS_WRAP = 0x100000000;
const NOTIF_MAX_LEN = 69;
const NOTIF_NAME = 'YN010';
const NOTIF_PRODNAME = 'YN011';
const NOTIF_CHILD = 'YN012';
const NOTIF_FIRMWARE = 'YN013';
const NOTIF_FUNCNAME = 'YN014';
const NOTIF_FUNCVAL = 'YN015';
const NOTIF_STREAMREADY = 'YN016';
const NOTIF_LOG = 'YN017';
const NOTIF_FUNCNAMEYDX = 'YN018';
const NOTIF_PRODINFO = 'YN019';
const NOTIF_CONFCHGYDX = 'YN01s';
const NOTIF_FLUSHV2YDX = 'YN01t';
const NOTIF_FUNCV2YDX = 'u';
const NOTIF_TIMEV2YDX = 'v';
const NOTIF_DEVLOGYDX = 'YN01w';
const NOTIF_TIMEVALYDX = 'x';
const NOTIF_FUNCVALYDX = 'y';
const NOTIF_TIMEAVGYDX = 'z';

class NotifStream
{
    protected VHubServer $server;
    protected string $datadir;
    protected string $notfile;
    protected int $filepos;     // Absolute offset of first notification in file
    protected int $abspos;      // Current absolute position within stream
    protected int $curpos;      // Current position within file
    protected int $reqlen;      // Requested length, if any
    private int $avail;         // Quantity of notification available to send
    protected mixed $fd;

    /*
     * Prepare to work on the notification stream as close as possible to requested position
     *
     * Requested position can be: -1 for opening at current latest position
     *                            n for opening at a specific position
     */

    public static function StreamAt(VHubServerHTTPRequest $httpReq, VHubServer $parent, int $position): NotifStream
    {
        $datadir = $parent->getDataDir();
        $regexpr = '~^'.str_replace('*', '([0-9]+)', NOTIF_FILE).'$~';
        $filelist = [];
        foreach (glob($datadir.NOTIF_FILE) as $filepath) {
            $filename = substr($filepath, strlen($datadir));
            if(preg_match($regexpr, $filename, $matches)) {
                $filelist[] = intVal($matches[1], 10);
            }
        }
        if(sizeof($filelist) == 0) {
            $filepos = 0;
        } else {
            sort($filelist);
            if(max($filelist) - min($filelist) > (NOTIF_POS_WRAP/4)) {
                // list of positions is wrapping, reorder them accordingly
                for($i = 1; $i < sizeof($filelist); $i++) {
                    if($filelist[$i] - $filelist[$i-1] > (NOTIF_POS_WRAP/4)) break;
                }
                $filelist = array_merge(array_slice($filelist, $i), array_slice($filelist, 0, $i));
            }
            if(sizeof($filelist) > 4) {
                // cleanup oldest notification files
                $notfile = sprintf(str_replace('*', '%010u', NOTIF_FILE), $filelist[0]);
                $fullpath = $parent->getDataDir().$notfile;
                if(file_exists($fullpath)) {
                    @unlink($fullpath);
                }
            }
            if($position == -1) {
                // use the latest file
                $filepos = $filelist[sizeof($filelist)-1];
            } else {
                // use the file containin the requested position, or the oldest file available
                $filepos = $filelist[0];
                for($i = 1; $i < sizeof($filelist); $i++) {
                    if($position >= $filelist[$i] && $position < $filelist[$i] + (NOTIF_POS_WRAP/4)) {
                        $filepos = $filelist[$i];
                    }
                }
            }
        }
        VHubServer::Log($httpReq, LOG_CLIENTREQ, 5, 'Open stream at '.$position.', using file @'.$filepos);
        return new NotifStream($parent, $filepos, $position);
    }

    public function __construct(VHubServer $parent, int $filepos, int $position)
    {
        $this->server = $parent;
        $this->datadir = $parent->getDataDir();
        $this->filepos = $filepos;
        $this->abspos = $position;
        $this->curpos = $position - $filepos;
        $this->reqlen = -1;
        $this->avail = 0;
        $this->fd = null;
    }

    protected function openNotFile(VHubServerHTTPRequest $httpReq, string $mode, bool $readonly): bool
    {
        $this->notfile = sprintf(str_replace('*', '%010u', NOTIF_FILE), $this->filepos);
        $fullpath = $this->datadir.$this->notfile;
        if($readonly && !file_exists($fullpath)) {
            $this->fd = null;
            return false;
        }
        try {
            $this->fd = @fopen($fullpath, $mode);
        } catch(Throwable) {}
        if($this->fd === false) {
            $this->fd = null;
            return false;
        }
        return true;
    }

    /*
     * Open the stream for reading, with a hint on requested data length
     *
     * Requested length can be: -1 for no limit
     *                          0 for a standard chunking by 32KB
     *                          1 for flushing always as soon as possible
     *                          n for flushing after n bytes
     *
     * Return the current absolute position in notification stream
     */
    public function openForRead(VHubServerHTTPRequest $httpReq, int $length): int
    {
        if($length == 0) {
            // default limit when flush is available but chunks help to improve performace
            $length = 0x7f00;
        }
        if($length >= 0) {
            // minimum value should allow for at least one notification
            if($length < NOTIF_MAX_LEN + 15) {
                $length = NOTIF_MAX_LEN + 15;
            }
        }
        $this->reqlen = $length;
        if($this->openNotFile($httpReq, 'rb', true)) {
            fseek($this->fd, 0, SEEK_END);
            $endpos = ftell($this->fd);
        } else {
            // file does not yet exist
            $endpos = 0;
        }
        if($this->abspos == -1) {
            $this->abspos = $this->filepos + $endpos;
            $this->curpos = $endpos;
        }
        if($endpos > $this->curpos && !is_null($this->fd)) {
            fseek($this->fd, $this->curpos, SEEK_SET);
            $this->avail = $endpos - $this->curpos;
        }
        return $this->abspos;
    }

    public function predictSize(): int
    {
        return max($this->reqlen, $this->avail);
    }

    public function readMore(VHubServerHTTPRequest $httpReq, int $maxlen): string
    {
        // switch to next file if needed
        if($this->curpos >= NOTIF_FILE_ENDSIZE) {
            if(!is_null($this->fd)) {
                fclose($this->fd);
            }
            $this->filepos = $this->abspos;
            $this->curpos = 0;
            $this->openForRead($httpReq, -1);
        }
        // make sure the log file has already been created
        if(is_null($this->fd)) {
            return "";
        }
        // read as much as permitted from the current notification file
        if($maxlen > 0) {
            $rsize = $maxlen;
        } else {
            $rsize = NOTIF_FILE_ENDSIZE;
        }
        $result = fread($this->fd, $rsize);
        $rsize = strlen($result);
        if($rsize == 0) {
            return $result;
        }
        if($result[$rsize-1] != "\n") {
            // make sure we stop on a complete notification
            while ($rsize > 0 && $result[$rsize - 1] != "\n") {
                $rsize--;
            }
            $result = substr($result, 0, $rsize);
            $this->curpos += $rsize;
            fseek($this->fd, $this->curpos, SEEK_SET);
            if($maxlen > 0) {
                // pad reply to the full requested size to force a flush and get a new buffer
                $result .= str_repeat("\n", $maxlen - $rsize);
            }
        } else {
            $this->curpos += $rsize;
        }
        $this->abspos += $rsize;
        return $result;
    }

    public function openForAppend(VHubServerHTTPRequest $httpReq): int
    {
        if(!$this->openNotFile($httpReq, 'a', false)) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 1, 'Fail to open for append '.$this->notfile);
            $endpos = 0;
        } else {
            fseek($this->fd, 0, SEEK_END);
            $endpos = ftell($this->fd);
        }
        $this->abspos = $this->filepos + $endpos;
        $this->curpos = $endpos;
        return $this->abspos;
    }

    public function close(VHubServerHTTPRequest $httpReq): void
    {
        if(!is_null($this->fd)) {
            fclose($this->fd);
        }
    }

    /*
     * Decode the timestamp in a TimedReport_V2
     */
    protected function decodeTimestamp(array $report, float &$duration): float
    {
        $time = $report[0] + 0x100 * $report[1] + 0x10000 * $report[2] + 0x1000000 * $report[3];
        $ms = $report[4] * 4;
        if (sizeof($report) > 5) {
            $mixedByte = $report[5];
            $ms += $mixedByte >> 6;
            $duration_ms = $report[6];
            $duration_ms += ($mixedByte & 0xf) * 0x100;
            if ($mixedByte & 0x10) {
                $duration = $duration_ms;
            } else {
                $duration = $duration_ms / 1000.0;
            }
        } else {
            $duration = 0.0;
        }
        return $time + $ms / 1000.0;
    }

    public function appendNotif(VHubServerHTTPRequest $httpReq, string $notif): void
    {
        // switch to next file if needed
        if($this->curpos >= NOTIF_FILE_ENDSIZE) {
            fclose($this->fd);
            $this->filepos = $this->abspos;
            $this->curpos = 0;
            $this->openForAppend($httpReq, );
        }
        fwrite($this->fd, $notif."\n");
    }

    /*
     * Add the notification corresponding to a module name change or beacon change
     */
    public function appendModuleNotification(VHubServerHTTPRequest $httpReq, array $wpVal): void
    {
        $serial = $wpVal['serialNumber'];
        $devYdxA = chr(65+$wpVal['index']);
        $this->appendNotif($httpReq, NOTIF_NAME.$serial.','.$wpVal['logicalName'].','.$wpVal['beacon'].','.$devYdxA);
    }

    /*
     * Add the set of notifications corresponding to a new whitepage entry
     */
    public function appendModuleArrivalNotifications(VHubServerHTTPRequest $httpReq, string $cloudSerial, array $wpVal): void
    {
        $serial = $wpVal['serialNumber'];
        $this->appendNotif($httpReq, NOTIF_CHILD.$cloudSerial.','.$serial.',1');
        $this->appendModuleNotification($httpReq, $wpVal);
        $this->appendNotif($httpReq, NOTIF_PRODINFO.$serial.','.sprintf('%04x', $wpVal['productId']));
    }

    /*
     * Add the set of notifications corresponding to a new whitepage entry
     */
    public function appendModuleRemovalNotifications(VHubServerHTTPRequest $httpReq, string $cloudSerial, string $serial): void
    {
        $this->appendNotif($httpReq, NOTIF_CHILD.$cloudSerial.','.$serial.',0');
    }

    /*
     * Add the notifications corresponding to a change in function name, type or idx
     */
    public function appendFunctionNameNotification(VHubServerHTTPRequest $httpReq, array $ypVal): void
    {
        $hwidParts = explode('.', $ypVal['hardwareId']);
        $this->appendNotif($httpReq, NOTIF_FUNCNAMEYDX.$hwidParts[0].','.$hwidParts[1].','.$ypVal['logicalName'].','.$ypVal['index'].','.$ypVal['baseType']);
    }

    /*
     * Add the notifications corresponding to a new function value
     */
    public function appendFunctionValNotification(VHubServerHTTPRequest $httpReq, array $ypVal): void
    {
        $hwidParts = explode('.', $ypVal['hardwareId']);
        $serial = $hwidParts[0];
        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);
        if($devydx < 0) return;
        $funydx = $ypVal['index'];
        $devYdxA = chr(65+($devydx & 63));
        $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));
        $this->appendNotif($httpReq, NOTIF_FUNCVALYDX.$devYdxA.$funYdxA.$ypVal['advertisedValue']);
        // FIXME: Make sure no buffer overflow can happen in API in since some advertiseValue
        //        should actually have been advertised using V2 notifications (6 bytes, etc)
    }

    /*
     * Add the notifications corresponding to a true timed report
     * Also insert data in the corresponding datalogger
     */
    public function handleTrueTimedReportNotification(VHubServerHTTPRequest $httpReq, string $serial, array $rawReports): void
    {
        VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Processing timed reports for {$serial}: ".sizeof($rawReports)." records");
        // 1. Forward timed reports in text mode
        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);
        if($devydx < 0) return;
        foreach($rawReports as $funydx => $rawReport) {
            $devYdxA = chr(65+($devydx & 63));
            $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));
            $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;
            for($i = 0; $i < sizeof($rawReport); $i++) {
                $msg .= sprintf('%02x', $rawReport[$i]);
            }
            $this->appendNotif($httpReq, $msg);
        }
        // 2. Add data to the dataLogger
        $datalogger = YDataLogger::FindDataLogger("{$serial}.dataLogger");
        $emulogger = $datalogger->get_userData();
        if(is_null($emulogger)) {
            $emulogger = new DataLogger($this->server, $serial);
            $datalogger->set_userData($emulogger);
        }
        $serverTimestamp = time();
        $timestamp = 0;
        $duration = 0;
        $newReports = [];
        $module = YModule::FindModule($serial);
        foreach($rawReports as $funydx => $rawReport) {
            if($funydx == 15) {
                $devTimestamp = $this->decodeTimestamp($rawReport, $duration);
                if($devTimestamp > $serverTimestamp+2*86400) {
                    // device timestamp more than a day in the future, this should never happen
                    VHubServer::Log($httpReq, LOG_HTTPCALLBACK, 2, "Timestamp of {$serial} is more than a 48h in the future (".$devTimestamp."), ignoring timed report");
                } else if($devTimestamp < $serverTimestamp-2*86400) {
                    // device timestamp more than a day in the past, this should never happen
                    VHubServer::Log($httpReq, LOG_HTTPCALLBACK, 2, "Timestamp of {$serial} is more than a 48h in the past (".$devTimestamp."), ignoring timed report");
                } else {
                    $timestamp = $devTimestamp;
                    VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "TimedReport for {$serial}: stamp={$timestamp}, duration={$duration}");
                }
            } else if($timestamp) {
                $functionId = $module->functionId($funydx);
                $sensor = YSensor::FindSensor("{$serial}.{$functionId}");
                $unit = $sensor->get_unit();
                $freqStr = $sensor->get_reportFrequency();
                if($freqStr == 'OFF') continue;
                $freq = new DataFrequency($freqStr);
                array_unshift($rawReport, 2); // prepend Timed Report V2 signature
                $measure = $sensor->_decodeTimedReport($timestamp, $duration, $rawReport);
                $newReports[$functionId] = [ 'sensor' => $sensor, 'measure' => $measure, 'unit' => $unit, 'freq' => $freq ];
            }
        }
        if(sizeof($newReports) > 0) {
            $emulogger->appendMeasures($httpReq, $newReports);
        }
    }

    /*
     * Add the notifications corresponding to a pseudo timed report
     * (this is only used for old hubs without timed-report buffer)
     */
    public function appendEmulatedTimedReportNotification(VHubServerHTTPRequest $httpReq, string $serial, array $reports): void
    {
        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);
        if($devydx < 0) return;
        $timestamp = $reports[array_key_first($reports)]['measure']->get_startTimeUTC();
        $funydx = 15; // special funYdx for the timestamp
        $devYdxA = chr(65+($devydx & 127));
        $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));
        $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;
        for($i = 0; $i < 4; $i++) {
            $msg .= sprintf('%02x', $timestamp & 0xff);
            $timestamp >>= 8;
        }
        $this->appendNotif($httpReq, $msg.'0003e8');
        foreach($reports as $functionId => $report) {
            $measure = $report['measure'];
            $value = $measure->get_averageValue();
            if(is_nan($value)) continue;
            $report = round($value * 1000);
            $hardwareId = $serial.'.'.$functionId;
            $funydx = $this->server->apiroot->funYdxByHwId[$hardwareId];
            $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));
            $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;
            while(true) {
                $lo = $report & 0xff;
                $msg .= sprintf('%02x', $lo);
                $report >>= 8;
                if($report >= 0) {
                    if(($lo & 0x80)==0 && $report == 0) break;
                } else {
                    if(($lo & 0x80)!=0 && $report == -1) break;
                }
            }
            $this->appendNotif($httpReq, $msg);
        }
    }

    /*
     * Add a config device log notification for the given serial number
     */
    public function appendDeviceLogNotification(VHubServerHTTPRequest $httpReq, string $serial): void
    {
        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);
        if($devydx < 0) return;
        $devYdxA = chr(65+($devydx & 63));
        $devYdxB = chr(48+(($devydx & 128) ? 64 : 0));
        $this->appendNotif($httpReq, NOTIF_LOG.$devYdxA.$devYdxB);
    }

    /*
     * Add a config change modification for the given serial number
     */
    public function appendConfigChangeNotification(VHubServerHTTPRequest $httpReq, string $serial): void
    {
        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);
        if($devydx < 0) return;
        $devYdxA = chr(65+($devydx & 63));
        $devYdxB = chr(48+(($devydx & 128) ? 64 : 0));
        $this->appendNotif($httpReq, NOTIF_CONFCHGYDX.$devYdxA.$devYdxB);
    }
}