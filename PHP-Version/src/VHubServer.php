<?php
/*********************************************************************
 *
 * $Id: VHubServer.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * Main objects handling VirtualHub4web server code
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

include_once("runtime-checks.php");
include_once("YoctoLib/yocto_api.php");
include_once("YoctoLib/yocto_network.php");
include_once("API.php");
include_once("APINode.php");
include_once("NotifStream.php");
include_once("FileServer.php");
include_once("DataLogger.php");

const LOG_VHUBSERVER = 0;
const LOG_HTTPCALLBACK = 1;
const LOG_WSCALLBACK = 2;
const LOG_CLIENTREQ = 3;
const LOG_TARFILE = 4;
const LOG_DATALOGGER = 5;
const LOG_FILESYNC = 6;

const GET_LAST_VERSION_URL = 'http://www.yoctopuce.com/FR/common/getLastFirmwareLink.php?serial=VHUB4WEB-00000';
const VHUB4WEB_SESSIONS = VHUB4WEB_DATA.'/sessions';

// Object used to retrieve data sent by HTTP Client and to send data back
//
class VHubServerHTTPRequest
{
    protected int $reqStartTime;
    protected int $reqProcessTime;
    protected int $nErr;
    protected int $nWrn;
    protected string $dataSent;
    protected string $clientIP;
    protected string $clientId;
    protected string $method;
    protected string $userAgent;
    protected string $node;
    protected string $rawPostData;
    protected ?array $jsonPostData;
    protected array $args;
    protected array $authParams;
    protected bool $shortReq;

    public function __construct(bool $pseudo = false)
    {
        $this->reqStartTime = intval(round(1000 * microtime(true)));
        $this->reqProcessTime = $this->reqStartTime;
        $this->nErr = 0;
        $this->nWrn = 0;
        $this->clientIP = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $this->clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        $this->clientSn = '';
        $this->clientId = $this->clientIP;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unspecified');
        $this->node = (isset($_GET['node']) ? $_GET['node'] : '');
        $this->args = [];
        $this->authParams = [];
        $this->rawPostData = '';
        $this->jsonPostData = null;
        $this->shortReq = false;
        $this->dataSent = '';
        if($pseudo) {
            // shortcut for creating a pseudo context
            return;
        }
        if($this->method == 'POST') {
            $this->rawPostData = file_get_contents("php://input");
            $this->reqProcessTime = intval(round(1000 * microtime(true)));
            if(str_starts_with($this->rawPostData, '{')) {
                // Most likely JSON post data (not Form-encoded)
                if(preg_match('~^HTTPCallback$~i', $this->node)) {
                    $this->jsonPostData = json_decode(iconv("ISO-8859-1", "UTF-8", $this->rawPostData), true);
                    //file_put_contents(VHUB4WEB_DATA.'/VHUB4WEB-postCbData.json', json_encode($this->jsonPostData, JSON_PRETTY_PRINT));
                } else {
                    $this->jsonPostData = json_decode($this->rawPostData, true);
                    //file_put_contents(VHUB4WEB_DATA.'/VHUB4WEB-postData.json', json_encode($this->jsonPostData, JSON_PRETTY_PRINT));
                }
            }
            if($this->jsonPostData) {
                if(isset($this->jsonPostData['x-yauth'])) {
                    $this->authParams = $this->jsonPostData['x-yauth'];
                    $this->authParams['type'] = 'x-yauth';
                }
                if(isset($this->authParams['method'])) {
                    $this->method = $this->authParams['method'];
                }
            }
        }
        if(sizeof($this->authParams) == 0 && isset($_SERVER['PHP_AUTH_DIGEST'])) {
            preg_match_all('~(nonce|nc|cnonce|qop|username|uri|response)=(?:([\'"])([^\2]+?)\2|([^\s,]+))~',
                $_SERVER['PHP_AUTH_DIGEST'], $matches, PREG_SET_ORDER);
            $this->authParams['type'] = 'digest';
            foreach ($matches as $m) {
                $this->authParams[$m[1]] = $m[3] ?: $m[4];
            }
        }
        if(isset($this->authParams['uri'])) {
            // If an authentication is provided, make sure to use the authenticated URI instead of
            // then unverified parameters possibly passed in the query
            $baseurl = dirname($_SERVER['PHP_SELF']);
            if(!str_ends_with($baseurl, '/')) {
                $baseurl .= '/';
            }
            $url = $this->authParams['uri'];
            if(str_starts_with($url, $baseurl)) {
                $url = substr($url, strlen($baseurl));
            }
            $qpos = strpos($url, '?');
            if($qpos === FALSE) {
                $this->node = $url;
            } else {
                $this->node = substr($url, 0, $qpos);
                $query = substr($url, $qpos+1);
                if(str_ends_with($query, '&.')) {
                    $this->shortReq = true;
                    $query = substr($query, 0, -2);
                }
                parse_str($query, $arguments);
                foreach($arguments as $name => $value) {
                    if(is_string($value)) {
                        $this->args[$name] = $value;
                    }
                }
            }
        } else {
            // Otherwise, we can fallback to standard $_GET variables
            foreach($_GET as $name => $value) {
                if(is_string($value)) {
                    $this->args[$name] = $value;
                }
            }
            if(str_ends_with($_SERVER['REQUEST_URI'], '&.')) {
                $this->shortReq = true;
            }
        }
        if(!$this->node) {
            $this->node = '';
        }
    }

    public function getRequestTimestamp(): int
    {
        return intval(round($this->reqStartTime / 1000));
    }

    public function getIOReadTime(): int
    {
        return $this->reqProcessTime - $this->reqStartTime;
    }

    public function getProcessingTime(): int
    {
        return intval(round(1000 * microtime(true))) - $this->reqProcessTime;
    }

    public function getErrorCount(): int
    {
        return $this->nErr;
    }

    public function getWarningCount(): int
    {
        return $this->nWrn;
    }

    public function incLogCount(int $logSeverity): void
    {
        switch($logSeverity) {
            case 1: $this->nErr++; return;
            case 2: $this->nWrn++; return;
        }
    }

    public function getProtocol(): string
    {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestHostname(): string
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function getRequestURL(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getFullClientRequest(): string
    {
        return $this->getMethod().' '.$this->getRequestURL().' '.$_SERVER['SERVER_PROTOCOL'];
    }

    public function getServerPort(): int
    {
        return intVal($_SERVER['SERVER_PORT']);
    }

    public function getServerIP(): string
    {
        return $_SERVER['SERVER_ADDR'];
    }

    public function getClientIP(): string
    {
        return $this->clientIP;
    }

    public function setClientIdent(string $clientSerial, string $clientIdent): void
    {
        $this->clientSn = $clientSerial;
        $this->clientId = $clientIdent;
    }

    public function getClientSerial(): string
    {
        return $this->clientSn;
    }

    public function getClientIdent(): string
    {
        return $this->clientId;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getOrigin(): string
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && !is_null($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != 'null') {
            return $_SERVER['HTTP_ORIGIN'];
        }
        return '*';
    }

    public function getAuthUser(): string
    {
        if(isset($this->authParams['username'])) {
            return $this->authParams['username'];
        }
        return '';
    }

    public function newNonce(): string
    {
        $newSessions = [];
        if(!is_dir(VHUB4WEB_SESSIONS)) {
            mkdir(VHUB4WEB_SESSIONS, 0700);
        } else {
            $now = time();
            $files = scandir(VHUB4WEB_SESSIONS);
            if($files !== FALSE) {
                foreach($files as $fname) {
                    if(!preg_match('/^([0-9a-f]{20})_(new|act)$/', $fname, $matches)) continue;
                    if($matches[2] == 'new') {
                        $hexstamp = substr($matches[1], 0, -12);
                        $stamp = hexdec($hexstamp);
                        $newSessions[$fname] = $now - $stamp;
                    } else {
                        $this->checkSession($matches[1], $A1);
                    }
                }
            }
        }

        // Cleanup old inactive pending sessionIds
        foreach($newSessions as $fname => $age) {
            if($age > SESSION_MAX_INACTIVITY) {
                $fullpath = VHUB4WEB_SESSIONS.'/'.$fname;
                if(file_exists($fullpath)) {
                    try { @unlink($fullpath); } catch(Throwable $e) {}
                }
            }
        }

        // Allocate a new secure session ID, make sure it is unused
        do {
            $res = strtolower(dechex(time()).bin2hex(random_bytes(6)));
            $fname = "{$res}_new";
        } while(isset($newSessions[$fname]));

        // Create the new (empty) session file
        file_put_contents(VHUB4WEB_SESSIONS.'/'.$fname, '');

        // If we have too many valid pending sessions, delay new allocations
        if(sizeof($newSessions) > SESSION_MAX_PENDING) {
            usleep(199000);
        }

        return $res;
    }

    public function checkSession(string $nonce, &$A1 = null): bool
    {
        if(!is_dir(VHUB4WEB_SESSIONS)) {
            return false;
        }
        $actfile = VHUB4WEB_SESSIONS."/{$nonce}_act";
        if(!file_exists($actfile)) {
            return false;
        }
        $data = explode(':', file_get_contents($actfile));
        if(sizeof($data) < 2 || time()-hexdec($data[0]) > SESSION_MAX_INACTIVITY) {
            try { @unlink($actfile); } catch(Throwable $e) {}
            return false;
        }
        $A1 = $data[1];
        return true;
    }

    public function touchSession(string $nonce, string $A1)
    {
        if(!is_dir(VHUB4WEB_SESSIONS)) {
            return false;
        }
        $actfile = VHUB4WEB_SESSIONS."/{$nonce}_act";
        file_put_contents($actfile, dechex(time()).':'.$A1);
    }

    public function checkPassword(string $password): bool
    {
        $authvals = $this->authParams;
        $reqkeys = [ 'uri', 'nonce', 'nc', 'cnonce', 'qop', 'response' ];
        foreach($reqkeys as $key) {
            if(!isset($authvals[$key]) || !is_string($authvals[$key])) {
                VHubServer::Log($this, LOG_CLIENTREQ, 3, "Missing x-yauth parameter {$key}");
                return false;
            }
        }
        if(!is_dir(VHUB4WEB_SESSIONS)) {
            return false;
        }
        $nonce = $authvals['nonce'];
        if(!preg_match('/^([0-9a-f]{20})$/', $nonce)) {
            return false;
        }
        $newSessionFile = VHUB4WEB_SESSIONS."/{$nonce}_new";
        if(file_exists($newSessionFile)) {
            // new session with a valid nonce, check signature against password
            try { @unlink($newSessionFile); } catch(Throwable $e) {}
            $A1 = bin2hex(substr(base64_decode($password),1));
        } else if(!$this->checkSession($nonce, $A1)) {
            return false; // invalid nonce (possibly expired)
        }
        if($authvals['type'] == 'x-yauth') {
            $A2 = sha1($this->method.':'.$authvals['uri']);
            $signature = sha1($A1.':'.$authvals['nonce'].':'.$authvals['nc'].':'.$authvals['cnonce'].':'.$authvals['qop'].':'.$A2);
        } else {
            $A2 = md5($this->method.':'.$authvals['uri']);
            $signature = md5($A1.':'.$authvals['nonce'].':'.$authvals['nc'].':'.$authvals['cnonce'].':'.$authvals['qop'].':'.$A2);
        }
        if($authvals['response'] != $signature) {
            return false;
        }
        $this->touchSession($nonce, $A1);
        return true;
    }

    public function setAuthUser(string $username): void
    {
        $this->authParams['username'] = $username;
    }

    public function getNode(): string
    {
        return $this->node;
    }

    public function getArg(string $argName): ?string
    {
        if(isset($this->args[$argName])) {
            return $this->args[$argName];
        }
        return null;
    }

    public function getAllArgs(): array
    {
        return $this->args;
    }

    public function getRawPostData(): string
    {
        return $this->rawPostData;
    }

    public function getJsonPostData(): ?array
    {
        return $this->jsonPostData;
    }

    public function isShortReq(): bool
    {
        return $this->shortReq;
    }

    public function putStatus(int $status): void
    {
        http_response_code($status);
    }

    public function putHeader(string $header): void
    {
        header($header);
    }

    public function requestAuthentication(string $realm, string $reason): void
    {
        if(isset($this->authParams['type']) && $this->authParams['type'] == 'x-yauth') {
            // Our custom authentication that does not pop-up a dialog on browsers
            $this->putStatus(204);
        } else {
            // Request standard digest authentication
            $this->putStatus(401);
            $this->putHeader('WWW-Authenticate: Digest realm="' . $realm .
                '",qop="auth",nonce="' . $this->newNonce() . '",opaque="' . md5($realm) . '"');
        }
        $this->putHeader('X-Auth-Error: ' . $reason);
        // mark user as not authentified
        $this->setAuthUser('');
    }

    public function put(string $message): void
    {
        $this->dataSent .= $message;
        Print($message);
    }

    public function getDataReceived(): int
    {
        $res = strlen($this->getFullClientRequest()) + strlen($this->rawPostData);
        if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $header => $value) {
                $res += strlen($header) + strlen($value) + 4;
            }
        }
        return $res;
    }

    public function getDataSent(): int
    {
        $res = strlen($this->dataSent);
        if(function_exists('apache_response_headers')) {
            flush();
            $headers = apache_response_headers();
            foreach ($headers as $header => $value) {
                $res += strlen($header) + strlen($value) + 4;
            }
        }
        return $res;
    }

    public function getRequestTrace(): string
    {
        $eventTime = date('Y-m-d H:i:s',time());
        $clientIdent = $this->getClientIdent();
        $serverIdent = $this->getRequestHostname();
        $phpVersion = phpversion().' '.php_sapi_name();
        $res = "{$eventTime}: from {$clientIdent} ({$this->clientIP}) to {$serverIdent} (PHP {$phpVersion})\r\n";
        $res .= '--- HTTP Request: '.$this->getFullClientRequest()."\r\n";
        if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $header => $value) {
                $res .= "{$header}: {$value}\r\n";
            }
        }
        $res .= $this->rawPostData."\r\n";
        $res .= '--- HTTP Reply: '."\r\n";
        if(function_exists('apache_response_headers')) {
            flush();
            $headers = apache_response_headers();
            foreach ($headers as $header => $value) {
                $res .= "{$header}: {$value}\r\n";
            }
        }
        return $res.$this->dataSent;
    }
}

class PhpErrorException extends Exception
{
    public function __construct(int $errno, string $errstr, string $errfile, int $errline)
    {
        parent::__construct($errstr, $errno);
        $this->file = $errfile;
        $this->line = $errline;
    }
}

class VHubServer
{
    // Static properties (globals)
    public static array $DebugLevel = [
        LOG_VHUBSERVER => DEFAULT_LOGLEVEL,
        LOG_HTTPCALLBACK => DEFAULT_LOGLEVEL,
        LOG_WSCALLBACK => DEFAULT_LOGLEVEL,
        LOG_CLIENTREQ => DEFAULT_LOGLEVEL,
        LOG_TARFILE => DEFAULT_LOGLEVEL,
        LOG_DATALOGGER => DEFAULT_LOGLEVEL,
        LOG_FILESYNC => DEFAULT_LOGLEVEL
    ];

    public static array $DebugLevels = [ 'SOS - ', 'ERR - ', 'WRN - ', 'INF - ', 'NOT - ', 'DBG -' ];

    public static array $DebugName = [
        LOG_VHUBSERVER => "VSRV ",
        LOG_HTTPCALLBACK => "HTCB ",
        LOG_WSCALLBACK => "WSCB ",
        LOG_CLIENTREQ => "CREQ ",
        LOG_TARFILE => "TARF ",
        LOG_DATALOGGER => "DLOG ",
        LOG_FILESYNC => "FILE "
    ];

    // Navigable properties
    public APIRootNode $apiroot;    // Device API cache
    public NotifStream $notif;      // VHubServer output notification stream
    public FileServer $files;       // File content server

    // Regular internal properties
    protected string $datadir;      // Data directory used by this instance, including trailing slash
    protected array $fdcache;       // File descriptor cache to prevent open/close of TAR files within a single HTTP callback

    // Freely accessible files:
    protected array $safeFiles = [ 'iframe.html', 'webapp.html', 'ssdp.xml', 'index.html', 'info.json', 'favicon.svg', 'favicon.ico' ];
    // Extra parameters that do not require admin rights:
    protected array $safeParams = [ 'node', 'abs', 'ctx', 'dir', 'fw', 'hub', 'len', 'pos', 'rnd', 'scr', 'logUrl', 'id', 'run', 'utc', 'from', 'to' ];

    protected static VHubServerHTTPRequest $CurrentHTTPRequest;

    public static function ProcessHTTPRequest(): void
    {
        // Make sure PHP configuration is still OK to write logs, etc.
        $err = check_php_conf(true);
        if(sizeof($err) > 0) {
            VHubServer::DisplayFriendlyErrors($err);
        }

        // Install global error and exception handlers
        set_error_handler('VHubServer::ErrorHandler', E_ALL);
        set_exception_handler('VHubServer::ExceptionHandler');

        // Dispatch HTTP request
        $request = new VHubServerHTTPRequest();
        VHubServer::$CurrentHTTPRequest = $request;
        $isHub = preg_match('/VirtualHub|YoctoHub/', $request->getUserAgent());
        if(preg_match('~^HTTPCallback$~i', $request->getNode())) {
            // Make sure this request does not come from a browser
            if(!$isHub) {
                VHubServer::DisplayFriendlyErrors([[
                    'error' => 'UserAgent',
                    'msg' => 'This service URL is not meant to be called by a web browser.',
                    'cause' => 'The URL ending with <b>HTTPCallback</b> should only be used as HTTP callback URL by '.
                        'VirtualHub or by a YoctoHub. In order to access VirtualHub-4web UI, remove HTTPCallback '.
                        'from the browser address.'
                ]]);
            }
            // Invoke HTTP callback support code
            VHubServer::HTTPCallback($request);
        } else {
            // Make sure this request does not come from a YoctoHub/VirtualHub
            if($isHub) {
                $url = $request->getRequestHostname().$request->getRequestURL();
                VHubServer::Abort($request, 'Hub configuration error: '.$url.' is not a correct HTTP Callback URL');
            }
            // Invoke Hub emulation support code
            VHubServer::ClientRequest($request);
        }
    }

    /**
     * Display user-friendly error messages
     */
    public static function DisplayFriendlyErrors(array $err): void
    {
        // Note: this function is used to report early configuration errors,
        //       before even creating the VHubServerHTTPRequest object
        Print("<style>\n");
        Print("body{font-family:sans-serif;text-align:justify;background-color:lightyellow;}\n");
        Print("a{font-size:small;}\n");
        Print(".more{display:none;font-style:italic;padding:6px;width:600px;}\n");
        Print("</style>\n");
        Print("<h2>VirtualHub-4web fatal error</h2>\n");
        Print("<script>\nfunction show(id) { document.getElementById(id).style.display='block'; }\n</script>\n");
        if(sizeof($err) > 1) {
            Print("<p>Oops, multiple problems have been found:</p>\n");
        } else {
            Print("<p>Oops, a serious problem has been detected:</p>\n");
        }
        Print("<ul>\n");
        foreach($err as $error) {
            Print("<li>{$error['msg']} <a href='javascript:show(\"{$error['error']}\")'>tell me more</a><div class='more' id='{$error['error']}'>{$error['cause']}</div></li>\n");
        }
        Print("</ul>\n");
        die("</body>\n");
    }

    /**
     * System log function, with customizable debug level
     */
    public static function Log(VHubServerHTTPRequest $httpReq, int $logType, int $logLevel, string $message): void
    {
        if ($logLevel <= VHubServer::$DebugLevel[$logType]) {
            $logfile = VHUB4WEB_DATA.'/VHUB4WEB-logs.txt';
            $fullmsg = date('Y-m-d H:i:s ',time()).
                VHubServer::$DebugName[$logType].VHubServer::$DebugLevels[$logLevel].
                $httpReq->getClientIdent().' '.$message;
            file_put_contents($logfile, $fullmsg."\n", FILE_APPEND | LOCK_EX);
            if(filesize($logfile) > SERVERLOGS_MAX_SIZE) {
                rename($logfile, VHUB4WEB_DATA.'/VHUB4WEB-logs-older.txt');
            }
        }
        $httpReq->incLogCount($logLevel);
    }

    /*
     * Abort execution immediately, logging the fatal error
     */
    public static function Abort(VHubServerHTTPRequest $httpReq, string $message, array $stackTrace = []): void
    {
        VHubServer::Log($httpReq, LOG_VHUBSERVER, 0, $message);
        $httpReq->put(htmlspecialchars($message)."\n");
        // If the fatal error is caused by a hub callback, keep the latest trace in a separate text file
        $hubSerial = $httpReq->getClientSerial();
        if($hubSerial) {
            $tracefile = VHUB4WEB_DATA."/{$hubSerial}-fatal.trace";
            $tracedata = $httpReq->getRequestTrace();
            // append full debug information to trace file
            $tracedata .= "--- Fatal Error:\r\n{$message}\r\n";
            for($i = 0; $i < sizeof($stackTrace); $i++) {
                $origin = basename($stackTrace[$i]['file']).':'.$stackTrace[$i]['line'];
                if($i+1 < sizeof($stackTrace)) {
                    $nextLevel = $stackTrace[$i + 1];
                    $classPrefix = '';
                    if (isset($nextLevel['class']) && $nextLevel['class'] != '') {
                        $classPrefix = $nextLevel['class'] . '::';
                    }
                    $origin = $classPrefix . $stackTrace[$i + 1]['function'] . " ({$origin})";
                }
                $tracedata .= "called from {$origin}\r\n";
            }
            file_put_contents($tracefile, $tracedata);
        }
        die("\nAbort.\n");
    }

    /*
     * Global error handler function: convert all errors to exceptions
     */
    public static function ErrorHandler(int $errno, string $errstr, string $errfile, int $errline): void
    {
        throw new PhpErrorException($errno, $errstr, $errfile, $errline);
    }

    /*
     * Global error handler function: convert all errors to exceptions
     */
    public static function ExceptionHandler(Throwable $ex): void
    {
        // We don't receive the context from the caller in case of exception,
        // so we need to use the static variable. This works for PHP since
        // there is only one request per process
        $httpReq = VHubServer::$CurrentHTTPRequest;
        if(is_null($httpReq)) {
            $httpReq = new VHubServerHTTPRequest(true);
        }
        $origin = basename($ex->getFile()).':'.$ex->getLine();
        $stackTrace = $ex->getTrace();
        if(sizeof($stackTrace) > 0) {
            $classPrefix = '';
            if(isset($stackTrace[0]['class']) && $stackTrace[0]['class'] != '') {
                $classPrefix = $stackTrace[0]['class'].'::';
            }
            $origin = $classPrefix.$stackTrace[0]['function']." ({$origin})";
        }
        VHubServer::Abort($httpReq, $ex->getMessage()." in ".$origin, $stackTrace);
    }

    /**
     * Entry point for HTTP Callbacks
     */
    public static function HTTPCallback(VHubServerHTTPRequest $httpReq): void
    {
        if($httpReq->getMethod() != "POST") {
            VHubServer::Abort($httpReq, 'Invalid HTTP method, expected a Yocto-API POST Callback');
        }

        // The input stream was already consumed, we need to make it available to the YoctoLib API
        $_SERVER['HTTP_RAW_POST_DATA'] = $httpReq->getRawPostData();
        $_SERVER['HTTP_JSON_POST_DATA'] = $jsonPostData = $httpReq->getJsonPostData();

        // Identify the network hub first
        if(isset($jsonPostData['serial'])) {
            $hubSerial = $jsonPostData['serial'];
        } else {
            $hubSerial = $jsonPostData['/api.json']['module']['serialNumber'];
        }

        // In PHP, we have to instantiate a new server for every connection (not persistent accross calls)
        $server = new VHubServer($httpReq, VHUB4WEB_DATA);
        $server->loadState($httpReq);

        // enable HTTP callback Cache
        if(!file_exists(VHUB4WEB_DATA . "/cache_dir")) {
            mkdir(VHUB4WEB_DATA . "/cache_dir");
        }
        YAPI::SetHTTPCallbackCacheDir(VHUB4WEB_DATA . "/cache_dir");

        // Try to RegisterHub - if it fails, we will catch the exception from caller
        $errmsg = '';
        if($server->apiroot->cloudConf->md5signPwd) {
            $auth = base64_decode($server->apiroot->cloudConf->md5signPwd);
            YAPI::RegisterHub("{$auth}@callback", $errmsg);
        } else {
            YAPI::RegisterHub("callback", $errmsg);
        }

        // Try to retrieve the network name
        $network = YNetwork::FindNetwork($hubSerial.'.network');
        if($network->isOnline()) {
            $hubName = $network->get_logicalName();
        } else {
            $hubName = $hubSerial;
        }
        $httpReq->setClientIdent($hubSerial, $hubName);
        VHubServer::Log($httpReq, LOG_HTTPCALLBACK, 5, 'Incoming HTTP Callback from ' . $hubName);

        $server->prepareToNotify($httpReq);
        $nReset = 0;
        $nDevices = $server->discoverDevices($httpReq, $nReset);
        $server->transferDeviceFiles($httpReq);
        if(isset($jsonPostData['tRepBuf'])) {
            $tRepBufSize = $jsonPostData['tRepBuf'];
            $tRepDataSize = $server->processTimedReports($httpReq, $hubSerial);
            $tRepUsage = intVal(round(100 * $tRepDataSize / $tRepBufSize));
        } else {
            $server->emulateTimedReports($httpReq);
            $tRepUsage = -1;
        }
        $server->executePendingQueries($httpReq, $network->get_serialNumber());
        $server->saveDeviceState($httpReq);
        $server->saveState($httpReq);
        $server->closeNotificationStream($httpReq);
        $httpReq->put('VirtualHub-4web callback complete.');
        // Save last request for trace purposes
        if($httpReq->getErrorCount() > 0) {
            $reqfile = 'lastError.trace';
        } else if($httpReq->getWarningCount() > 0) {
            $reqfile = 'lastWarning.trace';
        } else {
            $reqfile = 'lastCallback.trace';
        }
        $trace = $httpReq->getRequestTrace();
        $server->files->saveDeviceFile($httpReq, $hubSerial, $reqfile, $trace);
        // backup any previous fatal trace in the same place
        $tracefile = VHUB4WEB_DATA."/{$hubSerial}-fatal.trace";
        if(file_exists($tracefile)) {
            $fataltrace = file_get_contents($tracefile);
            $server->files->saveDeviceFile($httpReq, $hubSerial, 'lastFatal.trace', $fataltrace);
            unlink($tracefile);
        }
        // Update hub stats at the very end
        $hubNode = $server->apiroot->bySerial->subnode($hubSerial);
        $hubStats = $hubNode->getDeviceStats();
        if(!is_null($hubStats)) {
            $hubStats->appendStats($httpReq, $tRepUsage, $nDevices, $nReset);
            $statsObj = $hubStats->saveState();
            $server->files->saveDeviceFile($httpReq, $hubSerial, 'stats.json', json_encode($statsObj, JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * Entry point for API client requests
     */
    public static function ClientRequest(VHubServerHTTPRequest $httpReq): void
    {
        $server = new VHubServer($httpReq, VHUB4WEB_DATA);
        $server->loadState($httpReq);

        // Allow cross-origin requests, including authentication
        $httpReq->putHeader('Access-Control-Allow-Origin: '.$httpReq->getOrigin());
        $httpReq->putHeader('Access-Control-Allow-Credentials: true');
        $httpReq->putHeader('Access-Control-Allow-Headers: Authorization');
        $httpReq->putHeader('Vary: Origin');
        $httpReq->putHeader('X-DNS-Prefetch-Control: off');

        // Parse requested node path
        $reqpath = $httpReq->getNode();
        if($reqpath == '') {
            $defaultPage = $server->apiroot->api->network->getattr('defaultPage');
            if($defaultPage == '') {
                $defaultPage = 'index.html';
            }
            $rootUrl = parse_url($httpReq->getRequestURL(), PHP_URL_PATH);
            if(!str_ends_with($rootUrl, '/')) {
                $rootUrl .= '/';
            }
            $httpReq->putStatus(302);
            $httpReq->putHeader('Location: '.$rootUrl.$defaultPage);
            return;
        }
        $extension = '';
        $nodepath = explode('/', $reqpath);
        $filename = array_pop($nodepath);
        if($filename == '') {
            $filename = array_pop($nodepath);
        }
        $filepart = explode('.', $filename);
        if(sizeof($filepart) > 1) {
            // remove file extension
            $extension = $filepart[sizeof($filepart)-1];
            $nodepath[] = substr($filename, 0, -(strlen($extension)+1));
        } else {
            $nodepath[] = $filename;
        }

        // Determines if authentication is required
        $userPwd = $server->apiroot->api->network->getattr('userPassword');
        $adminPwd = $server->apiroot->api->network->getattr('adminPassword');
        $requiresAdmin = false;
        $requiresAuth = false;
        if(sizeof($nodepath) > 1 || array_search($reqpath, $server->safeFiles) === false) {
            if($userPwd != '') {
                $requiresAuth = true;
            }
        }
        if($adminPwd) {
            if($httpReq->getMethod() != 'GET') {
                $requiresAuth = true;
                $requiresAdmin = true;
            } else if($nodepath[0] != 'iframe') {
                $allArgs = $httpReq->getAllArgs();
                foreach($allArgs as $key => $value) {
                    if(array_search($key, $server->safeParams) !== false) continue;
                    if($key == 'a' && ($value == 'list' || $value == 'dir')) continue;
                    if($key == 'f' && isset($allArgs['a']) && $allArgs['a'] == 'dir') continue;
                    $requiresAuth = true;
                    $requiresAdmin = true;
                    break;
                }
            }
        }
        if($requiresAuth) {
            if(!$server->digestAuthenticate($httpReq)) {
                die('Unauthorized user');
            }
            if($userPwd != '' && $adminPwd == '') {
                // when only a user password is set, accept only 'user'
                if($httpReq->getAuthUser() == 'admin') {
                    die('Unauthorized user');
                }
            } else if($adminPwd != '') {
                // if an admin password is set, make sure only 'admin' is logged in when required
                if ($requiresAdmin) {
                    if ($httpReq->getAuthUser() != 'admin') {
                        $httpReq->requestAuthentication($server->apiroot->cloudConf->authRealm, 'Admin rights required');
                        die('Admin rights required');
                    }
                }
            }
        }
        if(!$adminPwd) {
            // when no admin password is required, grant admin rights to logged user
            $httpReq->setAuthUser('admin');
        }

        // Distinguish between API requests and simple file requests
        if($nodepath[0] == 'api' ||
            ($nodepath[0] == 'bySerial' && (sizeof($nodepath) < 3 || $nodepath[2] == 'api'))) {
            $server->processAPI($httpReq, $nodepath, $extension);
            return;
        }

        $logLevel = ($filename == 'not.byn' || $filename == 'flash.json' ? 5 : 4);
        VHubServer::Log($httpReq, LOG_CLIENTREQ, $logLevel, "Sending file ".json_encode($nodepath)." ".$extension);
        // Handle local file requests
        if(sizeof($nodepath) == 1) {
            switch($filename) {
                case 'logs.txt':            // logs.txt?pos=...
                    $pos = ($httpReq->getArg('pos') ?: '0');
                    // Note: that serialNumber has not been reloaded from the config file,
                    //       so it might not be the real one. But it is anyway the one against
                    //       which the serveLogs function will compare, so that does not matter :-)
                    $server->serveLogs($httpReq, $server->apiroot->cloudConf->serialNumber, intVal($pos));
                    return;
                case 'upload.html':         // upload.html?...
                    $server->handleUpload($httpReq, '');
                    return;
                case 'not.byn':             // not.byn?len=...&abs=...
                    $server->serveNotifications($httpReq);
                    return;
                case 'flash.json':          // flash.json?a=list - ignore for now
                    $httpReq->put('{"total":0, "list":[]}');
                    return;
                case 'getInstaller.json':   // getInstaller.json?forVersion=...
                    $server->serveInstaller($httpReq);
                    return;
                case 'testcb.txt':          // testcb.txt[?w=10]
                    // FIXME: emulate callbacks to third party services ?
                    return;
                case 'cbdata.txt':          // cbdata.txt?n=
                    return;
                case 'info.json':           // info.json
                    $server->serveInfo($httpReq);
                    return;
                case 'stats.json':
                    $server->serveStats($httpReq);
                    return;
                case 'configure.json':
                    $server->serveConf($httpReq);
                    return;
                case 'edithtml.js':         // edit.thml, generated file
                    global $ApiAttrEdit;
                    $server->files->sendFileContent($httpReq, $ApiAttrEdit, 'js');
                    return;
                case 'files.json':          // files.json?a=(dir|stat|del/format)&f=...
                    $action = ($httpReq->getArg('a') ?: 'dir');
                    $fname = ($httpReq->getArg('f') ?: '*');
                    $server->files->filesCmd($httpReq, $action, $fname);
                    $server->saveState($httpReq);
                    return;
                default:
                    $server->files->sendFile($httpReq, $reqpath, $extension);
                    return;
            }
        }
        if(sizeof($nodepath) >= 3 && $nodepath[0] == 'bySerial') {
            // Send special file or cached file from subdevice if available
            switch($filename) {
                case 'logger.json':         // logger.json[?id=...&utc=...]
                case 'dataLogger.json':     // dataLogger.json[?id=...&utc=...]
                    $fid = ($httpReq->getArg('id') ?: '');
                    $run = ($httpReq->getArg('run') ?: '');
                    $utc = ($httpReq->getArg('utc') ?: '');
                    $fromUtc = ($httpReq->getArg('from') ?: '');
                    $toUtc = ($httpReq->getArg('to') ?: '');
                    $server->serveLogger($httpReq, $nodepath[1], $fid, $run, $utc, $fromUtc, $toUtc, ($filename != 'logger.json'));
                    return;
                case 'logs.txt':            // logs.txt?pos=...
                    $pos = ($httpReq->getArg('pos') ?: '0');
                    $server->serveLogs($httpReq, $nodepath[1], intVal($pos));
                    return;
                case 'upload.html':         // upload.html?...
                    $server->handleUpload($httpReq, $nodepath[1]);
                    return;
                case 'files.json':          // files.json?a=(dir|stat|del/format)&f=...
                    $action = ($httpReq->getArg('a') ?: 'dir');
                    $fname = ($httpReq->getArg('f') ?: '*');
                    $server->files->deviceFilesCmd($httpReq, $nodepath[1], $action, $fname);
                    $server->saveState($httpReq);
                    return;
            }
            $server->files->sendDeviceFile($httpReq, $nodepath[1], implode('/', array_slice($nodepath, 2)).'.'.$extension, $extension);
            return;
        }
        $server->files->sendFile($httpReq, $reqpath, $extension);
    }

    /**
     * VHubServer constructor
     */
    public function __construct(VHubServerHTTPRequest $httpReq, string $datadir)
    {
        $this->datadir = $datadir.'/';
        $this->apiroot = new APIRootNode($httpReq, $this, '');
        $this->files = new FileServer($this);
        $this->fdcache = [];
    }

    /**
     * Return the data directory
     */
    public function getDataDir(): string
    {
        return $this->datadir;
    }

    /**
     * Tests if a datafile exists
     */
    public function fexists(string $relativePath): bool
    {
        return file_exists($this->datadir.$relativePath);
    }

    /**
     * Tests if a datafile exists
     */
    public function filesize(string $relativePath): int
    {
        return filesize($this->datadir.$relativePath);
    }

    /**
     * Open (or emulate open) of a file, leveraging fd cache for .tar files
     */
    protected function fopen_cached(VHubServerHTTPRequest $httpReq, string $relativePath): mixed
    {
        if(str_ends_with($relativePath, '.tar')) {
            if (!isset($this->fdcache[$relativePath])) {
                $fp = fopen($this->datadir . $relativePath, "r+b");
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fopen({$relativePath}): fp={$fp}");
                $this->fdcache[$relativePath] = $fp;
            } else {
                $fp = $this->fdcache[$relativePath];
                fseek($fp, 0, SEEK_SET);
            }
        } else {
            $fp = fopen($this->datadir . $relativePath, "r+b");
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fopen({$relativePath}): fp={$fp}");
        }
        return $fp;
    }

    /**
     * Open a shared file for "read-only" access (shared lock)
     * Actual permission allow write access to file descriptor, when non exclusive access is needed
     */
    public function fopen_ro(VHubServerHTTPRequest $httpReq, string $relativePath): mixed
    {
        $fp = $this->fopen_cached($httpReq, $relativePath);
        if (!flock($fp, LOCK_SH)) { // acquire a shared lock for reading
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Could not get shared lock to read {$relativePath}");
        }
        return $fp;
    }

    /**
     * Open a shared file for read-write access (exclusive lock)
     */
    public function fopen_rw(VHubServerHTTPRequest $httpReq, string $relativePath): mixed
    {
        $fp = $this->fopen_cached($httpReq, $relativePath);
        if (!flock($fp, LOCK_EX)) { // acquire an exclusive lock for writing
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Could not get exclusive lock to write {$relativePath}");
        }
        return $fp;
    }

    /**
     * Open a shared file for rewrite (create if it does not exists, zap content)
     */
    public function frewrite(VHubServerHTTPRequest $httpReq, string $relativePath): mixed
    {
        if (isset($this->fdcache[$relativePath]) || file_exists($this->datadir . $relativePath)) {
            $fp = $this->fopen_cached($httpReq, $relativePath);
        } else {
            $fp = fopen($this->datadir . $relativePath, "wb");
        }
        if (!flock($fp, LOCK_EX)) { // acquire an exclusive lock
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Fail to get exclusive lock to rewrite file {$relativePath}");
        }
        ftruncate($fp, 0);      // truncate file (needed despite fopen(w) because of flock)
        return $fp;
    }

    /**
     * Close a shared file previously open using one of the functions above
     */
    public function fclose(VHubServerHTTPRequest $httpReq, mixed $fp, string $relativePath): void
    {
        fflush($fp);                    // flush output before releasing the lock
        flock($fp, LOCK_UN);   // release the lock

        // Keep descriptor open for optimizations if path is found in cache
        if(!isset($this->fdcache[$relativePath])) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "fclose({$relativePath}): fp={$fp}");
            fclose($fp);
        }
    }

    /**
     * Open a shared file for read-write access (exclusive lock), append text and release lock
     */
    public function fappend(VHubServerHTTPRequest $httpReq, string $relativePath, $text): void
    {
        file_put_contents($this->datadir.$relativePath, $text, FILE_APPEND | LOCK_EX);
    }

    /**
     * Safely open and read a file from data directory
     */
    public function loadFile(VHubServerHTTPRequest $httpReq, string $relativePath, bool $getExclusiveLock = false, &$filedesc = null): string
    {
        if($getExclusiveLock) {
            if(!$this->fexists($relativePath)) {
                $filedesc = null;
                return '{}';
            }
            $fp = $this->fopen_rw($httpReq, $relativePath);
            $contents = stream_get_contents($fp);
            fseek($fp, 0, SEEK_SET);
            $filedesc = $fp;
        } else {
            $fp = $this->fopen_ro($httpReq, $relativePath);
            $contents = stream_get_contents($fp);
            $this->fclose($httpReq, $fp, $relativePath);
        }
        return $contents;
    }

    /**
     * Safely open and write a file from data directory
     */
    public function saveFile(VHubServerHTTPRequest $httpReq, string $relativePath, string $content, $fp = null): void
    {
        if(!$fp) {
            $fp = $this->frewrite($httpReq, $relativePath);
        } else {
            fseek($fp, 0, SEEK_SET);
            ftruncate($fp, 0);
        }
        fwrite($fp, $content);
        $this->fclose($httpReq, $fp, $relativePath);
    }

    /**
     * Load configuration file from data directory
     */
    public function loadState(VHubServerHTTPRequest $httpReq): void
    {
        // load VirtualHub4web API state
        if(file_exists($this->datadir.STATE_FILE)) {
            // Load current state
            $apiobj = json_decode($this->loadFile($httpReq, STATE_FILE), false, 99, JSON_THROW_ON_ERROR);
            $this->apiroot->loadState($httpReq, $apiobj, false);
            $this->apiroot->loadOwnServices($httpReq);
        } else {
            // Create initial state
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Config file does not yet exist, creating one");
            $this->apiroot->loadOwnServices($httpReq);
            $cloudapiobj = $this->apiroot->saveState();
            $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        // load client hubs API state
        foreach (glob($this->datadir.'????????-?*.tar') as $tarname) {
            if(!preg_match('~/([A-Z0-9]+-[0-9a-fA-F]+).tar$~', $tarname, $matches)) {
                continue;
            }
            $serial = $matches[1];
            $apijson = $this->files->loadDeviceFile($httpReq, $serial, 'api.json');
            if(is_null($apijson)) {
                continue;
            }
            try {
                $apiobj = json_decode($apijson, false, 99, JSON_THROW_ON_ERROR);
            } catch(Throwable $err) {
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Error parsing client api file for {$serial}: ".$err->getMessage());
                continue;
            }
            $apinode = new APIDeviceNode($httpReq, $this, $serial);
            $this->apiroot->bySerial->addSubnode($serial, $apinode);
            $apinode->loadState($httpReq, $apiobj, false);
            if(sizeof($apiobj->services->whitePages) > 0 && isset($apiobj->VirtualHub4web)) {
                $hubSerial = $apiobj->VirtualHub4web->parentHub;
                $this->apiroot->loadServices($httpReq, $hubSerial, $apiobj->services, false);
                // Load statistics for root nodes
                if($serial == $hubSerial) {
                    $apinode->initStats($httpReq);
                    $statsjson = $this->files->loadDeviceFile($httpReq, $serial, 'stats.json');
                    if(is_null($statsjson)) {
                        continue;
                    }
                    try {
                        $statsobj = json_decode($statsjson, false, 99, JSON_THROW_ON_ERROR);
                    } catch(Throwable $err) {
                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Error parsing client stats file for {$serial}: ".$err->getMessage());
                        continue;
                    }
                    $devStats = $apinode->getDeviceStats();
                    $devStats->loadState($httpReq, $statsobj);
                }
            }
        }
        $this->apiroot->api->services->sortServices($httpReq);
    }

    /**
     * Safely update configuration file without overwriting concurrent changes
     */
    public function updateCloudState(VHubServerHTTPRequest $httpReq): void
    {
        $stateChanges = $this->apiroot->getStateChanges($httpReq);
        if(sizeof($stateChanges) > 0) {
            // Reload state file while keeping an exclusive lock to update it
            $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), true, 99, JSON_THROW_ON_ERROR);
            // Selectively update changed values
            foreach($stateChanges as $key => $value) {
                if (!str_ends_with($key, 'Password')) {
                    // don't log password changes, to avoid security problems
                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Attribute change: $key = $value");
                }
                if ($key != 'persistentSettings') {
                    $cloudapiobj['VirtualHub4web']['valuesCache'][$key] = $value;
                }
            }
            // Handle persistentSettings change
            if(isset($stateChanges['persistentSettings'])) {
                switch($stateChanges['persistentSettings']) {
                    case 0: // revert from last saved settings
                        foreach ($cloudapiobj['VirtualHub4web']['savedSettings'] as $key => $savedValue) {
                            $cloudapiobj['VirtualHub4web']['valuesCache'][$key] = $savedValue;
                        }
                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 0;
                        break;
                    case 1: // save settings to persistent storage
                        foreach ($this->apiroot->cloudConf->savedSettings as $key => $prevValue) {
                            if(isset($cloudapiobj['VirtualHub4web']['valuesCache'][$key])) {
                                $cloudapiobj['VirtualHub4web']['savedSettings'][$key] = $cloudapiobj['VirtualHub4web']['valuesCache'][$key];
                            }
                        }
                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 1;
                        break;
                }
            } else {
                foreach ($cloudapiobj['VirtualHub4web']['savedSettings'] as $key => $savedValue) {
                    if($cloudapiobj['VirtualHub4web']['valuesCache'][$key] != $savedValue) {
                        $cloudapiobj['VirtualHub4web']['valuesCache']['persistentSettings'] = 2;
                        break;
                    }
                }
            }
            // Save file and release lock
            $apitxt = json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->saveFile($httpReq, STATE_FILE, $apitxt, $fp);
            // Reload updated state
            $apiobj = json_decode($apitxt, false, 99, JSON_THROW_ON_ERROR);
            $this->apiroot->loadState($httpReq, $apiobj, false);
        }
    }

    /**
     * Save all configuration files
     */
    public function saveState(VHubServerHTTPRequest $httpReq): void
    {
        $this->updateCloudState($httpReq);
        foreach($this->apiroot->bySerial->subnodeNames() as $serial) {
            $subnode = $this->apiroot->bySerial->subnode($serial);
            if($subnode->hasChanged()) {
                // Note: this code will change the state file so that next answers to client API
                // stay coherent, but the propagation to the device itself is handled separately
                // by the temporary "-changes.txt" file associated to the device hub
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Updating api.json for {$serial} after change");
                $apiobj = $subnode->saveState();
                $this->files->saveDeviceFile($httpReq, $serial, 'api.json', json_encode($apiobj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            }
        }
    }

    /*
     * Open the notification channel for writing
     */
    public function prepareToNotify(VHubServerHTTPRequest $httpReq): void
    {
        $this->notif = NotifStream::StreamAt($httpReq, $this, -1);
        $this->notif->openForAppend($httpReq);
    }

    /*
     * Close the notification channel for writing
     */
    public function closeNotificationStream(VHubServerHTTPRequest $httpReq): void
    {
        $this->notif->close($httpReq);
    }

    /**
     * urlencode according to RFC 3986 instead of php default RFC 1738
     */
    public function _escapeAttr(string $attrval): string
    {
        $safecodes = [ '%21', '%23', '%24', '%27', '%28', '%29', '%2A', '%2C', '%2F', '%3A', '%3B', '%40', '%3F', '%5B', '%5D' ];
        $safechars = [ '!', "#", "$", "'", "(", ")", '*', ",", "/", ":", ";", "@", "?", "[", "]" ];
        return str_replace($safecodes, $safechars, urlencode($attrval));
    }

    /**
     * Manual digest authentication support (to be URL-selective)
     */
    public function digestAuthenticate(VHubServerHTTPRequest $httpReq): bool
    {
        $realm = $this->apiroot->cloudConf->authRealm;
        $user = $httpReq->getAuthUser();
        if(!$user) {
            $httpReq->requestAuthentication($realm, 'Authentication required');
            return false;
        }
        if($user != 'user' && $user != 'admin') {
            $httpReq->requestAuthentication($realm, "Unknown user {$user}");
            return false;
        }

        // check password
        $pwd = $this->apiroot->api->network->getattr($user.'Password');
        if(!$httpReq->checkPassword($pwd)) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Authentication failure for user {$user} from IP ".$httpReq->getClientIP());
            $httpReq->requestAuthentication($realm, "Invalid credentials for user {$user}");
            return false;
        }

        // login successful
        return true;
    }

    /**
     * Run (or schedule) a request on a device next time it becomes available
     */
    public function scheduleUploadOnDevice(VHubServerHTTPRequest $httpReq, string $targetSerial, string $str_path, string $bin_content): void
    {
        $body = "Content-Disposition: form-data; name=\"$str_path\"; filename=\"api\"\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "Content-Transfer-Encoding: binary\r\n\r\n" . $bin_content;
        do {
            $boundary = sprintf("Zz%06xzZ", mt_rand(0, 0xffffff));
        } while (str_contains($body, $boundary));
        $mimebody = "--{$boundary}\r\n{$body}\r\n--{$boundary}--\r\n";
        $this->scheduleQueryOnDevice($httpReq, $targetSerial, 'POST', '/upload.html', $mimebody);
    }

    /**
     * Run (or schedulwe) a request on a device next time it becomes available
     */
    public function scheduleQueryOnDevice(VHubServerHTTPRequest $httpReq, string $targetSerial, string $reqType, string $url, string $body = ''): void
    {
        $deviceNode = $this->apiroot->bySerial->subnode($targetSerial);
        $rootHub = $deviceNode->cloudConf->parentHub;
        if($rootHub == '') {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Cannot apply change to {$targetSerial}, unknown parent hub");
            return;
        }
        if($targetSerial != $rootHub) {
            $url = '/bySerial/'.$targetSerial.$url;
        }
        $fullreq = date('Y-m-d_H:i:s ',time()).$reqType.' '.$url."\n";
        if($body != '') {
            $fullreq .= base64_encode($body)."\n";
        }
        $this->fappend($httpReq, $rootHub.'-pending.req', $fullreq);
    }

    /*
     * Send a callback API command to the connected YoctoHub
     */
    public function sendCallbackApiCommand(VHubServerHTTPRequest $httpReq, string $command, ?string $extradata = null): void
    {
        VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "@YoctoAPI:".$command);
        $httpReq->put("\n@YoctoAPI:{$command}\n");
        if(!is_null($extradata)) {
            $httpReq->put($extradata."\n");
        }
    }

    /*
     * Execute pending queries when a device is connected
     */
    public function executePendingQueries(VHubServerHTTPRequest $httpReq, string $rootHub): void
    {
        // check if there are pending queries for the specified root hub
        $pendingfile = $this->datadir.$rootHub.'-pending.req';
        if(!file_exists($pendingfile)) {
            return;
        }
        // load and unlink (atomically) pending reqiests
        $runningfile = str_replace('pending', 'running', $pendingfile);
        rename($pendingfile, $runningfile);
        $requests = preg_split('/\r\n|\r|\n/', file_get_contents($runningfile));
        unlink($runningfile);
        for($i = 0; $i < sizeof($requests); $i++) {
            $req = explode(' ', $requests[$i]);
            if(sizeof($req) < 3) {
                continue;
            }
            $reqUrl = trim($req[2]);
            if($req[1] == 'GET') {
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Execute GET ".json_encode($reqUrl));
                $this->sendCallbackApiCommand($httpReq, 'GET '.$reqUrl);
            } else if($req[1] == 'POST') {
                if($i+1 >= sizeof($requests)) {
                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Cannot execute POST request on {$reqUrl}, missing body");
                    return;
                }
                $str_body = base64_decode($requests[++$i]);
                $boundary = '???';
                $endb = strpos($str_body, "\r");
                if (str_starts_with($str_body, '--') && $endb > 2 && $endb < 20) {
                    $boundary = substr($str_body, 2, $endb - 2);
                }
                $this->sendCallbackApiCommand($httpReq, 'POST '.$reqUrl.' '.strlen($str_body).':'.$boundary, $str_body);
            }
        }
        // requests have been executed, force next callback immediately
        $this->sendCallbackApiCommand($httpReq, '%');
    }

    public function tryDownload(VHubServerHTTPRequest $httpReq, string $serial, string $fname, bool $requestAgain): ?string
    {
        VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "Try to load {$fname} from {$serial}");
        $module = YModule::FindModule($serial.'.module');
        try {
            $fcontent = $module->_download($fname);
            if(str_starts_with($fcontent, '64#')) {
                $fcontent = substr($fcontent,3);
                $fcontent = base64_decode($fcontent);
            }
            if($requestAgain) {
                // request file for the next time anyway
                $apinode = $this->apiroot->bySerial->subnode($serial);
                $rootHub = $apinode->cloudConf->parentHub;
                $rootUrl = ($rootHub != $serial ? '/bySerial/'.$serial : '');
                $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$fname}");
            }
            return $fcontent;
        } catch(Throwable $exception) {
            // Most probably caused by the file content not being posted in the HTTP callback data
            $serial = $module->get_serialNumber();
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 5, "Cannot load {$fname} from {$serial}: ".$exception->getMessage());
        }
        return null;
    }

    /**
     * Discover connected devices (when Yocto-API is available, e.g. during HTTP Callback)
     * Return the number of discovered devices
     */
    public function discoverDevices(VHubServerHTTPRequest $httpReq, &$nReset): int
    {
        // First create a list of modules with yoctohubs at the bottoms, and the virtualhubs at the very end
        $clientIP = $httpReq->getClientIP();
        $modules = [];
        $virthubs = [];
        $nReset = 0;
        $module = YModule::FirstModule();
        while($module) {
            $apibin = $module->_download('api.json');
            $apistr = iconv("ISO-8859-1", "UTF-8", $apibin);
            $apiobj = new stdClass();
            $apiobj->api = json_decode($apistr, false, 99, JSON_THROW_ON_ERROR);
            $toadd = ['module' => $module, 'apiobj' => $apiobj];
            if(isset($apiobj->api->services)) {
                $prodId = $apiobj->api->module->productId;
                if($prodId == 0xc10d) {
                    // add any other virtualhub-4web very very last, even after virtualhubs
                    $virthubs[] = $toadd;
                } else if($prodId == 0) {
                    // add virtualhub in a separate list
                    array_unshift($virthubs, $toadd);
                } else {
                    // add yoctohubs at the end
                    $modules[] = $toadd;
                }
            } else {
                // add other module at the start
                array_unshift($modules, $toadd);
            }
            $module = $module->nextModule();
        }
        foreach($virthubs as $toadd) {
            $modules[] = $toadd;
        }

        // Then process the list in this order
        for($mi = 0; $mi < sizeof($modules); $mi++) {
            $module = $modules[$mi]['module'];
            $apiobj = $modules[$mi]['apiobj'];
            $serial = $module->get_serialNumber();
            if($this->apiroot->bySerial->hasSubnode($serial)) {
                $apinode = $this->apiroot->bySerial->subnode($serial);
                $lastSeen = $apinode->api->module->getattr('lastSeen');
                $prevUptime = $apinode->api->module->getattr('upTime');
                $apinode->loadState($httpReq, $apiobj, true);
                // detect device resets
                $newUptime = $apinode->api->module->getattr('upTime');
                $deltaUptime = ($newUptime - $prevUptime) & 0xffffffff;
                $safeUptimeSec = intdiv(max($newUptime, $deltaUptime), 1000);
                // Ensure that uptime difference matches expectations with 2 % margin + 10 sec
                $wasReset = abs($safeUptimeSec - $lastSeen) < (0.02*$lastSeen + 10);
                if($wasReset) {
                    $apinode->cloudConf->deviceResetDetected();
                    $nReset++;
                }
            } else {
                $apinode = new APIDeviceNode($httpReq, $this, $serial);
                $apinode->loadState($httpReq, $apiobj, true);
                $this->apiroot->bySerial->addSubnode($serial, $apinode);
            }
            $apinode->cloudConf->lastSeen = time();
            if($apinode->cloudConf->reconnect) {
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 3, "Fast reconnect requested for {$serial}");
                $apinode->cloudConf->reconnect = 0;
                $this->sendCallbackApiCommand($httpReq, '%');
            }
            $apinode->markAsChanged();
            if(isset($apiobj->api->services) && substr($serial, 0, 7) != 'YHUBSHL') {
                $devYdxExists = $this->apiroot->loadServices($httpReq, $serial, $apiobj->api->services, false);
                if (!$devYdxExists) {
                    // Reload state file while keeping an exclusive lock to update it
                    $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, JSON_THROW_ON_ERROR);
                    $this->apiroot->loadState($httpReq, $cloudapiobj, false);
                    $this->apiroot->loadServices($httpReq, $serial, $apiobj->api->services, true);
                    $cloudapiobj = $this->apiroot->saveState();
                    $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);
                }
                // store link to parent hub and services in subdevice
                $wpdef = $apiobj->api->services->whitePages;
                foreach ($wpdef as $wpentry) {
                    $subserial = $wpentry->serialNumber;
                    if(!$this->apiroot->bySerial->hasSubnode($subserial)) {
                        // device is supposed to have been loaded first
                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Dropping services for unknown serial {$subserial}; possibly a subdevice of a hub connected via USB?");
                        continue;
                    }
                    $apisubnode = $this->apiroot->bySerial->subnode($subserial);
                    if($apisubnode->cloudConf->parentHub != $serial || $apisubnode->cloudConf->parentIP != $clientIP) {
                        $apisubnode->cloudConf->parentHub = $serial;
                        $apisubnode->cloudConf->parentIP = $clientIP;
                        $apisubnode->markAsChanged();
                    }
                    $subservices = $this->apiroot->saveServicesForSerial($subserial);
                    $apisubnode->services->loadState($httpReq, $subservices, true);
                }
            }
        }
        return sizeof($modules);
    }

    /**
     * Download and upload files to module (when Yocto-API is available, e.g. during HTTP Callback)
     */
    public function transferDeviceFiles(VHubServerHTTPRequest $httpReq): void
    {
        $module = YModule::FirstModule();
        while($module) {
            $serial = $module->get_serialNumber();
            $apinode = $this->apiroot->bySerial->subnode($serial);
            $knownFirmware = $apinode->cloudConf->yfsVer;
            $currentfirmware = $apinode->api->module->getattr('firmwareRelease');

            // Download built-in files if needed
            $yfsFiles = [];
            if(!$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/icon2d.png')) {
                $yfsFiles[] = 'icon2d.png';
            }
            if (!str_starts_with($serial, 'Y3DMK001')) { // Yocto-3D has no built-in UI
                if ($knownFirmware != $currentfirmware ||
                       (!$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/details.html') &&
                        !$this->files->isKnownDeviceFile($httpReq, $serial, 'yfs/details.html.gz'))) {
                    // Must download UI files
                    if ((str_contains($currentfirmware, ':') || intVal($currentfirmware) >= 51000) &&
                        !str_starts_with($serial, 'YHUB') &&
                        !str_starts_with($serial, 'VIRTHUB') &&
                        !str_starts_with($serial, 'VHUB4WEB')) {
                        // Download _FS all at once
                        try {
                            $fscontent = $module->_download('_FS');
                            if (str_starts_with($fscontent, '64#')) {
                                $fscontent = substr($fscontent, 3);
                                $fscontent = base64_decode($fscontent);
                            }
                            $this->files->saveAllDeviceFiles($httpReq, $serial, $fscontent);
                            $apinode->cloudConf->yfsVer = $currentfirmware;
                            $apinode->markAsChanged();
                            VHubServer::Log($httpReq, LOG_FILESYNC, 3, "Downloaded all UI files for {$serial}");
                        } catch (Throwable $exception) {
                            $msg = $exception->getMessage();
                            if (str_contains($msg, 'Network error')) {
                                VHubServer::Log($httpReq, LOG_FILESYNC, 2, "Failed to open _FS file for {$serial}: " . $msg);
                            }
                        }
                    } else {
                        // Older firmware, try to download individual files
                        $yfsFiles[] = 'details.html';
                        $yfsFiles[] = 'configure.html';
                    }
                }
            }
            foreach ($yfsFiles as $fname) {
                $fcontent = $this->tryDownload($httpReq, $serial, $fname, false);
                if (!is_null($fcontent)) {
                    if (strlen($fcontent) > 4 && ord($fcontent[0]) == 0x1f && ord($fcontent[1]) == 0x8b) {
                        $fname .= '.gz';
                    }
                    $this->files->saveDeviceFile($httpReq, $serial, 'yfs/' . $fname, $fcontent);
                    if(str_starts_with($fname, 'details.html')) {
                        $apinode->cloudConf->yfsVer = $currentfirmware;
                        $apinode->markAsChanged();
                        VHubServer::Log($httpReq, LOG_FILESYNC, 3, "Downloaded individual UI files for {$serial}");
                    }
                }
            }

            // Check latest logs as well
            $rootHub = $apinode->cloudConf->parentHub;
            $rootUrl = ($rootHub != $serial ? '/bySerial/'.$serial : '');
            try {
                $logUrl = 'logs.txt';
                if($apinode->cloudConf->logPos != 0) {
                    $logUrl .= '?pos='.$apinode->cloudConf->logPos;
                }
                $logs = $module->_download($logUrl);
                $endPos = strrpos($logs, "\n@");
                if($endPos > 0) {
                    $newLogPos = intVal(substr($logs, $endPos+2));
                    $logs = date("[Y-m-d H:i:s]\n", time()).substr($logs, 0, $endPos);
                    $prevLogs = $this->files->loadDeviceFile($httpReq, $serial, 'logs.txt');
                    if(!is_null($prevLogs)) {
                        $prevLogs = preg_replace('~ *$~', '', $prevLogs);
                        $logs = $prevLogs.$logs;
                    }
                    $logsLen = strlen($logs);
                    if($logsLen > DEVICELOGS_MAX_SIZE) {
                        $logs = substr($logs, -DEVICELOGS_MAX_SIZE);
                    } else if($logsLen < DEVICELOGS_MAX_SIZE) {
                        $logs .= str_repeat(' ', DEVICELOGS_MAX_SIZE - $logsLen);
                    }
                    $this->files->saveDeviceFile($httpReq, $serial, 'logs.txt', $logs);
                    $apinode->cloudConf->logPos = $newLogPos;
                    $this->notif->appendConfigChangeNotification($httpReq, $serial);
                    $apinode->markAsChanged();
                    // request new logs.txt for the next time
                    $logUrl = 'logs.txt?pos='.$apinode->cloudConf->logPos;
                    $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$logUrl}");
                } else {
                    // no new log, request logs.txt next time nevertheless
                    $this->sendCallbackApiCommand($httpReq, "+{$rootUrl}/{$logUrl}");
                }
            } catch(Throwable $exception) {
                $msg = $exception->getMessage();
                if(!str_contains($msg, 'Network error')) {
                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to open logs.txt for {$serial}: ".$msg);
                }
                $httpReq->put('logs.txt not available: '.$exception->getMessage()."\r\n");
            }

            // Download extra files for specific modules/functions
            if($apinode->api->hasSubnode('display')) {
                $fname = 'display.gif';
                $fcontent = $this->tryDownload($httpReq, $serial, $fname, true);
                if(!is_null($fcontent)) {
                    $this->files->saveDeviceFile($httpReq, $serial, $fname, $fcontent);
                }
            }
            if($apinode->api->hasSubnode('files')) {
                $fname = 'files.json';
                $fcontent = $this->tryDownload($httpReq, $serial, $fname, true);
                if(!is_null($fcontent)) {
                    try {
                        // process file records
                        $filesRecs = json_decode($fcontent, false, 99, JSON_THROW_ON_ERROR);
                        if($apinode->fileList->compareToDevice($httpReq, $filesRecs)) {
                            $apinode->markAsChanged();
                        }
                    } catch(Throwable $exception) {
                        VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to parse files.json for {$serial}: ".$exception->getMessage());
                    }
                }
            }

            $module = $module->nextModule();
        }
    }

    /**
     * Retrieve and decode timed report from special hub buffer availabl in Yocto-API mode
     * (when invoked by an HTTP Callback)
     * Returns the amount of timed reports issued since last callback, in bytes
     */
    public function processTimedReports(VHubServerHTTPRequest $httpReq, string $hubSerial): int
    {
        $hubModule = YModule::FindModule($hubSerial);
        // Index white page records to decode devYdx
        $hubAPI = json_decode($hubModule->_download('api.json'));
        $serialByDevYdx = [];
        foreach($hubAPI->services->whitePages as $wpRec) {
            $serialByDevYdx[$wpRec->index] = $wpRec->serialNumber;
        }
        $apinode = $this->apiroot->bySerial->subnode($hubSerial);
        $tRep = null;
        try {
            $tRepURL = 'tRep.bin';
            if($apinode->cloudConf->tRepPos != 0) {
                $tRepURL .= '?pos='.$apinode->cloudConf->tRepPos;
            }
            $tRep = $hubModule->_download($tRepURL);
        } catch(Throwable $exception) {
            $msg = $exception->getMessage();
            if(!str_contains($msg, 'Network error')) {
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 2, "Failed to open tRep.bin file for {$hubSerial}: ".$msg);
            }
            $httpReq->put('tRep.bin not available: '.$exception->getMessage()."\r\n");
        }
        if(is_null($tRep)) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "tRep not available for now");
            return -1;
        }

        // process all available timed reports
        $currDevYdx = -1;
        $currDevReports = [];
        $newTRepPos = 0;
        $newReps = 0;
        $endPos = strlen($tRep);
        for($pos = 0; $pos+2 < $endPos; ) {
            $devYdx = ord($tRep[$pos++]);
            $head = ord($tRep[$pos++]);
            $data0 = ord($tRep[$pos++]);
            $funYdx = $head & 0xf;
            $extraLen = $head >> 4;
            if($currDevYdx != $devYdx || $funYdx == 15 || $pos + $extraLen > $endPos) {
                // flush pending reports
                if($currDevYdx >= 0) {
                    if(isset($serialByDevYdx[$currDevYdx])) {
                        $currDevSerial = $serialByDevYdx[$currDevYdx];
                        $this->notif->handleTrueTimedReportNotification($httpReq, $currDevSerial, $currDevReports);
                        $currDevReports = [];
                    }
                }
            }
            if($devYdx == 0xff && $head == 0xff) {
                // end of file marker, parse end position
                $newTRepPos = $data0 + 0x100 * ord($tRep[$pos]) + 0x10000 * ord($tRep[$pos+1]) + 0x1000000 * ord($tRep[$pos+2]);
                break;
            }
            if($pos + $extraLen > $endPos) break;
            if($currDevYdx != $devYdx) {
                $currDevYdx = $devYdx;
            }
            $rawReport = [ $data0 ];
            for($i = 0; $i < $extraLen; $i++) {
                $rawReport[] = ord($tRep[$pos+$i]);
            }
            $currDevReports[$funYdx] = $rawReport;
            $pos += $extraLen;
        }
        if($newTRepPos) {
            $newReps = ($newTRepPos - $apinode->cloudConf->tRepPos) & 0xffffffff;
            $apinode->cloudConf->tRepPos = $newTRepPos;
            $apinode->markAsChanged();
            // request new logs.txt for the next time
            $tRepURL = 'tRep.bin?pos='.$apinode->cloudConf->tRepPos;
            $this->sendCallbackApiCommand($httpReq, "+/{$tRepURL}");
        } else {
            // missing events or no timed report, request tRep.bin next time nevertheless
            $this->sendCallbackApiCommand($httpReq, "+/{$tRepURL}");
        }
        return $newReps;
    }

    /**
     * Generate a pseudo-timed report and datalogger record for connected devices
     * (when invoked by an HTTP Callback)
     */
    public function emulateTimedReports(VHubServerHTTPRequest $httpReq): void
    {
        // default UTC timestamp taken from server, if no dataLogger is found
        $timestamp = time();
        $module = YModule::FirstModule();
        while($module) {
            $serial = $module->get_serialNumber();
            $deviceNode = $this->apiroot->bySerial->subnode($serial);
            $deviceApiNode = $deviceNode->api;
            $values = [];
            $fcount = $module->functionCount();
            for($i = 0; $i < $fcount; $i++) {
                if($module->functionBaseType($i) == 'Sensor') {
                    // Sensor found, check if a timed report is available
                    $functionId = $module->functionId($i);
                    $functionNode = $deviceApiNode->subnode($functionId);
                    $avgVal = $functionNode->getSensorValue();
                    if(!is_nan($avgVal)) {
                        $values[$functionId] = $avgVal;
                    }
                } else if($module->functionId($i) == 'dataLogger') {
                    $functionNode = $deviceApiNode->subnode('dataLogger');
                    $timestamp = $functionNode->get_timeUTC();
                }
            }
            if(sizeof($values) > 0) {
                $parentSerial = $deviceNode->cloudConf->parentHub;
                $parentNode = $this->apiroot->bySerial->subnode($parentSerial);
                $parentNet = $parentNode->api->subnode('network');
                $period = min(intval($parentNet->getattr('callbackMinDelay')), 3600);
                $freq = new DataFrequency($period);
                $endTime = $freq->alignTimestamp($timestamp);
                $startTime = $endTime - $period;
                $reports = [];
                foreach($values as $functionid => $avgVal) {
                    $sensor = YSensor::FindSensor("{$serial}.{$functionId}");
                    $unit = $sensor->get_unit();
                    $measure = new YMeasure($startTime, $endTime, $avgVal, $avgVal, $avgVal);
                    $reports[$functionId] = [ 'sensor' => $sensor, 'measure' => $measure, 'unit' => $unit, 'freq' => $freq];
                }
                $this->notif->appendEmulatedTimedReportNotification($httpReq, $serial, $reports);
                $logger = new DataLogger($this, $serial);
                $logger->appendMeasures($httpReq, $reports);
            }
            $module = $module->nextModule();
        }
    }

    /**
     * Update device config if needed after processing
     */
    public function saveDeviceState(VHubServerHTTPRequest $httpReq): void
    {
        $module = YModule::FirstModule();
        while($module) {
            $serial = $module->get_serialNumber();
            $apinode = $this->apiroot->bySerial->subnode($serial);
            if($apinode->hasChanged()) {
                $apiobj = $apinode->saveState();
                $this->files->saveDeviceFile($httpReq, $serial, 'api.json', json_encode($apiobj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            }
            $module = $module->nextModule();
        }
    }

    /**
     * Process a client API request (YoctoHub emulation)
     */
    public function processAPI(VHubServerHTTPRequest $httpReq, array $nodepath, string $disptype): void
    {
        // Search for requested node
        $ctx = $httpReq->getArg('ctx');
        if($ctx) {
            $ctxpath = explode('/', $ctx);
        } else {
            $ctxpath = [];
        }
        [ $apinode, $ctxnode, $subkey ] = $this->apiroot->search($nodepath, $ctxpath);
        if(is_null($apinode)) {
            $httpReq->putStatus(404);
            $httpReq->put("Sorry, the requested node ".htmlspecialchars(implode('/',$nodepath))." does not exist\r\n");
            return;
        }
        if(!is_null($ctxnode)) {
            if($ctxnode->fclass == 'Module' && $subkey == 'lastSeen' && !is_null($httpReq->getArg('lastSeen'))) {
                // special request to force immediate reconnects after next HTTP Callback
                $serial = $ctxnode->getattr('serialNumber');
                $deviceNode = $this->apiroot->bySerial->subnode($serial);
                $deviceNode->cloudConf->reconnect = 1;
                $deviceNode->markAsChanged();
                $this->saveState($httpReq);
                $httpReq->put("%OK");
                return;
            }
            // Apply changes to API nodes
            foreach($httpReq->getAllArgs() as $setattr => $setval) {
                if(array_search($setattr, ['node','fw','checkRW','rnd','ctx','scr','abs','dir','hub','len','pos','_','serialNumber','w']) !== FALSE) {
                    // not real attributes change, shortcut
                    continue;
                }
                if($setattr == 'persistentSettings' && $setval == '2') {
                    // pseudo-change to trigger an immediate config change callback on client
                    // no need to propagate this change to the client
                    $this->notif->appendConfigChangeNotification($httpReq, $ctxnode->getattr('serialNumber'));
                } else if($setattr != 'command') {
                    // - special attribute command is never stored in the api
                    VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "API requests attribute change: {$setattr} = {$setval} on ".json_encode($nodepath));
                    $ctxnode->setattr($setattr, $setval);
                }
                if($nodepath[0] == 'bySerial') {
                    // for remote devices, record change to perform next time the device becomes available
                    $relpath = array_merge(array_slice($nodepath, 2), $ctxpath, [ $setattr ]);
                    $changereq = '/'.implode('/', $relpath).'?'.$setattr.'='.$this->_escapeAttr($setval);
                    $this->scheduleQueryOnDevice($httpReq, $nodepath[1], 'GET', $changereq);
                }
            }
            $this->saveState($httpReq);
        }
        if(is_null($subkey)) {
            // Display node
            $this->files->sendContentHeader($httpReq, $disptype);
            switch($disptype) {
                case 'json':
                    $apinode->printJSON($httpReq);
                    break;
                case 'jzon':
                    $apinode->printJZON($httpReq);
                    break;
                case '':
                case 'html':
                    $devicedir = '';
                    for($i = sizeof($nodepath)-1; $i > 0 && $nodepath[$i] != 'api'; $i--) {
                        $devicedir .= '../';
                    }
                    for($basedir = $devicedir; $i > 0; $i--) {
                        $basedir .= '../';
                    }
                    $baseHRef = ($basedir != '' ? "<BASE href='{$basedir}'/>" : '');
                    $action = $httpReq->getNode();
                    $httpReq->put("<!DOCTYPE html>{$baseHRef}".
                        "<link href='edithtml.css' rel=stylesheet type='text/css'/>".
                        "<SCRIPT src='edithtml.js'></SCRIPT><SCRIPT src='js/edit.js'></SCRIPT>".
                        "<BODY onload='rescroll()'><form method='get' action='{$action}'>".
                        "<INPUT type='hidden' name='scr'><INPUT type='hidden' name='ctx'>");
                    $apinode->printHTML($httpReq, $apinode->name);
                    $httpReq->put('</FORM>');
                    break;
                case 'txt':
                    $apinode->printTXT($httpReq, $apinode->name);
                    break;
                case 'xml':
                    $httpReq->put('<'.'?xml version=\"1.0\"?'.">\r\n");
                    $apinode->printXML($httpReq, $apinode->name);
                    break;
            }
        } else if(!$httpReq->isShortReq()) {
            // Display value
            switch($disptype) {
                case 'json':
                case 'jzon':
                    $apinode->printJSONValue($httpReq, $subkey);
                    break;
                case 'txt':
                    $apinode->printTXTValue($httpReq, $subkey);
                    break;
                case '':
                case 'html':
                    $apinode->printHTMLValue($httpReq, $subkey);
                    break;
                case 'xml':
                    $apinode->printXMLValue($httpReq, $subkey);
                    break;
            }
        }
    }

    /**
     * Provide connection information in JSON format
     */
    public function serveInstaller(VHubServerHTTPRequest $httpReq): void
    {
        $res = [];

        // Test command, to avoid timeouts
        $testTimeout = $httpReq->getArg('testTimeout');
        if(!is_null($testTimeout)) {
            try {
                $fp = fsockopen('www.yoctopuce.com', 80, $errorCode, $errorMsg, floatVal($testTimeout));
                if($fp === FALSE) {
                    $res['error'] = "{$errorMsg} (error {$errorCode})";
                } else {
                    $res['success'] = 1;
                    fclose($fp);
                }
            } catch(Throwable $ex) {
                $res['error'] = $ex->getMessage();
            }
            $this->files->sendContentHeader($httpReq, 'json');
            $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));
            return;
        }

        // Real command to prepare to run the installer
        $version = $httpReq->getArg('forVersion');
        $getVersionStr = @file_get_contents(GET_LAST_VERSION_URL);
        if(!$version) {
            // forVersion flag is enforce requirements for admin rights
            $res['error'] = 'version specifier is MANDATORY';
        } else if(!$getVersionStr) {
            $res['error'] = 'unable to retrieve version information from www.yoctopuce.com';
        } else if(!class_exists('ZipArchive')) {
            $res['error'] = 'PHP zip extension is not enabled';
        } else {
            $getVersion = json_decode($getVersionStr);
            if(is_null($getVersion)) {
                $res['error'] = 'unable to retrieve version information from www.yoctopuce.com';
            } else if($version == 'latest') {
                $url = $getVersion->link;
            } else {
                $url = str_replace('.'.$getVersion->version.'.', '.'.urlencode($version).'.', $getVersion->link);
            }
            $res['installerURL'] = $url;
            $installer = @file_get_contents($url);
            if(!$installer) {
                $res['error'] = "unable to retrieve installer from www.yoctopuce.com ({$url})";
            } else {
                $baseDir = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
                $tempFile = tempnam($baseDir, 'vhw');
                $zip = new ZipArchive;
                if (!@file_put_contents($tempFile, $installer)) {
                    $res['error'] = 'unable to write ZIP file';
                } else if($zip->open($tempFile) !== TRUE) {
                    $res['error'] = 'unable to open ZIP file';
                    @unlink($tempFile);
                } else {
                    $installer = $zip->getFromName('vhub4web-installer.php');
                    $zip->close();
                    @unlink($tempFile);
                    if(!$installer) {
                        $res['error'] = 'unable to read from ZIP file';
                    } else {
                        $installerName = 'vhub4web-installer.'.bin2hex(random_bytes(6)).'.php';
                        $installerFile = $baseDir.'/'.$installerName;
                        if(!@file_put_contents($installerFile, $installer)) {
                            $res['error'] = 'unable to write installer file';
                        } else {
                            $baseUrl = dirname(dirname(parse_url($httpReq->getRequestURL(), PHP_URL_PATH)));
                            $res['location'] = $baseUrl.'/'.$installerName;
                        }
                    }
                }
            }
        }
        $this->files->sendContentHeader($httpReq, 'json');
        $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Provide connection information in JSON format
     */
    public function serveInfo(VHubServerHTTPRequest $httpReq): void
    {
        $uri = preg_replace('~(/info.json|\?).*~', '', $httpReq->getRequestURL());
        if(str_starts_with($uri, '/')) {
            $uri = substr($uri, 1);
        }
        $protocol = $httpReq->getProtocol();
        $userPwd = $this->apiroot->api->network->getattr('userPassword');
        $adminPwd = $this->apiroot->api->network->getattr('adminPassword');
        $info = [
            "productName" => $this->apiroot->api->module->getattr('productName'),
            "serialNumber" => $this->apiroot->api->module->getattr('serialNumber'),
            "firmwareRelease" => $this->apiroot->api->module->getattr('firmwareRelease'),
            "dir" => "$uri",
            "userPassword" => ($userPwd == '' ? "FALSE" : "TRUE"),
            "adminPassword" => ($adminPwd == '' ? "FALSE" : "TRUE"),
            "port" => [ $protocol.':'.$this->apiroot->api->network->getattr('httpPort') ],
            "protocol" => "HTTP/1.1",
            "realm" =>  $this->apiroot->cloudConf->authRealm,
            "nonce" => $httpReq->newNonce()
        ];
        $this->files->sendContentHeader($httpReq, 'json');
        $httpReq->put(json_encode($info, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Provide HTTP callback statistics in JSON format
     */
    public function serveStats(VHubServerHTTPRequest $httpReq): void
    {
        $stats = [];
        foreach($this->apiroot->bySerial->subnodeNames() as $serial) {
            $devnode = $this->apiroot->bySerial->subnode($serial);
            $devstats = $devnode->getDeviceStats();
            if(!is_null($devstats)) {
                $stats[$serial] = $devstats->saveState();
                $stats[$serial]['lastCallbackAge'] = $httpReq->getRequestTimestamp() - $stats[$serial]['prevTimestamp'];
                $stats[$serial]['lastCallbackIP'] = $devnode->cloudConf->parentIP;
                $hubname = '';
                if($devnode->api->hasSubnode('network')) {
                    $netnode = $devnode->api->subnode('network');
                    $hubname = $netnode->getattr('logicalName');
                    $stats[$serial]['callbackMaxDelay'] = $netnode->getattr('callbackMaxDelay');
                }
                $stats[$serial]['hubName'] = ($hubname != '' ? $hubname : $serial);
            }
        }
        $this->files->sendContentHeader($httpReq, 'json');
        $httpReq->put(json_encode($stats, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Change VirtualHub-4web configuration
     */
    public function serveConf(VHubServerHTTPRequest $httpReq): void
    {
        $res = [];
        $deleteDevice = $httpReq->getArg('deleteDevice');
        if(!is_null($deleteDevice)) {
            $serial = $deleteDevice;
            $res['deleteDevice'] = [ 'target' => $serial, 'done' => 0 ];
            $tarpath = VHUB4WEB_DATA.'/'.$deleteDevice.'.tar';
            if(file_exists($tarpath)) {
                unlink($tarpath);
                // Reload the state file while keeping an exclusive lock to update it
                $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, JSON_THROW_ON_ERROR);
                $this->apiroot->loadState($httpReq, $cloudapiobj, false);
                $this->apiroot->cloudConf->freeDevYdx($serial);
                $cloudapiobj = $this->apiroot->saveState();
                $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);
                $res['deleteDevice']['done'] = 1;
            } else {
                $res['deleteDevice']['errmsg'] = 'unknown device '.$serial;
            }
        }
        $setCbMd5Pwd = $httpReq->getArg('callbackMD5Password');
        if(!is_null($setCbMd5Pwd)) {
            if($setCbMd5Pwd == '?') {
                $res['callbackMD5Password'] = [ 'changed' => 0 ];
            } else {
                // Reload the state file while keeping an exclusive lock to update it
                $cloudapiobj = json_decode($this->loadFile($httpReq, STATE_FILE, true, $fp), false, 99, JSON_THROW_ON_ERROR);
                $this->apiroot->loadState($httpReq, $cloudapiobj, false);
                $this->apiroot->cloudConf->md5signPwd = $setCbMd5Pwd;
                $cloudapiobj = $this->apiroot->saveState();
                $this->saveFile($httpReq, STATE_FILE, json_encode($cloudapiobj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $fp);
                $res['callbackMD5Password'] = [ 'changed' => 1 ];
            }
            $isSet = ($this->apiroot->cloudConf->md5signPwd ? 'YES' : 'NO');
            $res['callbackMD5Password']['isSet'] = $isSet;
        }
        $this->files->sendContentHeader($httpReq, 'json');
        $httpReq->put(json_encode($res, JSON_UNESCAPED_SLASHES));
    }

    /**
     * send the device-specific logs, from a given position
     */
    public function serveLogs(VHubServerHTTPRequest $httpReq, string $serial, int $pos): void
    {
        $logs = '';
        if($serial == $this->apiroot->cloudConf->serialNumber) {
            $fp = fopen(VHUB4WEB_DATA.'/VHUB4WEB-logs.txt', 'rb');
            if($fp) {
                $logs = stream_get_contents($fp);
                fclose($fp);
            }
        } else {
            $devlogs = $this->files->loadDeviceFile($httpReq, $serial, 'logs.txt');
            if(!is_null($devlogs)) {
                $logs = preg_replace('~ *$~', '', $devlogs);
            }
        }
        // when the logs have wrapped, the first line indicates the start offset
        $startPos = 0;
        if(preg_match('~^@([0-9]+)\n~', $logs, $matches)) {
            $startPos = intVal($matches[1]);
            $logs = substr($logs, strlen($matches[0]));
        }
        $endPos = $startPos + strlen($logs);
        $this->files->sendContentHeader($httpReq, 'txt');
        if($pos <= $startPos) {
            $httpReq->put($logs);
        } else {
            $httpReq->put(substr($logs, $pos - $startPos));
        }
        $httpReq->put("\n@$endPos");
    }

    /**
     * Process a client query for the notification channel (YoctoHub emulation)
     */
    public function serveNotifications(VHubServerHTTPRequest $httpReq): void
    {
        // default to unspecified position
        if(!is_null($httpReq->getArg('abs'))) {
            $position = intVal($httpReq->getArg('abs'));
            $veryFirstCall = false;
        } else {
            $position = -1;
            $veryFirstCall = true;
        }
        $this->notif = NotifStream::StreamAt($httpReq, $this, $position);
        // For PHP must stay in "short notification" as it is the
        // only reliable way to force Apache to flush ASAP
        $position = $this->notif->openForRead($httpReq, 1);
        $banner = "YN01@{$position}\n\n";
        $httpReq->putHeader('Content-Type: text/plain; charset=x-user-defined');
        $maxlength = $this->notif->predictSize();
        $httpReq->putHeader('Content-length: '.(strlen($banner)+$maxlength));
        $httpReq->put($banner);
        $started = microtime(true);
        while($maxlength != 0) {
            $newNotif = $this->notif->readMore($httpReq, $maxlength);
            if(strlen($newNotif) > 0) {
                $httpReq->put($newNotif);
                $maxlength -= strlen($newNotif);
                // for PHP, close immediately to force a flush since Apache may be forcing cache
                break;
            }
            // for PHP, flush every at every KEEPALIVE interval since Apache may be forcing cache
            if(microtime(true) - $started > NOTIF_KEEPALIVE_DELAY) {
                break;
            }
            // delay execution for up to 0,1 [s] before retrying
            time_nanosleep(0, 100000);
            // for PHP, we also flush quickly at the very first call to avoid any delay before
            // connection is diagnosed as working
            if($veryFirstCall) {
                break;
            }
        }
        if($maxlength > 0) {
            $httpReq->put(str_repeat("\n", $maxlength));
        }
        $this->notif->close($httpReq);
    }

    /**
     * Process a client query for datalogger retrieval
     */
    public function serveLogger(VHubServerHTTPRequest $httpReq, string $serial, string $functionid, string $run, string $utc, string $fromUtc, string $toUtc, bool $verbose): void
    {
        $this->files->sendContentHeader($httpReq, 'json');

        // Enumerate device sensors
        $deviceNode = $this->apiroot->bySerial->subnode($serial);
        $deviceApiNode = $deviceNode->api;
        $sensorIds = [];
        $functions = $deviceApiNode->subnodeNames();
        foreach($functions as $funcid) {
            if($deviceApiNode->subnode($funcid)->isSensor()) {
                $sensorIds[] = $funcid;
            }
        }
        if(sizeof($sensorIds) == 0) {
            $httpReq->put('[]');
            return;
        }
        if($functionid != '') {
            if(!in_array($functionid, $sensorIds)) {
                $functionid = '';
            }
        }

        // Retrieve data from the datalogger
        $logger = new DataLogger($this, $serial);
        if($utc == '') {
            // Dump summary
            $fromStamp = ($fromUtc == '' ? 0 : intVal($fromUtc));
            $toStamp = ($toUtc == '' ? 0xffff0000 : intVal($toUtc));
            if($functionid == '') {
                $sep = '[';
                foreach($sensorIds as $funcid) {
                    $httpReq->put($sep);
                    $logger->printIndex($httpReq, $deviceApiNode->subnode($funcid), $funcid, $run, $fromStamp, $toStamp, $verbose);
                    $sep = ',';
                }
                $httpReq->put(']');
            } else {
                $logger->printIndex($httpReq, $deviceApiNode->subnode($functionid), $functionid, $run, $fromStamp, $toStamp, $verbose);
            }
        } else if(str_contains($utc, ',')) {
            // Dump multiple streams in details (bulk transfer)
            $utcStamps = array_map(fn($value): int => intval($value), explode(',', $utc));
            $httpReq->put('[');
            $logger->printRun($httpReq, $functionid, $run, $utcStamps, $verbose);
            $httpReq->put(']');
        } else {
            // Dump a single stream in details
            $utcStamp = intVal($utc);
            $logger->printRun($httpReq, $functionid, $run, [ $utcStamp ], $verbose);
        }
    }

    /**
     * Process a local file upload
     */
    public function handleUpload(VHubServerHTTPRequest $httpReq, string $devserial = ''): void
    {
        $fname = '';
        $content = '';
        $jsonData = $httpReq->getJsonPostData();
        if($jsonData && isset($jsonData['body'])) {
            // JSON-encoded POST data
            $fname = $jsonData['body']['filename'];
            $content = base64_decode($jsonData['body']['b64content']);
        } else {
            $postdata = $httpReq->getRawPostData();
            if (strlen($postdata) > 0) {
                // Form-Encoded POST data
                $fnameMatches = [];
                $boundaryMatches = [];
                if (!preg_match('/Content-Disposition: form-data; name="([^"]*)";/i', $postdata, $fnameMatches)) {
                    die("upload.html: multipart/form-data encoding expected !\n");
                }
                if (!preg_match('/--\S*/', $postdata, $boundaryMatches)) {
                    die("upload.html: multipart boundary not found\n");
                }
                $boundary = $boundaryMatches[0];
                $fname = $fnameMatches[1];
                $startPos = strpos($postdata, "\r\n\r\n", strlen($boundary));
                $endPos = strpos($postdata, "\r\n" . $boundary, $startPos);
                if ($startPos >= 0 && $endPos >= 0) {
                    $startPos += 4;
                    $content = substr($postdata, $startPos, $endPos - $startPos);
                }
            } else {
                // PHP-Specific: Bug in many recent version (7.x), enable_post_data_reading does not work with .user.ini
                // => we need to fallback to tentative processing based on PHP $_FILES variable
                VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Upload detected without proper enable_post_data_reading=0");
                foreach ($_FILES as $fname => $filedef) {
                    // problem: PHP replaces dots by underscores in the filename, we need to revert that
                    $fname = preg_replace('~_(html|txt|xml|js|ts|bin|min|byn|gz|zip)~i', '.$1', $fname);
                    $content = file_get_contents($filedef['tmp_name']);
                }
            }
        }
        if(!$fname) {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Empty upload request");
            return;
        }
        if($devserial == '') {
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Uploading {$fname} to VirtualHub4web files");
            $this->files->filesUpload($httpReq, $fname, $content);
        } else {
            $csize = strlen($content);
            VHubServer::Log($httpReq, LOG_VHUBSERVER, 4, "Scheduling upload of {$fname} to ${$devserial} ({$csize} bytes)");
            $this->files->deviceFilesUpload($httpReq, $devserial, $fname, $content);
        }
        $this->saveState($httpReq);
    }
}
