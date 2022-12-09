<?php /* VirtualHub-4web (version 1.10.52282) - www.yoctopuce.com */
declare(strict_types=1);
const VERSION = "1.10.52282";
function check_php_conf(bool $checkDataFolder = false): array{    $res = [];    if(PHP_MAJOR_VERSION < 7) {        $res[] = [            'error' => 'PHP_MAJOR_VERSION',            'msg' => 'This software requires PHP version version 7.x or 8.x.',            'cause' => 'This server is running PHP version '.phpversion().', which is out of support for several years. '.                'You should seriously consider tp upgrade your server.'        ];    }    if(PHP_INT_MAX < 0x100000000) {        $res[] = [            'error' => 'PHP_INT_MAX',            'msg' => 'This software requires 64-bit integers.',            'cause' => 'On this server, <b>PHP_INT_MAX</b> = 0x'.dechex(PHP_INT_MAX).', which is less than 64 bit. '.                'This is not enough for this software to work properly.'        ];    }    $url_fopen = ini_get('allow_url_fopen');    if ($url_fopen !== 'On' && $url_fopen !== '1') {        $res[] = [            'error' => 'allow_url_fopen',            'msg' => 'This software requires <b>allow_url_fopen</b> to be enabled.',            'cause' => '<b>allow_url_fopen</b> is currenlty set to '.$url_fopen.'. '.                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',            '.user.ini' => 'allow_url_fopen="1"',            '.htaccess' => 'php_value allow_url_fopen 1'        ];    }    $post_reading = ini_get('enable_post_data_reading');    if ($post_reading !== '0') {        $res[] = [            'error' => 'enable_post_data_reading',            'msg' => 'This software requires <b>enable_post_data_reading</b> to be set to 0.',            'cause' => '<b>enable_post_data_reading</b> is currenlty set to '.$post_reading.'. '.                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',            '.user.ini' => 'enable_post_data_reading="0"',            '.htaccess' => 'php_value enable_post_data_reading 0'        ];    }    $max_post = ini_get('post_max_size');    $max_post_kb = intval(str_replace(['K', 'M', 'G'], ['', '000', '000000'], $max_post));    if ($max_post_kb < 2000) {        $res[] = [            'error' => 'post_max_size',            'msg' => 'This software requires <b>post_max_size</b> to be at least 2 MB (ideally at least 4 MB).',            'cause' => '<b>post_max_size</b> is currenlty set to '.$max_post.'. '.                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',            '.user.ini' => 'post_max_size="4M"',            '.htaccess' => 'php_value post_max_size 4M'        ];    }    $max_upload = ini_get('upload_max_filesize');    $max_upload_kb = intval(str_replace(['K', 'M', 'G'], ['', '000', '000000'], $max_upload));    if ($max_upload_kb < 2000) {        $res[] = [            'error' => 'upload_max_filesize',            'msg' => 'This software requires <b>upload_max_filesize</b> to be at least 2 MB (ideally at least 4 MB).',            'cause' => '<b>upload_max_filesize</b> is currenlty set to '.$max_upload.'. '.                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',            '.user.ini' => 'upload_max_filesize="4M"',            '.htaccess' => 'php_value upload_max_filesize 4M'        ];    }    if($checkDataFolder) {        // make sure the caller has defined a VHUB4WEB_DATA        if (!defined('VHUB4WEB_DATA')) {            $res[] = [                'error' => 'VHUB4WEB_DATA-undefined',                'msg' => 'This software requires a constant VHUB4WEB_DATA pointing to the directory where data should be stored.',                'cause' => 'The entry point (currently set to '.$_SERVER['SCRIPT_NAME'].') should be a simple script that '.                    'defines VHUB4WEB_DATA before including <b>vhub4web-init.php</b>. '.                    'This looks like an installation error, check the documentation or re-run the easy installer process.'            ];            return $res;        }        // check for data subfolder and server configuration        if (!file_exists(VHUB4WEB_DATA) || !is_dir(VHUB4WEB_DATA)) {            $res[] = [                'error' => 'VHUB4WEB_DATA-missing',                'msg' => 'This software was configured to store data in directory <b>'.VHUB4WEB_DATA.'</b>, which cannot be found.',                'cause' => 'Folder <b>'.VHUB4WEB_DATA.'</b> does not seems to be a valid path this server. '.                    'This looks like an installation error, check the documentation or re-run the easy installer process.'            ];            return $res;        }        if (!is_writable(VHUB4WEB_DATA)) {            $res[] = [                'error' => 'VHUB4WEB_DATA-readonly',                'msg' => 'This software was configured to store data in directory <b>'.VHUB4WEB_DATA.'</b>, which is write-protected.',                'cause' => 'Folder <b>'.VHUB4WEB_DATA.'</b> does not seems to be a writable for this PHP script. '.                    'This looks like an installation error, check the documentation or re-run the easy installer process.'            ];            return $res;        }    }    return $res;}
//--- (generated code: YFunction definitions)
// Yoctopuce error codes, also used by default as function return value
define('YAPI_SUCCESS',                 0);     // everything worked all right
define('YAPI_NOT_INITIALIZED',         -1);    // call yInitAPI() first !
define('YAPI_INVALID_ARGUMENT',        -2);    // one of the arguments passed to the function is invalid
define('YAPI_NOT_SUPPORTED',           -3);    // the operation attempted is (currently) not supported
define('YAPI_DEVICE_NOT_FOUND',        -4);    // the requested device is not reachable
define('YAPI_VERSION_MISMATCH',        -5);    // the device firmware is incompatible with this API version
define('YAPI_DEVICE_BUSY',             -6);    // the device is busy with another task and cannot answer
define('YAPI_TIMEOUT',                 -7);    // the device took too long to provide an answer
define('YAPI_IO_ERROR',                -8);    // there was an I/O problem while talking to the device
define('YAPI_NO_MORE_DATA',            -9);    // there is no more data to read from
define('YAPI_EXHAUSTED',               -10);   // you have run out of a limited resource, check the documentation
define('YAPI_DOUBLE_ACCES',            -11);   // you have two process that try to access to the same device
define('YAPI_UNAUTHORIZED',            -12);   // unauthorized access to password-protected device
define('YAPI_RTC_NOT_READY',           -13);   // real-time clock has not been initialized (or time was lost)
define('YAPI_FILE_NOT_FOUND',          -14);   // the file is not found
define('YAPI_SSL_ERROR',               -15);   // Error reported by mbedSSL
define('YAPI_INVALID_INT',             0x7fffffff);
define('YAPI_INVALID_UINT',            -1);
define('YAPI_INVALID_LONG',            0x7fffffffffffffff);
define('YAPI_INVALID_DOUBLE',          -66666666.66666666);
define('YAPI_INVALID_STRING',          "!INVALID!");
define('Y_FUNCTIONDESCRIPTOR_INVALID', YAPI_INVALID_STRING);
define('Y_HARDWAREID_INVALID',         YAPI_INVALID_STRING);
define('Y_FUNCTIONID_INVALID',         YAPI_INVALID_STRING);
define('Y_FRIENDLYNAME_INVALID',       YAPI_INVALID_STRING);
if(!defined('Y_LOGICALNAME_INVALID'))        define('Y_LOGICALNAME_INVALID',       YAPI_INVALID_STRING);
if(!defined('Y_ADVERTISEDVALUE_INVALID'))    define('Y_ADVERTISEDVALUE_INVALID',   YAPI_INVALID_STRING);
//--- (end of generated code: YFunction definitions)
define('YAPI_HASH_BUF_SIZE', 28);
define('YAPI_MIN_DOUBLE', -INF);
define('YAPI_MAX_DOUBLE', INF);
//--- (generated code: YMeasure definitions)
//--- (end of generated code: YMeasure definitions)
if (!defined('Y_DATA_INVALID')) define('Y_DATA_INVALID', YAPI_INVALID_DOUBLE);
if (!defined('Y_DURATION_INVALID')) define('Y_DURATION_INVALID', YAPI_INVALID_INT);
//--- (generated code: YFirmwareUpdate definitions)
//--- (end of generated code: YFirmwareUpdate definitions)
//--- (generated code: YDataStream definitions)
//--- (end of generated code: YDataStream definitions)
//--- (generated code: YDataSet definitions)
//--- (end of generated code: YDataSet definitions)
//--- (generated code: YConsolidatedDataSet definitions)
//--- (end of generated code: YConsolidatedDataSet definitions)
//--- (generated code: YSensor definitions)
if(!defined('Y_ADVMODE_IMMEDIATE'))          define('Y_ADVMODE_IMMEDIATE',         0);
if(!defined('Y_ADVMODE_PERIOD_AVG'))         define('Y_ADVMODE_PERIOD_AVG',        1);
if(!defined('Y_ADVMODE_PERIOD_MIN'))         define('Y_ADVMODE_PERIOD_MIN',        2);
if(!defined('Y_ADVMODE_PERIOD_MAX'))         define('Y_ADVMODE_PERIOD_MAX',        3);
if(!defined('Y_ADVMODE_INVALID'))            define('Y_ADVMODE_INVALID',           -1);
if(!defined('Y_UNIT_INVALID'))               define('Y_UNIT_INVALID',              YAPI_INVALID_STRING);
if(!defined('Y_CURRENTVALUE_INVALID'))       define('Y_CURRENTVALUE_INVALID',      YAPI_INVALID_DOUBLE);
if(!defined('Y_LOWESTVALUE_INVALID'))        define('Y_LOWESTVALUE_INVALID',       YAPI_INVALID_DOUBLE);
if(!defined('Y_HIGHESTVALUE_INVALID'))       define('Y_HIGHESTVALUE_INVALID',      YAPI_INVALID_DOUBLE);
if(!defined('Y_CURRENTRAWVALUE_INVALID'))    define('Y_CURRENTRAWVALUE_INVALID',   YAPI_INVALID_DOUBLE);
if(!defined('Y_LOGFREQUENCY_INVALID'))       define('Y_LOGFREQUENCY_INVALID',      YAPI_INVALID_STRING);
if(!defined('Y_REPORTFREQUENCY_INVALID'))    define('Y_REPORTFREQUENCY_INVALID',   YAPI_INVALID_STRING);
if(!defined('Y_CALIBRATIONPARAM_INVALID'))   define('Y_CALIBRATIONPARAM_INVALID',  YAPI_INVALID_STRING);
if(!defined('Y_RESOLUTION_INVALID'))         define('Y_RESOLUTION_INVALID',        YAPI_INVALID_DOUBLE);
if(!defined('Y_SENSORSTATE_INVALID'))        define('Y_SENSORSTATE_INVALID',       YAPI_INVALID_INT);
//--- (end of generated code: YSensor definitions)
//--- (generated code: YModule definitions)
if(!defined('Y_PERSISTENTSETTINGS_LOADED'))  define('Y_PERSISTENTSETTINGS_LOADED', 0);
if(!defined('Y_PERSISTENTSETTINGS_SAVED'))   define('Y_PERSISTENTSETTINGS_SAVED',  1);
if(!defined('Y_PERSISTENTSETTINGS_MODIFIED')) define('Y_PERSISTENTSETTINGS_MODIFIED', 2);
if(!defined('Y_PERSISTENTSETTINGS_INVALID')) define('Y_PERSISTENTSETTINGS_INVALID', -1);
if(!defined('Y_BEACON_OFF'))                 define('Y_BEACON_OFF',                0);
if(!defined('Y_BEACON_ON'))                  define('Y_BEACON_ON',                 1);
if(!defined('Y_BEACON_INVALID'))             define('Y_BEACON_INVALID',            -1);
if(!defined('Y_PRODUCTNAME_INVALID'))        define('Y_PRODUCTNAME_INVALID',       YAPI_INVALID_STRING);
if(!defined('Y_SERIALNUMBER_INVALID'))       define('Y_SERIALNUMBER_INVALID',      YAPI_INVALID_STRING);
if(!defined('Y_PRODUCTID_INVALID'))          define('Y_PRODUCTID_INVALID',         YAPI_INVALID_UINT);
if(!defined('Y_PRODUCTRELEASE_INVALID'))     define('Y_PRODUCTRELEASE_INVALID',    YAPI_INVALID_UINT);
if(!defined('Y_FIRMWARERELEASE_INVALID'))    define('Y_FIRMWARERELEASE_INVALID',   YAPI_INVALID_STRING);
if(!defined('Y_LUMINOSITY_INVALID'))         define('Y_LUMINOSITY_INVALID',        YAPI_INVALID_UINT);
if(!defined('Y_UPTIME_INVALID'))             define('Y_UPTIME_INVALID',            YAPI_INVALID_LONG);
if(!defined('Y_USBCURRENT_INVALID'))         define('Y_USBCURRENT_INVALID',        YAPI_INVALID_UINT);
if(!defined('Y_REBOOTCOUNTDOWN_INVALID'))    define('Y_REBOOTCOUNTDOWN_INVALID',   YAPI_INVALID_INT);
if(!defined('Y_USERVAR_INVALID'))            define('Y_USERVAR_INVALID',           YAPI_INVALID_INT);
//--- (end of generated code: YModule definitions)
// yInitAPI constants (not really useful in PHP, but defined for code portability)
define('Y_DETECT_NONE', 0);
define('Y_DETECT_USB', 1);
define('Y_DETECT_NET', 2);
define('Y_DETECT_ALL', Y_DETECT_USB | Y_DETECT_NET);
// Calibration types
define('YOCTO_CALIB_TYPE_OFS', 30);
// Maximum device request timeout
define('YAPI_BLOCKING_REQUEST_TIMEOUT', 20000);
define('YIO_DEFAULT_TCP_TIMEOUT',20000);
define('YIO_1_MINUTE_TCP_TIMEOUT',60000);
define('YIO_10_MINUTES_TCP_TIMEOUT',600000);
define('NOTIFY_NETPKT_NAME', '0');
define('NOTIFY_NETPKT_CHILD', '2');
define('NOTIFY_NETPKT_FUNCNAME', '4');
define('NOTIFY_NETPKT_FUNCVAL', '5');
define('NOTIFY_NETPKT_LOG', '7');
define('NOTIFY_NETPKT_FUNCNAMEYDX', '8');
define('NOTIFY_NETPKT_CONFCHGYDX', 's');
define('NOTIFY_NETPKT_FLUSHV2YDX', 't');
define('NOTIFY_NETPKT_FUNCV2YDX', 'u');
define('NOTIFY_NETPKT_TIMEV2YDX', 'v');
define('NOTIFY_NETPKT_DEVLOGYDX', 'w');
define('NOTIFY_NETPKT_TIMEVALYDX', 'x');
define('NOTIFY_NETPKT_FUNCVALYDX', 'y');
define('NOTIFY_NETPKT_TIMEAVGYDX', 'z');
define('NOTIFY_NETPKT_NOT_SYNC', '@');
define('NOTIFY_NETPKT_STOP', 10); // =\n
define('NOTIFY_V2_LEGACY', 0);       // unused (reserved for compatibility with legacy notifications)
define('NOTIFY_V2_6RAWBYTES', 1);    // largest type: data is always 6 bytes
define('NOTIFY_V2_TYPEDDATA', 2);    // other types: first data byte holds the decoding format
define('NOTIFY_V2_FLUSHGROUP', 3);   // no data associated
define('PUBVAL_LEGACY', 0);   // 0-6 ASCII characters (normally sent as YSTREAM_NOTICE)
define('PUBVAL_1RAWBYTE', 1);   // 1 raw byte  (=2 characters)
define('PUBVAL_2RAWBYTES', 2);   // 2 raw bytes (=4 characters)
define('PUBVAL_3RAWBYTES', 3);   // 3 raw bytes (=6 characters)
define('PUBVAL_4RAWBYTES', 4);   // 4 raw bytes (=8 characters)
define('PUBVAL_5RAWBYTES', 5);   // 5 raw bytes (=10 characters)
define('PUBVAL_6RAWBYTES', 6);   // 6 hex bytes (=12 characters) (sent as V2_6RAWBYTES)
define('PUBVAL_C_LONG', 7);   // 32-bit C signed integer
define('PUBVAL_C_FLOAT', 8);   // 32-bit C float
define('PUBVAL_YOCTO_FLOAT_E3', 9);   // 32-bit Yocto fixed-point format (e-3)
define('PUBVAL_YOCTO_FLOAT_E6', 10);   // 32-bit Yocto fixed-point format (e-6)
define('YOCTO_PUBVAL_LEN', 16);
define('YOCTO_PUBVAL_SIZE', 6);
define('YOCTO_SERIAL_LEN', 20);
define('YOCTO_BASE_SERIAL_LEN', 8);
//
// Class used to report exceptions within Yocto-API
// Do not instantiate directly
//
class YAPI_Exception extends Exception
{
}
// Pseudo class used to create structures in PHP
class YAggregate
{
}
// numeric strpos helper
function Ystrpos($haystack, $needle)
{
    $res = strpos($haystack, $needle);
    if ($res === false) $res = -1;
    return $res;
}
//
// Structure used internally to report results of a query. It only uses public attributes.
// Do not instantiate directly
//
class YAPI_YReq
{
    public $hwid       = "";
    public $deviceid   = "";
    public $functionid = "";
    public $errorType;
    public $errorMsg;
    public $result;
    public $obj_result = NULL;
    function __construct($str_hwid, $int_errType, $str_errMsg, $bin_result, $obj_result = null)
    {
        $sep = strpos($str_hwid, ".");
        if ($sep !== false) {
            $this->hwid = $str_hwid;
            $this->deviceid = substr($str_hwid, 0, $sep);
            $this->functionid = substr($str_hwid, $sep + 1);
        }
        $this->errorType = $int_errType;
        $this->errorMsg = $str_errMsg;
        $this->result = $bin_result;
        $this->obj_result = $obj_result;
    }
}
//
// YTcpHub Class (used internally)
//
// Instances of this class represent a VirtualHub or a networked Yoctopuce device
// to which we can connect to get access to device functions. For historical reasons,
// this class is mostly used like a structure, rather than a real object.
//
class YTcpHub
{
    // attributes
    public $rooturl;                    // root url of the hub (without auth parameters)
    public $streamaddr;                 // stream address of the hub ("tcp://addr:port")
    public $url_info;                   // $url parsed
    public $notifurl;                   // notification file used by this hub
    public $use_pure_http;              // boolean that is true if the hub is VirtualHub-4web
    public $notifReq;                   // notification request, or null if not open
    public $notifPos;                   // absolute position in notification stream
    public $isNotifWorking;            // boolean that is true when we receive ping notification
    public $devListExpires;             // timestamp of next useful updateDeviceList
    public    $devListReq;                 // updateDeviceList request, or null if not open
    public    $serialByYdx;                // serials by hub-specific devYdx
    public    $retryDelay;                 // delay before reconnecting in case of error
    public    $retryExpires;               // timestamp of next reconnection attempt
    public    $missing;                    // list of missing devices during updateDeviceList
    public    $writeProtected;             // true if an adminPassword is set
    public    $user;                       // user for authentication
    public    $callbackData;               // raw HTTP callback data received
    public    $callbackCache;              // pre-parsed cache for callback-based API
    public    $reuseskt;                   // keep-alive socket to be reused
    protected $realm;                   // hub authentication realm
    protected $pwd;                     // password for authentication
    protected $nonce;                   // lasPrint(t received nonce
    protected $opaque;                  // last received opaque
    protected $ha1;                     // our authentication ha1 string
    protected $nc;                      // nounce usage count
    function __construct($url_info)
    {
        $this->rooturl = $url_info['rooturl'];
        $this->url_info = $url_info;
        $this->streamaddr = str_replace('http://', 'tcp://', $this->rooturl);
        $this->streamaddr = str_replace('https://', 'tls://', $this->streamaddr);
        $colon = strpos( $url_info['auth'], ':');
        if ($colon === false) {
            $this->user = $url_info['auth'];
            $this->pwd = '';
        } else {
            $this->user = substr($url_info['auth'], 0, $colon);
            $this->pwd = substr($url_info['auth'], $colon + 1);
        }
        $this->notifurl = 'not.byn';
        $this->notifHandle = null;
        $this->notifPos = -1;
        $this->isNotifWorking = false;
        $this->devListExpires = 0;
        $this->serialByYdx = Array();
        $this->retryDelay = 15;
        $this->retryExpires = 0;
        $this->writeProtected = false;
        $this->use_pure_http = false;
    }
    static function decodeJZONReq($jzon, $ref)
    {
        $res = array();
        $ofs = 0;
        if (is_array($ref)) {
            foreach ($ref as $key => $value) {
                if (key_exists($key, $jzon)) {
                    $res[$key] = self::decodeJZONReq($jzon[$key], $value);
                } else if (isset($jzon[$ofs])) {
                    $res[$key] = self::decodeJZONReq($jzon[$ofs], $value);
                }
                $ofs++;
            }
            return $res;
        }
        return $jzon;
    }
    static function decodeJZONService($jzon, $ref)
    {
        $wp = array();
        $yp = array();
        foreach($jzon[0] as $wp_entry) {
            $wp[] = self::decodeJZONReq($wp_entry, $ref['whitePages'][0]);
        }
        $yp_entry_ref = $ref['yellowPages'][array_key_first($ref['yellowPages'])][0];
        foreach($jzon[1] as $yp_type => $yp_entries) {
            $yp[$yp_type] = array();
            foreach($yp_entries as $yp_entry) {
                $yp[$yp_type][] = self::decodeJZONReq($yp_entry, $yp_entry_ref);
            }
        }
        return ['whitePages' => $wp, 'yellowPages'=>$yp];
    }
    static function decodeJZON($jzon, $ref)
    {
        $decoded = self::decodeJZONReq($jzon, $ref);
        if (array_key_exists('services', $ref)) {
            $ofs = sizeof($jzon) - 1;
            if(isset($jzon[$ofs])) {
                $decoded['services'] = self::decodeJZONService($jzon[$ofs], $ref['services']);
            }
        }
        return $decoded;
    }
    static function cleanJsonRef($ref)
    {
        $res = array();
        foreach ($ref as $key => $value) {
            if (is_array($value)) {
                $res[$key] = self::cleanJsonRef($value);
            } else if ($key == "serialNumber") {
                $res[$key] = substr($value, 0, YOCTO_BASE_SERIAL_LEN);
            } else if ($key == "firmwareRelease") {
                $res[$key] = $value;
            } else {
                $res[$key] = "";
            }
        }
        return $res;
    }
    function verfiyStreamAddr($fullTest = true, &$errmsg = '')
    {
        if ($this->streamaddr == 'tcp://CALLBACK') {
            if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
                $errmsg = "invalid request method";
                $this->callbackCache = Array();
                return YAPI_IO_ERROR;
            }
            if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/json') {
                $errmsg = "invalid content type";
                $this->callbackCache = Array();
                return YAPI_IO_ERROR;
            }
            if (!isset($_SERVER['HTTP_USER_AGENT'])) {
                $errmsg = "not agent provided";
                $this->callbackCache = Array();
                return YAPI_IO_ERROR;
            }
            $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $patern = 'yoctohub';
            if ($useragent != 'virtualhub' && substr($useragent, 0, strlen($patern)) != $patern) {
                $errmsg = "no user agent provided";
                $this->callbackCache = Array();
                return YAPI_IO_ERROR;
            }
            if ($fullTest) {
                if(isset($_SERVER['HTTP_RAW_POST_DATA'])) {
                    $data = $_SERVER['HTTP_RAW_POST_DATA'];
                } else {
                    $data = file_get_contents('php://input');
                }
                $this->callbackData = $data;
                if ($data == "") {
                    $errmsg = "RegisterHub(callback) used without posting YoctoAPI data";
                    Print("\n!YoctoAPI:$errmsg\n");
                    $this->callbackCache = Array();
                    return YAPI_IO_ERROR;
                } else {
                    if(isset($_SERVER['HTTP_JSON_POST_DATA'])) {
                        $this->callbackCache = $_SERVER['HTTP_JSON_POST_DATA'];
                    } else {
                        $utf8_encode = utf8_encode($data);
                        $this->callbackCache = json_decode($utf8_encode, true);
                    }
                    if (is_null($this->callbackCache)) {
                        $errmsg = "invalid data:[\n$data\n]";
                        Print("\n!YoctoAPI:$errmsg\n");
                        $this->callbackCache = Array();
                        return YAPI_IO_ERROR;
                    }
                    if ($this->pwd != '') {
                        // callback data signed, verify signature
                        if (!isset($this->callbackCache['sign'])) {
                            $errmsg = "missing signature from incoming YoctoHub (callback password required)";
                            Print("\n!YoctoAPI:$errmsg\n");
                            $this->callbackCache = Array();
                            return YAPI_UNAUTHORIZED;
                        }
                        $sign = $this->callbackCache['sign'];
                        $salt = $this->pwd;
                        if (strlen($salt) != 32) $salt = md5($salt);
                        $data = str_replace($sign, strtolower($salt), $data);
                        $check = strtolower(md5($data));
                        if ($check != $sign) {
                            //Print("Computed signature: $check\n");
                            //Print("Received signature: $sign\n");
                            $errmsg = "invalid signature from incoming YoctoHub (invalid callback password)";
                            Print("\n!YoctoAPI:$errmsg\n");
                            $this->callbackCache = Array();
                            return YAPI_UNAUTHORIZED;
                        }
                    }
                    if (isset($this->callbackCache['serial']) && !is_null(YAPI::$_jzonCacheDir)) {
                        $jzonCacheDir = YAPI::$_jzonCacheDir;
                        $mergedCache = array();
                        $upToDate = true;
                        foreach ($this->callbackCache as $req => $value) {
                            $pos = strpos($req, "/api.json");
                            if ($pos !== False) {
                                $fwpos = strpos($req, "?fw=", $pos);
                                $isJZON = false;
                                if ($fwpos !== False) {
                                    if (key_exists('module', $value)) {
                                        // device did not return JZON (probably due to fw update)
                                        $req = substr($req, 0, $fwpos);
                                    } else {
                                        $isJZON = true;
                                    }
                                }
                                if ($isJZON) {
                                    if ($pos == 0) {
                                        $serial = $this->callbackCache['serial'];
                                    } else {
                                        // "/bySerial/" = 10 chars
                                        $serial = substr($req, 10, $pos - 10);
                                    }
                                    $firm = str_replace([' ',':'], '_', substr($req, $fwpos + 4));
                                    $base = substr($serial, 0, YOCTO_BASE_SERIAL_LEN);
                                    if (!is_file("{$jzonCacheDir}{$base}_{$firm}.json")) {
                                        $errmsg = "No JZON reference file for {$serial}/{$firm}";
                                        Print("\n!YoctoAPI:$errmsg\n");
                                        $this->callbackCache = Array();
                                        Print("\n@YoctoAPI:#!noref\n");
                                        return YAPI_IO_ERROR;
                                    }
                                    $ref = file_get_contents("{$jzonCacheDir}{$base}_{$firm}.json");
                                    $ref = json_decode($ref, true);
                                    $decoded = self::decodeJZON($value, $ref);
                                    if ($ref['module']['firmwareRelease'] != $decoded['module']['firmwareRelease']) {
                                        $errmsg = "invalid JZON data";
                                        Print("\n!YoctoAPI:$errmsg\n");
                                        $this->callbackCache = Array();
                                        Print("\n@YoctoAPI:#!invalid\n");
                                        return YAPI_IO_ERROR;
                                    }
                                    $req = substr($req, 0, $fwpos);
                                    $mergedCache[$req] = $decoded;
                                    //Print("Use jzon data for {$serial}/{$firm}\n");
                                } else {
                                    $serial = $value['module']['serialNumber'];
                                    $base = substr($serial, 0, YOCTO_BASE_SERIAL_LEN);
                                    $firm = str_replace([' ',':'], '_', $value['module']['firmwareRelease']);
                                    $clean_struct = self::cleanJsonRef($value);
                                    file_put_contents("{$jzonCacheDir}{$base}_{$firm}.json", json_encode($clean_struct));
                                    $mergedCache[$req] = $value;
                                    Print("\n@YoctoAPI:#{$serial}/{$firm}\n");
                                    $upToDate = false;
                                }
                            } else {
                                $mergedCache[$req] = $value;
                            }
                        }
                        if ($upToDate) {
                            Print("\n@YoctoAPI:#=\n");
                        }
                        $this->callbackCache = $mergedCache;
                    }
                    // decode binary content
                    foreach ($this->callbackCache as $url => $data) {
                        if (!is_string($data)){
                            continue;
                        }
                        $len = strlen($url);
                        if ($len > 2 && substr($url, $len - 2) === '.#') {
                            $this->callbackCache[substr($url,0, $len-2)] = base64_decode($data);
                        }
                    }
                }
            }
        } else {
            $info_json_url = $this->rooturl.$this->url_info['subdomain'].'/info.json';
            $info_json = @file_get_contents($info_json_url);
            $jsonData = json_decode($info_json, true);
            if ($jsonData != null &&array_key_exists('protocol', $jsonData) && $jsonData['protocol'] =='HTTP/1.1') {
                $this->use_pure_http = true;
            }
            $this->callbackCache = NULL;
        }
        return 0;
    }
    // Update the hub internal variables according
    // to a received header with WWW-Authenticate
    function parseWWWAuthenticate($header)
    {
        $pos = stripos($header, "\r\nWWW-Authenticate:");
        if ($pos === false) return;
        $header = substr($header, $pos + 19);
        $eol = strpos($header, "\r");
        if ($eol !== false) {
            $header = substr($header, 0, $eol);
        }
        $tags = null;
        if (preg_match_all('~(?<tag>\w+)="(?<value>[^"]*)"~m', $header, $tags) == false) {
            return;
        }
        $this->realm = '';
        $this->qop = '';
        $this->nonce = '';
        $this->opaque = '';
        for ($i = 0; $i < sizeof($tags['tag']); $i++) {
            if ($tags['tag'][$i] == "realm") {
                $this->realm = $tags['value'][$i];
            } else if ($tags['tag'][$i] == "qop") {
                $this->qop = $tags['value'][$i];
            } else if ($tags['tag'][$i] == "nonce") {
                $this->nonce = $tags['value'][$i];
            } else if ($tags['tag'][$i] == "opaque") {
                $this->opaque = $tags['value'][$i];
            }
        }
        $this->nc = 0;
        $this->ha1 = md5($this->user . ':' . $this->realm . ':' . $this->pwd);
    }
    // Return an Authorization header for a given request
    function getAuthorization($request)
    {
        if ($this->user == '' || $this->realm == '') return '';
        $this->nc++;
        $pos = strpos($request, ' ');
        $method = substr($request, 0, $pos);
        $uri = substr($request, $pos + 1);
        $nc = sprintf("%08x", $this->nc);
        $cnonce = sprintf("%08x", mt_rand(0, 0x7fffffff));
        $ha1 = $this->ha1;
        $ha2 = md5("{$method}:{$uri}");
        $nonce = $this->nonce;
        $response = md5("{$ha1}:{$nonce}:{$nc}:{$cnonce}:auth:{$ha2}");
        $res = 'Authorization: Digest username="' . $this->user . '", realm="' . $this->realm . '",' .
            ' nonce="' . $this->nonce . '", uri="' . $uri . '", qop=auth, nc=' . $nc . ',' .
            ' cnonce="' . $cnonce . '", response="' . $response . '", opaque="' . $this->opaque . '"';
        return "$res\r\n";
    }
    // Return true if a hub is just a virtual cache (for callback mode)
    function isCachedHub()
    {
        return !is_null($this->callbackCache);
    }
    // Execute a query for cached hub (for callback mode)
    function cachedQuery($str_query, $str_body)
    {
        // apply POST remotely
        if (substr($str_query, 0, 5) == 'POST ') {
            $boundary = '???';
            $endb = strpos($str_body, "\r");
            if (substr($str_body, 0, 2) == '--' && $endb > 2 && $endb < 20) {
                $boundary = substr($str_body, 2, $endb - 2);
            }
            Printf("\n@YoctoAPI:$str_query %d:%s\n%s", strlen($str_body), $boundary, $str_body);
            return "OK\r\n\r\n";
        }
        if (substr($str_query, 0, 4) != 'GET ')
            return NULL;
        // remove JZON trigger if present (not relevant in callback mode)
        $jzon = strpos($str_query, '?fw=');
        if ($jzon !== FALSE && strpos($str_query, '&', $jzon) === FALSE) {
            $str_query = substr($str_query, 0, $jzon);
        }
        // dispatch between cached get and remote set
        if (strpos($str_query, '?') === FALSE ||
            strpos($str_query, '/@YCB+') !== FALSE ||
            strpos($str_query, '/logs.txt') !== FALSE ||
            strpos($str_query, '/tRep.bin') !== FALSE ||
            strpos($str_query, '/logger.json') !== FALSE ||
            strpos($str_query, '/ping.txt') !== FALSE ||
            strpos($str_query, '/files.json?a=dir') !== FALSE) {
            // read request, load from cache
            $parts = explode(' ', $str_query);
            $url = $parts[1];
            $getmodule = (strpos($url, 'api/module.json') !== FALSE);
            if ($getmodule) {
                $url = str_replace('api/module.json', 'api.json', $url);
            }
            if (!isset($this->callbackCache[$url])) {
                if ($url == "/api.json") {
                    // /api.json is not present in cache. Report an error to force the hub
                    // to switch back to json encoding
                    print("\n!YoctoAPI:$url is in cache. Disable JZON encoding");
                    Print("\n@YoctoAPI:#!invalid\n");
                    return NULL;
                }
                if (strpos($url,"@YCB+")!== false) {
                    // file has be requested by addFileToHTTPCallback
                    $url = str_replace('@YCB+', '',$url);
                    Print("\n@YoctoAPI:+$url\n");
                    return "OK\r\n\r\n";
                }else {
                    print("\n!YoctoAPI:$url is not preloaded, adding to list");
                    Print("\n@YoctoAPI:+$url\n");
                    return NULL;
                }
            }
            // Print("\n[$url found]\n");
            $jsonres = $this->callbackCache[$url];
            if ($getmodule) $jsonres = $jsonres['module'];
            if (strpos($str_query, '.json') !== FALSE) {
                $jsonres = json_encode($jsonres);
            }
            return "OK\r\n\r\n$jsonres";
        } else {
            // change request, print to output stream
            Print("\n@YoctoAPI:$str_query \n");
            return "OK\r\n\r\n";
        }
    }
}
//
// YTcpReq Class (used internally)
//
// Instances of this class represent an open TCP connection to a HTTP socket.
// The class handles digest authorization transparently.
//
class YTcpReq
{
    // attributes
    public $hub;                        // the YTcpHub to which we connect
    public $async;                      // true if the request is async
    public $skt;                        // stream socket
    public $request;                    // request to be sent
    public $reqbody;                    // request body to send, if any
    public $boundary;                   // request body boundary, if used
    public $meta;                       // HTTP headers received in reply
    public $reply;                      // reply buffer
    public $retryCount;                 // number of retries for this request
    // the following attributes should not be taken for granted unless eof() returns true
    public $errorType;                  // status of current connection
    public $errorMsg;                   // last error message
    public $reqcnt;
    public static $totalTcpReqs = 0;
    function __construct($hub, $request, $async, $reqbody = '', $mstimeout = YAPI_BLOCKING_REQUEST_TIMEOUT)
    {
        $pos = strpos($request, "\r");
        if ($pos !== false) {
            $request = substr($request, 0, $pos);
        }
        $boundary = '';
        if ($reqbody != '') {
            do {
                $boundary = sprintf("Zz%06xzZ", mt_rand(0, 0xffffff));
            } while (strpos($reqbody, $boundary) !== false);
            $reqbody = "--{$boundary}\r\n{$reqbody}\r\n--{$boundary}--\r\n";
        }
        $this->hub = $hub;
        $this->async = $async;
        $this->request = trim($request);
        $this->reqbody = $reqbody;
        $this->boundary = $boundary;
        $this->meta = '';
        $this->reply = '';
        $this->retryCount = 0;
        $this->mstimeout = $mstimeout;
        $this->errorType = YAPI_IO_ERROR;
        $this->errorMsg = 'could not open connection';
        $this->reqcnt = ++YTcpReq::$totalTcpReqs;
    }
    function eof()
    {
        if (!is_null($this->skt)) {
            // there is still activity going on
            return false;
        }
        if ($this->meta != '' && $this->errorType == YAPI_SUCCESS) {
            // connection was done and ended successfully
            // check we need to unchunk the response
            $t_ofs = strpos($this->meta,"Transfer-Encoding");
            if ($t_ofs > 0) {
                $t_ofs += 17;
                $t_endl = strpos($this->meta,"\r\n", $t_ofs);
                $t_chunk = strpos($this->meta,"chunked", $t_ofs);
                if ($t_chunk!==false  && $t_endl!==false && $t_chunk < $t_endl) {
                    // chuck encoded
                    $new = $this->http_chunked_decode($this->reply);
                    $this->reply = $new;
                    $this->meta= substr($this->meta, 0 , $t_ofs). substr($this->meta,$t_endl+2);
                }
            }
            return true;
        }
        if ($this->retryCount > 3) {
            // connection permanently failed
            return true;
        }
        // connection is expected to be reopened
        return false;
    }
    function http_chunked_decode($data) {
        $data_length = strlen($data);
        $dechunk = '';
        $ofs = 0;
        do {
            $hexstr = '';
            while ($ofs < $data_length && ($c = $data[$ofs]) != "\n") {
                if (($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'F') || ($c >= 'a' && $c <= 'f')) {
                    $hexstr.=$c;
                }
                $ofs++;
            }
            if ($ofs < $data_length){
                $len = hexdec($hexstr);
                if ($ofs + 3 + $len < $data_length) {
                    $ofs++;
                    $dechunk .= substr($data,$ofs, $len);
                    $ofs+=2;
                } else{
                    $ofs+=1;
                }
            }
        } while($ofs < $data_length);
        return $dechunk;
    }
    function newsocket(&$errno, &$errstr, $mstimeout)
    {
        // for now, use client socket only since server sockets
        // for callbacks are not reliably available on a public server
        $addr = $this->hub->streamaddr;
        $pos = strpos($addr, '/', 9);
        if ($pos !== FALSE) {
            $addr = substr($addr, 0, $pos);
        }
        return @stream_socket_client($addr, $errno, $errstr, $mstimeout / 1000);
    }
    function process(&$errmsg = '')
    {
        if ($this->eof()) {
            if ($this->errorType != YAPI_SUCCESS) {
                $errmsg = $this->errorMsg;
            }
            return $this->errorType;
        }
        if (!is_null($this->skt) && !is_resource($this->skt)) {
            // connection died, need to reopen
            $this->skt = null;
        }
        if (is_null($this->skt)) {
            // need to reopen connection
            if ($this->hub->isCachedHub()) {
                // special handling for "connection-less" callback mode
                $data = $this->hub->cachedQuery($this->request, $this->reqbody);
                if (is_null($data)) {
                    $this->errorType = YAPI_NOT_SUPPORTED;
                    $this->errorMsg = "query is not available in callback mode";
                    $this->retryCount = 99;
                    return YAPI_SUCCESS; // will propagate error later if needed
                }
                $skt = fopen('data:text/plain;base64,' . base64_encode($data), 'rb');
                if ($skt === false) {
                    $this->errorType = YAPI_IO_ERROR;
                    $this->errorMsg = "failed to open data stream";
                    $this->retryCount = 99;
                    return YAPI_SUCCESS; // will propagate error later if needed
                }
                stream_set_blocking($skt, false);
                $this->skt = $skt;
            } else {
                $skt = null;
                if (!is_null($this->hub->reuseskt)) {
                    $skt = $this->hub->reuseskt;
                    $this->hub->reuseskt = null;
                    if (!is_resource($skt)) {
                        // reusable socket is no more valid
                        $skt = null;
                    }
                }
                if (is_null($skt)) {
                    $errno = 0;
                    $errstr = '';
                    $skt = $this->newsocket($errno, $errstr, $this->mstimeout);
                    if ($skt === false) {
                        $this->errorType = YAPI_IO_ERROR;
                        $this->errorMsg = "failed to open socket ($errno): $errstr";
                        $this->retryCount++;
                        return YAPI_SUCCESS; // will retry later
                    }
                }
                stream_set_blocking($skt, false);
                $request = $this->format_request();
                $reqlen = strlen($request);
                if (fwrite($skt, $request, $reqlen) != $reqlen) {
                    fclose($skt);
                    $this->errorType = YAPI_IO_ERROR;
                    $this->errorMsg = "failed to write to socket";
                    $this->retryCount++;
                    return YAPI_SUCCESS; // will retry later
                }
                $this->skt = $skt;
            }
        } else {
            // read anything available on current socket, and process authentication headers
            while (true) {
                $data = fread($this->skt, 8192);
                if ($data === false) {
                    $this->errorType = YAPI_IO_ERROR;
                    $this->errorMsg = "failed to read from socket";
                    $this->retryCount++;
                    return YAPI_SUCCESS; // will retry later
                }
                //Printf("[read %d bytes]\n",strlen($data));
                if (strlen($data) == 0) break;
                if ($this->reply == '' && strpos($this->meta, "\r\n\r\n") === false) {
                    $this->meta .= $data;
                    $eoh = strpos($this->meta, "\r\n\r\n");
                    if ($eoh !== false) {
                        // fully received header
                        $this->reply = substr($this->meta, $eoh + 4);
                        $this->meta = substr($this->meta, 0, $eoh + 4);
                        $firstline = substr($this->meta, 0, strpos($this->meta, "\r"));
                        if (substr($firstline, 0, 12) == 'HTTP/1.1 401') {
                            // authentication required
                            $this->errorType = YAPI_UNAUTHORIZED;
                            $this->errorMsg = "Authentication required";
                            fclose($this->skt);
                            $this->skt = null;
                            $this->hub->parseWWWAuthenticate($this->meta);
                            if ($this->hub->user != '') {
                                $this->meta = '';
                                $this->reply = '';
                                $this->retryCount++;
                            } else {
                                $this->retryCount = 99;
                            }
                            return YAPI_SUCCESS; // will propagate error later if needed
                        }
                    }
                } else {
                    $this->reply .= $data;
                }
                // so far so good
                $this->errorType = YAPI_SUCCESS;
            }
            // write request body, if any, once header is fully received
            if ($this->reqbody != '' && strpos($this->meta, "\r\n\r\n") !== false) {
                $bodylen = strlen($this->reqbody);
                $written = fwrite($this->skt, $this->reqbody, $bodylen);
                if ($written > 0) {
                    $this->reqbody = substr($this->reqbody, $written);
                }
            }
            if (!is_resource($this->skt)) {
                // socket dropped dead
                $this->skt = null;
            } else if (feof($this->skt)) {
                fclose($this->skt);
                $this->skt = null;
            } else if ($this->meta == "0K\r\n\r\n" && $this->reply == "\r\n") {
                if (is_null($this->hub->reuseskt)) {
                    $this->hub->reuseskt = $this->skt;
                } else {
                    fclose($this->skt);
                }
                $this->skt = null;
            }
        }
        return YAPI_SUCCESS;
    }
    function  format_request()
    {
        $parts = explode(' ', $this->request);
        if (sizeof($parts)==2) {
            $req = $parts[0] . ' ' . $this->hub->url_info['subdomain'] . $parts[1];
        }else{
            $req = $this->request;
        }
        if ($this->hub->use_pure_http) {
            $request = $req . " HTTP/1.1\r\n";
            $host = $this->hub->url_info['host'];
            $request .= "Host: " . $host . "\r\n";
        }else {
            $request = $req. " \r\n"; // no HTTP/1.1 suffix for light queries
        }
        $request.=$this->hub->getAuthorization($req);
        if ($this->boundary != '') {
            $request .= "Content-Type: multipart/form-data; boundary={$this->boundary}\r\n";
        }
        if (substr($this->request, -2) == "&." && !$this->hub->use_pure_http) {
            $request .= "\r\n";
        } else {
            $request .= "Connection: close\r\n\r\n";
        }
        return $request;
    }
    function close()
    {
        if ($this->skt) fclose($this->skt);
    }
}
//
// YFunctionType Class (used internally)
//
// Instances of this class stores everything we know about a given type of function:
// Mapping between function logical names and Hardware ID as discovered on hubs,
// and existing instances of YFunction (either already connected or simply requested).
// To keep it simple, this implementation separates completely the name resolution
// mechanism, implemented using the yellow pages, and the storage and retrieval of
// existing YFunction instances.
//
class YFunctionType
{
    // private attributes, to be used within yocto_api only
    protected $_className;
    protected $_connectedFns;           // functions requested and available, by Hardware Id
    protected $_requestedFns;           // functions requested but not yet known, by any type of name
    protected $_hwIdByName;             // hash table of function Hardware Id by logical name
    protected $_nameByHwId;             // hash table of function logical name by Hardware Id
    protected $_valueByHwId;            // hash table of function advertised value by logical name
    protected $_baseType;               // default to no abstract base type (generic YFunction)
    function __construct($str_classname)
    {
        if (ord($str_classname[strlen($str_classname) - 1]) <= 57) throw new Exception("Invalid function type", -1);
        $this->_className = $str_classname;
        $this->_connectedFns = Array();
        $this->_requestedFns = Array();
        $this->_hwIdByName = Array();
        $this->_nameByHwId = Array();
        $this->_valueByHwId = Array();
        $this->_baseType = 0;
    }
    // Index a single function given by HardwareId and logical name; store any advertised value
    // Return true iff there was a logical name discrepency
    public function reindexFunction($str_hwid, $str_name, $str_val, $int_basetype)
    {
        $currname = '';
        $res = false;
        if (isset($this->_nameByHwId[$str_hwid])) {
            $currname = $this->_nameByHwId[$str_hwid];
        }
        if ($currname == '') {
            if ($str_name != '') {
                $this->_nameByHwId[$str_hwid] = $str_name;
                $res = true;
            }
        } else if ($currname != $str_name) {
            if ($this->_hwIdByName[$currname] == $str_hwid)
                unset($this->_hwIdByName[$currname]);
            if ($str_name != '') {
                $this->_nameByHwId[$str_hwid] = $str_name;
            } else {
                unset($this->_nameByHwId[$str_hwid]);
            }
            $res = true;
        }
        if ($str_name != '') {
            $this->_hwIdByName[$str_name] = $str_hwid;
        }
        if (!is_null($str_val)) {
            $this->_valueByHwId[$str_hwid] = $str_val;
        } else {
            if (!isset($this->_valueByHwId[$str_hwid])) {
                $this->_valueByHwId[$str_hwid] = '';
            }
        }
        if (!is_null($int_basetype)) {
            if ($this->_baseType == 0) {
                $this->_baseType = $int_basetype;
            }
        }
        return $res;
    }
    // Forget a disconnected function given by HardwareId
    public function forgetFunction($str_hwid)
    {
        if (isset($this->_nameByHwId[$str_hwid])) {
            $currname = $this->_nameByHwId[$str_hwid];
            if ($currname != '' && $this->_hwIdByName[$currname] == $str_hwid) {
                unset($this->_hwIdByName[$currname]);
            }
            unset($this->_nameByHwId[$str_hwid]);
        }
        if (isset($this->_valueByHwId[$str_hwid])) {
            unset($this->_valueByHwId[$str_hwid]);
        }
    }
    // Find the exact Hardware Id of the specified function, if currently connected
    // If device is not known as connected, return a clean error
    // This function will not cause any network access
    public function resolve($str_func)
    {
        // Try to resolve str_func to a known Function instance, if possible, without any device access
        $dotpos = strpos($str_func, '.');
        if ($dotpos === false) {
            // First case: str_func is the logicalname of a function
            if (isset($this->_hwIdByName[$str_func])) {
                return new YAPI_YReq($this->_hwIdByName[$str_func],
                    YAPI_SUCCESS,
                    'no error',
                    $this->_hwIdByName[$str_func]);
            }
            // fallback to assuming that str_func is a logicalname or serial number of a module
            // with an implicit function name (like serial.module for instance)
            $dotpos = strlen($str_func);
            $str_func .= '.' . strtolower($this->_className[0]) . substr($this->_className, 1);
        }
        // Second case: str_func is in the form: device_id.function_id
        // quick lookup for a known pure hardware id
        if (isset($this->_valueByHwId[$str_func])) {
            return new YAPI_YReq($this->_valueByHwId[$str_func],
                YAPI_SUCCESS,
                'no error',
                $str_func);
        }
        if ($dotpos > 0) {
            // either the device id is a logical name, or the function is unknown
            $devid = substr($str_func, 0, $dotpos);
            $funcid = substr($str_func, $dotpos + 1);
            $dev = YAPI::getDevice($devid);
            if (!$dev) {
                return new YAPI_YReq($str_func,
                    YAPI_DEVICE_NOT_FOUND,
                    "Device [$devid] not online",
                    null);
            }
            $serial = $dev->getSerialNumber();
            $res = "$serial.$funcid";
            if (isset($this->_valueByHwId[$res])) {
                return new YAPI_YReq($res,
                    YAPI_SUCCESS,
                    'no error',
                    $res);
            }
            // not found neither, may be funcid is a function logicalname
            $nfun = $dev->functionCount();
            for ($i = 0; $i < $nfun; $i++) {
                $res = "$serial." . $dev->functionId($i);
                if (isset($this->_nameByHwId[$res])) {
                    $name = $this->_nameByHwId[$res];
                    if ($name == $funcid) {
                        return new YAPI_YReq($res,
                            YAPI_SUCCESS,
                            'no error',
                            $res);
                    }
                }
            }
        } else {
            $serial = '';
            $funcid = substr($str_func, 1);
            // only functionId  (ie ".temperature")
            foreach (array_keys($this->_connectedFns) as $hwid_str) {
                $pos = strpos($hwid_str, '.');
                $function = substr($hwid_str, $pos + 1);
                //print("search for $funcid in {$this->_className} $function\n");
                if ($function == $funcid) {
                    return new YAPI_YReq($hwid_str,
                        YAPI_SUCCESS,
                        'no error',
                        $hwid_str);
                }
            }
        }
        return new YAPI_YReq("$serial.$funcid",
            YAPI_DEVICE_NOT_FOUND,
            "No function [$funcid] found on device [$serial]",
            null);
    }
    public function getFriendlyName($str_func)
    {
        $resolved = $this->resolve($str_func);
        if ($resolved->errorType != YAPI_SUCCESS) {
            return $resolved;
        }
        if ($this->_className == "Module") {
            $friend = $resolved->result;
            if (isset($this->_nameByHwId[$resolved->result]))
                $friend = $this->_nameByHwId[$resolved->result];
            return new YAPI_YReq($resolved->result,
                YAPI_SUCCESS,
                'no error',
                $friend);
        } else {
            $pos = strpos($resolved->result, '.');
            $serial_mod = substr($resolved->result, 0, $pos);
            $friend_mod_full = YAPI::getFriendlyNameFunction("Module", $serial_mod)->result;
            $friend_mod_dot = strpos($friend_mod_full, '.');
            $friend_mod = ($friend_mod_dot ? substr($friend_mod_full, 0, $friend_mod_dot) : $friend_mod_full);
            $friend_func = substr($resolved->result, $pos + 1);
            if (isset($this->_nameByHwId[$resolved->result]) && $this->_nameByHwId[$resolved->result] != '')
                $friend_func = $this->_nameByHwId[$resolved->result];
            return new YAPI_YReq($resolved->result,
                YAPI_SUCCESS,
                'no error',
                $friend_mod . '.' . $friend_func);
        }
    }
    // Retrieve a function object by hardware id, updating the indexes on the fly if needed
    public function setFunction($str_func, $obj_func)
    {
        $funres = $this->resolve($str_func);
        if ($funres->errorType == YAPI_SUCCESS) {
            // the function has been located on a device
            $this->_connectedFns[$funres->result] = $obj_func;
        } else {
            // the function is still abstract
            $this->_requestedFns[$str_func] = $obj_func;
        }
    }
    // Retrieve a function object by hardware id, updating the indexes on the fly if needed
    public function getFunction($str_func)
    {
        $funres = $this->resolve($str_func);
        if ($funres->errorType == YAPI_SUCCESS) {
            // the function has been located on a device
            if (isset($this->_connectedFns[$funres->result]))
                return $this->_connectedFns[$funres->result];
            if (isset($this->_requestedFns[$str_func])) {
                $req_fn = $this->_requestedFns[$str_func];
                $this->_connectedFns[$funres->result] = $req_fn;
                unset($this->_requestedFns[$str_func]);
                return $req_fn;
            }
        } else {
            // the function is still abstract
            if (isset($this->_requestedFns[$str_func]))
                return $this->_requestedFns[$str_func];
        }
        return null;
    }
    // Stores a function advertised value by hardware id, queue an event if needed
    public function setFunctionValue($str_hwid, $str_pubval)
    {
        if (isset($this->_valueByHwId[$str_hwid]) &&
            $this->_valueByHwId[$str_hwid] == $str_pubval) {
            return;
        }
        $this->_valueByHwId[$str_hwid] = $str_pubval;
        foreach (YFunction::$_ValueCallbackList as $fun) {
            $hwId = $fun->_getHwId();
            if (!$hwId) continue;
            if ($hwId == $str_hwid) {
                YAPI::addValueEvent($fun, $str_pubval);
            }
        }
    }
    // Retrieve a function advertised value by hardware id
    public function getFunctionValue($str_hwid)
    {
        return $this->_valueByHwId[$str_hwid];
    }
    // Stores a function advertised value by hardware id, queue an event if needed
    public function setTimedReport($str_hwid, $float_timestamp, $float_duration, $arr_report)
    {
        foreach (YFunction::$_TimedReportCallbackList as $fun) {
            $hwId = $fun->_getHwId();
            if (!$hwId) continue;
            if ($hwId == $str_hwid) {
                YAPI::addTimedReportEvent($fun, $float_timestamp, $float_duration, $arr_report);
            }
        }
    }
    // Return the basetype of this function class
    public function getBaseType()
    {
        return $this->_baseType;
    }
    public function matchBaseType($baseType)
    {
        if ($baseType == 0)
            return true;
        return $this->_baseType == $baseType;
    }
    // Find the the hardwareId of the first instance of a given function class
    public function getFirstHardwareId()
    {
        foreach (array_keys($this->_valueByHwId) as $res) {
            return $res;
        }
        return null;
    }
    // Find the hardwareId for the next instance of a given function class
    public function getNextHardwareId($str_hwid)
    {
        foreach (array_keys($this->_valueByHwId) as $iter_hwid) {
            if ($str_hwid == "!")
                return $iter_hwid;
            if ($str_hwid == $iter_hwid)
                $str_hwid = "!";
        }
        return null; // no more instance found
    }
}
//
// YDevice Class (used internally)
//
// This class is used to store everything we know about connected Yocto-Devices.
// Instances are created when devices are discovered in the white pages
// (or registered manually, for root hubs) and then used to keep track of
// device naming changes. When a device or a function is renamed, this
// object forces the local indexes to be immediately updated, even if not
// yet fully propagated through the yellow pages of the device hub.
//
// In order to regroup multiple function queries on the same physical device,
// this class implements a device-wide API string cache (agnostic of API content).
// This is in addition to the function-specific cache implemented in YFunction.
//
class YDevice
{
    // private attributes, to be used within yocto_api only
    protected $_rootUrl;
    protected $_serialNumber;
    protected $_logicalName;
    protected $_productName;
    protected $_productId;
    protected $_lastTimeRef;
    protected $_lastDuration;
    protected $_beacon;
    protected $_deviceTime;
    protected $_devYdx;
    protected $_cache;
    protected $_functions;
    protected $_ongoingReq;
    public    $_lastErrorType;
    public    $_lastErrorMsg;
    private   $_logNeedPulling;
    private   $_logIsPulling;
    private   $_logCallback;
    private   $_logpos;
    function __construct($str_rooturl, $obj_wpRec = null, $obj_ypRecs = null)
    {
        $this->_rootUrl = $str_rooturl;
        $this->_serialNumber = '';
        $this->_logicalName = '';
        $this->_productName = '';
        $this->_productId = 0;
        $this->_beacon = 0;
        $this->_devYdx = -1;
        $this->_cache = Array('_expiration' => 0, '_json' => '');
        $this->_functions = Array();
        $this->_lastErrorType = YAPI_SUCCESS;
        $this->_lastErrorMsg = 'no error';
        if (!is_null($obj_wpRec)) {
            // preload values from white pages, if provided
            $this->_serialNumber = $obj_wpRec['serialNumber'];
            $this->_logicalName = $obj_wpRec['logicalName'];
            $this->_productName = $obj_wpRec['productName'];
            $this->_productId = $obj_wpRec['productId'];
            $this->_beacon = $obj_wpRec['beacon'];
            $this->_devYdx = (isset($obj_wpRec['index']) ? $obj_wpRec['index'] : -1);
            $this->_updateFromYP($obj_ypRecs);
            YAPI::reindexDevice($this);
        } else {
            // preload values from device directly
            $this->refresh();
        }
    }
    // Throw an exception, keeping track of it in the object itself
    protected function _throw($int_errType, $str_errMsg, $obj_retVal)
    {
        $this->_lastErrorType = $int_errType;
        $this->_lastErrorMsg = $str_errMsg;
        if (YAPI::$exceptionsDisabled) {
            return $obj_retVal;
        }
        // throw an exception
        throw new YAPI_Exception($str_errMsg, $int_errType);
    }
    // Update device cache and YAPI function lists from yp records
    protected function _updateFromYP($obj_ypRecs)
    {
        $funidx = 0;
        foreach ($obj_ypRecs as $ypRec) {
            foreach ($ypRec as $rec) {
                $hwid = $rec['hardwareId'];
                $dotpos = strpos($hwid, '.');
                if (substr($hwid, 0, $dotpos) == $this->_serialNumber) {
                    if (isset($rec['index'])) {
                        $funydx = $rec['index'];
                    } else {
                        $funydx = $funidx;
                    }
                    $this->_functions[$funydx] = Array(substr($hwid, $dotpos + 1), $rec["logicalName"]);
                }
            }
        }
    }
    // Return the root URL used to access a device (including the trailing slash)
    public function getRootUrl()
    {
        return $this->_rootUrl;
    }
    // Return the serial number of the device, as found during discovery
    public function getSerialNumber()
    {
        return $this->_serialNumber;
    }
    // Return the logical name of the device, as found during discovery
    public function getLogicalName()
    {
        return $this->_logicalName;
    }
    // Return the product name of the device, as found during discovery
    public function getProductName()
    {
        return $this->_productName;
    }
    // Return the product Id of the device, as found during discovery
    public function getProductId()
    {
        return $this->_productId;
    }
    // Return the beacon state of the device, as found during discovery
    public function getBeacon()
    {
        return $this->_beacon;
    }
    public function getLastTimeRef()
    {
        return $this->_lastTimeRef;
    }
    public function getLastDuration()
    {
        return $this->_lastDuration;
    }
    public function setTimeRef($float_timestamp, $float_duration)
    {
        $this->_lastTimeRef = $float_timestamp;
        $this->_lastDuration = $float_duration;
    }
    public function triggerLogPull()
    {
        if ($this->_logCallback == null || $this->_logIsPulling) {
            return;
        }
        $this->_logIsPulling = true;
        $request = "GET logs.txt?pos=" . $this->_logpos;
        $yreq = YAPI::devRequest($this->_rootUrl, $request);
        if ($yreq->errorType != YAPI_SUCCESS) return;
        if ($this->_logCallback == null) {
            $this->_logIsPulling = false;
            return;
        }
        $resultStr = iconv("ISO-8859-1", "UTF-8", $yreq->result);
        $pos = strrpos($resultStr, "\n@");
        if ($pos < 0) {
            $this->_logIsPulling = false;
            return;
        }
        $logs = substr($resultStr, 0, $pos);
        if (strlen($logs) > 0) {
            $posStr = substr($resultStr, $pos + 2);
            $this->_logpos = (int)$posStr;
            $module = YModule::FindModule($this->_serialNumber . ".module");
            $lines = explode("\n", rtrim($logs));
            foreach ($lines as $line) {
                call_user_func($this->_logCallback, $module, $line);
            }
        }
        $this->_logIsPulling = false;
    }
    public function setDeviceLogPending()
    {
        $this->_logNeedPulling = true;
    }
    public function registerLogCallback($obj_callback)
    {
        $this->_logCallback = $obj_callback;
        if ($obj_callback != null) {
            $this->triggerLogPull();
        }
    }
    // Return the hub-specific devYdx of the device, as found during discovery
    public function getDevYdx()
    {
        return $this->_devYdx;
    }
    // Return a string that describes the device (serial number, logical name or root URL)
    public function describe()
    {
        $res = $this->_rootUrl;
        if ($this->_serialNumber != '') {
            $res = $this->_serialNumber;
            if ($this->_logicalName != '') {
                $res .= ' (' . ($this->_logicalName) . ')';
            }
        }
        return $this->_productName . ' ' . $res;
    }
    public function prepRequest($tcpreq)
    {
        if (!is_null($this->_ongoingReq)) {
            while (!$this->_ongoingReq->eof()) {
                YAPI::_handleEvents_internal(100);
            }
        }
        $this->_ongoingReq = $tcpreq;
    }
    public function requestAPI()
    {
        if ($this->_cache['_expiration'] > YAPI::GetTickCount()) {
            return new YAPI_YReq($this->_serialNumber . ".module",
                YAPI_SUCCESS, 'no error', $this->_cache['_json'], $this->_cache['_precooked']);
        }
        $req = 'GET /api.json';
        $use_jzon = false;
        if (isset($this->_cache['_precooked']) && $this->_cache['_precooked']['module']['firmwareRelease']) {
            $req .= "?fw=" . urlencode($this->_cache['_precooked']['module']['firmwareRelease']);
            $use_jzon = true;
        }
        $yreq = YAPI::devRequest($this->_rootUrl, $req);
        if ($yreq->errorType != YAPI_SUCCESS) return $yreq;
        $json_req = json_decode(iconv("ISO-8859-1", "UTF-8", $yreq->result), true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return $this->_throw(YAPI_IO_ERROR, 'Request failed, could not parse API result for ' . $this->_rootUrl,
                YAPI_IO_ERROR);
        }
        if ($use_jzon && !key_exists('module', $json_req)) {
            $decoded = YTcpHub::decodeJZON($json_req, $this->_cache['_precooked']);
            $this->_cache['_json'] = json_encode($decoded);
            $this->_cache['_precooked'] = $decoded;
        } else {
            $this->_cache['_json'] = $yreq->result;
            $this->_cache['_precooked'] = $json_req;
        }
        $this->_cache['_expiration'] = YAPI::GetTickCount() + YAPI::$defaultCacheValidity;
        return new YAPI_YReq($this->_serialNumber . ".module",
            YAPI_SUCCESS, 'no error', $this->_cache['_json'], $this->_cache['_precooked']);
    }
    // Reload a device API (store in cache), and update YAPI function lists accordingly
    // Intended to be called within UpdateDeviceList only
    public function refresh()
    {
        $yreq = $this->requestAPI();
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $this->_throw($yreq->errorType, $yreq->errorMsg, $yreq->errorType);
        }
        $loadval = $yreq->obj_result;
        $reindex = false;
        if ($this->_productName == "") {
            // parse module and function names for the first time
            foreach ($loadval as $func => $iface) {
                if ($func == 'module') {
                    $this->_serialNumber = $iface['serialNumber'];
                    $this->_logicalName = $iface['logicalName'];
                    $this->_productName = $iface['productName'];
                    $this->_productId = $iface['productId'];
                    $this->_beacon = $iface['beacon'];
                } else if ($func == 'services') {
                    $this->_updateFromYP($iface['yellowPages']);
                }
            }
            $reindex = true;
        } else {
            // parse module and refresh names if needed
            foreach ($loadval as $func => $iface) {
                if ($func == 'module') {
                    if ($this->_logicalName != $iface['logicalName']) {
                        $this->_logicalName = $iface['logicalName'];
                        $reindex = true;
                    }
                    $this->_beacon = $iface['beacon'];
                } else if ($func != 'services') {
                    if (isset($iface[$func]['logicalName']))
                        $name = $iface[$func]['logicalName'];
                    else
                        $name = $this->_logicalName;
                    if (isset($iface[$func]['advertisedValue'])) {
                        $pubval = $iface[$func]['advertisedValue'];
                        YAPI::setFunctionValue($this->_serialNumber . '.' . $func, $pubval);
                    }
                    foreach ($this->_functions as $funydx => $fundef) {
                        if ($fundef[0] == $func) {
                            if ($fundef[1] != $name) {
                                $this->_functions[$funydx][1] = $name;
                                $reindex = true;
                            }
                            break;
                        }
                    }
                }
            }
        }
        if ($reindex) {
            YAPI::reindexDevice($this);
        }
        return YAPI_SUCCESS;
    }
    // Force the REST API string in cache to expire immediately
    public function dropCache()
    {
        $this->_cache['_expiration'] = 0;
    }
    public function functionCount()
    {
        $funcPos = 0;
        foreach ($this->_functions as $funydx => $fundef) {
            $funcPos++;
        }
        return $funcPos;
    }
    public function functionId($functionIndex)
    {
        $funcPos = 0;
        foreach ($this->_functions as $funydx => $fundef) {
            if ($functionIndex == $funcPos) {
                return $fundef[0];
            }
            $funcPos++;
        }
        return '';
    }
    public function functionBaseType($functionIndex)
    {
        $fid = $this->functionId($functionIndex);
        if ($fid != '') {
            $ftype = YAPI::getFunctionBaseType($this->_serialNumber . '.' . $fid);
            foreach (YAPI::$BASETYPES as $name => $type) {
                if ($ftype === $type) {
                    return $name;
                }
            }
        }
        return 'Function';
    }
    public function functionType($functionIndex)
    {
        $fid = $this->functionId($functionIndex);
        if ($fid != '') {
            for ($i = strlen($fid); $i > 0; $i--) {
                if ($fid[$i-1] > '9') {
                    break;
                }
            }
            return strtoupper($fid[0]) . substr($fid, 1, $i - 1);
        }
        return '';
    }
    public function functionName($functionIndex)
    {
        $funcPos = 0;
        foreach ($this->_functions as $funydx => $fundef) {
            if ($functionIndex == $funcPos) {
                return $fundef[1];
            }
            $funcPos++;
        }
        return '';
    }
    public function functionValue($functionIndex)
    {
        $fid = $this->functionId($functionIndex);
        if ($fid != '') {
            return YAPI::getFunctionValue($this->_serialNumber . '.' . $fid);
        }
        return '';
    }
    public function functionIdByFunYdx($funYdx)
    {
        if(isset($this->_functions[$funYdx])) {
            return $this->_functions[$funYdx][0];
        }
        return '';
    }
}
//--- (generated code: YAPIContext definitions)
//--- (end of generated code: YAPIContext definitions)
//--- (generated code: YAPIContext declaration)
class YAPIContext
{
    //--- (end of generated code: YAPIContext declaration)
    public $_deviceListValidityMs = 10000;                        // ulong
    public $_networkTimeoutMs = YAPI_BLOCKING_REQUEST_TIMEOUT;
    //--- (generated code: YAPIContext attributes)
    protected $_defaultCacheValidity     = 5;                            // ulong
    //--- (end of generated code: YAPIContext attributes)
    function __construct()
    {
        //--- (generated code: YAPIContext constructor)
        //--- (end of generated code: YAPIContext constructor)
    }
    private function AddUdevRule_internal($force)
    {
        return "error: Not supported in PHP";
    }
    //--- (generated code: YAPIContext implementation)
    public function SetDeviceListValidity($deviceListValidity)
    {
        $this->SetDeviceListValidity_internal($deviceListValidity);
    }
    //cannot be generated for PHP:
    //private function SetDeviceListValidity_internal($deviceListValidity)
    public function GetDeviceListValidity()
    {
        return $this->GetDeviceListValidity_internal();
    }
    //cannot be generated for PHP:
    //private function GetDeviceListValidity_internal()
    public function AddUdevRule($force)
    {
        return $this->AddUdevRule_internal($force);
    }
    //cannot be generated for PHP:
    //private function AddUdevRule_internal($force)
    public function SetNetworkTimeout($networkMsTimeout)
    {
        $this->SetNetworkTimeout_internal($networkMsTimeout);
    }
    //cannot be generated for PHP:
    //private function SetNetworkTimeout_internal($networkMsTimeout)
    public function GetNetworkTimeout()
    {
        return $this->GetNetworkTimeout_internal();
    }
    //cannot be generated for PHP:
    //private function GetNetworkTimeout_internal()
    public function SetCacheValidity($cacheValidityMs)
    {
        $this->_defaultCacheValidity = $cacheValidityMs;
    }
    public function GetCacheValidity()
    {
        return $this->_defaultCacheValidity;
    }
    //--- (end of generated code: YAPIContext implementation)
    public function SetDeviceListValidity_internal($deviceListValidity)
    {
        $this->_deviceListValidityMs = $deviceListValidity * 1000;
    }
    public function GetDeviceListValidity_internal()
    {
        return intval($this->_deviceListValidityMs / 1000);
    }
    public function SetNetworkTimeout_internal($networkMsTimeout)
    {
        $this->_networkTimeoutMs = $networkMsTimeout;
    }
    public function GetNetworkTimeout_internal()
    {
        return $this->_networkTimeoutMs;
    }
}
//
// YAPI Context
//
// This class provides the high-level entry points to access Functions, stores
// an indexes instances of the Device object and of FunctionType collections.
//
class YAPI
{
    const INVALID_STRING = YAPI_INVALID_STRING;
    const INVALID_INT = YAPI_INVALID_INT;
    const INVALID_UINT = YAPI_INVALID_UINT;
    const INVALID_DOUBLE = YAPI_INVALID_DOUBLE;
    const INVALID_LONG = YAPI_INVALID_LONG;
//--- (generated code: YFunction return codes)
    const SUCCESS               = 0;       // everything worked all right
    const NOT_INITIALIZED       = -1;      // call yInitAPI() first !
    const INVALID_ARGUMENT      = -2;      // one of the arguments passed to the function is invalid
    const NOT_SUPPORTED         = -3;      // the operation attempted is (currently) not supported
    const DEVICE_NOT_FOUND      = -4;      // the requested device is not reachable
    const VERSION_MISMATCH      = -5;      // the device firmware is incompatible with this API version
    const DEVICE_BUSY           = -6;      // the device is busy with another task and cannot answer
    const TIMEOUT               = -7;      // the device took too long to provide an answer
    const IO_ERROR              = -8;      // there was an I/O problem while talking to the device
    const NO_MORE_DATA          = -9;      // there is no more data to read from
    const EXHAUSTED             = -10;     // you have run out of a limited resource, check the documentation
    const DOUBLE_ACCES          = -11;     // you have two process that try to access to the same device
    const UNAUTHORIZED          = -12;     // unauthorized access to password-protected device
    const RTC_NOT_READY         = -13;     // real-time clock has not been initialized (or time was lost)
    const FILE_NOT_FOUND        = -14;     // the file is not found
    const SSL_ERROR             = -15;     // Error reported by mbedSSL
//--- (end of generated code: YFunction return codes)
    // yInitAPI constants (not really useful in JavaScript)
    const DETECT_NONE = 0;
    const DETECT_USB = 1;
    const DETECT_NET = 2;
    const DETECT_ALL = 3;
    // Abstract function BaseTypes
    public static $BASETYPES = Array('Function' => 0,
        'Sensor' => 1);
    protected static $_hubs;           // array of root urls
    protected static $_devs;           // hash table of devices, by serial number
    protected static $_snByUrl;        // serial number for each device, by URL
    protected static $_snByName;       // serial number for each device, by name
    protected static $_fnByType;       // functions by type
    protected static $_lastErrorType;
    protected static $_lastErrorMsg;
    protected static $_firstArrival;
    protected static $_pendingCallbacks;
    protected static $_arrivalCallback;
    protected static $_namechgCallback;
    protected static $_removalCallback;
    protected static $_data_events;
    protected static $_pendingRequests;
    protected static $_beacons;
    protected static $_calibHandlers;
    protected static $_decExp;
    static $_jzonCacheDir;
    static $_yapiContext;
    // PUBLIC GLOBAL SETTINGS
    // Default cache validity (in [ms]) before reloading data from device. This saves a lots of trafic.
    // Note that a value under 2 ms makes little sense since a USB bus itself has a 2ms roundtrip period
    public static $defaultCacheValidity = 5;
    // Switch to turn off exceptions and use return codes instead, for source-code compatibility
    // with languages without exception support like C
    public static $exceptionsDisabled = false;  // set to true if you want error codes instead of exceptions
    public static function _init()
    {
        // private
        self::$_hubs = Array();
        self::$_devs = Array();
        self::$_snByUrl = Array();
        self::$_snByName = Array();
        self::$_fnByType = Array();
        self::$_lastErrorType = YAPI_SUCCESS;
        self::$_lastErrorMsg = 'no error';
        self::$_firstArrival = true;
        self::$_pendingCallbacks = Array();
        self::$_arrivalCallback = null;
        self::$_namechgCallback = null;
        self::$_removalCallback = null;
        self::$_data_events = Array();
        self::$_pendingRequests = Array();
        self::$_beacons = array();
        self::$_jzonCacheDir = null;
        self::$_yapiContext = new YAPIContext();
        self::$_decExp = Array(
            1.0e-6, 1.0e-5, 1.0e-4, 1.0e-3, 1.0e-2, 1.0e-1, 1.0,
            1.0e1, 1.0e2, 1.0e3, 1.0e4, 1.0e5, 1.0e6, 1.0e7, 1.0e8, 1.0e9);
        self::$_fnByType['Module'] = new YFunctionType('Module');
        register_shutdown_function('YAPI::flushConnections');
    }
    // Throw an exception, keeping track of it in the object itself
    protected static function _throw($int_errType, $str_errMsg, $obj_retVal)
    {
        self::$_lastErrorType = $int_errType;
        self::$_lastErrorMsg = $str_errMsg;
        if (self::$exceptionsDisabled) {
            return $obj_retVal;
        }
        // throw an exception
        throw new YAPI_Exception($str_errMsg, $int_errType);
    }
    // Update the list of known devices internally
    public static function _updateDeviceList_internal($bool_forceupdate, $bool_invokecallbacks)
    {
        if (self::$_firstArrival && $bool_invokecallbacks && !is_null(self::$_arrivalCallback)) {
            $bool_forceupdate = true;
        }
        $now = self::GetTickCount();
        if ($bool_forceupdate) {
            foreach (self::$_hubs as $hub) {
                $hub->devListExpires = $now;
            }
        }
        // Prepare to scan all expired hubs
        $hubs = Array();
        foreach (self::$_hubs as $hub) {
            if ($hub->devListExpires <= $now) {
                $tcpreq = new YTcpReq($hub, 'GET /api.json', false, '', YAPI::$_yapiContext->_networkTimeoutMs);
                self::$_pendingRequests[] = $tcpreq;
                $hubs[] = $hub;
                $hub->devListReq = $tcpreq;
                $hub->missing = Array();
            }
        }
        // assume all device as unpluged, unless proved wrong
        foreach (self::$_devs as $serial => $dev) {
            $rooturl = $dev->getRootUrl();
            foreach ($hubs as $hub) {
                $huburl = $hub->rooturl;
                if (substr($rooturl, 0, strlen($huburl)) == $huburl) {
                    $hub->missing[$serial] = true;
                }
            }
        }
        // Wait until all hubs are complete, and process replies as they come
        $timeout = self::GetTickCount() + YAPI::$_yapiContext->_networkTimeoutMs;
        while (self::GetTickCount() < $timeout) {
            self::_handleEvents_internal(100);
            $alldone = true;
            foreach ($hubs as $hub) {
                $req = $hub->devListReq;
                if (!$req->eof()) {
                    $alldone = false;
                    continue;
                }
                if ($req->errorType != YAPI_SUCCESS) {
                    // report problems later
                    continue;
                }
                $loadval = json_decode(iconv("ISO-8859-1", "UTF-8", $req->reply), true);
                if (!$loadval) {
                    $req->errorType = YAPI_IO_ERROR;
                    continue;
                }
                if (!isset($loadval['services']) || !isset($loadval['services']['whitePages'])) {
                    $req->errorType = YAPI_INVALID_ARGUMENT;
                    continue;
                }
                if (isset($loadval['network']) && isset($loadval['network']['adminPassword'])) {
                    $hub->writeProtected = ($loadval['network']['adminPassword'] != '');
                }
                $whitePages = $loadval['services']['whitePages'];
                // Reindex all functions from yellow pages
                $refresh = Array();
                $yellowPages = $loadval["services"]["yellowPages"];
                foreach ($yellowPages as $classname => $obj_yprecs) {
                    if (!isset(self::$_fnByType[$classname])) {
                        self::$_fnByType[$classname] = new YFunctionType($classname);
                    }
                    $ftype = self::$_fnByType[$classname];
                    foreach ($obj_yprecs as $yprec) {
                        $hwid = $yprec["hardwareId"];
                        $basetype = (isset($yprec["baseType"]) ? $yprec["baseType"] : null);
                        if ($ftype->reindexFunction($hwid, $yprec["logicalName"], $yprec["advertisedValue"], $basetype)) {
                            // logical name discrepency detected, force a refresh from device
                            $serial = substr($hwid, 0, strpos($hwid, '.'));
                            $refresh[$serial] = true;
                        }
                    }
                }
                // Reindex all devices from white pages
                foreach ($whitePages as $devinfo) {
                    $serial = $devinfo['serialNumber'];
                    $rooturl = substr($devinfo['networkUrl'], 0, -3);
                    if ($rooturl[0] == '/')
                        $rooturl = $hub->rooturl . $rooturl;
                    $currdev = null;
                    if (isset(self::$_devs[$serial])) {
                        $currdev = self::$_devs[$serial];
                        if (!is_null(self::$_arrivalCallback) && self::$_firstArrival) {
                            self::$_pendingCallbacks[] = "+$serial";
                        }
                    }
                    if (isset($devinfo['index'])) {
                        $devydx = $devinfo['index'];
                        $hub->serialByYdx[$devydx] = $serial;
                    }
                    if (!isset(self::$_devs[$serial])) {
                        // Add new device
                        new YDevice($rooturl, $devinfo, $loadval["services"]["yellowPages"]);
                        if (!is_null(self::$_arrivalCallback)) {
                            self::$_pendingCallbacks[] = "+$serial";
                        }
                    } else if ($currdev->getLogicalName() != $devinfo['logicalName']) {
                        // Reindex device from its own data
                        $currdev->refresh();
                        if (!is_null(self::$_namechgCallback)) {
                            self::$_pendingCallbacks[] = "/$serial";
                        }
                    } else if (isset($refresh[$serial]) || $currdev->getRootUrl() != $rooturl ||
                        $currdev->getBeacon() != $devinfo['beacon']) {
                        // Reindex device from its own data in case of discrepency
                        $currdev->refresh();
                    }
                    $hub->missing[$serial] = false;
                }
                // Keep track of all unplugged devices on this hub
                foreach ($hub->missing as $serial => $missing) {
                    if ($missing) {
                        if (!is_null(self::$_removalCallback)) {
                            self::$_pendingCallbacks[] = "-$serial";
                        } else {
                            self::forgetDevice(self::$_devs[$serial]);
                        }
                    }
                }
                // enable monitoring for this hub if not yet done
                self::monitorEvents($hub);
                if ($hub->isNotifWorking) {
                    $hub->devListExpires = $now + YAPI::$_yapiContext->_deviceListValidityMs;
                } else {
                    $hub->devListExpires = $now + 500;
                }
            }
            if ($alldone) break;
        }
        // after processing all hubs, invoke pending callbacks if required
        if ($bool_invokecallbacks) {
            $nbevents = sizeof(self::$_pendingCallbacks);
            for ($i = 0; $i < $nbevents; $i++) {
                $evt = self::$_pendingCallbacks[$i];
                $serial = substr($evt, 1);
                switch (substr($evt, 0, 1)) {
                    case '+':
                        if (!is_null(self::$_arrivalCallback)) {
                            $cb = self::$_arrivalCallback;
                            $cb(yFindModule($serial . ".module"));
                        }
                        break;
                    case '/':
                        if (!is_null(self::$_namechgCallback)) {
                            $cb = self::$_namechgCallback;
                            $cb(yFindModule($serial . ".module"));
                        }
                        break;
                    case '-':
                        if (!is_null(self::$_removalCallback)) {
                            $cb = self::$_removalCallback;
                            $cb(yFindModule($serial . ".module"));
                        }
                        self::forgetDevice(self::$_devs[$serial]);
                        break;
                }
            }
            self::$_pendingCallbacks = array_slice(self::$_pendingCallbacks, $nbevents);
            if (!is_null(self::$_arrivalCallback) && self::$_firstArrival) {
                self::$_firstArrival = false;
            }
        }
        // report any error seen during scan
        foreach ($hubs as $hub) {
            $req = $hub->devListReq;
            if ($req->errorType != YAPI_SUCCESS) {
                return new YAPI_YReq("", $req->errorType,
                    'Error while scanning ' . $hub->rooturl . ': ' . $req->errorMsg,
                    $req->errorType);
            }
        }
        return new YAPI_YReq("", YAPI_SUCCESS, "no error", YAPI_SUCCESS);
    }
    public static function _handleEvents_internal($int_maxwait)
    {
        $something_done = false;
        // start event monitoring if needed
        foreach (self::$_hubs as $hub) {
            $req = $hub->notifReq;
            if ($req) {
                if ($req->eof()) {
                    //Printf("Event channel at eof, reopen\n");
                    $something_done = true;
                    $hub->notifReq = $req = null;
                    self::monitorEvents($hub);
                }
            } else if ($hub->retryExpires > 0 && $hub->retryExpires <= self::GetTickCount()) {
                Printf("RetryExpires, calling monitorEvents\n");
                $something_done = true;
                self::monitorEvents($hub);
            }
        }
        // Monitor all pending request for logs
        foreach (self::$_devs as $serial => $dev) {
            $dev->triggerLogPull();
        }
        // monitor all pending requests
        $streams = Array();
        foreach (self::$_pendingRequests as $req) {
            if (is_null($req->skt) || !is_resource($req->skt)) {
                $req->process();
            }
            if (!is_null($req->skt) && is_resource($req->skt)) {
                $streams[] = $req->skt;
            }
        }
        if (sizeof($streams) == 0) {
            usleep($int_maxwait * 1000);
            return false;
        }
        $wr = NULL;
        $ex = NULL;
        if (false === ($select_res = stream_select($streams, $wr, $ex, 0, $int_maxwait * 1000))) {
            Printf("stream_select error\n");
            return false;
        }
        for ($idx = 0; $idx < sizeof(self::$_pendingRequests); $idx++) {
            $req = self::$_pendingRequests[$idx];
            $hub = $req->hub;
            // generic request processing
            $req->process();
            if ($req->eof()) {
                array_splice(self::$_pendingRequests, $idx, 1);
            }
            // handle notification channel
            if ($req === $hub->notifReq) {
                $linepos = strpos($req->reply, "\n");
                while ($linepos !== false) {
                    $ev = trim(substr($req->reply, 0, $linepos));
                    $req->reply = substr($req->reply, $linepos + 1);
                    $linepos = strpos($req->reply, "\n");
                    $firstCode = substr($ev, 0, 1);
                    if (strlen($ev) == 0) {
                        // empty line to send ping
                        continue;
                    }
                    if (strlen($ev) >= 3 && $firstCode >= NOTIFY_NETPKT_CONFCHGYDX && $firstCode <= NOTIFY_NETPKT_TIMEAVGYDX) {
                        // function value ydx (tiny notification)
                        $hub->isNotifWorking = true;
                        $hub->retryDelay = 15;
                        if ($hub->notifPos >= 0) {
                            $hub->notifPos += strlen($ev) + 1;
                        }
                        $devydx = ord($ev[1]) - 65; // from 'A'
                        $funydx = ord($ev[2]) - 48; // from '0'
                        if ($funydx >= 64) { // high bit of devydx is on second character
                            $funydx -= 64;
                            $devydx += 128;
                        }
                        if (isset($hub->serialByYdx[$devydx])) {
                            $serial = $hub->serialByYdx[$devydx];
                            if (isset(self::$_devs[$serial])) {
                                $funcid = ($funydx == 0xf ? 'time' : self::$_devs[$serial]->functionIdByFunYdx($funydx));
                                if ($funcid != "") {
                                    $value = substr($ev, 3);
                                    switch ($firstCode) {
                                        case NOTIFY_NETPKT_FUNCVALYDX:
                                            // function value ydx (tiny notification)
                                            $value = explode("\0", $value);
                                            $value = $value[0];
                                            YAPI::setFunctionValue($serial . '.' . $funcid, $value);
                                            break;
                                        case NOTIFY_NETPKT_DEVLOGYDX:
                                            // log notification
                                            $dev = self::$_devs[$serial];
                                            $dev->setDeviceLogPending();
                                            break;
                                        case NOTIFY_NETPKT_CONFCHGYDX:
                                            // configuration change notification
                                            YAPI::setConfChange($serial);
                                            break;
                                        case NOTIFY_NETPKT_TIMEVALYDX:
                                        case NOTIFY_NETPKT_TIMEAVGYDX:
                                        case NOTIFY_NETPKT_TIMEV2YDX:
                                            // timed value report
                                            $arr = Array($firstCode == 'x' ? 0 : ($firstCode == 'z' ? 1 : 2));
                                            for ($pos = 0; $pos < strlen($value); $pos += 2) {
                                                $arr[] = hexdec(substr($value, $pos, 2));
                                            }
                                            $dev = self::$_devs[$serial];
                                            if ($funcid == 'time') {
                                                $time = $arr[1] + 0x100 * $arr[2] + 0x10000 * $arr[3] + 0x1000000 * $arr[4];
                                                $ms = $arr[5] * 4;
                                                if (sizeof($arr) >= 7) {
                                                    $ms += $arr[6] >> 6;
                                                    $duration_ms = $arr[7];
                                                    $duration_ms += ($arr[6] & 0xf) * 0x100;
                                                    if ($arr[6] & 0x10) {
                                                        $duration = $duration_ms;
                                                    } else {
                                                        $duration = $duration_ms / 1000.0;
                                                    }
                                                } else {
                                                    $duration = 0.0;
                                                }
                                                $dev->setTimeRef($time + $ms / 1000.0, $duration);
                                            } else {
                                                YAPI::setTimedReport($serial . '.' . $funcid, $dev->getLastTimeRef(), $dev->getLastDuration(), $arr);
                                            }
                                            break;
                                        case NOTIFY_NETPKT_FUNCV2YDX:
                                            $rawval = YAPI::decodeNetFuncValV2($value);
                                            if ($rawval != null) {
                                                $decodedval = YAPI::decodePubVal($rawval[0], $rawval, 1, 6);
                                                YAPI::setFunctionValue($serial . '.' . $funcid, $decodedval);
                                            }
                                            break;
                                        case NOTIFY_NETPKT_FLUSHV2YDX:
                                            // To be implemented later
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                    } else if (strlen($ev) > 5 && substr($ev, 0, 4) == 'YN01') {
                        $hub->isNotifWorking = true;
                        $hub->retryDelay = 15;
                        if ($hub->notifPos >= 0) {
                            $hub->notifPos += strlen($ev) + 1;
                        }
                        $notype = substr($ev, 4, 1);
                        if ($notype == NOTIFY_NETPKT_NOT_SYNC) {
                            $hub->notifPos = intVal(substr($ev, 5));
                        } else {
                            switch (intVal($notype)) {
                                case 0: // device name change, or arrival
                                    $parts = explode(',', substr($ev, 5));
                                    YAPI::setBeaconChange($parts[0], $parts[2]);
                                // no break on purpose
                                case 2: // device plug/unplug
                                case 4: // function name change
                                case 8: // function name change (ydx)
                                    $hub->devListExpires = 0;
                                    break;
                                case 5: // function value (long notification)
                                    $parts = explode(',', substr($ev, 5));
                                    $value = explode("\0", $parts[2]);
                                    YAPI::setFunctionValue($parts[0] . '.' . $parts[1], $value[0]);
                                    break;
                            }
                        }
                    } else {
                        // oops, bad notification ? be safe until a good one comes
                        $hub->isNotifWorking = false;
                        $hub->devListExpires = 0;
                        $hub->notifPos = -1;
                    }
                }
            }
        }
        return $something_done;
    }
    public static function flushConnections()
    {
        foreach (self::$_pendingRequests as $req) {
            if ($req->async) {
                while (!$req->eof()) {
                    self::_handleEvents_internal(200);
                }
            }
        }
    }
    public static function monitorEvents($hub)
    {
        if (!is_null($hub->notifReq)) return;
        if ($hub->retryExpires > self::GetTickCount()) return;
        if ($hub->isCachedHub()) return;
        $url = $hub->notifurl . '?len=0';
        if ($hub->notifPos >= 0) $url .= '&abs=' . $hub->notifPos;
        $req = new YTcpReq($hub, 'GET /' . $url, false);
        $errmsg = '';
        if ($req->process($errmsg) != YAPI_SUCCESS) {
            if ($hub->retryDelay == 0) {
                $hub->retryDelay = 15;
            } else if ($hub->retryDelay < 15000) {
                $hub->retryDelay = 2 * $hub->retryDelay;
            }
            $hub->retryExpires = self::GetTickCount() + $hub->retryDelay;
            return;
        }
        self::$_pendingRequests[] = $req;
        $hub->notifReq = $req;
    }
    // Convert Yoctopuce 16-bit decimal floats to standard double-precision floats
    //
    public static function _decimalToDouble($val)
    {
        $negate = false;
        $mantis = $val & 2047;
        if ($mantis == 0) return 0.0;
        if ($val > 32767) {
            $negate = true;
            $val = 65536 - $val;
        } else if ($val < 0) {
            $negate = true;
            $val = -$val;
        }
        $decexp = self::$_decExp[$val >> 11];
        if ($decexp >= 1.0) {
            $res = ($mantis) * $decexp;
        } else {
            $res = ($mantis) / round(1.0 / $decexp);
        }
        return ($negate ? -$res : $res);
    }
    // Convert standard double-precision floats to Yoctopuce 16-bit decimal floats
    //
    public static function _doubleToDecimal($val)
    {
        $negate = false;
        if ($val == 0.0) {
            return 0;
        }
        if ($val < 0) {
            $negate = true;
            $val = -$val;
        }
        $comp = $val / 1999.0;
        $decpow = 0;
        while ($comp > self::$_decExp[$decpow] && $decpow < 15) {
            $decpow++;
        }
        $mant = $val / self::$_decExp[$decpow];
        if ($decpow == 15 && $mant > 2047.0) {
            $res = (15 << 11) + 2047; // overflow
        } else {
            $res = ($decpow << 11) + round($mant);
        }
        return ($negate ? -$res : $res);
    }
    // Return a the calibration handler for a given type
    public static function _getCalibrationHandler($calibType)
    {
        if (!isset(self::$_calibHandlers[strVal($calibType)])) {
            return null;
        }
        return self::$_calibHandlers[strVal($calibType)];
    }
    // Parse an array of u16 encoded in a base64-like string with memory-based compresssion
    public static function _decodeWords($data)
    {
        $datalen = strlen($data);
        $udata = Array();
        for ($i = 0; $i < $datalen;) {
            $c = $data[$i];
            if ($c == '*') {
                $val = 0;
                $i++;
            } else if ($c == 'X') {
                $val = 0xffff;
                $i++;
            } else if ($c == 'Y') {
                $val = 0x7fff;
                $i++;
            } else if ($c >= 'a') {
                $srcpos = sizeof($udata) - 1 - (ord($data[$i++]) - 97);
                if ($srcpos < 0) {
                    $val = 0;
                } else {
                    $val = $udata[$srcpos];
                }
            } else {
                if ($i + 2 > $datalen) return YAPI_IO_ERROR;
                $val = ord($data[$i++]) - 48;
                $val += (ord($data[$i++]) - 48) << 5;
                if ($data[$i] == 'z') $data[$i] = '\\';
                $val += (ord($data[$i++]) - 48) << 10;
            }
            $udata[] = $val;
        }
        return $udata;
    }
    // Parse an array of u16 encoded in a base64-like string with memory-based compresssion
    public static function _decodeFloats($data)
    {
        $datalen = strlen($data);
        $idata = Array();
        $p = 0;
        while ($p < $datalen) {
            $val = 0;
            $sign = 1;
            $dec = 0;
            $decInc = 0;
            $c = $data[$p++];
            while ($c != '-' && ($c < '0' || $c > '9')) {
                if ($p >= $datalen) {
                    return $idata;
                }
                $c = $data[$p++];
            }
            if ($c == '-') {
                if ($p >= $datalen) {
                    return $idata;
                }
                $sign = -$sign;
                $c = $data[$p++];
            }
            while (($c >= '0' && $c <= '9') || $c == '.') {
                if ($c == '.') {
                    $decInc = 1;
                } else if ($dec < 3) {
                    $val = $val * 10 + (ord($c) - 48);
                    $dec += $decInc;
                }
                if ($p < $datalen) {
                    $c = $data[$p++];
                } else {
                    $c = '\0';
                }
            }
            if ($dec < 3) {
                if ($dec == 0) $val *= 1000;
                else if ($dec == 1) $val *= 100;
                else $val *= 10;
            }
            $idata[] = $sign * $val;
        }
        return $idata;
    }
    public static function _bytesToHexStr($data)
    {
        return strtoupper(bin2hex($data));
    }
    public static function _hexStrToBin($data)
    {
        $pos = 0;
        $result = '';
        while ($pos < strlen($data)) {
            $code = hexdec(substr($data, $pos, 2));
            $pos = $pos + 2;
            $result .= chr($code);
        }
        return $result;
    }
    public static function getDevice($str_device)
    {
        $dev = null;
        if (substr($str_device, 0, 7) == 'http://') {
            if (isset(self::$_snByUrl[$str_device])) {
                $serial = self::$_snByUrl[$str_device];
                if (isset(self::$_devs[$serial])) {
                    $dev = self::$_devs[$serial];
                }
            }
        } else {
            // lookup by serial
            if (isset(self::$_devs[$str_device])) {
                $dev = self::$_devs[$str_device];
            } else {
                // fallback to lookup by logical name
                if (isset(self::$_snByName[$str_device])) {
                    $serial = self::$_snByName[$str_device];
                    $dev = self::$_devs[$serial];
                }
            }
        }
        return $dev;
    }
    // Return the class name for a given function ID or full Hardware Id
    // Also make sure that the function type is registered in the API
    public static function functionClass($str_funcid)
    {
        $dotpos = strpos($str_funcid, '.');
        if ($dotpos !== false) $str_funcid = substr($str_funcid, $dotpos + 1);
        $classlen = strlen($str_funcid);
        while (ord($str_funcid[$classlen - 1]) <= 57) {
            $classlen--;
        }
        $classname = strtoupper($str_funcid[0]) . substr($str_funcid, 1, $classlen - 1);
        if (!isset(self::$_fnByType[$classname])) {
            self::$_fnByType[$classname] = new YFunctionType($classname);
        }
        return $classname;
    }
    // Reindex a device in YAPI after a name change detected by device refresh
    public static function reindexDevice($obj_dev)
    {
        $rootUrl = $obj_dev->getRootUrl();
        $serial = $obj_dev->getSerialNumber();
        $lname = $obj_dev->getLogicalName();
        self::$_devs[$serial] = $obj_dev;
        self::$_snByUrl[$rootUrl] = $serial;
        if ($lname != '') self::$_snByName[$lname] = $serial;
        self::$_fnByType['Module']->reindexFunction("$serial.module", $lname, null, null);
        $count = $obj_dev->functionCount();
        for ($i = 0; $i < $count; $i++) {
            $funcid = $obj_dev->functionId($i);
            $funcname = $obj_dev->functionName($i);
            $classname = self::functionClass($funcid);
            self::$_fnByType[$classname]->reindexFunction("$serial.$funcid", $funcname, null, null);
        }
    }
    // Remove a device from YAPI after an unplug detected by device refresh
    public static function forgetDevice($obj_dev)
    {
        $rootUrl = $obj_dev->getRootUrl();
        $serial = $obj_dev->getSerialNumber();
        $lname = $obj_dev->getLogicalName();
        unset(self::$_devs[$serial]);
        unset(self::$_snByUrl[$rootUrl]);
        if (isset(self::$_snByName[$lname]) && self::$_snByName[$lname] == $serial) {
            unset(self::$_snByName[$lname]);
        }
        self::$_fnByType['Module']->forgetFunction("$serial.module");
        $count = $obj_dev->functionCount();
        for ($i = 0; $i < $count; $i++) {
            $funcid = $obj_dev->functionId($i);
            $classname = self::functionClass($funcid);
            self::$_fnByType[$classname]->forgetFunction("$serial.$funcid");
        }
    }
    public static function resolveFunction($str_className, $str_func)
    {
        if (!isset(self::$BASETYPES[$str_className])) {
            // using a regular function type
            if (!isset(self::$_fnByType[$str_className]))
                self::$_fnByType[$str_className] = new YFunctionType($str_className);
            return self::$_fnByType[$str_className]->resolve($str_func);
        }
        // using an abstract baseType
        $baseType = self::$BASETYPES[$str_className];
        $res = null;
        foreach (self::$_fnByType as $str_className => $funtype) {
            if ($funtype->matchBaseType($baseType)) {
                $res = $funtype->resolve($str_func);
                if ($res->errorType == YAPI_SUCCESS) return $res;
            }
        }
        return new YAPI_YReq($str_func,
            YAPI_DEVICE_NOT_FOUND,
            "No $str_className [$str_func] found (old firmware?)",
            null);
    }
    // return a firendly name for of a given function
    public static function getFriendlyNameFunction($str_className, $str_func)
    {
        if (!isset(self::$BASETYPES[$str_className])) {
            // using a regular function type
            if (!isset(self::$_fnByType[$str_className]))
                self::$_fnByType[$str_className] = new YFunctionType($str_className);
            return self::$_fnByType[$str_className]->getFriendlyName($str_func);
        }
        // using an abstract baseType
        $baseType = self::$BASETYPES[$str_className];
        $res = null;
        foreach (self::$_fnByType as $str_className => $funtype) {
            if ($funtype->matchBaseType($baseType)) {
                $res = $funtype->getFriendlyName($str_func);
                if ($res->errorType == YAPI_SUCCESS) return $res;
            }
        }
        return new YAPI_YReq($str_func,
            YAPI_DEVICE_NOT_FOUND,
            "No $str_className [$str_func] found (old firmware?)",
            null);
    }
    // Retrieve a function object by hardware id, updating the indexes on the fly if needed
    public static function setFunction($str_className, $str_func, $obj_func)
    {
        if (!isset(self::$_fnByType[$str_className]))
            self::$_fnByType[$str_className] = new YFunctionType($str_className);
        self::$_fnByType[$str_className]->setFunction($str_func, $obj_func);
    }
    // Retrieve a function object by hardware id, updating the indexes on the fly if needed
    public static function getFunction($str_className, $str_func)
    {
        if (is_null(self::$_hubs)) self::_init();
        if (!isset(self::$_fnByType[$str_className]))
            self::$_fnByType[$str_className] = new YFunctionType($str_className);
        return self::$_fnByType[$str_className]->getFunction($str_func);
    }
    // Set a function advertised value by hardware id
    public static function setFunctionValue($str_hwid, $str_pubval)
    {
        $classname = self::functionClass($str_hwid);
        self::$_fnByType[$classname]->setFunctionValue($str_hwid, $str_pubval);
    }
    // Set add a timed value report for a function
    public static function setTimedReport($str_hwid, $float_timestamp, $float_duration, $arr_report)
    {
        $classname = self::functionClass($str_hwid);
        self::$_fnByType[$classname]->setTimedReport($str_hwid, $float_timestamp, $float_duration, $arr_report);
    }
    // Publish a configuration change event
    public static function setConfChange($str_serial)
    {
        $module = yFindModule($str_serial . ".module");
        $module->_invokeConfigChangeCallback();
    }
    // Publish a configuration change event
    public static function setBeaconChange($str_serial, $int_beacon)
    {
        if (!array_key_exists($str_serial, self::$_beacons) || self::$_beacons[$str_serial] != $int_beacon) {
            self::$_beacons[$str_serial] = $int_beacon;
            $module = yFindModule($str_serial . ".module");
            $module->_invokeBeaconCallback($int_beacon);
        }
    }
    // Retrieve a function advertised value by hardware id
    public static function getFunctionValue($str_hwid)
    {
        $classname = self::functionClass($str_hwid);
        return self::$_fnByType[$classname]->getFunctionValue($str_hwid);
    }
    // Retrieve a function base type
    public static function getFunctionBaseType($str_hwid)
    {
        $classname = self::functionClass($str_hwid);
        return self::$_fnByType[$classname]->getBaseType();
    }
    // Queue a function value event
    public static function addValueEvent($obj_func, $str_newval)
    {
        self::$_data_events[] = Array($obj_func, $str_newval);
    }
    // Queue a function value event
    public static function addTimedReportEvent($obj_func, $float_timestamp, $float_duration, $arr_report)
    {
        self::$_data_events[] = Array($obj_func, $float_timestamp, $float_duration, $arr_report);
    }
    // Find the hardwareId for the first instance of a given function class
    public static function getFirstHardwareId($str_className)
    {
        if (is_null(self::$_hubs)) self::_init();
        if (!isset(self::$BASETYPES[$str_className])) {
            // enumeration of a regular function type
            if (!isset(self::$_fnByType[$str_className]))
                self::$_fnByType[$str_className] = new YFunctionType($str_className);
            return self::$_fnByType[$str_className]->getFirstHardwareId();
        }
        // enumeration of an abstract class
        $baseType = self::$BASETYPES[$str_className];
        $res = null;
        foreach (self::$_fnByType as $funtype) {
            if ($funtype->matchBaseType($baseType)) {
                $res = $funtype->getFirstHardwareId();
                if (!is_null($res)) return $res;
            }
        }
        return null;
    }
    // Find the hardwareId for the next instance of a given function class
    public static function getNextHardwareId($str_className, $str_hwid)
    {
        if (!isset(self::$BASETYPES[$str_className])) {
            // enumeration of a regular function type
            return self::$_fnByType[$str_className]->getNextHardwareId($str_hwid);
        }
        // enumeration of an abstract class
        $baseType = self::$BASETYPES[$str_className];
        $prevclass = self::functionClass($str_hwid);
        $res = self::$_fnByType[$prevclass]->getNextHardwareId($str_hwid);
        if (!is_null($res)) return $res;
        foreach (self::$_fnByType as $str_className => $funtype) {
            if ($prevclass != "") {
                if ($str_className != $prevclass) continue;
                $prevclass = "";
                continue;
            }
            if ($funtype->matchBaseType($baseType)) {
                $res = $funtype->getFirstHardwareId();
                if (!is_null($res)) return $res;
            }
        }
        return $res;
    }
    public static function devRequest($str_device, $str_request, $async = false, $body = '')
    {
        $lines = explode("\n", $str_request);
        $dev = null;
        $baseUrl = $str_device;
        if (substr($str_device, 0, 7) == 'http://') {
            if (substr($baseUrl, -1) != '/') $baseUrl .= '/';
            if (isset(self::$_snByUrl[$baseUrl])) {
                $serial = self::$_snByUrl[$baseUrl];
                if (isset(self::$_devs[$serial])) {
                    $dev = self::$_devs[$serial];
                }
            }
        } else {
            $dev = self::getDevice($str_device);
            if (!$dev) {
                return new YAPI_YReq("", YAPI_DEVICE_NOT_FOUND,
                    "Device [$str_device] not online",
                    null);
            }
            // use the device cache when loading the whole API
            if ($lines[0] == 'GET /api.json') {
                return $dev->requestAPI();
            }
            $baseUrl = $dev->getRootUrl();
        }
        // map str_device to a URL
        $words = explode(' ', $lines[0]);
        if (sizeof($words) < 2) {
            return new YAPI_YReq("", YAPI_INVALID_ARGUMENT,
                'Invalid request, not enough words; expected a method name and a URL',
                null);
        } else if (sizeof($words) > 2) {
            return new YAPI_YReq("", YAPI_INVALID_ARGUMENT,
                'Invalid request, too many words; make sure the URL is URI-encoded',
                null);
        }
        $method = $words[0];
        $devUrl = $words[1];
        if (substr($devUrl, 0, 1) == '/') $devUrl = substr($devUrl, 1);
        $baseUrl = str_replace('http://', '', $baseUrl);
        $pos = strpos($baseUrl, '/');
        if ($pos !== false) {
            $devUrl = substr($baseUrl, $pos) . $devUrl;
            $baseUrl = substr($baseUrl, 0, $pos);
        } else {
            $devUrl = "/$devUrl";
        }
        $rooturl = "http://$baseUrl";
        if (!isset(self::$_hubs[$rooturl])) {
            return new YAPI_YReq("", YAPI_DEVICE_NOT_FOUND, 'No hub registered on ' . $baseUrl, null);
        }
        $hub = self::$_hubs[$rooturl];
        if ($async && $hub->writeProtected && $hub->user != 'admin' && !$hub->isCachedHub()) {
            // async query, make sure the hub is not write-protected
            return new YAPI_YReq("", YAPI_UNAUTHORIZED,
                'Access denied: admin credentials required',
                null);
        }
        if (strpos($devUrl,'@YCB+') && !$hub->isCachedHub()) {
            return new YAPI_YReq("", YAPI_INVALID_ARGUMENT,
                'Preloading of URL is only supported for HTTP callback.',
                null);
        }
        $tcpreq = new YTcpReq($hub, "$method $devUrl", $async, $body);
        if (!is_null($dev)) {
            $dev->prepRequest($tcpreq);
        }
        if ($tcpreq->process() != YAPI_SUCCESS) {
            return new YAPI_YReq("", $tcpreq->errorType, $tcpreq->errorMsg, null);
        }
        self::$_pendingRequests[] = $tcpreq;
        if (!$async) {
            // normal query, wait for completion until timeout
            $mstimeout = YIO_DEFAULT_TCP_TIMEOUT;
            if (strpos($devUrl,'/testcb.txt') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/logger.json') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/rxmsg.json') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/rxdata.bin') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/at.txt') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/files.json') !== false) {
                $mstimeout = YIO_1_MINUTE_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/upload.html') !== false) {
                $mstimeout = YIO_10_MINUTES_TCP_TIMEOUT;
            } else if (strpos($devUrl,'/flash.json') !== false) {
                $mstimeout = YIO_10_MINUTES_TCP_TIMEOUT;
            }
            if ($mstimeout < YAPI::$_yapiContext->_networkTimeoutMs){
                $mstimeout = YAPI::$_yapiContext->_networkTimeoutMs;
            }
            $timeout = YAPI::GetTickCount() +  $mstimeout;
            do {
                self::_handleEvents_internal(100);
            } while (!$tcpreq->eof() && YAPI::GetTickCount() < $timeout);
            if (!$tcpreq->eof()) {
                $tcpreq->close();
                return new YAPI_YReq("", YAPI_TIMEOUT,
                    'Timeout waiting for device reply',
                    null);
            }
            if ($tcpreq->errorType == YAPI_UNAUTHORIZED) {
                return new YAPI_YReq("", YAPI_UNAUTHORIZED,
                    'Access denied, authorization required',
                    null);
            } else if ($tcpreq->errorType != YAPI_SUCCESS) {
                return new YAPI_YReq("", $tcpreq->errorType,
                    'Network error while reading from device',
                    null);
            }
            if (strpos($tcpreq->meta, "OK\r\n") === 0) {
                return new YAPI_YReq("", YAPI_SUCCESS,
                    'no error',
                    $tcpreq->reply);
            }
            if (strpos($tcpreq->meta, "0K\r\n") === 0) {
                return new YAPI_YReq("", YAPI_SUCCESS,
                    'no error',
                    $tcpreq->reply);
            }
            $matches = null;
            $preg_match = preg_match('/^HTTP[^ ]* (?P<status>\d+) (?P<statusmsg>.)+\r\n/', $tcpreq->meta, $matches);
            if (!$preg_match) {
                return new YAPI_YReq("", YAPI_IO_ERROR,
                    'Unexpected HTTP response header: ' . $tcpreq->meta,
                    null);
            }
            if ($matches['status'] != '200' && $matches['status'] != '304') {
                return new YAPI_YReq("", YAPI_IO_ERROR,
                    'Received HTTP status ' . $matches['status'] . ' (' . $matches['statusmsg'] . ')',
                    null);
            }
        }
        return new YAPI_YReq("", YAPI_SUCCESS,
            'no error',
            $tcpreq->reply);
    }
    public static function isReadOnly($str_device)
    {
        $dev = self::getDevice($str_device);
        if (!$dev) {
            return true;
        }
        $rooturl = $dev->getRootUrl();
        $pos = strpos($rooturl, '/',7);
        if ($pos >= 0) {
            $rooturl = substr($rooturl,0, $pos+1);
        }
        if (!isset(self::$_hubs[$rooturl])) {
            return true;
        }
        $hub = self::$_hubs[$rooturl];
        if ($hub->writeProtected && $hub->user != 'admin' && !$hub->isCachedHub()) {
            // async query, make sure the hub is not write-protected
            return true;
        }
        return false;
    }
    public static function getSubDevicesFrom($str_device)
    {
        $dev = self::getDevice($str_device);
        if (!$dev) {
            return '';
        }
        $baseUrl = $dev->getRootUrl();
        $baseUrl = str_replace('http://', '', $baseUrl);
        $pos = strpos($baseUrl, '/');
        if ($pos !== false) {
            $baseUrl = substr($baseUrl, 0, $pos);
        }
        $rooturl = "http://$baseUrl/";
        if (!isset(self::$_hubs[$rooturl])) {
            return new YAPI_YReq("", YAPI_DEVICE_NOT_FOUND, 'No hub registered on ' . $baseUrl, null);
        }
        $hub = self::$_hubs[$rooturl];
        if ($hub->serialByYdx[0] == $str_device) {
            return array_slice($hub->serialByYdx, 1);
        }
        return array();
    }
    public static function getHubSerialFrom($str_device)
    {
        $dev = self::getDevice($str_device);
        if (!$dev) {
            return '';
        }
        $baseUrl = $dev->getRootUrl();
        $baseUrl = str_replace('http://', '', $baseUrl);
        $pos = strpos($baseUrl, '/');
        if ($pos !== false) {
            $baseUrl = substr($baseUrl, 0, $pos);
        }
        $rooturl = "http://$baseUrl/";
        if (!isset(self::$_hubs[$rooturl])) {
            return new YAPI_YReq("", YAPI_DEVICE_NOT_FOUND, 'No hub registered on ' . $baseUrl, null);
        }
        $hub = self::$_hubs[$rooturl];
        return $hub->serialByYdx[0];
    }
    public static function funcRequest($str_className, $str_func, $str_extra)
    {
        $resolve = self::resolveFunction($str_className, $str_func);
        if ($resolve->errorType != YAPI_SUCCESS) {
            if ($resolve->errorType == YAPI_DEVICE_NOT_FOUND && sizeof(self::$_hubs) == 0) {
                // when USB is supported, check if no USB device is connected before outputing this message
                $resolve->errorMsg = "Impossible to contact any device because no hub has been registered";
            } else {
                $resolve = self::_updateDeviceList_internal(true, false);
                if ($resolve->errorType != YAPI_SUCCESS) {
                    return $resolve;
                }
                $resolve = self::resolveFunction($str_className, $str_func);
            }
            if ($resolve->errorType != YAPI_SUCCESS) {
                return $resolve;
            }
        }
        $str_func = $resolve->result;
        $dotpos = strpos($str_func, '.');
        $devid = substr($str_func, 0, $dotpos);
        $funcid = substr($str_func, $dotpos + 1);
        $dev = self::getDevice($devid);
        if (!$dev) {
            // try to force a device list update to check if the device arrived in between
            $resolve = self::_updateDeviceList_internal(true, false);
            if ($resolve->errorType != YAPI_SUCCESS) {
                return $resolve;
            }
            $dev = self::getDevice($devid);
            if (!$dev) {
                return new YAPI_YReq("{$devid}.{$funcid}", YAPI_DEVICE_NOT_FOUND,
                    "Device [$devid] not online",
                    null);
            }
        }
        $loadval = false;
        if ($str_extra == '') {
            // use a cached API string, without reloading unless module is requested
            $yreq = $dev->requestAPI();
            if (!is_null($yreq)) {
                $yreq->hwid = "{$devid}.{$funcid}";
                $yreq->deviceid = $devid;
                $yreq->functionid = $funcid;
                if ($yreq->errorType != YAPI_SUCCESS) return $yreq;
                $loadval = json_decode(iconv("ISO-8859-1", "UTF-8", $yreq->result), true);
                $loadval = $loadval[$funcid];
            }
        } else {
            $dev->dropCache();
            $yreq = new YAPI_YReq("{$devid}.{$funcid}", YAPI_NOT_INITIALIZED, "dummy", null);
        }
        if (!$loadval) {
            // request specified function only to minimize traffic
            if ($str_extra == "") {
                $httpreq = "GET /api/{$funcid}.json";
                $yreq = self::devRequest($devid, $httpreq);
                $yreq->hwid = "{$devid}.{$funcid}";
                $yreq->deviceid = $devid;
                $yreq->functionid = $funcid;
                if ($yreq->errorType != YAPI_SUCCESS) return $yreq;
                $loadval = json_decode(iconv("ISO-8859-1", "UTF-8", $yreq->result), true);
            } else {
                $httpreq = "GET /api/{$funcid}{$str_extra}";
                $yreq = self::devRequest($devid, $httpreq, true);
                $yreq->hwid = "{$devid}.{$funcid}";
                $yreq->deviceid = $devid;
                $yreq->functionid = $funcid;
                return $yreq;
            }
        }
        if (!$loadval) {
            return new YAPI_YReq("{$devid}.{$funcid}", YAPI_IO_ERROR,
                "Request failed, could not parse API value for function $str_func",
                null);
        }
        $yreq->result = $loadval;
        return $yreq;
    }
    // Perform an HTTP request on a device and return the result string
    // Throw an exception (or return YAPI_ERROR_STRING on error)
    public static function HTTPRequest($str_device, $str_request)
    {
        $res = self::devRequest($str_device, $str_request);
        if ($res->errorType != YAPI_SUCCESS) {
            return self::_throw($res->errorType, $res->errorMsg, null);
        }
        return $res->result;
    }
    //--- (generated code: YAPIContext yapiwrapper)
    public static function SetDeviceListValidity($deviceListValidity)
    {
        self::$_yapiContext->SetDeviceListValidity($deviceListValidity);
    }
    public static function GetDeviceListValidity()
    {
        return self::$_yapiContext->GetDeviceListValidity();
    }
    public static function AddUdevRule($force)
    {
        return self::$_yapiContext->AddUdevRule($force);
    }
    public static function SetNetworkTimeout($networkMsTimeout)
    {
        self::$_yapiContext->SetNetworkTimeout($networkMsTimeout);
    }
    public static function GetNetworkTimeout()
    {
        return self::$_yapiContext->GetNetworkTimeout();
    }
    public static function SetCacheValidity($cacheValidityMs)
    {
        self::$_yapiContext->SetCacheValidity($cacheValidityMs);
    }
    public static function GetCacheValidity()
    {
        return self::$_yapiContext->GetCacheValidity();
    }
   #--- (end of generated code: YAPIContext yapiwrapper)
    public static function GetAPIVersion()
    {
        return "1.10.52282";
    }
    public static function SetHTTPCallbackCacheDir($str_directory)
    {
        if (is_null(self::$_hubs)) self::_init();
        if (!is_dir($str_directory)) {
            throw new YAPI_Exception("Directory does not exist");
        }
        if (!is_dir($str_directory)) {
            throw new YAPI_Exception("Directory does not exist");
        }
        if (!is_writable($str_directory)) {
            throw new YAPI_Exception("Directory is not writable");
        }
        if (substr($str_directory, -1) != '/')
            $str_directory .= '/';
        self::$_jzonCacheDir = $str_directory;
    }
    public static function ClearHTTPCallbackCacheDir($bool_removeFiles)
    {
        if (is_null(self::$_hubs) or is_null(self::$_jzonCacheDir)) return;
        if ($bool_removeFiles && is_dir(self::$_jzonCacheDir)) {
            $files = glob(self::$_jzonCacheDir . "{,.}*.json", GLOB_BRACE); // get all file names
            foreach ($files as $file) {
                if (is_file($file))
                    unlink($file);
            }
        }
        self::$_jzonCacheDir = null;
    }
    public static function InitAPI($mode = Y_DETECT_NET, &$errmsg = '')
    {
        if (is_null(self::$_hubs)) self::_init();
        $errmsg = '';
        return YAPI_SUCCESS;
    }
    public static function FreeAPI()
    {
        // leave max 10 second to finish pending requests
        $timeout = YAPI::GetTickCount() + 10000;
        foreach (self::$_pendingRequests as $tcpreq) {
            $request = trim($tcpreq->request);
            if (substr($request, 0, 12) == 'GET /not.byn') {
                continue;
            }
            while (!$tcpreq->eof() && YAPI::GetTickCount() < $timeout) {
                self::_handleEvents_internal(100);
            }
        }
        // clear all caches
        self::_init();
    }
    public static function DisableExceptions()
    {
        if (is_null(self::$_hubs)) self::_init();
        self::$exceptionsDisabled = true;
    }
    public static function EnableExceptions()
    {
        if (is_null(self::$_hubs)) self::_init();
        self::$exceptionsDisabled = false;
    }
    private static function _parseRegisteredURL($str_url)
    {
        $res = [];
        $res['proto'] = 'http';
        if (substr($str_url, 0, 7) == 'http://') {
            $str_url = substr($str_url, 7);
        } else if (substr($str_url, 0, 8) == 'https://') {
            $str_url = substr($str_url, 8);
            $res['proto'] = "https";
        } else if (substr($str_url, 0, 5) == 'ws://') {
            $str_url = substr($str_url, 5);
            $res['proto'] = "ws";
        }
        $subdompos = strpos($str_url, '/');
        if ($subdompos===false){
            $res['subdomain']='';
        } else {
            $res['subdomain'] = substr($str_url,$subdompos);
            while (substr($res['subdomain'], -1) == '/') {
                $res['subdomain'] = substr($res['subdomain'], 0, -1);
            }
            $str_url = substr($str_url, 0, $subdompos);
        }
        $authpos = strpos($str_url, '@');
        if ($authpos === false) {
            $res['auth'] = '';
        } else {
            $res['auth'] = substr($str_url, 0, $authpos);
            $str_url = substr($str_url, $authpos + 1);
        }
        $res['port'] = 4444;
        $p_ofs = strpos($str_url, ':');
        if ($p_ofs !== false) {
            $res['host'] = substr($str_url,0,$p_ofs);
            $res['port'] = (int)substr($str_url,$p_ofs+1);
        } else {
            $res['host'] = $str_url;
        }
        if (strcasecmp(substr($str_url, 0, 8), "callback") == 0) {
            $res['rooturl'] = "http://" . strtoupper($str_url) ;
        } else {
            $res['rooturl'] = "{$res['proto']}://{$res['host']}:{$res['port']}";
        }
        return $res;
    }
    public static function RegisterHub($url, &$errmsg = '')
    {
        if (is_null(self::$_hubs)) self::_init();
        $url_detail = self::_parseRegisteredURL($url);
        // Test hub
        $tcphub = new YTcpHub($url_detail);
        $res = $tcphub->verfiyStreamAddr(true, $errmsg);
        if ($res < 0) {
            return self::_throw(YAPI_IO_ERROR, $errmsg, YAPI_IO_ERROR);
        }
        $timeout = YAPI::GetTickCount() + YAPI::$_yapiContext->_networkTimeoutMs;
        $tcpreq = new YTcpReq($tcphub, "GET /api/module.json", false, '', YAPI::$_yapiContext->_networkTimeoutMs);
        if ($tcpreq->process($errmsg) != YAPI_SUCCESS) {
            return self::_throw($tcpreq->errorType, $errmsg, $tcpreq->errorType);
        }
        self::$_pendingRequests[] = $tcpreq;
        do {
            self::_handleEvents_internal(100);
        } while (!$tcpreq->eof() && YAPI::GetTickCount() < $timeout);
        if (!$tcpreq->eof()) {
            $tcpreq->close();
            $errmsg = 'Timeout waiting for device reply';
            return self::_throw(YAPI_TIMEOUT, $errmsg, YAPI_TIMEOUT);
        }
        if ($tcpreq->errorType == YAPI_UNAUTHORIZED) {
            $errmsg = 'Access denied, authorization required';
            return self::_throw(YAPI_UNAUTHORIZED, $errmsg, YAPI_UNAUTHORIZED);
        } else if ($tcpreq->errorType != YAPI_SUCCESS) {
            $errmsg = 'Network error while testing hub :' . $tcpreq->errorMsg;
            return self::_throw($tcpreq->errorType, $errmsg, $tcpreq->errorType);
        }
        // Add hub to known list
        if (!isset(self::$_hubs[$url_detail['rooturl']])) {
            self::$_hubs[$url_detail['rooturl']] = $tcphub;
        }
        // Register device list
        $yreq = self::_updateDeviceList_internal(true, false);
        if ($yreq->errorType != YAPI_SUCCESS) {
            $errmsg = $yreq->errorMsg;
            return self::_throw($yreq->errorType, $yreq->errorMsg, $yreq->errorType);
        }
        return YAPI_SUCCESS;
    }
    public static function PreregisterHub($url, &$errmsg = '')
    {
        if (is_null(self::$_hubs)) self::_init();
        $url_detail = self::_parseRegisteredURL($url);
        // Add hub to known list
        if (!isset(self::$_hubs[$url_detail['rooturl']])) {
            self::$_hubs[$url_detail['rooturl']] = new YTcpHub($url_detail);
            if (self::$_hubs[$url_detail['rooturl']]->verfiyStreamAddr(true, $errmsg) < 0) {
                return self::_throw(YAPI_IO_ERROR, $errmsg, YAPI_IO_ERROR);
            }
        }
        return YAPI_SUCCESS;
    }
    public static function UnregisterHub($url)
    {
        if (is_null(self::$_hubs))
            return;
        $url_detail = self::_parseRegisteredURL($url);
        $new_hubs = array();
        foreach (self::$_hubs as $hub_url => $hubst) {
            if ($hub_url == $url_detail['rooturl']) {
                // leave max 10 second to finish pending requests
                $timeout = YAPI::GetTickCount() + 10000;
                foreach (self::$_pendingRequests as $tcpreq) {
                    if ($tcpreq->hub->rooturl === $hubst->rooturl) {
                        $request = trim($tcpreq->request);
                        if (substr($request, 0, 12) == 'GET /not.byn') {
                            continue;
                        }
                        while (!$tcpreq->eof() && YAPI::GetTickCount() < $timeout) {
                            self::_handleEvents_internal(100);
                        }
                    }
                }
                // remove all connected devices
                foreach (self::$_hubs[$hub_url]->serialByYdx as $serial) {
                    self::forgetDevice(self::$_devs[$serial]);
                }
                if ($hubst->notifReq) {
                    $hubst->notifReq->close();
                    for ($idx = 0; $idx < sizeof(self::$_pendingRequests); $idx++) {
                        $req = self::$_pendingRequests[$idx];
                        if ($req == $hubst->notifReq) {
                            array_splice(self::$_pendingRequests, $idx, 1);
                        }
                    }
                }
            } else {
                $new_hubs[$hub_url] = self::$_hubs[$hub_url];
            }
        }
        self::$_hubs = $new_hubs;
    }
    public static function TestHub($url, $mstimeout, &$errmsg = '')
    {
        if (is_null(self::$_hubs)) self::_init();
        $url_detail = self::_parseRegisteredURL($url);
        // Test hub
        $tcphub = new YTcpHub($url_detail);
        $res = $tcphub->verfiyStreamAddr(false, $errmsg);
        if ($res < 0) {
            return YAPI_IO_ERROR;
        }
        if ($tcphub->streamaddr == 'tcp://CALLBACK') {
            return YAPI_SUCCESS;
        }
        $tcpreq = new YTcpReq($tcphub, "GET /api/module.json", false, '', $mstimeout);
        $timeout = YAPI::GetTickCount() + $mstimeout;
        do {
            if ($tcpreq->process($errmsg) != YAPI_SUCCESS) {
                return $tcpreq->errorType;
            }
        } while (!$tcpreq->eof() && YAPI::GetTickCount() < $timeout);
        if (!$tcpreq->eof()) {
            $tcpreq->close();
            $errmsg = 'Timeout waiting for device reply';
            return YAPI_TIMEOUT;
        }
        if ($tcpreq->errorType == YAPI_UNAUTHORIZED) {
            $errmsg = 'Access denied, authorization required';
            return YAPI_UNAUTHORIZED;
        } else if ($tcpreq->errorType != YAPI_SUCCESS) {
            $errmsg = 'Network error while testing hub :' . $tcpreq->errorMsg;
            return $tcpreq->errorType;
        }
        return YAPI_SUCCESS;
    }
    static public function _forwardHTTPreq($host, $relurl, $cbdata, &$errmsg)
    {
        $errno = 0;
        $errstr = '';
        $implicitPort = '';
        if (strpos($host, ':') === false) {
            $implicitPort = ':80';
        }
        $skt = stream_socket_client("tcp://$host$implicitPort", $errno, $errstr, 10);
        if ($skt === false) {
            $errmsg = "failed to open socket ($errno): $errstr";
            return YAPI_IO_ERROR;
        }
        $request = "POST $relurl HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n";
        $request .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $request .= "Content-Type: application/json\r\n";
        $request .= "Content-Length: " . strlen($cbdata) . "\r\n\r\n";
        $reqlen = strlen($request);
        if (fwrite($skt, $request, $reqlen) != $reqlen) {
            fclose($skt);
            $errmsg = "failed to write to socket";
            return YAPI_IO_ERROR;
        }
        $bodylen = strlen($cbdata);
        fwrite($skt, $cbdata, $bodylen);
        stream_set_blocking($skt, false);
        $header = '';
        $headerOK = false;
        $chunked = false;
        $chunkhdr = '';
        $chunksize = 0;
        while (true) {
            $data = fread($skt, 8192);
            if ($data === false || !is_resource($skt)) {
                fclose($skt);
                $errmsg = "failed to read from socket";
                return YAPI_IO_ERROR;
            }
            if (strlen($data) == 0) {
                if (feof($skt)) {
                    fclose($skt);
                    if (!$headerOK) {
                        $errmsg = "connection closed unexpectly";
                        return YAPI_IO_ERROR;
                    }
                    return YAPI_SUCCESS;
                } else {
                    $rd = Array($skt);
                    $wr = NULL;
                    $ex = NULL;
                    if (false === ($select_res = stream_select($rd, $wr, $ex, 0, 1000000))) {
                        $errmsg = "stream select error";
                        return YAPI_IO_ERROR;
                    }
                }
                continue;
            }
            if (!$headerOK) {
                $header .= $data;
                $data = '';
                $eoh = strpos($header, "\r\n\r\n");
                if ($eoh !== false) {
                    // fully received header
                    $headerOK = true;
                    $data = substr($header, $eoh + 4);
                    $header = substr($header, 0, $eoh + 4);
                    $lines = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
                    $meta = array();
                    foreach ($lines as $line) {
                        if (preg_match('/([^:]+): (.+)/m', $line, $match)) {
                            $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($matches) {
                                return strtoupper($matches[0]);
                            }, strtolower(trim($match[1])));
                            $meta[$match[1]] = trim($match[2]);
                        }
                    }
                    $firstline = $lines[0];
                    $words = explode(' ', $firstline);
                    $code = $words[1];
                    if ($code == '401') {
                        fclose($skt);
                        $errmsg = "HTTP Authentication not supported";
                        return YAPI_UNAUTHORIZED;
                    } else if ($code == '101') {
                        fclose($skt);
                        $errmsg = "Websocket not supported";
                        return YAPI_NOT_SUPPORTED;
                    } else if ($code >= '300' && $code <= '302' && isset($meta['Location'])) {
                        fclose($skt);
                        return self::_forwardHTTPreq($host, $meta['Location'], $cbdata, $errmsg);
                    } else if (substr($code, 0, 2) != '20' || $code[2] == '3') {
                        fclose($skt);
                        $errmsg = "HTTP error" . substr($firstline, strlen($words[0]));
                        return YAPI_NOT_SUPPORTED;
                    }
                    $chunked = isset($meta['Transfer-Encoding']) && strtolower($meta['Transfer-Encoding']) == 'chunked';
                }
            }
            // process body according to encoding
            if (!$chunked) {
                print $data;
                continue;
            }
            // chunk decoding
            while (strlen($data) > 0) {
                if ($chunksize == 0) {
                    // reading chunk size
                    $chunkhdr .= $data;
                    if (substr($chunkhdr, 0, 2) == "\r\n") {
                        $chunkhdr = substr($chunkhdr, 2);
                    }
                    $endhdr = strpos($chunkhdr, "\r\n");
                    if ($endhdr !== false) {
                        $data = substr($chunkhdr, $endhdr + 2);
                        $sizestr = substr($chunkhdr, 0, $endhdr);
                        $chunksize = hexdec($sizestr);
                        $chunkhdr = '';
                    } else {
                        $data = '';
                    }
                } else {
                    // reading chunk data
                    $datalen = strlen($data);
                    if ($datalen > $chunksize) {
                        $datalen = $chunksize;
                    }
                    print(substr($data, 0, $datalen));
                    $data = substr($data, $datalen);
                    $chunksize -= $datalen;
                }
            }
        }
    }
    public static function ForwardHTTPCallback($url, &$errmsg = "")
    {
        $rooturl = 'callback';
        $url_detail = self::_parseRegisteredURL('callback');
        if (isset(self::$_hubs[$url_detail['rooturl']])) {
            $cb_hub = self::$_hubs[$url_detail['rooturl']];
            // data to post is found in $cb_hub->callbackData
            $url = str_replace('http://', '', $url);
            $pos = strpos($url, '/');
            if ($pos === FALSE) {
                $relurl = '/';
            } else {
                $relurl = substr($url, $pos);
                $url = substr($url, 0, $pos);
            }
            return self::_forwardHTTPreq($url, $relurl, $cb_hub->callbackData, $errmsg);
        } else {
            $errmsg = 'ForwardHTTPCallback must be called AFTER RegisterHub("callback")';
            return YAPI_NOT_INITIALIZED;
        }
    }
    public static function UpdateDeviceList(&$errmsg = '')
    {
        $yreq = self::_updateDeviceList_internal(false, true);
        if ($yreq->errorType != YAPI_SUCCESS) {
            $errmsg = $yreq->errorMsg;
            return self::_throw($yreq->errorType, $yreq->errorMsg, $yreq->errorType);
        }
        return YAPI_SUCCESS;
    }
    public static function HandleEvents(&$errmsg = '')
    {
        // monitor hubs for events
        while(self::_handleEvents_internal(0)) {}
        // handle pending events
        $nEvents = sizeof(self::$_data_events);
        for ($i = 0; $i < $nEvents; $i++) {
            $evt = self::$_data_events[$i];
            if (is_string($evt[1])) {
                $fun = $evt[0];
                // event object is an advertised value
                $fun->_invokeValueCallback($evt[1]);
            } else {
                $ysensor = $evt[0];
                // event object is an array of bytes (encoded timed report)
                $dev = YAPI::getDevice($ysensor->get_module()->get_serialNumber());
                if (!is_null($dev)) {
                    $report = $ysensor->_decodeTimedReport($evt[1], $evt[2], $evt[3]);
                    $ysensor->_invokeTimedReportCallback($report);
                }
            }
        }
        self::$_data_events = array_slice(self::$_data_events, $nEvents);
        $errmsg = '';
        return YAPI_SUCCESS;
    }
    public static function Sleep($ms_duration, &$errmsg = '')
    {
        $end = YAPI::GetTickCount() + $ms_duration;
        self::HandleEvents($errmsg);
        $remain = $end - YAPI::GetTickCount();
        while ($remain > 0) {
            if ($remain > 999) $remain = 999;
            self::_handleEvents_internal($remain);
            self::HandleEvents($errmsg);
            $remain = $end - YAPI::GetTickCount();
        }
        $errmsg = '';
        return YAPI_SUCCESS;
    }
    public static function GetTickCount()
    {
        return round(microtime(true) * 1000);
    }
    public static function CheckLogicalName($name)
    {
        if ($name == '') return true;
        if (!$name) return false;
        if (strlen($name) > 19) return false;
        return preg_match('/^[A-Za-z0-9_\-]*$/', $name);
    }
    public static function RegisterDeviceArrivalCallback($arrivalCallback)
    {
        self::$_arrivalCallback = $arrivalCallback;
    }
    public static function RegisterDeviceChangeCallback($changeCallback)
    {
        self::$_namechgCallback = $changeCallback;
    }
    public static function RegisterDeviceRemovalCallback($removalCallback)
    {
        self::$_removalCallback = $removalCallback;
    }
    // Register a new value calibration handler for a given calibration type
    //
    public static function RegisterCalibrationHandler($calibrationType, $calibrationHandler)
    {
        self::$_calibHandlers[$calibrationType] = $calibrationHandler;
    }
    // Standard value calibration handler (n-point linear error correction)
    //
    public static function LinearCalibrationHandler($float_rawValue, $int_calibType, $arr_calibParams,
                                                    $arr_calibRawValues, $arr_calibRefValues)
    {
        $x = $arr_calibRawValues[0];
        $adj = $arr_calibRefValues[0] - $x;
        $i = 0;
        if ($int_calibType < YOCTO_CALIB_TYPE_OFS) {
            // calibration types n=1..10 are meant for linear calibration using n points
            $npt = min($int_calibType % 10, sizeof($arr_calibRawValues), sizeof($arr_calibRefValues));
        } else {
            $npt = sizeof($arr_calibRefValues);
        }
        while ($float_rawValue > $arr_calibRawValues[$i] && ++$i < $npt) {
            $x2 = $x;
            $adj2 = $adj;
            $x = $arr_calibRawValues[$i];
            $adj = $arr_calibRefValues[$i] - $x;
            if ($float_rawValue < $x && $x > $x2) {
                $adj = $adj2 + ($adj - $adj2) * ($float_rawValue - $x2) / ($x - $x2);
            }
        }
        return $float_rawValue + $adj;
    }
    // Network notification format: 7x7bit (mapped to 7 chars in range 32..159)
    //                              used to represent 1 flag (RAW6BYTES) + 6 bytes
    // INPUT:  [R765432][1076543][2107654][3210765][4321076][5432107][6543210]
    // OUTPUT: 7 bytes array (1 byte for the funcTypeV2 and 6 bytes of USB like data
    //                     funcTypeV2 + [R][-byte 0][-byte 1-][-byte 2-][-byte 3-][-byte 4-][-byte 5-]
    //
    // return null on error
    //
    private static function decodeNetFuncValV2($p)
    {
        $p_ofs = 0;
        $ch = ord($p[$p_ofs]);
        $len = 0;
        $funcVal = array_fill(0, 7, 0);
        if ($ch < 32 || $ch > 32 + 127) {
            return null;
        }
        // get the 7 first bits
        $ch -= 32;
        $funcVal[0] = (($ch & 0x40) != 0 ? NOTIFY_V2_6RAWBYTES : NOTIFY_V2_TYPEDDATA);
        // clear flag
        $ch &= 0x3f;
        while ($len < YOCTO_PUBVAL_SIZE) {
            $p_ofs++;
            if ($p_ofs >= strlen($p))
                break;
            $newCh = ord($p[$p_ofs]);
            if ($newCh == NOTIFY_NETPKT_STOP) {
                break;
            }
            if ($newCh < 32 || $newCh > 32 + 127) {
                return null;
            }
            $newCh -= 32;
            $ch = ($ch << 7) + $newCh;
            $funcVal[$len + 1] = ($ch >> (5 - $len)) & 0xff;
            $len++;
        }
        return $funcVal;
    }
    private static function decodePubVal($typeV2, $funcval, $ofs, $funcvalen)
    {
        $buffer = "";
        if ($typeV2 == NOTIFY_V2_6RAWBYTES || $typeV2 == NOTIFY_V2_TYPEDDATA) {
            if ($typeV2 == NOTIFY_V2_6RAWBYTES) {
                $funcValType = PUBVAL_6RAWBYTES;
            } else {
                $funcValType = $funcval[$ofs++];
            }
            switch ($funcValType) {
                case PUBVAL_LEGACY:
                    // fallback to legacy handling, just in case
                    break;
                case PUBVAL_1RAWBYTE:
                case PUBVAL_2RAWBYTES:
                case PUBVAL_3RAWBYTES:
                case PUBVAL_4RAWBYTES:
                case PUBVAL_5RAWBYTES:
                case PUBVAL_6RAWBYTES:
                    // 1..5 hex bytes
                    for ($i = 0; $i < $funcValType; $i++) {
                        $c = $funcval[$ofs++];
                        $b = $c >> 4;
                        $buffer .= dechex($b);
                        $b = $c & 0xf;
                        $buffer .= dechex($b);
                    }
                    return $buffer;
                case PUBVAL_C_LONG:
                case PUBVAL_YOCTO_FLOAT_E3:
                    // 32bit integer in little endian format or Yoctopuce 10-3 format
                    $numVal = $funcval[$ofs++];
                    $numVal += $funcval[$ofs++] << 8;
                    $numVal += $funcval[$ofs++] << 16;
                    $numVal += $funcval[$ofs++] << 24;
                    if ($funcValType == PUBVAL_C_LONG) {
                        return sprintf("%d", $numVal);
                    } else {
                        $buffer = sprintf("%.3f", $numVal / 1000.0);
                        $endp = strlen($buffer);
                        while ($endp > 0 && $buffer[$endp - 1] == '0') {
                            --$endp;
                        }
                        if ($endp > 0 && $buffer[$endp - 1] == '.') {
                            --$endp;
                            $buffer = substr($buffer, 0, $endp);
                        }
                        return $buffer;
                    }
                case PUBVAL_C_FLOAT:
                    // 32bit (short) float
                    $v = $funcval[$ofs++];
                    $v += $funcval[$ofs++] << 8;
                    $v += $funcval[$ofs++] << 16;
                    $v += $funcval[$ofs++] << 24;
                    $fraction = ($v & ((1 << 23) - 1)) + (1 << 23) * ($v >> 31 | 1);
                    $exp = ($v >> 23 & 0xFF) - 127;
                    $floatVal = $fraction * pow(2, $exp - 23);
                    $buffer = sprintf("%.6f", $floatVal);
                    $endp = strlen($buffer);
                    while ($endp > 0 && $buffer[$endp - 1] == '0') {
                        --$endp;
                    }
                    if ($endp > 0 && $buffer[$endp - 1] == '.') {
                        --$endp;
                        $buffer = substr($buffer, 0, $endp);
                    }
                    return $buffer;
                default:
                    return "?";
            }
        }
        // Legacy handling: just pad with NUL up to 7 chars
        $len = 0;
        $buffer = '';
        while ($len < YOCTO_PUBVAL_SIZE && $len < $funcvalen) {
            if ($funcval[$len] == 0)
                break;
            $buffer .= chr($funcval[$len]);
            $len++;
        }
        return $buffer;
    }
}
//--- (generated code: YMeasure declaration)
class YMeasure
{
    //--- (end of generated code: YMeasure declaration)
    const DATA_INVALID = YAPI_INVALID_DOUBLE;
    //--- (generated code: YMeasure attributes)
    protected $_start                    = 0;                            // float
    protected $_end                      = 0;                            // float
    protected $_minVal                   = 0;                            // float
    protected $_avgVal                   = 0;                            // float
    protected $_maxVal                   = 0;                            // float
    //--- (end of generated code: YMeasure attributes)
    public function __construct($float_start, $float_end, $float_minVal, $float_avgVal, $float_maxVal)
    {
        //--- (generated code: YMeasure constructor)
        //--- (end of generated code: YMeasure constructor)
        $this->_start = $float_start;
        $this->_end = $float_end;
        $this->_minVal = $float_minVal;
        $this->_avgVal = $float_avgVal;
        $this->_maxVal = $float_maxVal;
    }
    //--- (generated code: YMeasure implementation)
    public function get_startTimeUTC()
    {
        return $this->_start;
    }
    public function get_endTimeUTC()
    {
        return $this->_end;
    }
    public function get_minValue()
    {
        return $this->_minVal;
    }
    public function get_averageValue()
    {
        return $this->_avgVal;
    }
    public function get_maxValue()
    {
        return $this->_maxVal;
    }
    //--- (end of generated code: YMeasure implementation)
}
//--- (generated code: YFirmwareUpdate declaration)
class YFirmwareUpdate
{
    //--- (end of generated code: YFirmwareUpdate declaration)
    const DATA_INVALID = YAPI_INVALID_DOUBLE;
    //--- (generated code: YFirmwareUpdate attributes)
    protected $_serial                   = "";                           // str
    protected $_settings                 = "";                           // bin
    protected $_firmwarepath             = "";                           // str
    protected $_progress_msg             = "";                           // str
    protected $_progress_c               = 0;                            // int
    protected $_progress                 = 0;                            // int
    protected $_restore_step             = 0;                            // int
    protected $_force                    = 0;                            // bool
    //--- (end of generated code: YFirmwareUpdate attributes)
    public function __construct($serial, $path, $settings, $force)
    {
        //--- (generated code: YFirmwareUpdate constructor)
        //--- (end of generated code: YFirmwareUpdate constructor)
        $this->_serial = $serial;
        $this->_firmwarepath = $path;
        $this->_settings = $settings;
        $this->_force = $force;
    }
    private function _processMore_internal($i)
    {
        //not yet implemented
        $this->_progress = -1;
        $this->_progress_msg = "Not supported in PHP";
        return $this->_progress;
    }
    private static function CheckFirmware_internal($serial, $path, $minrelease)
    {
        if ($path == "http://www.yoctopuce.com" || $path == "www.yoctopuce.com") {
            $yoctopuce_infos = file_get_contents('http://www.yoctopuce.com/FR/common/getLastFirmwareLink.php?serial=' . $serial);
            if ($yoctopuce_infos === false) {
                return 'error: Unable to get last firmware info from www.yoctopuce.com';
            }
            $jsonData = json_decode($yoctopuce_infos, true);
            if (!array_key_exists('link', $jsonData) || !array_key_exists('version', $jsonData)) {
                return 'error: Invalid JSON response from www.yoctopuce.com';
            }
            $link = $jsonData['link'];
            $version = $jsonData['version'];
            if ($minrelease != "") {
                if ($version > $minrelease) {
                    return $link;
                }
            } else {
                return $link;
            }
            return '';
        } else {
            return 'error: Not yet supported in PHP';
        }
    }
    private static function GetAllBootLoaders_internal()
    {
        return array();
    }
    //--- (generated code: YFirmwareUpdate implementation)
    public function _processMore($newupdate)
    {
        return $this->_processMore_internal($newupdate);
    }
    //cannot be generated for PHP:
    //private function _processMore_internal($newupdate)
    public static function GetAllBootLoaders()
    {
        return self::GetAllBootLoaders_internal();
    }
    //cannot be generated for PHP:
    //private static function GetAllBootLoaders_internal()
    public static function CheckFirmware($serial,$path,$minrelease)
    {
        return self::CheckFirmware_internal($serial,$path,$minrelease);
    }
    //cannot be generated for PHP:
    //private static function CheckFirmware_internal($serial,$path,$minrelease)
    public function get_progress()
    {
        if ($this->_progress >= 0) {
            $this->_processMore(0);
        }
        return $this->_progress;
    }
    public function get_progressMessage()
    {
        return $this->_progress_msg;
    }
    public function startUpdate()
    {
        // $err                    is a str;
        // $leng                   is a int;
        $err = $this->_settings;
        $leng = strlen($err);
        if (($leng >= 6) && ('error:' == substr($err, 0, 6))) {
            $this->_progress = -1;
            $this->_progress_msg = substr($err,  6, $leng - 6);
        } else {
            $this->_progress = 0;
            $this->_progress_c = 0;
            $this->_processMore(1);
        }
        return $this->_progress;
    }
    //--- (end of generated code: YFirmwareUpdate implementation)
}
//--- (generated code: YDataStream declaration)
class YDataStream
{
    //--- (end of generated code: YDataStream declaration)
    const DATA_INVALID = YAPI_INVALID_DOUBLE;
    //--- (generated code: YDataStream attributes)
    protected $_parent                   = null;                         // YFunction
    protected $_runNo                    = 0;                            // int
    protected $_utcStamp                 = 0;                            // u32
    protected $_nCols                    = 0;                            // int
    protected $_nRows                    = 0;                            // int
    protected $_startTime                = 0;                            // float
    protected $_duration                 = 0;                            // float
    protected $_dataSamplesInterval      = 0;                            // float
    protected $_firstMeasureDuration     = 0;                            // float
    protected $_columnNames              = Array();                      // strArr
    protected $_functionId               = "";                           // str
    protected $_isClosed                 = 0;                            // bool
    protected $_isAvg                    = 0;                            // bool
    protected $_minVal                   = 0;                            // float
    protected $_avgVal                   = 0;                            // float
    protected $_maxVal                   = 0;                            // float
    protected $_caltyp                   = 0;                            // int
    protected $_calpar                   = Array();                      // intArr
    protected $_calraw                   = Array();                      // floatArr
    protected $_calref                   = Array();                      // floatArr
    protected $_values                   = Array();                      // floatArrArr
    protected $_isLoaded                 = 0;                            // bool
    //--- (end of generated code: YDataStream attributes)
    public function __construct($obj_parent, $obj_dataset = null, $encoded = null)
    {
        //--- (generated code: YDataStream constructor)
        //--- (end of generated code: YDataStream constructor)
        $this->_parent = $obj_parent;
        $this->_calhdl = null;
        if (!is_null($obj_dataset)) {
            $this->_initFromDataSet($obj_dataset, $encoded);
        }
    }
    //--- (generated code: YDataStream implementation)
    public function _initFromDataSet($dataset,$encoded)
    {
        // $val                    is a int;
        // $i                      is a int;
        // $maxpos                 is a int;
        // $ms_offset              is a int;
        // $samplesPerHour         is a int;
        // $fRaw                   is a float;
        // $fRef                   is a float;
        $iCalib = Array();      // intArr;
        // decode sequence header to extract data
        $this->_runNo = $encoded[0] + ((($encoded[1]) << (16)));
        $this->_utcStamp = $encoded[2] + ((($encoded[3]) << (16)));
        $val = $encoded[4];
        $this->_isAvg = ((($val) & (0x100)) == 0);
        $samplesPerHour = (($val) & (0xff));
        if ((($val) & (0x100)) != 0) {
            $samplesPerHour = $samplesPerHour * 3600;
        } else {
            if ((($val) & (0x200)) != 0) {
                $samplesPerHour = $samplesPerHour * 60;
            }
        }
        $this->_dataSamplesInterval = 3600.0 / $samplesPerHour;
        $ms_offset = $encoded[6];
        if ($ms_offset < 1000) {
            // new encoding -> add the ms to the UTC timestamp
            $this->_startTime = $this->_utcStamp + ($ms_offset / 1000.0);
        } else {
            // legacy encoding subtract the measure interval form the UTC timestamp
            $this->_startTime = $this->_utcStamp -  $this->_dataSamplesInterval;
        }
        $this->_firstMeasureDuration = $encoded[5];
        if (!($this->_isAvg)) {
            $this->_firstMeasureDuration = $this->_firstMeasureDuration / 1000.0;
        }
        $val = $encoded[7];
        $this->_isClosed = ($val != 0xffff);
        if ($val == 0xffff) {
            $val = 0;
        }
        $this->_nRows = $val;
        if ($this->_nRows > 0) {
            if ($this->_firstMeasureDuration > 0) {
                $this->_duration = $this->_firstMeasureDuration + ($this->_nRows - 1) * $this->_dataSamplesInterval;
            } else {
                $this->_duration = $this->_nRows * $this->_dataSamplesInterval;
            }
        } else {
            $this->_duration = 0;
        }
        // precompute decoding parameters
        $iCalib = $dataset->_get_calibration();
        $this->_caltyp = $iCalib[0];
        if ($this->_caltyp != 0) {
            $this->_calhdl = YAPI::_getCalibrationHandler($this->_caltyp);
            $maxpos = sizeof($iCalib);
            while(sizeof($this->_calpar) > 0) { array_pop($this->_calpar); };
            while(sizeof($this->_calraw) > 0) { array_pop($this->_calraw); };
            while(sizeof($this->_calref) > 0) { array_pop($this->_calref); };
            $i = 1;
            while ($i < $maxpos) {
                $this->_calpar[] = $iCalib[$i];
                $i = $i + 1;
            }
            $i = 1;
            while ($i + 1 < $maxpos) {
                $fRaw = $iCalib[$i];
                $fRaw = $fRaw / 1000.0;
                $fRef = $iCalib[$i + 1];
                $fRef = $fRef / 1000.0;
                $this->_calraw[] = $fRaw;
                $this->_calref[] = $fRef;
                $i = $i + 2;
            }
        }
        // preload column names for backward-compatibility
        $this->_functionId = $dataset->get_functionId();
        if ($this->_isAvg) {
            while(sizeof($this->_columnNames) > 0) { array_pop($this->_columnNames); };
            $this->_columnNames[] = sprintf('%s_min', $this->_functionId);
            $this->_columnNames[] = sprintf('%s_avg', $this->_functionId);
            $this->_columnNames[] = sprintf('%s_max', $this->_functionId);
            $this->_nCols = 3;
        } else {
            while(sizeof($this->_columnNames) > 0) { array_pop($this->_columnNames); };
            $this->_columnNames[] = $this->_functionId;
            $this->_nCols = 1;
        }
        // decode min/avg/max values for the sequence
        if ($this->_nRows > 0) {
            $this->_avgVal = $this->_decodeAvg($encoded[8] + ((((($encoded[9]) ^ (0x8000))) << (16))), 1);
            $this->_minVal = $this->_decodeVal($encoded[10] + ((($encoded[11]) << (16))));
            $this->_maxVal = $this->_decodeVal($encoded[12] + ((($encoded[13]) << (16))));
        }
        return 0;
    }
    public function _parseStream($sdata)
    {
        // $idx                    is a int;
        $udat = Array();        // intArr;
        $dat = Array();         // floatArr;
        if ($this->_isLoaded && !($this->_isClosed)) {
            return YAPI_SUCCESS;
        }
        if (strlen($sdata) == 0) {
            $this->_nRows = 0;
            return YAPI_SUCCESS;
        }
        $udat = YAPI::_decodeWords($this->_parent->_json_get_string($sdata));
        while(sizeof($this->_values) > 0) { array_pop($this->_values); };
        $idx = 0;
        if ($this->_isAvg) {
            while ($idx + 3 < sizeof($udat)) {
                while(sizeof($dat) > 0) { array_pop($dat); };
                if (($udat[$idx] == 65535) && ($udat[$idx + 1] == 65535)) {
                    $dat[] = NAN;
                    $dat[] = NAN;
                    $dat[] = NAN;
                } else {
                    $dat[] = $this->_decodeVal($udat[$idx + 2] + ((($udat[$idx + 3]) << (16))));
                    $dat[] = $this->_decodeAvg($udat[$idx] + ((((($udat[$idx + 1]) ^ (0x8000))) << (16))), 1);
                    $dat[] = $this->_decodeVal($udat[$idx + 4] + ((($udat[$idx + 5]) << (16))));
                }
                $idx = $idx + 6;
                $this->_values[] = $dat;
            }
        } else {
            while ($idx + 1 < sizeof($udat)) {
                while(sizeof($dat) > 0) { array_pop($dat); };
                if (($udat[$idx] == 65535) && ($udat[$idx + 1] == 65535)) {
                    $dat[] = NAN;
                } else {
                    $dat[] = $this->_decodeAvg($udat[$idx] + ((((($udat[$idx + 1]) ^ (0x8000))) << (16))), 1);
                }
                $this->_values[] = $dat;
                $idx = $idx + 2;
            }
        }
        $this->_nRows = sizeof($this->_values);
        $this->_isLoaded = true;
        return YAPI_SUCCESS;
    }
    public function _wasLoaded()
    {
        return $this->_isLoaded;
    }
    public function _get_url()
    {
        // $url                    is a str;
        $url = sprintf('logger.json?id=%s&run=%d&utc=%u',
                       $this->_functionId,$this->_runNo,$this->_utcStamp);
        return $url;
    }
    public function _get_baseurl()
    {
        // $url                    is a str;
        $url = sprintf('logger.json?id=%s&run=%d&utc=',
                       $this->_functionId,$this->_runNo);
        return $url;
    }
    public function _get_urlsuffix()
    {
        // $url                    is a str;
        $url = sprintf('%u',$this->_utcStamp);
        return $url;
    }
    public function loadStream()
    {
        return $this->_parseStream($this->_parent->_download($this->_get_url()));
    }
    public function _decodeVal($w)
    {
        // $val                    is a float;
        $val = $w;
        $val = $val / 1000.0;
        if ($this->_caltyp != 0) {
            if (!is_null($this->_calhdl)) {
                $val = call_user_func($this->_calhdl, $val, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
            }
        }
        return $val;
    }
    public function _decodeAvg($dw,$count)
    {
        // $val                    is a float;
        $val = $dw;
        $val = $val / 1000.0;
        if ($this->_caltyp != 0) {
            if (!is_null($this->_calhdl)) {
                $val = call_user_func($this->_calhdl, $val, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
            }
        }
        return $val;
    }
    public function isClosed()
    {
        return $this->_isClosed;
    }
    public function get_runIndex()
    {
        return $this->_runNo;
    }
    public function get_startTime()
    {
        return $this->_utcStamp - time();
    }
    public function get_startTimeUTC()
    {
        return round($this->_startTime);
    }
    public function get_realStartTimeUTC()
    {
        return $this->_startTime;
    }
    public function get_dataSamplesIntervalMs()
    {
        return round($this->_dataSamplesInterval*1000);
    }
    public function get_dataSamplesInterval()
    {
        return $this->_dataSamplesInterval;
    }
    public function get_firstDataSamplesInterval()
    {
        return $this->_firstMeasureDuration;
    }
    public function get_rowCount()
    {
        if (($this->_nRows != 0) && $this->_isClosed) {
            return $this->_nRows;
        }
        $this->loadStream();
        return $this->_nRows;
    }
    public function get_columnCount()
    {
        if ($this->_nCols != 0) {
            return $this->_nCols;
        }
        $this->loadStream();
        return $this->_nCols;
    }
    public function get_columnNames()
    {
        if (sizeof($this->_columnNames) != 0) {
            return $this->_columnNames;
        }
        $this->loadStream();
        return $this->_columnNames;
    }
    public function get_minValue()
    {
        return $this->_minVal;
    }
    public function get_averageValue()
    {
        return $this->_avgVal;
    }
    public function get_maxValue()
    {
        return $this->_maxVal;
    }
    public function get_realDuration()
    {
        if ($this->_isClosed) {
            return $this->_duration;
        }
        return time() - $this->_utcStamp;
    }
    public function get_dataRows()
    {
        if ((sizeof($this->_values) == 0) || !($this->_isClosed)) {
            $this->loadStream();
        }
        return $this->_values;
    }
    public function get_data($row,$col)
    {
        if ((sizeof($this->_values) == 0) || !($this->_isClosed)) {
            $this->loadStream();
        }
        if ($row >= sizeof($this->_values)) {
            return Y_DATA_INVALID;
        }
        if ($col >= sizeof($this->_values[$row])) {
            return Y_DATA_INVALID;
        }
        return $this->_values[$row][$col];
    }
    //--- (end of generated code: YDataStream implementation)
}
//--- (generated code: YDataSet declaration)
class YDataSet
{
    //--- (end of generated code: YDataSet declaration)
    const DATA_INVALID = YAPI_INVALID_DOUBLE;
    //--- (generated code: YDataSet attributes)
    protected $_parent                   = null;                         // YFunction
    protected $_hardwareId               = "";                           // str
    protected $_functionId               = "";                           // str
    protected $_unit                     = "";                           // str
    protected $_bulkLoad                 = 0;                            // int
    protected $_startTimeMs              = 0;                            // float
    protected $_endTimeMs                = 0;                            // float
    protected $_progress                 = 0;                            // int
    protected $_calib                    = Array();                      // intArr
    protected $_streams                  = Array();                      // YDataStreamArr
    protected $_summary                  = null;                         // YMeasure
    protected $_preview                  = Array();                      // YMeasureArr
    protected $_measures                 = Array();                      // YMeasureArr
    protected $_summaryMinVal            = 0;                            // float
    protected $_summaryMaxVal            = 0;                            // float
    protected $_summaryTotalAvg          = 0;                            // float
    protected $_summaryTotalTime         = 0;                            // float
    //--- (end of generated code: YDataSet attributes)
    public function __construct($obj_parent, $str_functionId = null, $str_unit = null, $float_startTime = null, $float_endTime = null)
    {
        //--- (generated code: YDataSet constructor)
        //--- (end of generated code: YDataSet constructor)
        $this->_summary = new YMeasure(0, 0, 0, 0, 0);
        if (is_null($str_unit)) {
            // 1st version of constructor, called from YDataLogger
            $this->_parent = $obj_parent;
            $this->_startTime = 0;
            $this->_endTime = 0;
        } else {
            // 2nd version of constructor, called from YFunction
            $this->_parent = $obj_parent;
            $this->_functionId = $str_functionId;
            $this->_unit = $str_unit;
            $this->_startTimeMs = $float_startTime * 1000;
            $this->_endTimeMs = $float_endTime * 1000;
            $this->_progress = -1;
        }
    }
    //--- (generated code: YDataSet implementation)
    public function _get_calibration()
    {
        return $this->_calib;
    }
    public function loadSummary($data)
    {
        $dataRows = Array();    // floatArrArr;
        // $tim                    is a float;
        // $mitv                   is a float;
        // $itv                    is a float;
        // $fitv                   is a float;
        // $end_                   is a float;
        // $nCols                  is a int;
        // $minCol                 is a int;
        // $avgCol                 is a int;
        // $maxCol                 is a int;
        // $res                    is a int;
        // $m_pos                  is a int;
        // $previewTotalTime       is a float;
        // $previewTotalAvg        is a float;
        // $previewMinVal          is a float;
        // $previewMaxVal          is a float;
        // $previewAvgVal          is a float;
        // $previewStartMs         is a float;
        // $previewStopMs          is a float;
        // $previewDuration        is a float;
        // $streamStartTimeMs      is a float;
        // $streamDuration         is a float;
        // $streamEndTimeMs        is a float;
        // $minVal                 is a float;
        // $avgVal                 is a float;
        // $maxVal                 is a float;
        // $summaryStartMs         is a float;
        // $summaryStopMs          is a float;
        // $summaryTotalTime       is a float;
        // $summaryTotalAvg        is a float;
        // $summaryMinVal          is a float;
        // $summaryMaxVal          is a float;
        // $url                    is a str;
        // $strdata                is a str;
        $measure_data = Array(); // floatArr;
        if ($this->_progress < 0) {
            $strdata = $data;
            if ($strdata == '{}') {
                $this->_parent->_throw(YAPI_VERSION_MISMATCH, 'device firmware is too old');
                return YAPI_VERSION_MISMATCH;
            }
            $res = $this->_parse($strdata);
            if ($res < 0) {
                return $res;
            }
        }
        $summaryTotalTime = 0;
        $summaryTotalAvg = 0;
        $summaryMinVal = YAPI_MAX_DOUBLE;
        $summaryMaxVal = YAPI_MIN_DOUBLE;
        $summaryStartMs = YAPI_MAX_DOUBLE;
        $summaryStopMs = YAPI_MIN_DOUBLE;
        // Parse complete streams
        foreach( $this->_streams as $each) {
            $streamStartTimeMs = round($each->get_realStartTimeUTC() *1000);
            $streamDuration = $each->get_realDuration() ;
            $streamEndTimeMs = $streamStartTimeMs + round($streamDuration * 1000);
            if (($streamStartTimeMs >= $this->_startTimeMs) && (($this->_endTimeMs == 0) || ($streamEndTimeMs <= $this->_endTimeMs))) {
                // stream that are completely inside the dataset
                $previewMinVal = $each->get_minValue();
                $previewAvgVal = $each->get_averageValue();
                $previewMaxVal = $each->get_maxValue();
                $previewStartMs = $streamStartTimeMs;
                $previewStopMs = $streamEndTimeMs;
                $previewDuration = $streamDuration;
            } else {
                // stream that are partially in the dataset
                // we need to parse data to filter value outside the dataset
                if (!($each->_wasLoaded())) {
                    $url = $each->_get_url();
                    $data = $this->_parent->_download($url);
                    $each->_parseStream($data);
                }
                $dataRows = $each->get_dataRows();
                if (sizeof($dataRows) == 0) {
                    return $this->get_progress();
                }
                $tim = $streamStartTimeMs;
                $fitv = round($each->get_firstDataSamplesInterval() * 1000);
                $itv = round($each->get_dataSamplesInterval() * 1000);
                $nCols = sizeof($dataRows[0]);
                $minCol = 0;
                if ($nCols > 2) {
                    $avgCol = 1;
                } else {
                    $avgCol = 0;
                }
                if ($nCols > 2) {
                    $maxCol = 2;
                } else {
                    $maxCol = 0;
                }
                $previewTotalTime = 0;
                $previewTotalAvg = 0;
                $previewStartMs = $streamEndTimeMs;
                $previewStopMs = $streamStartTimeMs;
                $previewMinVal = YAPI_MAX_DOUBLE;
                $previewMaxVal = YAPI_MIN_DOUBLE;
                $m_pos = 0;
                while ($m_pos < sizeof($dataRows)) {
                    $measure_data  = $dataRows[$m_pos];
                    if ($m_pos == 0) {
                        $mitv = $fitv;
                    } else {
                        $mitv = $itv;
                    }
                    $end_ = $tim + $mitv;
                    if (($end_ > $this->_startTimeMs) && (($this->_endTimeMs == 0) || ($tim < $this->_endTimeMs))) {
                        $minVal = $measure_data[$minCol];
                        $avgVal = $measure_data[$avgCol];
                        $maxVal = $measure_data[$maxCol];
                        if ($previewStartMs > $tim) {
                            $previewStartMs = $tim;
                        }
                        if ($previewStopMs < $end_) {
                            $previewStopMs = $end_;
                        }
                        if ($previewMinVal > $minVal) {
                            $previewMinVal = $minVal;
                        }
                        if ($previewMaxVal < $maxVal) {
                            $previewMaxVal = $maxVal;
                        }
                        if (!(is_nan($avgVal))) {
                            $previewTotalAvg = $previewTotalAvg + ($avgVal * $mitv);
                            $previewTotalTime = $previewTotalTime + $mitv;
                        }
                    }
                    $tim = $end_;
                    $m_pos = $m_pos + 1;
                }
                if ($previewTotalTime > 0) {
                    $previewAvgVal = $previewTotalAvg / $previewTotalTime;
                    $previewDuration = ($previewStopMs - $previewStartMs) / 1000.0;
                } else {
                    $previewAvgVal = 0.0;
                    $previewDuration = 0.0;
                }
            }
            $this->_preview[] = new YMeasure($previewStartMs / 1000.0, $previewStopMs / 1000.0, $previewMinVal, $previewAvgVal, $previewMaxVal);
            if ($summaryMinVal > $previewMinVal) {
                $summaryMinVal = $previewMinVal;
            }
            if ($summaryMaxVal < $previewMaxVal) {
                $summaryMaxVal = $previewMaxVal;
            }
            if ($summaryStartMs > $previewStartMs) {
                $summaryStartMs = $previewStartMs;
            }
            if ($summaryStopMs < $previewStopMs) {
                $summaryStopMs = $previewStopMs;
            }
            $summaryTotalAvg = $summaryTotalAvg + ($previewAvgVal * $previewDuration);
            $summaryTotalTime = $summaryTotalTime + $previewDuration;
        }
        if (($this->_startTimeMs == 0) || ($this->_startTimeMs > $summaryStartMs)) {
            $this->_startTimeMs = $summaryStartMs;
        }
        if (($this->_endTimeMs == 0) || ($this->_endTimeMs < $summaryStopMs)) {
            $this->_endTimeMs = $summaryStopMs;
        }
        if ($summaryTotalTime > 0) {
            $this->_summary = new YMeasure($summaryStartMs / 1000.0, $summaryStopMs / 1000.0, $summaryMinVal, $summaryTotalAvg / $summaryTotalTime, $summaryMaxVal);
        } else {
            $this->_summary = new YMeasure(0.0, 0.0, YAPI_INVALID_DOUBLE, YAPI_INVALID_DOUBLE, YAPI_INVALID_DOUBLE);
        }
        return $this->get_progress();
    }
    public function processMore($progress,$data)
    {
        // $stream                 is a YDataStream;
        $dataRows = Array();    // floatArrArr;
        // $tim                    is a float;
        // $itv                    is a float;
        // $fitv                   is a float;
        // $avgv                   is a float;
        // $end_                   is a float;
        // $nCols                  is a int;
        // $minCol                 is a int;
        // $avgCol                 is a int;
        // $maxCol                 is a int;
        // $firstMeasure           is a bool;
        // $baseurl                is a str;
        // $url                    is a str;
        // $suffix                 is a str;
        $suffixes = Array();    // strArr;
        // $idx                    is a int;
        // $bulkFile               is a bin;
        $streamStr = Array();   // strArr;
        // $urlIdx                 is a int;
        // $streamBin              is a bin;
        if ($progress != $this->_progress) {
            return $this->_progress;
        }
        if ($this->_progress < 0) {
            return $this->loadSummary($data);
        }
        $stream = $this->_streams[$this->_progress];
        if (!($stream->_wasLoaded())) {
            $stream->_parseStream($data);
        }
        $dataRows = $stream->get_dataRows();
        $this->_progress = $this->_progress + 1;
        if (sizeof($dataRows) == 0) {
            return $this->get_progress();
        }
        $tim = round($stream->get_realStartTimeUTC() * 1000);
        $fitv = round($stream->get_firstDataSamplesInterval() * 1000);
        $itv = round($stream->get_dataSamplesInterval() * 1000);
        if ($fitv == 0) {
            $fitv = $itv;
        }
        if ($tim < $itv) {
            $tim = $itv;
        }
        $nCols = sizeof($dataRows[0]);
        $minCol = 0;
        if ($nCols > 2) {
            $avgCol = 1;
        } else {
            $avgCol = 0;
        }
        if ($nCols > 2) {
            $maxCol = 2;
        } else {
            $maxCol = 0;
        }
        $firstMeasure = true;
        foreach($dataRows as $each) {
            if ($firstMeasure) {
                $end_ = $tim + $fitv;
                $firstMeasure = false;
            } else {
                $end_ = $tim + $itv;
            }
            $avgv = $each[$avgCol];
            if (($end_ > $this->_startTimeMs) && (($this->_endTimeMs == 0) || ($tim < $this->_endTimeMs)) && !(is_nan($avgv))) {
                $this->_measures[] = new YMeasure($tim / 1000, $end_ / 1000, $each[$minCol], $avgv, $each[$maxCol]);
            }
            $tim = $end_;
        }
        // Perform bulk preload to speed-up network transfer
        if (($this->_bulkLoad > 0) && ($this->_progress < sizeof($this->_streams))) {
            $stream = $this->_streams[$this->_progress];
            if ($stream->_wasLoaded()) {
                return $this->get_progress();
            }
            $baseurl = $stream->_get_baseurl();
            $url = $stream->_get_url();
            $suffix = $stream->_get_urlsuffix();
            $suffixes[] = $suffix;
            $idx = $this->_progress+1;
            while (($idx < sizeof($this->_streams)) && (sizeof($suffixes) < $this->_bulkLoad)) {
                $stream = $this->_streams[$idx];
                if (!($stream->_wasLoaded()) && ($stream->_get_baseurl() == $baseurl)) {
                    $suffix = $stream->_get_urlsuffix();
                    $suffixes[] = $suffix;
                    $url = $url . ',' . $suffix;
                }
                $idx = $idx + 1;
            }
            $bulkFile = $this->_parent->_download($url);
            $streamStr = $this->_parent->_json_get_array($bulkFile);
            $urlIdx = 0;
            $idx = $this->_progress;
            while (($idx < sizeof($this->_streams)) && ($urlIdx < sizeof($suffixes)) && ($urlIdx < sizeof($streamStr))) {
                $stream = $this->_streams[$idx];
                if (($stream->_get_baseurl() == $baseurl) && ($stream->_get_urlsuffix() == $suffixes[$urlIdx])) {
                    $streamBin = $streamStr[$urlIdx];
                    $stream->_parseStream($streamBin);
                    $urlIdx = $urlIdx + 1;
                }
                $idx = $idx + 1;
            }
        }
        return $this->get_progress();
    }
    public function get_privateDataStreams()
    {
        return $this->_streams;
    }
    public function get_hardwareId()
    {
        // $mo                     is a YModule;
        if (!($this->_hardwareId == '')) {
            return $this->_hardwareId;
        }
        $mo = $this->_parent->get_module();
        $this->_hardwareId = sprintf('%s.%s', $mo->get_serialNumber(), $this->get_functionId());
        return $this->_hardwareId;
    }
    public function get_functionId()
    {
        return $this->_functionId;
    }
    public function get_unit()
    {
        return $this->_unit;
    }
    public function get_startTimeUTC()
    {
        return $this->imm_get_startTimeUTC();
    }
    public function imm_get_startTimeUTC()
    {
        return ($this->_startTimeMs / 1000.0);
    }
    public function get_endTimeUTC()
    {
        return $this->imm_get_endTimeUTC();
    }
    public function imm_get_endTimeUTC()
    {
        return round($this->_endTimeMs / 1000.0);
    }
    public function get_progress()
    {
        if ($this->_progress < 0) {
            return 0;
        }
        // index not yet loaded
        if ($this->_progress >= sizeof($this->_streams)) {
            return 100;
        }
        return intVal((1 + (1 + $this->_progress) * 98 ) / ((1 + sizeof($this->_streams))));
    }
    public function loadMore()
    {
        // $url                    is a str;
        // $stream                 is a YDataStream;
        if ($this->_progress < 0) {
            $url = sprintf('logger.json?id=%s',$this->_functionId);
            if ($this->_startTimeMs != 0) {
                $url = sprintf('%s&from=%u',$url,$this->imm_get_startTimeUTC());
            }
            if ($this->_endTimeMs != 0) {
                $url = sprintf('%s&to=%u',$url,$this->imm_get_endTimeUTC()+1);
            }
        } else {
            if ($this->_progress >= sizeof($this->_streams)) {
                return 100;
            } else {
                $stream = $this->_streams[$this->_progress];
                if ($stream->_wasLoaded()) {
                    // Do not reload stream if it was already loaded
                    return $this->processMore($this->_progress, '');
                }
                $url = $stream->_get_url();
            }
        }
        try {
            return $this->processMore($this->_progress, $this->_parent->_download($url));
        } catch (Exception $ex) {
            return $this->processMore($this->_progress, $this->_parent->_download($url));
        }
    }
    public function get_summary()
    {
        return $this->_summary;
    }
    public function get_preview()
    {
        return $this->_preview;
    }
    public function get_measuresAt($measure)
    {
        // $startUtcMs             is a float;
        // $stream                 is a YDataStream;
        $dataRows = Array();    // floatArrArr;
        $measures = Array();    // YMeasureArr;
        // $tim                    is a float;
        // $itv                    is a float;
        // $end_                   is a float;
        // $nCols                  is a int;
        // $minCol                 is a int;
        // $avgCol                 is a int;
        // $maxCol                 is a int;
        $startUtcMs = $measure.get_startTimeUTC() * 1000;
        $stream = null;
        foreach($this->_streams as $each) {
            if (round($each->get_realStartTimeUTC() *1000) == $startUtcMs) {
                $stream = $each;
            }
        }
        if ($stream == null) {
            return $measures;
        }
        $dataRows = $stream->get_dataRows();
        if (sizeof($dataRows) == 0) {
            return $measures;
        }
        $tim = round($stream->get_realStartTimeUTC() * 1000);
        $itv = round($stream->get_dataSamplesInterval() * 1000);
        if ($tim < $itv) {
            $tim = $itv;
        }
        $nCols = sizeof($dataRows[0]);
        $minCol = 0;
        if ($nCols > 2) {
            $avgCol = 1;
        } else {
            $avgCol = 0;
        }
        if ($nCols > 2) {
            $maxCol = 2;
        } else {
            $maxCol = 0;
        }
        foreach($dataRows as $each) {
            $end_ = $tim + $itv;
            if (($end_ > $this->_startTimeMs) && (($this->_endTimeMs == 0) || ($tim < $this->_endTimeMs))) {
                $measures[] = new YMeasure($tim / 1000.0, $end_ / 1000.0, $each[$minCol], $each[$avgCol], $each[$maxCol]);
            }
            $tim = $end_;
        }
        return $measures;
    }
    public function get_measures()
    {
        return $this->_measures;
    }
    //--- (end of generated code: YDataSet implementation)
    // YDataSet parser for stream list
    public function _parse($str_json)
    {
        $loadval = json_decode(iconv("ISO-8859-1", "UTF-8", $str_json), true);
        $this->_functionId = $loadval['id'];
        $this->_unit = $loadval['unit'];
        $this->_bulkLoad = (isset($loadval['bulk']) ? intval($loadval['bulk']) : 0);
        if (isset($loadval['calib'])) {
            $this->_calib = YAPI::_decodeFloats($loadval['calib']);
            $this->_calib[0] = intVal($this->_calib[0] / 1000);
        } else {
            $this->_calib = YAPI::_decodeWords($loadval['cal']);
        }
        $this->_summary = new YMeasure(0, 0, 0, 0, 0);
        $this->_streams = Array();
        $this->_preview = Array();
        $this->_measures = Array();
        for ($i = 0; $i < sizeof($loadval['streams']); $i++) {
            $stream = $this->_parent->_findDataStream($this, $loadval['streams'][$i]);
            $streamStartTime = $stream->get_realstartTimeUTC() * 1000;
            $streamEndTime = $streamStartTime + $stream->get_realDuration() * 1000;
            if ($this->_startTimeMs > 0 && $streamEndTime <= $this->_startTimeMs) {
                // this stream is too early, drop it
            } else if ($this->_endTimeMs > 0 && $streamStartTime >= $this->_endTimeMs) {
                // this stream is too late, drop it
            } else {
                $this->_streams[] = $stream;
            }
        }
        $this->_progress = 0;
        return $this->get_progress();
    }
}
//--- (generated code: YConsolidatedDataSet declaration)
class YConsolidatedDataSet
{
    //--- (end of generated code: YConsolidatedDataSet declaration)
    //--- (generated code: YConsolidatedDataSet attributes)
    protected $_start                    = 0;                            // float
    protected $_end                      = 0;                            // float
    protected $_nsensors                 = 0;                            // int
    protected $_sensors                  = Array();                      // YSensorArr
    protected $_datasets                 = Array();                      // YDataSetArr
    protected $_progresss                = Array();                      // intArr
    protected $_nextidx                  = Array();                      // intArr
    protected $_nexttim                  = Array();                      // floatArr
    //--- (end of generated code: YConsolidatedDataSet attributes)
    public function __construct($float_startTime, $float_endTime, $obj_sensorList)
    {
        //--- (generated code: YConsolidatedDataSet constructor)
        //--- (end of generated code: YConsolidatedDataSet constructor)
        $this->imm_init($float_startTime, $float_endTime, $obj_sensorList);
    }
    //--- (generated code: YConsolidatedDataSet implementation)
    public function imm_init($startt,$endt,$sensorList)
    {
        $this->_start = $startt;
        $this->_end = $endt;
        $this->_sensors = $sensorList;
        $this->_nsensors = -1;
        return YAPI_SUCCESS;
    }
    public static function Init($sensorNames,$startTime,$endTime)
    {
        // $nSensors               is a int;
        $sensorList = Array();  // YSensorArr;
        // $idx                    is a int;
        // $sensorName             is a str;
        // $s                      is a YSensor;
        // $obj                    is a YConsolidatedDataSet;
        $nSensors = sizeof($sensorNames);
        while(sizeof($sensorList) > 0) { array_pop($sensorList); };
        $idx = 0;
        while ($idx < $nSensors) {
            $sensorName = $sensorNames[$idx];
            $s = YSensor::FindSensor($sensorName);
            $sensorList[] = $s;
            $idx = $idx + 1;
        }
        $obj = new YConsolidatedDataSet($startTime, $endTime, $sensorList);
        return $obj;
    }
    public function nextRecord(&$datarec)
    {
        // $s                      is a int;
        // $idx                    is a int;
        // $sensor                 is a YSensor;
        // $newdataset             is a YDataSet;
        // $globprogress           is a int;
        // $currprogress           is a int;
        // $currnexttim            is a float;
        // $newvalue               is a float;
        $measures = Array();    // YMeasureArr;
        // $nexttime               is a float;
        //
        // Ensure the dataset have been retrieved
        //
        if ($this->_nsensors == -1) {
            $this->_nsensors = sizeof($this->_sensors);
            while(sizeof($this->_datasets) > 0) { array_pop($this->_datasets); };
            while(sizeof($this->_progresss) > 0) { array_pop($this->_progresss); };
            while(sizeof($this->_nextidx) > 0) { array_pop($this->_nextidx); };
            while(sizeof($this->_nexttim) > 0) { array_pop($this->_nexttim); };
            $s = 0;
            while ($s < $this->_nsensors) {
                $sensor = $this->_sensors[$s];
                $newdataset = $sensor->get_recordedData($this->_start, $this->_end);
                $this->_datasets[] = $newdataset;
                $this->_progresss[] = 0;
                $this->_nextidx[] = 0;
                $this->_nexttim[] = 0.0;
                $s = $s + 1;
            }
        }
        while(sizeof($datarec) > 0) { array_pop($datarec); };
        //
        // Find next timestamp to process
        //
        $nexttime = 0;
        $s = 0;
        while ($s < $this->_nsensors) {
            $currnexttim = $this->_nexttim[$s];
            if ($currnexttim == 0) {
                $idx = $this->_nextidx[$s];
                $measures = $this->_datasets[$s]->get_measures();
                $currprogress = $this->_progresss[$s];
                while (($idx >= sizeof($measures)) && ($currprogress < 100)) {
                    $currprogress = $this->_datasets[$s]->loadMore();
                    if ($currprogress < 0) {
                        $currprogress = 100;
                    }
                    $this->_progresss[$s] = $currprogress;
                    $measures = $this->_datasets[$s]->get_measures();
                }
                if ($idx < sizeof($measures)) {
                    $currnexttim = $measures[$idx]->get_endTimeUTC();
                    $this->_nexttim[$s] = $currnexttim;
                }
            }
            if ($currnexttim > 0) {
                if (($nexttime == 0) || ($nexttime > $currnexttim)) {
                    $nexttime = $currnexttim;
                }
            }
            $s = $s + 1;
        }
        if ($nexttime == 0) {
            return 100;
        }
        //
        // Extract data for $this timestamp
        //
        while(sizeof($datarec) > 0) { array_pop($datarec); };
        $datarec[] = $nexttime;
        $globprogress = 0;
        $s = 0;
        while ($s < $this->_nsensors) {
            if ($this->_nexttim[$s] == $nexttime) {
                $idx = $this->_nextidx[$s];
                $measures = $this->_datasets[$s]->get_measures();
                $newvalue = $measures[$idx]->get_averageValue();
                $datarec[] = $newvalue;
                $this->_nexttim[$s] = 0.0;
                $this->_nextidx[$s] = $idx+1;
            } else {
                $datarec[] = NAN;
            }
            $currprogress = $this->_progresss[$s];
            $globprogress = $globprogress + $currprogress;
            $s = $s + 1;
        }
        if ($globprogress > 0) {
            $globprogress = intVal(($globprogress) / ($this->_nsensors));
            if ($globprogress > 99) {
                $globprogress = 99;
            }
        }
        return $globprogress;
    }
    //--- (end of generated code: YConsolidatedDataSet implementation)
}
//--- (generated code: YFunction declaration)
class YFunction
{
    const LOGICALNAME_INVALID            = YAPI_INVALID_STRING;
    const ADVERTISEDVALUE_INVALID        = YAPI_INVALID_STRING;
    //--- (end of generated code: YFunction declaration)
    public static $_TimedReportCallbackList = Array();
    public static $_ValueCallbackList = Array();
    protected $_className     = 'Function';
    protected $_func;
    protected $_lastErrorType = YAPI_SUCCESS;
    protected $_lastErrorMsg  = 'no error';
    protected $_dataStreams;
    protected $_userData      = NULL;
    protected $_cache;
    //--- (generated code: YFunction attributes)
    protected $_logicalName              = Y_LOGICALNAME_INVALID;        // Text
    protected $_advertisedValue          = Y_ADVERTISEDVALUE_INVALID;    // PubText
    protected $_valueCallbackFunction    = null;                         // YFunctionValueCallback
    protected $_cacheExpiration          = 0;                            // ulong
    protected $_serial                   = "";                           // str
    protected $_funId                    = "";                           // str
    protected $_hwId                     = "";                           // str
    //--- (end of generated code: YFunction attributes)
    function __construct($str_func)
    {
        $this->_func = $str_func;
        $this->_cache = Array('_expiration' => 0);
        $this->_dataStreams = Array();
        //--- (generated code: YFunction constructor)
        //--- (end of generated code: YFunction constructor)
    }
    // internal helper for YFunctionType
    function _getHwId()
    {
        return $this->_hwId;
    }
    private function isReadOnly_internal()
    {
        try {
            $serial = $this->get_serialNumber();
            return YAPI::isReadOnly($serial);
        } catch (Exception $ignore) {
            return true;
        }
    }
    //--- (generated code: YFunction implementation)
    function _parseAttr($name, $val)
    {
        switch($name) {
        case '_expiration':
            $this->_cacheExpiration = $val;
            return 1;
        case 'logicalName':
            $this->_logicalName = $val;
            return 1;
        case 'advertisedValue':
            $this->_advertisedValue = $val;
            return 1;
        }
        return 0;
    }
    public function get_logicalName()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_LOGICALNAME_INVALID;
            }
        }
        $res = $this->_logicalName;
        return $res;
    }
    public function set_logicalName($newval)
    {
        if (!YAPI::CheckLogicalName($newval))
            return $this->_throw(YAPI_INVALID_ARGUMENT,'Invalid name :'.$newval);
        $rest_val = $newval;
        return $this->_setAttr("logicalName",$rest_val);
    }
    public function get_advertisedValue()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_ADVERTISEDVALUE_INVALID;
            }
        }
        $res = $this->_advertisedValue;
        return $res;
    }
    public function set_advertisedValue($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("advertisedValue",$rest_val);
    }
    public static function FindFunction($func)
    {
        // $obj                    is a YFunction;
        $obj = YFunction::_FindFromCache('Function', $func);
        if ($obj == null) {
            $obj = new YFunction($func);
            YFunction::_AddToCache('Function', $func, $obj);
        }
        return $obj;
    }
    public function registerValueCallback($callback)
    {
        // $val                    is a str;
        if (!is_null($callback)) {
            YFunction::_UpdateValueCallbackList($this, true);
        } else {
            YFunction::_UpdateValueCallbackList($this, false);
        }
        $this->_valueCallbackFunction = $callback;
        // Immediately invoke value callback with current value
        if (!is_null($callback) && $this->isOnline()) {
            $val = $this->_advertisedValue;
            if (!($val == '')) {
                $this->_invokeValueCallback($val);
            }
        }
        return 0;
    }
    public function _invokeValueCallback($value)
    {
        if (!is_null($this->_valueCallbackFunction)) {
            call_user_func($this->_valueCallbackFunction, $this, $value);
        } else {
        }
        return 0;
    }
    public function muteValueCallbacks()
    {
        return $this->set_advertisedValue('SILENT');
    }
    public function unmuteValueCallbacks()
    {
        return $this->set_advertisedValue('');
    }
    public function loadAttribute($attrName)
    {
        // $url                    is a str;
        // $attrVal                is a bin;
        $url = sprintf('api/%s/%s', $this->get_functionId(), $attrName);
        $attrVal = $this->_download($url);
        return $attrVal;
    }
    public function isReadOnly()
    {
        return $this->isReadOnly_internal();
    }
    //cannot be generated for PHP:
    //private function isReadOnly_internal()
    public function get_serialNumber()
    {
        // $m                      is a YModule;
        $m = $this->get_module();
        return $m->get_serialNumber();
    }
    public function _parserHelper()
    {
        return 0;
    }
    public function logicalName()
    { return $this->get_logicalName(); }
    public function setLogicalName($newval)
    { return $this->set_logicalName($newval); }
    public function advertisedValue()
    { return $this->get_advertisedValue(); }
    public function setAdvertisedValue($newval)
    { return $this->set_advertisedValue($newval); }
    public function nextFunction()
    {   $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if($resolve->errorType != YAPI_SUCCESS) return null;
        $next_hwid = YAPI::getNextHardwareId($this->_className, $resolve->result);
        if($next_hwid == null) return null;
        return self::FindFunction($next_hwid);
    }
    public static function FirstFunction()
    {   $next_hwid = YAPI::getFirstHardwareId('Function');
        if($next_hwid == null) return null;
        return self::FindFunction($next_hwid);
    }
    //--- (end of generated code: YFunction implementation)
    public static function _FindFromCache($className, $func)
    {
        return YAPI::getFunction($className, $func);
    }
    public static function _AddToCache($className, $func, $obj)
    {
        YAPI::setFunction($className, $func, $obj);
    }
    public static function _ClearCache()
    {
        YAPI::_init();
    }
    public static function _UpdateValueCallbackList($obj_func, $bool_add)
    {
        $index = array_search($obj_func, self::$_ValueCallbackList);
        if ($bool_add) {
            $obj_func->isOnline();
            if ($index === false) {
                self::$_ValueCallbackList[] = $obj_func;
            }
        } else if ($index !== false) {
            array_splice(self::$_ValueCallbackList, $index, 1);
        }
    }
    public static function _UpdateTimedReportCallbackList($obj_func, $bool_add)
    {
        $index = array_search($obj_func, self::$_TimedReportCallbackList);
        if ($bool_add) {
            $obj_func->isOnline();
            if ($index === false) {
                self::$_TimedReportCallbackList[] = $obj_func;
            }
        } else if ($index !== false) {
            array_splice(self::$_TimedReportCallbackList, $index, 1);
        }
    }
    // Throw an exception, keeping track of it in the object itself
    public function _throw($int_errType, $str_errMsg, $obj_retVal = null)
    {
        $this->_lastErrorType = $int_errType;
        $this->_lastErrorMsg = $str_errMsg;
        if (YAPI::$exceptionsDisabled) {
            return $obj_retVal;
        }
        // throw an exception
        throw new YAPI_Exception($str_errMsg, $int_errType);
    }
    public function describe()
    {
        $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if ($resolve->errorType != YAPI_SUCCESS && $resolve->result != $this->_func) {
            return $this->_className . "({$this->_func})=unresolved";
        }
        return $this->_className . "({$this->_func})={$resolve->result}";
    }
    public function get_hardwareId()
    {
        $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if ($resolve->errorType != YAPI_SUCCESS) {
            $this->isOnline();
            $resolve = YAPI::resolveFunction($this->_className, $this->_func);
            if ($resolve->errorType != YAPI_SUCCESS) {
                return $this->_throw($resolve->errorType, $resolve->errorMsg, Y_HARDWAREID_INVALID);
            }
        }
        return $resolve->result;
    }
    public function get_functionId()
    {
        $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if ($resolve->errorType != YAPI_SUCCESS) {
            $this->isOnline();
            $resolve = YAPI::resolveFunction($this->_className, $this->_func);
            if ($resolve->errorType != YAPI_SUCCESS) {
                return $this->_throw($resolve->errorType, $resolve->errorMsg, Y_FUNCTIONID_INVALID);
            }
        }
        return substr($resolve->result, strpos($resolve->result, '.') + 1);
    }
    public function get_friendlyName()
    {
        $resolve = YAPI::getFriendlyNameFunction($this->_className, $this->_func);
        if ($resolve->errorType != YAPI_SUCCESS) {
            $this->isOnline();
            $resolve = YAPI::getFriendlyNameFunction($this->_className, $this->_func);
            if ($resolve->errorType != YAPI_SUCCESS) {
                return $this->_throw($resolve->errorType, $resolve->errorMsg, Y_FRIENDLYNAME_INVALID);
            }
        }
        return $resolve->result;
    }
    // Store and parse a an API request for current function
    //
    protected function _parse($yreq, $msValidity)
    {
        // save the whole structure for backward-compatibility
        $yreq->result["_expiration"] = YAPI::GetTickCount() + $msValidity;
        $this->_serial = $yreq->deviceid;
        $this->_funId = $yreq->functionid;
        $this->_hwId = $yreq->hwid;
        $this->_cache = $yreq->result;
        // process each attribute in turn for class-oriented processing
        foreach ($yreq->result as $key => $val) {
            $this->_parseAttr($key, $val);
        }
        $this->_parserHelper();
    }
    // Return the value of an attribute from function cache, after reloading it from device if needed
    // Note: the function cache is a typed (parsed) cache, contrarily to the agnostic device cache
    protected function _getAttr($str_attr)
    {
        if ($this->_cache['_expiration'] <= YAPI::GetTickCount()) {
            // no valid cached value, reload from device
            if ($this->load(YAPI::$defaultCacheValidity) != YAPI_SUCCESS) return null;
        }
        if (!isset($this->_cache[$str_attr])) {
            $this->_throw(YAPI_VERSION_MISMATCH, 'No such attribute $str_attr in function', null);
        }
        return $this->_cache[$str_attr];
    }
    // Return the value of an attribute from function cache, after loading it from device if never done
    protected function _getFixedAttr($str_attr)
    {
        if ($this->_cache['_expiration'] == 0) {
            // no cached value, load from device
            if ($this->load(YAPI::$defaultCacheValidity) != YAPI_SUCCESS) return null;
        }
        if (!isset($this->_cache[$str_attr])) {
            $this->_throw(YAPI_VERSION_MISMATCH, "No such attribute $str_attr in function", null);
        }
        return $this->_cache[$str_attr];
    }
    protected function _escapeAttr($str_newval)
    {
        // urlencode according to RFC 3986 instead of php default RFC 1738
        $safecodes = array('%21', '%23', '%24', '%27', '%28', '%29', '%2A', '%2C', '%2F', '%3A', '%3B', '%40', '%3F', '%5B', '%5D');
        $safechars = array('!', "#", "$", "'", "(", ")", '*', ",", "/", ":", ";", "@", "?", "[", "]");
        return str_replace($safecodes, $safechars, urlencode($str_newval));
    }
    // Change the value of an attribute on a device, and update cache on the fly
    // Note: the function cache is a typed (parsed) cache, contrarily to the agnostic device cache
    protected function _setAttr($str_attr, $str_newval)
    {
        if (!isset($str_newval)) {
            $this->_throw(YAPI_INVALID_ARGUMENT, "Undefined value to set for attribute $str_attr", null);
        }
        // urlencode according to RFC 3986 instead of php default RFC 1738
        $safecodes = array('%21', '%23', '%24', '%27', '%28', '%29', '%2A', '%2C', '%2F', '%3A', '%3B', '%40', '%3F', '%5B', '%5D');
        $safechars = array('!', "#", "$", "'", "(", ")", '*', ",", "/", ":", ";", "@", "?", "[", "]");
        $attrname = str_replace($safecodes, $safechars, urlencode($str_attr));
        $extra = "/$attrname?$attrname=" . $this->_escapeAttr($str_newval) . "&.";
        $yreq = YAPI::funcRequest($this->_className, $this->_func, $extra);
        if ($this->_cache['_expiration'] != 0) {
            $this->_cache['_expiration'] = YAPI::GetTickCount();
        }
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $this->_throw($yreq->errorType, $yreq->errorMsg, $yreq->errorType);
        }
        return YAPI_SUCCESS;
    }
    // Execute an arbitrary HTTP GET request on the device and return the binary content
    //
    public function _download($str_path)
    {
        // get the device serial number
        $devid = $this->module()->get_serialNumber();
        if ($devid == Y_SERIALNUMBER_INVALID) {
            return '';
        }
        $yreq = YAPI::devRequest($devid, "GET /$str_path");
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $this->_throw($yreq->errorType, $yreq->errorMsg, '');
        }
        return $yreq->result;
    }
    // Upload a file to the filesystem, to the specified full path name.
    // If a file already exists with the same path name, its content is overwritten.
    //
    public function _upload($str_path, $bin_content)
    {
        // get the device serial number
        $devid = $this->module()->get_serialNumber();
        if ($devid == Y_SERIALNUMBER_INVALID) {
            return $this->get_errorType();
        }
        if (is_array($bin_content)) {
            $bin_content = call_user_func_array('pack', array_merge(array("C*"), $bin_content));
        }
        $httpreq = 'POST /upload.html';
        $body = "Content-Disposition: form-data; name=\"$str_path\"; filename=\"api\"\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "Content-Transfer-Encoding: binary\r\n\r\n" . $bin_content;
        $yreq = YAPI::devRequest($devid, $httpreq, true, $body);
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $yreq->errorType;
        }
        return YAPI_SUCCESS;
    }
    // Upload a file to the filesystem, to the specified full path name.
    // If a file already exists with the same path name, its content is overwritten.
    //
    public function _uploadEx($str_path, $bin_content)
    {
        // get the device serial number
        $devid = $this->module()->get_serialNumber();
        if ($devid == Y_SERIALNUMBER_INVALID) {
            return $this->get_errorType();
        }
        if (is_array($bin_content)) {
            $bin_content = call_user_func_array('pack', array_merge(array("C*"), $bin_content));
        }
        $httpreq = 'POST /upload.html';
        $body = "Content-Disposition: form-data; name=\"$str_path\"; filename=\"api\"\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "Content-Transfer-Encoding: binary\r\n\r\n" . $bin_content;
        $yreq = YAPI::devRequest($devid, $httpreq, false, $body);
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $this->_throw($yreq->errorType, $yreq->errorMsg, '');
        }
        return $yreq->result;
    }
    // Get a value from a JSON buffer
    //
    public function _json_get_key($bin_jsonbuff, $str_key)
    {
        $loadval = json_decode($bin_jsonbuff, true);
        if (isset($loadval[$str_key])) {
            return $loadval[$str_key];
        }
        return "";
    }
    // Get a string from a JSON buffer
    //
    public function _json_get_string($bin_jsonbuff)
    {
        return json_decode($bin_jsonbuff, true);
    }
    // Get an array of strings from a JSON buffer
    //
    public function _json_get_array($bin_jsonbuff)
    {
        $loadval = json_decode($bin_jsonbuff, true);
        $res = Array();
        foreach ($loadval as $record) {
            $res[] = json_encode($record);
        }
        return $res;
    }
    public function _get_json_path($str_json, $path)
    {
        $json = json_decode($str_json, true);
        $paths = explode('|', $path);
        foreach ($paths as $key) {
            if (array_key_exists($key, $json)) {
                $json = $json[$key];
            } else {
                return '';
            }
        }
        return json_encode($json);
    }
    public function _decode_json_string($json)
    {
        $decoded = json_decode($json);
        return $decoded;
    }
    public function _findDataStream($obj_dataset, $str_def)
    {
        $key = $obj_dataset->get_functionId() . ":" . $str_def;
        if (isset($this->_dataStreams[$key]))
            return $this->_dataStreams[$key];
        $words = YAPI::_decodeWords($str_def);
        if (sizeof($words) < 14) {
            $this->_throw(YAPI_VERSION_MISMATCH, "device firmware is too old");
            return null;
        }
        $newDataStream = new YDataStream($this, $obj_dataset, $words);
        $this->_dataStreams[$key] = $newDataStream;
        return $newDataStream;
    }
    // Method used to clear cache of DataStream object (undocumented)
    public function _clearDataStreamCache()
    {
        $this->_dataStreams = array();
    }
    public function _getValueCallback()
    {
        return $this->_valueCallbackFunction;
    }
    public function isOnline()
    {
        // A valid value in cache means that the device is online
        if ($this->_cache['_expiration'] > YAPI::GetTickCount()) return true;
        // Check that the function is available without throwing exceptions
        $yreq = YAPI::funcRequest($this->_className, $this->_func, '');
        if ($yreq->errorType != YAPI_SUCCESS) {
            return false;
        }
        // save result in cache anyway
        $this->_parse($yreq, YAPI::$defaultCacheValidity);
        return true;
    }
    public function get_errorType()
    {
        return $this->_lastErrorType;
    }
    public function errorType()
    {
        return $this->_lastErrorType;
    }
    public function errType()
    {
        return $this->_lastErrorType;
    }
    public function get_errorMessage()
    {
        return $this->_lastErrorMsg;
    }
    public function errorMessage()
    {
        return $this->_lastErrorMsg;
    }
    public function errMessage()
    {
        return $this->_lastErrorMsg;
    }
    public function load($msValidity)
    {
        $yreq = YAPI::funcRequest($this->_className, $this->_func, '');
        if ($yreq->errorType != YAPI_SUCCESS) {
            return $this->_throw($yreq->errorType, $yreq->errorMsg, $yreq->errorType);
        }
        $this->_parse($yreq, $msValidity);
        return YAPI_SUCCESS;
    }
    public function clearCache()
    {
        $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if ($resolve->errorType != YAPI_SUCCESS) {
            return;
        }
        $str_func = $resolve->result;
        $dotpos = strpos($str_func, '.');
        $devid = substr($str_func, 0, $dotpos);
        $funcid = substr($str_func, $dotpos + 1);
        $dev = YAPI::getDevice($devid);
        if (is_null($dev)) {
            return;
        }
        $dev->dropCache();
        if ($this->_cacheExpiration > 0) {
            $this->_cacheExpiration = YAPI::GetTickCount();
        }
    }
    public function get_module()
    {
        // try to resolve the function name to a device id without query
        if ($this->_serial != '') {
            return yFindModule($this->_serial . '.module');
        }
        $hwid = $this->_func;
        if (strpos($hwid, '.') === FALSE) {
            $resolve = YAPI::resolveFunction($this->_className, $this->_func);
            if ($resolve->errorType == YAPI_SUCCESS) $hwid = $resolve->result;
        }
        $dotidx = strpos($hwid, '.');
        if ($dotidx !== FALSE) {
            // resolution worked
            return yFindModule(substr($hwid, 0, $dotidx) . '.module');
        }
        // device not resolved for now, force a communication for a last chance resolution
        if ($this->load(YAPI::$defaultCacheValidity) == YAPI_SUCCESS) {
            $resolve = YAPI::resolveFunction($this->_className, $this->_func);
            if ($resolve->errorType == YAPI_SUCCESS) $hwid = $resolve->result;
        }
        $dotidx = strpos($hwid, '.');
        if ($dotidx !== FALSE) {
            // resolution worked
            return yFindModule(substr($hwid, 0, $dotidx) . '.module');
        }
        // return a true yFindModule object even if it is not a module valid for communicating
        return yFindModule('module_of_' . $this->_className . '_' . $this->_func);
    }
    public function module()
    {
        return $this->get_module();
    }
    public function get_functionDescriptor()
    {
        // try to resolve the function name to a device id without query
        $hwid = $this->_func;
        if (strpos($hwid, '.') === FALSE) {
            $resolve = YAPI::resolveFunction($this->_className, $this->_func);
            if ($resolve->errorType != YAPI_SUCCESS) $hwid = $resolve->result;
        }
        $dotidx = strpos($hwid, '.');
        if ($dotidx !== FALSE) {
            // resolution worked
            return $hwid;
        }
        return Y_FUNCTIONDESCRIPTOR_INVALID;
    }
    public function getFunctionDescriptor()
    {
        return $this->get_functionDescriptor();
    }
    public function get_userData()
    {
        return $this->_userData;
    }
    public function userData()
    {
        return $this->_userData;
    }
    public function set_userData($data)
    {
        $this->_userData = $data;
    }
    public function setUserData($data)
    {
        $this->_userData = $data;
    }
}
//--- (generated code: YSensor declaration)
class YSensor extends YFunction
{
    const UNIT_INVALID                   = YAPI_INVALID_STRING;
    const CURRENTVALUE_INVALID           = YAPI_INVALID_DOUBLE;
    const LOWESTVALUE_INVALID            = YAPI_INVALID_DOUBLE;
    const HIGHESTVALUE_INVALID           = YAPI_INVALID_DOUBLE;
    const CURRENTRAWVALUE_INVALID        = YAPI_INVALID_DOUBLE;
    const LOGFREQUENCY_INVALID           = YAPI_INVALID_STRING;
    const REPORTFREQUENCY_INVALID        = YAPI_INVALID_STRING;
    const ADVMODE_IMMEDIATE              = 0;
    const ADVMODE_PERIOD_AVG             = 1;
    const ADVMODE_PERIOD_MIN             = 2;
    const ADVMODE_PERIOD_MAX             = 3;
    const ADVMODE_INVALID                = -1;
    const CALIBRATIONPARAM_INVALID       = YAPI_INVALID_STRING;
    const RESOLUTION_INVALID             = YAPI_INVALID_DOUBLE;
    const SENSORSTATE_INVALID            = YAPI_INVALID_INT;
    //--- (end of generated code: YSensor declaration)
    const DATA_INVALID = YAPI_INVALID_DOUBLE;
    //--- (generated code: YSensor attributes)
    protected $_unit                     = Y_UNIT_INVALID;               // Text
    protected $_currentValue             = Y_CURRENTVALUE_INVALID;       // MeasureVal
    protected $_lowestValue              = Y_LOWESTVALUE_INVALID;        // MeasureVal
    protected $_highestValue             = Y_HIGHESTVALUE_INVALID;       // MeasureVal
    protected $_currentRawValue          = Y_CURRENTRAWVALUE_INVALID;    // MeasureVal
    protected $_logFrequency             = Y_LOGFREQUENCY_INVALID;       // YFrequency
    protected $_reportFrequency          = Y_REPORTFREQUENCY_INVALID;    // YFrequency
    protected $_advMode                  = Y_ADVMODE_INVALID;            // AdvertisingMode
    protected $_calibrationParam         = Y_CALIBRATIONPARAM_INVALID;   // CalibParams
    protected $_resolution               = Y_RESOLUTION_INVALID;         // MeasureVal
    protected $_sensorState              = Y_SENSORSTATE_INVALID;        // Int
    protected $_timedReportCallbackSensor = null;                         // YSensorTimedReportCallback
    protected $_prevTimedReport          = 0;                            // float
    protected $_iresol                   = 0;                            // float
    protected $_offset                   = 0;                            // float
    protected $_scale                    = 0;                            // float
    protected $_decexp                   = 0;                            // float
    protected $_caltyp                   = 0;                            // int
    protected $_calpar                   = Array();                      // intArr
    protected $_calraw                   = Array();                      // floatArr
    protected $_calref                   = Array();                      // floatArr
    protected $_calhdl                   = null;                         // yCalibrationHandler
    //--- (end of generated code: YSensor attributes)
    function __construct($str_func)
    {
        //--- (generated code: YSensor constructor)
        parent::__construct($str_func);
        $this->_className = 'Sensor';
        //--- (end of generated code: YSensor constructor)
    }
    public function _getTimedReportCallback()
    {
        return $this->_timedReportCallbackSensor;
    }
    //--- (generated code: YSensor implementation)
    function _parseAttr($name, $val)
    {
        switch($name) {
        case 'unit':
            $this->_unit = $val;
            return 1;
        case 'currentValue':
            $this->_currentValue = round($val / 65.536) / 1000.0;
            return 1;
        case 'lowestValue':
            $this->_lowestValue = round($val / 65.536) / 1000.0;
            return 1;
        case 'highestValue':
            $this->_highestValue = round($val / 65.536) / 1000.0;
            return 1;
        case 'currentRawValue':
            $this->_currentRawValue = round($val / 65.536) / 1000.0;
            return 1;
        case 'logFrequency':
            $this->_logFrequency = $val;
            return 1;
        case 'reportFrequency':
            $this->_reportFrequency = $val;
            return 1;
        case 'advMode':
            $this->_advMode = intval($val);
            return 1;
        case 'calibrationParam':
            $this->_calibrationParam = $val;
            return 1;
        case 'resolution':
            $this->_resolution = round($val / 65.536) / 1000.0;
            return 1;
        case 'sensorState':
            $this->_sensorState = intval($val);
            return 1;
        }
        return parent::_parseAttr($name, $val);
    }
    public function get_unit()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_UNIT_INVALID;
            }
        }
        $res = $this->_unit;
        return $res;
    }
    public function get_currentValue()
    {
        // $res                    is a float;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CURRENTVALUE_INVALID;
            }
        }
        $res = $this->_applyCalibration($this->_currentRawValue);
        if ($res == Y_CURRENTVALUE_INVALID) {
            $res = $this->_currentValue;
        }
        $res = $res * $this->_iresol;
        $res = round($res) / $this->_iresol;
        return $res;
    }
    public function set_lowestValue($newval)
    {
        $rest_val = strval(round($newval * 65536.0));
        return $this->_setAttr("lowestValue",$rest_val);
    }
    public function get_lowestValue()
    {
        // $res                    is a float;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_LOWESTVALUE_INVALID;
            }
        }
        $res = $this->_lowestValue * $this->_iresol;
        $res = round($res) / $this->_iresol;
        return $res;
    }
    public function set_highestValue($newval)
    {
        $rest_val = strval(round($newval * 65536.0));
        return $this->_setAttr("highestValue",$rest_val);
    }
    public function get_highestValue()
    {
        // $res                    is a float;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_HIGHESTVALUE_INVALID;
            }
        }
        $res = $this->_highestValue * $this->_iresol;
        $res = round($res) / $this->_iresol;
        return $res;
    }
    public function get_currentRawValue()
    {
        // $res                    is a double;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CURRENTRAWVALUE_INVALID;
            }
        }
        $res = $this->_currentRawValue;
        return $res;
    }
    public function get_logFrequency()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_LOGFREQUENCY_INVALID;
            }
        }
        $res = $this->_logFrequency;
        return $res;
    }
    public function set_logFrequency($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("logFrequency",$rest_val);
    }
    public function get_reportFrequency()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_REPORTFREQUENCY_INVALID;
            }
        }
        $res = $this->_reportFrequency;
        return $res;
    }
    public function set_reportFrequency($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("reportFrequency",$rest_val);
    }
    public function get_advMode()
    {
        // $res                    is a enumADVERTISINGMODE;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_ADVMODE_INVALID;
            }
        }
        $res = $this->_advMode;
        return $res;
    }
    public function set_advMode($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("advMode",$rest_val);
    }
    public function get_calibrationParam()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALIBRATIONPARAM_INVALID;
            }
        }
        $res = $this->_calibrationParam;
        return $res;
    }
    public function set_calibrationParam($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("calibrationParam",$rest_val);
    }
    public function set_resolution($newval)
    {
        $rest_val = strval(round($newval * 65536.0));
        return $this->_setAttr("resolution",$rest_val);
    }
    public function get_resolution()
    {
        // $res                    is a double;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_RESOLUTION_INVALID;
            }
        }
        $res = $this->_resolution;
        return $res;
    }
    public function get_sensorState()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_SENSORSTATE_INVALID;
            }
        }
        $res = $this->_sensorState;
        return $res;
    }
    public static function FindSensor($func)
    {
        // $obj                    is a YSensor;
        $obj = YFunction::_FindFromCache('Sensor', $func);
        if ($obj == null) {
            $obj = new YSensor($func);
            YFunction::_AddToCache('Sensor', $func, $obj);
        }
        return $obj;
    }
    public function _parserHelper()
    {
        // $position               is a int;
        // $maxpos                 is a int;
        $iCalib = Array();      // intArr;
        // $iRaw                   is a int;
        // $iRef                   is a int;
        // $fRaw                   is a float;
        // $fRef                   is a float;
        $this->_caltyp = -1;
        $this->_scale = -1;
        while(sizeof($this->_calpar) > 0) { array_pop($this->_calpar); };
        while(sizeof($this->_calraw) > 0) { array_pop($this->_calraw); };
        while(sizeof($this->_calref) > 0) { array_pop($this->_calref); };
        // Store inverted resolution, to provide better rounding
        if ($this->_resolution > 0) {
            $this->_iresol = round(1.0 / $this->_resolution);
        } else {
            $this->_iresol = 10000;
            $this->_resolution = 0.0001;
        }
        // Old format: supported when there is no calibration
        if ($this->_calibrationParam == '' || $this->_calibrationParam == '0') {
            $this->_caltyp = 0;
            return 0;
        }
        if (Ystrpos($this->_calibrationParam,',') >= 0) {
            // Plain text format
            $iCalib = YAPI::_decodeFloats($this->_calibrationParam);
            $this->_caltyp = intVal(($iCalib[0]) / (1000));
            if ($this->_caltyp > 0) {
                if ($this->_caltyp < YOCTO_CALIB_TYPE_OFS) {
                    // Unknown calibration type: calibrated value will be provided by the device
                    $this->_caltyp = -1;
                    return 0;
                }
                $this->_calhdl = YAPI::_getCalibrationHandler($this->_caltyp);
                if (!(!is_null($this->_calhdl))) {
                    // Unknown calibration type: calibrated value will be provided by the device
                    $this->_caltyp = -1;
                    return 0;
                }
            }
            // New 32 bits text format
            $this->_offset = 0;
            $this->_scale = 1000;
            $maxpos = sizeof($iCalib);
            while(sizeof($this->_calpar) > 0) { array_pop($this->_calpar); };
            $position = 1;
            while ($position < $maxpos) {
                $this->_calpar[] = $iCalib[$position];
                $position = $position + 1;
            }
            while(sizeof($this->_calraw) > 0) { array_pop($this->_calraw); };
            while(sizeof($this->_calref) > 0) { array_pop($this->_calref); };
            $position = 1;
            while ($position + 1 < $maxpos) {
                $fRaw = $iCalib[$position];
                $fRaw = $fRaw / 1000.0;
                $fRef = $iCalib[$position + 1];
                $fRef = $fRef / 1000.0;
                $this->_calraw[] = $fRaw;
                $this->_calref[] = $fRef;
                $position = $position + 2;
            }
        } else {
            // Recorder-encoded format, including encoding
            $iCalib = YAPI::_decodeWords($this->_calibrationParam);
            // In case of unknown format, calibrated value will be provided by the device
            if (sizeof($iCalib) < 2) {
                $this->_caltyp = -1;
                return 0;
            }
            // Save variable format (scale for scalar, or decimal exponent)
            $this->_offset = 0;
            $this->_scale = 1;
            $this->_decexp = 1.0;
            $position = $iCalib[0];
            while ($position > 0) {
                $this->_decexp = $this->_decexp * 10;
                $position = $position - 1;
            }
            // Shortcut when there is no calibration parameter
            if (sizeof($iCalib) == 2) {
                $this->_caltyp = 0;
                return 0;
            }
            $this->_caltyp = $iCalib[2];
            $this->_calhdl = YAPI::_getCalibrationHandler($this->_caltyp);
            // parse calibration points
            if ($this->_caltyp <= 10) {
                $maxpos = $this->_caltyp;
            } else {
                if ($this->_caltyp <= 20) {
                    $maxpos = $this->_caltyp - 10;
                } else {
                    $maxpos = 5;
                }
            }
            $maxpos = 3 + 2 * $maxpos;
            if ($maxpos > sizeof($iCalib)) {
                $maxpos = sizeof($iCalib);
            }
            while(sizeof($this->_calpar) > 0) { array_pop($this->_calpar); };
            while(sizeof($this->_calraw) > 0) { array_pop($this->_calraw); };
            while(sizeof($this->_calref) > 0) { array_pop($this->_calref); };
            $position = 3;
            while ($position + 1 < $maxpos) {
                $iRaw = $iCalib[$position];
                $iRef = $iCalib[$position + 1];
                $this->_calpar[] = $iRaw;
                $this->_calpar[] = $iRef;
                $this->_calraw[] = YAPI::_decimalToDouble($iRaw);
                $this->_calref[] = YAPI::_decimalToDouble($iRef);
                $position = $position + 2;
            }
        }
        return 0;
    }
    public function isSensorReady()
    {
        if (!($this->isOnline())) {
            return false;
        }
        if (!($this->_sensorState == 0)) {
            return false;
        }
        return true;
    }
    public function get_dataLogger()
    {
        // $logger                 is a YDataLogger;
        // $modu                   is a YModule;
        // $serial                 is a str;
        // $hwid                   is a str;
        $modu = $this->get_module();
        $serial = $modu->get_serialNumber();
        if ($serial == YAPI_INVALID_STRING) {
            return null;
        }
        $hwid = $serial . '.dataLogger';
        $logger = YDataLogger::FindDataLogger($hwid);
        return $logger;
    }
    public function startDataLogger()
    {
        // $res                    is a bin;
        $res = $this->_download('api/dataLogger/recording?recording=1');
        if (!(strlen($res)>0)) return $this->_throw( YAPI_IO_ERROR, 'unable to start datalogger',YAPI_IO_ERROR);
        return YAPI_SUCCESS;
    }
    public function stopDataLogger()
    {
        // $res                    is a bin;
        $res = $this->_download('api/dataLogger/recording?recording=0');
        if (!(strlen($res)>0)) return $this->_throw( YAPI_IO_ERROR, 'unable to stop datalogger',YAPI_IO_ERROR);
        return YAPI_SUCCESS;
    }
    public function get_recordedData($startTime,$endTime)
    {
        // $funcid                 is a str;
        // $funit                  is a str;
        $funcid = $this->get_functionId();
        $funit = $this->get_unit();
        return new YDataSet($this, $funcid, $funit, $startTime, $endTime);
    }
    public function registerTimedReportCallback($callback)
    {
        // $sensor                 is a YSensor;
        $sensor = $this;
        if (!is_null($callback)) {
            YFunction::_UpdateTimedReportCallbackList($sensor, true);
        } else {
            YFunction::_UpdateTimedReportCallbackList($sensor, false);
        }
        $this->_timedReportCallbackSensor = $callback;
        return 0;
    }
    public function _invokeTimedReportCallback($value)
    {
        if (!is_null($this->_timedReportCallbackSensor)) {
            call_user_func($this->_timedReportCallbackSensor, $this, $value);
        } else {
        }
        return 0;
    }
    public function calibrateFromPoints($rawValues,$refValues)
    {
        // $rest_val               is a str;
        // $res                    is a int;
        $rest_val = $this->_encodeCalibrationPoints($rawValues, $refValues);
        $res = $this->_setAttr('calibrationParam', $rest_val);
        return $res;
    }
    public function loadCalibrationPoints(&$rawValues,&$refValues)
    {
        while(sizeof($rawValues) > 0) { array_pop($rawValues); };
        while(sizeof($refValues) > 0) { array_pop($refValues); };
        // Load function parameters if not yet loaded
        if ($this->_scale == 0) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return YAPI_DEVICE_NOT_FOUND;
            }
        }
        if ($this->_caltyp < 0) {
            $this->_throw(YAPI_NOT_SUPPORTED, 'Calibration parameters format mismatch. Please upgrade your library or firmware.');
            return YAPI_NOT_SUPPORTED;
        }
        while(sizeof($rawValues) > 0) { array_pop($rawValues); };
        while(sizeof($refValues) > 0) { array_pop($refValues); };
        foreach($this->_calraw as $each) {
            $rawValues[] = $each;
        }
        foreach($this->_calref as $each) {
            $refValues[] = $each;
        }
        return YAPI_SUCCESS;
    }
    public function _encodeCalibrationPoints($rawValues,$refValues)
    {
        // $res                    is a str;
        // $npt                    is a int;
        // $idx                    is a int;
        $npt = sizeof($rawValues);
        if ($npt != sizeof($refValues)) {
            $this->_throw(YAPI_INVALID_ARGUMENT, 'Invalid calibration parameters (size mismatch)');
            return YAPI_INVALID_STRING;
        }
        // Shortcut when building empty calibration parameters
        if ($npt == 0) {
            return '0';
        }
        // Load function parameters if not yet loaded
        if ($this->_scale == 0) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return YAPI_INVALID_STRING;
            }
        }
        // Detect old firmware
        if (($this->_caltyp < 0) || ($this->_scale < 0)) {
            $this->_throw(YAPI_NOT_SUPPORTED, 'Calibration parameters format mismatch. Please upgrade your library or firmware.');
            return '0';
        }
        // 32-bit fixed-point encoding
        $res = sprintf('%d', YOCTO_CALIB_TYPE_OFS);
        $idx = 0;
        while ($idx < $npt) {
            $res = sprintf('%s,%F,%F', $res, $rawValues[$idx], $refValues[$idx]);
            $idx = $idx + 1;
        }
        return $res;
    }
    public function _applyCalibration($rawValue)
    {
        if ($rawValue == Y_CURRENTVALUE_INVALID) {
            return Y_CURRENTVALUE_INVALID;
        }
        if ($this->_caltyp == 0) {
            return $rawValue;
        }
        if ($this->_caltyp < 0) {
            return Y_CURRENTVALUE_INVALID;
        }
        if (!(!is_null($this->_calhdl))) {
            return Y_CURRENTVALUE_INVALID;
        }
        return call_user_func($this->_calhdl, $rawValue, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
    }
    public function _decodeTimedReport($timestamp,$duration,$report)
    {
        // $i                      is a int;
        // $byteVal                is a int;
        // $poww                   is a float;
        // $minRaw                 is a float;
        // $avgRaw                 is a float;
        // $maxRaw                 is a float;
        // $sublen                 is a int;
        // $difRaw                 is a float;
        // $startTime              is a float;
        // $endTime                is a float;
        // $minVal                 is a float;
        // $avgVal                 is a float;
        // $maxVal                 is a float;
        if ($duration > 0) {
            $startTime = $timestamp - $duration;
        } else {
            $startTime = $this->_prevTimedReport;
        }
        $endTime = $timestamp;
        $this->_prevTimedReport = $endTime;
        if ($startTime == 0) {
            $startTime = $endTime;
        }
        // 32 bits timed report format
        if (sizeof($report) <= 5) {
            // sub-second report, 1-4 bytes
            $poww = 1;
            $avgRaw = 0;
            $byteVal = 0;
            $i = 1;
            while ($i < sizeof($report)) {
                $byteVal = $report[$i];
                $avgRaw = $avgRaw + $poww * $byteVal;
                $poww = $poww * 0x100;
                $i = $i + 1;
            }
            if ((($byteVal) & (0x80)) != 0) {
                $avgRaw = $avgRaw - $poww;
            }
            $avgVal = $avgRaw / 1000.0;
            if ($this->_caltyp != 0) {
                if (!is_null($this->_calhdl)) {
                    $avgVal = call_user_func($this->_calhdl, $avgVal, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
                }
            }
            $minVal = $avgVal;
            $maxVal = $avgVal;
        } else {
            // averaged report: avg,avg-min,max-avg
            $sublen = 1 + (($report[1]) & (3));
            $poww = 1;
            $avgRaw = 0;
            $byteVal = 0;
            $i = 2;
            while (($sublen > 0) && ($i < sizeof($report))) {
                $byteVal = $report[$i];
                $avgRaw = $avgRaw + $poww * $byteVal;
                $poww = $poww * 0x100;
                $i = $i + 1;
                $sublen = $sublen - 1;
            }
            if ((($byteVal) & (0x80)) != 0) {
                $avgRaw = $avgRaw - $poww;
            }
            $sublen = 1 + (((($report[1]) >> (2))) & (3));
            $poww = 1;
            $difRaw = 0;
            while (($sublen > 0) && ($i < sizeof($report))) {
                $byteVal = $report[$i];
                $difRaw = $difRaw + $poww * $byteVal;
                $poww = $poww * 0x100;
                $i = $i + 1;
                $sublen = $sublen - 1;
            }
            $minRaw = $avgRaw - $difRaw;
            $sublen = 1 + (((($report[1]) >> (4))) & (3));
            $poww = 1;
            $difRaw = 0;
            while (($sublen > 0) && ($i < sizeof($report))) {
                $byteVal = $report[$i];
                $difRaw = $difRaw + $poww * $byteVal;
                $poww = $poww * 0x100;
                $i = $i + 1;
                $sublen = $sublen - 1;
            }
            $maxRaw = $avgRaw + $difRaw;
            $avgVal = $avgRaw / 1000.0;
            $minVal = $minRaw / 1000.0;
            $maxVal = $maxRaw / 1000.0;
            if ($this->_caltyp != 0) {
                if (!is_null($this->_calhdl)) {
                    $avgVal = call_user_func($this->_calhdl, $avgVal, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
                    $minVal = call_user_func($this->_calhdl, $minVal, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
                    $maxVal = call_user_func($this->_calhdl, $maxVal, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
                }
            }
        }
        return new YMeasure($startTime, $endTime, $minVal, $avgVal, $maxVal);
    }
    public function _decodeVal($w)
    {
        // $val                    is a float;
        $val = $w;
        if ($this->_caltyp != 0) {
            if (!is_null($this->_calhdl)) {
                $val = call_user_func($this->_calhdl, $val, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
            }
        }
        return $val;
    }
    public function _decodeAvg($dw)
    {
        // $val                    is a float;
        $val = $dw;
        if ($this->_caltyp != 0) {
            if (!is_null($this->_calhdl)) {
                $val = call_user_func($this->_calhdl, $val, $this->_caltyp, $this->_calpar, $this->_calraw, $this->_calref);
            }
        }
        return $val;
    }
    public function unit()
    { return $this->get_unit(); }
    public function currentValue()
    { return $this->get_currentValue(); }
    public function setLowestValue($newval)
    { return $this->set_lowestValue($newval); }
    public function lowestValue()
    { return $this->get_lowestValue(); }
    public function setHighestValue($newval)
    { return $this->set_highestValue($newval); }
    public function highestValue()
    { return $this->get_highestValue(); }
    public function currentRawValue()
    { return $this->get_currentRawValue(); }
    public function logFrequency()
    { return $this->get_logFrequency(); }
    public function setLogFrequency($newval)
    { return $this->set_logFrequency($newval); }
    public function reportFrequency()
    { return $this->get_reportFrequency(); }
    public function setReportFrequency($newval)
    { return $this->set_reportFrequency($newval); }
    public function advMode()
    { return $this->get_advMode(); }
    public function setAdvMode($newval)
    { return $this->set_advMode($newval); }
    public function calibrationParam()
    { return $this->get_calibrationParam(); }
    public function setCalibrationParam($newval)
    { return $this->set_calibrationParam($newval); }
    public function setResolution($newval)
    { return $this->set_resolution($newval); }
    public function resolution()
    { return $this->get_resolution(); }
    public function sensorState()
    { return $this->get_sensorState(); }
    public function nextSensor()
    {   $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if($resolve->errorType != YAPI_SUCCESS) return null;
        $next_hwid = YAPI::getNextHardwareId($this->_className, $resolve->result);
        if($next_hwid == null) return null;
        return self::FindSensor($next_hwid);
    }
    public static function FirstSensor()
    {   $next_hwid = YAPI::getFirstHardwareId('Sensor');
        if($next_hwid == null) return null;
        return self::FindSensor($next_hwid);
    }
    //--- (end of generated code: YSensor implementation)
}
//--- (generated code: YModule declaration)
class YModule extends YFunction
{
    const PRODUCTNAME_INVALID            = YAPI_INVALID_STRING;
    const SERIALNUMBER_INVALID           = YAPI_INVALID_STRING;
    const PRODUCTID_INVALID              = YAPI_INVALID_UINT;
    const PRODUCTRELEASE_INVALID         = YAPI_INVALID_UINT;
    const FIRMWARERELEASE_INVALID        = YAPI_INVALID_STRING;
    const PERSISTENTSETTINGS_LOADED      = 0;
    const PERSISTENTSETTINGS_SAVED       = 1;
    const PERSISTENTSETTINGS_MODIFIED    = 2;
    const PERSISTENTSETTINGS_INVALID     = -1;
    const LUMINOSITY_INVALID             = YAPI_INVALID_UINT;
    const BEACON_OFF                     = 0;
    const BEACON_ON                      = 1;
    const BEACON_INVALID                 = -1;
    const UPTIME_INVALID                 = YAPI_INVALID_LONG;
    const USBCURRENT_INVALID             = YAPI_INVALID_UINT;
    const REBOOTCOUNTDOWN_INVALID        = YAPI_INVALID_INT;
    const USERVAR_INVALID                = YAPI_INVALID_INT;
    //--- (end of generated code: YModule declaration)
    //--- (generated code: YModule attributes)
    protected $_productName              = Y_PRODUCTNAME_INVALID;        // Text
    protected $_serialNumber             = Y_SERIALNUMBER_INVALID;       // Text
    protected $_productId                = Y_PRODUCTID_INVALID;          // XWord
    protected $_productRelease           = Y_PRODUCTRELEASE_INVALID;     // XWord
    protected $_firmwareRelease          = Y_FIRMWARERELEASE_INVALID;    // Text
    protected $_persistentSettings       = Y_PERSISTENTSETTINGS_INVALID; // FlashSettings
    protected $_luminosity               = Y_LUMINOSITY_INVALID;         // Percent
    protected $_beacon                   = Y_BEACON_INVALID;             // OnOff
    protected $_upTime                   = Y_UPTIME_INVALID;             // Time
    protected $_usbCurrent               = Y_USBCURRENT_INVALID;         // UsedCurrent
    protected $_rebootCountdown          = Y_REBOOTCOUNTDOWN_INVALID;    // Int
    protected $_userVar                  = Y_USERVAR_INVALID;            // Int
    protected $_logCallback              = null;                         // YModuleLogCallback
    protected $_confChangeCallback       = null;                         // YModuleConfigChangeCallback
    protected $_beaconCallback           = null;                         // YModuleBeaconCallback
    //--- (end of generated code: YModule attributes)
    protected static $_moduleCallbackList = array();
    function __construct($str_func)
    {
        //--- (generated code: YModule constructor)
        parent::__construct($str_func);
        $this->_className = 'Module';
        //--- (end of generated code: YModule constructor)
    }
    private static function _updateModuleCallbackList($module, $add)
    {
    }
    // Return the internal device object hosting the function
    protected function _getDev()
    {
        $devid = $this->_func;
        $dotidx = strpos($devid, '.');
        if ($dotidx !== false) $devid = substr($devid, 0, $dotidx);
        $dev = YAPI::getDevice($devid);
        if (is_null($dev)) {
            $this->_throw(YAPI_DEVICE_NOT_FOUND, "Device [$devid] is not online", null);
        }
        return $dev;
    }
    public function functionCount()
    {
        $dev = $this->_getDev();
        return $dev->functionCount();
    }
    public function functionId($functionIndex)
    {
        $dev = $this->_getDev();
        return $dev->functionId($functionIndex);
    }
    public function functionType($functionIndex)
    {
        $dev = $this->_getDev();
        return $dev->functionType($functionIndex);
    }
    public function functionBaseType($functionIndex)
    {
        $dev = $this->_getDev();
        return $dev->functionBaseType($functionIndex);
    }
    public function functionName($functionIndex)
    {
        $devid = $this->_func;
        $dotidx = strpos($devid, '.');
        if ($dotidx !== FALSE) $devid = substr($devid, 0, $dotidx);
        $dev = YAPI::getDevice($devid);
        return $dev->functionName($functionIndex);
    }
    public function functionValue($functionIndex)
    {
        $dev = $this->_getDev();
        return $dev->functionValue($functionIndex);
    }
    protected function _flattenJsonStruct_internal($jsoncomplex)
    {
        $decoded = json_decode($jsoncomplex);
        if ($decoded == null) {
            $this->_throw(YAPI_INVALID_ARGUMENT, 'Invalid json structure');
            return "";
        }
        $attrs = array();
        foreach ($decoded as $function_name => $fuction_attrs) {
            if ($function_name == "services")
                continue;
            foreach ($fuction_attrs as $attr_name => $attr_value) {
                if (is_object($attr_value)) {
                    // skip complext attributes (move and pulse)
                    continue;
                }
                $flat = $function_name . '/' . $attr_name . '=' . $attr_value;
                $attrs[] = $flat;
            }
        }
        return json_encode($attrs);
    }
    private function get_subDevices_internal()
    {
        $serial = $this->get_serialNumber();
        return YAPI::getSubDevicesFrom($serial);
    }
    private function get_parentHub_internal()
    {
        $serial = $this->get_serialNumber();
        $hubserial = YAPI::getHubSerialFrom($serial);
        if ($hubserial == $serial)
            return '';
        return $hubserial;
    }
    private function get_url_internal()
    {
        $dev = $this->_getDev();
        if (!($dev == null)) {
            return $dev->getRootUrl();
        }
        return "";
    }
    private function _startStopDevLog_internal($str_serial, $bool_start)
    {
        $dev = $this->_getDev();
        if (!($dev == null)) {
            $dev->registerLogCallback($this->_logCallback);
        }
    }
    //--- (generated code: YModule implementation)
    function _parseAttr($name, $val)
    {
        switch($name) {
        case 'productName':
            $this->_productName = $val;
            return 1;
        case 'serialNumber':
            $this->_serialNumber = $val;
            return 1;
        case 'productId':
            $this->_productId = intval($val);
            return 1;
        case 'productRelease':
            $this->_productRelease = intval($val);
            return 1;
        case 'firmwareRelease':
            $this->_firmwareRelease = $val;
            return 1;
        case 'persistentSettings':
            $this->_persistentSettings = intval($val);
            return 1;
        case 'luminosity':
            $this->_luminosity = intval($val);
            return 1;
        case 'beacon':
            $this->_beacon = intval($val);
            return 1;
        case 'upTime':
            $this->_upTime = intval($val);
            return 1;
        case 'usbCurrent':
            $this->_usbCurrent = intval($val);
            return 1;
        case 'rebootCountdown':
            $this->_rebootCountdown = intval($val);
            return 1;
        case 'userVar':
            $this->_userVar = intval($val);
            return 1;
        }
        return parent::_parseAttr($name, $val);
    }
    public function get_productName()
    {
        // $res                    is a string;
        // $dev                    is a YDevice;
        if ($this->_cacheExpiration == 0) {
            $dev = $this->_getDev();
            if (!($dev == null)) {
                return $dev->getProductName();
            }
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_PRODUCTNAME_INVALID;
            }
        }
        $res = $this->_productName;
        return $res;
    }
    public function get_serialNumber()
    {
        // $res                    is a string;
        // $dev                    is a YDevice;
        if ($this->_cacheExpiration == 0) {
            $dev = $this->_getDev();
            if (!($dev == null)) {
                return $dev->getSerialNumber();
            }
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_SERIALNUMBER_INVALID;
            }
        }
        $res = $this->_serialNumber;
        return $res;
    }
    public function get_productId()
    {
        // $res                    is a int;
        // $dev                    is a YDevice;
        if ($this->_cacheExpiration == 0) {
            $dev = $this->_getDev();
            if (!($dev == null)) {
                return $dev->getProductId();
            }
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_PRODUCTID_INVALID;
            }
        }
        $res = $this->_productId;
        return $res;
    }
    public function get_productRelease()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration == 0) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_PRODUCTRELEASE_INVALID;
            }
        }
        $res = $this->_productRelease;
        return $res;
    }
    public function get_firmwareRelease()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_FIRMWARERELEASE_INVALID;
            }
        }
        $res = $this->_firmwareRelease;
        return $res;
    }
    public function get_persistentSettings()
    {
        // $res                    is a enumFLASHSETTINGS;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_PERSISTENTSETTINGS_INVALID;
            }
        }
        $res = $this->_persistentSettings;
        return $res;
    }
    public function set_persistentSettings($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("persistentSettings",$rest_val);
    }
    public function get_luminosity()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_LUMINOSITY_INVALID;
            }
        }
        $res = $this->_luminosity;
        return $res;
    }
    public function set_luminosity($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("luminosity",$rest_val);
    }
    public function get_beacon()
    {
        // $res                    is a enumONOFF;
        // $dev                    is a YDevice;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            $dev = $this->_getDev();
            if (!($dev == null)) {
                return $dev->getBeacon();
            }
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_BEACON_INVALID;
            }
        }
        $res = $this->_beacon;
        return $res;
    }
    public function set_beacon($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("beacon",$rest_val);
    }
    public function get_upTime()
    {
        // $res                    is a long;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_UPTIME_INVALID;
            }
        }
        $res = $this->_upTime;
        return $res;
    }
    public function get_usbCurrent()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_USBCURRENT_INVALID;
            }
        }
        $res = $this->_usbCurrent;
        return $res;
    }
    public function get_rebootCountdown()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_REBOOTCOUNTDOWN_INVALID;
            }
        }
        $res = $this->_rebootCountdown;
        return $res;
    }
    public function set_rebootCountdown($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("rebootCountdown",$rest_val);
    }
    public function get_userVar()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_USERVAR_INVALID;
            }
        }
        $res = $this->_userVar;
        return $res;
    }
    public function set_userVar($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("userVar",$rest_val);
    }
    public static function FindModule($func)
    {
        // $obj                    is a YModule;
        // $cleanHwId              is a str;
        // $modpos                 is a int;
        $cleanHwId = $func;
        $modpos = Ystrpos($func,'.module');
        if ($modpos != (strlen($func) - 7)) {
            $cleanHwId = $func . '.module';
        }
        $obj = YFunction::_FindFromCache('Module', $cleanHwId);
        if ($obj == null) {
            $obj = new YModule($cleanHwId);
            YFunction::_AddToCache('Module', $cleanHwId, $obj);
        }
        return $obj;
    }
    public function get_productNameAndRevision()
    {
        // $prodname               is a str;
        // $prodrel                is a int;
        // $fullname               is a str;
        $prodname = $this->get_productName();
        $prodrel = $this->get_productRelease();
        if ($prodrel > 1) {
            $fullname = sprintf('%s rev. %c', $prodname, 64+$prodrel);
        } else {
            $fullname = $prodname;
        }
        return $fullname;
    }
    public function saveToFlash()
    {
        return $this->set_persistentSettings(Y_PERSISTENTSETTINGS_SAVED);
    }
    public function revertFromFlash()
    {
        return $this->set_persistentSettings(Y_PERSISTENTSETTINGS_LOADED);
    }
    public function reboot($secBeforeReboot)
    {
        return $this->set_rebootCountdown($secBeforeReboot);
    }
    public function triggerFirmwareUpdate($secBeforeReboot)
    {
        return $this->set_rebootCountdown(-$secBeforeReboot);
    }
    public function _startStopDevLog($serial,$start)
    {
        $this->_startStopDevLog_internal($serial,$start);
    }
    //cannot be generated for PHP:
    //private function _startStopDevLog_internal($serial,$start)
    public function registerLogCallback($callback)
    {
        // $serial                 is a str;
        $serial = $this->get_serialNumber();
        if ($serial == YAPI_INVALID_STRING) {
            return YAPI_DEVICE_NOT_FOUND;
        }
        $this->_logCallback = $callback;
        $this->_startStopDevLog($serial, !is_null($callback));
        return 0;
    }
    public function get_logCallback()
    {
        return $this->_logCallback;
    }
    public function registerConfigChangeCallback($callback)
    {
        if (!is_null($callback)) {
            YModule::_updateModuleCallbackList($this, true);
        } else {
            YModule::_updateModuleCallbackList($this, false);
        }
        $this->_confChangeCallback = $callback;
        return 0;
    }
    public function _invokeConfigChangeCallback()
    {
        if (!is_null($this->_confChangeCallback)) {
            call_user_func($this->_confChangeCallback, $this);
        }
        return 0;
    }
    public function registerBeaconCallback($callback)
    {
        if (!is_null($callback)) {
            YModule::_updateModuleCallbackList($this, true);
        } else {
            YModule::_updateModuleCallbackList($this, false);
        }
        $this->_beaconCallback = $callback;
        return 0;
    }
    public function _invokeBeaconCallback($beaconState)
    {
        if (!is_null($this->_beaconCallback)) {
            call_user_func($this->_beaconCallback, $this, $beaconState);
        }
        return 0;
    }
    public function triggerConfigChangeCallback()
    {
        $this->_setAttr('persistentSettings', '2');
        return 0;
    }
    public function checkFirmware($path,$onlynew)
    {
        // $serial                 is a str;
        // $release                is a int;
        // $tmp_res                is a str;
        if ($onlynew) {
            $release = intVal($this->get_firmwareRelease());
        } else {
            $release = 0;
        }
        //may throw an exception
        $serial = $this->get_serialNumber();
        $tmp_res = YFirmwareUpdate::CheckFirmware($serial, $path, $release);
        if (Ystrpos($tmp_res,'error:') == 0) {
            $this->_throw(YAPI_INVALID_ARGUMENT, $tmp_res);
        }
        return $tmp_res;
    }
    public function updateFirmwareEx($path,$force)
    {
        // $serial                 is a str;
        // $settings               is a bin;
        $serial = $this->get_serialNumber();
        $settings = $this->get_allSettings();
        if (strlen($settings) == 0) {
            $this->_throw(YAPI_IO_ERROR, 'Unable to get device settings');
            $settings = 'error:Unable to get device settings';
        }
        return new YFirmwareUpdate($serial, $path, $settings, $force);
    }
    public function updateFirmware($path)
    {
        return $this->updateFirmwareEx($path, false);
    }
    public function get_allSettings()
    {
        // $settings               is a bin;
        // $json                   is a bin;
        // $res                    is a bin;
        // $sep                    is a str;
        // $name                   is a str;
        // $item                   is a str;
        // $t_type                 is a str;
        // $id                     is a str;
        // $url                    is a str;
        // $file_data              is a str;
        // $file_data_bin          is a bin;
        // $temp_data_bin          is a bin;
        // $ext_settings           is a str;
        $filelist = Array();    // strArr;
        $templist = Array();    // strArr;
        $settings = $this->_download('api.json');
        if (strlen($settings) == 0) {
            return $settings;
        }
        $ext_settings = ', "extras":[';
        $templist = $this->get_functionIds('Temperature');
        $sep = '';
        foreach( $templist as $each) {
            if (intVal($this->get_firmwareRelease()) > 9000) {
                $url = sprintf('api/%s/sensorType',$each);
                $t_type = $this->_download($url);
                if ($t_type == 'RES_NTC' || $t_type == 'RES_LINEAR') {
                    $id = substr($each,  11, strlen($each) - 11);
                    if ($id == '') {
                        $id = '1';
                    }
                    $temp_data_bin = $this->_download(sprintf('extra.json?page=%s', $id));
                    if (strlen($temp_data_bin) > 0) {
                        $item = sprintf('%s{"fid":"%s", "json":%s}'."\n".'', $sep, $each, $temp_data_bin);
                        $ext_settings = $ext_settings . $item;
                        $sep = ',';
                    }
                }
            }
        }
        $ext_settings = $ext_settings . '],'."\n".'"files":[';
        if ($this->hasFunction('files')) {
            $json = $this->_download('files.json?a=dir&f=');
            if (strlen($json) == 0) {
                return $json;
            }
            $filelist = $this->_json_get_array($json);
            $sep = '';
            foreach( $filelist as $each) {
                $name = $this->_json_get_key($each, 'name');
                if ((strlen($name) > 0) && !($name == 'startupConf.json')) {
                    $file_data_bin = $this->_download($this->_escapeAttr($name));
                    $file_data = YAPI::_bytesToHexStr($file_data_bin);
                    $item = sprintf('%s{"name":"%s", "data":"%s"}'."\n".'', $sep, $name, $file_data);
                    $ext_settings = $ext_settings . $item;
                    $sep = ',';
                }
            }
        }
        $res = '{ "api":' . $settings . $ext_settings . ']}';
        return $res;
    }
    public function loadThermistorExtra($funcId,$jsonExtra)
    {
        $values = Array();      // strArr;
        // $url                    is a str;
        // $curr                   is a str;
        // $currTemp               is a str;
        // $ofs                    is a int;
        // $size                   is a int;
        $url = 'api/' . $funcId . '.json?command=Z';
        $this->_download($url);
        // add records in growing resistance value
        $values = $this->_json_get_array($jsonExtra);
        $ofs = 0;
        $size = sizeof($values);
        while ($ofs + 1 < $size) {
            $curr = $values[$ofs];
            $currTemp = $values[$ofs + 1];
            $url = sprintf('api/%s.json?command=m%s:%s', $funcId, $curr, $currTemp);
            $this->_download($url);
            $ofs = $ofs + 2;
        }
        return YAPI_SUCCESS;
    }
    public function set_extraSettings($jsonExtra)
    {
        $extras = Array();      // strArr;
        // $functionId             is a str;
        // $data                   is a str;
        $extras = $this->_json_get_array($jsonExtra);
        foreach( $extras as $each) {
            $functionId = $this->_get_json_path($each, 'fid');
            $functionId = $this->_decode_json_string($functionId);
            $data = $this->_get_json_path($each, 'json');
            if ($this->hasFunction($functionId)) {
                $this->loadThermistorExtra($functionId, $data);
            }
        }
        return YAPI_SUCCESS;
    }
    public function set_allSettingsAndFiles($settings)
    {
        // $down                   is a bin;
        // $json                   is a str;
        // $json_api               is a str;
        // $json_files             is a str;
        // $json_extra             is a str;
        // $fuperror               is a int;
        // $globalres              is a int;
        $fuperror = 0;
        $json = $settings;
        $json_api = $this->_get_json_path($json, 'api');
        if ($json_api == '') {
            return $this->set_allSettings($settings);
        }
        $json_extra = $this->_get_json_path($json, 'extras');
        if (!($json_extra == '')) {
            $this->set_extraSettings($json_extra);
        }
        $this->set_allSettings($json_api);
        if ($this->hasFunction('files')) {
            $files = Array();       // strArr;
            // $res                    is a str;
            // $name                   is a str;
            // $data                   is a str;
            $down = $this->_download('files.json?a=format');
            $res = $this->_get_json_path($down, 'res');
            $res = $this->_decode_json_string($res);
            if (!($res == 'ok')) return $this->_throw( YAPI_IO_ERROR, 'format failed',YAPI_IO_ERROR);
            $json_files = $this->_get_json_path($json, 'files');
            $files = $this->_json_get_array($json_files);
            foreach( $files as $each) {
                $name = $this->_get_json_path($each, 'name');
                $name = $this->_decode_json_string($name);
                $data = $this->_get_json_path($each, 'data');
                $data = $this->_decode_json_string($data);
                if ($name == '') {
                    $fuperror = $fuperror + 1;
                } else {
                    $this->_upload($name, YAPI::_hexStrToBin($data));
                }
            }
        }
        // Apply settings a second time for file-dependent settings and dynamic sensor nodes
        $globalres = $this->set_allSettings($json_api);
        if (!($fuperror == 0)) return $this->_throw( YAPI_IO_ERROR, 'Error during file upload',YAPI_IO_ERROR);
        return $globalres;
    }
    public function hasFunction($funcId)
    {
        // $count                  is a int;
        // $i                      is a int;
        // $fid                    is a str;
        $count = $this->functionCount();
        $i = 0;
        while ($i < $count) {
            $fid = $this->functionId($i);
            if ($fid == $funcId) {
                return true;
            }
            $i = $i + 1;
        }
        return false;
    }
    public function get_functionIds($funType)
    {
        // $count                  is a int;
        // $i                      is a int;
        // $ftype                  is a str;
        $res = Array();         // strArr;
        $count = $this->functionCount();
        $i = 0;
        while ($i < $count) {
            $ftype = $this->functionType($i);
            if ($ftype == $funType) {
                $res[] = $this->functionId($i);
            } else {
                $ftype = $this->functionBaseType($i);
                if ($ftype == $funType) {
                    $res[] = $this->functionId($i);
                }
            }
            $i = $i + 1;
        }
        return $res;
    }
    public function _flattenJsonStruct($jsoncomplex)
    {
        return $this->_flattenJsonStruct_internal($jsoncomplex);
    }
    //cannot be generated for PHP:
    //private function _flattenJsonStruct_internal($jsoncomplex)
    public function calibVersion($cparams)
    {
        if ($cparams == '0,') {
            return 3;
        }
        if (Ystrpos($cparams,',') >= 0) {
            if (Ystrpos($cparams,' ') > 0) {
                return 3;
            } else {
                return 1;
            }
        }
        if ($cparams == '' || $cparams == '0') {
            return 1;
        }
        if ((strlen($cparams) < 2) || (Ystrpos($cparams,'.') >= 0)) {
            return 0;
        } else {
            return 2;
        }
    }
    public function calibScale($unit_name,$sensorType)
    {
        if ($unit_name == 'g' || $unit_name == 'gauss' || $unit_name == 'W') {
            return 1000;
        }
        if ($unit_name == 'C') {
            if ($sensorType == '') {
                return 16;
            }
            if (intVal($sensorType) < 8) {
                return 16;
            } else {
                return 100;
            }
        }
        if ($unit_name == 'm' || $unit_name == 'deg') {
            return 10;
        }
        return 1;
    }
    public function calibOffset($unit_name)
    {
        if ($unit_name == '% RH' || $unit_name == 'mbar' || $unit_name == 'lx') {
            return 0;
        }
        return 32767;
    }
    public function calibConvert($param,$currentFuncValue,$unit_name,$sensorType)
    {
        // $paramVer               is a int;
        // $funVer                 is a int;
        // $funScale               is a int;
        // $funOffset              is a int;
        // $paramScale             is a int;
        // $paramOffset            is a int;
        $words = Array();       // intArr;
        $words_str = Array();   // strArr;
        $calibData = Array();   // floatArr;
        $iCalib = Array();      // intArr;
        // $calibType              is a int;
        // $i                      is a int;
        // $maxSize                is a int;
        // $ratio                  is a float;
        // $nPoints                is a int;
        // $wordVal                is a float;
        // Initial guess for parameter encoding
        $paramVer = $this->calibVersion($param);
        $funVer = $this->calibVersion($currentFuncValue);
        $funScale = $this->calibScale($unit_name, $sensorType);
        $funOffset = $this->calibOffset($unit_name);
        $paramScale = $funScale;
        $paramOffset = $funOffset;
        if ($funVer < 3) {
            // Read the effective device scale if available
            if ($funVer == 2) {
                $words = YAPI::_decodeWords($currentFuncValue);
                if (($words[0] == 1366) && ($words[1] == 12500)) {
                    // Yocto-3D RefFrame used a special encoding
                    $funScale = 1;
                    $funOffset = 0;
                } else {
                    $funScale = $words[1];
                    $funOffset = $words[0];
                }
            } else {
                if ($funVer == 1) {
                    if ($currentFuncValue == '' || (intVal($currentFuncValue) > 10)) {
                        $funScale = 0;
                    }
                }
            }
        }
        while(sizeof($calibData) > 0) { array_pop($calibData); };
        $calibType = 0;
        if ($paramVer < 3) {
            // Handle old 16 bit parameters formats
            if ($paramVer == 2) {
                $words = YAPI::_decodeWords($param);
                if (($words[0] == 1366) && ($words[1] == 12500)) {
                    // Yocto-3D RefFrame used a special encoding
                    $paramScale = 1;
                    $paramOffset = 0;
                } else {
                    $paramScale = $words[1];
                    $paramOffset = $words[0];
                }
                if ((sizeof($words) >= 3) && ($words[2] > 0)) {
                    $maxSize = 3 + 2 * (($words[2]) % (10));
                    if ($maxSize > sizeof($words)) {
                        $maxSize = sizeof($words);
                    }
                    $i = 3;
                    while ($i < $maxSize) {
                        $calibData[] = $words[$i];
                        $i = $i + 1;
                    }
                }
            } else {
                if ($paramVer == 1) {
                    $words_str = explode(',', $param);
                    foreach($words_str as $each) {
                        $words[] = intVal($each);
                    }
                    if ($param == '' || ($words[0] > 10)) {
                        $paramScale = 0;
                    }
                    if ((sizeof($words) > 0) && ($words[0] > 0)) {
                        $maxSize = 1 + 2 * (($words[0]) % (10));
                        if ($maxSize > sizeof($words)) {
                            $maxSize = sizeof($words);
                        }
                        $i = 1;
                        while ($i < $maxSize) {
                            $calibData[] = $words[$i];
                            $i = $i + 1;
                        }
                    }
                } else {
                    if ($paramVer == 0) {
                        $ratio = floatval($param);
                        if ($ratio > 0) {
                            $calibData[] = 0.0;
                            $calibData[] = 0.0;
                            $calibData[] = round(65535 / $ratio);
                            $calibData[] = 65535.0;
                        }
                    }
                }
            }
            $i = 0;
            while ($i < sizeof($calibData)) {
                if ($paramScale > 0) {
                    // scalar decoding
                    $calibData[$i] = ($calibData[$i] - $paramOffset) / $paramScale;
                } else {
                    // floating-point decoding
                    $calibData[$i] = YAPI::_decimalToDouble(round($calibData[$i]));
                }
                $i = $i + 1;
            }
        } else {
            // Handle latest 32bit parameter format
            $iCalib = YAPI::_decodeFloats($param);
            $calibType = round($iCalib[0] / 1000.0);
            if ($calibType >= 30) {
                $calibType = $calibType - 30;
            }
            $i = 1;
            while ($i < sizeof($iCalib)) {
                $calibData[] = $iCalib[$i] / 1000.0;
                $i = $i + 1;
            }
        }
        if ($funVer >= 3) {
            // Encode parameters in new format
            if (sizeof($calibData) == 0) {
                $param = '0,';
            } else {
                $param = 30 + $calibType;
                $i = 0;
                while ($i < sizeof($calibData)) {
                    if ((($i) & (1)) > 0) {
                        $param = $param . ':';
                    } else {
                        $param = $param . ' ';
                    }
                    $param = $param . round($calibData[$i] * 1000.0 / 1000.0);
                    $i = $i + 1;
                }
                $param = $param . ',';
            }
        } else {
            if ($funVer >= 1) {
                // Encode parameters for older devices
                $nPoints = intVal((sizeof($calibData)) / (2));
                $param = $nPoints;
                $i = 0;
                while ($i < 2 * $nPoints) {
                    if ($funScale == 0) {
                        $wordVal = YAPI::_doubleToDecimal(round($calibData[$i]));
                    } else {
                        $wordVal = $calibData[$i] * $funScale + $funOffset;
                    }
                    $param = $param . ',' . round($wordVal);
                    $i = $i + 1;
                }
            } else {
                // Initial V0 encoding used for old Yocto-Light
                if (sizeof($calibData) == 4) {
                    $param = round(1000 * ($calibData[3] - $calibData[1]) / $calibData[2] - $calibData[0]);
                }
            }
        }
        return $param;
    }
    public function _tryExec($url)
    {
        // $res                    is a int;
        // $done                   is a int;
        $res = YAPI_SUCCESS;
        $done = 1;
        try {
            $this->_download($url);
        } catch (Exception $ex) {
            $done = 0;
        }
        if ($done == 0) {
            // retry silently after a short wait
            try {
                YAPI.Sleep(500);
                $this->_download($url);
            } catch (Exception $ex) {
                // second failure, return error code
                $res = $this->get_errorType();
            }
        }
        return $res;
    }
    public function set_allSettings($settings)
    {
        $restoreLast = Array(); // strArr;
        // $old_json_flat          is a bin;
        $old_dslist = Array();  // strArr;
        $old_jpath = Array();   // strArr;
        $old_jpath_len = Array(); // intArr;
        $old_val_arr = Array(); // strArr;
        // $actualSettings         is a bin;
        $new_dslist = Array();  // strArr;
        $new_jpath = Array();   // strArr;
        $new_jpath_len = Array(); // intArr;
        $new_val_arr = Array(); // strArr;
        // $cpos                   is a int;
        // $eqpos                  is a int;
        // $leng                   is a int;
        // $i                      is a int;
        // $j                      is a int;
        // $subres                 is a int;
        // $res                    is a int;
        // $njpath                 is a str;
        // $jpath                  is a str;
        // $fun                    is a str;
        // $attr                   is a str;
        // $value                  is a str;
        // $url                    is a str;
        // $tmp                    is a str;
        // $new_calib              is a str;
        // $sensorType             is a str;
        // $unit_name              is a str;
        // $newval                 is a str;
        // $oldval                 is a str;
        // $old_calib              is a str;
        // $each_str               is a str;
        // $do_update              is a bool;
        // $found                  is a bool;
        $res = YAPI_SUCCESS;
        $tmp = $settings;
        $tmp = $this->_get_json_path($tmp, 'api');
        if (!($tmp == '')) {
            $settings = $tmp;
        }
        $oldval = '';
        $newval = '';
        $old_json_flat = $this->_flattenJsonStruct($settings);
        $old_dslist = $this->_json_get_array($old_json_flat);
        foreach($old_dslist as $each) {
            $each_str = $this->_json_get_string($each);
            // split json path and attr
            $leng = strlen($each_str);
            $eqpos = Ystrpos($each_str,'=');
            if (($eqpos < 0) || ($leng == 0)) {
                $this->_throw(YAPI_INVALID_ARGUMENT, 'Invalid settings');
                return YAPI_INVALID_ARGUMENT;
            }
            $jpath = substr($each_str,  0, $eqpos);
            $eqpos = $eqpos + 1;
            $value = substr($each_str,  $eqpos, $leng - $eqpos);
            $old_jpath[] = $jpath;
            $old_jpath_len[] = strlen($jpath);
            $old_val_arr[] = $value;
        }
        try {
            $actualSettings = $this->_download('api.json');
        } catch (Exception $ex) {
            // retry silently after a short wait
            YAPI.Sleep(500);
            $actualSettings = $this->_download('api.json');
        }
        $actualSettings = $this->_flattenJsonStruct($actualSettings);
        $new_dslist = $this->_json_get_array($actualSettings);
        foreach($new_dslist as $each) {
            // remove quotes
            $each_str = $this->_json_get_string($each);
            // split json path and attr
            $leng = strlen($each_str);
            $eqpos = Ystrpos($each_str,'=');
            if (($eqpos < 0) || ($leng == 0)) {
                $this->_throw(YAPI_INVALID_ARGUMENT, 'Invalid settings');
                return YAPI_INVALID_ARGUMENT;
            }
            $jpath = substr($each_str,  0, $eqpos);
            $eqpos = $eqpos + 1;
            $value = substr($each_str,  $eqpos, $leng - $eqpos);
            $new_jpath[] = $jpath;
            $new_jpath_len[] = strlen($jpath);
            $new_val_arr[] = $value;
        }
        $i = 0;
        while ($i < sizeof($new_jpath)) {
            $njpath = $new_jpath[$i];
            $leng = strlen($njpath);
            $cpos = Ystrpos($njpath,'/');
            if (($cpos < 0) || ($leng == 0)) {
                continue;
            }
            $fun = substr($njpath,  0, $cpos);
            $cpos = $cpos + 1;
            $attr = substr($njpath,  $cpos, $leng - $cpos);
            $do_update = true;
            if ($fun == 'services') {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'firmwareRelease')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'usbCurrent')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'upTime')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'persistentSettings')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'adminPassword')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'userPassword')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'rebootCountdown')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'advertisedValue')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'poeCurrent')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'readiness')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'ipAddress')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'subnetMask')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'router')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'linkQuality')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'ssid')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'channel')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'security')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'message')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'signalValue')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'currentValue')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'currentRawValue')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'currentRunIndex')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'pulseTimer')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'lastTimePressed')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'lastTimeReleased')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'filesCount')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'freeSpace')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'timeUTC')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'rtcTime')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'unixTime')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'dateTime')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'rawValue')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'lastMsg')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'delayedPulseTimer')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'rxCount')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'txCount')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'msgCount')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'rxMsgCount')) {
                $do_update = false;
            }
            if (($do_update) && ($attr == 'txMsgCount')) {
                $do_update = false;
            }
            if ($do_update) {
                $do_update = false;
                $newval = $new_val_arr[$i];
                $j = 0;
                $found = false;
                while (($j < sizeof($old_jpath)) && !($found)) {
                    if (($new_jpath_len[$i] == $old_jpath_len[$j]) && ($new_jpath[$i] == $old_jpath[$j])) {
                        $found = true;
                        $oldval = $old_val_arr[$j];
                        if (!($newval == $oldval)) {
                            $do_update = true;
                        }
                    }
                    $j = $j + 1;
                }
            }
            if ($do_update) {
                if ($attr == 'calibrationParam') {
                    $old_calib = '';
                    $unit_name = '';
                    $sensorType = '';
                    $new_calib = $newval;
                    $j = 0;
                    $found = false;
                    while (($j < sizeof($old_jpath)) && !($found)) {
                        if (($new_jpath_len[$i] == $old_jpath_len[$j]) && ($new_jpath[$i] == $old_jpath[$j])) {
                            $found = true;
                            $old_calib = $old_val_arr[$j];
                        }
                        $j = $j + 1;
                    }
                    $tmp = $fun . '/unit';
                    $j = 0;
                    $found = false;
                    while (($j < sizeof($new_jpath)) && !($found)) {
                        if ($tmp == $new_jpath[$j]) {
                            $found = true;
                            $unit_name = $new_val_arr[$j];
                        }
                        $j = $j + 1;
                    }
                    $tmp = $fun . '/sensorType';
                    $j = 0;
                    $found = false;
                    while (($j < sizeof($new_jpath)) && !($found)) {
                        if ($tmp == $new_jpath[$j]) {
                            $found = true;
                            $sensorType = $new_val_arr[$j];
                        }
                        $j = $j + 1;
                    }
                    $newval = $this->calibConvert($old_calib, $new_val_arr[$i], $unit_name, $sensorType);
                    $url = 'api/' . $fun . '.json?' . $attr . '=' . $this->_escapeAttr($newval);
                    $subres = $this->_tryExec($url);
                    if (($res == YAPI_SUCCESS) && ($subres != YAPI_SUCCESS)) {
                        $res = $subres;
                    }
                } else {
                    $url = 'api/' . $fun . '.json?' . $attr . '=' . $this->_escapeAttr($oldval);
                    if ($attr == 'resolution') {
                        $restoreLast[] = $url;
                    } else {
                        $subres = $this->_tryExec($url);
                        if (($res == YAPI_SUCCESS) && ($subres != YAPI_SUCCESS)) {
                            $res = $subres;
                        }
                    }
                }
            }
            $i = $i + 1;
        }
        foreach($restoreLast as $each) {
            $subres = $this->_tryExec($each);
            if (($res == YAPI_SUCCESS) && ($subres != YAPI_SUCCESS)) {
                $res = $subres;
            }
        }
        $this->clearCache();
        return $res;
    }
    public function addFileToHTTPCallback($filename)
    {
        // $content                is a bin;
        $content = $this->_download('@YCB+' . $filename);
        if (strlen($content) == 0) {
            return YAPI_NOT_SUPPORTED;
        }
        return YAPI_SUCCESS;
    }
    public function get_hardwareId()
    {
        // $serial                 is a str;
        $serial = $this->get_serialNumber();
        return $serial . '.module';
    }
    public function download($pathname)
    {
        return $this->_download($pathname);
    }
    public function get_icon2d()
    {
        return $this->_download('icon2d.png');
    }
    public function get_lastLogs()
    {
        // $content                is a bin;
        $content = $this->_download('logs.txt');
        return $content;
    }
    public function log($text)
    {
        return $this->_upload('logs.txt', $text);
    }
    public function get_subDevices()
    {
        return $this->get_subDevices_internal();
    }
    //cannot be generated for PHP:
    //private function get_subDevices_internal()
    public function get_parentHub()
    {
        return $this->get_parentHub_internal();
    }
    //cannot be generated for PHP:
    //private function get_parentHub_internal()
    public function get_url()
    {
        return $this->get_url_internal();
    }
    //cannot be generated for PHP:
    //private function get_url_internal()
    public function productName()
    { return $this->get_productName(); }
    public function serialNumber()
    { return $this->get_serialNumber(); }
    public function productId()
    { return $this->get_productId(); }
    public function productRelease()
    { return $this->get_productRelease(); }
    public function firmwareRelease()
    { return $this->get_firmwareRelease(); }
    public function persistentSettings()
    { return $this->get_persistentSettings(); }
    public function setPersistentSettings($newval)
    { return $this->set_persistentSettings($newval); }
    public function luminosity()
    { return $this->get_luminosity(); }
    public function setLuminosity($newval)
    { return $this->set_luminosity($newval); }
    public function beacon()
    { return $this->get_beacon(); }
    public function setBeacon($newval)
    { return $this->set_beacon($newval); }
    public function upTime()
    { return $this->get_upTime(); }
    public function usbCurrent()
    { return $this->get_usbCurrent(); }
    public function rebootCountdown()
    { return $this->get_rebootCountdown(); }
    public function setRebootCountdown($newval)
    { return $this->set_rebootCountdown($newval); }
    public function userVar()
    { return $this->get_userVar(); }
    public function setUserVar($newval)
    { return $this->set_userVar($newval); }
    public function nextModule()
    {   $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if($resolve->errorType != YAPI_SUCCESS) return null;
        $next_hwid = YAPI::getNextHardwareId($this->_className, $resolve->result);
        if($next_hwid == null) return null;
        return self::FindModule($next_hwid);
    }
    public static function FirstModule()
    {   $next_hwid = YAPI::getFirstHardwareId('Module');
        if($next_hwid == null) return null;
        return self::FindModule($next_hwid);
    }
    //--- (end of generated code: YModule implementation)
}
function ySetHTTPCallbackCacheDir($str_directory)
{
    YAPI::SetHTTPCallbackCacheDir($str_directory);
}
function yClearHTTPCallbackCacheDir($bool_removeFiles)
{
    YAPI::ClearHTTPCallbackCacheDir($bool_removeFiles);
}
function yGetAPIVersion()
{
    return YAPI::GetAPIVersion();
}
function yInitAPI($mode = 0, &$errmsg = "")
{
    return YAPI::InitAPI($mode, $errmsg);
}
function yFreeAPI()
{
    YAPI::FreeAPI();
}
function yDisableExceptions()
{
    YAPI::DisableExceptions();
}
function yEnableExceptions()
{
    YAPI::EnableExceptions();
}
function yRegisterHub($url, &$errmsg = "")
{
    return YAPI::RegisterHub($url, $errmsg);
}
function yPreregisterHub($url, &$errmsg = "")
{
    return YAPI::PreregisterHub($url, $errmsg);
}
function yUnregisterHub($url)
{
    YAPI::UnregisterHub($url);
}
function yTestHub($url, $mstimeout, &$errmsg = "")
{
    return YAPI::TestHub($url, $mstimeout, $errmsg);
}
function yForwardHTTPCallback($url, &$errmsg = "")
{
    return YAPI::ForwardHTTPCallback($url, $errmsg);
}
function yUpdateDeviceList(&$errmsg = "")
{
    return YAPI::UpdateDeviceList($errmsg);
}
function yHandleEvents(&$errmsg = "")
{
    return YAPI::HandleEvents($errmsg);
}
function ySleep($ms_duration, &$errmsg = "")
{
    return YAPI::Sleep($ms_duration, $errmsg);
}
function yGetTickCount()
{
    return YAPI::GetTickCount();
}
function yCheckLogicalName($name)
{
    return YAPI::CheckLogicalName($name);
}
function yRegisterDeviceArrivalCallback($arrivalCallback)
{
    YAPI::RegisterDeviceArrivalCallback($arrivalCallback);
}
function yRegisterDeviceChangeCallback($changeCallback)
{
    YAPI::RegisterDeviceChangeCallback($changeCallback);
}
function yRegisterDeviceRemovalCallback($removalCallback)
{
    YAPI::RegisterDeviceRemovalCallback($removalCallback);
}
// Register a new value calibration handler for a given calibration type
//
function yRegisterCalibrationHandler($int_calibrationType, $calibrationHandler)
{
    YAPI::RegisterCalibrationHandler($int_calibrationType, $calibrationHandler);
}
// Standard value calibration handler (n-point linear error correction)
//
function yLinearCalibrationHandler($int_calibType, $float_rawValue, $arr_calibParams,
                                   $arr_calibRawValues, $arr_calibRefValues)
{
    return YAPI::LinearCalibrationHandler($int_calibType, $float_rawValue, $arr_calibParams,
        $arr_calibRawValues, $arr_calibRefValues);
}
for ($yHdlrIdx = 1; $yHdlrIdx <= 20; $yHdlrIdx++) {
    yRegisterCalibrationHandler($yHdlrIdx, 'yLinearCalibrationHandler');
}
yRegisterCalibrationHandler(YOCTO_CALIB_TYPE_OFS, 'yLinearCalibrationHandler');
//--- (generated code: YFunction functions)
function yFindFunction($func)
{
    return YFunction::FindFunction($func);
}
function yFirstFunction()
{
    return YFunction::FirstFunction();
}
//--- (end of generated code: YFunction functions)
//--- (generated code: YSensor functions)
function yFindSensor($func)
{
    return YSensor::FindSensor($func);
}
function yFirstSensor()
{
    return YSensor::FirstSensor();
}
//--- (end of generated code: YSensor functions)
//--- (generated code: YModule functions)
function yFindModule($func)
{
    return YModule::FindModule($func);
}
function yFirstModule()
{
    return YModule::FirstModule();
}
//--- (end of generated code: YModule functions)
//--- (generated code: YDataLogger definitions)
if(!defined('Y_RECORDING_OFF'))              define('Y_RECORDING_OFF',             0);
if(!defined('Y_RECORDING_ON'))               define('Y_RECORDING_ON',              1);
if(!defined('Y_RECORDING_PENDING'))          define('Y_RECORDING_PENDING',         2);
if(!defined('Y_RECORDING_INVALID'))          define('Y_RECORDING_INVALID',         -1);
if(!defined('Y_AUTOSTART_OFF'))              define('Y_AUTOSTART_OFF',             0);
if(!defined('Y_AUTOSTART_ON'))               define('Y_AUTOSTART_ON',              1);
if(!defined('Y_AUTOSTART_INVALID'))          define('Y_AUTOSTART_INVALID',         -1);
if(!defined('Y_BEACONDRIVEN_OFF'))           define('Y_BEACONDRIVEN_OFF',          0);
if(!defined('Y_BEACONDRIVEN_ON'))            define('Y_BEACONDRIVEN_ON',           1);
if(!defined('Y_BEACONDRIVEN_INVALID'))       define('Y_BEACONDRIVEN_INVALID',      -1);
if(!defined('Y_CLEARHISTORY_FALSE'))         define('Y_CLEARHISTORY_FALSE',        0);
if(!defined('Y_CLEARHISTORY_TRUE'))          define('Y_CLEARHISTORY_TRUE',         1);
if(!defined('Y_CLEARHISTORY_INVALID'))       define('Y_CLEARHISTORY_INVALID',      -1);
if(!defined('Y_CURRENTRUNINDEX_INVALID'))    define('Y_CURRENTRUNINDEX_INVALID',   YAPI_INVALID_UINT);
if(!defined('Y_TIMEUTC_INVALID'))            define('Y_TIMEUTC_INVALID',           YAPI_INVALID_LONG);
if(!defined('Y_USAGE_INVALID'))              define('Y_USAGE_INVALID',             YAPI_INVALID_UINT);
//--- (end of generated code: YDataLogger definitions)
//--- (generated code: YDataLogger declaration)
class YDataLogger extends YFunction
{
    const CURRENTRUNINDEX_INVALID        = YAPI_INVALID_UINT;
    const TIMEUTC_INVALID                = YAPI_INVALID_LONG;
    const RECORDING_OFF                  = 0;
    const RECORDING_ON                   = 1;
    const RECORDING_PENDING              = 2;
    const RECORDING_INVALID              = -1;
    const AUTOSTART_OFF                  = 0;
    const AUTOSTART_ON                   = 1;
    const AUTOSTART_INVALID              = -1;
    const BEACONDRIVEN_OFF               = 0;
    const BEACONDRIVEN_ON                = 1;
    const BEACONDRIVEN_INVALID           = -1;
    const USAGE_INVALID                  = YAPI_INVALID_UINT;
    const CLEARHISTORY_FALSE             = 0;
    const CLEARHISTORY_TRUE              = 1;
    const CLEARHISTORY_INVALID           = -1;
    //--- (end of generated code: YDataLogger declaration)
    //--- (generated code: YDataLogger attributes)
    protected $_currentRunIndex          = Y_CURRENTRUNINDEX_INVALID;    // UInt31
    protected $_timeUTC                  = Y_TIMEUTC_INVALID;            // UTCTime
    protected $_recording                = Y_RECORDING_INVALID;          // OffOnPending
    protected $_autoStart                = Y_AUTOSTART_INVALID;          // OnOff
    protected $_beaconDriven             = Y_BEACONDRIVEN_INVALID;       // OnOff
    protected $_usage                    = Y_USAGE_INVALID;              // Percent
    protected $_clearHistory             = Y_CLEARHISTORY_INVALID;       // Bool
    //--- (end of generated code: YDataLogger attributes)
    protected $dataLoggerURL = null;
    function __construct($str_func)
    {
        //--- (generated code: YDataLogger constructor)
        parent::__construct($str_func);
        $this->_className = 'DataLogger';
        //--- (end of generated code: YDataLogger constructor)
    }
    // Internal function to retrieve datalogger memory
    //
    public function getData($runIdx, $timeIdx, &$loadval)
    {
        if (is_null($this->dataLoggerURL)) {
            $this->dataLoggerURL = "/logger.json";
        }
        // get the device serial number
        $devid = $this->module()->get_serialNumber();
        if ($devid == Y_SERIALNUMBER_INVALID) {
            return $this->get_errorType();
        }
        $httpreq = "GET " . $this->dataLoggerURL;
        if (!is_null($timeIdx)) {
            $httpreq .= "?run={$runIdx}&time={$timeIdx}";
        }
        $yreq = YAPI::devRequest($devid, $httpreq);
        if ($yreq->errorType != YAPI_SUCCESS) {
            if (strpos($yreq->errorMsg, 'HTTP status 404') !== false && $this->dataLoggerURL != "/dataLogger.json") {
                $this->dataLoggerURL = "/dataLogger.json";
                return $this->getData($runIdx, $timeIdx, $loadval);
            }
            return $yreq->errorType;
        }
        $loadval = json_decode($yreq->result, true);
        return YAPI_SUCCESS;
    }
    //--- (generated code: YDataLogger implementation)
    function _parseAttr($name, $val)
    {
        switch($name) {
        case 'currentRunIndex':
            $this->_currentRunIndex = intval($val);
            return 1;
        case 'timeUTC':
            $this->_timeUTC = intval($val);
            return 1;
        case 'recording':
            $this->_recording = intval($val);
            return 1;
        case 'autoStart':
            $this->_autoStart = intval($val);
            return 1;
        case 'beaconDriven':
            $this->_beaconDriven = intval($val);
            return 1;
        case 'usage':
            $this->_usage = intval($val);
            return 1;
        case 'clearHistory':
            $this->_clearHistory = intval($val);
            return 1;
        }
        return parent::_parseAttr($name, $val);
    }
    public function get_currentRunIndex()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CURRENTRUNINDEX_INVALID;
            }
        }
        $res = $this->_currentRunIndex;
        return $res;
    }
    public function get_timeUTC()
    {
        // $res                    is a long;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_TIMEUTC_INVALID;
            }
        }
        $res = $this->_timeUTC;
        return $res;
    }
    public function set_timeUTC($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("timeUTC",$rest_val);
    }
    public function get_recording()
    {
        // $res                    is a enumOFFONPENDING;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_RECORDING_INVALID;
            }
        }
        $res = $this->_recording;
        return $res;
    }
    public function set_recording($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("recording",$rest_val);
    }
    public function get_autoStart()
    {
        // $res                    is a enumONOFF;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_AUTOSTART_INVALID;
            }
        }
        $res = $this->_autoStart;
        return $res;
    }
    public function set_autoStart($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("autoStart",$rest_val);
    }
    public function get_beaconDriven()
    {
        // $res                    is a enumONOFF;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_BEACONDRIVEN_INVALID;
            }
        }
        $res = $this->_beaconDriven;
        return $res;
    }
    public function set_beaconDriven($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("beaconDriven",$rest_val);
    }
    public function get_usage()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_USAGE_INVALID;
            }
        }
        $res = $this->_usage;
        return $res;
    }
    public function get_clearHistory()
    {
        // $res                    is a enumBOOL;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CLEARHISTORY_INVALID;
            }
        }
        $res = $this->_clearHistory;
        return $res;
    }
    public function set_clearHistory($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("clearHistory",$rest_val);
    }
    public static function FindDataLogger($func)
    {
        // $obj                    is a YDataLogger;
        $obj = YFunction::_FindFromCache('DataLogger', $func);
        if ($obj == null) {
            $obj = new YDataLogger($func);
            YFunction::_AddToCache('DataLogger', $func, $obj);
        }
        return $obj;
    }
    public function forgetAllDataStreams()
    {
        return $this->set_clearHistory(Y_CLEARHISTORY_TRUE);
    }
    public function get_dataSets()
    {
        return $this->parse_dataSets($this->_download('logger.json'));
    }
    public function parse_dataSets($json)
    {
        $dslist = Array();      // strArr;
        // $dataset                is a YDataSetPtr;
        $res = Array();         // YDataSetArr;
        $dslist = $this->_json_get_array($json);
        while(sizeof($res) > 0) { array_pop($res); };
        foreach($dslist as $each) {
            $dataset = new YDataSet($this);
            $dataset->_parse($each);
            $res[] = $dataset;
        }
        return $res;
    }
    public function currentRunIndex()
    { return $this->get_currentRunIndex(); }
    public function timeUTC()
    { return $this->get_timeUTC(); }
    public function setTimeUTC($newval)
    { return $this->set_timeUTC($newval); }
    public function recording()
    { return $this->get_recording(); }
    public function setRecording($newval)
    { return $this->set_recording($newval); }
    public function autoStart()
    { return $this->get_autoStart(); }
    public function setAutoStart($newval)
    { return $this->set_autoStart($newval); }
    public function beaconDriven()
    { return $this->get_beaconDriven(); }
    public function setBeaconDriven($newval)
    { return $this->set_beaconDriven($newval); }
    public function usage()
    { return $this->get_usage(); }
    public function clearHistory()
    { return $this->get_clearHistory(); }
    public function setClearHistory($newval)
    { return $this->set_clearHistory($newval); }
    public function nextDataLogger()
    {   $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if($resolve->errorType != YAPI_SUCCESS) return null;
        $next_hwid = YAPI::getNextHardwareId($this->_className, $resolve->result);
        if($next_hwid == null) return null;
        return self::FindDataLogger($next_hwid);
    }
    public static function FirstDataLogger()
    {   $next_hwid = YAPI::getFirstHardwareId('DataLogger');
        if($next_hwid == null) return null;
        return self::FindDataLogger($next_hwid);
    }
    //--- (end of generated code: YDataLogger implementation)
}
//--- (generated code: YDataLogger functions)
function yFindDataLogger($func)
{
    return YDataLogger::FindDataLogger($func);
}
function yFirstDataLogger()
{
    return YDataLogger::FirstDataLogger();
}
//--- (end of generated code: YDataLogger functions)

//--- (YNetwork return codes)
//--- (end of YNetwork return codes)
//--- (YNetwork definitions)
if(!defined('Y_READINESS_DOWN'))             define('Y_READINESS_DOWN',            0);
if(!defined('Y_READINESS_EXISTS'))           define('Y_READINESS_EXISTS',          1);
if(!defined('Y_READINESS_LINKED'))           define('Y_READINESS_LINKED',          2);
if(!defined('Y_READINESS_LAN_OK'))           define('Y_READINESS_LAN_OK',          3);
if(!defined('Y_READINESS_WWW_OK'))           define('Y_READINESS_WWW_OK',          4);
if(!defined('Y_READINESS_INVALID'))          define('Y_READINESS_INVALID',         -1);
if(!defined('Y_DISCOVERABLE_FALSE'))         define('Y_DISCOVERABLE_FALSE',        0);
if(!defined('Y_DISCOVERABLE_TRUE'))          define('Y_DISCOVERABLE_TRUE',         1);
if(!defined('Y_DISCOVERABLE_INVALID'))       define('Y_DISCOVERABLE_INVALID',      -1);
if(!defined('Y_CALLBACKMETHOD_POST'))        define('Y_CALLBACKMETHOD_POST',       0);
if(!defined('Y_CALLBACKMETHOD_GET'))         define('Y_CALLBACKMETHOD_GET',        1);
if(!defined('Y_CALLBACKMETHOD_PUT'))         define('Y_CALLBACKMETHOD_PUT',        2);
if(!defined('Y_CALLBACKMETHOD_INVALID'))     define('Y_CALLBACKMETHOD_INVALID',    -1);
if(!defined('Y_CALLBACKENCODING_FORM'))      define('Y_CALLBACKENCODING_FORM',     0);
if(!defined('Y_CALLBACKENCODING_JSON'))      define('Y_CALLBACKENCODING_JSON',     1);
if(!defined('Y_CALLBACKENCODING_JSON_ARRAY')) define('Y_CALLBACKENCODING_JSON_ARRAY', 2);
if(!defined('Y_CALLBACKENCODING_CSV'))       define('Y_CALLBACKENCODING_CSV',      3);
if(!defined('Y_CALLBACKENCODING_YOCTO_API')) define('Y_CALLBACKENCODING_YOCTO_API', 4);
if(!defined('Y_CALLBACKENCODING_JSON_NUM'))  define('Y_CALLBACKENCODING_JSON_NUM', 5);
if(!defined('Y_CALLBACKENCODING_EMONCMS'))   define('Y_CALLBACKENCODING_EMONCMS',  6);
if(!defined('Y_CALLBACKENCODING_AZURE'))     define('Y_CALLBACKENCODING_AZURE',    7);
if(!defined('Y_CALLBACKENCODING_INFLUXDB'))  define('Y_CALLBACKENCODING_INFLUXDB', 8);
if(!defined('Y_CALLBACKENCODING_MQTT'))      define('Y_CALLBACKENCODING_MQTT',     9);
if(!defined('Y_CALLBACKENCODING_YOCTO_API_JZON')) define('Y_CALLBACKENCODING_YOCTO_API_JZON', 10);
if(!defined('Y_CALLBACKENCODING_PRTG'))      define('Y_CALLBACKENCODING_PRTG',     11);
if(!defined('Y_CALLBACKENCODING_INFLUXDB_V2')) define('Y_CALLBACKENCODING_INFLUXDB_V2', 12);
if(!defined('Y_CALLBACKENCODING_INVALID'))   define('Y_CALLBACKENCODING_INVALID',  -1);
if(!defined('Y_MACADDRESS_INVALID'))         define('Y_MACADDRESS_INVALID',        YAPI_INVALID_STRING);
if(!defined('Y_IPADDRESS_INVALID'))          define('Y_IPADDRESS_INVALID',         YAPI_INVALID_STRING);
if(!defined('Y_SUBNETMASK_INVALID'))         define('Y_SUBNETMASK_INVALID',        YAPI_INVALID_STRING);
if(!defined('Y_ROUTER_INVALID'))             define('Y_ROUTER_INVALID',            YAPI_INVALID_STRING);
if(!defined('Y_CURRENTDNS_INVALID'))         define('Y_CURRENTDNS_INVALID',        YAPI_INVALID_STRING);
if(!defined('Y_IPCONFIG_INVALID'))           define('Y_IPCONFIG_INVALID',          YAPI_INVALID_STRING);
if(!defined('Y_PRIMARYDNS_INVALID'))         define('Y_PRIMARYDNS_INVALID',        YAPI_INVALID_STRING);
if(!defined('Y_SECONDARYDNS_INVALID'))       define('Y_SECONDARYDNS_INVALID',      YAPI_INVALID_STRING);
if(!defined('Y_NTPSERVER_INVALID'))          define('Y_NTPSERVER_INVALID',         YAPI_INVALID_STRING);
if(!defined('Y_USERPASSWORD_INVALID'))       define('Y_USERPASSWORD_INVALID',      YAPI_INVALID_STRING);
if(!defined('Y_ADMINPASSWORD_INVALID'))      define('Y_ADMINPASSWORD_INVALID',     YAPI_INVALID_STRING);
if(!defined('Y_HTTPPORT_INVALID'))           define('Y_HTTPPORT_INVALID',          YAPI_INVALID_UINT);
if(!defined('Y_DEFAULTPAGE_INVALID'))        define('Y_DEFAULTPAGE_INVALID',       YAPI_INVALID_STRING);
if(!defined('Y_WWWWATCHDOGDELAY_INVALID'))   define('Y_WWWWATCHDOGDELAY_INVALID',  YAPI_INVALID_UINT);
if(!defined('Y_CALLBACKURL_INVALID'))        define('Y_CALLBACKURL_INVALID',       YAPI_INVALID_STRING);
if(!defined('Y_CALLBACKCREDENTIALS_INVALID')) define('Y_CALLBACKCREDENTIALS_INVALID', YAPI_INVALID_STRING);
if(!defined('Y_CALLBACKINITIALDELAY_INVALID')) define('Y_CALLBACKINITIALDELAY_INVALID', YAPI_INVALID_UINT);
if(!defined('Y_CALLBACKSCHEDULE_INVALID'))   define('Y_CALLBACKSCHEDULE_INVALID',  YAPI_INVALID_STRING);
if(!defined('Y_CALLBACKMINDELAY_INVALID'))   define('Y_CALLBACKMINDELAY_INVALID',  YAPI_INVALID_UINT);
if(!defined('Y_CALLBACKMAXDELAY_INVALID'))   define('Y_CALLBACKMAXDELAY_INVALID',  YAPI_INVALID_UINT);
if(!defined('Y_POECURRENT_INVALID'))         define('Y_POECURRENT_INVALID',        YAPI_INVALID_UINT);
//--- (end of YNetwork definitions)
    #--- (YNetwork yapiwrapper)
   #--- (end of YNetwork yapiwrapper)
//--- (YNetwork declaration)
class YNetwork extends YFunction
{
    const READINESS_DOWN                 = 0;
    const READINESS_EXISTS               = 1;
    const READINESS_LINKED               = 2;
    const READINESS_LAN_OK               = 3;
    const READINESS_WWW_OK               = 4;
    const READINESS_INVALID              = -1;
    const MACADDRESS_INVALID             = YAPI_INVALID_STRING;
    const IPADDRESS_INVALID              = YAPI_INVALID_STRING;
    const SUBNETMASK_INVALID             = YAPI_INVALID_STRING;
    const ROUTER_INVALID                 = YAPI_INVALID_STRING;
    const CURRENTDNS_INVALID             = YAPI_INVALID_STRING;
    const IPCONFIG_INVALID               = YAPI_INVALID_STRING;
    const PRIMARYDNS_INVALID             = YAPI_INVALID_STRING;
    const SECONDARYDNS_INVALID           = YAPI_INVALID_STRING;
    const NTPSERVER_INVALID              = YAPI_INVALID_STRING;
    const USERPASSWORD_INVALID           = YAPI_INVALID_STRING;
    const ADMINPASSWORD_INVALID          = YAPI_INVALID_STRING;
    const HTTPPORT_INVALID               = YAPI_INVALID_UINT;
    const DEFAULTPAGE_INVALID            = YAPI_INVALID_STRING;
    const DISCOVERABLE_FALSE             = 0;
    const DISCOVERABLE_TRUE              = 1;
    const DISCOVERABLE_INVALID           = -1;
    const WWWWATCHDOGDELAY_INVALID       = YAPI_INVALID_UINT;
    const CALLBACKURL_INVALID            = YAPI_INVALID_STRING;
    const CALLBACKMETHOD_POST            = 0;
    const CALLBACKMETHOD_GET             = 1;
    const CALLBACKMETHOD_PUT             = 2;
    const CALLBACKMETHOD_INVALID         = -1;
    const CALLBACKENCODING_FORM          = 0;
    const CALLBACKENCODING_JSON          = 1;
    const CALLBACKENCODING_JSON_ARRAY    = 2;
    const CALLBACKENCODING_CSV           = 3;
    const CALLBACKENCODING_YOCTO_API     = 4;
    const CALLBACKENCODING_JSON_NUM      = 5;
    const CALLBACKENCODING_EMONCMS       = 6;
    const CALLBACKENCODING_AZURE         = 7;
    const CALLBACKENCODING_INFLUXDB      = 8;
    const CALLBACKENCODING_MQTT          = 9;
    const CALLBACKENCODING_YOCTO_API_JZON = 10;
    const CALLBACKENCODING_PRTG          = 11;
    const CALLBACKENCODING_INFLUXDB_V2   = 12;
    const CALLBACKENCODING_INVALID       = -1;
    const CALLBACKCREDENTIALS_INVALID    = YAPI_INVALID_STRING;
    const CALLBACKINITIALDELAY_INVALID   = YAPI_INVALID_UINT;
    const CALLBACKSCHEDULE_INVALID       = YAPI_INVALID_STRING;
    const CALLBACKMINDELAY_INVALID       = YAPI_INVALID_UINT;
    const CALLBACKMAXDELAY_INVALID       = YAPI_INVALID_UINT;
    const POECURRENT_INVALID             = YAPI_INVALID_UINT;
    //--- (end of YNetwork declaration)
    //--- (YNetwork attributes)
    protected $_readiness                = Y_READINESS_INVALID;          // Readiness
    protected $_macAddress               = Y_MACADDRESS_INVALID;         // MACAddress
    protected $_ipAddress                = Y_IPADDRESS_INVALID;          // IPAddress
    protected $_subnetMask               = Y_SUBNETMASK_INVALID;         // IPAddress
    protected $_router                   = Y_ROUTER_INVALID;             // IPAddress
    protected $_currentDNS               = Y_CURRENTDNS_INVALID;         // IPAddress
    protected $_ipConfig                 = Y_IPCONFIG_INVALID;           // IPConfig
    protected $_primaryDNS               = Y_PRIMARYDNS_INVALID;         // IPAddress
    protected $_secondaryDNS             = Y_SECONDARYDNS_INVALID;       // IPAddress
    protected $_ntpServer                = Y_NTPSERVER_INVALID;          // IPAddress
    protected $_userPassword             = Y_USERPASSWORD_INVALID;       // UserPassword
    protected $_adminPassword            = Y_ADMINPASSWORD_INVALID;      // AdminPassword
    protected $_httpPort                 = Y_HTTPPORT_INVALID;           // UInt31
    protected $_defaultPage              = Y_DEFAULTPAGE_INVALID;        // Text
    protected $_discoverable             = Y_DISCOVERABLE_INVALID;       // Bool
    protected $_wwwWatchdogDelay         = Y_WWWWATCHDOGDELAY_INVALID;   // UInt31
    protected $_callbackUrl              = Y_CALLBACKURL_INVALID;        // Text
    protected $_callbackMethod           = Y_CALLBACKMETHOD_INVALID;     // HTTPMethod
    protected $_callbackEncoding         = Y_CALLBACKENCODING_INVALID;   // CallbackEncoding
    protected $_callbackCredentials      = Y_CALLBACKCREDENTIALS_INVALID; // Credentials
    protected $_callbackInitialDelay     = Y_CALLBACKINITIALDELAY_INVALID; // UInt31
    protected $_callbackSchedule         = Y_CALLBACKSCHEDULE_INVALID;   // CallbackSchedule
    protected $_callbackMinDelay         = Y_CALLBACKMINDELAY_INVALID;   // UInt31
    protected $_callbackMaxDelay         = Y_CALLBACKMAXDELAY_INVALID;   // UInt31
    protected $_poeCurrent               = Y_POECURRENT_INVALID;         // UsedCurrent
    //--- (end of YNetwork attributes)
    function __construct($str_func)
    {
        //--- (YNetwork constructor)
        parent::__construct($str_func);
        $this->_className = 'Network';
        //--- (end of YNetwork constructor)
    }
    //--- (YNetwork implementation)
    function _parseAttr($name, $val)
    {
        switch($name) {
        case 'readiness':
            $this->_readiness = intval($val);
            return 1;
        case 'macAddress':
            $this->_macAddress = $val;
            return 1;
        case 'ipAddress':
            $this->_ipAddress = $val;
            return 1;
        case 'subnetMask':
            $this->_subnetMask = $val;
            return 1;
        case 'router':
            $this->_router = $val;
            return 1;
        case 'currentDNS':
            $this->_currentDNS = $val;
            return 1;
        case 'ipConfig':
            $this->_ipConfig = $val;
            return 1;
        case 'primaryDNS':
            $this->_primaryDNS = $val;
            return 1;
        case 'secondaryDNS':
            $this->_secondaryDNS = $val;
            return 1;
        case 'ntpServer':
            $this->_ntpServer = $val;
            return 1;
        case 'userPassword':
            $this->_userPassword = $val;
            return 1;
        case 'adminPassword':
            $this->_adminPassword = $val;
            return 1;
        case 'httpPort':
            $this->_httpPort = intval($val);
            return 1;
        case 'defaultPage':
            $this->_defaultPage = $val;
            return 1;
        case 'discoverable':
            $this->_discoverable = intval($val);
            return 1;
        case 'wwwWatchdogDelay':
            $this->_wwwWatchdogDelay = intval($val);
            return 1;
        case 'callbackUrl':
            $this->_callbackUrl = $val;
            return 1;
        case 'callbackMethod':
            $this->_callbackMethod = intval($val);
            return 1;
        case 'callbackEncoding':
            $this->_callbackEncoding = intval($val);
            return 1;
        case 'callbackCredentials':
            $this->_callbackCredentials = $val;
            return 1;
        case 'callbackInitialDelay':
            $this->_callbackInitialDelay = intval($val);
            return 1;
        case 'callbackSchedule':
            $this->_callbackSchedule = $val;
            return 1;
        case 'callbackMinDelay':
            $this->_callbackMinDelay = intval($val);
            return 1;
        case 'callbackMaxDelay':
            $this->_callbackMaxDelay = intval($val);
            return 1;
        case 'poeCurrent':
            $this->_poeCurrent = intval($val);
            return 1;
        }
        return parent::_parseAttr($name, $val);
    }
    public function get_readiness()
    {
        // $res                    is a enumREADINESS;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_READINESS_INVALID;
            }
        }
        $res = $this->_readiness;
        return $res;
    }
    public function get_macAddress()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration == 0) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_MACADDRESS_INVALID;
            }
        }
        $res = $this->_macAddress;
        return $res;
    }
    public function get_ipAddress()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_IPADDRESS_INVALID;
            }
        }
        $res = $this->_ipAddress;
        return $res;
    }
    public function get_subnetMask()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_SUBNETMASK_INVALID;
            }
        }
        $res = $this->_subnetMask;
        return $res;
    }
    public function get_router()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_ROUTER_INVALID;
            }
        }
        $res = $this->_router;
        return $res;
    }
    public function get_currentDNS()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CURRENTDNS_INVALID;
            }
        }
        $res = $this->_currentDNS;
        return $res;
    }
    public function get_ipConfig()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_IPCONFIG_INVALID;
            }
        }
        $res = $this->_ipConfig;
        return $res;
    }
    public function set_ipConfig($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("ipConfig",$rest_val);
    }
    public function get_primaryDNS()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_PRIMARYDNS_INVALID;
            }
        }
        $res = $this->_primaryDNS;
        return $res;
    }
    public function set_primaryDNS($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("primaryDNS",$rest_val);
    }
    public function get_secondaryDNS()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_SECONDARYDNS_INVALID;
            }
        }
        $res = $this->_secondaryDNS;
        return $res;
    }
    public function set_secondaryDNS($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("secondaryDNS",$rest_val);
    }
    public function get_ntpServer()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_NTPSERVER_INVALID;
            }
        }
        $res = $this->_ntpServer;
        return $res;
    }
    public function set_ntpServer($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("ntpServer",$rest_val);
    }
    public function get_userPassword()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_USERPASSWORD_INVALID;
            }
        }
        $res = $this->_userPassword;
        return $res;
    }
    public function set_userPassword($newval)
    {
        if (strlen($newval) > YAPI_HASH_BUF_SIZE)
            return $this->_throw(YAPI_INVALID_ARGUMENT,'Password too long :'.$newval);
        $rest_val = $newval;
        return $this->_setAttr("userPassword",$rest_val);
    }
    public function get_adminPassword()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_ADMINPASSWORD_INVALID;
            }
        }
        $res = $this->_adminPassword;
        return $res;
    }
    public function set_adminPassword($newval)
    {
        if (strlen($newval) > YAPI_HASH_BUF_SIZE)
            return $this->_throw(YAPI_INVALID_ARGUMENT,'Password too long :'.$newval);
        $rest_val = $newval;
        return $this->_setAttr("adminPassword",$rest_val);
    }
    public function get_httpPort()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_HTTPPORT_INVALID;
            }
        }
        $res = $this->_httpPort;
        return $res;
    }
    public function set_httpPort($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("httpPort",$rest_val);
    }
    public function get_defaultPage()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_DEFAULTPAGE_INVALID;
            }
        }
        $res = $this->_defaultPage;
        return $res;
    }
    public function set_defaultPage($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("defaultPage",$rest_val);
    }
    public function get_discoverable()
    {
        // $res                    is a enumBOOL;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_DISCOVERABLE_INVALID;
            }
        }
        $res = $this->_discoverable;
        return $res;
    }
    public function set_discoverable($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("discoverable",$rest_val);
    }
    public function get_wwwWatchdogDelay()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_WWWWATCHDOGDELAY_INVALID;
            }
        }
        $res = $this->_wwwWatchdogDelay;
        return $res;
    }
    public function set_wwwWatchdogDelay($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("wwwWatchdogDelay",$rest_val);
    }
    public function get_callbackUrl()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKURL_INVALID;
            }
        }
        $res = $this->_callbackUrl;
        return $res;
    }
    public function set_callbackUrl($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("callbackUrl",$rest_val);
    }
    public function get_callbackMethod()
    {
        // $res                    is a enumHTTPMETHOD;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKMETHOD_INVALID;
            }
        }
        $res = $this->_callbackMethod;
        return $res;
    }
    public function set_callbackMethod($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("callbackMethod",$rest_val);
    }
    public function get_callbackEncoding()
    {
        // $res                    is a enumCALLBACKENCODING;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKENCODING_INVALID;
            }
        }
        $res = $this->_callbackEncoding;
        return $res;
    }
    public function set_callbackEncoding($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("callbackEncoding",$rest_val);
    }
    public function get_callbackCredentials()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKCREDENTIALS_INVALID;
            }
        }
        $res = $this->_callbackCredentials;
        return $res;
    }
    public function set_callbackCredentials($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("callbackCredentials",$rest_val);
    }
    public function callbackLogin($username,$password)
    {
        $rest_val = sprintf("%s:%s", $username, $password);
        return $this->_setAttr("callbackCredentials",$rest_val);
    }
    public function get_callbackInitialDelay()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKINITIALDELAY_INVALID;
            }
        }
        $res = $this->_callbackInitialDelay;
        return $res;
    }
    public function set_callbackInitialDelay($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("callbackInitialDelay",$rest_val);
    }
    public function get_callbackSchedule()
    {
        // $res                    is a string;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKSCHEDULE_INVALID;
            }
        }
        $res = $this->_callbackSchedule;
        return $res;
    }
    public function set_callbackSchedule($newval)
    {
        $rest_val = $newval;
        return $this->_setAttr("callbackSchedule",$rest_val);
    }
    public function get_callbackMinDelay()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKMINDELAY_INVALID;
            }
        }
        $res = $this->_callbackMinDelay;
        return $res;
    }
    public function set_callbackMinDelay($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("callbackMinDelay",$rest_val);
    }
    public function get_callbackMaxDelay()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_CALLBACKMAXDELAY_INVALID;
            }
        }
        $res = $this->_callbackMaxDelay;
        return $res;
    }
    public function set_callbackMaxDelay($newval)
    {
        $rest_val = strval($newval);
        return $this->_setAttr("callbackMaxDelay",$rest_val);
    }
    public function get_poeCurrent()
    {
        // $res                    is a int;
        if ($this->_cacheExpiration <= YAPI::GetTickCount()) {
            if ($this->load(YAPI::$_yapiContext->GetCacheValidity()) != YAPI_SUCCESS) {
                return Y_POECURRENT_INVALID;
            }
        }
        $res = $this->_poeCurrent;
        return $res;
    }
    public static function FindNetwork($func)
    {
        // $obj                    is a YNetwork;
        $obj = YFunction::_FindFromCache('Network', $func);
        if ($obj == null) {
            $obj = new YNetwork($func);
            YFunction::_AddToCache('Network', $func, $obj);
        }
        return $obj;
    }
    public function useDHCP($fallbackIpAddr,$fallbackSubnetMaskLen,$fallbackRouter)
    {
        return $this->set_ipConfig(sprintf('DHCP:%s/%d/%s', $fallbackIpAddr, $fallbackSubnetMaskLen, $fallbackRouter));
    }
    public function useDHCPauto()
    {
        return $this->set_ipConfig('DHCP:');
    }
    public function useStaticIP($ipAddress,$subnetMaskLen,$router)
    {
        return $this->set_ipConfig(sprintf('STATIC:%s/%d/%s', $ipAddress, $subnetMaskLen, $router));
    }
    public function ping($host)
    {
        // $content                is a bin;
        $content = $this->_download(sprintf('ping.txt?host=%s',$host));
        return $content;
    }
    public function triggerCallback()
    {
        return $this->set_callbackMethod($this->get_callbackMethod());
    }
    public function set_periodicCallbackSchedule($interval,$offset)
    {
        return $this->set_callbackSchedule(sprintf('every %s+%d',$interval,$offset));
    }
    public function readiness()
    { return $this->get_readiness(); }
    public function macAddress()
    { return $this->get_macAddress(); }
    public function ipAddress()
    { return $this->get_ipAddress(); }
    public function subnetMask()
    { return $this->get_subnetMask(); }
    public function router()
    { return $this->get_router(); }
    public function currentDNS()
    { return $this->get_currentDNS(); }
    public function ipConfig()
    { return $this->get_ipConfig(); }
    public function setIpConfig($newval)
    { return $this->set_ipConfig($newval); }
    public function primaryDNS()
    { return $this->get_primaryDNS(); }
    public function setPrimaryDNS($newval)
    { return $this->set_primaryDNS($newval); }
    public function secondaryDNS()
    { return $this->get_secondaryDNS(); }
    public function setSecondaryDNS($newval)
    { return $this->set_secondaryDNS($newval); }
    public function ntpServer()
    { return $this->get_ntpServer(); }
    public function setNtpServer($newval)
    { return $this->set_ntpServer($newval); }
    public function userPassword()
    { return $this->get_userPassword(); }
    public function setUserPassword($newval)
    { return $this->set_userPassword($newval); }
    public function adminPassword()
    { return $this->get_adminPassword(); }
    public function setAdminPassword($newval)
    { return $this->set_adminPassword($newval); }
    public function httpPort()
    { return $this->get_httpPort(); }
    public function setHttpPort($newval)
    { return $this->set_httpPort($newval); }
    public function defaultPage()
    { return $this->get_defaultPage(); }
    public function setDefaultPage($newval)
    { return $this->set_defaultPage($newval); }
    public function discoverable()
    { return $this->get_discoverable(); }
    public function setDiscoverable($newval)
    { return $this->set_discoverable($newval); }
    public function wwwWatchdogDelay()
    { return $this->get_wwwWatchdogDelay(); }
    public function setWwwWatchdogDelay($newval)
    { return $this->set_wwwWatchdogDelay($newval); }
    public function callbackUrl()
    { return $this->get_callbackUrl(); }
    public function setCallbackUrl($newval)
    { return $this->set_callbackUrl($newval); }
    public function callbackMethod()
    { return $this->get_callbackMethod(); }
    public function setCallbackMethod($newval)
    { return $this->set_callbackMethod($newval); }
    public function callbackEncoding()
    { return $this->get_callbackEncoding(); }
    public function setCallbackEncoding($newval)
    { return $this->set_callbackEncoding($newval); }
    public function callbackCredentials()
    { return $this->get_callbackCredentials(); }
    public function setCallbackCredentials($newval)
    { return $this->set_callbackCredentials($newval); }
    public function callbackInitialDelay()
    { return $this->get_callbackInitialDelay(); }
    public function setCallbackInitialDelay($newval)
    { return $this->set_callbackInitialDelay($newval); }
    public function callbackSchedule()
    { return $this->get_callbackSchedule(); }
    public function setCallbackSchedule($newval)
    { return $this->set_callbackSchedule($newval); }
    public function callbackMinDelay()
    { return $this->get_callbackMinDelay(); }
    public function setCallbackMinDelay($newval)
    { return $this->set_callbackMinDelay($newval); }
    public function callbackMaxDelay()
    { return $this->get_callbackMaxDelay(); }
    public function setCallbackMaxDelay($newval)
    { return $this->set_callbackMaxDelay($newval); }
    public function poeCurrent()
    { return $this->get_poeCurrent(); }
    public function nextNetwork()
    {   $resolve = YAPI::resolveFunction($this->_className, $this->_func);
        if($resolve->errorType != YAPI_SUCCESS) return null;
        $next_hwid = YAPI::getNextHardwareId($this->_className, $resolve->result);
        if($next_hwid == null) return null;
        return self::FindNetwork($next_hwid);
    }
    public static function FirstNetwork()
    {   $next_hwid = YAPI::getFirstHardwareId('Network');
        if($next_hwid == null) return null;
        return self::FindNetwork($next_hwid);
    }
    //--- (end of YNetwork implementation)
};
//--- (YNetwork functions)
function yFindNetwork($func)
{
    return YNetwork::FindNetwork($func);
}
function yFirstNetwork()
{
    return YNetwork::FirstNetwork();
}
//--- (end of YNetwork functions)

define('NOTIF_FILE', 'VHUB4WEB-YN*.byn');   // Name pattern for the notification buffers filesconst NOTIF_FILE_ENDSIZE = 32768;const NOTIF_POS_WRAP = 0x100000000;const NOTIF_MAX_LEN = 69;const NOTIF_NAME = 'YN010';const NOTIF_PRODNAME = 'YN011';const NOTIF_CHILD = 'YN012';const NOTIF_FIRMWARE = 'YN013';const NOTIF_FUNCNAME = 'YN014';const NOTIF_FUNCVAL = 'YN015';const NOTIF_STREAMREADY = 'YN016';const NOTIF_LOG = 'YN017';const NOTIF_FUNCNAMEYDX = 'YN018';const NOTIF_PRODINFO = 'YN019';const NOTIF_CONFCHGYDX = 'YN01s';const NOTIF_FLUSHV2YDX = 'YN01t';const NOTIF_FUNCV2YDX = 'u';const NOTIF_TIMEV2YDX = 'v';const NOTIF_DEVLOGYDX = 'YN01w';const NOTIF_TIMEVALYDX = 'x';const NOTIF_FUNCVALYDX = 'y';const NOTIF_TIMEAVGYDX = 'z';class NotifStream{    protected $server;    protected $datadir;    protected $notfile;    protected $filepos;     // Absolute offset of first notification in file    protected $abspos;      // Current absolute position within stream    protected $curpos;      // Current position within file    protected $reqlen;      // Requested length, if any    private $avail;         // Quantity of notification available to send    protected  $fd;    public static function StreamAt(VHubServerHTTPRequest $httpReq, VHubServer $parent, int $position): NotifStream    {        $datadir = $parent->getDataDir();        $regexpr = '~^'.str_replace('*', '([0-9]+)', NOTIF_FILE).'$~';        $filelist = [];        foreach (glob($datadir.NOTIF_FILE) as $filepath) {            $filename = substr($filepath, strlen($datadir));            if(preg_match($regexpr, $filename, $matches)) {                $filelist[] = intVal($matches[1], 10);            }        }        if(sizeof($filelist) == 0) {            $filepos = 0;        } else {            sort($filelist);            if(max($filelist) - min($filelist) > (NOTIF_POS_WRAP/4)) {                // list of positions is wrapping, reorder them accordingly                for($i = 1; $i < sizeof($filelist); $i++) {                    if($filelist[$i] - $filelist[$i-1] > (NOTIF_POS_WRAP/4)) break;                }                $filelist = array_merge(array_slice($filelist, $i), array_slice($filelist, 0, $i));            }            if(sizeof($filelist) > 4) {                // cleanup oldest notification files                $notfile = sprintf(str_replace('*', '%010u', NOTIF_FILE), $filelist[0]);                $fullpath = $parent->getDataDir().$notfile;                if(file_exists($fullpath)) {                    @unlink($fullpath);                }            }            if($position == -1) {                // use the latest file                $filepos = $filelist[sizeof($filelist)-1];            } else {                // use the file containin the requested position, or the oldest file available                $filepos = $filelist[0];                for($i = 1; $i < sizeof($filelist); $i++) {                    if($position >= $filelist[$i] && $position < $filelist[$i] + (NOTIF_POS_WRAP/4)) {                        $filepos = $filelist[$i];                    }                }            }        }        VHubServer::Log($httpReq, LOG_CLIENTREQ, 5, 'Open stream at '.$position.', using file @'.$filepos);        return new NotifStream($parent, $filepos, $position);    }    public function __construct(VHubServer $parent, int $filepos, int $position)    {        $this->server = $parent;        $this->datadir = $parent->getDataDir();        $this->filepos = $filepos;        $this->abspos = $position;        $this->curpos = $position - $filepos;        $this->reqlen = -1;        $this->avail = 0;        $this->fd = null;    }    protected function openNotFile(VHubServerHTTPRequest $httpReq, string $mode, bool $readonly): bool    {        $this->notfile = sprintf(str_replace('*', '%010u', NOTIF_FILE), $this->filepos);        $fullpath = $this->datadir.$this->notfile;        if($readonly && !file_exists($fullpath)) {            $this->fd = null;            return false;        }        try {            $this->fd = @fopen($fullpath, $mode);        } catch(Throwable $e) {}        if($this->fd === false) {            $this->fd = null;            return false;        }        return true;    }    public function openForRead(VHubServerHTTPRequest $httpReq, int $length): int    {        if($length == 0) {            // default limit when flush is available but chunks help to improve performace            $length = 0x7f00;        }        if($length >= 0) {            // minimum value should allow for at least one notification            if($length < NOTIF_MAX_LEN + 15) {                $length = NOTIF_MAX_LEN + 15;            }        }        $this->reqlen = $length;        if($this->openNotFile($httpReq, 'rb', true)) {            fseek($this->fd, 0, SEEK_END);            $endpos = ftell($this->fd);        } else {            // file does not yet exist            $endpos = 0;        }        if($this->abspos == -1) {            $this->abspos = $this->filepos + $endpos;            $this->curpos = $endpos;        }        if($endpos > $this->curpos && !is_null($this->fd)) {            fseek($this->fd, $this->curpos, SEEK_SET);            $this->avail = $endpos;        }        return $this->abspos;    }    public function predictSize(): int    {        return max($this->reqlen, $this->avail);    }    public function readMore(VHubServerHTTPRequest $httpReq, int $maxlen): string    {        // switch to next file if needed        if($this->curpos >= NOTIF_FILE_ENDSIZE) {            if(!is_null($this->fd)) {                fclose($this->fd);            }            $this->filepos = $this->abspos;            $this->curpos = 0;            $this->openForRead($httpReq, -1);        }        // make sure the log file has already been created        if(is_null($this->fd)) {            return "";        }        // read as much as permitted from the current notification file        if($maxlen > 0) {            $rsize = $maxlen;        } else {            $rsize = NOTIF_FILE_ENDSIZE;        }        $result = fread($this->fd, $rsize);        $rsize = strlen($result);        if($rsize == 0) {            return $result;        }        if($result[$rsize-1] != "\n") {            // make sure we stop on a complete notification            while ($rsize > 0 && $result[$rsize - 1] != "\n") {                $rsize--;            }            $result = substr($result, 0, $rsize);            $this->curpos += $rsize;            fseek($this->fd, $this->curpos, SEEK_SET);            if($maxlen > 0) {                // pad reply to the full requested size to force a flush and get a new buffer                $result .= str_repeat("\n", $maxlen - $rsize);            }        } else {            $this->curpos += $rsize;        }        $this->abspos += $rsize;        return $result;    }    public function openForAppend(VHubServerHTTPRequest $httpReq): int    {        if(!$this->openNotFile($httpReq, 'a', false)) {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 1, 'Fail to open for append '.$this->notfile);            $endpos = 0;        } else {            fseek($this->fd, 0, SEEK_END);            $endpos = ftell($this->fd);        }        $this->abspos = $this->filepos + $endpos;        $this->curpos = $endpos;        return $this->abspos;    }    public function close(VHubServerHTTPRequest $httpReq)    {        if(!is_null($this->fd)) {            fclose($this->fd);        }    }    protected function decodeTimestamp(array $report, float &$duration): float    {        $time = $report[0] + 0x100 * $report[1] + 0x10000 * $report[2] + 0x1000000 * $report[3];        $ms = $report[4] * 4;        if (sizeof($report) > 5) {            $Byte = $report[5];            $ms += $Byte >> 6;            $duration_ms = $report[6];            $duration_ms += ($Byte & 0xf) * 0x100;            if ($Byte & 0x10) {                $duration = $duration_ms;            } else {                $duration = $duration_ms / 1000.0;            }        } else {            $duration = 0.0;        }        return $time + $ms / 1000.0;    }    public function appendNotif(VHubServerHTTPRequest $httpReq, string $notif)    {        // switch to next file if needed        if($this->curpos >= NOTIF_FILE_ENDSIZE) {            fclose($this->fd);            $this->filepos = $this->abspos;            $this->curpos = 0;            $this->openForAppend($httpReq, );        }        fwrite($this->fd, $notif."\n");    }    public function appendModuleNotification(VHubServerHTTPRequest $httpReq, array $wpVal)    {        $serial = $wpVal['serialNumber'];        $devYdxA = chr(65+$wpVal['index']);        $this->appendNotif($httpReq, NOTIF_NAME.$serial.','.$wpVal['logicalName'].','.$wpVal['beacon'].','.$devYdxA);    }    public function appendModuleArrivalNotifications(VHubServerHTTPRequest $httpReq, string $cloudSerial, array $wpVal)    {        $serial = $wpVal['serialNumber'];        $this->appendNotif($httpReq, NOTIF_CHILD.$cloudSerial.','.$serial.',1');        $this->appendModuleNotification($httpReq, $wpVal);        $this->appendNotif($httpReq, NOTIF_PRODINFO.$serial.','.sprintf('%04x', $wpVal['productId']));    }    public function appendModuleRemovalNotifications(VHubServerHTTPRequest $httpReq, string $cloudSerial, string $serial)    {        $this->appendNotif($httpReq, NOTIF_CHILD.$cloudSerial.','.$serial.',0');    }    public function appendFunctionNameNotification(VHubServerHTTPRequest $httpReq, array $ypVal)    {        $hwidParts = explode('.', $ypVal['hardwareId']);        $this->appendNotif($httpReq, NOTIF_FUNCNAMEYDX.$hwidParts[0].','.$hwidParts[1].','.$ypVal['logicalName'].','.$ypVal['index'].','.$ypVal['baseType']);    }    public function appendFunctionValNotification(VHubServerHTTPRequest $httpReq, array $ypVal)    {        $hwidParts = explode('.', $ypVal['hardwareId']);        $serial = $hwidParts[0];        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);        if($devydx < 0) return;        $funydx = $ypVal['index'];        $devYdxA = chr(65+($devydx & 63));        $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));        $this->appendNotif($httpReq, NOTIF_FUNCVALYDX.$devYdxA.$funYdxA.$ypVal['advertisedValue']);        // FIXME: Make sure no buffer overflow can happen in API in since some advertiseValue        //        should actually have been advertised using V2 notifications (6 bytes, etc)    }    public function handleTrueTimedReportNotification(VHubServerHTTPRequest $httpReq, string $serial, array $rawReports)    {        VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Processing timed reports for {$serial}: ".sizeof($rawReports)." records");        // 1. Forward timed reports in text mode        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);        if($devydx < 0) return;        foreach($rawReports as $funydx => $rawReport) {            $devYdxA = chr(65+($devydx & 63));            $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));            $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;            for($i = 0; $i < sizeof($rawReport); $i++) {                $msg .= sprintf('%02x', $rawReport[$i]);            }            $this->appendNotif($httpReq, $msg);        }        // 2. Add data to the dataLogger        $datalogger = YDataLogger::FindDataLogger("{$serial}.dataLogger");        $emulogger = $datalogger->get_userData();        if(is_null($emulogger)) {            $emulogger = new DataLogger($this->server, $serial);            $datalogger->set_userData($emulogger);        }        $timestamp = 0;        $duration = 0;        $newReports = [];        $module = YModule::FindModule($serial);        foreach($rawReports as $funydx => $rawReport) {            if($funydx == 15) {                $timestamp = $this->decodeTimestamp($rawReport, $duration);                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "TimedReport for {$serial}: stamp={$timestamp}, duration={$duration}");            } else if($timestamp) {                $functionId = $module->functionId($funydx);                $sensor = YSensor::FindSensor("{$serial}.{$functionId}");                $unit = $sensor->get_unit();                $freqStr = $sensor->get_reportFrequency();                if($freqStr == 'OFF') continue;                $freq = new DataFrequency($freqStr);                array_unshift($rawReport, 2); // prepend Timed Report V2 signature                $measure = $sensor->_decodeTimedReport($timestamp, $duration, $rawReport);                $newReports[$functionId] = [ 'sensor' => $sensor, 'measure' => $measure, 'unit' => $unit, 'freq' => $freq ];            }        }        if(sizeof($newReports) > 0) {            $emulogger->appendMeasures($httpReq, $newReports);        }    }    public function appendEmulatedTimedReportNotification(VHubServerHTTPRequest $httpReq, string $serial, array $reports)    {        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);        if($devydx < 0) return;        $timestamp = $reports[array_key_first($reports)]['measure']->get_startTimeUTC();        $funydx = 15; // special funYdx for the timestamp        $devYdxA = chr(65+($devydx & 127));        $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));        $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;        for($i = 0; $i < 4; $i++) {            $msg .= sprintf('%02x', $timestamp & 0xff);            $timestamp >>= 8;        }        $this->appendNotif($httpReq, $msg.'0003e8');        foreach($reports as $functionId => $report) {            $measure = $report['measure'];            $value = $measure->get_averageValue();            if(is_nan($value)) continue;            $report = round($value * 1000);            $hardwareId = $serial.'.'.$functionId;            $funydx = $this->server->apiroot->funYdxByHwId[$hardwareId];            $funYdxA = chr(48+$funydx+(($devydx & 128) ? 64 : 0));            $msg = NOTIF_TIMEV2YDX.$devYdxA.$funYdxA;            while(true) {                $lo = $report & 0xff;                $msg .= sprintf('%02x', $lo);                $report >>= 8;                if($report >= 0) {                    if(($lo & 0x80)==0 && $report == 0) break;                } else {                    if(($lo & 0x80)!=0 && $report == -1) break;                }            }            $this->appendNotif($httpReq, $msg);        }    }    public function appendDeviceLogNotification(VHubServerHTTPRequest $httpReq, string $serial)    {        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);        if($devydx < 0) return;        $devYdxA = chr(65+($devydx & 63));        $devYdxB = chr(48+(($devydx & 128) ? 64 : 0));        $this->appendNotif($httpReq, NOTIF_LOG.$devYdxA.$devYdxB);    }    public function appendConfigChangeNotification(VHubServerHTTPRequest $httpReq, string $serial)    {        $devydx = $this->server->apiroot->cloudConf->getDevYdx($serial);        if($devydx < 0) return;        $devYdxA = chr(65+($devydx & 63));        $devYdxB = chr(48+(($devydx & 128) ? 64 : 0));        $this->appendNotif($httpReq, NOTIF_CONFCHGYDX.$devYdxA.$devYdxB);    }}
const TARHEADER_PATH_OFS = 0;const TARHEADER_MODESTR_OFS = 100;const TARHEADER_UIDSTR_OFS = 108;const TARHEADER_GIDSTR_OFS = 116;const TARHEADER_SIZESTR_OFS = 124;const TARHEADER_UNIXTIMESTR_OFS = 136;const TARHEADER_CHECKSUMSTR_OFS = 148;const TARHEADER_TYPEFLAG_OFS = 156;const TARHEADER_LINKNAME_OFS = 157;const TARHEADER_MAGIC_OFS = 257;const TARHEADER_MAGICVER_OFS = 263;const TARHEADER_PAD_OFS = 265;const TAROP_LOAD_FILE = 0;      // first functions require shared Read-only accessconst TAROP_LIST_FILES = 1;const TAROP_WORKON_FILES = 2;   // must stay the first TAR op requiring Read-write accessconst TAROP_UPDATE_FILE = 3;    // must stay the first TAR op requiring Read-write access and causing file rewriteconst TAROP_REPLACE_FILE = 4;const TAROP_DELETE_FILE = 5;function decodeUint(string $buf, int $ofs, int $size): float{    $res = 0;    for($i = $size-1; $i >= 0; $i--) {        $res = ($res << 8) + ord($buf[$ofs+$i]);    }    return $res;}function decodeFloat(string $buf, int $ofs, bool $flipBit): float{    $intVal = ord($buf[$ofs]) + 0x100*ord($buf[$ofs+1]) + 0x10000*ord($buf[$ofs+2]) + 0x1000000*ord($buf[$ofs+3]);    if($flipBit) {        if($intVal == 0xffffffff) {            return NAN;        }        $intVal ^= 0x80000000;    }    if($intVal > 0x7fffffff) {        $intVal -= 0x100000000;    }    return $intVal / 1000.0;}function encodeUint(int $value, int $size): string{    $data = chr($value & 0xff);    for($i = 1; $i < $size; $i++) {        $value = $value >> 8;        $data .= chr($value & 0xff);    }    return $data;}function encodeFloat(float $value, bool $flipBit): string{    if($flipBit) {        if(is_nan($value)) {            $intVal = 0xffffffff;        } else {            $intVal = intval(round($value * 1000));            $intVal ^= 0x80000000;        }    } else {        $intVal = intval(round($value * 1000));    }    $intVal &= 0xffffffff;    return chr($intVal & 0xff).chr(($intVal >> 8) & 0xff)        .chr(($intVal >> 16) & 0xff).chr(($intVal >> 24) & 0xff);}function parseOctal(string $buffer, int $ofs, int $maxlen): int{    for($len = 0; $len < $maxlen; $len++) {        if(ord($buffer[$ofs+$len]) == 0) break;    }    $octalStr = substr($buffer, $ofs, $len);    return intval(base_convert($octalStr, 8, 10));}class TarObject{    public $path;    public $header;    public $content;    public $contentSize;    public $storageSize;    public $modifTime;    public $tarOffset;    public $crc;    public $gzipEncoded;    public function __construct(VHubServerHTTPRequest $httpReq, int $tarOffset, int $fileSize, string $header)    {        $headerlen = strlen($header);        $maxpathlen = min($headerlen, 99);        for($pathlen = 0; $pathlen < $maxpathlen; $pathlen++) {            if(ord($header[$pathlen]) == 0) break;        }        $this->path = substr($header, 0, $pathlen);        $this->contentSize = $fileSize;        $this->storageSize = ($fileSize + 511) & ~0x1ff;        $this->gzipEncoded = false;        if($headerlen >= 256) {            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Found ".$this->path." (size=".$this->contentSize.")");            $this->header = $header;            $this->modifTime = parseOctal($header, TARHEADER_UNIXTIMESTR_OFS, 12);        } else {            $this->header = str_repeat(chr(0), 512);            $this->modifTime = time();            $this->safecopyz($this->path, TARHEADER_PATH_OFS, TARHEADER_MODESTR_OFS);            $this->safecopyz('0100777', TARHEADER_MODESTR_OFS, TARHEADER_UIDSTR_OFS);            $this->safecopyz('0000000', TARHEADER_UIDSTR_OFS, TARHEADER_GIDSTR_OFS);            $this->safecopyz('0000000', TARHEADER_GIDSTR_OFS, TARHEADER_SIZESTR_OFS);            $this->header[TARHEADER_TYPEFLAG_OFS] = '0';            $this->safecopyz('ustar', TARHEADER_MAGIC_OFS, TARHEADER_MAGICVER_OFS);            $this->header[TARHEADER_MAGICVER_OFS] = '0';            $this->header[TARHEADER_MAGICVER_OFS+1] = '0';        }        $this->tarOffset = $tarOffset;    }    public function u32toOctal(int $number, int $headerOffset, int $ndigits)    {        $octal = base_convert(strval($number), 10, 8);        $octal = str_repeat('0', $ndigits-strlen($octal)).$octal;        for($i = 0; $i < $ndigits; $i++) {            $this->header[$headerOffset+$i] = $octal[$i];        }        $this->header[$headerOffset+$i] = chr(0);    }    public function memset(string $char, int $headerOffset, int $rep)    {        for($i = 0; $i < $rep; $i++) {            $this->header[$headerOffset+$i] = $char[0];        }    }    public function safecopyz(string $data, int $headerOffset, int $endOffset)    {        $len = strlen($data);        if($headerOffset + $len >= $endOffset) {            $len = $endOffset - $headerOffset - 1;        }        for($i = 0; $i < $len; $i++) {            $this->header[$headerOffset+$i] = $data[$i];        }        $this->header[$headerOffset+$len] = chr(0);    }    public function updateTarHeader()    {        $this->u32toOctal($this->contentSize, TARHEADER_SIZESTR_OFS, 11);        $this->u32toOctal($this->modifTime, TARHEADER_UNIXTIMESTR_OFS, 11);        $this->memset(' ', TARHEADER_CHECKSUMSTR_OFS, 8);        $checksum = 0;        for ($i = 0; $i < 512; $i++) {            $checksum += ord($this->header[$i]);        }        $this->u32toOctal($checksum, TARHEADER_CHECKSUMSTR_OFS, 7);    }}class TarFile{    protected $server;    protected $tarfile;    protected $blankbuf;    protected $userFiles;    protected  $workfd;    protected $tarfilesize;    public function __construct(VHubServer $parent, string $tarname)    {        $this->server = $parent;        $this->tarfile = $tarname;        $this->blankbuf = str_repeat(chr(0), 1024);        $this->userFiles = [];        $this->workfd = null;        $this->tarfilesize = 0;    }    public function formatTarFile(VHubServerHTTPRequest $httpReq)    {        $fp = $this->server->frewrite($httpReq, $this->tarfile);        fwrite($fp, $this->blankbuf, 1024);        $this->server->fclose($httpReq, $fp, $this->tarfile);    }    public function searchTarFile(VHubServerHTTPRequest $httpReq, string $path): ?TarObject    {        $obj = $this->processTarFile($httpReq, $path, TAROP_LOAD_FILE);        return $obj;    }    public function knownFile(string $path): ?TarObject    {        foreach($this->userFiles as $ufile) {            if($ufile->path == $path) return $ufile;        }        return null;    }    public function knownFilesCount(): int    {        return sizeof($this->userFiles);    }    public function knownFilesMatching(string $pattern): array    {        $res = [];        foreach($this->userFiles as $ufile) {            if(fnmatch($pattern, $ufile->path, 0)) {                $res[] = $ufile;            }        }        return $res;    }    public function tarSize(): int    {        if($this->tarfilesize > 0) {            return $this->tarfilesize;        }        if (!$this->server->fexists($this->tarfile)) {            return 0;        }        return $this->server->filesize($this->tarfile);    }    public function processTarFile(VHubServerHTTPRequest $httpReq, string $targetPath, int $operation, string $newContent = '')    {        VHubServer::Log($httpReq, LOG_TARFILE, 5, "processTarFile ".$this->tarfile." for ".$targetPath.", op=".$operation);        $res = ($operation == TAROP_LIST_FILES || $operation == TAROP_WORKON_FILES ? [] : null);        if(!$this->server->fexists($this->tarfile)) {            VHubServer::Log($httpReq, LOG_TARFILE, 3, "User container file does not yet exist ({$this->tarfile})");            $this->formatTarFile($httpReq);            return $res;        }        if($operation < TAROP_WORKON_FILES) {            // non-exclusive access is required            $fp = $this->server->fopen_ro($httpReq, $this->tarfile);            $newfile = null;        } else {            // exclusive read-write access for update            if($operation == TAROP_REPLACE_FILE) {                $names = explode('|', $targetPath);                $targetPath = $names[0];                $newPath = $names[1];                $operation = TAROP_UPDATE_FILE;            } else {                $newPath = $targetPath;            }            if($operation == TAROP_UPDATE_FILE) {                $newfile = new TarObject($httpReq, -1, strlen($newContent), $newPath);                $newfile->content = $newContent;                $newfile->crc = crc32($newfile->content);            } else {                $newfile = null;            }            $fp = $this->server->fopen_rw($httpReq, $this->tarfile);        }        $rewriteFrom = -1;        $this->userFiles = [];        $tarOffset = 0;        while(($rec = fread($fp, 512)) !== false) {            // end of file is marked by a zero block            if (ord($rec[0]) == 0) {                if($operation >= TAROP_UPDATE_FILE) {                    fseek($fp, $tarOffset, SEEK_SET); // rewind prior to zero block                }                break;            }            // skip over directories silently            if ($rec[TARHEADER_TYPEFLAG_OFS] == '5') {                $tarOffset += 512;                continue;            }            // make sure this is a plain file            if ($rec[TARHEADER_TYPEFLAG_OFS] != 0 && $rec[TARHEADER_TYPEFLAG_OFS] != '0') {                VHubServer::Log($httpReq, LOG_TARFILE, 2, "Unexpected record type in .tar file header at {$tarOffset}, ignoring end of file");                break;            }            // verify checksum to make sure we are not out of sync            $checkstr = substr($rec, TARHEADER_CHECKSUMSTR_OFS, 8);            $checksum = parseOctal($rec, TARHEADER_CHECKSUMSTR_OFS, 8);            for($i = 0; $i < 8; $i++) {                $rec[TARHEADER_CHECKSUMSTR_OFS+$i] = ' ';            }            $checkcheck = 0;            for ($i = 0; $i < 512; $i++) {                $checkcheck += ord($rec[$i]);            }            //VHubServer::Log($httpReq, LOG_TARFILE, 5, "Checksums: $checksum vs $checkcheck");            if ($checksum != $checkcheck) {                VHubServer::Log($httpReq, LOG_TARFILE, 2, "Checksum error in .tar file header at {$tarOffset}, ignoring end of file");                break;            }            for($i = 0; $i < 8; $i++) {                $rec[TARHEADER_CHECKSUMSTR_OFS+$i] = $checkstr[$i];            }            // make sure the file size makes sense (not more than half the "flash" size)            $fsize = parseOctal($rec, TARHEADER_SIZESTR_OFS, 12);            if ($fsize >= USERFILE_MAX_SIZE) {                VHubServer::Log($httpReq, LOG_TARFILE, 2, "File in .tar file at {$tarOffset} is too large, ignoring end of file");                break;            }            // all checks OK, we can now load the file into our list            $obj = new TarObject($httpReq, $tarOffset, $fsize, $rec);            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Tar object at {$tarOffset}: {$obj->path}, size={$fsize} ({$obj->storageSize})");            if($obj->path == $targetPath || ($obj->path == $targetPath.'.gz' && $operation == TAROP_LOAD_FILE)) {                // this is the target path (load or update operation)                if ($operation == TAROP_LOAD_FILE) {                    // load the complete file                    $obj->content = fread($fp, $obj->contentSize);                    $obj->crc = crc32($obj->content);                    $obj->gzipEncoded = ($obj->path == $targetPath.'.gz');                    if($obj->storageSize > $obj->contentSize) {                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);                    }                    $res = $obj;                } else if($operation == TAROP_UPDATE_FILE) {                    // must update this file                    if ($obj->storageSize == $newfile->storageSize) {                        // same storage size, update on the fly                        VHubServer::Log($httpReq, LOG_TARFILE, 5, "Same storage size, updating on the fly");                        $obj = $newfile;                        $obj->tarOffset = $tarOffset;                        $obj->updateTarHeader();                        fseek($fp, $tarOffset, SEEK_SET); // rewind to header                        fwrite($fp, $obj->header);                        fwrite($fp, $obj->content);                        if($obj->storageSize > $obj->contentSize) {                            fwrite($fp, $this->blankbuf, $obj->storageSize - $obj->contentSize);                        }                        $res = $obj;                        $newfile = null;                    } else {                        // different size, prepare to move file to the end (skip over current content)                        VHubServer::Log($httpReq, LOG_TARFILE, 4, "New version of {$obj->path} has a different storage size, must rewrite tar file from $tarOffset");                        $rewriteFrom = sizeof($this->userFiles);                        fseek($fp, $obj->storageSize, SEEK_CUR);                        continue;                    }                } else if($operation == TAROP_DELETE_FILE) {                    // must remove this file (skip over current content)                    VHubServer::Log($httpReq, LOG_TARFILE, 4, "Deleting {$obj->path}, must rewrite tar file from $tarOffset");                    $rewriteFrom = sizeof($this->userFiles);                    fseek($fp, $obj->storageSize, SEEK_CUR);                    continue;                } else if($operation == TAROP_LIST_FILES) {                    // compute CRC of all files matching targetpath pattern                    $content = fread($fp, $obj->contentSize);                    if($obj->storageSize > $obj->contentSize) {                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);                    }                    $obj->crc = crc32($content);                    $res[] = $obj;                }            } else if($rewriteFrom >= 0) {                // about to move a file to the end, load remaining content                $obj->tarOffset = $tarOffset;                $obj->content = fread($fp, $obj->contentSize);                if($obj->storageSize > $obj->contentSize) {                    fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);                }            } else if($operation == TAROP_WORKON_FILES) {                // compute CRC of all files matching targetpath pattern                if(fnmatch($targetPath, $obj->path, 0)) {                    $res[] = $obj;                }                // skip over content                fseek($fp, $obj->storageSize, SEEK_CUR);            } else if($operation == TAROP_LIST_FILES) {                // compute CRC of all files matching targetpath pattern                if(fnmatch($targetPath, $obj->path, 0)) {                    $content = fread($fp, $obj->contentSize);                    if($obj->storageSize > $obj->contentSize) {                        fseek($fp, $obj->storageSize - $obj->contentSize, SEEK_CUR);                    }                    $obj->crc = crc32($content);                    $res[] = $obj;                } else {                    // skip over content                    fseek($fp, $obj->storageSize, SEEK_CUR);                }            } else {                // skip over content                fseek($fp, $obj->storageSize, SEEK_CUR);            }            $this->userFiles[] = $obj;            // prepare to handle next record in .tar file            $tarOffset += 512 + $obj->storageSize;        }        if($operation >= TAROP_UPDATE_FILE) {            // append updated file at the end if not updated on the file            if($operation == TAROP_UPDATE_FILE && !is_null($newfile)) {                if($tarOffset + $newfile->storageSize > FILES_MAX_SIZE) {                    VHubServer::Log($httpReq, LOG_TARFILE, 2, "TAR file is too big to add a new file");                } else {                    if ($rewriteFrom < 0) {                        $rewriteFrom = sizeof($this->userFiles);                    }                    $newfile->tarOffset = $tarOffset;                    $newfile->updateTarHeader();                    $this->userFiles[] = $newfile;                    $res = $newfile;                }            }            // rewrite part of the archive if a file is beeing moved            if ($rewriteFrom >= 0) {                if(isset($this->userFiles[$rewriteFrom])) {                    // rewrite archive from first moved file                    $obj = $this->userFiles[$rewriteFrom];                    fseek($fp, $obj->tarOffset, SEEK_SET);                    VHubServer::Log($httpReq, LOG_TARFILE, 5, "Rewriting tar file starting at {$obj->path} at {$obj->tarOffset}");                    for ($i = $rewriteFrom; $i < sizeof($this->userFiles); $i++) {                        $obj = $this->userFiles[$i];                        fwrite($fp, $obj->header);                        fwrite($fp, $obj->content);                        if($obj->storageSize > $obj->contentSize) {                            fwrite($fp, $this->blankbuf, $obj->storageSize - $obj->contentSize);                        }                    }                }            }            // append terminal block in any case            fwrite($fp, $this->blankbuf, 1024);            // truncate file at current position            ftruncate($fp, ftell($fp));        }        if($operation == TAROP_WORKON_FILES) {            $this->workfd = $fp;        } else {            $this->server->fclose($httpReq, $fp, $this->tarfile);        }        return $res;    }    public function tarWorkRead(TarObject $obj, int $relofs, int $size): string    {        if($relofs >= $obj->contentSize) {            return '';        }        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);        if($relofs + $size > $obj->contentSize) {            $size = $obj->contentSize - $relofs;        }        return fread($this->workfd, $size);    }    public function tarWorkReadUint(TarObject $obj, int $relofs, int $size): int    {        if($relofs >= $obj->contentSize) {            return -1;        }        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);        if($relofs + $size > $obj->contentSize) {            $size = $obj->contentSize - $relofs;        }        $res = 0;        $data = fread($this->workfd, $size);        for($i = $size-1; $i >= 0; $i--) {            $res = ($res << 8) + ord($data[$i]);        }        return $res;    }    public function tarWorkWrite(TarObject $obj, int $relofs, string $data)    {        if($relofs >= $obj->contentSize) {            return;        }        $absofs = $obj->tarOffset + 512 + $relofs;        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);        $size = strlen($data);        if($relofs + $size > $obj->contentSize) {            $size = $obj->contentSize - $relofs;        }        try {            fwrite($this->workfd, $data, $size);        } catch(Throwable $err) {            VHubServer::Log($httpReq, LOG_DATALOGGER, 2, "Error writing to file {$this->workfd} in tarWorkWrite: ".$err->getMessage());            VHubServer::Log($httpReq, LOG_DATALOGGER, 2, "   while writing {$size}/".strlen($data)." bytes at offset {$absofs} ({$relofs})");        }    }    public function tarWorkWriteUint(TarObject $obj, int $relofs, int $value, int $size)    {        if($relofs >= $obj->contentSize) {            return;        }        fseek($this->workfd, $obj->tarOffset + 512 + $relofs, SEEK_SET);        if($relofs + $size > $obj->contentSize) {            $size = $obj->contentSize - $relofs;        }        $data = chr($value & 0xff);        for($i = 1; $i < $size; $i++) {            $value = $value >> 8;            $data .= chr($value & 0xff);        }        fwrite($this->workfd, $data, $size);    }    public function tarWorkDone(VHubServerHTTPRequest $httpReq)    {        if(!is_null($this->workfd)) {            $this->server->fclose($httpReq, $this->workfd, $this->tarfile);        }    }}class YfsObject{    public $path;    public $header;    public $content;    public $contentSize;    public $crc;    public $gzipEncoded;    public function __construct(string $header,  $fd)    {        $nameLen = ord($header[8]);        $this->path = substr($header, 9, $nameLen);        $this->header = $header;        $this->contentSize = ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]);        $this->gzipEncoded = ((ord($header[3]) & 0x80) != 0);        $this->crc = ord($header[4]) + 0x100*ord($header[5]) + 0x10000*ord($header[6]) + 0x1000000*ord($header[7]);        if($this->contentSize > 0) {            $prefix = ($this->gzipEncoded ? "\x1f\x8b\x08\x00\x00\x00\x00\x00" : '');            $this->content = $prefix.fread($fd, $this->contentSize);        } else {            $this->content = '';        }    }}class YfsFile{    protected $server;    protected $yfspath;    protected  $fd;    protected $nFiles;    protected $pageSize;    protected $index;    public function __construct(VHubServer $parent, string $yfspath)    {        $this->server = $parent;        $this->yfspath = $yfspath;        $this->fd = null;        $this->nFiles = 0;        $this->pageSize = 0;        $this->index = [];    }    protected function loadIndex(VHubServerHTTPRequest $httpReq)    {        if(substr($this->yfspath, 0, 5) == 'data:') {            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Open YFS image from memory, size=".(strlen($this->yfspath)-5));            $this->fd = fopen('php://memory', 'r+b');            fwrite($this->fd, substr($this->yfspath, 5));            rewind($this->fd);        } else {            VHubServer::Log($httpReq, LOG_TARFILE, 5, "Open YFS image from disk, path len=".strlen($this->yfspath));            $this->fd = fopen($this->yfspath, 'rb');        }        if($this->fd === false) {            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Cannot open YFS image");            return;        }        $header = fread($this->fd, 12);        if(substr($header, 0, 4) != 'YFS3') {            VHubServer::Log($httpReq, LOG_TARFILE, 2, "YFS image is corrupt");            fclose($this->fd);            $this->fd = false;            return;        }        $nfiles = ord($header[10]) + 256 * ord($header[11]);        $tocBuff = fread($this->fd, 6 * $nfiles);        // determine the YFS page size by looking at the length of the first file wrapping page zero        $prevPage = 0;        $prevOfs = ftell($this->fd);        for($i = 0; $i < $nfiles; $i++) {            $ofs = 2*$nfiles + 4*$i;            $dataPage = ord($tocBuff[$ofs+0]) + 256 * ord($tocBuff[$ofs+1]);            $dataOfs = ord($tocBuff[$ofs+2]) + 256 * ord($tocBuff[$ofs+3]);            if($dataPage > 0) break;            $prevPage = $dataPage;            $prevOfs = $dataOfs;        }        // read header of previous file        fseek($this->fd, $prevOfs, SEEK_SET);        $header = fread($this->fd, 10);        $pathlen = ord($header[8]);        $hdrlen = ($pathlen + 10) & ~1;        $contentStorage = (ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]) + 1) & ~1;        $pageSize = ($prevOfs + $hdrlen + $contentStorage - $dataOfs) / ($dataPage - $prevPage);        $this->pageSize = intVal(round($pageSize/2)*2); // round to 2, just in case        // now parse the complete index        for($i = 0; $i < $nfiles; $i++) {            $nameHash = ord($tocBuff[2*$i]) + 256 * ord($tocBuff[2*$i+1]);            $ofs = 2*$nfiles + 4*$i;            $dataPage = ord($tocBuff[$ofs+0]) + 256 * ord($tocBuff[$ofs+1]);            $dataOfs = ord($tocBuff[$ofs+2]) + 256 * ord($tocBuff[$ofs+3]) + $this->pageSize * $dataPage;            if(isset($this->index[$nameHash])) {                $this->index[$nameHash][] = $dataOfs;            } else {                $this->index[$nameHash] = [ $dataOfs ];            }        }        $this->nFiles = $nfiles;    }    protected function nameHash(string $name): int    {        $hash = 0;        $nameLen = strlen($name);        for($i = 0; $i < $nameLen; $i++) {            $hash = (($hash << 1) + ord($name[$i])) & 0xffff;        }        if($hash == 0xffff) {            // 0xffff is a reserved value            $hash--;        }        return $hash;    }    public function search(VHubServerHTTPRequest $httpReq, string $path): ?YfsObject    {        if(is_null($this->fd)) {            // preload index            $this->loadIndex($httpReq);        }        if($this->fd === false) {            // failed to preload index, fail every file            return null;        }        // compute hash, lookup in index, seek in file at dataOfs, verify filename        $hash = $this->nameHash($path);        if(!isset($this->index[$hash])) {            return null;        }        $pathlen = strlen($path);        $candidates = $this->index[$hash];        foreach($candidates as $dataOfs) {            // load file header            $hdrlen = ($pathlen + 10) & ~1;            fseek($this->fd, $dataOfs, SEEK_SET);            $filehdr = fread($this->fd, $hdrlen);            // verify that file name len matches            if(ord($filehdr[8]) != $pathlen) {                continue;            }            // verify that file name matches            if(substr($filehdr, 9, $pathlen) != $path) {                continue;            }            return new YfsObject($filehdr, $this->fd);        }        return null;    }    public function loadAll(VHubServerHTTPRequest $httpReq): array    {        if(is_null($this->fd)) {            // preload index            $this->loadIndex($httpReq);        }        // default to no file found if we failed to load index        $result = [];        if($this->fd === false) {            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Failed to load YFS file index");        } else {            foreach($this->index as $hash => $filelist) {                foreach ($filelist as $dataOfs) {                    // load file header                    fseek($this->fd, $dataOfs, SEEK_SET);                    $filehdr = fread($this->fd, 10);                    if(strlen($filehdr) > 8) {                        $pathlen = ord($filehdr[8]);                        VHubServer::Log($httpReq, LOG_TARFILE, 4, "YFS object at {$dataOfs}: size={$pathlen}");                        $filehdr .= fread($this->fd, $pathlen & ~1);                        $result[] = new YfsObject($filehdr, $this->fd);                    } else {                        VHubServer::Log($httpReq, LOG_TARFILE, 2, "YFS object at {$dataOfs}: bad header");                    }                }            }        }        return $result;    }}class FileServer{    protected $server;    protected $yfsFiles;    protected $ownFiles;    protected $deviceFiles;    public $specialUploadFiles = [        'txdata', 'logs.txt', 'rgb:', 'hsl:', 'sendSMS',        'layer0', 'layer1', 'layer2', 'layer3', 'layer4', 'layer5'    ];    public $specialDownloadFiles = [        'display.gif', 'rgb.bin'    ];    public function __construct(VHubServer $parent)    {        $this->server = $parent;        $this->yfsFiles = new YfsFile($parent, UIFILE);        $this->ownFiles = new TarFile($parent, 'VHUB4WEB-files.tar');        $this->deviceFiles = [];    }    public function sendContentHeader(VHubServerHTTPRequest $httpReq, string $extension)    {        switch(strtolower($extension)) {            case 'json':            case 'jzon':                $mimetype = 'application/json; charset=iso-8859-1';                break;            case 'html':            case '':                $mimetype = 'text/html';                break;            case 'js':                $mimetype = 'application/javascript';                break;            case 'xml':                $mimetype = 'text/xml';                break;            case 'txt':                $mimetype = 'text/plain';                break;            case 'png':                $mimetype = 'image/png';                break;            case 'gif':                $mimetype = 'image/gif';                break;            case 'css':                $mimetype = 'text/css';                break;            case 'jpeg':            case 'jpg':                $mimetype = 'image/jpeg';                break;            case 'svg':                $mimetype = 'image/svg+xml';                break;            case 'byn':            case 'bin':                $mimetype = 'text/plain; charset=x-user-defined';                break;            default:                $mimetype = 'application/'.$extension;        }        $httpReq->putHeader('Content-Type: '.$mimetype);    }    public function accessDeviceFiles(VHubServerHTTPRequest $httpReq, string $serial): TarFile    {        if(!isset($this->deviceFiles[$serial])) {            $this->deviceFiles[$serial] = new TarFile($this->server, $serial.'.tar');        }        return $this->deviceFiles[$serial];    }    public function loadDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile): ?string    {        $tarfile = $this->accessDeviceFiles($httpReq, $serial);        $obj = $tarfile->searchTarFile($httpReq, $subfile);        if(is_null($obj)) {            return null;        }        return $obj->content;    }    public function isKnownDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile): bool    {        $tarfile = $this->accessDeviceFiles($httpReq, $serial);        $existing = $tarfile->knownFile($subfile);        if(is_null($existing)) {            $existing = $tarfile->knownFile($subfile.'.gz');            if(is_null($existing)) {                return false;            }        }        return true;    }    public function saveDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile, string $content)    {        $tarfile = $this->accessDeviceFiles($httpReq, $serial);        if(str_ends_with($subfile, '.json') || str_ends_with($subfile, '.trace')) {            $existing = $tarfile->knownFile($subfile);            if(is_null($existing)) {                // Reserve extra space for future growth                $padsize = (str_contains($serial, 'HUB') ? 8192 : 1024);                $padsize += strlen($content) >> 1;            } else {                // Keep allocated size unchanged, unless growth is really needed                $padsize = $existing->storageSize - strlen($content) - 1;                if($padsize < 0) {                    $padsize = $existing->storageSize >> 1;                }            }            $content .= str_repeat(' ', $padsize);        }        if($subfile != 'api.json') {            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Archiving file {$subfile} for {$serial}");        }        $tarfile->processTarFile($httpReq, $subfile, TAROP_UPDATE_FILE, $content);    }    public function saveAllDeviceFiles(VHubServerHTTPRequest $httpReq, string $serial, string $fscontent)    {        if(substr($fscontent, 0, 2) == 'S3') {            VHubServer::Log($httpReq, LOG_TARFILE, 5, "New _FS file format found in {$serial}");            $yfs = new YfsFile($this->server, 'data:YF' . $fscontent);        } else if(substr($fscontent, 0, 3) == 'FS3') {            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Old _FS file format found in {$serial}");            $yfs = new YfsFile($this->server, 'data:Y' . $fscontent);        } else {            VHubServer::Log($httpReq, LOG_TARFILE, 2, "Bad _FS file content for {$serial}");            return;        }        $files = $yfs->loadAll($httpReq);        VHubServer::Log($httpReq, LOG_TARFILE, 5, "Number of files found: ".sizeof($files));        foreach($files as $yfsfile) {            VHubServer::Log($httpReq, LOG_TARFILE, 5, "YFS file found: {$yfsfile->path} (size={$yfsfile->contentSize})");            $savepath = 'yfs/'.$yfsfile->path;            if($yfsfile->gzipEncoded) {                $savepath .= '.gz';            }            $this->saveDeviceFile($httpReq, $serial, $savepath, $yfsfile->content);        }    }    public function filesCmd(VHubServerHTTPRequest $httpReq, string $action, string $fname)    {        $res = [];        switch($action) {            case 'dir':                $objs = $this->ownFiles->processTarFile($httpReq, $fname, TAROP_LIST_FILES);                $res = [];                foreach($objs as $obj) {                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);                    $res[] = ['name' => $obj->path, 'size' => $obj->contentSize, 'crc' => $crc];                }                break;            case 'stat':                $objs = $this->ownFiles->processTarFile($httpReq, $fname, TAROP_LIST_FILES);                if(sizeof($objs) == 0) {                    $res = ['stat' => 'absent', 'size' => 0, 'crc' => 0];                } else {                    $obj = $objs[0];                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);                    $res = ['stat' => 'present', 'size' => $obj->contentSize, 'crc' => $crc];                }                break;            case 'del':                $this->ownFiles->processTarFile($httpReq, $fname, TAROP_DELETE_FILE);                $res = ['res' => 'ok'];                break;            case 'format':                $this->ownFiles->formatTarFile($httpReq);                $res = ['res' => 'ok'];                break;        }        $this->server->apiroot->api->files->updateStats($httpReq, $this->ownFiles->knownFilesCount(), $this->ownFiles->tarSize());        $this->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($res));    }    public function deviceFilesCmd(VHubServerHTTPRequest $httpReq, string $serial, string $action, string $fname)    {        $tarfile = $this->accessDeviceFiles($httpReq, $serial);        $res = [];        switch($action) {            case 'dir':                $objs = $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_LIST_FILES);                $res = [];                foreach($objs as $obj) {                    // all results are expected to be in 'files/' subdirectory                    $devpath = $obj->path;                    if(substr($devpath, 0, 6) != 'files/') continue;                    $devpath = substr($devpath, 6);                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);                    $res[] = ['name' => $devpath, 'size' => $obj->contentSize, 'crc' => $crc];                }                break;            case 'stat':                $objs = $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_LIST_FILES);                if(sizeof($objs) == 0) {                    $res = ['stat' => 'absent', 'size' => 0, 'crc' => 0];                } else {                    $obj = $objs[0];                    $crc = ($obj->crc > 0x7fffffff ? $obj->crc - 0x100000000 : $obj->crc);                    $res = ['stat' => 'present', 'size' => $obj->contentSize, 'crc' => $crc];                }                break;            case 'del':                // schedule deletion on device                $apinode = $this->server->apiroot->bySerial->subnode($serial);                $apinode->fileList->deleteOnDevice($fname);                // remove from tarball                $tarfile->processTarFile($httpReq, 'files/'.$fname, TAROP_DELETE_FILE);                $res = ['res' => 'ok'];                break;            case 'format':                // schedule format on device                $apinode = $this->server->apiroot->bySerial->subnode($serial);                $apinode->fileList->formatOnDevice();                // remove all user files from tarball                $objs = $tarfile->processTarFile($httpReq, 'files/*', TAROP_LIST_FILES);                for($i = 0; $i < sizeof($objs); $i++) {                    $tarfile->processTarFile($httpReq, 'files/'.$objs[$i]->path, TAROP_DELETE_FILE);                }                $res = ['res' => 'ok'];                break;        }        $this->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($res));    }    public function filesUpload(VHubServerHTTPRequest $httpReq, string $path, string $content)    {        $this->ownFiles->processTarFile($httpReq, $path, TAROP_UPDATE_FILE, $content);        $this->server->apiroot->api->files->updateStats($httpReq, $this->ownFiles->knownFilesCount(), $this->ownFiles->tarSize());    }    public function deviceFilesUpload(VHubServerHTTPRequest $httpReq, string $serial, string $path, string $content)    {        // For other special upload files, put in -pending req only and exit        if(array_search($path, $this->specialUploadFiles) !== FALSE) {            $this->server->scheduleUploadOnDevice($httpReq, $serial, $path, $content);            return;        }        // Firmware update is handled in a special way        if($path == 'firmware' || $path == 'firmwareConf' || $path == 'Xfirmw') {            return;        }        // For regular user files, put content in tarball and update filelist for synchronization        $tarfile = $this->accessDeviceFiles($httpReq, $serial);        $tarfile->processTarFile($httpReq, 'files/'.$path, TAROP_UPDATE_FILE, $content);        $newfile = $tarfile->knownFile('files/'.$path);        $apinode = $this->server->apiroot->bySerial->subnode($serial);        $apinode->fileList->uploadToDevice($httpReq, $path, $newfile->contentSize, $newfile->crc);    }    public function sendFileContent(VHubServerHTTPRequest $httpReq, string $content, string $extension, ?int $crc = null)    {        if(is_null($crc)) {            $crc = crc32($content);        }        $this->sendContentHeader($httpReq, $extension);        $httpReq->putHeader('Content-Length: '.strlen($content));        $httpReq->putHeader('Cache-Control: no-cache');        $httpReq->putHeader('ETag: '.dechex($crc));        $httpReq->put($content);    }    public function sendFile(VHubServerHTTPRequest $httpReq, string $path, string $extension)    {        // if a local mount override is in place, search it first        if(defined('MOUNT_SERVER_FILES')) {            foreach(MOUNT_SERVER_FILES as $mountDir) {                $fullPath = $mountDir.'/'.$path;                if(file_exists($fullPath)) {                    $content = file_get_contents($fullPath);                    if(str_ends_with($fullPath, '.html') && strpos($content, ' rel="icon"') !== FALSE) {                        $favicon = false;                        foreach(MOUNT_SERVER_FILES as $mountDirAgain) {                            $faviconPath = $mountDirAgain . '/favicon.svg';                            if(file_exists($faviconPath)) {                                $favicon = base64_encode(file_get_contents($faviconPath));                                break;                            }                        }                        if($favicon) {                            $content = preg_replace('~(rel="icon" id="favicon" type="image/svg[+]xml" href="data:image/svg[+]xml;base64,)[^"]*~', '$1'.$favicon, $content);                        }                    }                    // use special e-tag to identify mounted file                    $crc = 0xFF00000000 + crc32($content);                    $this->sendFileContent($httpReq, $content, $extension, $crc);                    return;                }            }        }        // search in embedded UI files        $obj = $this->yfsFiles->search($httpReq, $path);        if(is_null($obj)) {            // search in user files            $obj = $this->ownFiles->searchTarFile($httpReq, $path);            if(is_null($obj)) {                // not found neither                $httpReq->putStatus(404);                Print("Sorry, the requested file ".htmlspecialchars($path)." does not exist on server");                return;            }        }        $content = $obj->content;        $crc = $obj->crc;        if($obj->gzipEncoded) {            $httpReq->putHeader('Content-Encoding: gzip');        }        $this->sendFileContent($httpReq, $content, $extension, $crc);    }    public function sendDeviceFile(VHubServerHTTPRequest $httpReq, string $serial, string $subfile, string $extension)    {        if(!isset($this->deviceFiles[$serial])) {            $this->deviceFiles[$serial] = new TarFile($this->server, $serial.'.tar');        }        $tarfile = $this->deviceFiles[$serial];        if(array_search($subfile, $this->specialDownloadFiles) !== FALSE) {            // special files are in root directory            $obj = $tarfile->searchTarFile($httpReq, $subfile);        } else {            // search for regular files in yfs/, then files/, then standard EmbeddedUI            VHubServer::Log($httpReq, LOG_TARFILE, 4, "Search for ".'yfs/'.$subfile);            $obj = $tarfile->searchTarFile($httpReq, 'yfs/'.$subfile);            if(is_null($obj)) {                VHubServer::Log($httpReq, LOG_TARFILE, 4, "Search for ".'files/'.$subfile);                $obj = $tarfile->searchTarFile($httpReq, 'files/'.$subfile);            }            if(is_null($obj)) {                // fallback to standard EmbeddedUI file if available                $subpath = $subfile;                $obj = $this->yfsFiles->search($httpReq, $subpath);            }        }        if(is_null($obj)) {            // file not found            $httpReq->putStatus(404);            Print("Sorry, the requested device file ".htmlspecialchars($subfile)." does not exist on ".htmlspecialchars($serial));            return;        }        $content = $obj->content;        $crc = $obj->crc;        if($obj->gzipEncoded) {            $httpReq->putHeader('Content-Encoding: gzip');        }        $this->sendFileContent($httpReq, $content, $extension, $crc);    }}
class DataFrequency{    public $freqStr;    public $period;    public $nb;    public $perSec;    public $perMin;    public $perHour;    protected $maxSeqRowsCache;    public function __construct( $timebase)  // string|int|float    {        $this->maxSeqRowsCache = 0;        $this->perSec = false;        $this->perMin = false;        $this->perHour = false;        if(is_string($timebase)) {            if(strlen($timebase) == 2) {                // binary representation (two bytes)                $freq = ord($timebase[0]);                $this->nb = max($freq, 1);                $period = 1.0 / $this->nb;                $unit = ord($timebase[1]) & 7;                if($unit == 2) {                    $period *= 60;                    $this->perMin = true;                    $this->freqStr = $this->nb.'/m';                } else if($unit == 4) {                    $period *= 3600;                    $this->perHour = true;                    $this->freqStr = $this->nb.'/h';                } else {                    $this->perSec = true;                    $this->freqStr = $this->nb.'/s';                }                $this->period = $period;            } else {                // device-like frequency, eg "30/m"                $pos = strpos($timebase, '/');                if($pos === False) {                    $freq = floatval($timebase);                    $this->nb = max($freq, 1);                    $this->freqStr = $this->nb.'/s';                    $this->perSec = true;                    $this->period = 1.0 / $freq;                } else {                    $this->freqStr = $timebase;                    $freq = intval(substr($timebase, 0, $pos));                    $this->nb = max($freq, 1);                    $period = 1.0 / $this->nb;                    $unit = substr($timebase, $pos+1);                    if($unit == 'm') {                        $period *= 60;                        $this->perMin = true;                    } else if($unit == 'h') {                        $period *= 3600;                        $this->perHour = true;                    } else {                        $this->perSec = true;                    }                    $this->period = $period;                }            }        } else if(is_numeric($timebase) && $timebase > 0) {            // period in seconds            $this->period = $timebase;            if($this->period <= 1) {                $freq = intval(round(1.0 / $this->period));                $this->nb = min($freq, 100);                $this->perSec = true;                $this->freqStr = $this->nb.'/s';            } elseif($this->period <= 60) {                $this->nb = intval(round(60.0 / $this->period));                $this->perMin = true;                $this->freqStr = $this->nb.'/m';            } else {                $freq = intval(round(3600.0 / $this->period));                $this->nb = max($freq, 1);                $this->perHour = true;                $this->freqStr = $this->nb.'/h';            }        }    }    public function alignTimestamp(float $timestamp): float    {        if($this->period < 1) {            $alignmentErr = fmod($timestamp, $this->period);        } else {            $timestamp = intval(round($timestamp));            $timeofday = $timestamp % 86400;            $alignmentErr = $timeofday % intval($this->period);        }        if($alignmentErr < $this->period / 2) {            $timestamp -= $alignmentErr;        } else {            $timestamp += $this->period - $alignmentErr;        }        return $timestamp;    }    public function encoded(): string    {        if($this->perHour) {            return chr($this->nb).chr(4);        } else if($this->perMin) {            return chr($this->nb).chr(2);        } else {            return chr($this->nb).chr(1);        }    }    public function maxSeqRows(): int    {        if($this->maxSeqRowsCache <= 0) {            $count = $this->nb;            $maxRecs = ($this->perSec ? 250 : 120);            // multiple of time units that make sense            // (number of hours, 5-min periods or 5-sec periods)            $timeMult = [ 12, 6, 3, 2, 1 ];            if(!$this->perHour) {                // current total is a second or a minute                for($i = 0; $i < sizeof($timeMult); $i++) {                    $better = $count*5*$timeMult[$i];                    if($better <= $maxRecs) {                        // use 12min sequences instead of 10min (far more efficient)                        if($better == 120 && $this->perMin && $this->nb == 12) {                            $better = 144;                        }                        $count = $better;                        break;                    }                }                if($i == 0 || $i >= sizeof($timeMult)) {                    if($count*3 < $maxRecs) $count *= 3;                    else if($count*2 < $maxRecs) $count *= 2;                }            } else {                if($count <= 5) {                    // up to 5 measures per hour => full day                    return $count*24;                }                for($i = 0; $i < sizeof($timeMult); $i++) {                    $better = $count*$timeMult[$i];                    if($better <= $maxRecs) {                        $count = $better;                        break;                    }                }            }            $this->maxSeqRowsCache = $count;        }        return $this->maxSeqRowsCache;    }}class DataFile{    public $startstamp;    public $stopstamp;    public $functionid;    public $unit;    public $tarObject;    public function __construct(TarObject $tarObject)    {        if(preg_match('~^datalogger/([0-9]+)-([a-zA-Z0-9]+)-(.*)-20[0-9]{2}-[0-9]{2}-[0-9]{2}.bin$~', $tarObject->path, $matches)) {            $this->startstamp = intval($matches[1]);            $this->functionid = $matches[2];            $this->unit = $matches[3];        } else {            $this->startstamp = -1;            $this->functionid = '???';            $this->unit = '';        }        $this->stopstamp = time(); // default value, will be overriden when better known        $this->tarObject = $tarObject;    }}const DATASEQ_HEADER_SIZE = 28;   // first functions require shared Read-only accessclass DataSeq{    protected $tarFile;    protected $tarObj;    protected $seqOfs;    protected $dataOfs;    protected $nextSeqStampCache;    protected $header;    protected $data;    public $runIdx;                  // offset 0-3: run number    public $utcStamp;                // offset 4-7: start UTC timestamp    public $frequency;     // offset 8-9: measure frequency    public $firstDur;                // offset 10-11: duration of 1st measure (s/ms)    public $firstMs;                 // offset 12-13: ms offset of 1st measure    public $nRows;                   // offset 14-15: number of measures (max 250)    public $avgVal;                // offset 16-19: sequence average (when complete)    public $minVal;                // offset 20-23: sequence min value (when complete)    public $maxVal;                // offset 24-27: sequence max value (when complete)    public $measures;              // one s32 (instant) or three s32 (avg/min/max) per time unit    public function __construct(TarFile $tarFile, ?TarObject $tarObj, int $seqOfs)    {        $this->tarFile = $tarFile;        $this->tarObj = $tarObj;        $this->seqOfs = $seqOfs;        $this->dataOfs = $seqOfs + DATASEQ_HEADER_SIZE;        $this->nextSeqStampCache = 0;        $this->header = '';        $this->data = '';        $this->nRows = 0;        $this->avgVal = NAN;        $this->minVal = NAN;        $this->maxVal = NAN;        $this->measures = [];    }    public function loadSeq(VHubServerHTTPRequest $httpReq, bool $withData)    {        $header = $this->tarFile->tarWorkRead($this->tarObj, $this->seqOfs, 28);        $this->header = $header;        $this->runIdx = ord($header[0]) + 0x100*ord($header[1]) + 0x10000*ord($header[2]) + 0x1000000*ord($header[3]);        $this->utcStamp = ord($header[4]) + 0x100*ord($header[5]) + 0x10000*ord($header[6]) + 0x1000000*ord($header[7]);        $this->frequency = new DataFrequency(substr($header, 8, 2));        $this->firstDur = ord($header[10]) + 0x100*ord($header[11]);        $this->firstMs = ord($header[12]) + 0x100*ord($header[13]);        $this->nRows = ord($header[14]) + 0x100*ord($header[15]);        $this->avgVal = decodeFloat($header, 16, true);        $this->minVal = decodeFloat($header, 20, false);        $this->maxVal = decodeFloat($header, 24, false);        if($withData) {            $rsize = 4 * $this->nRows;            if($this->frequency->perSec) {                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);                for($pos = 0; $pos < $rsize; $pos += 4) {                    $this->measures[] = decodeFloat($data, $pos, true);                }            } else {                $rsize *= 3;                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);                for($pos = 0; $pos < $rsize; $pos += 12) {                    $avgVal = decodeFloat($data, $pos, true);                    $this->measures[] = $avgVal;                    if(!is_nan($avgVal)) {                        $this->measures[] = decodeFloat($data, $pos+4, false);                        $this->measures[] = decodeFloat($data, $pos+8, false);                    } else {                        $this->measures[] = NAN;                        $this->measures[] = NAN;                    }                }            }            $this->data = $data;        }    }    public function storageSize(): int    {        if($this->frequency->perSec) {            return DATASEQ_HEADER_SIZE + 4 * $this->nRows;        } else {            return DATASEQ_HEADER_SIZE + 12 * $this->nRows;        }    }    public function isClosed(): bool    {        return !is_nan($this->avgVal);    }    public function getAvgMinMax(): array    {        if($this->isClosed()) {            return [ $this->avgVal, $this->minVal, $this->maxVal ];        }        $nval = 1;        if($this->frequency->perSec) {            if(sizeof($this->measures) == 0) {                // sequence not yet loaded                $rsize = 4 * $this->nRows;                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);                for($pos = 0; $pos < $rsize; $pos += 4) {                    $this->measures[] = decodeFloat($data, $pos, true);                }                $this->data = $data;            }            $value = $this->measures[0];            $sum = $value;            $minVal = $value;            $maxVal = $value;            for($i = 1; $i < $this->nRows; $i++) {                $value = $this->measures[$i];                if(!is_nan($value)) {                    $nval++;                    $sum += $value;                    if($value < $minVal) {                        $minVal = $value;                    }                    if($value > $maxVal) {                        $maxVal = $value;                    }                }            }        } else {            if(sizeof($this->measures) == 0) {                // sequence not yet loaded                $rsize = 12 * $this->nRows;                $data = $this->tarFile->tarWorkRead($this->tarObj, $this->dataOfs, $rsize);                for($pos = 0; $pos < $rsize; $pos += 12) {                    $avgVal = decodeFloat($data, $pos, true);                    $this->measures[] = $avgVal;                    if(!is_nan($avgVal)) {                        $this->measures[] = decodeFloat($data, $pos+4, false);                        $this->measures[] = decodeFloat($data, $pos+8, false);                    } else {                        $this->measures[] = NAN;                        $this->measures[] = NAN;                    }                }                $this->data = $data;            }            $sum = $this->measures[0];            $minVal = $this->measures[1];            $maxVal = $this->measures[2];            for($i = 1; $i < $this->nRows; $i++) {                $avgVal = $this->measures[3*$i];                if(!is_nan($avgVal)) {                    $nval++;                    $sum += $avgVal;                    if($this->measures[3*$i+1] < $minVal) {                        $minVal = $this->measures[3*$i+1];                    }                    if($this->measures[3*$i+2] > $maxVal) {                        $maxVal = $this->measures[3*$i+2];                    }                }            }        }        $avgVal = round(1000 * $sum / $nval) / 1000.0;        return [ $avgVal, $minVal, $maxVal ];    }    public function closeSeq(VHubServerHTTPRequest $httpReq)    {        $avgMinMax = $this->getAvgMinMax();        VHubServer::Log($httpReq, LOG_DATALOGGER, 4, 'Closing sequence, summary: avg='.$avgMinMax[0].' min='.$avgMinMax[1].' max='.$avgMinMax[2]);        $this->avgVal = $avgMinMax[0];        $this->minVal = $avgMinMax[1];        $this->maxVal = $avgMinMax[2];        $buff = encodeFloat($this->avgVal, true).            encodeFloat($this->minVal, false).            encodeFloat($this->maxVal, false);        $this->tarFile->tarWorkWrite($this->tarObj, $this->seqOfs+16, $buff);    }    // Return the timestamp of the last measure inserted in the sequence    public function lastStamp(): float    {        if($this->frequency->perSec) {            $endFirstRow = $this->utcStamp + ($this->firstMs + $this->firstDur) / 1000;        } else {            $endFirstRow = $this->utcStamp + $this->firstDur;        }        return $endFirstRow + ($this->nRows-1) * $this->frequency->period;    }    // Compute the timestamp of the next sequence to start    public function nextSeqStartStamp(): int    {        if($this->nextSeqStampCache <= 0) {            $count = $this->frequency->maxSeqRows();            $seqPeriod = intval(round($this->frequency->period * $count));            $this->nextSeqStampCache = $this->utcStamp - ($this->utcStamp % $seqPeriod) + $seqPeriod;        }        return $this->nextSeqStampCache;    }    // Setup the sequence given the first measure, initializing the header as required    // (use for creating a new sequence when no header data is available yet)    public function initialize(DataFrequency $freq, YMeasure $measure)    {        $startTime = $measure->get_startTimeUTC();        $endTime = $measure->get_endTimeUTC();        $avgVal = $measure->get_averageValue();        $minVal = $measure->get_minValue();        $maxVal = $measure->get_maxValue();        $this->runIdx = 0;        $this->frequency = $freq;        if($freq->perSec) {            $this->utcStamp = intval(floor($startTime));            $this->firstMs = intval(round(1000 * ($startTime - $this->utcStamp)));            $this->firstDur = intval(round(1000 * $freq->period));        } else {            $this->utcStamp = intval(round($startTime));            $this->firstMs = 0;            $this->firstDur = intval(round($endTime)) - $this->utcStamp;            if(is_nan($minVal)) $minVal = $avgVal;            if(is_nan($maxVal)) $maxVal = $avgVal;        }        $this->nRows = 1;        $this->header =            encodeUint($this->runIdx, 4).encodeUint($this->utcStamp, 4).$freq->encoded().            encodeUint($this->firstDur, 2).encodeUint($this->firstMs, 2).encodeUint($this->nRows, 2).            encodeFloat($this->avgVal, true).encodeFloat($this->minVal, false).encodeFloat($this->maxVal, false);        if($freq->perSec) {            $this->measures[] = $avgVal;            $this->data .= encodeFloat($avgVal, true);        } else {            $this->measures[] = $avgVal;            $this->measures[] = $minVal;            $this->measures[] = $maxVal;            $this->data .= encodeFloat($avgVal, true).encodeFloat($minVal, false).encodeFloat($maxVal, false);        }    }    // Return the raw buffer representing header for current sequence    //    // For compatibility with devices, leave nRows to 0xffff as long    // as the sequence is not closed.    //    public function getRawHeader(): string    {        $res = $this->header;        if(!$this->isClosed()) {            $res[14] = chr(255);            $res[15] = chr(255);        }        return $res;    }    // Return the raw buffer representing data for current sequence    //    public function getRawData(): string    {        return $this->data;    }    // Return the raw buffer representing header and data for current sequence    //    public function getRawBytes(): string    {        return $this->header.$this->data;    }    // Flush current sequence to Tar file worker (including header)    //    public function flush()    {        $this->tarFile->tarWorkWrite($this->tarObj, $this->seqOfs, $this->getRawBytes());    }    // Attempt to add a single measure to an existing sequence    // - The sequence is expected to be open    // - The timestamp is expected to be after current sequence start    // - Intermediate "holes" are automatically added if needed    // - If the new measure cannot fit in current sequence close it and return false    // - Once the sequence is complete, it will automatically be closed    public function appendMeasure(VHubServerHTTPRequest $httpReq, DataFrequency $freq, YMeasure $measure): bool    {        if($this->frequency->freqStr != $freq->freqStr) {            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Recording frequency changed from {$this->frequency->freqStr} to {$freq->freqStr}");            $this->closeSeq($httpReq);            return false;        }        $startTime = $measure->get_startTimeUTC();        $endTime = $measure->get_endTimeUTC();        $nextSeqStamp = $this->nextSeqStartStamp();        if($startTime >= $nextSeqStamp) {            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Timestamp beyond sequence end ({$startTime} >= {$nextSeqStamp})");            $this->closeSeq($httpReq);            return false;        }        $prevEndTime = $this->lastStamp();        if($startTime < $prevEndTime) {            // duplicate data for same timestamp, probably a rounding issue: silently drop            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Timestamp {$startTime} < {$prevEndTime}, dropping measure");            return true;        }        $skipRows = intval(($startTime - $prevEndTime) / $this->frequency->period + 0.001); // safe round down        $avgVal = $measure->get_averageValue();        $minVal = $measure->get_minValue();        $maxVal = $measure->get_maxValue();        if($this->frequency->perSec) {            $data = str_repeat(chr(0xff), 4*$skipRows);            $data .= encodeFloat($avgVal, true);            $this->tarFile->tarWorkWrite($this->tarObj, $this->dataOfs + 4*$this->nRows, $data);            if(sizeof($this->measures) > 0) {                $this->data .= $data;                for ($i = 0; $i < $skipRows; $i++) {                    $this->measures[] = NAN;                }                $this->measures[] = $avgVal;            }        } else {            if(is_nan($minVal)) $minVal = $avgVal;            if(is_nan($maxVal)) $maxVal = $avgVal;            $data = str_repeat(chr(0xff), 12*$skipRows);            $data .= encodeFloat($avgVal, true).encodeFloat($minVal, false).encodeFloat($maxVal, false);            $this->tarFile->tarWorkWrite($this->tarObj, $this->dataOfs + 12*$this->nRows, $data);            if(sizeof($this->measures) > 0) {                $this->data .= $data;                for($i = 0; $i < 3*$skipRows; $i++) {                    $this->measures[] = NAN;                }                $this->measures[] = $avgVal;                $this->measures[] = $minVal;                $this->measures[] = $maxVal;            }        }        $this->nRows += $skipRows + 1;        $this->tarFile->tarWorkWriteUint($this->tarObj, $this->seqOfs + 14, $this->nRows, 2);        if($endTime >= $nextSeqStamp) {            VHubServer::Log($httpReq, LOG_DATALOGGER, 4, "This is the last measure of the sequence ({$endTime} >= {$nextSeqStamp})");            $this->closeSeq($httpReq);        }        return true;    }}class DataLogger{    protected $server;    protected $filesrv;    protected $serial;    protected $tarfile;    public function __construct(VHubServer $parent, string $serial)    {        $this->server = $parent;        $this->filesrv = $parent->files;        $this->serial = $serial;        $this->tarfile = null;    }    public function recorderEncode(string $data): string    {        $nwords = strlen($data) >> 1;        $wbuff = [];        for($pos = 0; $pos < $nwords; $pos++) {            $wbuff[$pos] = ord($data[2*$pos])+256*ord($data[2*$pos+1]);        }        $res = '';        for($pos = 0; $pos < $nwords; $pos++) {            $val = $wbuff[$pos];            if($val == 0) {                $res .= '*';                continue;            } else if($val == 0x7fff) {                $res .= 'Y';                continue;            } else if($val == 0xffff) {                $res .= 'X';                continue;            }            for ($dist = 1; $dist <= $pos && $dist <= 30; $dist++) {                if ($wbuff[$pos - $dist] == $val) break;            }            if ($dist <= $pos && $dist <= 30) {                $res .= chr(96 + $dist);            } else {                $res .= chr(48 + ($val & 0x1f)); // 5 lowest bits                $val >>= 5;                $res .= chr(48 + ($val & 0x1f)); // 5 medium bits                $val >>= 5;                $val += 48;                if ($val == 92) {                    $res .= 'z';                } else {                    $res .= chr($val);                }            }        }        return $res;    }    protected function accessData(VHubServerHTTPRequest $httpReq, string $fnpattern = '*'): array    {        $this->tarfile = $this->filesrv->accessDeviceFiles($httpReq, $this->serial);        $tarObjects = $this->tarfile->processTarFile($httpReq, 'datalogger/'.$fnpattern, TAROP_WORKON_FILES);        usort($tarObjects, function(TarObject $a, TarObject $b) { return strcmp($a->path, $b->path); });        $res = [];        foreach($tarObjects as $tarObj) {            $df = new DataFile($tarObj);            if(!isset($res[$df->functionid])) {                $res[$df->functionid] = [ $df ];            } else {                $res[$df->functionid][] = $df;            }        }        // determine the last usage date of each datafile based on startstamp of next file        foreach($res as $functionid => $functionFiles) {            for($i = 1; $i < sizeof($functionFiles); $i++) {                $functionFiles[$i-1]->stopstamp = $functionFiles[$i]->startstamp;            }        }        return $res;    }    protected function canAddDataFile(VHubServerHTTPRequest $httpReq, array $dataFiles, ?DataFile &$oldestDataFile): bool    {        $nFiles = 0;        $oldestDataFile = null;        $oldestStamp = time();        foreach($dataFiles as $functionid => $functionFiles) {            $nFiles += sizeof($functionFiles);            $df = $functionFiles[0];            if($oldestStamp > $df->stopstamp) {                $oldestStamp = $df->stopstamp;                $oldestDataFile = $df;            }        }        return $nFiles < DATAFILE_MAX_COUNT;    }    protected function loadSeq(VHubServerHTTPRequest $httpReq, DataFile $dataFile, int $seqOfs, bool $withData = true): DataSeq    {        $dataSeq = new DataSeq($this->tarfile, $dataFile->tarObject, $seqOfs);        $dataSeq->loadSeq($httpReq, $withData);        return $dataSeq;    }    public function appendMeasures(VHubServerHTTPRequest $httpReq, array $reports)    {        $dataFiles = $this->accessData($httpReq);        $lastSeqOfs = [];        $mustCreate = [];        $mustAdd = [];        foreach($reports as $functionid => $report) {            $hardwareid = "{$this->serial}.{$functionid}";            $freq = $report['freq'];            $measure = $report['measure'];            $startTime = $measure->get_startTimeUTC();            $endTime = $measure->get_endTimeUTC();            $value = $measure->get_averageValue();            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "@{$startTime}-{$endTime}: {$hardwareid}: {$value} {$report['unit']}");            if($endTime < time()-(2*86400)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is more than a 48h in the past, dropping data");                continue;            }            if($endTime > time()+(2*86400)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is more than a 48h in the future, dropping data");                continue;            }            if(!isset($dataFiles[$functionid])) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "No datafile for {$hardwareid} yet, must create one");                $mustCreate[$functionid] = $report;                continue;            }            $lastFile = $dataFiles[$functionid][sizeof($dataFiles[$functionid])-1];            if($endTime < $lastFile->startstamp) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$endTime} for {$hardwareid} is going back, dropping data");                continue;            }            $lastSeqOfs[$functionid] = $this->tarfile->tarWorkReadUint($lastFile->tarObject, 0, 4);            $dataSeq = $this->loadSeq($httpReq, $lastFile, $lastSeqOfs[$functionid], false);            if($dataSeq->isClosed()) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Current sequence for {$hardwareid} is closed, opening a new sequence");                $mustAdd[$functionid] = $report;                continue;            }            if($startTime < $dataSeq->lastStamp()) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Timestamp {$startTime} for {$hardwareid} is going back, dropping data");                continue;            }            if(!$dataSeq->appendMeasure($httpReq, $freq, $measure)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Could not add measure to current sequence {$hardwareid}, opening a new sequence");                $mustAdd[$functionid] = $report;                continue;            }        }        foreach($mustAdd as $functionid => $report) {            $hardwareid = "{$this->serial}.{$functionid}";            $lastFile = $dataFiles[$functionid][sizeof($dataFiles[$functionid])-1];            $seqOfs = $lastSeqOfs[$functionid];            $dataSeq = $this->loadSeq($httpReq, $lastFile, $seqOfs, false);            $seqOfs += $dataSeq->storageSize();            // ensure we have space for one more sequence            $seqMaxSize = $freq->maxSeqRows() * 4;            if(!$freq->perSec) $seqMaxSize *= 3;            $seqMaxSize += DATASEQ_HEADER_SIZE;            if($seqOfs + $seqMaxSize > DATAFILE_MAX_SIZE) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Datafile for {$hardwareid} is full, must create new file");                $mustCreate[$functionid] = $report;                continue;            }            // create new data sequence in file            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "New datastream for {$hardwareid}");            $dataSeq = new DataSeq($this->tarfile, $lastFile->tarObject, $seqOfs);            $dataSeq->initialize($freq, $report['measure']);            $dataSeq->flush();            // update position of last sequence position at start of file            $this->tarfile->tarWorkWriteUint($lastFile->tarObject, 0, $seqOfs, 4);        }        // Release tar file anyway to allow exclusive read/write mode for adding a file        $this->tarfile->tarWorkDone($httpReq);        if(sizeof($mustCreate) == 0) {            return;        }        // Add missing data files in tar archive        $datapad = str_repeat(chr(255), DATAFILE_MAX_SIZE);        foreach($mustCreate as $functionid => $report) {            $measure = $report['measure'];            $utcstamp = intval(round($measure->get_startTimeUTC()));            $prefix = 'datalogger/'.$utcstamp.'-';            $cleanUnit = str_replace('/', '_', $report['unit']);            $suffix = '-'.date('Y-m-d', $utcstamp).'.bin';            $subfile = $prefix.$functionid.'-'.$cleanUnit.$suffix;            $seqOfs = 4;            $dataSeq = new DataSeq($this->tarfile, null, $seqOfs);            $dataSeq->initialize($freq, $measure);            $content = encodeUint($seqOfs, 4).$dataSeq->getRawBytes();            $content .= substr($datapad, strlen($content));            if($this->canAddDataFile($httpReq, $dataFiles, $oldestDataFile)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Creating {$subfile} for {$this->serial}");                $this->tarfile->processTarFile($httpReq, $subfile, TAROP_UPDATE_FILE, $content);            } else {                $oldsubfile = $oldestDataFile->tarObject->path;                VHubServer::Log($httpReq, LOG_DATALOGGER, 3, "Creating {$subfile} for {$this->serial}, replacing oldest file {$oldsubfile}");                $this->tarfile->processTarFile($httpReq, $oldsubfile.'|'.$subfile, TAROP_REPLACE_FILE, $content);            }            // refresh file list            $dataFiles = $this->accessData($httpReq);        }    }    public function printIndex(VHubServerHTTPRequest $httpReq, APISensorNode $sensorNode, string $functionid, string $runmatch, int $fromStamp, int $toStamp, bool $verbose)    {        $unit = $sensorNode->getattr('unit');        $calib = $sensorNode->getattr('calibrationParam');        $httpReq->put('{"id":"'.$functionid.'","unit":"'.$unit.'","calib":"'.$calib.'","cal":"*","bulk":"128","streams":'."[\n");        $sep = '';        $dataFiles = $this->accessData($httpReq, '*-'.$functionid.'-*');        if(isset($dataFiles[$functionid])) {            $functionFiles = $dataFiles[$functionid];        } else {            $functionFiles = [];        }        VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Found ".sizeof($functionFiles). " file matching functionId $functionid");        for($i = 0; $i < sizeof($functionFiles); $i++) {            // filter out files not relevant for the requested period and unit            $dataFile = $functionFiles[$i];            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check unit");            $cleanUnit = str_replace('/', '_', $unit);            if($dataFile->unit != $cleanUnit) continue;            if($i+1 < sizeof($functionFiles)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check start timestamp");                if($functionFiles[$i+1]->startstamp <= $fromStamp) continue;            }            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check end timestamp");            if($dataFile->startstamp > $toStamp) break;            if($i+1 < sizeof($functionFiles)) {                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Check next timestamp");                $nextFile = $functionFiles[$i+1];                if($nextFile->startstamp <= $fromStamp) {                    continue;                }            }            // data file might contain data for the requested period            $lastSeqOfs = $this->tarfile->tarWorkReadUint($dataFile->tarObject, 0, 4);            VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Last sequence at offset $lastSeqOfs");            for($seqOfs = 4; $seqOfs <= $lastSeqOfs; ) {                $dataSeq = $this->loadSeq($httpReq, $dataFile, $seqOfs, false);                $duration = intVal(round($dataSeq->nRows * $dataSeq->frequency->period));                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Sequence at $seqOfs start stamp: ".$dataSeq->utcStamp);                if($dataSeq->utcStamp >= $toStamp) break;                VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Sequence at $seqOfs end stamp: ".($dataSeq->utcStamp+$duration));                if($dataSeq->utcStamp+$duration > $fromStamp &&                    ($runmatch == '' || intval($runmatch) == $dataSeq->runIdx)) {                    if ($verbose) {                        $avgMinMax = $dataSeq->getAvgMinMax();                        $avgVal = $avgMinMax[0];                        $minVal = $avgMinMax[1];                        $maxVal = $avgMinMax[2];                        $httpReq->put($sep . '{"run":' . $dataSeq->runIdx . ',"utc":' . $dataSeq->utcStamp . ',"dur":' . $duration .                            ',"freq":"' . $dataSeq->frequency->freqStr . '","val":[' . $minVal . ',' . $avgVal . ',' . $maxVal . ']}' . "\n");                    } else {                        $httpReq->put($sep . '"' . $this->recorderEncode($dataSeq->getRawHeader()) . '"' . "\n");                    }                    $sep = ',';                }                $seqOfs += $dataSeq->storageSize();            }        }        $this->tarfile->tarWorkDone($httpReq);        $httpReq->put("]}");    }    public function printRun(VHubServerHTTPRequest $httpReq, string $functionid, string $runmatch, array $utcStamps, bool $verbose)    {        $dataFiles = $this->accessData($httpReq, '*-'.$functionid.'-*');        if(isset($dataFiles[$functionid])) {            $functionFiles = $dataFiles[$functionid];        } else {            $functionFiles = [];        }        $isFirst = true;        $minStamp = min($utcStamps);        $stampIdx = 0;        for($fi = 0; $fi < sizeof($functionFiles); $fi++) {            // filter out files not relevant for the requested period and unit            $dataFile = $functionFiles[$fi];            if($fi+1 < sizeof($functionFiles)) {                $nextFile = $functionFiles[$fi+1];                if($nextFile->startstamp <= $minStamp) {                    continue;                }            }            // data file might contain data for the requested period            $lastSeqOfs = $this->tarfile->tarWorkReadUint($dataFile->tarObject, 0, 4);            for($seqOfs = 4; $seqOfs <= $lastSeqOfs; ) {                $dataSeq = $this->loadSeq($httpReq, $dataFile, $seqOfs, false);                $duration = intVal(round($dataSeq->nRows * $dataSeq->frequency->period));                if($dataSeq->utcStamp == $utcStamps[$stampIdx] &&                    ($runmatch == '' || intval($runmatch) == $dataSeq->runIdx)) {                    VHubServer::Log($httpReq, LOG_DATALOGGER, 5, "Using sequence starting at {$dataSeq->utcStamp}, {$dataSeq->nRows} rows, {$duration}s");                    $dataSeq->loadSeq($httpReq, true);                    if ($verbose) {                        $httpReq->put($isFirst ? '[' : ",\n[");                        $sep = '';                        $measures = $dataSeq->measures;                        if($dataSeq->frequency->perSec) {                            for($i = 0; $i < sizeof($measures); $i++) {                                $httpReq->put($sep.$measures[$i]."\n");                                $sep = ',';                            }                        } else {                            for($i = 0; $i+2 < sizeof($measures); $i += 3) {                                $httpReq->put($sep."[".$measures[$i+1].','.$measures[$i].','.$measures[$i+2]."]\n");                                $sep = ',';                            }                        }                        $httpReq->put(']');                    } else {                        $httpReq->put(($isFirst ? '"' : "\n,\"") . $this->recorderEncode($dataSeq->getRawData()) . '"');                    }                    $isFirst = false;                    $stampIdx++;                    if($stampIdx >= sizeof($utcStamps)) {                        // exit outside loop                        $fi = sizeof($functionFiles);                        break;                    }                }                $seqOfs += $dataSeq->storageSize();            }        }        $this->tarfile->tarWorkDone($httpReq);    }}
function parseEnum(string $ystr, $enumDef): int{    if(is_numeric($ystr)) {        return intVal($ystr);    }    $res = array_search($ystr, $enumDef);    if($res !== FALSE) {        return $res;    }    return 0;}function parseUInt(string $ystr): int{    $xpos = strpos($ystr, 'x');    if($xpos !== FALSE) {        return hexdec(substr($ystr, $xpos+1));    }    return intVal($ystr);}function parseMeasure(string $ystr): float{    return floatVal($ystr);}function parseStepPos(string $ystr): float{    return floatVal($ystr);}function parseMove(string $ystr): object{    if(preg_match('/^(?<target>-?\d+):(?<msval>\d+)$/', $ystr, $matches)) {        return (object)[            'moving' => 1,            'target' => $matches['target'],            'ms' => $matches['ms']        ];    }    return (object)[        'moving' => 0,        'target' => 0,        'ms' => 0    ];}function APIBitString(string $bitstring, int $value): string{    $nbits = strlen($bitstring);    for($i = 0; $i < $nbits; $i++) {        if(($value & 1) == 0) {            $bitstring[$i] = '.';        }        $value >>= 1;    }    return '['.$bitstring.']';}function APIPassword(VHubServerHTTPRequest $httpReq, string $pwd): string{    if($httpReq->getAuthUser() == 'admin') {        return $pwd;    } else {        return '*****';    }}
class CloudConf{    public function __construct()    {    }    function loadState(VHubServerHTTPRequest $httpReq, object $data)    {    }    function saveState(): array    {        return [];    }}class DeviceCloudConf extends CloudConf{    public $parentHub;    public $parentIP;    public $lastSeen;    public $reconnect;    public $logPos;    public $tRepPos;    public $yfsVer;    public function __construct()    {        parent::__construct();        $this->parentHub = '';        $this->parentIP = '';        $this->lastSeen = 0;        $this->reconnect = 0;        $this->logPos = 0;        $this->tRepPos = 0;        $this->yfsVer = '';    }    function loadState($httpReq, object $data)    {        parent::loadState($httpReq, $data);        if(isset($data->parentHub)) {            $this->parentHub = $data->parentHub;            $this->parentIP = $data->parentIP;            $this->lastSeen = $data->lastSeen;        }        if(isset($data->reconnect)) {            $this->reconnect = $data->reconnect;        }        $this->logPos = $data->logPos;        if(isset($data->tRepPos)) {            $this->tRepPos = $data->tRepPos;        }        if(isset($data->yfsVer)) {            $this->yfsVer = $data->yfsVer;        }    }    function deviceResetDetected()    {        $this->logPos = 0;        $this->tRepPos = 0;    }    function saveState(): array    {        $res = parent::saveState();        $res['parentHub'] = $this->parentHub;        $res['parentIP'] = $this->parentIP;        $res['lastSeen'] = $this->lastSeen;        $res['reconnect'] = $this->reconnect;        $res['logPos'] = $this->logPos;        if($this->tRepPos != 0) {            $res['tRepPos'] = $this->tRepPos;        }        $res['yfsVer'] = $this->yfsVer;        return $res;    }}class GlobalCloudConf extends CloudConf{    // The serial number is initialized randomly only, then preserved    public $serialNumber;    // The authentication realm is initialized to the serial number, then preserved.    // Passwords must be reset if the realm is changed manually.    public $authRealm;    // Incoming HTTP callback MD5 signature password.    public $md5signPwd;    // Settings saved explicitely    public $savedSettings;    // Current attribute values    public $valuesCache;    // Additional state variables to be saved, to be used through accessor functions    protected $devYdxBySerial;    const PARENT_DEVYDX = 10000;    public function __construct()    {        parent::__construct();        $this->serialNumber = 'VHUB4WEB-'.dechex(mt_rand(0x1000000,0xfffffff));        $this->authRealm = $this->serialNumber;        $this->md5signPwd = '';        $this->savedSettings = [            'logicalName' => '',            'networkName' => '',            'filesName' => '',            'luminosity' => 0,            'defaultPage' => '',            'userPassword' => '',            'adminPassword' => ''        ];        $this->valuesCache = array_merge($this->savedSettings);        $this->devYdxBySerial = [];    }    public function loadState(VHubServerHTTPRequest $httpReq, object $data)    {        parent::loadState($httpReq, $data);        $this->serialNumber = $data->serialNumber;        $this->authRealm = $data->authRealm;        if(isset($data->md5signPwd)) {            $this->md5signPwd = $data->md5signPwd;        }        foreach($data->savedSettings as $name => $value) {            $this->savedSettings[$name] = $value;            // default current value to saved setting            $this->valuesCache[$name] = $value;        }        foreach($data->valuesCache as $name => $value) {            $this->valuesCache[$name] = $value;        }        foreach($data->devYdxBySerial as $serial => $devydx) {            $this->devYdxBySerial[$serial] = $devydx;        }    }    // Allocate a new the devYdx for a given serial number, and bind it to the parent devYdx    // Return the newly allocated devYdx    // If no more devYdx is available (> 255 devices), return -1;    //    public function allocDevYdx(string $serial, int $parentDevYdx): int    {        $usedDevYdx = [];        foreach($this->devYdxBySerial as $devYdx) {            $usedDevYdx[$devYdx % GlobalCloudConf::PARENT_DEVYDX] = true;        }        for($devYdx = 1; $devYdx < 256; $devYdx++) {            if(!isset($usedDevYdx[$devYdx])) {                $this->devYdxBySerial[$serial] = $devYdx + GlobalCloudConf::PARENT_DEVYDX * $parentDevYdx;                return $devYdx;            }        }        return -1;    }    // Return the devYdx for a given serial number, or -1 if device is unknown    //    public function getDevYdx(string $serial): int    {        if(!isset($this->devYdxBySerial[$serial])) {            return -1;        }        return $this->devYdxBySerial[$serial] % GlobalCloudConf::PARENT_DEVYDX;    }    // Return the parent device devYdx for a device given by its serialNumber    //    public function getParentDevYdx(string $serial): int    {        if(!isset($this->devYdxBySerial[$serial])) {            return 0;   // VirtualHub-4web own devYdx        }        return intdiv($this->devYdxBySerial[$serial], GlobalCloudConf::PARENT_DEVYDX);    }    // Sets the parent devYdx only for a device given by serialNumber    //    public function setParentDevYdx(string $serial, int $parentDevYdx)    {        if(!isset($this->devYdxBySerial[$serial])) {            return; // should never happen, but not that bad anyway        }        $devYdx = $this->getDevYdx($serial);        $this->devYdxBySerial[$serial] = $devYdx + GlobalCloudConf::PARENT_DEVYDX * $parentDevYdx;    }    // Free a given devYdx when forgetting a device    //    public function freeDevYdx(string $serial)    {        unset($this->devYdxBySerial[$serial]);    }    public function saveState(): array    {        $res = parent::saveState();        $res['serialNumber'] = $this->serialNumber;        $res['authRealm'] = $this->authRealm;        $res['md5signPwd'] = $this->md5signPwd;        $res['savedSettings'] = $this->savedSettings;        $res['valuesCache'] = $this->valuesCache;        $res['devYdxBySerial'] = $this->devYdxBySerial;        return $res;    }    // Save current API settings to persistent zone    public function saveSettings()    {        foreach($this->savedSettings as $name => $value) {            $this->savedSettings[$name] = $this->valuesCache[$name];        }    }    // Revert current API settings to saved values    public function revertSettings()    {        foreach($this->savedSettings as $name => $value) {            $this->valuesCache[$name] = $value;        }    }}class DailyStats{    protected $divisor;    protected $color;    protected $byCallback;    protected $byDayMin;    protected $byDayVal;    protected $byDayMax;    protected $prevDayStamp;    protected $prevDayCount;    protected $prevDaySum;    protected $prevDayMin;    protected $prevDayMax;    public function __construct(int $divisor, int $color)    {        $this->divisor = $divisor;        $this->color = $color;        $this->byCallback = [];        $this->byDayMin = [];        $this->byDayVal = [];        $this->byDayMax = [];        $this->prevDayStamp = 0;        $this->prevDayCount = 0;        $this->prevDaySum = 0;        $this->prevDayMin = 0;        $this->prevDayMax = 0;    }    function loadState(VHubServerHTTPRequest $httpReq, object $data)    {        $this->byCallback = $data->byCallback;        $this->byDayVal = $data->byDayVal;        $this->byDayMin = $data->byDayMin;        $this->byDayMax = $data->byDayMax;        $this->prevDayStamp = $data->prevDayStamp;        $this->prevDayCount = $data->prevDayCount;        $this->prevDaySum = $data->prevDaySum;        $this->prevDayMin = $data->prevDayMin;        $this->prevDayMax = $data->prevDayMax;    }    function appendVal(VHubServerHTTPRequest $httpReq, int $timeStamp, int $val)    {        // Save per-callback information        $this->byCallback[] = $val;        if(sizeof($this->byCallback) > DEVICESTATS_MAX_CONN) {            array_splice($this->byCallback, 0, sizeof($this->byCallback) - DEVICESTATS_MAX_CONN);        }        // Save per-day information        $dayStamp = $timeStamp - ($timeStamp % 86400) + 43200;        if($this->prevDayStamp != $dayStamp) {            if($this->prevDayStamp != 0) {                if ($this->prevDayCount > 0) {                    $divisor = ($this->divisor > 0 ? $this->divisor : $this->prevDayCount);                    $this->byDayVal[] = intval(round($this->prevDaySum / $divisor));                    $this->byDayMin[] = $this->prevDayMin;                    $this->byDayMax[] = $this->prevDayMax;                }                $dayInterval = intdiv($dayStamp - $this->prevDayStamp, 86400);                while ($dayInterval > 1) {                    $this->byDayVal[] = 0;                    $this->byDayMin[] = 0;                    $this->byDayMax[] = 0;                    $dayInterval--;                }                if(sizeof($this->byDayVal) > DEVICESTATS_MAX_DAYS) {                    array_splice($this->byDayVal, 0, sizeof($this->byDayVal) - DEVICESTATS_MAX_DAYS);                    array_splice($this->byDayMin, 0, sizeof($this->byDayMin) - DEVICESTATS_MAX_DAYS);                    array_splice($this->byDayMax, 0, sizeof($this->byDayMax) - DEVICESTATS_MAX_DAYS);                }            }            $this->prevDayStamp = $dayStamp;            $this->prevDayCount = 1;            $this->prevDaySum = $val;            $this->prevDayMin = $val;            $this->prevDayMax = $val;        } else {            $this->prevDayCount++;            $this->prevDaySum += $val;            $this->prevDayMin = min($this->prevDayMin, $val);            $this->prevDayMax = max($this->prevDayMax, $val);        }    }    function saveState(): array    {        $res = [];        $res['byCallback'] = $this->byCallback;        $res['byDayMin'] = $this->byDayMin;        $res['byDayVal'] = $this->byDayVal;        $res['byDayMax'] = $this->byDayMax;        $res['dayValDivisor'] = $this->divisor;        $res['prevDayStamp'] = $this->prevDayStamp;        $res['prevDayCount'] = $this->prevDayCount;        $res['prevDaySum'] = $this->prevDaySum;        $res['prevDayMin'] = $this->prevDayMin;        $res['prevDayMax'] = $this->prevDayMax;        $res['defaultColor'] = $this->color;        return $res;    }}class DeviceStats{    protected $prevTimestamp;    protected $modified;    protected $stats; // actually a YearlyStats[]    public function __construct()    {        $this->prevTimestamp = 0;        $this->modified = false;        $this->stats = [            'callbackInterval_s' => new DailyStats(0, 0x8b4513),            'sensorBufferUsage_percent' => new DailyStats(0, 0x7f007f),            'errors_count' => new DailyStats(1, 0xdf0000),            'warnings_count' => new DailyStats(1, 0xdf5f00),            'devices_count' => new DailyStats(1, 0x5f5f5f),            'resets_count' => new DailyStats(1, 0xe5b718),            'callbackIOReadTime_ms' => new DailyStats(0, 0x00006f),            'callbackProcessingTime_ms' => new DailyStats(0, 0x0000cf),            'dataReceived_bytes_kb' => new DailyStats(1024, 0x005f00),            'dataSent_bytes_kb' => new DailyStats(1024, 0x008f00)        ];    }    function loadState(VHubServerHTTPRequest $httpReq, object $data)    {        $this->prevTimestamp = $data->prevTimestamp;        foreach($data as $key => $stats) {            if(isset($this->stats[$key]) && isset($stats->prevDayStamp)) {                $this->stats[$key]->loadState($httpReq, $stats);            }        }        $this->modified = false;    }    function appendStats(VHubServerHTTPRequest $httpReq, int $sensorBufferUsage, int $nDevice, int $nReset)    {        $now = $httpReq->getRequestTimestamp();        $interval = ($this->prevTimestamp == 0 ? 0 : $now - $this->prevTimestamp);        $this->stats['callbackInterval_s']->appendVal($httpReq, $now, $interval);        $this->stats['sensorBufferUsage_percent']->appendVal($httpReq, $now, $sensorBufferUsage);        $this->stats['errors_count']->appendVal($httpReq, $now, $httpReq->getErrorCount());        $this->stats['warnings_count']->appendVal($httpReq, $now, $httpReq->getWarningCount());        $this->stats['devices_count']->appendVal($httpReq, $now, $nDevice);        $this->stats['resets_count']->appendVal($httpReq, $now, $nReset);        $this->stats['callbackIOReadTime_ms']->appendVal($httpReq, $now, $httpReq->getIOReadTime());        $this->stats['callbackProcessingTime_ms']->appendVal($httpReq, $now, $httpReq->getProcessingTime());        $this->stats['dataReceived_bytes_kb']->appendVal($httpReq, $now, $httpReq->getDataReceived());        $this->stats['dataSent_bytes_kb']->appendVal($httpReq, $now, $httpReq->getDataSent());        $this->prevTimestamp = $now;        $this->changed = true;    }    public function hasChanged(): bool    {        return $this->modified;    }    function saveState(): array    {        $res = [ 'prevTimestamp' => $this->prevTimestamp ];        foreach($this->stats as $key => $stats) {            $res[$key] = $stats->saveState();        }        return $res;    }}
$ApiAttrEdit = "function editHtml(n,v,t){e=editFreeText;switch(t){case 1:case 9:case 19:case 24:case 27:case 62:case 63:return e(n,v,20);case 3:return editSelect(n,v,['Revert','Save to flash']);case 4:return e(n,v.slice(0,-1),3);case 5:case 36:return editRadio(n,v,['OFF','ON']);case 8:case 11:case 14:case 28:return e(n,v,7);case 10:case 86:return (typeof eF=='undefined'?e:eF)(n,v,9);case 12:return editSelect(n,v,['IMMEDIATE','PERIOD_AVG','PERIOD_MIN','PERIOD_MAX']);case 16:case 25:case 32:case 18:case 49:case 51:case 60:case 67:case 6:case 74:case 81:case 83:case 88:case 89:return e(n,v,15);case 17:return editSelect(n,v,['ANALOG_FAST','DIGITAL4','ANALOG_SMOOTH']);case 15:return editRadio(n,v,['FALSE','TRUE']);case 26:return editSelect(n,v,['HOMENETWORK','ROAMING','NEVER','NEUTRALITY']);case 30:return editSelect(n,v,['RGB','RGBW','WS2811']);case 35:return editUTC(n,v);case 37:return e(n,v,10);case 39:return editSelect(n,v,['USB_5V','USB_3V','EXT_V']);case 40:return editSelect(n,v,['LEFT','UP','RIGHT','DOWN']);case 43:return editSelect(n,v,['AUTO','FROM_USB','FROM_EXT','OFF']);case 44:return editSelect(n,v,['HIGH_RATE','HIGH_RATE_FILTERED','LOW_NOISE','LOW_NOISE_FILTERED','HIGHEST_RATE','AC']);case 45:return editSelect(n,v,['GPS_DMS','GPS_DM','GPS_D']);case 46:return editSelect(n,v,['GNSS','GPS','GLONASS','GALILEO','GPS_GLONASS','GPS_GALILEO','GLONASS_GALILEO']);case 50:return editSelect(n,v,['OFF','3V3','1V8']);case 53:return editSelect(n,v,['STILL','RELAX','AWARE','RUN','CALL','PANIC']);case 54:return editSelect(n,v,['HUMAN_EYE','WIDE_SPECTRUM','INFRARED','HIGH_RATE','HIGH_ENERGY','HIGH_RESOLUTION']);case 57:return editSelect(n,v,['OFF','DC','AC']);case 58:return editSelect(n,v,['IDLE','BRAKE','FORWD','BACKWD','LOVOLT','HICURR','HIHEAT','FAILSF']);case 61:case 96:return e(n,v,33);case 64:return editSelect(n,v,['POST','GET','PUT']);case 65:return editSelect(n,v,['FORM','JSON','JSON_ARRAY','CSV','YOCTO_API','JSON_NUM','EMONCMS','AZURE','INFLUXDB','MQTT','YOCTO_API_JZON','PRTG','INFLUXDB_V2']);case 66:case 99:return e(n,v,50);case 68:return editSelect(n,v,['OFF','OUT3V3','OUT5V','OUT4V7','OUT1V8']);case 69:return editSelect(n,v,['INT','EXT']);case 70:return editSelect(n,v,['NUMERIC','PRESENCE','PULSECOUNT']);case 71:return editSelect(n,v,['PWM_DUTYCYCLE','PWM_FREQUENCY','PWM_PULSEDURATION','PWM_EDGECOUNT','PWM_PULSECOUNT','PWM_CPS','PWM_CPM','PWM_STATE','PWM_FREQ_CPS','PWM_FREQ_CPM','PWM_PERIODCOUNT']);case 72:return editSelect(n,v,['USB5V','USB3V','EXTV','OPNDRN']);case 73:return editSelect(n,v,['DEFAULT','LONG_RANGE','HIGH_ACCURACY','HIGH_SPEED']);case 75:return editSelect(n,v,['NDOF','NDOF_FMC_OFF','M4G','COMPASS','IMU','INCLIN_90DEG_1G8','INCLIN_90DEG_3G6','INCLIN_10DEG']);case 76:return editRadio(n,v,['A','B']);case 77:return editSelect(n,v,['UNCHANGED','A','B']);case 79:return editSelect(n,v,['DISCONNECTED','MANUAL','AUTO1','AUTO60']);case 80:return editSelect(n,v,['OFF','TTL3V','TTL3VR','TTL5V','TTL5VR','RS232','RS485','TTL1V8','SDI12']);case 82:return e(n,v.slice(0,-2),3);case 84:return editRadio(n,v,['ACTIVE_LOW','ACTIVE_HIGH']);case 87:return editRadio(n,v,['MICROSTEP16','MICROSTEP8','MICROSTEP4','HALFSTEP','FULLSTEP']);case 90:return editSelect(n,v,['DIGITAL','TYPE_K','TYPE_E','TYPE_J','TYPE_N','TYPE_R','TYPE_S','TYPE_T','PT100_4WIRES','PT100_3WIRES','PT100_2WIRES','RES_OHM','RES_NTC','RES_LINEAR','RES_INTERNAL','IR','RES_PT1000','CHANNEL_OFF']);case 92:return editSelect(n,v,['SLEEPING','AWAKE']);case 94:return e(n,v,26);case 95:return e(n,v,9);case 97:return e(n,v,14);}return e(n,v,32)}";
$ApiDef = [
    "DataLogger" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "currentRunIndex"    => 14,
        "timeUTC"            => -35,
        "recording"          => -36,
        "autoStart"          => -5,
        "beaconDriven"       => -5,
        "usage"              => 4,
        "clearHistory"       => -15
    ],
    "Qt" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Module" => [
        "productName"        => 1,
        "serialNumber"       => 1,
        "logicalName"        => -1,
        "productId"          => 2,
        "productRelease"     => 2,
        "firmwareRelease"    => 1,
        "persistentSettings" => -3,
        "luminosity"         => -4,
        "beacon"             => -5,
        "upTime"             => 6,
        "usbCurrent"         => 7,
        "rebootCountdown"    => -8,
        "userVar"            => -8
    ],
    "Accelerometer" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "bandwidth"          => -14,
        "xValue"             => 10,
        "yValue"             => 10,
        "zValue"             => 10,
        "gravityCancellation" => -5
    ],
    "Altitude" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => -10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "qnh"                => -10,
        "technology"         => 1
    ],
    "AnButton" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "calibratedValue"    => 14,
        "rawValue"           => 14,
        "analogCalibration"  => -5,
        "calibrationMax"     => -14,
        "calibrationMin"     => -14,
        "sensitivity"        => -14,
        "isPressed"          => 15,
        "lastTimePressed"    => 6,
        "lastTimeReleased"   => 6,
        "pulseCounter"       => -16,
        "pulseTimer"         => 6,
        "inputType"          => -17
    ],
    "ArithmeticSensor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "description"        => 1,
        "command"            => -1
    ],
    "AudioIn" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "volume"             => -4,
        "mute"               => -15,
        "volumeRange"        => 18,
        "signal"             => 8,
        "noSignalFor"        => 8
    ],
    "AudioOut" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "volume"             => -4,
        "mute"               => -15,
        "volumeRange"        => 18,
        "signal"             => 8,
        "noSignalFor"        => 8
    ],
    "BluetoothLink" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "ownAddress"         => 19,
        "pairingPin"         => -1,
        "remoteAddress"      => -19,
        "remoteName"         => 1,
        "mute"               => -15,
        "preAmplifier"       => -4,
        "volume"             => -4,
        "linkState"          => 20,
        "linkQuality"        => 4,
        "command"            => -1
    ],
    "Buzzer" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "frequency"          => -10,
        "volume"             => -4,
        "playSeqSize"        => 14,
        "playSeqMaxSize"     => 14,
        "playSeqSignature"   => 14,
        "command"            => -1
    ],
    "CarbonDioxide" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "abcPeriod"          => -14,
        "command"            => -1
    ],
    "Cellular" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "linkQuality"        => 4,
        "cellOperator"       => 1,
        "cellIdentifier"     => 1,
        "cellType"           => 21,
        "imsi"               => 22,
        "message"            => 23,
        "pin"                => -24,
        "radioConfig"        => -25,
        "lockedOperator"     => -1,
        "airplaneMode"       => -5,
        "enableData"         => -26,
        "apn"                => -1,
        "apnSecret"          => -27,
        "pingInterval"       => -14,
        "dataSent"           => -14,
        "dataReceived"       => -14,
        "command"            => -1
    ],
    "ColorLed" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "rgbColor"           => -28,
        "hslColor"           => -28,
        "rgbMove"            => -29,
        "hslMove"            => -29,
        "rgbColorAtPowerOn"  => -28,
        "blinkSeqSize"       => 14,
        "blinkSeqMaxSize"    => 14,
        "blinkSeqSignature"  => 14,
        "command"            => -1
    ],
    "ColorLedCluster" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "activeLedCount"     => -14,
        "ledType"            => -30,
        "maxLedCount"        => 14,
        "dynamicLedCount"    => 14,
        "blinkSeqMaxCount"   => 14,
        "blinkSeqMaxSize"    => 14,
        "command"            => -1
    ],
    "Compass" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "bandwidth"          => -14,
        "axis"               => 31,
        "magneticHeading"    => 10
    ],
    "Current" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "enabled"            => -15
    ],
    "CurrentLoopOutput" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "current"            => -10,
        "currentTransition"  => -32,
        "currentAtStartUp"   => -10,
        "loopPower"          => 33
    ],
    "DaisyChain" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "daisyState"         => 34,
        "childCount"         => 14,
        "requiredChildCount" => -14
    ],
    "DigitalIO" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "portState"          => -37,
        "portDirection"      => -37,
        "portOpenDrain"      => -37,
        "portPolarity"       => -37,
        "portDiags"          => 38,
        "portSize"           => 14,
        "outputVoltage"      => -39,
        "command"            => -1
    ],
    "Display" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "enabled"            => -15,
        "startupSeq"         => -1,
        "brightness"         => -4,
        "orientation"        => -40,
        "displayWidth"       => 14,
        "displayHeight"      => 14,
        "displayType"        => 41,
        "layerWidth"         => 14,
        "layerHeight"        => 14,
        "layerCount"         => 14,
        "command"            => -1
    ],
    "DualPower" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "powerState"         => 42,
        "powerControl"       => -43,
        "extVoltage"         => 14
    ],
    "Files" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "filesCount"         => 14,
        "freeSpace"          => 14
    ],
    "GenericSensor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "signalValue"        => 10,
        "signalUnit"         => 1,
        "signalRange"        => -18,
        "valueRange"         => -18,
        "signalBias"         => -10,
        "signalSampling"     => -44,
        "enabled"            => -15
    ],
    "Gps" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "isFixed"            => 15,
        "satCount"           => 16,
        "satPerConst"        => 16,
        "gpsRefreshRate"     => 10,
        "coordSystem"        => -45,
        "constellation"      => -46,
        "latitude"           => 1,
        "longitude"          => 1,
        "dilution"           => 10,
        "altitude"           => 10,
        "groundSpeed"        => 10,
        "direction"          => 10,
        "unixTime"           => 35,
        "dateTime"           => 1,
        "utcOffset"          => -8,
        "command"            => -1
    ],
    "GroundSpeed" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Gyro" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "bandwidth"          => -14,
        "xValue"             => 10,
        "yValue"             => 10,
        "zValue"             => 10
    ],
    "HubPort" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "enabled"            => -15,
        "portState"          => 47,
        "baudRate"           => 48
    ],
    "Humidity" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "relHum"             => 10,
        "absHum"             => 10
    ],
    "I2cPort" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "rxCount"            => 14,
        "txCount"            => 14,
        "errCount"           => 14,
        "rxMsgCount"         => 14,
        "txMsgCount"         => 14,
        "lastMsg"            => 1,
        "currentJob"         => -1,
        "startupJob"         => -1,
        "jobMaxTask"         => 14,
        "jobMaxSize"         => 14,
        "command"            => -1,
        "protocol"           => -49,
        "i2cVoltageLevel"    => -50,
        "i2cMode"            => -51
    ],
    "InputChain" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "expectedNodes"      => -14,
        "detectedNodes"      => 14,
        "loopbackTest"       => -5,
        "refreshRate"        => -14,
        "bitChain1"          => 1,
        "bitChain2"          => 1,
        "bitChain3"          => 1,
        "bitChain4"          => 1,
        "bitChain5"          => 1,
        "bitChain6"          => 1,
        "bitChain7"          => 1,
        "watchdogPeriod"     => -14,
        "chainDiags"         => 52
    ],
    "Latitude" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Longitude" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Led" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "power"              => -5,
        "luminosity"         => -4,
        "blinking"           => -53
    ],
    "LightSensor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => -10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "measureType"        => -54
    ],
    "Magnetometer" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "bandwidth"          => -14,
        "xValue"             => 10,
        "yValue"             => 10,
        "zValue"             => 10
    ],
    "MessageBox" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "slotsInUse"         => 14,
        "slotsCount"         => 14,
        "slotsBitmap"        => 55,
        "pduSent"            => -14,
        "pduReceived"        => -14,
        "command"            => -1
    ],
    "MultiAxisController" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "nAxis"              => -14,
        "globalState"        => 56,
        "command"            => -1
    ],
    "MultiCellWeighScale" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "cellCount"          => -14,
        "externalSense"      => -15,
        "excitation"         => -57,
        "tempAvgAdaptRatio"  => -10,
        "tempChgAdaptRatio"  => -10,
        "compTempAvg"        => 10,
        "compTempChg"        => 10,
        "compensation"       => 10,
        "zeroTracking"       => -10,
        "command"            => -1
    ],
    "MultiSensController" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "nSensors"           => -14,
        "maxSensors"         => 14,
        "maintenanceMode"    => -15,
        "lastAddressDetected" => 14,
        "command"            => -1
    ],
    "Motor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "motorStatus"        => -58,
        "drivingForce"       => -10,
        "brakingForce"       => -10,
        "cutOffVoltage"      => -10,
        "overCurrentLimit"   => -14,
        "frequency"          => -10,
        "starterTime"        => -14,
        "failSafeTimeout"    => -14,
        "command"            => -1
    ],
    "Network" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "readiness"          => 59,
        "macAddress"         => 19,
        "ipAddress"          => 60,
        "subnetMask"         => 60,
        "router"             => 60,
        "currentDNS"         => 60,
        "ipConfig"           => -61,
        "primaryDNS"         => -60,
        "secondaryDNS"       => -60,
        "ntpServer"          => -60,
        "userPassword"       => -62,
        "adminPassword"      => -63,
        "httpPort"           => -14,
        "defaultPage"        => -1,
        "discoverable"       => -15,
        "wwwWatchdogDelay"   => -14,
        "callbackUrl"        => -1,
        "callbackMethod"     => -64,
        "callbackEncoding"   => -65,
        "callbackCredentials" => -66,
        "callbackInitialDelay" => -14,
        "callbackSchedule"   => -67,
        "callbackMinDelay"   => -14,
        "callbackMaxDelay"   => -14,
        "poeCurrent"         => 7
    ],
    "OsControl" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "shutdownCountdown"  => -14
    ],
    "Power" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "cosPhi"             => 10,
        "meter"              => -10,
        "deliveredEnergyMeter" => 10,
        "receivedEnergyMeter" => 10,
        "meterTimer"         => 14
    ],
    "Pressure" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "PowerOutput" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "voltage"            => -68
    ],
    "PowerSupply" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "voltageSetPoint"    => -10,
        "currentLimit"       => -10,
        "powerOutput"        => -5,
        "voltageSense"       => -69,
        "measuredVoltage"    => 10,
        "measuredCurrent"    => 10,
        "inputVoltage"       => 10,
        "vInt"               => 10,
        "ldoTemperature"     => 10,
        "voltageTransition"  => -32,
        "voltageAtStartUp"   => -10,
        "currentAtStartUp"   => -10,
        "command"            => -1
    ],
    "Proximity" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "signalValue"        => 10,
        "detectionThreshold" => -14,
        "detectionHysteresis" => -14,
        "presenceMinTime"    => -14,
        "removalMinTime"     => -14,
        "isPresent"          => 15,
        "lastTimeApproached" => 6,
        "lastTimeRemoved"    => 6,
        "pulseCounter"       => -16,
        "pulseTimer"         => 6,
        "proximityReportMode" => -70
    ],
    "PwmInput" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "dutyCycle"          => 10,
        "pulseDuration"      => 10,
        "frequency"          => 10,
        "period"             => 10,
        "pulseCounter"       => -16,
        "pulseTimer"         => 6,
        "pwmReportMode"      => -71,
        "debouncePeriod"     => -14,
        "bandwidth"          => -14,
        "edgesPerPeriod"     => 14
    ],
    "PwmOutput" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "enabled"            => -15,
        "frequency"          => -10,
        "period"             => -10,
        "dutyCycle"          => -10,
        "pulseDuration"      => -10,
        "pwmTransition"      => -1,
        "enabledAtPowerOn"   => -15,
        "dutyCycleAtPowerOn" => -10
    ],
    "PwmPowerSource" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "powerMode"          => -72
    ],
    "QuadratureDecoder" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => -10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "speed"              => 10,
        "decoding"           => -5,
        "edgesPerCycle"      => -14
    ],
    "RangeFinder" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "rangeFinderMode"    => -73,
        "timeFrame"          => -6,
        "quality"            => 4,
        "hardwareCalibration" => -74,
        "currentTemperature" => 10,
        "command"            => -1
    ],
    "RealTimeClock" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unixTime"           => -35,
        "dateTime"           => 1,
        "utcOffset"          => -8,
        "timeSet"            => 15,
        "disableHostSync"    => -15
    ],
    "RefFrame" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "mountPos"           => -14,
        "bearing"            => -10,
        "calibrationParam"   => -13,
        "fusionMode"         => -75
    ],
    "Relay" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "state"              => -76,
        "stateAtPowerOn"     => -77,
        "maxTimeOnStateA"    => -6,
        "maxTimeOnStateB"    => -6,
        "output"             => -5,
        "pulseTimer"         => -6,
        "delayedPulseTimer"  => -78,
        "countdown"          => 6
    ],
    "SegmentedDisplay" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "displayedText"      => -1,
        "displayMode"        => -79
    ],
    "SerialPort" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "rxCount"            => 14,
        "txCount"            => 14,
        "errCount"           => 14,
        "rxMsgCount"         => 14,
        "txMsgCount"         => 14,
        "lastMsg"            => 1,
        "currentJob"         => -1,
        "startupJob"         => -1,
        "jobMaxTask"         => 14,
        "jobMaxSize"         => 14,
        "command"            => -1,
        "protocol"           => -49,
        "voltageLevel"       => -80,
        "serialMode"         => -81
    ],
    "Servo" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "position"           => -8,
        "enabled"            => -15,
        "range"              => -4,
        "neutral"            => -82,
        "move"               => -29,
        "positionAtPowerOn"  => -8,
        "enabledAtPowerOn"   => -15
    ],
    "SpiPort" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "rxCount"            => 14,
        "txCount"            => 14,
        "errCount"           => 14,
        "rxMsgCount"         => 14,
        "txMsgCount"         => 14,
        "lastMsg"            => 1,
        "currentJob"         => -1,
        "startupJob"         => -1,
        "jobMaxTask"         => 14,
        "jobMaxSize"         => 14,
        "command"            => -1,
        "protocol"           => -49,
        "voltageLevel"       => -80,
        "spiMode"            => -83,
        "ssPolarity"         => -84,
        "shiftSampling"      => -5
    ],
    "StepperMotor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "motorState"         => 56,
        "diags"              => 85,
        "stepPos"            => -86,
        "speed"              => 10,
        "pullinSpeed"        => -10,
        "maxAccel"           => -10,
        "maxSpeed"           => -10,
        "stepping"           => -87,
        "overcurrent"        => -14,
        "tCurrStop"          => -14,
        "tCurrRun"           => -14,
        "alertMode"          => -88,
        "auxMode"            => -89,
        "auxSignal"          => -8,
        "command"            => -1
    ],
    "Temperature" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "sensorType"         => -90,
        "signalValue"        => 10,
        "signalUnit"         => 1,
        "command"            => -1
    ],
    "Tilt" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "bandwidth"          => -14,
        "axis"               => 31
    ],
    "Tvoc" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Voc" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8
    ],
    "Voltage" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => 1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "enabled"            => -15
    ],
    "VoltageOutput" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "currentVoltage"     => -10,
        "voltageTransition"  => -32,
        "voltageAtStartUp"   => -10
    ],
    "WakeUpMonitor" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "powerDuration"      => -14,
        "sleepCountdown"     => -14,
        "nextWakeUp"         => -35,
        "wakeUpReason"       => 91,
        "wakeUpState"        => -92,
        "rtcTime"            => 35
    ],
    "WakeUpSchedule" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "minutesA"           => -93,
        "minutesB"           => -93,
        "hours"              => -94,
        "weekDays"           => -95,
        "monthDays"          => -96,
        "months"             => -97,
        "nextOccurence"      => 35
    ],
    "Watchdog" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "state"              => -76,
        "stateAtPowerOn"     => -77,
        "maxTimeOnStateA"    => -6,
        "maxTimeOnStateB"    => -6,
        "output"             => -5,
        "pulseTimer"         => -6,
        "delayedPulseTimer"  => -78,
        "countdown"          => 6,
        "autoStart"          => -5,
        "running"            => -5,
        "triggerDelay"       => -6,
        "triggerDuration"    => -6,
        "lastTrigger"        => 14
    ],
    "WeighScale" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "unit"               => -1,
        "currentValue"       => 10,
        "lowestValue"        => -10,
        "highestValue"       => -10,
        "currentRawValue"    => 10,
        "logFrequency"       => -11,
        "reportFrequency"    => -11,
        "advMode"            => -12,
        "calibrationParam"   => -13,
        "resolution"         => -10,
        "sensorState"        => 8,
        "excitation"         => -57,
        "tempAvgAdaptRatio"  => -10,
        "tempChgAdaptRatio"  => -10,
        "compTempAvg"        => 10,
        "compTempChg"        => 10,
        "compensation"       => 10,
        "zeroTracking"       => -10,
        "command"            => -1
    ],
    "Wireless" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9,
        "linkQuality"        => 4,
        "ssid"               => 1,
        "channel"            => 14,
        "security"           => 98,
        "message"            => 23,
        "wlanConfig"         => -99,
        "wlanState"          => 100
    ],
    "TestSuite" => [
        "logicalName"        => -1,
        "advertisedValue"    => -9
    ],
    "DeviceInfo" => [
        "serialNumber"       => 101,
        "logicalName"        => 101,
        "productName"        => 101,
        "productId"          => 2,
        "networkUrl"         => 102,
        "beacon"             => 5,
        "index"              => 8
    ],
    "Provider" => [
        "baseType"           => 103,
        "hardwareId"         => 104,
        "logicalName"        => 101,
        "advertisedValue"    => 105,
        "index"              => 8
    ]
];
$ApiTypes = ["","Text","XWord","enumFlashSettings","Percent","enumOnOff","Time","UsedCurrent","Int","PubText","MeasureVal","YFrequency","enumAdvertisingMode","CalibParams","UInt31","enumBool","UInt","enumInputType","ValueRange","MACAddress","enumBtState","enumCellType","IMSI","YFSText","PinPassword","RadioConfig","enumServiceScope","APNPassword","U24Color","Move","enumLedType","enumAxis","AnyFloatTransition","enumLoopPwrState","enumDaisyState","UTCTime","enumOffOnPending","BitByte","DigitalIODiags","enumIOVoltage","enumOrientation","enumDisplayType","enumDualPwrState","enumDualPwrControl","enumSignalSampling","enumGPSCoordinateSystem","enumGPSConstellation","enumPortState","BaudRate","Protocol","enumI2cVoltageLevel","I2cMode","InputChainDiags","enumBlink","enumLightSensorTypeAll","BinaryBuffer","enumStepperState","enumExcitationMode","enumMotorState","enumReadiness","IPAddress","IPConfig","UserPassword","AdminPassword","enumHTTPMethod","enumCallbackEncoding","Credentials","CallbackSchedule","enumPowerOuputVoltage","enumVoltageSense","enumProximityReportModeType","enumPwmReportModeType","enumPwmPwrState","enumRangeFinderMode","RangeFinderCalib","enumFusionModeTypeAll","enumToggle","enumToggleAtPowerOn","DelayedPulse","enumDisplayMode","enumSerialVoltageLevel","SerialMode","MicroSeconds","SpiMode","enumPolarity","StepperDiags","StepPos","enumSteppingMode","AlertMode","AuxMode","enumTempSensorTypeAll","enumWakeUpReason","enumWakeUpState","MinOfHalfHourBits","HoursOfDayBits","DaysOfWeekBits","DaysOfMonthBits","MonthsOfYearBits","enumWLANSec","WLANConfig","enumWLANState","HText","ApiURL","enumBaseType","HwId","PubStrText"];
$ApiEnums = [
    "enumBool"           => ["FALSE","TRUE"],
    "enumOnOff"          => ["OFF","ON"],
    "enumToggle"         => ["A","B"],
    "enumOffOnPending"   => ["OFF","ON","PENDING"],
    "enumBlink"          => ["STILL","RELAX","AWARE","RUN","CALL","PANIC"],
    "enumMotorState"     => ["IDLE","BRAKE","FORWD","BACKWD","LOVOLT","HICURR","HIHEAT","FAILSF"],
    "enumPwrCtrl"        => ["AUTO","FROM_USB","FROM_EXT","OFF"],
    "enumPwrState"       => ["OFF","FROM_USB","FROM_EXT"],
    "enumPwmPwrMode"     => ["USB_5V","USB_3V","EXT_V","OPNDRN"],
    "enumLoopPwrState"   => ["NOPWR","LOWPWR","POWEROK"],
    "enumPortState"      => ["OFF","OVRLD","ON","RUN","PROG"],
    "enumIOVoltage"      => ["USB_5V","USB_3V","EXT_V"],
    "enumBaseType"       => ["FUNCTION","SENSOR"],
    "enumReadiness"      => ["DOWN","EXISTS","LINKED","LAN_OK","WWW_OK"],
    "enumWLANState"      => ["DOWN","SCANNING","CONNECTED","REJECTED"],
    "enumBtState"        => ["DOWN","FREE","SEARCH","EXISTS","LINKED","PLAY"],
    "enumWLANSec"        => ["UNKNOWN","OPEN","WEP","WPA","WPA2"],
    "enumCellType"       => ["GPRS","EGPRS","WCDMA","HSDPA","NONE","CDMA","LTE_M","NB_IOT","EC_GSM_IOT"],
    "enumTempSensorTypeAll" => ["DIGITAL","TYPE_K","TYPE_E","TYPE_J","TYPE_N","TYPE_R","TYPE_S","TYPE_T","PT100_4WIRES","PT100_3WIRES","PT100_2WIRES","RES_OHM","RES_NTC","RES_LINEAR","RES_INTERNAL","IR","RES_PT1000","CHANNEL_OFF"],
    "enumPwmReportModeType" => ["PWM_DUTYCYCLE","PWM_FREQUENCY","PWM_PULSEDURATION","PWM_EDGECOUNT","PWM_PULSECOUNT","PWM_CPS","PWM_CPM","PWM_STATE","PWM_FREQ_CPS","PWM_FREQ_CPM","PWM_PERIODCOUNT"],
    "enumLightSensorTypeAll" => ["HUMAN_EYE","WIDE_SPECTRUM","INFRARED","HIGH_RATE","HIGH_ENERGY","HIGH_RESOLUTION"],
    "enumSignalSampling" => ["HIGH_RATE","HIGH_RATE_FILTERED","LOW_NOISE","LOW_NOISE_FILTERED","HIGHEST_RATE","AC"],
    "enumHTTPMethod"     => ["POST","GET","PUT"],
    "enumCallbackEncoding" => ["FORM","JSON","JSON_ARRAY","CSV","YOCTO_API","JSON_NUM","EMONCMS","AZURE","INFLUXDB","MQTT","YOCTO_API_JZON","PRTG","INFLUXDB_V2"],
    "enumDisplayType"    => ["MONO","GRAY","RGB"],
    "enumOrientation"    => ["LEFT","UP","RIGHT","DOWN"],
    "enumDisplayMode"    => ["DISCONNECTED","MANUAL","AUTO1","AUTO60"],
    "enumWakeUpReason"   => ["USBPOWER","EXTPOWER","ENDOFSLEEP","EXTSIG1","SCHEDULE1","SCHEDULE2"],
    "enumWakeUpState"    => ["SLEEPING","AWAKE"],
    "enumToggleAtPowerOn" => ["UNCHANGED","A","B"],
    "enumAxis"           => ["X","Y","Z"],
    "enumFusionModeTypeAll" => ["NDOF","NDOF_FMC_OFF","M4G","COMPASS","IMU","INCLIN_90DEG_1G8","INCLIN_90DEG_3G6","INCLIN_10DEG"],
    "enumSerialVoltageLevel" => ["OFF","TTL3V","TTL3VR","TTL5V","TTL5VR","RS232","RS485","TTL1V8","SDI12"],
    "enumI2cVoltageLevel" => ["OFF","3V3","1V8"],
    "enumPowerOuputVoltage" => ["OFF","OUT3V3","OUT5V","OUT4V7","OUT1V8"],
    "enumServiceScope"   => ["HOMENETWORK","ROAMING","NEVER","NEUTRALITY"],
    "enumGPSCoordinateSystem" => ["GPS_DMS","GPS_DM","GPS_D"],
    "enumGPSConstellation" => ["GNSS","GPS","GLONASS","GALILEO","GPS_GLONASS","GPS_GALILEO","GLONASS_GALILEO"],
    "enumPolarity"       => ["ACTIVE_LOW","ACTIVE_HIGH"],
    "enumStepperState"   => ["ABSENT","ALERT","HI_Z","STOP","RUN","BATCH"],
    "enumSteppingMode"   => ["MICROSTEP16","MICROSTEP8","MICROSTEP4","HALFSTEP","FULLSTEP"],
    "enumRangeFinderMode" => ["DEFAULT","LONG_RANGE","HIGH_ACCURACY","HIGH_SPEED"],
    "enumDaisyState"     => ["READY","IS_CHILD","FIRMWARE_MISMATCH","CHILD_MISSING","CHILD_LOST"],
    "enumProximityReportModeType" => ["NUMERIC","PRESENCE","PULSECOUNT"],
    "enumExcitationMode" => ["OFF","DC","AC"],
    "enumVoltageSense"   => ["INT","EXT"],
    "enumLedType"        => ["RGB","RGBW","WS2811"],
    "enumAdvertisingMode" => ["IMMEDIATE","PERIOD_AVG","PERIOD_MIN","PERIOD_MAX"],
    "enumInputType"      => ["ANALOG_FAST","DIGITAL4","ANALOG_SMOOTH"],
    "enumFlashSettings"  => ["LOADED","SAVED","MODIFIED"]
];
// Decode an attribute value received from REST API
function ApiRestDecodeAttribute(int $attrtype, string $ystr)
{
    global $ApiTypes, $ApiEnums;
    $typename = $ApiTypes[abs($attrtype)];
    if(isset($ApiEnums[$typename])) {
        return parseEnum($ystr, $ApiEnums[$typename]);
    }
    switch(abs($attrtype)) {
    case 2:                             // XWord
        return parseUInt($ystr);
    case 10:                            // MeasureVal
        return parseMeasure($ystr);
    case 28:                            // U24Color
        return parseUInt($ystr);
    case 29:                            // Move
        return parseMove($ystr);
    case 78:                            // DelayedPulse
        return parseMove($ystr);
    case 86:                            // StepPos
        return parseStepPos($ystr);
    default:
        if(is_numeric($ystr)) {
            return floatVal($ystr);
        }
        return $ystr;
    }
}
// Encode an attribute value for setting a new value using REST API
function ApiRestEncodeAttribute(int $attrtype,  $php_val): string
{
    switch(abs($attrtype)) {
    case 2:                             // XWord
        return sprintf("0x%04x", $php_val);
    case 10:                            // MeasureVal
        return strval(round($php_val * 65536.0));
    case 28:                            // U24Color
        return sprintf("0x%06x", $php_val);
    case 29:                            // Move
        return $php_val["target"].':'.$php_val["ms"];
    case 78:                            // DelayedPulse
        return $php_val["target"].':'.$php_val["ms"];
    case 86:                            // StepPos
        return strval(round($php_val * 100.0)/100.0);
    default:
        return strVal($php_val);
    }
}
// Decode an attribute value received from JSON API
function ApiJsonDecodeAttribute( $json_val, int $attrtype)
{
    switch(abs($attrtype)) {
    case 10:                            // MeasureVal
        return round($json_val / 65.536) / 1000.0;
    case 86:                            // StepPos
        return $json_val / 16.0;
    default:
        return $json_val;
    }
}
// Encode an attribute value for pushing as JSON API
function ApiJsonEncodeAttribute(VHubServerHTTPRequest $httpReq,  $php_val, int $attrtype)
{
    switch(abs($attrtype)) {
    case 10:                            // MeasureVal
        return round($php_val * 65536.0);
    case 24:                            // PinPassword
        return APIPassword($httpReq, $php_val);
    case 27:                            // APNPassword
        return APIPassword($httpReq, $php_val);
    case 62:                            // UserPassword
        return APIPassword($httpReq, $php_val);
    case 63:                            // AdminPassword
        return APIPassword($httpReq, $php_val);
    case 86:                            // StepPos
        return round($php_val * 16.0);
    default:
        return $php_val;
    }
}
// Encode an attribute value for pushing as TXT (or XML) API
function ApiTxtEncodeAttribute(VHubServerHTTPRequest $httpReq,  $val, int $attrtype): string
{
    global $ApiTypes, $ApiEnums;
    $typename = $ApiTypes[abs($attrtype)];
    if(isset($ApiEnums[$typename])) {
        if(isset($ApiEnums[$typename][$val])) {
            return $ApiEnums[$typename][$val];
        }
        return $typename.'_'.$val;
    }
    switch(abs($attrtype)) {
    case 2:                             // XWord
        return sprintf("0x%04x", $val);
    case 4:                             // Percent
        return $val.'%';
    case 6:                             // Time
        return ($val / 1000.0).' [s]';
    case 7:                             // UsedCurrent
        return ($val).' [mA]';
    case 10:                            // MeasureVal
        return strval(round($val * 1000.0) / 1000.0);
    case 24:                            // PinPassword
        return APIPassword($httpReq, $val);
    case 27:                            // APNPassword
        return APIPassword($httpReq, $val);
    case 28:                            // U24Color
        return sprintf("0x%06x", $val);
    case 29:                            // Move
        return ($val->moving ? "{$val->target} in {$val->ms} [ms]" : 'none');
    case 37:                            // BitByte
        return APIBitString("11111111", $val);
    case 38:                            // DigitalIODiags
        return APIBitString("012345678TP", $val);
    case 48:                            // BaudRate
        return ($val).' [kbps]';
    case 52:                            // InputChainDiags
        return APIBitString("NW5CDLRT", $val);
    case 62:                            // UserPassword
        return APIPassword($httpReq, $val);
    case 63:                            // AdminPassword
        return APIPassword($httpReq, $val);
    case 78:                            // DelayedPulse
        return ($val->moving ? "{$val->target} in {$val->ms} [ms]" : 'none');
    case 82:                            // MicroSeconds
        return $val.' [us]';
    case 85:                            // StepperDiags
        return APIBitString("VCTXLHOStho!", $val);
    case 93:                            // MinOfHalfHourBits
        return APIBitString("012345678901234567890123456789", $val);
    case 94:                            // HoursOfDayBits
        return APIBitString("012345678901234567890123", $val);
    case 95:                            // DaysOfWeekBits
        return APIBitString("MTWTFSS", $val);
    case 96:                            // DaysOfMonthBits
        return APIBitString("1234567890123456789012345678901", $val);
    case 97:                            // MonthsOfYearBits
        return APIBitString("JFMAMJJASOND", $val);
    default:
        return strVal($val);
    }
}

class APINode{    protected $server;    public $name;    protected $subnodes;    protected $values;    // immediate properties    protected $types;     // immediate properties type, for edition    protected $modified;   // true if the node (or subnode) state needs to be saved    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        for($fclasslen = strlen($name); $fclasslen > 0; $fclasslen--) {            if(!ctype_digit($name[$fclasslen-1])) break;        }        $this->server = $server;        $this->name = $name;        $this->fclass = substr(ucfirst($name), 0, $fclasslen);        $this->subnodes = [];   // Associative array        $this->values = [];     // Associative array        $this->types = [];      // Associative array        $this->modified = false;    }    protected function setupTypes(VHubServerHTTPRequest $httpReq)    {        foreach($this->values as $name => $value) {            if(!isset($this->types[$name])) {                $this->types[$name] = $this->server->apiroot->getAttrType($httpReq, $this->fclass, $name, $value);            }        }    }    public function addSubnode(string $name, APINode $subnode)    {        $this->subnodes[$name] = $subnode;    }    public function hasSubnode(string $name): bool    {        return isset($this->subnodes[$name]);    }    public function subnodeNames(): array    {        return array_keys($this->subnodes);    }    public function subnode(string $name): APINode    {        return $this->subnodes[$name];    }    public function getattr(string $name)    {        return $this->values[$name];    }    public function setattr(string $name, string $value)    {        if(!isset($this->types[$name])) {            // unknown attribute, assume read-only            return;        }        $attrtype = $this->types[$name];        if($attrtype >= 0) {            // read-only attribute            return;        }        $this->values[$name] = ApiRestDecodeAttribute($attrtype, $value);    }    public function search(array $nodepath, array $ctxpath): array    {        $apinode = $this;        for($offset = 0; $offset < sizeof($nodepath); $offset++) {            $key = $nodepath[$offset];            if(isset($apinode->subnodes[$key])) {                $apinode = $apinode->subnodes[$key];            } else if(sizeof($ctxpath) == 0 && sizeof($apinode->values) > 0) {                if(isset($apinode->values[$key])) {                    return [ $apinode, $apinode, $key ];                } else {                    return [ $apinode, $apinode, null ];                }            } else {                return [ null, null, null ];            }        }        $ctxnode = $apinode;        for($offset = 0; $offset < sizeof($ctxpath); $offset++) {            $key = $ctxpath[$offset];            if(isset($ctxnode->subnodes[$key])) {                $ctxnode = $ctxnode->subnodes[$key];            } else if(sizeof($ctxnode->values) > 0) {                if(isset($ctxnode->values[$key])) {                    return [ $apinode, $ctxnode, $key ];                } else {                    return [ $apinode, $ctxnode, null ];                }            } else {                return [ $apinode, null, null ];            }        }        return [ $apinode, $ctxnode, null ];    }    public function loadState(VHubServerHTTPRequest $httpReq,  $data, bool $detectChanges): bool    {        foreach($data as $name => $value) {            if($name == 'VirtualHub4web' || $name == 'FileList') {                // VirtualHub4web own data is handled separately, just ignore here            } else if((is_object($value) || is_array($value)) && !isset($this->values['advertisedValue'])) {                if (!isset($this->subnodes[$name])) {                    // Automatically instantiate typed dynamic nodes                    switch($name) {                        case 'dataLogger':                            $moduleData = (isset($data->module) ? $data->module : $data['module']);                            $this->subnodes[$name] = new APIDataLoggerNode($httpReq, $this->server, $name, $moduleData);                            break;                        case 'services':                            $this->subnodes[$name] = new APIServicesNode($httpReq, $this->server, $name);                            break;                        default:                            if(is_object($value) && isset($value->reportFrequency)) {                                $this->subnodes[$name] = new APISensorNode($httpReq, $this->server, $name);                            } else {                                $this->subnodes[$name] = new APINode($httpReq, $this->server, $name);                            }                    }                    if($detectChanges) $this->modified = true;                }                $subres = $this->subnodes[$name]->loadState($httpReq, $value, $detectChanges);                if($detectChanges && $subres) $this->modified = true;            } else {                if(!isset($this->types[$name])) {                    if($detectChanges) $this->modified = true;                    $this->types[$name] = $this->server->apiroot->getAttrType($httpReq, $this->fclass, $name, $value);                    $decoded = ApiJsonDecodeAttribute($value, $this->types[$name]);                    $this->values[$name] = $decoded;                } else {                    $decoded = ApiJsonDecodeAttribute($value, $this->types[$name]);                    if($this->values[$name] != $decoded) {                        if($detectChanges) $this->modified = true;                        $this->values[$name] = $decoded;                    }                }            }        }        return $this->modified;    }    public function hasChanged(): bool    {        return $this->modified;    }    public function saveState(): array    {        $res = [];        foreach($this->subnodes as $name => $subnode) {            $res[$name] = $subnode->saveState();        }        foreach($this->values as $name => $value) {            $pseudoHttpReq = new VHubServerHTTPRequest(true);            $pseudoHttpReq->setAuthUser('admin');            $res[$name] = ApiJsonEncodeAttribute($pseudoHttpReq, $value, $this->types[$name]);        }        $this->modified = false;        return $res;    }    public function printJSON(VHubServerHTTPRequest $httpReq)    {        $isleaf = sizeof($this->values) > 0;        $sep = '{';        if($isleaf) {            foreach($this->values as $key => $value) {                $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);                $httpReq->put("{$sep}\"{$key}\":".json_encode($jsonval, JSON_UNESCAPED_SLASHES));                $sep = ',';            }        } else {            if(sizeof($this->subnodes) == 0) {                $httpReq->put('{}');                return;            }            foreach($this->subnodes as $name => $subnode) {                $httpReq->put("{$sep}\"{$name}\":");                $subnode->printJSON($httpReq);                $sep = ',';            }        }        $httpReq->put('}');    }    public function printJZON(VHubServerHTTPRequest $httpReq)    {        $isleaf = sizeof($this->values) > 0;        $sep = '[';        if($isleaf) {            foreach($this->values as $key => $value) {                $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);                $httpReq->put($sep.json_encode($jsonval, JSON_UNESCAPED_SLASHES ));                $sep = ',';            }        } else {            if(sizeof($this->subnodes) == 0) {                $httpReq->put('[]');                return;            }            foreach($this->subnodes as $subnode) {                $httpReq->put($sep);                $subnode->printJZON($httpReq);                $sep = ',';            }        }        $httpReq->put(']');    }    public function printJSONValue(VHubServerHTTPRequest $httpReq, string $key)    {        $value = $this->values[$key];        $jsonval = ApiJsonEncodeAttribute($httpReq, $value, $this->types[$key]);        $httpReq->put(json_encode($jsonval, JSON_UNESCAPED_SLASHES));    }    public function printHTML(VHubServerHTTPRequest $httpReq, string $label)    {        $isleaf = sizeof($this->values) > 0;        $cssclass = ($isleaf ? "interface" : "folder");        $httpReq->put("<dl name='{$label}' class='{$cssclass}'><h4>{$label} <a href='javascript:reload()'>refresh</a></h4>\n");        if($isleaf) {            foreach($this->values as $key => $value) {                $attrtype = $this->types[$key];                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);                if($key == 'networkUrl') {                    $relUrl = substr($txtval, 1);                    $txtval = "<a href='{$relUrl}'>Browse REST API</a>";                }                $httpReq->put("<div name='{$key}'><dt>{$key}:</dt><dd>{$txtval}</dd>");                if($attrtype < 0) {                    $attrtype = abs($attrtype);                    $httpReq->put("<a href='javascript:' onclick='edit(this,{$attrtype})'>edit</a></div>\n");                } else {                    $httpReq->put('</div>');                }            }        } else {            foreach($this->subnodes as $name => $subnode) {                $subnode->printHTML($httpReq, $name);            }        }        $httpReq->put("</dl>");    }    public function printHTMLValue(VHubServerHTTPRequest $httpReq, string $key)    {        $value = $this->values[$key];        $attrtype = $this->types[$key];        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);        $httpReq->put($txtval);    }    public function printTXT(VHubServerHTTPRequest $httpReq, string $label)    {        $isleaf = sizeof($this->values) > 0;        $httpReq->put("*** {$label}\r\n");        if($isleaf) {            foreach($this->values as $key => $value) {                $attrtype = $this->types[$key];                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);                if(is_string($value)) {                    $txtval = "\"{$txtval}\"";                }                $httpReq->put("{$key}: {$txtval}\r\n");            }        } else {            foreach($this->subnodes as $name => $subnode) {                $httpReq->put("=> {$name}\r\n");            }            foreach($this->subnodes as $name => $subnode) {                $httpReq->put("\r\n");                $subnode->printTXT($httpReq, $name);            }        }    }    public function printTXTValue(VHubServerHTTPRequest $httpReq, string $key)    {        $value = $this->values[$key];        $attrtype = $this->types[$key];        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);        if(is_string($value)) {            $txtval = "\"{$txtval}\"";        }        $httpReq->put($txtval);    }    public function printXML(VHubServerHTTPRequest $httpReq, string $label)    {        $isleaf = sizeof($this->values) > 0;        $httpReq->put("<{$label}>\r\n");        if($isleaf) {            foreach($this->values as $key => $value) {                $attrtype = $this->types[$key];                $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);                $httpReq->put("<{$key}>{$txtval}</{$key}>\r\n");            }        } else {            foreach($this->subnodes as $name => $subnode) {                $subnode->printXML($httpReq, $name);            }        }        $httpReq->put("</{$label}>\r\n");    }    public function printXMLValue(VHubServerHTTPRequest $httpReq, string $key)    {        $value = $this->values[$key];        $attrtype = $this->types[$key];        $txtval = ApiTxtEncodeAttribute($httpReq, $value, $attrtype);        $httpReq->put($txtval);    }    public function isSensor(): bool    {        return false;    }}class APIArrayNode extends APINode{    public function saveState(): array    {        $res = [];        foreach($this->subnodes as $subnode) {            $res[] = $subnode->saveState();        }        $this->modified = false;        return $res;    }    public function printJSON(VHubServerHTTPRequest $httpReq)    {        if(sizeof($this->subnodes) == 0) {            $httpReq->put('[]');            return;        }        $sep = '[';        foreach($this->subnodes as $subnode) {            $httpReq->put($sep);            $subnode->printJSON($httpReq);            $sep = ',';        }        $httpReq->put(']');    }    public function printJZON(VHubServerHTTPRequest $httpReq)    {        if(sizeof($this->subnodes) == 0) {            $httpReq->put('[]');            return;        }        $sep = '[';        foreach($this->subnodes as $name => $subnode) {            $httpReq->put($sep);            $subnode->printJZON($httpReq);            $sep = ',';        }        $httpReq->put(']');    }    public function printHTML(VHubServerHTTPRequest $httpReq, string $label)    {        $httpReq->put("<dl name='{$label}' class='folder'><h4>{$label} <a href='javascript:reload()'>refresh</a></h4>\n");        foreach($this->subnodes as $index => $subnode) {            $subnode->printHTML($httpReq, "entry #{$index}");        }    }    public function printTXT(VHubServerHTTPRequest $httpReq, string $label)    {        foreach($this->subnodes as $index => $subnode) {            $subnode->printTXT($httpReq, "{$label}[{$index}]");        }    }    public function printXML(VHubServerHTTPRequest $httpReq, string $label)    {        if($label == 'whitePages') {            $sublabel = 'whitePage';        } else {            $sublabel = 'ypEntry';        }        $httpReq->put("<{$label}>\r\n");        foreach($this->subnodes as $subnode) {            $subnode->printXML($httpReq, $sublabel);        }        $httpReq->put("</{$label}>\r\n");    }}class APIModuleNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->values['productName'] = '';        $this->values['serialNumber'] = '';        $this->values['logicalName'] = '';        $this->values['productId'] = 0;        $this->values['productRelease'] = 0;        $this->values['firmwareRelease'] = '';        $this->values['persistentSettings'] = 0;        $this->values['luminosity'] = 0;        $this->values['beacon'] = 0;        $this->values['upTime'] = 0;        $this->values['usbCurrent'] = 0;        $this->values['rebootCountdown'] = 0;        $this->values['userVar'] = 0;        $this->setupTypes($httpReq);    }}class APIDeviceModuleNode extends APIModuleNode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        global $ApiDef;        parent::__construct($httpReq, $server, $name);        $this->values['lastSeen'] = 0;        $this->values['parentHub'] = '';        $this->values['parentIP'] = '';        $this->types['lastSeen'] = $ApiDef['Watchdog']['lastTrigger'];        $this->types['parentHub'] = $ApiDef['Module']['serialNumber'];        $this->types['parentIP'] = $ApiDef['Network']['ipAddress'];    }}class APICloudModuleNode extends APIModuleNode{    protected $cachedAttributes;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->cachedAttributes = [ 'logicalName', 'luminosity', 'beacon', 'userVar', 'persistentSettings' ];        $this->values['productName'] = 'VirtualHub-4web';        $this->values['productId'] = 0xc10d;        $this->values['productRelease'] = 1;        $this->values['upTime'] = round(gettimeofday(true) * 1000.0) & 0xffffffff;        $versionDotPos = strrpos(VERSION, '.');        if($versionDotPos !== FALSE) {            $this->values['firmwareRelease'] = substr(VERSION, $versionDotPos+1);        } else {            $this->values['firmwareRelease'] = VERSION;        }    }    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)    {        $this->values['serialNumber'] = $cloudConf->serialNumber;        foreach($this->cachedAttributes as $key) {            if(isset($cloudConf->valuesCache[$key])) {                $this->values[$key] = $cloudConf->valuesCache[$key];            }        }    }    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)    {        foreach($this->cachedAttributes as $key) {            if(isset($cloudConf->valuesCache[$key]) && $cloudConf->valuesCache[$key] != $this->values[$key]) {                $changes[$key] = $this->values[$key];            }        }    }}class APIFunctionNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->values['logicalName'] = '';        $this->values['advertisedValue'] = '';        $this->setupTypes($httpReq);    }}class APISensorNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->values['logicalName'] = '';        $this->values['unit'] = '';        $this->values['currentValue'] = 0;        $this->values['lowestValue'] = 0;        $this->values['highestValue'] = 0;        $this->values['currentRawValue'] = 0;        $this->values['logFrequency'] = '1/s';        $this->values['reportFrequency'] = 'OFF';        $this->values['advMode'] = 0;        $this->values['calibrationParam'] = '0,';        $this->values['resolution'] = 0.01;        $this->values['sensorState'] = 1;        $this->setupTypes($httpReq);    }    public function isSensor(): bool    {        return true;    }    // Return current sensor value, if valid    //    public function getSensorValue(): float    {        $avgVal = NAN;        if($this->values['sensorState'] == 0) {            $avgVal = $this->values['currentValue'];        }        return $avgVal;    }}class APINetworkNode extends APIFunctionNode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->values['readiness'] = 0;        $this->values['macAddress'] = '00:00:00:00:00:00';        $this->values['ipAddress'] = '0.0.0.0';        $this->values['subnetMask'] = '0.0.0.0';        $this->values['router'] = '0.0.0.0';        $this->values['ipConfig'] = 'DHCP:169.254.95.6/16/169.254.0.1';        $this->values['primaryDNS'] = '0.0.0.0';        $this->values['secondaryDNS'] = '0.0.0.0';        $this->values['ntpServer'] = '0.0.0.0';        $this->values['userPassword'] = '';        $this->values['adminPassword'] = '';        $this->values['httpPort'] = 4444;        $this->values['defaultPage'] = '';        $this->values['discoverable'] = 0;        $this->values['wwwWatchdogDelay'] = 0;        $this->values['callbackUrl'] = '';        $this->values['callbackMethod'] = 0;        $this->values['callbackEncoding'] = 0;        $this->values['callbackCredentials'] = ':';        $this->values['callbackInitialDelay'] = 0;        $this->values['callbackSchedule'] = 'after 20s/60s';        $this->values['callbackMinDelay'] = 20;        $this->values['callbackMaxDelay'] = 60;        $this->values['poeCurrent'] = 0;        $this->setupTypes($httpReq);    }}class APICloudNetworkNode extends APINetworkNode{    protected $cachedAttributes;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->cachedAttributes = ['defaultPage', 'userPassword', 'adminPassword'];        $this->values['ipAddress'] = $httpReq->getServerIP();        $this->values['httpPort'] = $httpReq->getServerPort();    }    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)    {        $this->values['logicalName'] = $cloudConf->valuesCache['networkName'];        foreach ($this->cachedAttributes as $key) {            if (isset($cloudConf->valuesCache[$key])) {                $this->values[$key] = $cloudConf->valuesCache[$key];            }        }    }    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)    {        if ($cloudConf->valuesCache['networkName'] != $this->values['logicalName']) {            $changes['networkName'] = $this->values['logicalName'];        }        foreach ($this->cachedAttributes as $key) {            if (isset($cloudConf->valuesCache[$key]) && $cloudConf->valuesCache[$key] != $this->values[$key]) {                $changes[$key] = $this->values[$key];            }        }    }    public function setattr(string $name, string $value)    {        if(substr($name,-8) == 'Password') {            $mustHash = (strlen($value) != 24);            if(!$mustHash) {                $decoded = base64_decode($value, true); // strict decode                if($decoded === false || strlen($decoded) != 17 || ord($decoded[0]) != 0) {                    // non a Base64-encoded hashed password                    $mustHash = true;                }            }            if($mustHash && $value != '') {                // for safety reasons, don't save the password but pre-hash it with the realm                // in order to prevent easy password recovery from the configuration file                $user = substr($name, 0, -8);                $realm = $this->server->apiroot->cloudConf->authRealm;                $value = base64_encode(chr(0).md5($user . ':' . $realm . ':' . $value, true));            }        }        parent::setattr($name, $value);    }}class APICloudFilesNode extends APIFunctionNode{    protected $cachedAttributes;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->cachedAttributes = [ 'filesCount', 'freeSpace' ];        $this->values['filesCount'] = 0;        $this->values['freeSpace'] = FILES_MAX_SIZE;        $this->setupTypes($httpReq);    }    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)    {        $this->values['logicalName'] = $cloudConf->valuesCache['filesName'];        foreach($this->cachedAttributes as $key) {            if(isset($cloudConf->valuesCache[$key])) {                $this->values[$key] = $cloudConf->valuesCache[$key];            }        }    }    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)    {        if($cloudConf->valuesCache['filesName'] != $this->values['logicalName']) {            $changes['filesName'] = $this->values['logicalName'];        }        foreach($this->cachedAttributes as $key) {            if(!isset($cloudConf->valuesCache[$key]) || $cloudConf->valuesCache[$key] != $this->values[$key]) {                $changes[$key] = $this->values[$key];            }        }    }    public function updateStats(VHubServerHTTPRequest $httpReq, int $filesCount, int $totalSize)    {        $freeSpace = ($totalSize >= FILES_MAX_SIZE ? 0 : FILES_MAX_SIZE - $totalSize);        if($this->values['filesCount'] != $filesCount || $this->values['freeSpace'] != $freeSpace) {            $this->values['filesCount'] = $filesCount;            $this->values['freeSpace'] = $freeSpace;            $this->modified = true;        }    }}class APIDataLoggerNode extends APIFunctionNode{    protected $serial;    protected $deviceValues;    // actual values on the device    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name,  $moduleData)    {        parent::__construct($httpReq, $server, $name);        // Setup emulated values        $this->serial = (isset($moduleData->serialNumber) ? $moduleData->serialNumber : $moduleData['serialNumber']);        $this->values['logicalName'] = '';        $this->values['advertisedValue'] = 'ON';        $this->values['currentRunIndex'] = 1;        $this->values['timeUTC'] = 0;        $this->values['recording'] = 1;        $this->values['autoStart'] = 1;        $this->values['beaconDriven'] = 0;        $this->values['usage'] = 1;        $this->values['clearHistory'] = 0;        $this->setupTypes($httpReq);        // Setup default device values as well        $this->deviceValues['logicalName'] = '';        $this->deviceValues['advertisedValue'] = '';        $this->deviceValues['currentRunIndex'] = 0;        $this->deviceValues['timeUTC'] = 0;        $this->deviceValues['recording'] = 0;        $this->deviceValues['autoStart'] = 0;        $this->deviceValues['beaconDriven'] = 0;        $this->deviceValues['usage'] = 0;        $this->deviceValues['clearHistory'] = 0;        $this->setupTypes($httpReq);    }    // We cache the device values for use in very specific cases    public function loadState(VHubServerHTTPRequest $httpReq,  $data, bool $detectChanges): bool    {        if(!$detectChanges) {            // This is an emulated datalogger, only reload the last known device UTC time            $this->values['timeUTC'] = (isset($data->timeUTC) ? $data->timeUTC : $data['timeUTC']);            // Compute current datalogger usage based on file list            $tarfile = $this->server->files->accessDeviceFiles($httpReq, $this->serial);            $knownFiles = $tarfile->knownFilesMatching('datalogger/*');            $usage = 0;            if(sizeof($knownFiles) == 0) {                foreach ($knownFiles as $tarObj) {                    $usage += $tarObj->contentSize;                }                $usage = intVal(round($usage / (DATAFILE_MAX_COUNT * DATAFILE_MAX_SIZE)));                if($usage < 1) {                    $usage = 1;                }            }            $this->values['usage'] = $usage;            return false;        }        foreach($data as $name => $value) {            $decoded = ApiJsonDecodeAttribute($value, $this->types[$name]);            if($this->deviceValues[$name] != $decoded) {                if($detectChanges) $this->modified = true;                $this->deviceValues[$name] = $decoded;            }        }        // When updating from device, mirror the last known device UTC time as well        $this->values['timeUTC'] = $this->deviceValues['timeUTC'];        return $this->modified;    }    // We cache the device values for later use    public function saveState(): array    {        $res = [];        foreach($this->deviceValues as $name => $value) {            $pseudoHttpReq = new VHubServerHTTPRequest(true);            $pseudoHttpReq->setAuthUser('admin');            $res[$name] = ApiJsonEncodeAttribute($pseudoHttpReq, $value, $this->types[$name]);        }        $this->modified = false;        return $res;    }}class APIWPRecordNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name, object $template)    {        parent::__construct($httpReq, $server, $name);        $this->fclass = 'DeviceInfo';        $this->values['serialNumber'] = $template->serialNumber;        $this->values['logicalName'] = $template->logicalName;        $this->values['productName'] = $template->productName;        $this->values['productId'] = $template->productId;        $this->values['networkUrl'] = $template->networkUrl;        $this->values['beacon'] = $template->beacon;        $this->values['index'] = $template->index;        $this->setupTypes($httpReq);    }    public function loadState(VHubServerHTTPRequest $httpReq,  $data, bool $detectChanges): bool    {        if($detectChanges) {            if ($this->values['logicalName'] != $data->logicalName || $this->values['beacon'] != $data->beacon) {                $this->values['logicalName'] = $data->logicalName;                $this->values['beacon'] = $data->beacon;                $this->server->notif->appendModuleNotification($httpReq, $this->values);                $this->modified = true;            }            foreach ($data as $name => $value) {                if ($this->values[$name] != $value) {                    $this->values[$name] = $value;                    $this->modified = true;                }            }            return $this->modified;        } else {            foreach ($data as $name => $value) {                $this->values[$name] = $value;            }            return false;        }    }}class APIWhitePagesNode extends APIArrayNode{    protected $arrayIndexBySerial;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->arrayIndexBySerial = [];    }    public function loadState(VHubServerHTTPRequest $httpReq,  $wpdef, bool $detectChanges): bool    {        foreach($wpdef as $wprec) {            $wpentry = (object)$wprec;            $serial = $wpentry->serialNumber;            if(!$this->server->apiroot->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {                // unknown device, ignore services                continue;            }            if(isset($this->arrayIndexBySerial[$serial])) {                $arrayIndex = $this->arrayIndexBySerial[$serial];                $changed = $this->subnodes[$arrayIndex]->loadState($httpReq, $wpentry, $detectChanges);                if($changed && $detectChanges) {                    $this->modified = true;                }            } else {                $subnode = new APIWPRecordNode($httpReq, $this->server, $serial, $wpentry);                $this->arrayIndexBySerial[$serial] = sizeof($this->subnodes);                $this->subnodes[] = $subnode;                if($detectChanges) {                    $this->modified = true;                    $cloudSerial = $this->server->apiroot->cloudConf->serialNumber;                    $this->server->notif->appendModuleArrivalNotifications($httpReq, $cloudSerial, $subnode->values);                }            }        }        // FIXME: detect device removal?        return $this->modified;    }    public function sortServices(VHubServerHTTPRequest $httpReq)    {        usort($this->subnodes, function(APIWPRecordNode $a,APIWPRecordNode $b) { return $a->values['index'] - $b->values['index']; });    }    public function saveStateForSerial(string $serial): array    {        $res = [];        if(isset($this->arrayIndexBySerial[$serial])) {            $arrayIndex = $this->arrayIndexBySerial[$serial];            $res[] = $this->subnodes[$arrayIndex]->saveState();        }        return $res;    }}class APIYPRecordNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $hwId, object $template)    {        parent::__construct($httpReq, $server, $hwId);        $this->fclass = 'Provider';        $this->values['baseType'] = $template->baseType;        $this->values['hardwareId'] = $template->hardwareId;        $this->values['logicalName'] = $template->logicalName;        $this->values['advertisedValue'] = $template->advertisedValue;        $this->values['index'] = $template->index;        $this->setupTypes($httpReq);        // Update global index of funydx by hwid        $this->server->apiroot->funYdxByHwId[$template->hardwareId] = $template->index;    }    public function loadState(VHubServerHTTPRequest $httpReq,  $data, bool $detectChanges): bool    {        if($detectChanges) {            $funchanged = false;            if ($this->values['baseType'] != $data->baseType) {                $this->values['baseType'] = $data->baseType;                $funchanged = true;            }            if ($this->values['logicalName'] != $data->logicalName) {                $this->values['logicalName'] = $data->logicalName;                $funchanged = true;            }            if ($this->values['index'] != $data->index) {                $this->values['index'] = $data->index;                $funchanged = true;                // Update global index of funydx by hwid                $this->server->apiroot->funYdxByHwId[$this->values['hardwareId']] = $data->index;            }            if($funchanged) {                $this->server->notif->appendFunctionNameNotification($httpReq, $this->values);                $this->modified = true;            }            if ($this->values['advertisedValue'] != $data->advertisedValue) {                $this->values['advertisedValue'] = $data->advertisedValue;                $this->server->notif->appendFunctionValNotification($httpReq, $this->values);                $this->modified = true;            }            return $this->modified;        } else {            foreach ($data as $name => $value) {                $this->values[$name] = $value;            }            return false;        }    }}class APIYPCategNode extends APIArrayNode{    protected $arrayIndexByHardwareId;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->arrayIndexByHardwareId = [];    }    public function loadState(VHubServerHTTPRequest $httpReq,  $ypcateg, bool $detectChanges): bool    {        foreach($ypcateg as $yprec) {            $ypentry = (object)$yprec;            $hwId = $ypentry->hardwareId;            if(isset($this->arrayIndexByHardwareId[$hwId])) {                $arrayIndex = $this->arrayIndexByHardwareId[$hwId];                $changed = $this->subnodes[$arrayIndex]->loadState($httpReq, $ypentry, $detectChanges);                if($changed && $detectChanges) {                    $this->modified = true;                }            } else {                $parts = explode('.', $hwId);                $serial = $parts[0];                if(!$this->server->apiroot->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {                    // unknown device, ignore services                    continue;                }                $subnode = new APIYPRecordNode($httpReq, $this->server, $hwId, $ypentry);                $this->arrayIndexByHardwareId[$hwId] = sizeof($this->subnodes);                $this->subnodes[] = $subnode;                if($detectChanges) {                    $this->modified = true;                    $this->server->notif->appendFunctionNameNotification($httpReq, $subnode->values);                }            }        }        // FIXME: detect function removal        return $this->modified;    }    public function saveStateForHwIdPattern(string $pattern): array    {        $res = [];        foreach($this->subnodes as $yprecord) {            if(preg_match($pattern, $yprecord->values['hardwareId'])) {                $res[] = $yprecord->saveState();            }        }        return $res;    }}class APIYellowPagesNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);    }    public function loadState(VHubServerHTTPRequest $httpReq,  $ypdef, bool $detectChanges): bool    {        foreach($ypdef as $categ => $ypcateg) {            if(!isset($this->subnodes[$categ])) {                $this->addSubnode($categ, new APIYPCategNode($httpReq, $this->server, $categ));                $this->modified = true;            }            $categnode = $this->subnodes[$categ];            $changed = $categnode->loadState($httpReq, $ypcateg, $detectChanges);            if($changed) {                $this->modified = true;            }        }        return $this->modified;    }    public function saveStateForSerial(string $serial): array    {        $res = [];        $pattern = '~^'.$serial.'[.]~';        foreach($this->subnodes as $categ => $categnode) {            $subres = $categnode->saveStateForHwIdPattern($pattern);            if(sizeof($subres) > 0) {                $res[$categ] = $subres;            }        }        return $res;    }}class APIServicesNode extends APINode{    public $wp;    public $yp;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->wp = new APIWhitePagesNode($httpReq, $this->server, 'whitePages');        $this->yp = new APIYellowPagesNode($httpReq, $this->server, 'yellowPages');        $this->addSubnode('whitePages', $this->wp);        $this->addSubnode('yellowPages', $this->yp);    }    public function sortServices(VHubServerHTTPRequest $httpReq)    {        $this->wp->sortServices($httpReq);    }    public function saveStateForSerial(string $serial): array    {        return [            'whitePages' => $this->wp->saveStateForSerial($serial),            'yellowPages' => $this->yp->saveStateForSerial($serial)        ];    }}class APIDeviceAPINode extends APINode{    public $module;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->module = new APIDeviceModuleNode($httpReq, $this->server, 'module');        $this->addSubnode('module', $this->module);    }}class DeviceFileList{    protected $server;    protected $serial;    protected $modified;    protected $entries;    // Possible status for entries:    // - discovered: file discovered on device, to be downloaded to VirtualHub4web    // - uploaded: file uploaded to VirtualHub4web, to be uploaded to device    // - known: file exists both on device and in VirtualHub4web    // - deleting: file deleted on VirtualHub4web, to be deleted on device    // - deleted: file deleted on VirtualHub4web and deleted on device, expected to disappear    // - disappeared: file disappeared on device, to be deleted on VirtualHub4web    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $serial)    {        $this->server = $server;        $this->serial = $serial;        $this->modified = false;        $this->entries = [];    }    public function loadState(VHubServerHTTPRequest $httpReq, array $data)    {        for($i = 0; $i < sizeof($data); $i++) {            $this->entries[$data[$i]->name] = $data[$i];        }    }    public function saveState(): array    {        $res = [];        foreach($this->entries as $path => $entry) {            $res[] = $entry;        }        $this->modified = false;        return $res;    }    public function hasChanged(): bool    {        return $this->modified;    }    protected function setEntryState(VHubServerHTTPRequest $httpReq, string $filename, string $newState)    {        if(!isset($this->entries[$filename])) {            $this->entries[$filename] = new stdClass();            $this->entries[$filename]->name = $filename;            $this->entries[$filename]->size = 0;            $this->entries[$filename]->crc = 0;            $this->entries[$filename]->status = $newState;            $this->modified = true;            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} on {$this->serial} added in state {$newState}");        } else if($this->entries[$filename]->status != $newState) {            $this->entries[$filename]->status = $newState;            $this->modified = true;            VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} on {$this->serial} is now in state {$newState}");        }    }    // compare a known fileList to the current device filesystem    function compareToDevice(VHubServerHTTPRequest $httpReq, array $filerecs): bool    {        // first detect all changes compared to VirtualHub4web state        $foundOnDevice = [];        for($i = 0; $i < sizeof($filerecs); $i++) {            $entry = $filerecs[$i];            $foundOnDevice[$entry->name] = true;            if(!isset($this->entries[$entry->name])) {                // new entry                $entry->status = 'discovered';                $this->entries[$entry->name] = $entry;                $this->modified = true;                VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$entry->name} on {$this->serial} added in state {$entry->status}");                continue;            }            $existing = $this->entries[$entry->name];            VHubServer::Log($httpReq, LOG_FILESYNC, 5, "CompareToDevice {$entry->name} on {$this->serial}: status={$existing->status}");            switch($existing->status) {                case 'discovered':  // new file on device, not yet downloaded                    break;                case 'deleting':    // deletion is expected next time the device connects                    VHubServer::Log($httpReq, LOG_FILESYNC, 2, "File {$entry->name} on {$this->serial} is scheduled for deletion");                    $this->setEntryState($httpReq, $entry->name, 'deleted');                    break;                case 'deleted':     // deletion failed? retry                    VHubServer::Log($httpReq, LOG_FILESYNC, 2, "File deletion for {$entry->name} failed on {$this->serial}, retrying");                    $this->deleteOnDevice($httpReq, $entry->name);                    break;                case 'uploaded':                    if($entry->size == $existing->size && ($entry->crc & 0xffffffff) == ($existing->crc & 0xffffffff)) {                        // file on device is the same                        $this->setEntryState($httpReq, $entry->name, 'known');                    }                    break;                case 'disappeared':                case 'known':                    if($entry->size == $existing->size && ($entry->crc & 0xffffffff) == ($existing->crc & 0xffffffff)) {                        $this->setEntryState($httpReq, $entry->name, 'known');                    } else {                        // file has changed on device, must be downloaded                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$entry->name} has changed on {$this->serial}");                        $this->setEntryState($httpReq, $entry->name, 'discovered');                    }                    break;            }        }        foreach($this->entries as $filename => $entry) {            if(!isset($foundOnDevice[$filename])) {                switch($entry->status) {                    case 'discovered':  // new file on device, not yet downloaded, has disappeared                    case 'deleting':    // deletion is expected next time the device connects                    case 'deleted':     // file just deleted on device                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} is no more in {$this->serial}");                        unset($this->entries[$filename]);                        $this->modified = true;                        break;                    case 'uploaded':                        // expect new file to appear on device shortly                        break;                    case 'disappeared':                    case 'known':                        $this->setEntryState($httpReq, $filename, 'disappeared');                        break;                }            }        }        // Then process changes        foreach($this->entries as $filename => $entry) {            switch($entry->status) {                case 'discovered':                    // download file asap                    $fcontent = $this->server->tryDownload($httpReq, $this->serial, $filename, false);                    if(is_null($fcontent)) {                        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Will download {$filename} from {$this->serial}");                    } else {                        $this->server->files->saveDeviceFile($httpReq, $this->serial, 'files/'.$filename, $fcontent);                        $this->setEntryState($httpReq, $filename, 'known');                    }                    break;                case 'deleting':                case 'deleted':                    // deletion already scheduled, nothing to be done                    break;                case 'uploaded':                    VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Must upload {$filename} to {$this->serial}");                    $tarfile = $this->server->files->accessDeviceFiles($httpReq, $this->serial);                    $obj = $tarfile->searchTarFile($httpReq, 'files/'.$filename);                    if(is_null($obj)) {                        VHubServer::Log($httpReq, LOG_FILESYNC, 1, "Cannot upload {$filename} to {$this->serial}, file is missing on VirtualHub4web");                    } else {                        $this->server->scheduleUploadOnDevice($httpReq, $this->serial, $filename, $obj->content);                    }                    break;                case 'disappeared':                    VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} has disappeared on {$this->serial}, removing on VirtualHub4web");                    $tarfile = $this->server->files->accessDeviceFiles($httpReq, $this->serial);                    $tarfile->processTarFile($httpReq, 'files/'.$filename, TAROP_DELETE_FILE);                    unset($this->entries[$filename]);                    break;                case 'known':                    VHubServer::Log($httpReq, LOG_FILESYNC, 5, "File {$filename} is up-to-date on {$this->serial}");                    break;            }        }        return $this->modified;    }    // propagate VirtualHub4web upload to the device    function uploadToDevice(VHubServerHTTPRequest $httpReq, string $filename, int $filesize, int $crc)    {        $this->entries[$filename] = (object)['name' => $filename, 'size' => $filesize, 'crc' => $crc, 'status' => 'uploaded'];        $this->modified = true;        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "File {$filename} for {$this->serial} uploaded to VirtualHub4web");    }    // propagate VirtualHub4web delete to the device    function deleteOnDevice(VHubServerHTTPRequest $httpReq, string $filename)    {        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Schedule deletion of {$filename} on {$this->serial}");        $url = '/files.json?a=del&f='.$this->server->_escapeAttr($filename);        $this->server->scheduleQueryOnDevice($httpReq, $this->serial, 'GET', $url);        $this->setEntryState($httpReq, $filename, 'deleting');    }    // propagate VirtualHub4web format to the device    function formatOnDevice(VHubServerHTTPRequest $httpReq)    {        VHubServer::Log($httpReq, LOG_FILESYNC, 4, "Schedule filesystem format on {$this->serial}");        $this->server->scheduleQueryOnDevice($httpReq, $this->serial, 'GET', '/files.json?a=format');        foreach($this->entries as $filename => $entry) {            $this->setEntryState($httpReq, $filename, 'deleting');        }    }}class APIDeviceNode extends APINode{    public $cloudConf;    public $fileList;    public $services;    public $api;    public $deviceStats;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $serial)    {        parent::__construct($httpReq, $server, $serial);        $this->cloudConf = new DeviceCloudConf();        $this->fileList = new DeviceFileList($httpReq, $this->server, $serial);        $this->services = new APIServicesNode($httpReq, $this->server, 'services');        $this->api = new APIDeviceAPINode($httpReq, $this->server, 'api');        $this->addSubnode('api', $this->api);        $this->deviceStats = null;    }    // Load device global state from file data or live device api    public function loadState(VHubServerHTTPRequest $httpReq,  $data, $detectChanges): bool    {        if(isset($data->VirtualHub4web)) {            $this->cloudConf->loadState($httpReq, $data->VirtualHub4web);        }        if(isset($data->FileList)) {            $this->fileList->loadState($httpReq, $data->FileList);        }        // Restore services (originally published by the hub) from individual device files        // next to the api tree, where we have saved them there, instead as from the hub.        // This avoids keeping a dependence between the device and its own hub,        // and allows to transpose easily a device from one VirtualHub4web to the other        if(isset($data->services)) {            $this->services->loadState($httpReq, $data->services, false);        }        $modified = parent::loadState($httpReq, $data, $detectChanges);        if(isset($data->VirtualHub4web)) {            if (isset($data->VirtualHub4web->lastSeen)) {                $this->api->module->values['lastSeen'] = time() - $data->VirtualHub4web->lastSeen;            }            if (isset($data->VirtualHub4web->parentHub)) {                $this->api->module->values['parentHub'] = $data->VirtualHub4web->parentHub;                $this->api->module->values['parentIP'] = $data->VirtualHub4web->parentIP;            }        }        return $modified;    }     // Save device global state into an array for saving    public function saveState(): array    {        $res = parent::saveState();        $res['services'] = $this->services->saveState();        $res['FileList'] = $this->fileList->saveState();        $res['VirtualHub4web'] = $this->cloudConf->saveState();        return $res;    }    // Mark node as modified to force saving VirtualHub4web configuration    public function markAsChanged()    {        $this->modified = true;    }    public function hasChanged(): bool    {        return $this->modified || $this->fileList->hasChanged();    }    // Prepare to collect device statistics    public function initStats(VHubServerHTTPRequest $httpReq)    {        $this->deviceStats = new DeviceStats();    }    // Prepare to save device statistics to the device-specific file    public function getDeviceStats(): ?DeviceStats    {        return $this->deviceStats;    }}class APICloudApiNode extends APINode{    public $module;    public $network;    public $files;    public $services;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->module = new APICloudModuleNode($httpReq, $this->server, 'module');        $this->network = new APICloudNetworkNode($httpReq, $this->server, 'network');        $this->files = new APICloudFilesNode($httpReq, $this->server, 'files');        $this->services = new APIServicesNode($httpReq, $this->server, 'services');        $this->addSubnode('module', $this->module);        $this->addSubnode('network', $this->network);        $this->addSubnode('files', $this->files);        $this->addSubnode('services', $this->services);    }    public function loadStateFromCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf)    {        $this->module->loadStateFromCloudConf($httpReq, $cloudConf);        $this->network->loadStateFromCloudConf($httpReq, $cloudConf);        $this->files->loadStateFromCloudConf($httpReq, $cloudConf);    }    public function compareStateToCloudConf(VHubServerHTTPRequest $httpReq, GlobalCloudConf $cloudConf, array &$changes)    {        $this->module->compareStateToCloudConf($httpReq, $cloudConf, $changes);        $this->network->compareStateToCloudConf($httpReq, $cloudConf, $changes);        $this->files->compareStateToCloudConf($httpReq, $cloudConf, $changes);    }}class APIBySerialNode extends APINode{    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);    }}class APIRootNode extends APINode{    public $cloudConf;    public $api;    public $bySerial;    public $funYdxByHwId;    protected $guessedAttrTypes;    public function __construct(VHubServerHTTPRequest $httpReq, VHubServer $server, string $name)    {        parent::__construct($httpReq, $server, $name);        $this->server->apiroot = $this;        $this->cloudConf = new GlobalCloudConf();        $this->api = new APICloudApiNode($httpReq, $this->server, 'api');        $this->bySerial = new APIBySerialNode($httpReq, $this->server, 'bySerial');        $this->funYdxByHwId = [];        $this->guessedAttrTypes = [];        $this->addSubnode('api', $this->api);        $this->addSubnode('bySerial', $this->bySerial);    }    // Load VirtualHub4web global configuration from saved state    public function loadState(VHubServerHTTPRequest $httpReq,  $data, bool $detectChanges): bool    {        if(isset($data->VirtualHub4web)) {            $this->cloudConf->loadState($httpReq, $data->VirtualHub4web);            $this->api->loadStateFromCloudConf($httpReq, $this->cloudConf);        }        return true;    // not relevant for global configuration    }    // Save VirtualHub4web global state into configuration object    public function saveState(): array    {        $res = [];        $res['VirtualHub4web'] = $this->cloudConf->saveState();        return $res;    }    // Return a list of changes to VirtualHub4web state since last loaded    public function getStateChanges(VHubServerHTTPRequest $httpReq): array    {        $changes = [];        $this->api->compareStateToCloudConf($httpReq, $this->cloudConf, $changes);        return $changes;    }    // Load our own services into the whitePages/yellowPages    public function loadOwnServices(VHubServerHTTPRequest $httpReq)    {        $wpdef = new stdClass();        $wpdef->serialNumber = $this->cloudConf->serialNumber;        $wpdef->logicalName = $this->api->module->getattr('logicalName');        $wpdef->productName = $this->api->module->getattr('productName');        $wpdef->productId = $this->api->module->getattr('productId');        $wpdef->networkUrl = '/api';        $wpdef->beacon = $this->api->module->getattr('beacon');        $wpdef->index = 0;        $filesdef = new stdClass();        $filesdef->baseType = 0;        $filesdef->hardwareId = $this->cloudConf->serialNumber.'.files';        $filesdef->logicalName = $this->api->files->getattr('logicalName');        $filesdef->advertisedValue = $this->api->files->getattr('advertisedValue');        $filesdef->index = 0;        $netdef = clone $filesdef;        $netdef->hardwareId = $this->cloudConf->serialNumber.'.network';        $netdef->logicalName = $this->api->network->getattr('logicalName');        $filesdef->advertisedValue = $this->api->network->getattr('advertisedValue');        $netdef->index = 1;        $ypdef = new stdClass();        $ypdef->Files = [ $filesdef ];        $ypdef->Network = [ $netdef ];        $this->api->services->wp->loadState($httpReq, [$wpdef], false);        $this->api->services->yp->loadState($httpReq, $ypdef, false);    }    // Attempt to load specified service definitions into the VirtualHub4web    // Return true if success or false if a devYdx needs to be allocated    public function loadServices(VHubServerHTTPRequest $httpReq, string $hubSerial, object $servicesdef, bool $canUpdateDevYdx): bool    {        $hubDevYdx = max($this->cloudConf->getDevYdx($hubSerial), 0);        $wpdef = $servicesdef->whitePages;        foreach($wpdef as &$wpentry) {            $serial = $wpentry->serialNumber;            if(!$this->bySerial->hasSubnode($serial) && $serial != $this->server->apiroot->cloudConf->serialNumber) {                // unknown device, ignore services                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "LoadServices: ignore unknown serial $serial");                continue;            }            $parentDevYdx = ($serial == $hubSerial ? 0 : $hubDevYdx);            $devYdx = $this->cloudConf->getDevYdx($serial);            if ($devYdx < 0) { // new device                if(!$canUpdateDevYdx) return false;                $devYdx = $this->cloudConf->allocDevYdx($serial, $parentDevYdx);                if($devYdx < 0) {                    // too many devices for this instance of VirtualHub-4web                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Too many devices on this instance, ignoring $serial");                    continue;                }            } else if($parentDevYdx != -1 && $this->cloudConf->getParentDevYdx($serial) != $parentDevYdx) {                if(!$canUpdateDevYdx) return false;                $this->cloudConf->setParentDevYdx($serial, $parentDevYdx);            }            $wpentry->networkUrl = "/bySerial/$serial/api";            $wpentry->index = $devYdx;        }        $this->api->services->loadState($httpReq, $servicesdef, false);        return true;    }    // Return a services structure describing services offered by the given serial    public function saveServicesForSerial(string $serial): array    {        return $this->api->services->saveStateForSerial($serial);    }    // Return the known (or guessed) type of a given attribute    public function getAttrType(VHubServerHTTPRequest $httpReq, string $functionClass, string $attrName,  $value): int    {        global $ApiDef;        // First search into known function class definitions (generated file)        if(isset($ApiDef[$functionClass]) && isset($ApiDef[$functionClass][$attrName])) {            return $ApiDef[$functionClass][$attrName];        }        // Compute inference table when needed for the first time        VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Infer attribute type for [{$functionClass}.]{$attrName}");        if(sizeof($this->guessedAttrTypes) == 0) {            $typesByAttr = [];            foreach($ApiDef as $fclass => $classdef) {                foreach($classdef as $attr => $typeidx) {                    if(!isset($typesByAttr[$attr])) {                        $typesByAttr[$attr] = [ $typeidx => [ 'cnt' => 1, 'idx' => $typeidx ] ];                    } else if(!isset($typesByAttr[$attr][$typeidx])) {                        $typesByAttr[$attr][$typeidx] = [ 'cnt' => 1, 'idx' => $typeidx ];                    } else {                        $typesByAttr[$attr][$typeidx]['cnt'] += 1;                    }                }            }            foreach($typesByAttr as $attr => $alltypes) {                $bestCnt = 0;                foreach($alltypes as $typedesc => $typestats) {                    if($bestCnt < $typestats['cnt']) {                        $bestCnt = $typestats['cnt'];                        $this->guessedAttrTypes[$attr] = $typestats['idx'];                    }                }            }        }        // If this is a brand new attribute, assume read-only and infer type from value        if(!isset($this->guessedAttrTypes[$attrName])) {            if (is_numeric($value)) {                $this->guessedAttrTypes[$attrName] = $ApiDef['DeviceInfo']['index'];    // aka read-only Int            } else {                $this->guessedAttrTypes[$attrName] = $ApiDef['Module']['serialNumber']; // aka read-only Text            }        }        return $this->guessedAttrTypes[$attrName];    }}
const LOG_VHUBSERVER = 0;const LOG_HTTPCALLBACK = 1;const LOG_WSCALLBACK = 2;const LOG_CLIENTREQ = 3;const LOG_TARFILE = 4;const LOG_DATALOGGER = 5;const LOG_FILESYNC = 6;const GET_LAST_VERSION_URL = 'http://www.yoctopuce.com/FR/common/getLastFirmwareLink.php?serial=VHUB4WEB-00000';const VHUB4WEB_SESSIONS = VHUB4WEB_DATA.'/sessions';// Object used to retrieve data sent by HTTP Client and to send data back//class VHubServerHTTPRequest{    protected $reqStartTime;    protected $reqProcessTime;    protected $nErr;    protected $nWrn;    protected $dataSent;    protected $clientIP;    protected $clientId;    protected $method;    protected $userAgent;    protected $node;    protected $rawPostData;    protected $jsonPostData;    protected $args;    protected $authParams;    protected $shortReq;    public function __construct(bool $pseudo = false)    {        $this->reqStartTime = intval(round(1000 * microtime(true)));        $this->reqProcessTime = $this->reqStartTime;        $this->nErr = 0;        $this->nWrn = 0;        $this->clientIP = $_SERVER['REMOTE_ADDR'];        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {            $this->clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];        }        $this->clientSn = '';        $this->clientId = $this->clientIP;        $this->method = $_SERVER['REQUEST_METHOD'];        $this->userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unspecified');        $this->node = (isset($_GET['node']) ? $_GET['node'] : '');        $this->args = [];        $this->authParams = [];        $this->rawPostData = '';        $this->jsonPostData = null;        $this->shortReq = false;        $this->dataSent = '';        if($pseudo) {            // shortcut for creating a pseudo context            return;        }        if($this->method == 'POST') {            $this->rawPostData = file_get_contents("php://input");            $this->reqProcessTime = intval(round(1000 * microtime(true)));            if(str_starts_with($this->rawPostData, '{')) {                // Most likely JSON post data (not Form-encoded)                if(preg_match('~^HTTPCallback$~i', $this->node)) {                    $this->jsonPostData = json_decode(iconv("ISO-8859-1", "UTF-8", $this->rawPostData), true);                    //file_put_contents(VHUB4WEB_DATA.'/VHUB4WEB-postCbData.json', json_encode($this->jsonPostData, JSON_PRETTY_PRINT));                } else {                    $this->jsonPostData = json_decode($this->rawPostData, true);                    //file_put_contents(VHUB4WEB_DATA.'/VHUB4WEB-postData.json', json_encode($this->jsonPostData, JSON_PRETTY_PRINT));                }            }            if($this->jsonPostData) {                if(isset($this->jsonPostData['x-yauth'])) {                    $this->authParams = $this->jsonPostData['x-yauth'];                    $this->authParams['type'] = 'x-yauth';                }                if(isset($this->authParams['method'])) {                    $this->method = $this->authParams['method'];                }            }        }        if(sizeof($this->authParams) == 0 && isset($_SERVER['PHP_AUTH_DIGEST'])) {            preg_match_all('~(nonce|nc|cnonce|qop|username|uri|response)=(?:([\'"])([^\2]+?)\2|([^\s,]+))~',                $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);            $this->authParams['type'] = 'digest';            foreach ($matches as $m) {                $this->authParams[$m[1]] = $m[3] ?: $m[4];            }        }        if(isset($this->authParams['uri'])) {            // If an authentication is provided, make sure to use the authenticated URI instead of            // then unverified parameters possibly passed in the query            $baseurl = dirname($_SERVER['PHP_SELF']);            if(!str_ends_with($baseurl, '/')) {                $baseurl .= '/';            }            $url = $this->authParams['uri'];            if(str_starts_with($url, $baseurl)) {                $url = substr($url, strlen($baseurl));            }            $qpos = strpos($url, '?');            if($qpos === FALSE) {                $this->node = $url;            } else {                $this->node = substr($url, 0, $qpos);                $query = substr($url, $qpos+1);                if(str_ends_with($query, '&.')) {                    $this->shortReq = true;                    $query = substr($query, 0, -2);                }                parse_str($query, $arguments);                foreach($arguments as $name => $value) {                    if(is_string($value)) {                        $this->args[$name] = $value;                    }                }            }        } else {            // Otherwise, we can fallback to standard $_GET variables            foreach($_GET as $name => $value) {                if(is_string($value)) {                    $this->args[$name] = $value;                }            }            if(str_ends_with($_SERVER['REQUEST_URI'], '&.')) {                $this->shortReq = true;            }        }        if(!$this->node) {            $this->node = '';        }    }    public function getRequestTimestamp(): int    {        return intval(round($this->reqStartTime / 1000));    }    public function getIOReadTime(): int    {        return $this->reqProcessTime - $this->reqStartTime;    }    public function getProcessingTime(): int    {        return intval(round(1000 * microtime(true))) - $this->reqProcessTime;    }    public function getErrorCount(): int    {        return $this->nErr;    }    public function getWarningCount(): int    {        return $this->nWrn;    }    public function incLogCount(int $logSeverity)    {        switch($logSeverity) {            case 1: $this->nErr++; return;            case 2: $this->nWrn++; return;        }    }    public function getProtocol(): string    {        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';    }    public function getMethod(): string    {        return $this->method;    }    public function getRequestHostname(): string    {        return $_SERVER['SERVER_NAME'];    }    public function getRequestURL(): string    {        return $_SERVER['REQUEST_URI'];    }    public function getFullClientRequest(): string    {        return $this->getMethod().' '.$this->getRequestURL().' '.$_SERVER['SERVER_PROTOCOL'];    }    public function getServerPort(): int    {        return intVal($_SERVER['SERVER_PORT']);    }    public function getServerIP(): string    {        return (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0');    }    public function getClientIP(): string    {        return $this->clientIP;    }    public function setClientIdent(string $clientSerial, string $clientIdent)    {        $this->clientSn = $clientSerial;        $this->clientId = $clientIdent;    }    public function getClientSerial(): string    {        return $this->clientSn;    }    public function getClientIdent(): string    {        return $this->clientId;    }    public function getUserAgent(): string    {        return $this->userAgent;    }    public function getOrigin(): string    {        if (isset($_SERVER['HTTP_ORIGIN']) && !is_null($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != 'null') {            return $_SERVER['HTTP_ORIGIN'];        }        return '*';    }    public function getAuthUser(): string    {        if(isset($this->authParams['username'])) {            return $this->authParams['username'];        }        return '';    }    public function newNonce(): string    {        $newSessions = [];        if(!is_dir(VHUB4WEB_SESSIONS)) {            mkdir(VHUB4WEB_SESSIONS, 0700);        } else {            $now = time();            $files = scandir(VHUB4WEB_SESSIONS);            if($files !== FALSE) {                foreach($files as $fname) {                    if(!preg_match('/^([0-9a-f]{20})_(new|act)$/', $fname, $matches)) continue;                    if($matches[2] == 'new') {                        $hexstamp = substr($matches[1], 0, -12);                        $stamp = hexdec($hexstamp);                        $newSessions[$fname] = $now - $stamp;                    } else {                        $this->checkSession($matches[1], $A1);                    }                }            }        }        // Cleanup old inactive pending sessionIds        foreach($newSessions as $fname => $age) {            if($age > SESSION_MAX_INACTIVITY) {                $fullpath = VHUB4WEB_SESSIONS.'/'.$fname;                if(file_exists($fullpath)) {                    try { @unlink($fullpath); } catch(Throwable $e) {}                }            }        }        // Allocate a new secure session ID, make sure it is unused        do {            $res = strtolower(dechex(time()).bin2hex(random_bytes(6)));            $fname = "{$res}_new";        } while(isset($newSessions[$fname]));        // Create the new (empty) session file        file_put_contents(VHUB4WEB_SESSIONS.'/'.$fname, '');        // If we have too many valid pending sessions, delay new allocations        if(sizeof($newSessions) > SESSION_MAX_PENDING) {            usleep(199000);        }        return $res;    }    public function checkSession(string $nonce, &$A1 = null): bool    {        if(!is_dir(VHUB4WEB_SESSIONS)) {            return false;        }        $actfile = VHUB4WEB_SESSIONS."/{$nonce}_act";        if(!file_exists($actfile)) {            return false;        }        $data = explode(':', file_get_contents($actfile));        if(sizeof($data) < 2 || time()-hexdec($data[0]) > SESSION_MAX_INACTIVITY) {            try { @unlink($actfile); } catch(Throwable $e) {}            return false;        }        $A1 = $data[1];        return true;    }    public function touchSession(string $nonce, string $A1)    {        if(!is_dir(VHUB4WEB_SESSIONS)) {            return false;        }        $actfile = VHUB4WEB_SESSIONS."/{$nonce}_act";        file_put_contents($actfile, dechex(time()).':'.$A1);    }    public function checkPassword(string $password): bool    {        $authvals = $this->authParams;        $reqkeys = [ 'uri', 'nonce', 'nc', 'cnonce', 'qop', 'response' ];        foreach($reqkeys as $key) {            if(!isset($authvals[$key]) || !is_string($authvals[$key])) {                VHubServer::Log($this, LOG_CLIENTREQ, 3, "Missing x-yauth parameter {$key}");                return false;            }        }        if(!is_dir(VHUB4WEB_SESSIONS)) {            return false;        }        $nonce = $authvals['nonce'];        if(!preg_match('/^([0-9a-f]{20})$/', $nonce)) {            return false;        }        $newSessionFile = VHUB4WEB_SESSIONS."/{$nonce}_new";        if(file_exists($newSessionFile)) {            // new session with a valid nonce, check signature against password            try { @unlink($newSessionFile); } catch(Throwable $e) {}            $A1 = bin2hex(substr(base64_decode($password),1));        } else if(!$this->checkSession($nonce, $A1)) {            return false; // invalid nonce (possibly expired)        }        if($authvals['type'] == 'x-yauth') {            $A2 = sha1($this->method.':'.$authvals['uri']);            $signature = sha1($A1.':'.$authvals['nonce'].':'.$authvals['nc'].':'.$authvals['cnonce'].':'.$authvals['qop'].':'.$A2);        } else {            $A2 = md5($this->method.':'.$authvals['uri']);            $signature = md5($A1.':'.$authvals['nonce'].':'.$authvals['nc'].':'.$authvals['cnonce'].':'.$authvals['qop'].':'.$A2);        }        if($authvals['response'] != $signature) {            return false;        }        $this->touchSession($nonce, $A1);        return true;    }    public function setAuthUser(string $username)    {        $this->authParams['username'] = $username;    }    public function getNode(): string    {        return $this->node;    }    public function getArg(string $argName): ?string    {        if(isset($this->args[$argName])) {            return $this->args[$argName];        }        return null;    }    public function getAllArgs(): array    {        return $this->args;    }    public function getRawPostData(): string    {        return $this->rawPostData;    }    public function getJsonPostData(): ?array    {        return $this->jsonPostData;    }    public function isShortReq(): bool    {        return $this->shortReq;    }    public function putStatus(int $status)    {        http_response_code($status);    }    public function putHeader(string $header)    {        header($header);    }    public function requestAuthentication(string $realm, string $reason)    {        if(isset($this->authParams['type']) && $this->authParams['type'] == 'x-yauth') {            // Our custom authentication that does not pop-up a dialog on browsers            $this->putStatus(204);        } else {            // Request standard digest authentication            $this->putStatus(401);            $this->putHeader('WWW-Authenticate: Digest realm="' . $realm .                '",qop="auth",nonce="' . $this->newNonce() . '",opaque="' . md5($realm) . '"');        }        $this->putHeader('X-Auth-Error: ' . $reason);        // mark user as not authentified        $this->setAuthUser('');    }    public function put(string $message)    {        $this->dataSent .= $message;        Print($message);    }    public function getDataReceived(): int    {        $res = strlen($this->getFullClientRequest()) + strlen($this->rawPostData);        if(function_exists('apache_request_headers')) {            $headers = apache_request_headers();            foreach ($headers as $header => $value) {                $res += strlen($header) + strlen($value) + 4;            }        }        return $res;    }    public function getDataSent(): int    {        $res = strlen($this->dataSent);        if(function_exists('apache_response_headers')) {            flush();            $headers = apache_response_headers();            foreach ($headers as $header => $value) {                $res += strlen($header) + strlen($value) + 4;            }        }        return $res;    }    public function getRequestTrace(): string    {        $eventTime = date('Y-m-d H:i:s',time());        $clientIdent = $this->getClientIdent();        $serverIdent = $this->getRequestHostname();        $phpVersion = phpversion().' '.php_sapi_name();        $res = "{$eventTime}: from {$clientIdent} ({$this->clientIP}) to {$serverIdent} (PHP {$phpVersion})\r\n";        $res .= '--- HTTP Request: '.$this->getFullClientRequest()."\r\n";        if(function_exists('apache_request_headers')) {            $headers = apache_request_headers();            foreach ($headers as $header => $value) {                $res .= "{$header}: {$value}\r\n";            }        }        $res .= $this->rawPostData."\r\n";        $res .= '--- HTTP Reply: '."\r\n";        if(function_exists('apache_response_headers')) {            flush();            $headers = apache_response_headers();            foreach ($headers as $header => $value) {                $res .= "{$header}: {$value}\r\n";            }        }        return $res.$this->dataSent;    }}class PhpErrorException extends Exception{    public function __construct(int $errno, string $errstr, string $errfile, int $errline)    {        parent::__construct($errstr, $errno);        $this->file = $errfile;        $this->line = $errline;    }}class VHubServer{    // Static properties (globals)    public static $DebugLevel = [        LOG_VHUBSERVER => DEFAULT_LOGLEVEL,        LOG_HTTPCALLBACK => DEFAULT_LOGLEVEL,        LOG_WSCALLBACK => DEFAULT_LOGLEVEL,        LOG_CLIENTREQ => DEFAULT_LOGLEVEL,        LOG_TARFILE => DEFAULT_LOGLEVEL,        LOG_DATALOGGER => DEFAULT_LOGLEVEL,        LOG_FILESYNC => DEFAULT_LOGLEVEL    ];    public static $DebugLevels = [ 'SOS - ', 'ERR - ', 'WRN - ', 'INF - ', 'NOT - ', 'DBG -' ];    public static $DebugName = [        LOG_VHUBSERVER => "VSRV ",        LOG_HTTPCALLBACK => "HTCB ",        LOG_WSCALLBACK => "WSCB ",        LOG_CLIENTREQ => "CREQ ",        LOG_TARFILE => "TARF ",        LOG_DATALOGGER => "DLOG ",        LOG_FILESYNC => "FILE "    ];    // Navigable properties    public $apiroot;    // Device API cache    public $notif;      // VHubServer output notification stream    public $files;       // File content server    // Regular internal properties    protected $datadir;      // Data directory used by this instance, including trailing slash    protected $fdcache;       // File descriptor cache to prevent open/close of TAR files within a single HTTP callback    // Freely accessible files:    protected $safeFiles = [ 'iframe.html', 'webapp.html', 'ssdp.xml', 'index.html', 'info.json', 'favicon.svg', 'favicon.ico' ];    // Extra parameters that do not require admin rights:    protected $safeParams = [ 'node', 'abs', 'ctx', 'dir', 'fw', 'hub', 'len', 'pos', 'rnd', 'scr', 'logUrl', 'id', 'run', 'utc', 'from', 'to' ];    protected static $CurrentHTTPRequest;    public static function ProcessHTTPRequest()    {        // Make sure PHP configuration is still OK to write logs, etc.        $err = check_php_conf(true);        if(sizeof($err) > 0) {            VHubServer::DisplayFriendlyErrors($err);        }        // Install global error and exception handlers        set_error_handler('VHubServer::ErrorHandler', E_ALL);        set_exception_handler('VHubServer::ExceptionHandler');        // Dispatch HTTP request        $request = new VHubServerHTTPRequest();        VHubServer::$CurrentHTTPRequest = $request;        $isHub = preg_match('/VirtualHub|YoctoHub/', $request->getUserAgent());        if(preg_match('~^HTTPCallback$~i', $request->getNode())) {            // Make sure this request does not come from a browser            if(!$isHub) {                VHubServer::DisplayFriendlyErrors([[                    'error' => 'UserAgent',                    'msg' => 'This service URL is not meant to be called by a web browser.',                    'cause' => 'The URL ending with <b>HTTPCallback</b> should only be used as HTTP callback URL by '.                        'VirtualHub or by a YoctoHub. In order to access VirtualHub-4web UI, remove HTTPCallback '.                        'from the browser address.'                ]]);            }            // Invoke HTTP callback support code            VHubServer::HTTPCallback($request);        } else {            // Make sure this request does not come from a YoctoHub/VirtualHub            if($isHub) {                $url = $request->getRequestHostname().$request->getRequestURL();                VHubServer::Abort($request, 'Hub configuration error: '.$url.' is not a correct HTTP Callback URL');            }            // Invoke Hub emulation support code            VHubServer::ClientRequest($request);        }    }    public static function DisplayFriendlyErrors(array $err)    {        // Note: this function is used to report early configuration errors,        //       before even creating the VHubServerHTTPRequest object        Print("<style>\n");        Print("body{font-family:sans-serif;text-align:justify;background-color:lightyellow;}\n");        Print("a{font-size:small;}\n");        Print(".more{display:none;font-style:italic;padding:6px;width:600px;}\n");        Print("</style>\n");        Print("<h2>VirtualHub-4web fatal error</h2>\n");        Print("<script>\nfunction show(id) { document.getElementById(id).style.display='block'; }\n</script>\n");        if(sizeof($err) > 1) {            Print("<p>Oops, multiple problems have been found:</p>\n");        } else {            Print("<p>Oops, a serious problem has been detected:</p>\n");        }        Print("<ul>\n");        foreach($err as $error) {            Print("<li>{$error['msg']} <a href='javascript:show(\"{$error['error']}\")'>tell me more</a><div class='more' id='{$error['error']}'>{$error['cause']}</div></li>\n");        }        Print("</ul>\n");        die("</body>\n");    }    public static function Log(VHubServerHTTPRequest $httpReq, int $logType, int $logLevel, string $message)    {        if ($logLevel <= VHubServer::$DebugLevel[$logType]) {            $logfile = VHUB4WEB_DATA.'/VHUB4WEB-logs.txt';            $fullmsg = date('Y-m-d H:i:s ',time()).                VHubServer::$DebugName[$logType].VHubServer::$DebugLevels[$logLevel].                $httpReq->getClientIdent().' '.$message;            file_put_contents($logfile, $fullmsg."\n", FILE_APPEND | LOCK_EX);            if(filesize($logfile) > SERVERLOGS_MAX_SIZE) {                rename($logfile, VHUB4WEB_DATA.'/VHUB4WEB-logs-older.txt');            }        }        $httpReq->incLogCount($logLevel);    }    public static function Abort(VHubServerHTTPRequest $httpReq, string $message, array $stackTrace = [])    {        VHubServer::Log($httpReq, LOG_VHUBSERVER, 0, $message);        $httpReq->put(htmlspecialchars($message)."\n");        // If the fatal error is caused by a hub callback, keep the latest trace in a separate text file        $hubSerial = $httpReq->getClientSerial();        if($hubSerial) {            $tracefile = VHUB4WEB_DATA."/{$hubSerial}-fatal.trace";            $tracedata = $httpReq->getRequestTrace();            // append full debug information to trace file            $tracedata .= "--- Fatal Error:\r\n{$message}\r\n";            for($i = 0; $i < sizeof($stackTrace); $i++) {                $origin = basename($stackTrace[$i]['file']).':'.$stackTrace[$i]['line'];                if($i+1 < sizeof($stackTrace)) {                    $nextLevel = $stackTrace[$i + 1];                    $classPrefix = '';                    if (isset($nextLevel['class']) && $nextLevel['class'] != '') {                        $classPrefix = $nextLevel['class'] . '::';                    }                    $origin = $classPrefix . $stackTrace[$i + 1]['function'] . " ({$origin})";                }                $tracedata .= "called from {$origin}\r\n";            }            file_put_contents($tracefile, $tracedata);        }        die("\nAbort.\n");    }    public static function ErrorHandler(int $errno, string $errstr, string $errfile, int $errline)    {        throw new PhpErrorException($errno, $errstr, $errfile, $errline);    }    public static function ExceptionHandler(Throwable $ex)    {        // We don't receive the context from the caller in case of exception,        // so we need to use the static variable. This works for PHP since        // there is only one request per process        $httpReq = VHubServer::$CurrentHTTPRequest;        if(is_null($httpReq)) {            $httpReq = new VHubServerHTTPRequest(true);        }        $origin = basename($ex->getFile()).':'.$ex->getLine();        $stackTrace = $ex->getTrace();        if(sizeof($stackTrace) > 0) {            $classPrefix = '';            if(isset($stackTrace[0]['class']) && $stackTrace[0]['class'] != '') {                $classPrefix = $stackTrace[0]['class'].'::';            }            $origin = $classPrefix.$stackTrace[0]['function']." ({$origin})";        }        VHubServer::Abort($httpReq, $ex->getMessage()." in ".$origin, $stackTrace);    }    public static function HTTPCallback(VHubServerHTTPRequest $httpReq)    {        if($httpReq->getMethod() != "POST") {            VHubServer::Abort($httpReq, 'Invalid HTTP method, expected a Yocto-API POST Callback');        }        // The input stream was already consumed, we need to make it available to the YoctoLib API        $_SERVER['HTTP_RAW_POST_DATA'] = $httpReq->getRawPostData();        $_SERVER['HTTP_JSON_POST_DATA'] = $jsonPostData = $httpReq->getJsonPostData();        // Identify the network hub first        if(isset($jsonPostData['serial'])) {            $hubSerial = $jsonPostData['serial'];        } else {            $hubSerial = $jsonPostData['/api.json']['module']['serialNumber'];        }        // In PHP, we have to instantiate a new server for every connection (not persistent accross calls)        $server = new VHubServer($httpReq, VHUB4WEB_DATA);        $server->loadState($httpReq);        // enable HTTP callback Cache        if(!file_exists(VHUB4WEB_DATA . "/cache_dir")) {            mkdir(VHUB4WEB_DATA . "/cache_dir");        }        YAPI::SetHTTPCallbackCacheDir(VHUB4WEB_DATA . "/cache_dir");        // Try to RegisterHub - if it fails, we will catch the exception from caller        $errmsg = '';        if($server->apiroot->cloudConf->md5signPwd) {            $auth = base64_decode($server->apiroot->cloudConf->md5signPwd);            YAPI::RegisterHub("{$auth}@callback", $errmsg);        } else {            YAPI::RegisterHub("callback", $errmsg);        }        // Try to retrieve the network name        $network = YNetwork::FindNetwork($hubSerial.'.network');        if($network->isOnline()) {            $hubName = $network->get_logicalName();        } else {            $hubName = $hubSerial;        }        $httpReq->setClientIdent($hubSerial, $hubName);        VHubServer::Log($httpReq, LOG_HTTPCALLBACK, 5, 'Incoming HTTP Callback from ' . $hubName);        $server->prepareToNotify($httpReq);        $nReset = 0;        $nDevices = $server->discoverDevices($httpReq, $nReset);        $server->transferDeviceFiles($httpReq);        if(isset($jsonPostData['tRepBuf'])) {            $tRepBufSize = $jsonPostData['tRepBuf'];            $tRepDataSize = $server->processTimedReports($httpReq, $hubSerial);            $tRepUsage = intVal(round(100 * $tRepDataSize / $tRepBufSize));        } else {            $server->emulateTimedReports($httpReq);            $tRepUsage = -1;        }        $server->executePendingQueries($httpReq, $network->get_serialNumber());        $server->saveDeviceState($httpReq);        $server->saveState($httpReq);        $server->closeNotificationStream($httpReq);        $httpReq->put('VirtualHub-4web callback complete.');        // Save last request for trace purposes        if($httpReq->getErrorCount() > 0) {            $reqfile = 'lastError.trace';        } else if($httpReq->getWarningCount() > 0) {            $reqfile = 'lastWarning.trace';        } else {            $reqfile = 'lastCallback.trace';        }        $trace = $httpReq->getRequestTrace();        $server->files->saveDeviceFile($httpReq, $hubSerial, $reqfile, $trace);        // backup any previous fatal trace in the same place        $tracefile = VHUB4WEB_DATA."/{$hubSerial}-fatal.trace";        if(file_exists($tracefile)) {            $fataltrace = file_get_contents($tracefile);            $server->files->saveDeviceFile($httpReq, $hubSerial, 'lastFatal.trace', $fataltrace);            unlink($tracefile);        }        // Update hub stats at the very end        $hubNode = $server->apiroot->bySerial->subnode($hubSerial);        $hubStats = $hubNode->getDeviceStats();        if(!is_null($hubStats)) {            $hubStats->appendStats($httpReq, $tRepUsage, $nDevices, $nReset);            $statsObj = $hubStats->saveState();            $server->files->saveDeviceFile($httpReq, $hubSerial, 'stats.json', json_encode($statsObj, JSON_UNESCAPED_SLASHES));        }    }    public static function ClientRequest(VHubServerHTTPRequest $httpReq)    {        $server = new VHubServer($httpReq, VHUB4WEB_DATA);        $server->loadState($httpReq);        // Allow cross-origin requests, including authentication        $httpReq->putHeader('Access-Control-Allow-Origin: '.$httpReq->getOrigin());        $httpReq->putHeader('Access-Control-Allow-Credentials: true');        $httpReq->putHeader('Access-Control-Allow-Headers: Authorization');        $httpReq->putHeader('Vary: Origin');        $httpReq->putHeader('X-DNS-Prefetch-Control: off');        // Parse requested node path        $reqpath = $httpReq->getNode();        if($reqpath == '') {            $defaultPage = $server->apiroot->api->network->getattr('defaultPage');            if($defaultPage == '') {                $defaultPage = 'index.html';            }            $rootUrl = parse_url($httpReq->getRequestURL(), PHP_URL_PATH);            if(!str_ends_with($rootUrl, '/')) {                $rootUrl .= '/';            }            $httpReq->putStatus(302);            $httpReq->putHeader('Location: '.$rootUrl.$defaultPage);            return;        }        $extension = '';        $nodepath = explode('/', $reqpath);        $filename = array_pop($nodepath);        if($filename == '') {            $filename = array_pop($nodepath);        }        $filepart = explode('.', $filename);        if(sizeof($filepart) > 1) {            // remove file extension            $extension = $filepart[sizeof($filepart)-1];            $nodepath[] = substr($filename, 0, -(strlen($extension)+1));        } else {            $nodepath[] = $filename;        }        // Determines if authentication is required        $userPwd = $server->apiroot->api->network->getattr('userPassword');        $adminPwd = $server->apiroot->api->network->getattr('adminPassword');        $requiresAdmin = false;        $requiresAuth = false;        if(sizeof($nodepath) > 1 || array_search($reqpath, $server->safeFiles) === false) {            if($userPwd != '') {                $requiresAuth = true;            }        }        if($adminPwd) {            if($httpReq->getMethod() != 'GET') {                $requiresAuth = true;                $requiresAdmin = true;            } else if($nodepath[0] != 'iframe') {                $allArgs = $httpReq->getAllArgs();                foreach($allArgs as $key => $value) {                    if(array_search($key, $server->safeParams) !== false) continue;                    if($key == 'a' && ($value == 'list' || $value == 'dir')) continue;                    if($key == 'f' && isset($allArgs['a']) && $allArgs['a'] == 'dir') continue;                    $requiresAuth = true;                    $requiresAdmin = true;                    break;                }            }        }        if($requiresAuth) {            if(!$server->digestAuthenticate($httpReq)) {                die('Unauthorized user');            }            if($userPwd != '' && $adminPwd == '') {                // when only a user password is set, accept only 'user'                if($httpReq->getAuthUser() == 'admin') {                    die('Unauthorized user');                }            } else if($adminPwd != '') {                // if an admin password is set, make sure only 'admin' is logged in when required                if ($requiresAdmin) {                    if ($httpReq->getAuthUser() != 'admin') {                        $httpReq->requestAuthentication($server->apiroot->cloudConf->authRealm, 'Admin rights required');                        die('Admin rights required');                    }                }            }        }        if(!$adminPwd) {            // when no admin password is required, grant admin rights to logged user            $httpReq->setAuthUser('admin');        }        // Distinguish between API requests and simple file requests        if($nodepath[0] == 'api' ||            ($nodepath[0] == 'bySerial' && (sizeof($nodepath) < 3 || $nodepath[2] == 'api'))) {            $server->processAPI($httpReq, $nodepath, $extension);            return;        }        $logLevel = ($filename == 'not.byn' || $filename == 'flash.json' ? 5 : 4);        VHubServer::Log($httpReq, LOG_CLIENTREQ, $logLevel, "Sending file ".json_encode($nodepath)." ".$extension);        // Handle local file requests        if(sizeof($nodepath) == 1) {            switch($filename) {                case 'logs.txt':            // logs.txt?pos=...                    $pos = ($httpReq->getArg('pos') ?: '0');                    // Note: that serialNumber has not been reloaded from the config file,                    //       so it might not be the real one. But it is anyway the one against                    //       which the serveLogs function will compare, so that does not matter :-)                    $server->serveLogs($httpReq, $server->apiroot->cloudConf->serialNumber, intVal($pos));                    return;                case 'upload.html':         // upload.html?...                    $server->handleUpload($httpReq, '');                    return;                case 'not.byn':             // not.byn?len=...&abs=...                    $server->serveNotifications($httpReq);                    return;                case 'flash.json':          // flash.json?a=list - ignore for now                    $httpReq->put('{"total":0, "list":[]}');                    return;                case 'getInstaller.json':   // getInstaller.json?forVersion=...                    $server->serveInstaller($httpReq);                    return;                case 'testcb.txt':          // testcb.txt[?w=10]                    // FIXME: emulate callbacks to third party services ?                    return;                case 'cbdata.txt':          // cbdata.txt?n=                    return;                case 'info.json':           // info.json                    $server->serveInfo($httpReq);                    return;                case 'stats.json':                    $server->serveStats($httpReq);                    return;                case 'configure.json':                    $server->serveConf($httpReq);                    return;                case 'edithtml.js':         // edit.thml, generated file                    global $ApiAttrEdit;                    $server->files->sendFileContent($httpReq, $ApiAttrEdit, 'js');                    return;                case 'files.json':          // files.json?a=(dir|stat|del/format)&f=...                    $action = ($httpReq->getArg('a') ?: 'dir');                    $fname = ($httpReq->getArg('f') ?: '*');                    $server->files->filesCmd($httpReq, $action, $fname);                    $server->saveState($httpReq);                    return;                case 'Yv4wI.js':                    $server->serveYV4webInstaller($httpReq);                    return;                default:                    $server->files->sendFile($httpReq, $reqpath, $extension);                    return;            }        }        if(sizeof($nodepath) >= 3 && $nodepath[0] == 'bySerial') {            // Send special file or cached file from subdevice if available            switch($filename) {                case 'logger.json':         // logger.json[?id=...&utc=...]                case 'dataLogger.json':     // dataLogger.json[?id=...&utc=...]                    $fid = ($httpReq->getArg('id') ?: '');                    $run = ($httpReq->getArg('run') ?: '');                    $utc = ($httpReq->getArg('utc') ?: '');                    $fromUtc = ($httpReq->getArg('from') ?: '');                    $toUtc = ($httpReq->getArg('to') ?: '');                    $server->serveLogger($httpReq, $nodepath[1], $fid, $run, $utc, $fromUtc, $toUtc, ($filename != 'logger.json'));                    return;                case 'logs.txt':            // logs.txt?pos=...                    $pos = ($httpReq->getArg('pos') ?: '0');                    $server->serveLogs($httpReq, $nodepath[1], intVal($pos));                    return;                case 'upload.html':         // upload.html?...                    $server->handleUpload($httpReq, $nodepath[1]);                    return;                case 'files.json':          // files.json?a=(dir|stat|del/format)&f=...                    $action = ($httpReq->getArg('a') ?: 'dir');                    $fname = ($httpReq->getArg('f') ?: '*');                    $server->files->deviceFilesCmd($httpReq, $nodepath[1], $action, $fname);                    $server->saveState($httpReq);                    return;                case 'edithtml.js':         // edit.thml is the same for all devices (our own generated file)                    global $ApiAttrEdit;                    $server->files->sendFileContent($httpReq, $ApiAttrEdit, 'js');                    return;            }            $server->files->sendDeviceFile($httpReq, $nodepath[1], implode('/', array_slice($nodepath, 2)).'.'.$extension, $extension);            return;        }        $server->files->sendFile($httpReq, $reqpath, $extension);    }    public function __construct(VHubServerHTTPRequest $httpReq, string $datadir)    {        $this->datadir = $datadir.'/';        $this->apiroot = new APIRootNode($httpReq, $this, '');        $this->files = new FileServer($this);        $this->fdcache = [];    }    public function getDataDir(): string    {        return $this->datadir;    }    public function fexists(string $relativePath): bool    {        return file_exists($this->datadir.$relativePath);    }    public function filesize(string $relativePath): int    {        return filesize($this->datadir.$relativePath);    }    protected function fopen_cached(VHubServerHTTPRequest $httpReq, string $relativePath)    {        if(str_ends_with($relativePath, '.tar')) {            if (!isset($this->fdcache[$relativePath])) {                $fp = fopen($this->datadir . $relativePath, "r+b");                VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fopen({$relativePath}): fp={$fp}");                $this->fdcache[$relativePath] = $fp;            } else {                $fp = $this->fdcache[$relativePath];                fseek($fp, 0, SEEK_SET);            }        } else {            $fp = fopen($this->datadir . $relativePath, "r+b");            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fopen({$relativePath}): fp={$fp}");        }        return $fp;    }    public function fopen_ro(VHubServerHTTPRequest $httpReq, string $relativePath)    {        $fp = $this->fopen_cached($httpReq, $relativePath);        if (!flock($fp, LOCK_SH)) { // acquire a shared lock for reading            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Could not get shared lock to read {$relativePath}");        }        return $fp;    }    public function fopen_rw(VHubServerHTTPRequest $httpReq, string $relativePath)    {        $fp = $this->fopen_cached($httpReq, $relativePath);        if (!flock($fp, LOCK_EX)) { // acquire an exclusive lock for writing            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Could not get exclusive lock to write {$relativePath}");        }        return $fp;    }    public function frewrite(VHubServerHTTPRequest $httpReq, string $relativePath)    {        if (isset($this->fdcache[$relativePath]) || file_exists($this->datadir . $relativePath)) {            $fp = $this->fopen_cached($httpReq, $relativePath);        } else {            $fp = fopen($this->datadir . $relativePath, "wb");        }        if (!flock($fp, LOCK_EX)) { // acquire an exclusive lock            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Fail to get exclusive lock to rewrite file {$relativePath}");        }        ftruncate($fp, 0);      // truncate file (needed despite fopen(w) because of flock)        return $fp;    }    public function fclose(VHubServerHTTPRequest $httpReq,  $fp, string $relativePath)    {        fflush($fp);                    // flush output before releasing the lock        flock($fp, LOCK_UN);   // release the lock        // Keep descriptor open for optimizations if path is found in cache        if(!isset($this->fdcache[$relativePath])) {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fclose({$relativePath}): fp={$fp}");            fclose($fp);        }    }    public function fappend(VHubServerHTTPRequest $httpReq, string $relativePath, $text)    {        file_put_contents($this->datadir.$relativePath, $text, FILE_APPEND | LOCK_EX);    }    public function loadFile(VHubServerHTTPRequest $httpReq, string $relativePath, bool $getExclusiveLock = false, &$filedesc = null): string    {        if($getExclusiveLock) {            if(!$this->fexists($relativePath)) {                $filedesc = null;                return '{}';            }            $fp = $this->fopen_rw($httpReq, $relativePath);            $contents = stream_get_contents($fp);            fseek($fp, 0, SEEK_SET);            $filedesc = $fp;        } else {            $fp = $this->fopen_ro($httpReq, $relativePath);            $contents = stream_get_contents($fp);            $this->fclose($httpReq, $fp, $relativePath);        }        return $contents;    }    public function saveFile(VHubServerHTTPRequest $httpReq, string $relativePath, string $content, $fp = null)    {        if(!$fp) {            $fp = $this->frewrite($httpReq, $relativePath);        } else {            fseek($fp, 0, SEEK_SET);            ftruncate($fp, 0);        }        fwrite($fp, $content);        $this->fclose($httpReq, $fp, $relativePath);    }    public function loadState(VHubServerHTTPRequest $httpReq)    {        // load VirtualHub4web API state        if(file_exists($this->datadir.STATE_FILE)) {            // Load current state            $apiobj = json_decode($this->loadFile($httpReq, STATE_FILE), false, 99, 0);            $this->apiroot->loadState($httpReq, $apiobj, false);            $this->apiroot->loadOwnServices($httpReq);        } else {            // Create initial state            VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Config file does not yet exist, creating one");            $this->apiroot->loadOwnServices($httpReq);            $cloudapiobj = $this->apiroot->saveState();            $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));        }        // load client hubs API state        foreach (glob($this->datadir.'????????-?*.tar') as $tarname) {            if(!preg_match('~/([A-Z0-9]+-[0-9a-fA-F]+).tar$~', $tarname, $matches)) {                continue;            }            $serial = $matches[1];            $apijson = $this->files->loadDeviceFile($httpReq, $serial, 'api.json');            if(is_null($apijson)) {                continue;            }            try {                $apiobj = json_decode($apijson, false, 99, 0);            } catch(Throwable $err) {                VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Error parsing client api file for {$serial}: ".$err->getMessage());                continue;            }            $apinode = new APIDeviceNode($httpReq, $this, $serial);            $this->apiroot->bySerial->addSubnode($serial, $apinode);            $apinode->loadState($httpReq, $apiobj, false);            if(sizeof($apiobj->services->whitePages) > 0 && isset($apiobj->VirtualHub4web)) {                $hubSerial = $apiobj->VirtualHub4web->parentHub;                $this->apiroot->loadServices($httpReq, $hubSerial, $apiobj->services, false);                // Load statistics for root nodes                if($serial == $hubSerial) {                    $apinode->initStats($httpReq);                    $statsjson = $this->files->loadDeviceFile($httpReq, $serial, 'stats.json');                    if(is_null($statsjson)) {                        continue;                    }                    try {                        $statsobj = json_decode($statsjson, false, 99, 0);                    } catch(Throwable $err) {                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Error parsing client stats file for {$serial}: ".$err->getMessage());                        continue;                    }                    $devStats = $apinode->getDeviceStats();                    $devStats->loadState($httpReq, $statsobj);                }            }        }        $this->apiroot->api->services->sortServices($httpReq);    }    public function updateCloudState(VHubServerHTTPRequest $httpReq)    {        $stateChanges = $this->apiroot->getStateChanges($httpReq);        if(sizeof($stateChanges) > 0) {            // Reload state file while keeping an exclusive lock to update it            $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), true, 99, 0);            // Selectively update changed values            foreach($stateChanges as $key => $value) {                if (!str_ends_with($key, 'Password')) {                    // don't log password changes, to avoid security problems                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Attribute change: $key = $value");                }                if ($key != 'persistentSettings') {                    $cloudapiobj['VirtualHub4web']['valuesCache'][$key] = $value;                }            }            // Handle persistentSettings change            if(isset($stateChanges['persistentSettings'])) {                switch($stateChanges['persistentSettings']) {                    case 0: // revert from last saved settings                        foreach ($cloudapiobj['VirtualHub4web']['savedSettings'] as $key => $savedValue) {                            $cloudapiobj['VirtualHub4web']['valuesCache'][$key] = $savedValue;                        }                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 0;                        break;                    case 1: // save settings to persistent storage                        foreach ($this->apiroot->cloudConf->savedSettings as $key => $prevValue) {                            if(isset($cloudapiobj['VirtualHub4web']['valuesCache'][$key])) {                                $cloudapiobj['VirtualHub4web']['savedSettings'][$key] = $cloudapiobj['VirtualHub4web']['valuesCache'][$key];                            }                        }                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 1;                        break;                }            } else {                foreach ($cloudapiobj['VirtualHub4web']['savedSettings'] as $key => $savedValue) {                    if($cloudapiobj['VirtualHub4web']['valuesCache'][$key] != $savedValue) {                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 2;                        break;                    }                }            }            // Save file and release lock            $apitxt = json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);            $this->saveFile($httpReq, STATE_FILE, $apitxt, $fp);            // Reload updated state            $apiobj = json_decode($apitxt, false, 99, 0);            $this->apiroot->loadState($httpReq, $apiobj, false);        }    }    public function saveState(VHubServerHTTPRequest $httpReq)    {        $this->updateCloudState($httpReq);        foreach($this->apiroot->bySerial->subnodeNames() as $serial) {            $subnode = $this->apiroot->bySerial->subnode($serial);            if($subnode->hasChanged()) {                // Note: this code will change the state file so that next answers to client API                // stay coherent, but the propagation to the device itself is handled separately                // by the temporary "-changes.txt" file associated to the device hub                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Updating api.json for {$serial} after change");                $apiobj = $subnode->saveState();                $this->files->saveDeviceFile($httpReq, $serial, 'api.json', json_encode($apiobj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));            }        }    }    public function prepareToNotify(VHubServerHTTPRequest $httpReq)    {        $this->notif = NotifStream::StreamAt($httpReq, $this, -1);        $this->notif->openForAppend($httpReq);    }    public function closeNotificationStream(VHubServerHTTPRequest $httpReq)    {        $this->notif->close($httpReq);    }    public function _escapeAttr(string $attrval): string    {        $safecodes = [ '%21', '%23', '%24', '%27', '%28', '%29', '%2A', '%2C', '%2F', '%3A', '%3B', '%40', '%3F', '%5B', '%5D' ];        $safechars = [ '!', "#", "$", "'", "(", ")", '*', ",", "/", ":", ";", "@", "?", "[", "]" ];        return str_replace($safecodes, $safechars, urlencode($attrval));    }    public function digestAuthenticate(VHubServerHTTPRequest $httpReq): bool    {        $realm = $this->apiroot->cloudConf->authRealm;        $user = $httpReq->getAuthUser();        if(!$user) {            $httpReq->requestAuthentication($realm, 'Authentication required');            return false;        }        if($user != 'user' && $user != 'admin') {            $httpReq->requestAuthentication($realm, "Unknown user {$user}");            return false;        }        // check password        $pwd = $this->apiroot->api->network->getattr($user.'Password');        if(!$httpReq->checkPassword($pwd)) {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Authentication failure for user {$user} from IP ".$httpReq->getClientIP());            $httpReq->requestAuthentication($realm, "Invalid credentials for user {$user}");            return false;        }        // login successful        return true;    }    public function scheduleUploadOnDevice(VHubServerHTTPRequest $httpReq, string $targetSerial, string $str_path, string $bin_content)    {        $body = "Content-Disposition: form-data; name=\"$str_path\"; filename=\"api\"\r\n" .            "Content-Type: application/octet-stream\r\n" .            "Content-Transfer-Encoding: binary\r\n\r\n" . $bin_content;        do {            $boundary = sprintf("Zz%06xzZ", mt_rand(0, 0xffffff));        } while (str_contains($body, $boundary));        $mimebody = "--{$boundary}\r\n{$body}\r\n--{$boundary}--\r\n";        $this->scheduleQueryOnDevice($httpReq, $targetSerial, 'POST', '/upload.html', $mimebody);    }    public function scheduleQueryOnDevice(VHubServerHTTPRequest $httpReq, string $targetSerial, string $reqType, string $url, string $body = '')    {        $deviceNode = $this->apiroot->bySerial->subnode($targetSerial);        $rootHub = $deviceNode->cloudConf->parentHub;        if($rootHub == '') {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Cannot apply change to {$targetSerial}, unknown parent hub");            return;        }        if($targetSerial != $rootHub) {            $url = '/bySerial/'.$targetSerial.$url;        }        $fullreq = date('Y-m-d_H:i:s ',time()).$reqType.' '.$url."\n";        if($body != '') {            $fullreq .= base64_encode($body)."\n";        }        $this->fappend($httpReq, $rootHub.'-pending.req', $fullreq);    }    public function sendCallbackApiCommand(VHubServerHTTPRequest $httpReq, string $command, ?string $extradata = null)    {        VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "@YoctoAPI:".$command);        $httpReq->put("\n@YoctoAPI:{$command}\n");        if(!is_null($extradata)) {            $httpReq->put($extradata."\n");        }    }    public function executePendingQueries(VHubServerHTTPRequest $httpReq, string $rootHub)    {        // check if there are pending queries for the specified root hub        $pendingfile = $this->datadir.$rootHub.'-pending.req';        if(!file_exists($pendingfile)) {            return;        }        // load and unlink (atomically) pending reqiests        $runningfile = str_replace('pending', 'running', $pendingfile);        rename($pendingfile, $runningfile);        $requests = preg_split('/\r\n|\r|\n/', file_get_contents($runningfile));        unlink($runningfile);        for($i = 0; $i < sizeof($requests); $i++) {            $req = explode(' ', $requests[$i]);            if(sizeof($req) < 3) {                continue;            }            $reqUrl = trim($req[2]);            if($req[1] == 'GET') {                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Execute GET ".json_encode($reqUrl));                $this->sendCallbackApiCommand($httpReq, 'GET '.$reqUrl);            } else if($req[1] == 'POST') {                if($i+1 >= sizeof($requests)) {                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Cannot execute POST request on {$reqUrl}, missing body");                    return;                }                $str_body = base64_decode($requests[++$i]);                $boundary = '???';                $endb = strpos($str_body, "\r");                if (str_starts_with($str_body, '--') && $endb > 2 && $endb < 20) {                    $boundary = substr($str_body, 2, $endb - 2);                }                $this->sendCallbackApiCommand($httpReq, 'POST '.$reqUrl.' '.strlen($str_body).':'.$boundary, $str_body);            }        }        // requests have been executed, force next callback immediately        $this->sendCallbackApiCommand($httpReq, '%');    }    public function tryDownload(VHubServerHTTPRequest $httpReq, string $serial, string $fname, bool $requestAgain): ?string    {        VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "Try to load {$fname} from {$serial}");        $module = YModule::FindModule($serial.'.module');        try {            $fcontent = $module->_download($fname);            if(str_starts_with($fcontent, '64#')) {                $fcontent = substr($fcontent,3);                $fcontent = base64_decode($fcontent);            }            if($requestAgain) {                // request file for the next time anyway                $apinode = $this->apiroot->bySerial->subnode($serial);                $rootHub = $apinode->cloudConf->parentHub;                $rootUrl = ($rootHub != $serial ? '/bySerial/'.$serial : '');                $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$fname}");            }            return $fcontent;        } catch(Throwable $exception) {            // Most probably caused by the file content not being posted in the HTTP callback data            $serial = $module->get_serialNumber();            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "Cannot load {$fname} from {$serial}: ".$exception->getMessage());        }        return null;    }    public function discoverDevices(VHubServerHTTPRequest $httpReq, &$nReset): int    {        // First create a list of modules with yoctohubs at the bottoms, and the virtualhubs at the very end        $clientIP = $httpReq->getClientIP();        $modules = [];        $virthubs = [];        $nReset = 0;        $module = YModule::FirstModule();        while($module) {            $apibin = $module->_download('api.json');            $apistr = iconv("ISO-8859-1", "UTF-8", $apibin);            $apiobj = new stdClass();            $apiobj->api = json_decode($apistr, false, 99, 0);            $toadd = ['module' => $module, 'apiobj' => $apiobj];            if(isset($apiobj->api->services)) {                $prodId = $apiobj->api->module->productId;                if($prodId == 0xc10d) {                    // add any other virtualhub-4web very very last, even after virtualhubs                    $virthubs[] = $toadd;                } else if($prodId == 0) {                    // add virtualhub in a separate list                    array_unshift($virthubs, $toadd);                } else {                    // add yoctohubs at the end                    $modules[] = $toadd;                }            } else {                // add other module at the start                array_unshift($modules, $toadd);            }            $module = $module->nextModule();        }        foreach($virthubs as $toadd) {            $modules[] = $toadd;        }        // Then process the list in this order        for($mi = 0; $mi < sizeof($modules); $mi++) {            $module = $modules[$mi]['module'];            $apiobj = $modules[$mi]['apiobj'];            $serial = $module->get_serialNumber();            if($this->apiroot->bySerial->hasSubnode($serial)) {                $apinode = $this->apiroot->bySerial->subnode($serial);                $lastSeen = $apinode->api->module->getattr('lastSeen');                $prevUptime = $apinode->api->module->getattr('upTime');                $apinode->loadState($httpReq, $apiobj, true);                // detect device resets                $newUptime = $apinode->api->module->getattr('upTime');                $deltaUptime = ($newUptime - $prevUptime) & 0xffffffff;                $safeUptimeSec = intdiv(max($newUptime, $deltaUptime), 1000);                // Ensure that uptime difference matches expectations with 2 % margin + 10 sec                $wasReset = abs($safeUptimeSec - $lastSeen) < (0.02*$lastSeen + 10);                if($wasReset) {                    $apinode->cloudConf->deviceResetDetected();                    $nReset++;                }            } else {                $apinode = new APIDeviceNode($httpReq, $this, $serial);                $apinode->loadState($httpReq, $apiobj, true);                $this->apiroot->bySerial->addSubnode($serial, $apinode);            }            $apinode->cloudConf->lastSeen = time();            if($apinode->cloudConf->reconnect) {                VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Fast reconnect requested for {$serial}");                $apinode->cloudConf->reconnect = 0;                $this->sendCallbackApiCommand($httpReq, '%');            }            $apinode->markAsChanged();            if(isset($apiobj->api->services) && substr($serial, 0, 7) != 'YHUBSHL') {                $devYdxExists = $this->apiroot->loadServices($httpReq, $serial, $apiobj->api->services, false);                if (!$devYdxExists) {                    // Reload state file while keeping an exclusive lock to update it                    $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, 0);                    $this->apiroot->loadState($httpReq, $cloudapiobj, false);                    $this->apiroot->loadServices($httpReq, $serial, $apiobj->api->services, true);                    $cloudapiobj = $this->apiroot->saveState();                    $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);                }                // store link to parent hub and services in subdevice                $wpdef = $apiobj->api->services->whitePages;                foreach ($wpdef as $wpentry) {                    $subserial = $wpentry->serialNumber;                    if(!$this->apiroot->bySerial->hasSubnode($subserial)) {                        // device is supposed to have been loaded first                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Dropping services for unknown serial {$subserial}; possibly a subdevice of a hub connected via USB?");                        continue;                    }                    $apisubnode = $this->apiroot->bySerial->subnode($subserial);                    if($apisubnode->cloudConf->parentHub != $serial || $apisubnode->cloudConf->parentIP != $clientIP) {                        $apisubnode->cloudConf->parentHub = $serial;                        $apisubnode->cloudConf->parentIP = $clientIP;                        $apisubnode->markAsChanged();                    }                    $subservices = $this->apiroot->saveServicesForSerial($subserial);                    $apisubnode->services->loadState($httpReq, $subservices, true);                }            }        }        return sizeof($modules);    }    public function transferDeviceFiles(VHubServerHTTPRequest $httpReq)    {        $module = YModule::FirstModule();        while($module) {            $serial = $module->get_serialNumber();            $apinode = $this->apiroot->bySerial->subnode($serial);            $knownFirmware = $apinode->cloudConf->yfsVer;            $currentfirmware = $apinode->api->module->getattr('firmwareRelease');            // Download built-in files if needed            $yfsFiles = [];            if(!$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/icon2d.png')) {                $yfsFiles[] = 'icon2d.png';            }            if (!str_starts_with($serial, 'Y3DMK001')) { // Yocto-3D has no built-in UI                if ($knownFirmware != $currentfirmware ||                       (!$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/details.html') &&                        !$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/details.html.gz'))) {                    // Must download UI files                    if ((str_contains($currentfirmware, ':') || intVal($currentfirmware) >= 51000) &&                        !str_starts_with($serial, 'YHUB') &&                        !str_starts_with($serial, 'VIRTHUB') &&                        !str_starts_with($serial, 'VHUB4WEB')) {                        // Download _FS all at once                        try {                            $fscontent = $module->_download('_FS');                            if (str_starts_with($fscontent, '64#')) {                                $fscontent = substr($fscontent, 3);                                $fscontent = base64_decode($fscontent);                            }                            $this->files->saveAllDeviceFiles($httpReq, $serial, $fscontent);                            $apinode->cloudConf->yfsVer = $currentfirmware;                            $apinode->markAsChanged();                            VHubServer::Log($httpReq, LOG_FILESYNC, 3, "Downloaded all UI files for {$serial}");                        } catch (Throwable $exception) {                            $msg = $exception->getMessage();                            if (str_contains($msg, 'Network error')) {                                VHubServer::Log($httpReq, LOG_FILESYNC, 2, "Failed to open _FS file for {$serial}: " . $msg);                            }                        }                    } else {                        // Older firmware, try to download individual files                        $yfsFiles[] = 'details.html';                        $yfsFiles[] = 'configure.html';                    }                }            }            foreach ($yfsFiles as $fname) {                $fcontent = $this->tryDownload($httpReq, $serial, $fname, false);                if (!is_null($fcontent)) {                    if (strlen($fcontent) > 4 && ord($fcontent[0]) == 0x1f && ord($fcontent[1]) == 0x8b) {                        $fname .= '.gz';                    }                    $this->files->saveDeviceFile($httpReq, $serial, 'yfs/' . $fname, $fcontent);                    if(str_starts_with($fname, 'details.html')) {                        $apinode->cloudConf->yfsVer = $currentfirmware;                        $apinode->markAsChanged();                        VHubServer::Log($httpReq, LOG_FILESYNC, 3, "Downloaded individual UI files for {$serial}");                    }                }            }            // Check latest logs as well            $rootHub = $apinode->cloudConf->parentHub;            $rootUrl = ($rootHub != $serial ? '/bySerial/'.$serial : '');            try {                $logUrl = 'logs.txt';                if($apinode->cloudConf->logPos != 0) {                    $logUrl .= '?pos='.$apinode->cloudConf->logPos;                }                $logs = $module->_download($logUrl);                $endPos = strrpos($logs, "\n@");                if($endPos > 0) {                    $newLogPos = intVal(substr($logs, $endPos+2));                    $logs = date("[Y-m-d H:i:s]\n", time()).substr($logs, 0, $endPos);                    $prevLogs = $this->files->loadDeviceFile($httpReq, $serial, 'logs.txt');                    if(!is_null($prevLogs)) {                        $prevLogs = preg_replace('~ *$~', '', $prevLogs);                        $logs = $prevLogs.$logs;                    }                    $logsLen = strlen($logs);                    if($logsLen > DEVICELOGS_MAX_SIZE) {                        $logs = substr($logs, -DEVICELOGS_MAX_SIZE);                    } else if($logsLen < DEVICELOGS_MAX_SIZE) {                        $logs .= str_repeat(' ', DEVICELOGS_MAX_SIZE - $logsLen);                    }                    $this->files->saveDeviceFile($httpReq, $serial, 'logs.txt', $logs);                    $apinode->cloudConf->logPos = $newLogPos;                    $this->notif->appendConfigChangeNotification($httpReq, $serial);                    $apinode->markAsChanged();                    // request new logs.txt for the next time                    $logUrl = 'logs.txt?pos='.$apinode->cloudConf->logPos;                    $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$logUrl}");                } else {                    // no new log, request logs.txt next time nevertheless                    $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$logUrl}");                }            } catch(Throwable $exception) {                $msg = $exception->getMessage();                if(!str_contains($msg, 'Network error')) {                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to open logs.txt for {$serial}: ".$msg);                }                $httpReq->put('logs.txt not available: '.$exception->getMessage()."\r\n");            }            // Download extra files for specific modules/functions            if($apinode->api->hasSubnode('display')) {                $fname = 'display.gif';                $fcontent = $this->tryDownload($httpReq, $serial, $fname, true);                if(!is_null($fcontent)) {                    $this->files->saveDeviceFile($httpReq, $serial, $fname, $fcontent);                }            }            if($apinode->api->hasSubnode('files')) {                $fname = 'files.json';                $fcontent = $this->tryDownload($httpReq, $serial, $fname, true);                if(!is_null($fcontent)) {                    try {                        // process file records                        $filesRecs = json_decode($fcontent, false, 99, 0);                        if($apinode->fileList->compareToDevice($httpReq, $filesRecs)) {                            $apinode->markAsChanged();                        }                    } catch(Throwable $exception) {                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to parse files.json for {$serial}: ".$exception->getMessage());                    }                }            }            $module = $module->nextModule();        }    }    public function processTimedReports(VHubServerHTTPRequest $httpReq, string $hubSerial): int    {        $hubModule = YModule::FindModule($hubSerial);        // Index white page records to decode devYdx        $hubAPI = json_decode($hubModule->_download('api.json'));        $serialByDevYdx = [];        foreach($hubAPI->services->whitePages as $wpRec) {            $serialByDevYdx[$wpRec->index] = $wpRec->serialNumber;        }        $apinode = $this->apiroot->bySerial->subnode($hubSerial);        $tRep = null;        try {            $tRepURL = 'tRep.bin';            if($apinode->cloudConf->tRepPos != 0) {                $tRepURL .= '?pos='.$apinode->cloudConf->tRepPos;            }            $tRep = $hubModule->_download($tRepURL);        } catch(Throwable $exception) {            $msg = $exception->getMessage();            if(!str_contains($msg, 'Network error')) {                VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to open tRep.bin file for {$hubSerial}: ".$msg);            }            $httpReq->put('tRep.bin not available: '.$exception->getMessage()."\r\n");        }        if(is_null($tRep)) {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "tRep not available for now");            return -1;        }        // process all available timed reports        $currDevYdx = -1;        $currDevReports = [];        $newTRepPos = 0;        $newReps = 0;        $endPos = strlen($tRep);        for($pos = 0; $pos+2 < $endPos; ) {            $devYdx = ord($tRep[$pos++]);            $head = ord($tRep[$pos++]);            $data0 = ord($tRep[$pos++]);            $funYdx = $head & 0xf;            $extraLen = $head >> 4;            if($currDevYdx != $devYdx || $funYdx == 15 || $pos + $extraLen > $endPos) {                // flush pending reports                if($currDevYdx >= 0) {                    if(isset($serialByDevYdx[$currDevYdx])) {                        $currDevSerial = $serialByDevYdx[$currDevYdx];                        $this->notif->handleTrueTimedReportNotification($httpReq, $currDevSerial, $currDevReports);                        $currDevReports = [];                    }                }            }            if($devYdx == 0xff && $head == 0xff) {                // end of file marker, parse end position                $newTRepPos = $data0 + 0x100 * ord($tRep[$pos]) + 0x10000 * ord($tRep[$pos+1]) + 0x1000000 * ord($tRep[$pos+2]);                break;            }            if($pos + $extraLen > $endPos) break;            if($currDevYdx != $devYdx) {                $currDevYdx = $devYdx;            }            $rawReport = [ $data0 ];            for($i = 0; $i < $extraLen; $i++) {                $rawReport[] = ord($tRep[$pos+$i]);            }            $currDevReports[$funYdx] = $rawReport;            $pos += $extraLen;        }        if($newTRepPos) {            $newReps = ($newTRepPos - $apinode->cloudConf->tRepPos) & 0xffffffff;            $apinode->cloudConf->tRepPos = $newTRepPos;            $apinode->markAsChanged();            // request new logs.txt for the next time            $tRepURL = 'tRep.bin?pos='.$apinode->cloudConf->tRepPos;            $this->sendCallbackApiCommand($httpReq, "+/{$tRepURL}");        } else {            // missing events or no timed report, request tRep.bin next time nevertheless            $this->sendCallbackApiCommand($httpReq, "+/{$tRepURL}");        }        return $newReps;    }    public function emulateTimedReports(VHubServerHTTPRequest $httpReq)    {        // default UTC timestamp taken from server, if no dataLogger is found        $timestamp = time();        $module = YModule::FirstModule();        while($module) {            $serial = $module->get_serialNumber();            $deviceNode = $this->apiroot->bySerial->subnode($serial);            $deviceApiNode = $deviceNode->api;            $values = [];            $fcount = $module->functionCount();            for($i = 0; $i < $fcount; $i++) {                if($module->functionBaseType($i) == 'Sensor') {                    // Sensor found, check if a timed report is available                    $functionId = $module->functionId($i);                    $functionNode = $deviceApiNode->subnode($functionId);                    $avgVal = $functionNode->getSensorValue();                    if(!is_nan($avgVal)) {                        $values[$functionId] = $avgVal;                    }                } else if($module->functionId($i) == 'dataLogger') {                    $functionNode = $deviceApiNode->subnode('dataLogger');                    $timestamp = $functionNode->get_timeUTC();                }            }            if(sizeof($values) > 0) {                $parentSerial = $deviceNode->cloudConf->parentHub;                $parentNode = $this->apiroot->bySerial->subnode($parentSerial);                $parentNet = $parentNode->api->subnode('network');                $period = min(intval($parentNet->getattr('callbackMinDelay')), 3600);                $freq = new DataFrequency($period);                $endTime = $freq->alignTimestamp($timestamp);                $startTime = $endTime - $period;                $reports = [];                foreach($values as $functionid => $avgVal) {                    $sensor = YSensor::FindSensor("{$serial}.{$functionId}");                    $unit = $sensor->get_unit();                    $measure = new YMeasure($startTime, $endTime, $avgVal, $avgVal, $avgVal);                    $reports[$functionId] = [ 'sensor' => $sensor, 'measure' => $measure, 'unit' => $unit, 'freq' => $freq];                }                $this->notif->appendEmulatedTimedReportNotification($httpReq, $serial, $reports);                $logger = new DataLogger($this, $serial);                $logger->appendMeasures($httpReq, $reports);            }            $module = $module->nextModule();        }    }    public function saveDeviceState(VHubServerHTTPRequest $httpReq)    {        $module = YModule::FirstModule();        while($module) {            $serial = $module->get_serialNumber();            $apinode = $this->apiroot->bySerial->subnode($serial);            if($apinode->hasChanged()) {                $apiobj = $apinode->saveState();                $this->files->saveDeviceFile($httpReq, $serial, 'api.json', json_encode($apiobj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));            }            $module = $module->nextModule();        }    }    public function processAPI(VHubServerHTTPRequest $httpReq, array $nodepath, string $disptype)    {        // Search for requested node        $ctx = $httpReq->getArg('ctx');        if($ctx) {            $ctxpath = explode('/', $ctx);        } else {            $ctxpath = [];        }        [ $apinode, $ctxnode, $subkey ] = $this->apiroot->search($nodepath, $ctxpath);        if(is_null($apinode)) {            $httpReq->putStatus(404);            $httpReq->put("Sorry, the requested node ".htmlspecialchars(implode('/',$nodepath))." does not exist\r\n");            return;        }        if(!is_null($ctxnode)) {            if($ctxnode->fclass == 'Module' && $subkey == 'lastSeen' && !is_null($httpReq->getArg('lastSeen'))) {                // special request to force immediate reconnects after next HTTP Callback                $serial = $ctxnode->getattr('serialNumber');                $deviceNode = $this->apiroot->bySerial->subnode($serial);                $deviceNode->cloudConf->reconnect = 1;                $deviceNode->markAsChanged();                $this->saveState($httpReq);                $httpReq->put("%OK");                return;            }            // Apply changes to API nodes            foreach($httpReq->getAllArgs() as $setattr => $setval) {                if(array_search($setattr, ['node','fw','checkRW','rnd','ctx','scr','abs','dir','hub','len','pos','_','serialNumber','w']) !== FALSE) {                    // not real attributes change, shortcut                    continue;                }                if($ctxnode->fclass == 'DataLogger') {                    // Do not forward any datalogger requests for now                    continue;                }                if($setattr == 'persistentSettings' && $setval == '2') {                    // pseudo-change to trigger an immediate config change callback on client                    // no need to propagate this change to the client                    $this->notif->appendConfigChangeNotification($httpReq, $ctxnode->getattr('serialNumber'));                } else if($setattr != 'command') {                    // - special attribute command is never stored in the api                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "API requests attribute change: {$setattr} = {$setval} on ".json_encode($nodepath));                    $ctxnode->setattr($setattr, $setval);                }                if($nodepath[0] == 'bySerial') {                    // for remote devices, record change to perform next time the device becomes available                    $relpath = array_merge(array_slice($nodepath, 2), $ctxpath, [ $setattr ]);                    $changereq = '/'.implode('/', $relpath).'?'.$setattr.'='.$this->_escapeAttr($setval);                    $this->scheduleQueryOnDevice($httpReq, $nodepath[1], 'GET', $changereq);                }            }            $this->saveState($httpReq);        }        if(is_null($subkey)) {            // Display node            $this->files->sendContentHeader($httpReq, $disptype);            switch($disptype) {                case 'json':                    $apinode->printJSON($httpReq);                    break;                case 'jzon':                    $apinode->printJZON($httpReq);                    break;                case '':                case 'html':                    $devicedir = '';                    for($i = sizeof($nodepath)-1; $i > 0 && $nodepath[$i] != 'api'; $i--) {                        $devicedir .= '../';                    }                    for($basedir = $devicedir; $i > 0; $i--) {                        $basedir .= '../';                    }                    $baseHRef = ($basedir != '' ? "<BASE href='{$basedir}'/>" : '');                    $action = $httpReq->getNode();                    $httpReq->put("<!DOCTYPE html>{$baseHRef}".                        "<link href='edithtml.css' rel=stylesheet type='text/css'/>".                        "<SCRIPT src='edithtml.js'></SCRIPT><SCRIPT src='js/edit.js'></SCRIPT>".                        "<BODY onload='rescroll()'><form method='get' action='{$action}'>".                        "<INPUT type='hidden' name='scr'><INPUT type='hidden' name='ctx'>");                    $apinode->printHTML($httpReq, $apinode->name);                    $httpReq->put('</FORM>');                    break;                case 'txt':                    $apinode->printTXT($httpReq, $apinode->name);                    break;                case 'xml':                    $httpReq->put('<'.'?xml version=\"1.0\"?'.">\r\n");                    $apinode->printXML($httpReq, $apinode->name);                    break;            }        } else if(!$httpReq->isShortReq()) {            // Display value            switch($disptype) {                case 'json':                case 'jzon':                    $apinode->printJSONValue($httpReq, $subkey);                    break;                case 'txt':                    $apinode->printTXTValue($httpReq, $subkey);                    break;                case '':                case 'html':                    $apinode->printHTMLValue($httpReq, $subkey);                    break;                case 'xml':                    $apinode->printXMLValue($httpReq, $subkey);                    break;            }        }    }    public function serveInstaller(VHubServerHTTPRequest $httpReq)    {        $res = [];        // Test command, to avoid timeouts        $testTimeout = $httpReq->getArg('testTimeout');        if(!is_null($testTimeout)) {            try {                $fp = fsockopen('www.yoctopuce.com', 80, $errorCode, $errorMsg, floatVal($testTimeout));                if($fp === FALSE) {                    $res['error'] = "{$errorMsg} (error {$errorCode})";                } else {                    $res['success'] = 1;                    fclose($fp);                }            } catch(Throwable $ex) {                $res['error'] = $ex->getMessage();            }            $this->files->sendContentHeader($httpReq, 'json');            $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));            return;        }        // Real command to prepare to run the installer        $version = $httpReq->getArg('forVersion');        $getVersionStr = @file_get_contents(GET_LAST_VERSION_URL);        if(!$version) {            // forVersion flag is enforce requirements for admin rights            $res['error'] = 'version specifier is MANDATORY';        } else if(!$getVersionStr) {            $res['error'] = 'unable to retrieve version information from www.yoctopuce.com';        } else if(!class_exists('ZipArchive')) {            $res['error'] = 'PHP zip extension is not enabled';        } else {            $getVersion = json_decode($getVersionStr);            if(is_null($getVersion)) {                $res['error'] = 'unable to retrieve version information from www.yoctopuce.com';            } else if($version == 'latest') {                $url = $getVersion->link;            } else {                $url = str_replace('.'.$getVersion->version.'.', '.'.urlencode($version).'.', $getVersion->link);            }            $res['installerURL'] = $url;            $installer = @file_get_contents($url);            if(!$installer) {                $res['error'] = "unable to retrieve installer from www.yoctopuce.com ({$url})";            } else {                $baseDir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));                $tempFile = tempnam($baseDir, 'vhw');                $zip = new ZipArchive;                if (!@file_put_contents($tempFile, $installer)) {                    $res['error'] = 'unable to write ZIP file';                } else if($zip->open($tempFile) !== TRUE) {                    $res['error'] = 'unable to open ZIP file';                    @unlink($tempFile);                } else {                    $installer = $zip->getFromName('vhub4web-installer.php');                    $zip->close();                    @unlink($tempFile);                    if(!$installer) {                        $res['error'] = 'unable to read from ZIP file';                    } else {                        $installerName = 'vhub4web-installer.'.bin2hex(random_bytes(6)).'.php';                        $installerFile = $baseDir.'/'.$installerName;                        if(!@file_put_contents($installerFile, $installer)) {                            $res['error'] = 'unable to write installer file';                        } else {                            $baseUrl = dirname(dirname(parse_url($httpReq->getRequestURL(), PHP_URL_PATH)));                            $res['location'] = $baseUrl.'/'.$installerName;                        }                    }                }            }        }        $this->files->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));    }    public function serveInfo(VHubServerHTTPRequest $httpReq)    {        $uri = preg_replace('~(/info.json|\?).*~', '', $httpReq->getRequestURL());        if(str_starts_with($uri, '/')) {            $uri = substr($uri, 1);        }        $protocol = $httpReq->getProtocol();        $userPwd = $this->apiroot->api->network->getattr('userPassword');        $adminPwd = $this->apiroot->api->network->getattr('adminPassword');        $info = [            "productName" => $this->apiroot->api->module->getattr('productName'),            "serialNumber" => $this->apiroot->api->module->getattr('serialNumber'),            "firmwareRelease" => $this->apiroot->api->module->getattr('firmwareRelease'),            "dir" => "$uri",            "userPassword" => ($userPwd == '' ? "FALSE" : "TRUE"),            "adminPassword" => ($adminPwd == '' ? "FALSE" : "TRUE"),            "port" => [ $protocol.':'.$this->apiroot->api->network->getattr('httpPort') ],            "protocol" => "HTTP/1.1",            "realm" =>  $this->apiroot->cloudConf->authRealm,            "nonce" => $httpReq->newNonce()        ];        $this->files->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($info, JSON_UNESCAPED_SLASHES));    }    public function serveStats(VHubServerHTTPRequest $httpReq)    {        $stats = [];        foreach($this->apiroot->bySerial->subnodeNames() as $serial) {            $devnode = $this->apiroot->bySerial->subnode($serial);            $devstats = $devnode->getDeviceStats();            if(!is_null($devstats)) {                $stats[$serial] = $devstats->saveState();                $stats[$serial]['lastCallbackAge'] = $httpReq->getRequestTimestamp() - $stats[$serial]['prevTimestamp'];                $stats[$serial]['lastCallbackIP'] = $devnode->cloudConf->parentIP;                $hubname = '';                if($devnode->api->hasSubnode('network')) {                    $netnode = $devnode->api->subnode('network');                    $hubname = $netnode->getattr('logicalName');                    $stats[$serial]['callbackMaxDelay'] = $netnode->getattr('callbackMaxDelay');                }                $stats[$serial]['hubName'] = ($hubname != '' ? $hubname : $serial);            }        }        $this->files->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($stats, JSON_UNESCAPED_SLASHES));    }    public function serveConf(VHubServerHTTPRequest $httpReq)    {        $res = [];        $deleteDevice = $httpReq->getArg('deleteDevice');        if(!is_null($deleteDevice)) {            $serial = $deleteDevice;            $res['deleteDevice'] = [ 'target' => $serial, 'done' => 0 ];            $tarpath = VHUB4WEB_DATA.'/'.$deleteDevice.'.tar';            if(file_exists($tarpath)) {                unlink($tarpath);                // Reload the state file while keeping an exclusive lock to update it                $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, 0);                $this->apiroot->loadState($httpReq, $cloudapiobj, false);                $this->apiroot->cloudConf->freeDevYdx($serial);                $cloudapiobj = $this->apiroot->saveState();                $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);                $cloudSerial = $this->server->apiroot->cloudConf->serialNumber;                $this->notif->appendModuleRemovalNotifications($httpReq, $cloudSerial, $serial);                $res['deleteDevice']['done'] = 1;            } else {                $res['deleteDevice']['errmsg'] = 'unknown device '.$serial;            }        }        $setCbMd5Pwd = $httpReq->getArg('callbackMD5Password');        if(!is_null($setCbMd5Pwd)) {            if($setCbMd5Pwd == '?') {                $res['callbackMD5Password'] = [ 'changed' => 0 ];            } else {                // Reload the state file while keeping an exclusive lock to update it                $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, 0);                $this->apiroot->loadState($httpReq, $cloudapiobj, false);                $this->apiroot->cloudConf->md5signPwd = $setCbMd5Pwd;                $cloudapiobj = $this->apiroot->saveState();                $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);                $res['callbackMD5Password'] = [ 'changed' => 1 ];            }            $isSet = ($this->apiroot->cloudConf->md5signPwd ? 'YES' : 'NO');            $res['callbackMD5Password']['isSet'] = $isSet;        }        $this->files->sendContentHeader($httpReq, 'json');        $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));    }    public function serveYV4webInstaller(VHubServerHTTPRequest $httpReq)    {        try {            $installer = @file_get_contents('http://www.yoctopuce.com/Yv4wI.js');            if(ord($installer[0]) == 0x1f && ord($installer[1]) == 0x8b) {                $httpReq->putHeader('Content-encoding: gzip');            }            $this->files->sendContentHeader($httpReq, 'js');            $httpReq->put($installer);        } catch(Throwable $e) {            $httpReq->putStatus(404);            $httpReq->put("Failed to fetch Yocto-Visualization-4web installer from www.yoctopuce.com: {$e->getMessage()}\r\n");        }    }    public function serveLogs(VHubServerHTTPRequest $httpReq, string $serial, int $pos)    {        $logs = '';        if($serial == $this->apiroot->cloudConf->serialNumber) {            $fp = fopen(VHUB4WEB_DATA.'/VHUB4WEB-logs.txt', 'rb');            if($fp) {                $logs = stream_get_contents($fp);                fclose($fp);            }        } else {            $devlogs = $this->files->loadDeviceFile($httpReq, $serial, 'logs.txt');            if(!is_null($devlogs)) {                $logs = preg_replace('~ *$~', '', $devlogs);            }        }        // when the logs have wrapped, the first line indicates the start offset        $startPos = 0;        if(preg_match('~^@([0-9]+)\n~', $logs, $matches)) {            $startPos = intVal($matches[1]);            $logs = substr($logs, strlen($matches[0]));        }        $endPos = $startPos + strlen($logs);        $this->files->sendContentHeader($httpReq, 'txt');        if($pos <= $startPos) {            $httpReq->put($logs);        } else {            $httpReq->put(substr($logs, $pos - $startPos));        }        $httpReq->put("\n@$endPos");    }    public function serveNotifications(VHubServerHTTPRequest $httpReq)    {        // default to unspecified position        if(!is_null($httpReq->getArg('abs'))) {            $position = intVal($httpReq->getArg('abs'));            $veryFirstCall = false;        } else {            $position = -1;            $veryFirstCall = true;        }        $this->notif = NotifStream::StreamAt($httpReq, $this, $position);        // For PHP must stay in "short notification" as it is the        // only reliable way to force Apache to flush ASAP        $position = $this->notif->openForRead($httpReq, 1);        $banner = "YN01@{$position}\n\n";        $httpReq->putHeader('Content-Type: text/plain; charset=x-user-defined');        $maxlength = $this->notif->predictSize();        $httpReq->putHeader('Content-length: '.(strlen($banner)+$maxlength));        $httpReq->put($banner);        $started = microtime(true);        while($maxlength != 0) {            $newNotif = $this->notif->readMore($httpReq, $maxlength);            if(strlen($newNotif) > 0) {                $httpReq->put($newNotif);                $maxlength -= strlen($newNotif);                // for PHP, close immediately to force a flush since Apache may be forcing cache                break;            }            // for PHP, flush every at every KEEPALIVE interval since Apache may be forcing cache            if(microtime(true) - $started > NOTIF_KEEPALIVE_DELAY) {                break;            }            // delay execution for up to 0,1 [s] before retrying            time_nanosleep(0, 100000);            // for PHP, we also flush quickly at the very first call to avoid any delay before            // connection is diagnosed as working            if($veryFirstCall) {                break;            }        }        if($maxlength > 0) {            $httpReq->put(str_repeat("\n", $maxlength));        }        $this->notif->close($httpReq);    }    public function serveLogger(VHubServerHTTPRequest $httpReq, string $serial, string $functionid, string $run, string $utc, string $fromUtc, string $toUtc, bool $verbose)    {        $this->files->sendContentHeader($httpReq, 'json');        // Enumerate device sensors        $deviceNode = $this->apiroot->bySerial->subnode($serial);        $deviceApiNode = $deviceNode->api;        $sensorIds = [];        $functions = $deviceApiNode->subnodeNames();        foreach($functions as $funcid) {            if($deviceApiNode->subnode($funcid)->isSensor()) {                $sensorIds[] = $funcid;            }        }        if(sizeof($sensorIds) == 0) {            $httpReq->put('[]');            return;        }        if($functionid != '') {            if(!in_array($functionid, $sensorIds)) {                $functionid = '';            }        }        // Retrieve data from the datalogger        $logger = new DataLogger($this, $serial);        if($utc == '') {            // Dump summary            $fromStamp = ($fromUtc == '' ? 0 : intVal($fromUtc));            $toStamp = ($toUtc == '' ? 0xffff0000 : intVal($toUtc));            if($functionid == '') {                $sep = '[';                foreach($sensorIds as $funcid) {                    $httpReq->put($sep);                    $logger->printIndex($httpReq, $deviceApiNode->subnode($funcid), $funcid, $run, $fromStamp, $toStamp, $verbose);                    $sep = ',';                }                $httpReq->put(']');            } else {                $logger->printIndex($httpReq, $deviceApiNode->subnode($functionid), $functionid, $run, $fromStamp, $toStamp, $verbose);            }        } else if(str_contains($utc, ',')) {            // Dump multiple streams in details (bulk transfer)            $utcStamps = array_map(function($value) { return intval($value); }, explode(',', $utc));            $httpReq->put('[');            $logger->printRun($httpReq, $functionid, $run, $utcStamps, $verbose);            $httpReq->put(']');        } else {            // Dump a single stream in details            $utcStamp = intVal($utc);            $logger->printRun($httpReq, $functionid, $run, [ $utcStamp ], $verbose);        }    }    public function handleUpload(VHubServerHTTPRequest $httpReq, string $devserial = '')    {        $fname = '';        $content = '';        $jsonData = $httpReq->getJsonPostData();        if($jsonData && isset($jsonData['body'])) {            // JSON-encoded POST data            $fname = $jsonData['body']['filename'];            $content = base64_decode($jsonData['body']['b64content']);        } else {            $postdata = $httpReq->getRawPostData();            if (strlen($postdata) > 0) {                // Form-Encoded POST data                $fnameMatches = [];                $boundaryMatches = [];                if (!preg_match('/Content-Disposition: form-data; name="([^"]*)";/i', $postdata, $fnameMatches)) {                    die("upload.html: multipart/form-data encoding expected !\n");                }                if (!preg_match('/--\S*/', $postdata, $boundaryMatches)) {                    die("upload.html: multipart boundary not found\n");                }                $boundary = $boundaryMatches[0];                $fname = $fnameMatches[1];                $startPos = strpos($postdata, "\r\n\r\n", strlen($boundary));                $endPos = strpos($postdata, "\r\n" . $boundary, $startPos);                if ($startPos >= 0 && $endPos >= 0) {                    $startPos += 4;                    $content = substr($postdata, $startPos, $endPos - $startPos);                }            } else {                // PHP-Specific: Bug in many recent version (7.x), enable_post_data_reading does not work with .user.ini                // => we need to fallback to tentative processing based on PHP $_FILES variable                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Upload detected without proper enable_post_data_reading=0");                foreach ($_FILES as $fname => $filedef) {                    // problem: PHP replaces dots by underscores in the filename, we need to revert that                    $fname = preg_replace('~_(html|txt|xml|js|ts|bin|min|byn|gz|zip)~i', '.$1', $fname);                    $content = file_get_contents($filedef['tmp_name']);                }            }        }        if(!$fname) {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Empty upload request");            return;        }        if($devserial == '') {            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Uploading {$fname} to VirtualHub4web files");            $this->files->filesUpload($httpReq, $fname, $content);        } else {            $csize = strlen($content);            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Scheduling upload of {$fname} to ${$devserial} ({$csize} bytes)");            $this->files->deviceFilesUpload($httpReq, $devserial, $fname, $content);        }        $this->saveState($httpReq);    }}
