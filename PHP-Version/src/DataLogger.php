<?php
/*********************************************************************
 *
 * $Id: DataLogger.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * Yoctopuce device DataLogger emulation for VirtualHub4web
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

class DataFrequency
{
    public string $freqStr;
    public float $period;
    public int $nb;
    public bool $perSec;
    public bool $perMin;
    public bool $perHour;
    protected int $maxSeqRowsCache;

    /*
     * DataFrequency constructor: accepts multiple types of argument:
     * - a frequency string, as used in the devices, such as "30/m"
     * - a period specified as a number of seconds
     */
    public function __construct(mixed $timebase)  // string|int|float
    {
        $this->maxSeqRowsCache = 0;
        $this->perSec = false;
        $this->perMin = false;
        $this->perHour = false;
        if(is_string($timebase)) {
            if(strlen($timebase) == 2) {
                // binary representation (two bytes)
                $freq = ord($timebase[0]);
                $this->nb = max($freq, 1);
                $period = 1.0 / $this->nb;
                $unit = ord($timebase[1]) & 7;
                if($unit == 2) {
                    $period *= 60;
                    $this->perMin = true;
                    $this->freqStr = $this->nb.'/m';
                } else if($unit == 4) {
                    $period *= 3600;
                    $this->perHour = true;
                    $this->freqStr = $this->nb.'/h';
                } else {
                    $this->perSec = true;
                    $this->freqStr = $this->nb.'/s';
                }
                $this->period = $period;
            } else {
                // device-like frequency, eg "30/m"
                $pos = strpos($timebase, '/');
                if($pos === False) {
                    $freq = floatval($timebase);
                    $this->nb = max($freq, 1);
                    $this->freqStr = $this->nb.'/s';
                    $this->perSec = true;
                    $this->period = 1.0 / $freq;
                } else {
                    $this->freqStr = $timebase;
                    $freq = intval(substr($timebase, 0, $pos));
                    $this->nb = max($freq, 1);
                    $period = 1.0 / $this->nb;
                    $unit = substr($timebase, $pos+1);
                    if($unit == 'm') {
                        $period *= 60;
                        $this->perMin = true;
                    } else if($unit == 'h') {
                        $period *= 3600;
                        $this->perHour = true;
                    } else {
                        $this->perSec = true;
                    }
                    $this->period = $period;
                }
            }
        } else if(is_numeric($timebase) && $timebase > 0) {
            // period in seconds
            $this->period = $timebase;
            if($this->period <= 1) {
                $freq = intval(round(1.0 / $this->period));
                $this->nb = min($freq, 100);
                $this->perSec = true;
                $this->freqStr = $this->nb.'/s';
            } elseif($this->period <= 60) {
                $this->nb = intval(round(60.0 / $this->period));
                $this->perMin = true;
                $this->freqStr = $this->nb.'/m';
            } else {
                $freq = intval(round(3600.0 / $this->period));
                $this->nb = max($freq, 1);
                $this->perHour = true;
                $this->freqStr = $this->nb.'/h';
            }
        }
    }

    /*
     * Round a timestamp to the closest multiple of the recording frequency
     */
    public function alignTimestamp(float $timestamp): float
    {
        if($this->period < 1) {
            $alignmentErr = fmod($timestamp, $this->period);
        } else {
            $timestamp = intval(round($timestamp));
            $timeofday = $timestamp % 86400;
            $alignmentErr = $timeofday % intval($this->period);
        }
        if($alignmentErr < $this->period / 2) {
            $timestamp -= $alignmentErr;
        } else {
            $timestamp += $this->period - $alignmentErr;
        }
        return $timestamp;
    }

    /*
     * Return the binary encoded representation of the frequency
     */
    public function encoded(): string
    {
        if($this->perHour) {
            return chr($this->nb).chr(4);
        } else if($this->perMin) {
            return chr($this->nb).chr(2);
        } else {
            return chr($this->nb).chr(1);
        }
    }

    /*
     * Compute the max sequence size for the given record frequency.
     * - No more than 250 instant measures, or 120 averaged measures
     * - Sequence period must be a convenient time unit
     */
    public function maxSeqRows(): int
    {
        if($this->maxSeqRowsCache <= 0) {
            $count = $this->nb;
            $maxRecs = ($this->perSec ? 250 : 120);
            // multiple of time units that make sense
            // (number of hours, 5-min periods or 5-sec periods)
            $timeMult = [ 12, 6, 3, 2, 1 ];

            if(!$this->perHour) {
                // current total is a second or a minute
                for($i = 0; $i < sizeof($timeMult); $i++) {
                    $better = $count*5*$timeMult[$i];
                    if($better <= $maxRecs) {
                        // use 12min sequences instead of 10min (far more efficient)
                        if($better == 120 && $this->perMin && $this->nb == 12) {
                            $better = 144;
                        }
                        $count = $better;
                        break;
                    }
                }
                if($i == 0 || $i >= sizeof($timeMult)) {
                    if($count*3 < $maxRecs) $count *= 3;
                    else if($count*2 < $maxRecs) $count *= 2;
                }
            } else {
                if($count <= 5) {
                    // up to 5 measures per hour => full day
                    return $count*24;
                }
                for($i = 0; $i < sizeof($timeMult); $i++) {
                    $better = $count*$timeMult[$i];
                    if($better <= $maxRecs) {
                        $count = $better;
                        break;
                    }
                }
            }
            $this->maxSeqRowsCache = $count;
        }
        return $this->maxSeqRowsCache;
    }
}

