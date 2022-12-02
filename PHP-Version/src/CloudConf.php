<?php
/*********************************************************************
 *
 * $Id: CloudConf.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * VirtualHub4web configuration objects
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

class CloudConf
{
    public function __construct()
    {
    }

    function loadState(VHubServerHTTPRequest $httpReq, object $data): void
    {
    }

    function saveState(): array
    {
        return [];
    }
}

class DeviceCloudConf extends CloudConf
{
    public string $parentHub;
    public string $parentIP;
    public int $lastSeen;
    public int $reconnect;
    public int $logPos;
    public int $tRepPos;
    public string $yfsVer;

    public function __construct()
    {
        parent::__construct();
        $this->parentHub = '';
        $this->parentIP = '';
        $this->lastSeen = 0;
        $this->reconnect = 0;
        $this->logPos = 0;
        $this->tRepPos = 0;
        $this->yfsVer = '';
    }

    function loadState($httpReq, object $data): void
    {
        parent::loadState($httpReq, $data);
        if(isset($data->parentHub)) {
            $this->parentHub = $data->parentHub;
            $this->parentIP = $data->parentIP;
            $this->lastSeen = $data->lastSeen;
        }
        if(isset($data->reconnect)) {
            $this->reconnect = $data->reconnect;
        }
        $this->logPos = $data->logPos;
        if(isset($data->tRepPos)) {
            $this->tRepPos = $data->tRepPos;
        }
        if(isset($data->yfsVer)) {
            $this->yfsVer = $data->yfsVer;
        }
    }

    function deviceResetDetected()
    {
        $this->logPos = 0;
        $this->tRepPos = 0;
    }

    function saveState(): array
    {
        $res = parent::saveState();
        $res['parentHub'] = $this->parentHub;
        $res['parentIP'] = $this->parentIP;
        $res['lastSeen'] = $this->lastSeen;
        $res['reconnect'] = $this->reconnect;
        $res['logPos'] = $this->logPos;
        if($this->tRepPos != 0) {
            $res['tRepPos'] = $this->tRepPos;
        }
        $res['yfsVer'] = $this->yfsVer;
        return $res;
    }
}

class GlobalCloudConf extends CloudConf
{
    // The serial number is initialized randomly only, then preserved
    public string $serialNumber;
    // The authentication realm is initialized to the serial number, then preserved.
    // Passwords must be reset if the realm is changed manually.
    public string $authRealm;
    // Incoming HTTP callback MD5 signature password.
    public string $md5signPwd;
    // Settings saved explicitely
    public array $savedSettings;
    // Current attribute values
    public array $valuesCache;

    // Additional state variables to be saved, to be used through accessor functions
    protected array $devYdxBySerial;

    const PARENT_DEVYDX = 10000;

    public function __construct()
    {
        parent::__construct();
        $this->serialNumber = 'VHUB4WEB-'.dechex(mt_rand(0x1000000,0xfffffff));
        $this->authRealm = $this->serialNumber;
        $this->md5signPwd = '';
        $this->savedSettings = [
            'logicalName' => '',
            'networkName' => '',
            'filesName' => '',
            'luminosity' => 0,
            'defaultPage' => '',
            'userPassword' => '',
            'adminPassword' => ''
        ];
        $this->valuesCache = array_merge($this->savedSettings);
        $this->devYdxBySerial = [];
    }

    public function loadState(VHubServerHTTPRequest $httpReq, object $data): void
    {
        parent::loadState($httpReq, $data);
        $this->serialNumber = $data->serialNumber;
        $this->authRealm = $data->authRealm;
        if(isset($data->md5signPwd)) {
            $this->md5signPwd = $data->md5signPwd;
        }
        foreach($data->savedSettings as $name => $value) {
            $this->savedSettings[$name] = $value;
            // default current value to saved setting
            $this->valuesCache[$name] = $value;
        }
        foreach($data->valuesCache as $name => $value) {
            $this->valuesCache[$name] = $value;
        }
        foreach($data->devYdxBySerial as $serial => $devydx) {
            $this->devYdxBySerial[$serial] = $devydx;
        }
    }

    // Allocate a new the devYdx for a given serial number, and bind it to the parent devYdx
    // Return the newly allocated devYdx
    // If no more devYdx is available (> 255 devices), return -1;
    //
    public function allocDevYdx(string $serial, int $parentDevYdx): int
    {
        $usedDevYdx = [];
        foreach($this->devYdxBySerial as $devYdx) {
            $usedDevYdx[$devYdx % GlobalCloudConf::PARENT_DEVYDX] = true;
        }
        for($devYdx = 1; $devYdx < 256; $devYdx++) {
            if(!isset($usedDevYdx[$devYdx])) {
                $this->devYdxBySerial[$serial] = $devYdx + GlobalCloudConf::PARENT_DEVYDX * $parentDevYdx;
                return $devYdx;
            }
        }
        return -1;
    }

    // Return the devYdx for a given serial number, or -1 if device is unknown
    //
    public function getDevYdx(string $serial): int
    {
        if(!isset($this->devYdxBySerial[$serial])) {
            return -1;
        }
        return $this->devYdxBySerial[$serial] % GlobalCloudConf::PARENT_DEVYDX;
    }

    // Return the parent device devYdx for a device given by its serialNumber
    //
    public function getParentDevYdx(string $serial): int
    {
        if(!isset($this->devYdxBySerial[$serial])) {
            return 0;   // VirtualHub-4web own devYdx
        }
        return intdiv($this->devYdxBySerial[$serial], GlobalCloudConf::PARENT_DEVYDX);
    }

    // Sets the parent devYdx only for a device given by serialNumber
    //
    public function setParentDevYdx(string $serial, int $parentDevYdx): void
    {
        if(!isset($this->devYdxBySerial[$serial])) {
            return; // should never happen, but not that bad anyway
        }
        $devYdx = $this->getDevYdx($serial);
        $this->devYdxBySerial[$serial] = $devYdx + GlobalCloudConf::PARENT_DEVYDX * $parentDevYdx;
    }

    // Free a given devYdx when forgetting a device
    //
    public function freeDevYdx(string $serial): void
    {
        unset($this->devYdxBySerial[$serial]);
    }

    public function saveState(): array
    {
        $res = parent::saveState();
        $res['serialNumber'] = $this->serialNumber;
        $res['authRealm'] = $this->authRealm;
        $res['md5signPwd'] = $this->md5signPwd;
        $res['savedSettings'] = $this->savedSettings;
        $res['valuesCache'] = $this->valuesCache;
        $res['devYdxBySerial'] = $this->devYdxBySerial;
        return $res;
    }

    // Save current API settings to persistent zone
    public function saveSettings()
    {
        foreach($this->savedSettings as $name => $value) {
            $this->savedSettings[$name] = $this->valuesCache[$name];
        }
    }

    // Revert current API settings to saved values
    public function revertSettings()
    {
        foreach($this->savedSettings as $name => $value) {
            $this->valuesCache[$name] = $value;
        }
    }
}

class DailyStats
{
    protected int $divisor;
    protected int $color;
    protected array $byCallback;
    protected array $byDayMin;
    protected array $byDayVal;
    protected array $byDayMax;
    protected int $prevDayStamp;
    protected int $prevDayCount;
    protected int $prevDaySum;
    protected int $prevDayMin;
    protected int $prevDayMax;

    public function __construct(int $divisor, int $color)
    {
        $this->divisor = $divisor;
        $this->color = $color;
        $this->byCallback = [];
        $this->byDayMin = [];
        $this->byDayVal = [];
        $this->byDayMax = [];
        $this->prevDayStamp = 0;
        $this->prevDayCount = 0;
        $this->prevDaySum = 0;
        $this->prevDayMin = 0;
        $this->prevDayMax = 0;
    }

    function loadState(VHubServerHTTPRequest $httpReq, object $data): void
    {
        $this->byCallback = $data->byCallback;
        $this->byDayVal = $data->byDayVal;
        $this->byDayMin = $data->byDayMin;
        $this->byDayMax = $data->byDayMax;
        $this->prevDayStamp = $data->prevDayStamp;
        $this->prevDayCount = $data->prevDayCount;
        $this->prevDaySum = $data->prevDaySum;
        $this->prevDayMin = $data->prevDayMin;
        $this->prevDayMax = $data->prevDayMax;
    }

    function appendVal(VHubServerHTTPRequest $httpReq, int $timeStamp, int $val): void
    {
        // Save per-callback information
        $this->byCallback[] = $val;
        if(sizeof($this->byCallback) > DEVICESTATS_MAX_CONN) {
            array_splice($this->byCallback, 0, sizeof($this->byCallback) - DEVICESTATS_MAX_CONN);
        }

        // Save per-day information
        $dayStamp = $timeStamp - ($timeStamp % 86400) + 43200;
        if($this->prevDayStamp != $dayStamp) {
            if($this->prevDayStamp != 0) {
                if ($this->prevDayCount > 0) {
                    $divisor = ($this->divisor > 0 ? $this->divisor : $this->prevDayCount);
                    $this->byDayVal[] = intval(round($this->prevDaySum / $divisor));
                    $this->byDayMin[] = $this->prevDayMin;
                    $this->byDayMax[] = $this->prevDayMax;
                }
                $dayInterval = intdiv($dayStamp - $this->prevDayStamp, 86400);
                while ($dayInterval > 1) {
                    $this->byDayVal[] = 0;
                    $this->byDayMin[] = 0;
                    $this->byDayMax[] = 0;
                    $dayInterval--;
                }
                if(sizeof($this->byDayVal) > DEVICESTATS_MAX_DAYS) {
                    array_splice($this->byDayVal, 0, sizeof($this->byDayVal) - DEVICESTATS_MAX_DAYS);
                    array_splice($this->byDayMin, 0, sizeof($this->byDayMin) - DEVICESTATS_MAX_DAYS);
                    array_splice($this->byDayMax, 0, sizeof($this->byDayMax) - DEVICESTATS_MAX_DAYS);
                }
            }
            $this->prevDayStamp = $dayStamp;
            $this->prevDayCount = 1;
            $this->prevDaySum = $val;
            $this->prevDayMin = $val;
            $this->prevDayMax = $val;
        } else {
            $this->prevDayCount++;
            $this->prevDaySum += $val;
            $this->prevDayMin = min($this->prevDayMin, $val);
            $this->prevDayMax = max($this->prevDayMax, $val);
        }
    }

    function saveState(): array
    {
        $res = [];
        $res['byCallback'] = $this->byCallback;
        $res['byDayMin'] = $this->byDayMin;
        $res['byDayVal'] = $this->byDayVal;
        $res['byDayMax'] = $this->byDayMax;
        $res['dayValDivisor'] = $this->divisor;
        $res['prevDayStamp'] = $this->prevDayStamp;
        $res['prevDayCount'] = $this->prevDayCount;
        $res['prevDaySum'] = $this->prevDaySum;
        $res['prevDayMin'] = $this->prevDayMin;
        $res['prevDayMax'] = $this->prevDayMax;
        $res['defaultColor'] = $this->color;
        return $res;
    }
}

class DeviceStats
{
    protected int $prevTimestamp;
    protected bool $modified;
    protected array $stats; // actually a YearlyStats[]

    public function __construct()
    {
        $this->prevTimestamp = 0;
        $this->modified = false;
        $this->stats = [
            'callbackInterval_s' => new DailyStats(0, 0x8b4513),
            'sensorBufferUsage_percent' => new DailyStats(0, 0x7f007f),
            'errors_count' => new DailyStats(1, 0xdf0000),
            'warnings_count' => new DailyStats(1, 0xdf5f00),
            'devices_count' => new DailyStats(1, 0x5f5f5f),
            'resets_count' => new DailyStats(1, 0xe5b718),
            'callbackIOReadTime_ms' => new DailyStats(0, 0x00006f),
            'callbackProcessingTime_ms' => new DailyStats(0, 0x0000cf),
            'dataReceived_bytes_kb' => new DailyStats(1024, 0x005f00),
            'dataSent_bytes_kb' => new DailyStats(1024, 0x008f00)
        ];
    }

    function loadState(VHubServerHTTPRequest $httpReq, object $data): void
    {
        $this->prevTimestamp = $data->prevTimestamp;
        foreach($data as $key => $stats) {
            if(isset($this->stats[$key]) && isset($stats->prevDayStamp)) {
                $this->stats[$key]->loadState($httpReq, $stats);
            }
        }
        $this->modified = false;
    }

    function appendStats(VHubServerHTTPRequest $httpReq, int $sensorBufferUsage, int $nDevice, int $nReset): void
    {
        $now = $httpReq->getRequestTimestamp();
        $interval = ($this->prevTimestamp == 0 ? 0 : $now - $this->prevTimestamp);
        $this->stats['callbackInterval_s']->appendVal($httpReq, $now, $interval);
        $this->stats['sensorBufferUsage_percent']->appendVal($httpReq, $now, $sensorBufferUsage);
        $this->stats['errors_count']->appendVal($httpReq, $now, $httpReq->getErrorCount());
        $this->stats['warnings_count']->appendVal($httpReq, $now, $httpReq->getWarningCount());
        $this->stats['devices_count']->appendVal($httpReq, $now, $nDevice);
        $this->stats['resets_count']->appendVal($httpReq, $now, $nReset);
        $this->stats['callbackIOReadTime_ms']->appendVal($httpReq, $now, $httpReq->getIOReadTime());
        $this->stats['callbackProcessingTime_ms']->appendVal($httpReq, $now, $httpReq->getProcessingTime());
        $this->stats['dataReceived_bytes_kb']->appendVal($httpReq, $now, $httpReq->getDataReceived());
        $this->stats['dataSent_bytes_kb']->appendVal($httpReq, $now, $httpReq->getDataSent());
        $this->prevTimestamp = $now;
        $this->changed = true;
    }

    public function hasChanged(): bool
    {
        return $this->modified;
    }

    function saveState(): array
    {
        $res = [ 'prevTimestamp' => $this->prevTimestamp ];
        foreach($this->stats as $key => $stats) {
            $res[$key] = $stats->saveState();
        }
        return $res;
    }
}
