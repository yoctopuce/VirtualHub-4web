<?php
/*********************************************************************
 *
 * $Id: APINode.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * API node model support classes
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

include_once("API.php");
include_once("CloudConf.php");

class APINode
{
    protected VHubServer $server;
    public string $name;
    protected array $subnodes;
    protected array $values;    // immediate properties
    protected array $types;     // immediate properties type, for edition
    protected bool $modified;   // true if the node (or subnode) state needs to be saved

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        for($fclasslen = strlen($name); $fclasslen > 0; $fclasslen--) {
            if(!ctype_digit($name[$fclasslen-1])) break;
        }
        $this->server = $server;
        $this->name = $name;
        $this->fclass = substr(ucfirst($name), 0, $fclasslen);
        $this->subnodes = [];   // Associative array
        $this->values = [];     // Associative array
        $this->types = [];      // Associative array
        $this->modified = false;
    }

    protected function setupTypes(VHubServerHTTPRequest $httpReq): void
    {
        foreach($this->values as $name => $value) {
            if(!isset($this->types[$name])) {
                $this->types[$name] = $this->server->apiroot->getAttrType($httpReq, $this->fclass, $name, $value);
            }
        }
    }

    public function addSubnode(string $name, APINode $subnode): void
    {
        $this->subnodes[$name] = $subnode;
    }

    public function hasSubnode(string $name): bool
    {
        return isset($this->subnodes[$name]);
    }

    public function subnodeNames(): array
    {
        return array_keys($this->subnodes);
    }

    public function subnode(string $name): APINode
    {
        return $this->subnodes[$name];
    }

    public function getattr(string $name): mixed
    {
        return $this->values[$name];
    }

    public function setattr(string $name, string $value): void
    {
        if(!isset($this->types[$name])) {
            // unknown attribute, assume read-only
            return;
        }
        $attrtype = $this->types[$name];
        if($attrtype >= 0) {
            // read-only attribute
            return;
        }
        $this->values[$name] = ApiRestDecodeAttribute($attrtype, $value);
    }

    public function search(array $nodepath, array $ctxpath): array
    {
        $apinode = $this;
        for($offset = 0; $offset < sizeof($nodepath); $offset++) {
            $key = $nodepath[$offset];
            if(isset($apinode->subnodes[$key])) {
                $apinode = $apinode->subnodes[$key];
            } else if(sizeof($ctxpath) == 0 && sizeof($apinode->values) > 0) {
                if(isset($apinode->values[$key])) {
                    return [ $apinode, $apinode, $key ];
                } else {
                    return [ $apinode, $apinode, null ];
                }
            } else {
                return [ null, null, null ];
            }
        }
        $ctxnode = $apinode;
        for($offset = 0; $offset < sizeof($ctxpath); $offset++) {
            $key = $ctxpath[$offset];
            if(isset($ctxnode->subnodes[$key])) {
                $ctxnode = $ctxnode->subnodes[$key];
            } else if(sizeof($ctxnode->values) > 0) {
                if(isset($ctxnode->values[$key])) {
                    return [ $apinode, $ctxnode, $key ];
                } else {
                    return [ $apinode, $ctxnode, null ];
                }
            } else {
                return [ $apinode, null, null ];
            }
        }
        return [ $apinode, $ctxnode, null ];
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $data, bool $detectChanges): bool
    {
        foreach($data as $name => $value) {
            if($name == 'VirtualHub4web' || $name == 'FileList') {
                // VirtualHub4web own data is handled separately, just ignore here
            } else if((is_object($value) || is_array($value)) && !isset($this->values['advertisedValue'])) {
                if (!isset($this->subnodes[$name])) {
                    // Automatically instantiate typed dynamic nodes
                    switch($name) {
                        case 'dataLogger':
                            $this->subnodes[$name] = new APIDataLoggerNode($httpReq, $this->server, $name);
                            break;
                        case 'services':
                            $this->subnodes[$name] = new APIServicesNode($httpReq, $this->server, $name);
                            break;
                        default:
                            if(is_object($value) && isset($value->reportFrequency)) {
                                $this->subnodes[$name] = new APISensorNode($httpReq, $this->server, $name);
                            } else {
                                $this->subnodes[$name] = new APINode($httpReq, $this->server, $name);
                            }
                    }
                    if($detectChanges) $this->modified = true;
                }
                $subres = $this->subnodes[$name]->loadState($httpReq, $value, $detectChanges);
                if($detectChanges && $subres) $this->modified = true;
            } else {
                if(!isset($this->types[$name])) {
                    if($detectChanges) $this->modified = true;
                    $this->types[$name] = $this->server->apiroot->getAttrType($httpReq, $this->fclass, $name, $value);
                    $decoded = ApiJsonDecodeAttribute($value, $this->types[$name]);
                    $this->values[$name] = $decoded;
                } else {
                    $decoded = ApiJsonDecodeAttribute($value, $this->types[$name]);
                    if($this->values[$name] != $decoded) {
                        if($detectChanges) $this->modified = true;
                        $this->values[$name] = $decoded;
                    }
                }
            }
        }
        return $this->modified;
    }

    public function hasChanged(): bool
    {
        return $this->modified;
    }

    public function saveState(): array
    {
        $res = [];
        foreach($this->subnodes as $name => $subnode) {
            $res[$name] = $subnode->saveState();
        }
        foreach($this->values as $name => $value) {
            $pseudoHttpReq = new VHubServerHTTPRequest(true);
            $pseudoHttpReq->setAuthUser('admin');
            $res[$name] = ApiJsonEncodeAttribute($pseudoHttpReq, $value, $this->types[$name]);
        }
        $this->modified = false;
        return $res;
    }

    public function printJSON(VHubServerHTTPRequest $httpReq): void
    {
        $isleaf = sizeof($this->values) > 0;
        $sep = '{';
        if($isleaf) {
            foreach($this->values as $key => $value) {
                $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);
                $httpReq->put("{$sep}\"{$key}\":".json_encode($jsonval, JSON_UNESCAPED_SLASHES));
                $sep = ',';
            }
        } else {
            if(sizeof($this->subnodes) == 0) {
                $httpReq->put('{}');
                return;
            }
            foreach($this->subnodes as $name => $subnode) {
                $httpReq->put("{$sep}\"{$name}\":");
                $subnode->printJSON($httpReq);
                $sep = ',';
            }
        }
        $httpReq->put('}');
    }

    public function printJZON(VHubServerHTTPRequest $httpReq): void
    {
        $isleaf = sizeof($this->values) > 0;
        $sep = '[';
        if($isleaf) {
            foreach($this->values as $key => $value) {
                $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);
                $httpReq->put($sep.json_encode($jsonval, JSON_UNESCAPED_SLASHES ));
                $sep = ',';
            }
        } else {
            if(sizeof($this->subnodes) == 0) {
                $httpReq->put('[]');
                return;
            }
            foreach($this->subnodes as $subnode) {
                $httpReq->put($sep);
                $subnode->printJZON($httpReq);
                $sep = ',';
            }
        }
        $httpReq->put(']');
    }

    public function printJSONValue(VHubServerHTTPRequest $httpReq, string $key): void
    {
        $value = $this->values[$key];
        $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);
        $httpReq->put(json_encode($jsonval, JSON_UNESCAPED_SLASHES));
    }

    public function printHTML(VHubServerHTTPRequest $httpReq, string $label): void
    {
        $isleaf = sizeof($this->values) > 0;
        $cssclass = ($isleaf ? "interface" : "folder");
        $httpReq->put("<dl name='{$label}' class='{$cssclass}'><h4>{$label} <a href='javascript:reload()'>refresh</a></h4>\n");
        if($isleaf) {
            foreach($this->values as $key => $value) {
                $attrtype = $this->types[$key];
                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
                if($key == 'networkUrl') {
                    $relUrl = substr($txtval, 1);
                    $txtval = "<a href='{$relUrl}'>Browse REST API</a>";
                }
                $httpReq->put("<div name='{$key}'><dt>{$key}:</dt><dd>{$txtval}</dd>");
                if($attrtype < 0) {
                    $attrtype = abs($attrtype);
                    $httpReq->put("<a href='javascript:' onclick='edit(this,{$attrtype})'>edit</a></div>\n");
                } else {
                    $httpReq->put('</div>');
                }
            }
        } else {
            foreach($this->subnodes as $name => $subnode) {
                $subnode->printHTML($httpReq, $name);
            }
        }
        $httpReq->put("</dl>");
    }

    public function printHTMLValue(VHubServerHTTPRequest $httpReq, string $key): void
    {
        $value = $this->values[$key];
        $attrtype = $this->types[$key];
        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
        $httpReq->put($txtval);
    }

    public function printTXT(VHubServerHTTPRequest $httpReq, string $label): void
    {
        $isleaf = sizeof($this->values) > 0;
        $httpReq->put("*** {$label}\r\n");
        if($isleaf) {
            foreach($this->values as $key => $value) {
                $attrtype = $this->types[$key];
                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
                if(is_string($value)) {
                    $txtval = "\"{$txtval}\"";
                }
                $httpReq->put("{$key}: {$txtval}\r\n");
            }
        } else {
            foreach($this->subnodes as $name => $subnode) {
                $httpReq->put("=> {$name}\r\n");
            }
            foreach($this->subnodes as $name => $subnode) {
                $httpReq->put("\r\n");
                $subnode->printTXT($httpReq, $name);
            }
        }
    }

    public function printTXTValue(VHubServerHTTPRequest $httpReq, string $key): void
    {
        $value = $this->values[$key];
        $attrtype = $this->types[$key];
        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
        if(is_string($value)) {
            $txtval = "\"{$txtval}\"";
        }
        $httpReq->put($txtval);
    }

    public function printXML(VHubServerHTTPRequest $httpReq, string $label): void
    {
        $isleaf = sizeof($this->values) > 0;
        $httpReq->put("<{$label}>\r\n");
        if($isleaf) {
            foreach($this->values as $key => $value) {
                $attrtype = $this->types[$key];
                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
                $httpReq->put("<{$key}>{$txtval}</{$key}>\r\n");
            }
        } else {
            foreach($this->subnodes as $name => $subnode) {
                $subnode->printXML($httpReq, $name);
            }
        }
        $httpReq->put("</{$label}>\r\n");
    }

    public function printXMLValue(VHubServerHTTPRequest $httpReq, string $key): void
    {
        $value = $this->values[$key];
        $attrtype = $this->types[$key];
        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);
        $httpReq->put($txtval);
    }

    public function isSensor(): bool
    {
        return false;
    }
}

class APIArrayNode extends APINode
{
    public function saveState(): array
    {
        $res = [];
        foreach($this->subnodes as $subnode) {
            $res[] = $subnode->saveState();
        }
        $this->modified = false;
        return $res;
    }

    public function printJSON(VHubServerHTTPRequest $httpReq): void
    {
        if(sizeof($this->subnodes) == 0) {
            $httpReq->put('[]');
            return;
        }
        $sep = '[';
        foreach($this->subnodes as $subnode) {
            $httpReq->put($sep);
            $subnode->printJSON($httpReq);
            $sep = ',';
        }
        $httpReq->put(']');
    }

    public function printJZON(VHubServerHTTPRequest $httpReq): void
    {
        if(sizeof($this->subnodes) == 0) {
            $httpReq->put('[]');
            return;
        }
        $sep = '[';
        foreach($this->subnodes as $name => $subnode) {
            $httpReq->put($sep);
            $subnode->printJZON($httpReq);
            $sep = ',';
        }
        $httpReq->put(']');
    }

    public function printHTML(VHubServerHTTPRequest $httpReq, string $label): void
    {
        $httpReq->put("<dl name='{$label}' class='folder'><h4>{$label} <a href='javascript:reload()'>refresh</a></h4>\n");
        foreach($this->subnodes as $index => $subnode) {
            $subnode->printHTML($httpReq, "entry #{$index}");
        }
    }

    public function printTXT(VHubServerHTTPRequest $httpReq, string $label): void
    {
        foreach($this->subnodes as $index => $subnode) {
            $subnode->printTXT($httpReq, "{$label}[{$index}]");
        }
    }

    public function printXML(VHubServerHTTPRequest $httpReq, string $label): void
    {
        if($label == 'whitePages') {
            $sublabel = 'whitePage';
        } else {
            $sublabel = 'ypEntry';
        }
        $httpReq->put("<{$label}>\r\n");
        foreach($this->subnodes as $subnode) {
            $subnode->printXML($httpReq, $sublabel);
        }
        $httpReq->put("</{$label}>\r\n");
    }
}

class APIModuleNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->values['productName'] = '';
        $this->values['serialNumber'] = '';
        $this->values['logicalName'] = '';
        $this->values['productId'] = 0;
        $this->values['productRelease'] = 0;
        $this->values['firmwareRelease'] = '';
        $this->values['persistentSettings'] = 0;
        $this->values['luminosity'] = 0;
        $this->values['beacon'] = 0;
        $this->values['upTime'] = 0;
        $this->values['usbCurrent'] = 0;
        $this->values['rebootCountdown'] = 0;
        $this->values['userVar'] = 0;
        $this->setupTypes($httpReq);
    }
}

class APIDeviceModuleNode extends APIModuleNode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        global $ApiDef;

        parent::__construct($httpReq, $server, $name);
        $this->values['lastSeen'] = 0;
        $this->values['parentHub'] = '';
        $this->values['parentIP'] = '';
        $this->types['lastSeen'] = $ApiDef['Watchdog']['lastTrigger'];
        $this->types['parentHub'] = $ApiDef['Module']['serialNumber'];
        $this->types['parentIP'] = $ApiDef['Network']['ipAddress'];
    }
}

class APICloudModuleNode extends APIModuleNode
{
    protected array $cachedAttributes;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->cachedAttributes = [ 'logicalName', 'luminosity', 'beacon', 'userVar', 'persistentSettings' ];
        $this->values['productName'] = 'VirtualHub-4web';
        $this->values['productId'] = 0xc10d;
        $this->values['productRelease'] = 1;
        $this->values['upTime'] = round(gettimeofday(true) * 1000.0) & 0xffffffff;
        $versionDotPos = strrpos(VERSION, '.');
        if($versionDotPos !== FALSE) {
            $this->values['firmwareRelease'] = substr(VERSION, $versionDotPos+1);
        } else {
            $this->values['firmwareRelease'] = VERSION;
        }
    }

    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)
    {
        $this->values['serialNumber'] = $cloudConf->serialNumber;
        foreach($this->cachedAttributes as $key) {
            if(isset($cloudConf->valuesCache[$key])) {
                $this->values[$key] = $cloudConf->valuesCache[$key];
            }
        }
    }

    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)
    {
        foreach($this->cachedAttributes as $key) {
            if(isset($cloudConf->valuesCache[$key]) && $cloudConf->valuesCache[$key] != $this->values[$key]) {
                $changes[$key] = $this->values[$key];
            }
        }
    }
}

class APIFunctionNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->values['logicalName'] = '';
        $this->values['advertisedValue'] = '';
        $this->setupTypes($httpReq);
    }
}

class APISensorNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->values['logicalName'] = '';
        $this->values['unit'] = '';
        $this->values['currentValue'] = 0;
        $this->values['lowestValue'] = 0;
        $this->values['highestValue'] = 0;
        $this->values['currentRawValue'] = 0;
        $this->values['logFrequency'] = '1/s';
        $this->values['reportFrequency'] = 'OFF';
        $this->values['advMode'] = 0;
        $this->values['calibrationParam'] = '0,';
        $this->values['resolution'] = 0.01;
        $this->values['sensorState'] = 1;
        $this->setupTypes($httpReq);
    }

    public function isSensor(): bool
    {
        return true;
    }

    // Return current sensor value, if valid
    //
    public function getSensorValue(): float
    {
        $avgVal = NAN;
        if($this->values['sensorState'] == 0) {
            $avgVal = $this->values['currentValue'];
        }
        return $avgVal;
    }
}

class APINetworkNode extends APIFunctionNode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->values['readiness'] = 0;
        $this->values['macAddress'] = '00:00:00:00:00:00';
        $this->values['ipAddress'] = '0.0.0.0';
        $this->values['subnetMask'] = '0.0.0.0';
        $this->values['router'] = '0.0.0.0';
        $this->values['ipConfig'] = 'DHCP:169.254.95.6/16/169.254.0.1';
        $this->values['primaryDNS'] = '0.0.0.0';
        $this->values['secondaryDNS'] = '0.0.0.0';
        $this->values['ntpServer'] = '0.0.0.0';
        $this->values['userPassword'] = '';
        $this->values['adminPassword'] = '';
        $this->values['httpPort'] = 4444;
        $this->values['defaultPage'] = '';
        $this->values['discoverable'] = 0;
        $this->values['wwwWatchdogDelay'] = 0;
        $this->values['callbackUrl'] = '';
        $this->values['callbackMethod'] = 0;
        $this->values['callbackEncoding'] = 0;
        $this->values['callbackCredentials'] = ':';
        $this->values['callbackInitialDelay'] = 0;
        $this->values['callbackSchedule'] = 'after 20s/60s';
        $this->values['callbackMinDelay'] = 20;
        $this->values['callbackMaxDelay'] = 60;
        $this->values['poeCurrent'] = 0;
        $this->setupTypes($httpReq);
    }
}

class APICloudNetworkNode extends APINetworkNode
{
    protected array $cachedAttributes;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->cachedAttributes = ['defaultPage', 'userPassword', 'adminPassword'];
        $this->values['ipAddress'] = $httpReq->getServerIP();
        $this->values['httpPort'] = $httpReq->getServerPort();
    }

    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)
    {
        $this->values['logicalName'] = $cloudConf->valuesCache['networkName'];
        foreach ($this->cachedAttributes as $key) {
            if (isset($cloudConf->valuesCache[$key])) {
                $this->values[$key] = $cloudConf->valuesCache[$key];
            }
        }
    }

    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)
    {
        if ($cloudConf->valuesCache['networkName'] != $this->values['logicalName']) {
            $changes['networkName'] = $this->values['logicalName'];
        }
        foreach ($this->cachedAttributes as $key) {
            if (isset($cloudConf->valuesCache[$key]) && $cloudConf->valuesCache[$key] != $this->values[$key]) {
                $changes[$key] = $this->values[$key];
            }
        }
    }

    public function setattr(string $name, string $value): void
    {
        if(substr($name,-8) == 'Password') {
            $mustHash = (strlen($value) != 24);
            if(!$mustHash) {
                $decoded = base64_decode($value, true); // strict decode
                if($decoded === false || strlen($decoded) != 17 || ord($decoded[0]) != 0) {
                    // non a Base64-encoded hashed password
                    $mustHash = true;
                }
            }
            if($mustHash && $value != '') {
                // for safety reasons, don't save the password but pre-hash it with the realm
                // in order to prevent easy password recovery from the configuration file
                $user = substr($name, 0, -8);
                $realm = $this->server->apiroot->cloudConf->authRealm;
                $value = base64_encode(chr(0).md5($user . ':' . $realm . ':' . $value, true));
            }
        }
        parent::setattr($name, $value);
    }
}

class APICloudFilesNode extends APIFunctionNode
{
    protected array $cachedAttributes;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->cachedAttributes = [ 'filesCount', 'freeSpace' ];
        $this->values['filesCount'] = 0;
        $this->values['freeSpace'] = FILES_MAX_SIZE;
        $this->setupTypes($httpReq);
    }

    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)
    {
        $this->values['logicalName'] = $cloudConf->valuesCache['filesName'];
        foreach($this->cachedAttributes as $key) {
            if(isset($cloudConf->valuesCache[$key])) {
                $this->values[$key] = $cloudConf->valuesCache[$key];
            }
        }
    }

    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)
    {
        if($cloudConf->valuesCache['filesName'] != $this->values['logicalName']) {
            $changes['filesName'] = $this->values['logicalName'];
        }
        foreach($this->cachedAttributes as $key) {
            if(!isset($cloudConf->valuesCache[$key]) || $cloudConf->valuesCache[$key] != $this->values[$key]) {
                $changes[$key] = $this->values[$key];
            }
        }
    }

    public function updateStats(VHubServerHTTPRequest $httpReq, int $filesCount, int $totalSize)
    {
        $freeSpace = ($totalSize >= FILES_MAX_SIZE ? 0 : FILES_MAX_SIZE - $totalSize);
        if($this->values['filesCount'] != $filesCount || $this->values['freeSpace'] != $freeSpace) {
            $this->values['filesCount'] = $filesCount;
            $this->values['freeSpace'] = $freeSpace;
            $this->modified = true;
        }
    }
}

class APIDataLoggerNode extends APIFunctionNode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->values['currentRunIndex'] = 0;
        $this->values['timeUTC'] = 0;
        $this->values['recording'] = 0;
        $this->values['autoStart'] = 0;
        $this->values['beaconDriven'] = 0;
        $this->values['usage'] = 0;
        $this->values['clearHistory'] = 0;
        $this->setupTypes($httpReq);
    }

    public function get_timeUTC(): int
    {
        return $this->values['timeUTC'];
    }
}

class APIWPRecordNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name, object $template)
    {
        parent::__construct($httpReq, $server, $name);
        $this->fclass = 'DeviceInfo';
        $this->values['serialNumber'] = $template->serialNumber;
        $this->values['logicalName'] = $template->logicalName;
        $this->values['productName'] = $template->productName;
        $this->values['productId'] = $template->productId;
        $this->values['networkUrl'] = $template->networkUrl;
        $this->values['beacon'] = $template->beacon;
        $this->values['index'] = $template->index;
        $this->setupTypes($httpReq);
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $data, bool $detectChanges): bool
    {
        if($detectChanges) {
            if ($this->values['logicalName'] != $data->logicalName || $this->values['beacon'] != $data->beacon) {
                $this->values['logicalName'] = $data->logicalName;
                $this->values['beacon'] = $data->beacon;
                $this->server->notif->appendModuleNotification($httpReq, $this->values);
                $this->modified = true;
            }
            foreach ($data as $name => $value) {
                if ($this->values[$name] != $value) {
                    $this->values[$name] = $value;
                    $this->modified = true;
                }
            }
            return $this->modified;
        } else {
            foreach ($data as $name => $value) {
                $this->values[$name] = $value;
            }
            return false;
        }
    }
}

class APIWhitePagesNode extends APIArrayNode
{
    protected array $arrayIndexBySerial;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->arrayIndexBySerial = [];
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $wpdef, bool $detectChanges): bool
    {
        foreach($wpdef as $wprec) {
            $wpentry = (object)$wprec;
            $serial = $wpentry->serialNumber;
            if(!$this->server->apiroot->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {
                // unknown device, ignore services
                continue;
            }
            if(isset($this->arrayIndexBySerial[$serial])) {
                $arrayIndex = $this->arrayIndexBySerial[$serial];
                $changed = $this->subnodes[$arrayIndex]->loadState($httpReq, $wpentry, $detectChanges);
                if($changed && $detectChanges) {
                    $this->modified = true;
                }
            } else {
                $subnode = new APIWPRecordNode($httpReq, $this->server, $serial, $wpentry);
                $this->arrayIndexBySerial[$serial] = sizeof($this->subnodes);
                $this->subnodes[] = $subnode;
                if($detectChanges) {
                    $this->modified = true;
                    $cloudSerial = $this->server->apiroot->cloudConf->serialNumber;
                    $this->server->notif->appendModuleArrivalNotifications($httpReq, $cloudSerial, $subnode->values);
                }
            }
        }
        // FIXME: detect device removal?
        return $this->modified;
    }

    public function sortServices(VHubServerHTTPRequest $httpReq)
    {
        usort($this->subnodes, fn(APIWPRecordNode $a,APIWPRecordNode $b) => ($a->values['index']-$b->values['index']));
    }

    public function saveStateForSerial(string $serial): array
    {
        $res = [];
        if(isset($this->arrayIndexBySerial[$serial])) {
            $arrayIndex = $this->arrayIndexBySerial[$serial];
            $res[] = $this->subnodes[$arrayIndex]->saveState();
        }
        return $res;
    }
}

class APIYPRecordNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $hwId, object $template)
    {
        parent::__construct($httpReq, $server, $hwId);
        $this->fclass = 'Provider';
        $this->values['baseType'] = $template->baseType;
        $this->values['hardwareId'] = $template->hardwareId;
        $this->values['logicalName'] = $template->logicalName;
        $this->values['advertisedValue'] = $template->advertisedValue;
        $this->values['index'] = $template->index;
        $this->setupTypes($httpReq);
        // Update global index of funydx by hwid
        $this->server->apiroot->funYdxByHwId[$template->hardwareId] = $template->index;
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $data, bool $detectChanges): bool
    {
        if($detectChanges) {
            $funchanged = false;
            if ($this->values['baseType'] != $data->baseType) {
                $this->values['baseType'] = $data->baseType;
                $funchanged = true;
            }
            if ($this->values['logicalName'] != $data->logicalName) {
                $this->values['logicalName'] = $data->logicalName;
                $funchanged = true;
            }
            if ($this->values['index'] != $data->index) {
                $this->values['index'] = $data->index;
                $funchanged = true;
                // Update global index of funydx by hwid
                $this->server->apiroot->funYdxByHwId[$this->values['hardwareId']] = $data->index;
            }
            if($funchanged) {
                $this->server->notif->appendFunctionNameNotification($httpReq, $this->values);
                $this->modified = true;
            }
            if ($this->values['advertisedValue'] != $data->advertisedValue) {
                $this->values['advertisedValue'] = $data->advertisedValue;
                $this->server->notif->appendFunctionValNotification($httpReq, $this->values);
                $this->modified = true;
            }
            return $this->modified;
        } else {
            foreach ($data as $name => $value) {
                $this->values[$name] = $value;
            }
            return false;
        }
    }
}

class APIYPCategNode extends APIArrayNode
{
    protected array $arrayIndexByHardwareId;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->arrayIndexByHardwareId = [];
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $ypcateg, bool $detectChanges): bool
    {
        foreach($ypcateg as $yprec) {
            $ypentry = (object)$yprec;
            $hwId = $ypentry->hardwareId;
            if(isset($this->arrayIndexByHardwareId[$hwId])) {
                $arrayIndex = $this->arrayIndexByHardwareId[$hwId];
                $changed = $this->subnodes[$arrayIndex]->loadState($httpReq, $ypentry, $detectChanges);
                if($changed && $detectChanges) {
                    $this->modified = true;
                }
            } else {
                $parts = explode('.', $hwId);
                $serial = $parts[0];
                if(!$this->server->apiroot->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {
                    // unknown device, ignore services
                    continue;
                }
                $subnode = new APIYPRecordNode($httpReq, $this->server, $hwId, $ypentry);
                $this->arrayIndexByHardwareId[$hwId] = sizeof($this->subnodes);
                $this->subnodes[] = $subnode;
                if($detectChanges) {
                    $this->modified = true;
                    $this->server->notif->appendFunctionNameNotification($httpReq, $subnode->values);
                }
            }
        }
        // FIXME: detect function removal
        return $this->modified;
    }

    public function saveStateForHwIdPattern(string $pattern): array
    {
        $res = [];
        foreach($this->subnodes as $yprecord) {
            if(preg_match($pattern, $yprecord->values['hardwareId'])) {
                $res[] = $yprecord->saveState();
            }
        }
        return $res;
    }
}

class APIYellowPagesNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
    }

    public function loadState(VHubServerHTTPRequest $httpReq, mixed $ypdef, bool $detectChanges): bool
    {
        foreach($ypdef as $categ => $ypcateg) {
            if(!isset($this->subnodes[$categ])) {
                $this->addSubnode($categ, new APIYPCategNode($httpReq, $this->server, $categ));
                $this->modified = true;
            }
            $categnode = $this->subnodes[$categ];
            $changed = $categnode->loadState($httpReq, $ypcateg, $detectChanges);
            if($changed) {
                $this->modified = true;
            }
        }
        return $this->modified;
    }

    public function saveStateForSerial(string $serial): array
    {
        $res = [];
        $pattern = '~^'.$serial.'[.]~';
        foreach($this->subnodes as $categ => $categnode) {
            $subres = $categnode->saveStateForHwIdPattern($pattern);
            if(sizeof($subres) > 0) {
                $res[$categ] = $subres;
            }
        }
        return $res;
    }
}

class APIServicesNode extends APINode
{
    public APIWhitePagesNode $wp;
    public APIYellowPagesNode $yp;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->wp = new APIWhitePagesNode($httpReq, $this->server, 'whitePages');
        $this->yp = new APIYellowPagesNode($httpReq, $this->server, 'yellowPages');
        $this->addSubnode('whitePages', $this->wp);
        $this->addSubnode('yellowPages', $this->yp);
    }

    public function sortServices(VHubServerHTTPRequest $httpReq)
    {
        $this->wp->sortServices($httpReq);
    }

    public function saveStateForSerial(string $serial): array
    {
        return [
            'whitePages' => $this->wp->saveStateForSerial($serial),
            'yellowPages' => $this->yp->saveStateForSerial($serial)
        ];
    }
}

class APIDeviceAPINode extends APINode
{
    public APIDeviceModuleNode $module;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->module = new APIDeviceModuleNode($httpReq, $this->server, 'module');
        $this->addSubnode('module', $this->module);
    }
}

class DeviceFileList
{
    protected VHubServer $server;
    protected string $serial;
    protected bool $modified;
    protected array $entries;
    // Possible status for entries:
    // - discovered: file discovered on device, to be downloaded to VirtualHub4web
    // - uploaded: file uploaded to VirtualHub4web, to be uploaded to device
    // - known: file exists both on device and in VirtualHub4web
    // - deleting: file deleted on VirtualHub4web, to be deleted on device
    // - deleted: file deleted on VirtualHub4web and deleted on device, expected to disappear
    // - disappeared: file disappeared on device, to be deleted on VirtualHub4web

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $serial)
    {
        $this->server = $server;
        $this->serial = $serial;
        $this->modified = false;
        $this->entries = [];
    }

    public function loadState(VHubServerHTTPRequest $httpReq, array $data): void
    {
        for($i = 0; $i < sizeof($data); $i++) {
            $this->entries[$data[$i]->name] = $data[$i];
        }
    }

    public function saveState(): array
    {
        $res = [];
        foreach($this->entries as $path => $entry) {
            $res[] = $entry;
        }
        $this->modified = false;

        return $res;
    }

    public function hasChanged(): bool
    {
        return $this->modified;
    }

    protected function setEntryState(VHubServerHTTPRequest $httpReq, string $filename, string $newState): void
    {
        if(!isset($this->entries[$filename])) {
            $this->entries[$filename] = new stdClass();
            $this->entries[$filename]->name = $filename;
            $this->entries[$filename]->size = 0;
            $this->entries[$filename]->crc = 0;
            $this->entries[$filename]->status = $newState;
            $this->modified = true;
            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} on {$this->serial} added in state {$newState}");
        } else if($this->entries[$filename]->status != $newState) {
            $this->entries[$filename]->status = $newState;
            $this->modified = true;
            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} on {$this->serial} is now in state {$newState}");
        }
    }

    // compare a known fileList to the current device filesystem
    function compareToDevice(VHubServerHTTPRequest $httpReq, array $filerecs): bool
    {
        // first detect all changes compared to VirtualHub4web state
        $foundOnDevice = [];
        for($i = 0; $i < sizeof($filerecs); $i++) {
            $entry = $filerecs[$i];
            $foundOnDevice[$entry->name] = true;
            if(!isset($this->entries[$entry->name])) {
                // new entry
                $entry->status = 'discovered';
                $this->entries[$entry->name] = $entry;
                $this->modified = true;
                VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$entry->name} on {$this->serial} added in state {$entry->status}");
                continue;
            }
            $existing = $this->entries[$entry->name];
            VHubServer::Log($httpReq, LOG_FILESYNC, 5, "CompareToDevice {$entry->name} on {$this->serial}: status={$existing->status}");
            switch($existing->status) {
                case 'discovered':  // new file on device, not yet downloaded
                    break;
                case 'deleting':    // deletion is expected next time the device connects
                    VHubServer::Log($httpReq, LOG_FILESYNC, 2, "File {$entry->name} on {$this->serial} is scheduled for deletion");
                    $this->setEntryState($httpReq, $entry->name, 'deleted');
                    break;
                case 'deleted':     // deletion failed? retry
                    VHubServer::Log($httpReq, LOG_FILESYNC, 2, "File deletion for {$entry->name} failed on {$this->serial}, retrying");
                    $this->deleteOnDevice($httpReq, $entry->name);
                    break;
                case 'uploaded':
                    if($entry->size == $existing->size && ($entry->crc & 0xffffffff) == ($existing->crc & 0xffffffff)) {
                        // file on device is the same
                        $this->setEntryState($httpReq, $entry->name, 'known');
                    }
                    break;
                case 'disappeared':
                case 'known':
                    if($entry->size == $existing->size && ($entry->crc & 0xffffffff) == ($existing->crc & 0xffffffff)) {
                        $this->setEntryState($httpReq, $entry->name, 'known');
                    } else {
                        // file has changed on device, must be downloaded
                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$entry->name} has changed on {$this->serial}");
                        $this->setEntryState($httpReq, $entry->name, 'discovered');
                    }
                    break;
            }
        }
        foreach($this->entries as $filename => $entry) {
            if(!isset($foundOnDevice[$filename])) {
                switch($entry->status) {
                    case 'discovered':  // new file on device, not yet downloaded, has disappeared
                    case 'deleting':    // deletion is expected next time the device connects
                    case 'deleted':     // file just deleted on device
                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} is no more in {$this->serial}");
                        unset($this->entries[$filename]);
                        $this->modified = true;
                        break;
                    case 'uploaded':
                        // expect new file to appear on device shortly
                        break;
                    case 'disappeared':
                    case 'known':
                        $this->setEntryState($httpReq, $filename, 'disappeared');
                        break;
                }
            }
        }
        // Then process changes
        foreach($this->entries as $filename => $entry) {
            switch($entry->status) {
                case 'discovered':
                    // download file asap
                    $fcontent = $this->server->tryDownload($httpReq, $this->serial, $filename, false);
                    if(is_null($fcontent)) {
                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Will download {$filename} from {$this->serial}");
                    } else {
                        $this->server->files->saveDeviceFile($httpReq, $this->serial, 'files/'.$filename, $fcontent);
                        $this->setEntryState($httpReq, $filename, 'known');
                    }
                    break;
                case 'deleting':
                case 'deleted':
                    // deletion already scheduled, nothing to be done
                    break;
                case 'uploaded':
                    VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Must upload {$filename} to {$this->serial}");
                    $tarfile = $this->server->files->accessDeviceFiles($httpReq, $this->serial);
                    $obj = $tarfile->searchTarFile($httpReq, 'files/'.$filename);
                    if(is_null($obj)) {
                        VHubServer::Log($httpReq, LOG_FILESYNC, 1, "Cannot upload {$filename} to {$this->serial}, file is missing on VirtualHub4web");
                    } else {
                        $this->server->scheduleUploadOnDevice($httpReq, $this->serial, $filename, $obj->content);
                    }
                    break;
                case 'disappeared':
                    VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} has disappeared on {$this->serial}, removing on VirtualHub4web");
                    $tarfile = $this->server->files->accessDeviceFiles($httpReq, $this->serial);
                    $tarfile->processTarFile($httpReq, 'files/'.$filename, TAROP_DELETE_FILE);
                    unset($this->entries[$filename]);
                    break;
                case 'known':
                    VHubServer::Log($httpReq, LOG_FILESYNC, 5, "File {$filename} is up-to-date on {$this->serial}");
                    break;
            }
        }
        return $this->modified;
    }

    // propagate VirtualHub4web upload to the device
    function uploadToDevice(VHubServerHTTPRequest $httpReq, string $filename, int $filesize, int $crc): void
    {
        $this->entries[$filename] = (object)['name' => $filename, 'size' => $filesize, 'crc' => $crc, 'status' => 'uploaded'];
        $this->modified = true;
        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} for {$this->serial} uploaded to VirtualHub4web");
    }

    // propagate VirtualHub4web delete to the device
    function deleteOnDevice(VHubServerHTTPRequest $httpReq, string $filename): void
    {
        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Schedule deletion of {$filename} on {$this->serial}");
        $url = '/files.json?a=del&f='.$this->server->_escapeAttr($filename);
        $this->server->scheduleQueryOnDevice($httpReq, $this->serial, 'GET', $url);
        $this->setEntryState($httpReq, $filename, 'deleting');
    }

    // propagate VirtualHub4web format to the device
    function formatOnDevice(VHubServerHTTPRequest $httpReq): void
    {
        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Schedule filesystem format on {$this->serial}");
        $this->server->scheduleQueryOnDevice($httpReq, $this->serial, 'GET', '/files.json?a=format');
        foreach($this->entries as $filename => $entry) {
            $this->setEntryState($httpReq, $filename, 'deleting');
        }
    }
}


class APIDeviceNode extends APINode
{
    public DeviceCloudConf $cloudConf;
    public DeviceFileList $fileList;
    public APIServicesNode $services;
    public APIDeviceAPINode $api;
    public ?DeviceStats $deviceStats;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $serial)
    {
        parent::__construct($httpReq, $server, $serial);
        $this->cloudConf = new DeviceCloudConf();
        $this->fileList = new DeviceFileList($httpReq, $this->server, $serial);
        $this->services = new APIServicesNode($httpReq, $this->server, 'services');
        $this->api = new APIDeviceAPINode($httpReq, $this->server, 'api');
        $this->addSubnode('api', $this->api);
        $this->deviceStats = null;
    }

    // Load device global state from file data or live device api
    public function loadState(VHubServerHTTPRequest $httpReq, mixed $data, $detectChanges): bool
    {
        if(isset($data->VirtualHub4web)) {
            $this->cloudConf->loadState($httpReq, $data->VirtualHub4web);
        }
        if(isset($data->FileList)) {
            $this->fileList->loadState($httpReq, $data->FileList);
        }
        // Restore services (originally published by the hub) from individual device files
        // next to the api tree, where we have saved them there, instead as from the hub.
        // This avoids keeping a dependence between the device and its own hub,
        // and allows to transpose easily a device from one VirtualHub4web to the other
        if(isset($data->services)) {
            $this->services->loadState($httpReq, $data->services, false);
        }
        $modified = parent::loadState($httpReq, $data, $detectChanges);
        if(isset($data->VirtualHub4web)) {
            if (isset($data->VirtualHub4web->lastSeen)) {
                $this->api->module->values['lastSeen'] = time() - $data->VirtualHub4web->lastSeen;
            }
            if (isset($data->VirtualHub4web->parentHub)) {
                $this->api->module->values['parentHub'] = $data->VirtualHub4web->parentHub;
                $this->api->module->values['parentIP'] = $data->VirtualHub4web->parentIP;
            }
        }
        return $modified;
    }

     // Save device global state into an array for saving
    public function saveState(): array
    {
        $res = parent::saveState();
        $res['services'] = $this->services->saveState();
        $res['FileList'] = $this->fileList->saveState();
        $res['VirtualHub4web'] = $this->cloudConf->saveState();
        return $res;
    }

    // Mark node as modified to force saving VirtualHub4web configuration
    public function markAsChanged()
    {
        $this->modified = true;
    }

    public function hasChanged(): bool
    {
        return $this->modified || $this->fileList->hasChanged();
    }

    // Prepare to collect device statistics
    public function initStats(VHubServerHTTPRequest $httpReq): void
    {
        $this->deviceStats = new DeviceStats();
    }

    // Prepare to save device statistics to the device-specific file
    public function getDeviceStats(): ?DeviceStats
    {
        return $this->deviceStats;
    }

}

class APICloudApiNode extends APINode
{
    public APICloudModuleNode $module;
    public APICloudNetworkNode $network;
    public APICloudFilesNode $files;
    public APIServicesNode $services;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->module = new APICloudModuleNode($httpReq, $this->server, 'module');
        $this->network = new APICloudNetworkNode($httpReq, $this->server, 'network');
        $this->files = new APICloudFilesNode($httpReq, $this->server, 'files');
        $this->services = new APIServicesNode($httpReq, $this->server, 'services');
        $this->addSubnode('module', $this->module);
        $this->addSubnode('network', $this->network);
        $this->addSubnode('files', $this->files);
        $this->addSubnode('services', $this->services);
    }

    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf): void
    {
        $this->module->loadStateFromCloudConf($httpReq, $cloudConf);
        $this->network->loadStateFromCloudConf($httpReq, $cloudConf);
        $this->files->loadStateFromCloudConf($httpReq, $cloudConf);
    }

    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)
    {
        $this->module->compareStateToCloudConf($httpReq, $cloudConf, $changes);
        $this->network->compareStateToCloudConf($httpReq, $cloudConf, $changes);
        $this->files->compareStateToCloudConf($httpReq, $cloudConf, $changes);
    }
}

class APIBySerialNode extends APINode
{
    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
    }
}

class APIRootNode extends APINode
{
    public GlobalCloudConf $cloudConf;
    public APICloudApiNode $api;
    public APIBySerialNode $bySerial;
    public array $funYdxByHwId;
    protected array $guessedAttrTypes;

    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)
    {
        parent::__construct($httpReq, $server, $name);
        $this->server->apiroot = $this;
        $this->cloudConf = new GlobalCloudConf();
        $this->api = new APICloudApiNode($httpReq, $this->server, 'api');
        $this->bySerial = new APIBySerialNode($httpReq, $this->server, 'bySerial');
        $this->funYdxByHwId = [];
        $this->guessedAttrTypes = [];
        $this->addSubnode('api', $this->api);
        $this->addSubnode('bySerial', $this->bySerial);
    }

    // Load VirtualHub4web global configuration from saved state
    public function loadState(VHubServerHTTPRequest $httpReq, mixed $data, bool $detectChanges): bool
    {
        if(isset($data->VirtualHub4web)) {
            $this->cloudConf->loadState($httpReq, $data->VirtualHub4web);
            $this->api->loadStateFromCloudConf($httpReq, $this->cloudConf);
        }
        return true;    // not relevant for global configuration
    }

    // Save VirtualHub4web global state into configuration object
    public function saveState(): array
    {
        $res = [];
        $res['VirtualHub4web'] = $this->cloudConf->saveState();
        return $res;
    }

    // Return a list of changes to VirtualHub4web state since last loaded
    public function getStateChanges(VHubServerHTTPRequest $httpReq): array
    {
        $changes = [];
        $this->api->compareStateToCloudConf($httpReq, $this->cloudConf, $changes);
        return $changes;
    }

    // Load our own services into the whitePages/yellowPages
    public function loadOwnServices(VHubServerHTTPRequest $httpReq)
    {
        $wpdef = new stdClass();
        $wpdef->serialNumber = $this->cloudConf->serialNumber;
        $wpdef->logicalName = $this->api->module->getattr('logicalName');
        $wpdef->productName = $this->api->module->getattr('productName');
        $wpdef->productId = $this->api->module->getattr('productId');
        $wpdef->networkUrl = '/api';
        $wpdef->beacon = $this->api->module->getattr('beacon');
        $wpdef->index = 0;
        $filesdef = new stdClass();
        $filesdef->baseType = 0;
        $filesdef->hardwareId = $this->cloudConf->serialNumber.'.files';
        $filesdef->logicalName = $this->api->files->getattr('logicalName');
        $filesdef->advertisedValue = $this->api->files->getattr('advertisedValue');
        $filesdef->index = 0;
        $netdef = clone $filesdef;
        $netdef->hardwareId = $this->cloudConf->serialNumber.'.network';
        $netdef->logicalName = $this->api->network->getattr('logicalName');
        $filesdef->advertisedValue = $this->api->network->getattr('advertisedValue');
        $netdef->index = 1;
        $ypdef = new stdClass();
        $ypdef->Files = [ $filesdef ];
        $ypdef->Network = [ $netdef ];
        $this->api->services->wp->loadState($httpReq, [$wpdef], false);
        $this->api->services->yp->loadState($httpReq, $ypdef, false);
    }

    // Attempt to load specified service definitions into the VirtualHub4web
    // Return true if success or false if a devYdx needs to be allocated
    public function loadServices(VHubServerHTTPRequest $httpReq, string $hubSerial, object $servicesdef, bool $canUpdateDevYdx): bool
    {
        $hubDevYdx = max($this->cloudConf->getDevYdx($hubSerial), 0);
        $wpdef = $servicesdef->whitePages;
        foreach($wpdef as &$wpentry) {
            $serial = $wpentry->serialNumber;
            if(!$this->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {
                // unknown device, ignore services
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "LoadServices: ignore unknown serial $serial");
                continue;
            }
            $parentDevYdx = ($serial == $hubSerial ? 0 : $hubDevYdx);
            $devYdx = $this->cloudConf->getDevYdx($serial);
            if ($devYdx < 0) { // new device
                if(!$canUpdateDevYdx) return false;
                $devYdx = $this->cloudConf->allocDevYdx($serial, $parentDevYdx);
                if($devYdx < 0) {
                    // too many devices for this instance of VirtualHub-4web
                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Too many devices on this instance, ignoring $serial");
                    continue;
                }
            } else if($parentDevYdx != -1 && $this->cloudConf->getParentDevYdx($serial) != $parentDevYdx) {
                if(!$canUpdateDevYdx) return false;
                $this->cloudConf->setParentDevYdx($serial, $parentDevYdx);
            }
            $wpentry->networkUrl = "/bySerial/$serial/api";
            $wpentry->index = $devYdx;
        }
        $this->api->services->loadState($httpReq, $servicesdef, false);
        return true;
    }

    // Return a services structure describing services offered by the given serial
    public function saveServicesForSerial(string $serial): array
    {
        return $this->api->services->saveStateForSerial($serial);
    }

    // Return the known (or guessed) type of a given attribute
    public function getAttrType(VHubServerHTTPRequest $httpReq, string $functionClass, string $attrName, mixed $value): int
    {
        global $ApiDef;
        // First search into known function class definitions (generated file)
        if(isset($ApiDef[$functionClass]) && isset($ApiDef[$functionClass][$attrName])) {
            return $ApiDef[$functionClass][$attrName];
        }
        // Compute inference table when needed for the first time
        VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Infer attribute type for [{$functionClass}.]{$attrName}");
        if(sizeof($this->guessedAttrTypes) == 0) {
            $typesByAttr = [];
            foreach($ApiDef as $fclass => $classdef) {
                foreach($classdef as $attr => $typeidx) {
                    if(!isset($typesByAttr[$attr])) {
                        $typesByAttr[$attr] = [ $typeidx => [ 'cnt' => 1, 'idx' => $typeidx ] ];
                    } else if(!isset($typesByAttr[$attr][$typeidx])) {
                        $typesByAttr[$attr][$typeidx] = [ 'cnt' => 1, 'idx' => $typeidx ];
                    } else {
                        $typesByAttr[$attr][$typeidx]['cnt'] += 1;
                    }
                }
            }
            foreach($typesByAttr as $attr => $alltypes) {
                $bestCnt = 0;
                foreach($alltypes as $typedesc => $typestats) {
                    if($bestCnt < $typestats['cnt']) {
                        $bestCnt = $typestats['cnt'];
                        $this->guessedAttrTypes[$attr] = $typestats['idx'];
                    }
                }
            }
        }
        // If this is a brand new attribute, assume read-only and infer type from value
        if(!isset($this->guessedAttrTypes[$attrName])) {
            if (is_numeric($value)) {
                $this->guessedAttrTypes[$attrName] = $ApiDef['DeviceInfo']['index'];    // aka read-only Int
            } else {
                $this->guessedAttrTypes[$attrName] = $ApiDef['Module']['serialNumber']; // aka read-only Text
            }
        }
        return $this->guessedAttrTypes[$attrName];
    }
}