class DataFile
{
    public int $startstamp;
    public string $functionid;
    public string $unit;
    public TarObject $tarObject;

    public function __construct(TarObject $tarObject)
    {
        if(preg_match('~^datalogger/([0-9]+)-([a-zA-Z0-9]+)-(.*)-20[0-9]{2}-[0-9]{2}-[0-9]{2}.bin$~', $tarObject->path, $matches)) {
            $this->startstamp = intval($matches[1]);
            $this->functionid = $matches[2];
            $this->unit = $matches[3];
        } else {
            $this->startstamp = -1;
            $this->functionid = '???';
            $this->unit = '';
        }
        $this->tarObject = $tarObject;
    }
}

const DATASEQ_HEADER_SIZE = 28;   // first functions require shared Read-only access

class DataSeq
{
    protected TarFile $tarFile;
    protected ?TarObject $tarObj;
    protected int $seqOfs;
    protected int $dataOfs;
    protected int $nextSeqStampCache;
    protected string $header;
    protected string $data;
    public int $runIdx;                  // offset 0-3: run number
    public int $utcStamp;                // offset 4-7: start UTC timestamp
    public DataFrequency $frequency;     // offset 8-9: measure frequency
    public int $firstDur;                // offset 10-11: duration of 1st measure (s/ms)
    public int $firstMs;                 // offset 12-13: ms offset of 1st measure
    public int $nRows;                   // offset 14-15: number of measures (max 250)
    public float $avgVal;                // offset 16-19: sequence average (when complete)
    public float $minVal;                // offset 20-23: sequence min value (when complete)
    public float $maxVal;                // offset 24-27: sequence max value (when complete)
    public array $measures;              // one s32 (instant) or three s32 (avg/min/max) per time unit

    public function __construct(TarFile $tarFile, ?TarObject $tarObj, int $seqOfs)
    {
        $this->tarFile = $tarFile;
        $this->tarObj = $tarObj;
        $this->seqOfs = $seqOfs;
        $this->dataOfs = $seqOfs + DATASEQ_HEADER_SIZE;
        $this->nextSeqStampCache = 0;
        $this->header = '';
        $this->data = '';
        $this->nRows = 0;
        $this->avgVal = NAN;
        $this->minVal = NAN;
        $this->maxVal = NAN;
        $this->measures = [];
    }

    public function loadSeq(VHubServerHTTPRequest $httpReq, bool $withData): void
    {
        $header = $this->tarFile->tarWorkRead($this->tarObj, $this->seqOfs, 28);
        $this->header = $header;
        $this->runIdx = ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]) + 0x1000000*ord($header[3]);
        $this->utcStamp = ord($header[4]) + 0x100*ord($header[5]) + 0x10000*ord($header[6]) + 0x1000000*ord($header[7]);
        $this->frequency = new DataFrequency(substr($header, 8, 2));
        $this->firstDur = ord($header[10]) + 0x100*ord($header[11]);
        $this->firstMs = ord($header[12]) + 0x100*ord($header[13]);
        $this->nRows = ord($header[14]) + 0x100*ord($header[15]);
        $this->avgVal = decodeFloat($header, 16, true);
        $this->minVal = decodeFloat($header, 20, false);
        $this->maxVal = decodeFloat($header, 24, false);
        if($withData) {
            $rsize = 4 * $this->nRows;
            if($this->frequency->perSec) {
                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);
                for($pos = 0; $pos < $rsize; $pos += 4) {
                    $this->measures[] = decodeFloat($data, $pos, true);
                }
            } else {
                $rsize *= 3;
                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);
                for($pos = 0; $pos < $rsize; $pos += 12) {
                    $avgVal = decodeFloat($data, $pos, true);
                    $this->measures[] = $avgVal;
                    if(!is_nan($avgVal)) {
                        $this->measures[] = decodeFloat($data, $pos+4, false);
                        $this->measures[] = decodeFloat($data, $pos+8, false);
                    } else {
                        $this->measures[] = NAN;
                        $this->measures[] = NAN;
                    }
                }
            }
            $this->data = $data;
        }
    }

    public function storageSize(): int
    {
        if($this->frequency->perSec) {
            return DATASEQ_HEADER_SIZE + 4 * $this->nRows;
        } else {
            return DATASEQ_HEADER_SIZE + 12 * $this->nRows;
        }
    }

    public function isClosed(): bool
    {
        return !is_nan($this->avgVal);
    }

    public function getAvgMinMax(): array
    {
        if($this->isClosed()) {
            return [ $this->avgVal, $this->minVal, $this->maxVal ];
        }
        $nval = 1;
        if($this->frequency->perSec) {
            if(sizeof($this->measures) == 0) {
                // sequence not yet loaded
                $rsize = 4 * $this->nRows;
                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);
                for($pos = 0; $pos < $rsize; $pos += 4) {
                    $this->measures[] = decodeFloat($data, $pos, true);
                }
                $this->data = $data;
            }
            $value = $this->measures[0];
            $sum = $value;
            $minVal = $value;
            $maxVal = $value;
            for($i = 1; $i < $this->nRows; $i++) {
                $value = $this->measures[$i];
                if(!is_nan($value)) {
                    $nval++;
                    $sum += $value;
                    if($value < $minVal) {
                        $minVal = $value;
                    }
                    if($value > $maxVal) {
                        $maxVal = $value;
                    }
                }
            }
        } else {
            if(sizeof($this->measures) == 0) {
                // sequence not yet loaded
                $rsize = 12 * $this->nRows;
                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);
                for($pos = 0; $pos < $rsize; $pos += 12) {
                    $avgVal = decodeFloat($data, $pos, true);
                    $this->measures[] = $avgVal;
                    if(!is_nan($avgVal)) {
                        $this->measures[] = decodeFloat($data, $pos+4, false);
                        $this->measures[] = decodeFloat($data, $pos+8, false);
                    } else {
                        $this->measures[] = NAN;
                        $this->measures[] = NAN;
                    }
                }
                $this->data = $data;
            }
            $sum = $this->measures[0];
            $minVal = $this->measures[1];
            $maxVal = $this->measures[2];
            for($i = 1; $i < $this->nRows; $i++) {
                $avgVal = $this->measures[3*$i];
                if(!is_nan($avgVal)) {
                    $nval++;
                    $sum += $avgVal;
                    if($this->measures[3*$i+1] < $minVal) {
                        $minVal = $this->measures[3*$i+1];
                    }
                    if($this->measures[3*$i+2] > $maxVal) {
                        $maxVal = $this->measures[3*$i+2];
                    }
                }
            }
        }
        $avgVal = round(1000 * $sum / $nval) / 1000.0;
        return [ $avgVal, $minVal, $maxVal ];
    }

    /*
     * Close sequence, i.e. write the finale min/avg/max in the sequence header
     */
    public function closeSeq(VHubServerHTTPRequest $httpReq): void
    {
        $avgMinMax = $this->getAvgMinMax();
        VHubServer::Log($httpReq, LOG_DATALOGGER, 4, 'Closing sequence, summary: avg='.$avgMinMax[0].' min='.$avgMinMax[1].' max='.$avgMinMax[2]);
        $this->avgVal = $avgMinMax[0];
        $this->minVal = $avgMinMax[1];
        $this->maxVal = $avgMinMax[2];
        $buff = encodeFloat($this->avgVal, true).
            encodeFloat($this->minVal, false).
            encodeFloat($this->maxVal, false);
        $this->tarFile->tarWorkWrite($this->tarObj, $this->seqOfs+16, $buff);
    }

    // Return the timestamp of the last measure inserted in the sequence
    public function lastStamp(): float
    {
        if($this->frequency->perSec) {
            $endFirstRow = $this->utcStamp + ($this->firstMs + $this->firstDur) / 1000;
        } else {
            $endFirstRow = $this->utcStamp + $this->firstDur;
        }
        return $endFirstRow + ($this->nRows-1) * $this->frequency->period;
    }

    // Compute the timestamp of the next sequence to start
    public function nextSeqStartStamp(): int
    {
        if($this->nextSeqStampCache <= 0) {
            $count = $this->frequency->maxSeqRows();
            $seqPeriod = intval(round($this->frequency->period * $count));
            $this->nextSeqStampCache = $this->utcStamp - ($this->utcStamp % $seqPeriod) + $seqPeriod;
        }
        return $this->nextSeqStampCache;
    }

    // Setup the sequence given the first measure, initializing the header as required
    // (use for creating a new sequence when no header data is available yet)
    public function initialize(DataFrequency $freq, YMeasure $measure): void
    {
        $startTime = $measure->get_startTimeUTC();
        $endTime = $measure->get_endTimeUTC();
        $avgVal = $measure->get_averageValue();
        $minVal = $measure->get_minValue();
        $maxVal = $measure->get_maxValue();
        $this->runIdx = 0;
        $this->frequency = $freq;
        if($freq->perSec) {
            $this->utcStamp = intval(floor($startTime));
            $this->firstMs = intval(round(1000 * ($startTime - $this->utcStamp)));
            $this->firstDur = intval(round(1000 * $freq->period));
        } else {
            $this->utcStamp = intval(round($startTime));
            $this->firstMs = 0;
            $this->firstDur = intval(round($endTime)) - $this->utcStamp;
            if(is_nan($minVal)) $minVal = $avgVal;
            if(is_nan($maxVal)) $maxVal = $avgVal;
        }
        $this->nRows = 1;
        $this->header =
            encodeUint($this->runIdx, 4).encodeUint($this->utcStamp, 4).$freq->encoded().
            encodeUint($this->firstDur, 2).encodeUint($this->firstMs, 2).encodeUint($this->nRows, 2).
            encodeFloat($this->avgVal, true).encodeFloat($this->minVal, false).encodeFloat($this->maxVal, false);
        if($freq->perSec) {
            $this->measures[] = $avgVal;
            $this->data .= encodeFloat($avgVal, true);
        } else {
            $this->measures[] = $avgVal;
            $this->measures[] = $minVal;
            $this->measures[] = $maxVal;
            $this->data .= encodeFloat($avgVal, true).encodeFloat($minVal, false).encodeFloat($maxVal, false);
        }
    }

    // Return the raw buffer representing header for current sequence
    //
    // For compatibility with devices, leave nRows to 0xffff as long
    // as the sequence is not closed.
    //
    public function getRawHeader(): string
    {
        $res = $this->header;
        if(!$this->isClosed()) {
            $res[14] = chr(255);
            $res[15] = chr(255);
        }
        return $res;
    }

    // Return the raw buffer representing data for current sequence
    //
    public function getRawData(): string
    {
        return $this->data;
    }

    // Return the raw buffer representing header and data for current sequence
    //
    public function getRawBytes(): string
    {
        return $this->header.$this->data;
    }

    // Flush current sequence to Tar file worker (including header)
    //
    public function flush(): void
    {
        $this->tarFile->tarWorkWrite($this->tarObj, $this->seqOfs, $this->getRawBytes());
    }

    // Attempt to add a single measure to an existing sequence
    // - The sequence is expected to be open
    // - The timestamp is expected to be after current sequence start
    // - Intermediate "holes" are automatically added if needed
    // - If the new measure cannot fit in current sequence close it and return false
    // - Once the sequence is complete, it will automatically be closed
    public function appendMeasure(VHubServerHTTPRequest $httpReq, DataFrequency $freq, YMeasure $measure): bool
    {
        if($this->frequency->freqStr != $freq->freqStr) {
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Recording frequency changed from {$this->frequency->freqStr} to {$freq->freqStr}");
            $this->closeSeq($httpReq);
            return false;
        }
        $startTime = $measure->get_startTimeUTC();
        $endTime = $measure->get_endTimeUTC();
        $nextSeqStamp = $this->nextSeqStartStamp();
        if($startTime >= $nextSeqStamp) {
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Timestamp beyond sequence end ({$startTime} >= {$nextSeqStamp})");
            $this->closeSeq($httpReq);
            return false;
        }
        $prevEndTime = $this->lastStamp();
        if($startTime < $prevEndTime) {
            // duplicate data for same timestamp, probably a rounding issue: silently drop
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Timestamp {$startTime} < {$prevEndTime}, dropping measure");
            return true;
        }
        $skipRows = intval(($startTime - $prevEndTime) / $this->frequency->period + 0.001); // safe round down
        $avgVal = $measure->get_averageValue();
        $minVal = $measure->get_minValue();
        $maxVal = $measure->get_maxValue();
        if($this->frequency->perSec) {
            $data = str_repeat(chr(0xff), 4*$skipRows);
            $data .= encodeFloat($avgVal, true);
            $this->tarFile->tarWorkWrite($this->tarObj, $this->dataOfs + 4*$this->nRows, $data);
            if(sizeof($this->measures) > 0) {
                $this->data .= $data;
                for ($i = 0; $i < $skipRows; $i++) {
                    $this->measures[] = NAN;
                }
                $this->measures[] = $avgVal;
            }
        } else {
            if(is_nan($minVal)) $minVal = $avgVal;
            if(is_nan($maxVal)) $maxVal = $avgVal;
            $data = str_repeat(chr(0xff), 12*$skipRows);
            $data .= encodeFloat($avgVal, true).encodeFloat($minVal, false).encodeFloat($maxVal, false);
            $this->tarFile->tarWorkWrite($this->tarObj, $this->dataOfs + 12*$this->nRows, $data);
            if(sizeof($this->measures) > 0) {
                $this->data .= $data;
                for($i = 0; $i < 3*$skipRows; $i++) {
                    $this->measures[] = NAN;
                }
                $this->measures[] = $avgVal;
                $this->measures[] = $minVal;
                $this->measures[] = $maxVal;
            }
        }
        $this->nRows += $skipRows + 1;
        $this->tarFile->tarWorkWriteUint($this->tarObj, $this->seqOfs + 14, $this->nRows, 2);
        if($endTime >= $nextSeqStamp) {
            VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "This is the last measure of the sequence ({$endTime} >= {$nextSeqStamp})");
            $this->closeSeq($httpReq);
        }
        return true;
    }
}

class DataLogger
{
    protected VHubServer $server;
    protected FileServer $filesrv;
    protected string $serial;
    protected ?TarFile $tarfile;

    public function __construct(VHubServer $parent, string $serial)
    {
        $this->server = $parent;
        $this->filesrv = $parent->files;
        $this->serial = $serial;
        $this->tarfile = null;
    }

    public function recorderEncode(string $data): string
    {
        $nwords = strlen($data) >> 1;
        $wbuff = [];
        for($pos = 0; $pos < $nwords; $pos++) {
            $wbuff[$pos] = ord($data[2*$pos])+256*ord($data[2*$pos+1]);
        }
        $res = '';
        for($pos = 0; $pos < $nwords; $pos++) {
            $val = $wbuff[$pos];
            if($val == 0) {
                $res .= '*';
                continue;
            } else if($val == 0x7fff) {
                $res .= 'Y';
                continue;
            } else if($val == 0xffff) {
                $res .= 'X';
                continue;
            }
            for ($dist = 1; $dist <= $pos && $dist <= 30; $dist++) {
                if ($wbuff[$pos - $dist] == $val) break;
            }
            if ($dist <= $pos && $dist <= 30) {
                $res .= chr(96 + $dist);
            } else {
                $res .= chr(48 + ($val & 0x1f)); // 5 lowest bits
                $val >>= 5;
                $res .= chr(48 + ($val & 0x1f)); // 5 medium bits
                $val >>= 5;
                $val += 48;
                if ($val == 92) {
                    $res .= 'z';
                } else {
                    $res .= chr($val);
                }
            }
        }
        return $res;
    }

    protected function accessData(VHubServerHTTPRequest $httpReq, string $fnpattern = '*'): array
    {
        $this->tarfile = $this->filesrv->accessDeviceFiles($httpReq, $this->serial);
        $tarObjects = $this->tarfile->processTarFile($httpReq, 'datalogger/'.$fnpattern, TAROP_WORKON_FILES);
        usort($tarObjects, fn(TarObject $a, TarObject $b) => strcmp($a->path, $b->path));
        $res = [];
        foreach($tarObjects as $tarObj) {
            $df = new DataFile($tarObj);
            if(!isset($res[$df->functionid])) {
                $res[$df->functionid] = [ $df ];
            } else {
                $res[$df->functionid][] = $df;
            }
        }
        return $res;
    }

    protected function loadSeq(VHubServerHTTPRequest $httpReq, DataFile $dataFile, int $seqOfs, bool $withData = true): DataSeq
    {
        $dataSeq = new DataSeq($this->tarfile, $dataFile->tarObject, $seqOfs);
        $dataSeq->loadSeq($httpReq, $withData);
        return $dataSeq;
    }

    public function appendMeasures(VHubServerHTTPRequest $httpReq, array $reports): void
    {
        $dataFiles = $this->accessData($httpReq);
        $lastSeqOfs = [];
        $mustCreate = [];
        $mustAdd = [];
        foreach($reports as $functionid => $report) {
            $hardwareid = "{$this->serial}.{$functionid}";
            $freq = $report['freq'];
            $measure = $report['measure'];
            $startTime = $measure->get_startTimeUTC();
            $endTime = $measure->get_endTimeUTC();
            $value = $measure->get_averageValue();
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "@{$startTime}-{$endTime}: {$hardwareid}: {$value} {$report['unit']}");
            if($endTime < time()-(2*86400)) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is more than a 48h in the past, dropping data");
                continue;
            }
            if($endTime > time()+(2*86400)) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is more than a 48h in the future, dropping data");
                continue;
            }
            if(!isset($dataFiles[$functionid])) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "No datafile for {$hardwareid} yet, must create one");
                $mustCreate[$functionid] = $report;
                continue;
            }
            $lastFile = $dataFiles[$functionid][sizeof($dataFiles[$functionid])-1];
            if($endTime < $lastFile->startstamp) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is going back, dropping data");
                continue;
            }
            $lastSeqOfs[$functionid] = $this->tarfile->tarWorkReadUint($lastFile->tarObject, 0, 4);
            $dataSeq = $this->loadSeq($httpReq, $lastFile, $lastSeqOfs[$functionid], false);
            if($dataSeq->isClosed()) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Current sequence for {$hardwareid} is closed, opening a new sequence");
                $mustAdd[$functionid] = $report;
                continue;
            }
            if($startTime < $dataSeq->lastStamp()) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$startTime} for {$hardwareid} is going back, dropping data");
                continue;
            }
            if(!$dataSeq->appendMeasure($httpReq, $freq, $measure)) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Could not add measure to current sequence {$hardwareid}, opening a new sequence");
                $mustAdd[$functionid] = $report;
                continue;
            }
        }
        foreach($mustAdd as $functionid => $report) {
            $hardwareid = "{$this->serial}.{$functionid}";
            $lastFile = $dataFiles[$functionid][sizeof($dataFiles[$functionid])-1];
            $seqOfs = $lastSeqOfs[$functionid];
            $dataSeq = $this->loadSeq($httpReq, $lastFile, $seqOfs, false);
            $seqOfs += $dataSeq->storageSize();
            // ensure we have space for one more sequence
            $seqMaxSize = $freq->maxSeqRows() * 4;
            if(!$freq->perSec) $seqMaxSize *= 3;
            $seqMaxSize += DATASEQ_HEADER_SIZE;
            if($seqOfs + $seqMaxSize > DATAFILE_MAX_SIZE) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Datafile for {$hardwareid} is full, must create new file");
                $mustCreate[$functionid] = $report;
                continue;
            }
            // create new data sequence in file
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "New datastream for {$hardwareid}");
            $dataSeq = new DataSeq($this->tarfile, $lastFile->tarObject, $seqOfs);
            $dataSeq->initialize($freq, $report['measure']);
            $dataSeq->flush();
            // update position of last sequence position at start of file
            $this->tarfile->tarWorkWriteUint($lastFile->tarObject, 0, $seqOfs, 4);
        }
        // Release tar file anyway to allow exclusive read/write mode for adding a file
        $this->tarfile->tarWorkDone($httpReq);
        if(sizeof($mustCreate) == 0) {
            return;
        }
        // Add missing data files in tar archive
        $datapad = str_repeat(chr(255), DATAFILE_MAX_SIZE);
        foreach($mustCreate as $functionid => $report) {
            $measure = $report['measure'];
            $utcstamp = intval(round($measure->get_startTimeUTC()));
            $prefix = 'datalogger/'.$utcstamp.'-';
            $cleanUnit = str_replace('/', '_', $report['unit']);
            $suffix = '-'.date('Y-m-d', $utcstamp).'.bin';
            $subfile = $prefix.$functionid.'-'.$cleanUnit.$suffix;
            $seqOfs = 4;
            $dataSeq = new DataSeq($this->tarfile, null, $seqOfs);
            $dataSeq->initialize($freq, $measure);
            $content = encodeUint($seqOfs, 4).$dataSeq->getRawBytes();
            $content .= substr($datapad, strlen($content));
            VHubServer::Log($httpReq, LOG_DATALOGGER, 4, "Creating {$subfile} for {$this->serial}");
            $this->tarfile->processTarFile($httpReq, $subfile, TAROP_UPDATE_FILE, $content);
        }
    }

    public function printIndex(VHubServerHTTPRequest $httpReq, APISensorNode $sensorNode, string $functionid, string $runmatch, int $fromStamp, int $toStamp, bool $verbose): void
    {
        $unit = $sensorNode->getattr('unit');
        $calib = $sensorNode->getattr('calibrationParam');
        $httpReq->put('{"id":"'.$functionid.'","unit":"'.$unit.'","calib":"'.$calib.'","cal":"*","bulk":"128","streams":'."[\n");
        $sep = '';
        $dataFiles = $this->accessData($httpReq, '*-'.$functionid.'-*');
        if(isset($dataFiles[$functionid])) {
            $functionFiles = $dataFiles[$functionid];
        } else {
            $functionFiles = [];
        }
        VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Found ".sizeof($functionFiles). " file matching functionId $functionid");
        for($i = 0; $i < sizeof($functionFiles); $i++) {
            // filter out files not relevant for the requested period and unit
            $dataFile = $functionFiles[$i];
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check unit");
            $cleanUnit = str_replace('/', '_', $unit);
            if($dataFile->unit != $cleanUnit) continue;
            if($i+1 < sizeof($functionFiles)) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check start timestamp");
                if($functionFiles[$i+1]->startstamp <= $fromStamp) continue;
            }
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check end timestamp");
            if($dataFile->startstamp > $toStamp) break;
            if($i+1 < sizeof($functionFiles)) {
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check next timestamp");
                $nextFile = $functionFiles[$i+1];
                if($nextFile->startstamp <= $fromStamp) {
                    continue;
                }
            }
            // data file might contain data for the requested period
            $lastSeqOfs = $this->tarfile->tarWorkReadUint($dataFile->tarObject, 0, 4);
            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Last sequence at offset $lastSeqOfs");
            for($seqOfs = 4; $seqOfs <= $lastSeqOfs; ) {
                $dataSeq = $this->loadSeq($httpReq, $dataFile, $seqOfs, false);
                $duration = intVal(round($dataSeq->nRows * $dataSeq->frequency->period));
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Sequence at $seqOfs start stamp: ".$dataSeq->utcStamp);
                if($dataSeq->utcStamp >= $toStamp) break;
                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Sequence at $seqOfs end stamp: ".($dataSeq->utcStamp+$duration));
                if($dataSeq->utcStamp+$duration > $fromStamp &&
                    ($runmatch == '' || intval($runmatch) == $dataSeq->runIdx)) {
                    if ($verbose) {
                        $avgMinMax = $dataSeq->getAvgMinMax();
                        $avgVal = $avgMinMax[0];
                        $minVal = $avgMinMax[1];
                        $maxVal = $avgMinMax[2];
                        $httpReq->put($sep . '{"run":' . $dataSeq->runIdx . ',"utc":' . $dataSeq->utcStamp . ',"dur":' . $duration .
                            ',"freq":"' . $dataSeq->frequency->freqStr . '","val":[' . $minVal . ',' . $avgVal . ',' . $maxVal . ']}' . "\n");
                    } else {
                        $httpReq->put($sep . '"' . $this->recorderEncode($dataSeq->getRawHeader()) . '"' . "\n");
                    }
                    $sep = ',';
                }
                $seqOfs += $dataSeq->storageSize();
            }
        }
        $this->tarfile->tarWorkDone($httpReq);
        $httpReq->put("]}");
    }

    public function printRun(VHubServerHTTPRequest $httpReq, string $functionid, string $runmatch, array $utcStamps, bool $verbose): void
    {
        $dataFiles = $this->accessData($httpReq, '*-'.$functionid.'-*');
        if(isset($dataFiles[$functionid])) {
            $functionFiles = $dataFiles[$functionid];
        } else {
            $functionFiles = [];
        }
        $isFirst = true;
        $minStamp = min($utcStamps);
        $stampIdx = 0;
        for($fi = 0; $fi < sizeof($functionFiles); $fi++) {
            // filter out files not relevant for the requested period and unit
            $dataFile = $functionFiles[$fi];
            if($fi+1 < sizeof($functionFiles)) {
                $nextFile = $functionFiles[$fi+1];
                if($nextFile->startstamp <= $minStamp) {
                    continue;
                }
            }
            // data file might contain data for the requested period
            $lastSeqOfs = $this->tarfile->tarWorkReadUint($dataFile->tarObject, 0, 4);
            for($seqOfs = 4; $seqOfs <= $lastSeqOfs; ) {
                $dataSeq = $this->loadSeq($httpReq, $dataFile, $seqOfs, false);
                $duration = intVal(round($dataSeq->nRows * $dataSeq->frequency->period));
                if($dataSeq->utcStamp == $utcStamps[$stampIdx] &&
                    ($runmatch == '' || intval($runmatch) == $dataSeq->runIdx)) {
                    VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Using sequence starting at {$dataSeq->utcStamp}, {$dataSeq->nRows} rows, {$duration}s");
                    $dataSeq->loadSeq($httpReq, true);
                    if ($verbose) {
                        $httpReq->put($isFirst ? '[' : ",\n[");
                        $sep = '';
                        $measures = $dataSeq->measures;
                        if($dataSeq->frequency->perSec) {
                            for($i = 0; $i < sizeof($measures); $i++) {
                                $httpReq->put($sep.$measures[$i]."\n");
                                $sep = ',';
                            }
                        } else {
                            for($i = 0; $i+2 < sizeof($measures); $i += 3) {
                                $httpReq->put($sep."[".$measures[$i+1].','.$measures[$i].','.$measures[$i+2]."]\n");
                                $sep = ',';
                            }
                        }
                        $httpReq->put(']');
                    } else {
                        $httpReq->put(($isFirst ? '"' : "\n,\"") . $this->recorderEncode($dataSeq->getRawData()) . '"');
                    }
                    $isFirst = false;
                    $stampIdx++;
                    if($stampIdx >= sizeof($utcStamps)) {
                        // exit outside loop
                        $fi = sizeof($functionFiles);
                        break;
                    }
                }
                $seqOfs += $dataSeq->storageSize();
            }
        }
        $this->tarfile->tarWorkDone($httpReq);
    }
}