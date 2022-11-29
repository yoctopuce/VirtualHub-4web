<?php
include_once("runtime-checks.php");

const HTACCESS_REWRITE_RULES_SINGLE =
    "# Redirect all URLs to index.php for VirtualHub-4web processing\r\n" .
    "RewriteEngine on\r\n" .
    "RewriteRule ^.*$ index.php?node=$0 [L,QSA]\r\n";

const HTACCESS_PHP_VALUES =
    "# PHP settings for VirtualHub-4web\r\n" .
    "php_value post_max_size             \"4M\"\r\n" .
    "php_value upload_max_filesize       \"4M\"\r\n" .
    "php_value enable_post_data_reading  0\r\n" .
    "# this one is not supposed to work per-dir, but sometimes it does...\r\n" .
    "php_value allow_url_fopen           1\r\n";

const USER_INI_VALUES =
    "; PHP settings for VirtualHub-4web, when PHP is running as (Fast)CGI\r\n" .
    ";\r\n" .
    "; note: this file is always present, even when running as Apache module,\r\n" .
    ";       because it is harmless and may become useful one day if the server\r\n" .
    ";       configuration ever changes to (Fast)CGI\r\n" .
    "post_max_size=\"4M\"\r\n" .
    "upload_max_filesize=\"4M\"\r\n" .
    "enable_post_data_reading=\"0\"\r\n" .
    "; this one is not supposed to work per-dir, but sometimes it does...\r\n" .
    "allow_url_fopen=\"1\"\r\n";

const HTACCESS_REWRITE_RULES_MULTI =
    "# Redirect all URLs to index.php for VirtualHub-4web processing\r\n" .
    "RewriteEngine on\r\n" .
    "RewriteRule ^([^/]*)/(.*)$ $1/index.php?node=$2 [QSA,END]\r\n";

const HTACCESS_TEST_INDEX_PHP = '<?php 
Print(json_encode([ "props" => [
    "node" => $_GET["node"],
    "allowUrlFopen" => ini_get("allow_url_fopen"),
    "enablePostDataReading" => ini_get("enable_post_data_reading"),
    "postMaxSize" => ini_get("post_max_size"),
    "uploadMaxFilesize" => ini_get("upload_max_filesize")
], "errors"=>[] ]));
';

const BASIC_INSTALL_INDEX_PHP = "<?php
// Identify location of CloudHub code, and data specific to this instance
const VHUB4WEB_ROOT = __DIR__.'/..';
const VHUB4WEB_CODE = VHUB4WEB_ROOT;
const VHUB4WEB_DATA = __DIR__;

// Startup VirtualHub-4web
inc"."lude(VHUB4WEB_CODE.'/vhub4web-init.php');
";

const ADVANCED_INSTALL_INDEX_PHP = "<?php
// Identify location of CloudHub code, and data specific to this instance
const VHUB4WEB_ROOT = '_%_ROOT_%_';
const VHUB4WEB_CODE = VHUB4WEB_ROOT.'/dist/" . VERSION . "';
const VHUB4WEB_DATA = VHUB4WEB_ROOT.'/data/_%_INSTANCE_%_';

// Startup VirtualHub-4web
inc"."lude(VHUB4WEB_CODE.'/vhub4web-init.php');
";

const OR_INSTALL_MANUALLY = '<br>If this is not possible, follow the instructions to perform the install manually.';

function normalizeLineEndings(string $content, string $requiredEnding = PHP_EOL): string
{
    return preg_replace('~\R~u', $requiredEnding, $content);
}

/*
 * Trivial PHP 8.x to 7.x converter: mainly remove attribute typing, plus small details
 */
function downgradePHP(string $code): string
{
    $code = preg_replace('/JSON_THROW_ON_ERROR/i', '0', $code);
    $code = preg_replace('/\(Throwable\)/i', '(Throwable $e)', $code);
    $code = preg_replace('/: *void/', '', $code);
    $code = preg_replace('/string\|int\s+/', '', $code);
    $code = preg_replace('/(public\s+|protected\s+|private\s+)(static\s+|)\??(array|bool|float|int|string|object|self|parent|interable|mixed|[A-Z]\w+)\s+(\$\w+\s*(=[^;]+|);)/', '$1$2$4', $code);
    return $code;
}

/*
 * Install function: read and decompress runtime files for VirtualHub-4web
 */
function installFiles(string $destDir, array $prevConfig = []): bool
{
    global $phpCode, $initCode, $yfsImage;
    $php8 = '';
    $zp = gzopen($phpCode, 'r');
    while (!gzeof($zp)) {
        $php8 .= gzread($zp, 16384);
    }
    gzclose($zp);
    $newInitCode = file_get_contents($initCode);
    if(isset($prevConfig['definedSymbols']) && isset($prevConfig['codedir'])) {
        $prevInitPath = $prevConfig['codedir'].'/vhub4web-init.php';
        if(file_exists($prevInitPath)) {
            $prevInitCode = file_get_contents($prevInitPath);
            $markerPos = strpos($prevInitCode, '////-- MARKER: New constants');
            if($markerPos !== false &&
                preg_match_all('/const\s+(\w+)\s*=\s*([^\r\n]+)/', $newInitCode, $defines, PREG_SET_ORDER)) {
                $addConfig = '';
                foreach($defines as $def) {
                    if(!in_array($def[1], $prevConfig['definedSymbols'])) {
                        $addConfig .= "const $def[1] = $def[2]\n";
                    }
                }
                $newInitCode = substr($prevInitCode, 0, $markerPos).$addConfig.substr($prevInitCode, $markerPos);
            }
        }
    }
    $res = file_put_contents("{$destDir}/vhub4web-init.php", normalizeLineEndings($newInitCode));
    $res = $res && file_put_contents("{$destDir}/vhub4web-php8.php", normalizeLineEndings($php8));
    $res = $res && file_put_contents("{$destDir}/vhub4web-php7.php", normalizeLineEndings(downgradePHP($php8)));
    $res = $res && file_put_contents("{$destDir}/YFSImg.yfs", file_get_contents($yfsImage));
    return $res;
}

/*
 * Trivial PHP parser to process customized system-wide configuration options
 */
function commonPhpDefinitions($workDir): array
{
    return [
        '__DIR__' => $workDir,
        'PHP_VERSION' => PHP_VERSION,
        'PHP_MAJOR_VERSION' => PHP_MAJOR_VERSION,
        'PHP_MINOR_VERSION' => PHP_MINOR_VERSION
    ];
}

function parsePhpFile(string $filepath, array &$definitions, int $maxline = 999999): bool
{
    $fp = @fopen($filepath, "rb");
    if (!$fp) {
        return false;
    }
    $nlines = 0;
    while ($nlines < $maxline && ($line = stream_get_line($fp, 0, "\n")) !== false) {
        $line = preg_replace('/dirname\s*\(\s*__FILE__\s*\)/', '__DIR__', trim($line));
        if ((preg_match('/const\s*(\w+)\s*=\s*([^;]*);/', $line, $matches) ||
                preg_match('/define\s*\(\s*[\'"](\w+)[\'"]\s*,\s*([^)]*)\)/i', $line, $matches) ||
                preg_match('/(include|include_once)\s*\(\s*([^)]*)\)/i', $line, $matches)) &&
            preg_match_all('/[\'"][^\'"]*[\'"]|\w+/', trim($matches[2]), $defParts)) {
            $newDef = '';
            foreach ($defParts[0] as $defPart) {
                if (preg_match('/^"/', $defPart)) {
                    $newDef .= json_decode($defPart);
                } else if (preg_match('/^[\'"]/', $defPart)) {
                    $newDef .= substr($defPart, 1, -1);
                } else if (isset($definitions[$defPart])) {
                    $newDef .= $definitions[$defPart];
                } else if (preg_match('~^[0-9]~', $defPart)) {
                    $newDef = intval($defPart, 0);
                } else {
                    $newDef = $defPart;
                }
            }
            if (is_string($newDef) && preg_match('~^[/\\\]~', $newDef)) {
                $newDef = realpath($newDef);
            }
            $definitions[$matches[1]] = $newDef;
        }
        $nlines++;
    }
    fclose($fp);
    return true;
}

/*
 * Attempt to detect the version currently installed in a given code directory
 */
function getVhub4webConfig(string $entryPoint): array
{
    // Parse instance entry point
    $dirPath = dirname($entryPoint);
    $constants = commonPhpDefinitions($dirPath);
    if(!parsePhpFile($entryPoint, $constants)) {
        return [ 'version' => 'unknown', 'errmsg' => 'unable to parse index.php' ];
    }
    if(!isset($constants['VHUB4WEB_CODE'])) {
        return [ 'version' => 'unknown', 'errmsg' => 'no definition found for VHUB4WEB_CODE in index.php' ];
    }
    if(!isset($constants['VHUB4WEB_DATA'])) {
        return [ 'version' => 'unknown', 'errmsg' => 'no definition found for VHUB4WEB_DATA in index.php' ];
    }
    $codedir = preg_replace('~[/\\\\]$~', '', $constants['VHUB4WEB_CODE']);
    $datadir = preg_replace('~[/\\\\]$~', '', $constants['VHUB4WEB_DATA']);
    if(!is_dir($codedir)) {
        return [ 'version' => 'unknown', 'errmsg' => 'invalid VHUB4WEB_CODE in index.php' ];
    }
    if(!is_dir($datadir)) {
        return [ 'version' => 'unknown', 'errmsg' => 'invalid VHUB4WEB_DATA in index.php' ];
    }
    $res = [ 'codedir' => $codedir, 'datadir' => $datadir, 'version' => 'unknown' ];

    // Parse code directory
    $constants = commonPhpDefinitions($codedir);
    if(!parsePhpFile($codedir."/vhub4web-init.php", $constants, 200)) {
        $res['errmsg'] = 'Cannot read vhub4web-init.php';
        return $res;
    }
    $include = false;
    if(isset($constants['include'])) {
        $include = $constants['include'];
        unset($constants['include']);
    }
    if(isset($constants['include_once'])) {
        $include = $constants['include_once'];
        unset($constants['include_once']);
    }
    $res['constants'] = $constants;
    $res['definedSymbols'] = array_keys($constants);
    if(isset($constants['VERSION'])) {
        $res['version'] = $constants['VERSION'];
    } else if($include) {
        if(!parsePhpFile($include, $constants, 5)) {
            $res['errmsg'] = 'Cannot open '.$include;
            return $res;
        } else if(isset($constants['VERSION'])) {
            $res['version'] = $constants['VERSION'];
        }
    }
    return $res;
}

function uninstallInstance(string $instance, string $entryPoint, bool $removeData, array &$properties, array &$errors): bool
{
    if(!isset($properties['removedFiles'])) {
        $properties['removedFiles'] = [];
    }
    if(!isset($properties['removedDirs'])) {
        $properties['removedDirs'] = [];
    }
    if(!isset($properties['linkedCodeDirs'])) {
        $properties['linkedCodeDirs'] = [];
    }
    if(!file_exists($entryPoint)) {
        $errors[] = [
            'error' => 'noSuchInstance',
            'msg' => 'Invalid instance name specified: '.$instance,
            'cause' => 'The instance to be removed appears to have already been deleted.<br>'.
                'Try to reload the installer to perform a new detection.'
        ];
        return false;
    }
    $dirPath = dirname($entryPoint);
    $instanceData = getVhub4webConfig($entryPoint);
    if(isset($instanceData['errmsg'])) {
        $errors[] = [
            'error' => 'badInstance',
            'msg' => 'Cannot retrieve instance configuration for ['.$instance.']: '.$instanceData['errmsg'],
            'cause' => 'The installer cannot recognize the setup of this instance.<br>'.
                'You may have to uninstall it manually.'
        ];
        return false;
    }
    // Remove entry point first, preventing any further access to the instance
    if(!@unlink($entryPoint)) {
        $errors[] = [
            'error' => 'unlinkFailed',
            'msg' => 'Cannot delete entry point for ['.$instance.']',
            'cause' => 'The unlink command has failed. You will have to delete this instance manually.'
        ];
        return false;
    }
    $properties['removedFiles'][] = $entryPoint;
    $properties['linkedCodeDirs'][$instanceData['codedir']] = $instanceData['version'];

    // Remove Yocto API cache_dir if possible, as it is not worth preserving anyway
    $cacheDir = $instanceData['datadir'].'/cache_dir';
    if(is_dir($cacheDir)) {
        foreach(scandir($cacheDir) as $entry) {
            if(!preg_match('~^[A-Z0-9]{8}_.*\.json$~', $entry)) continue;
            if(@unlink($cacheDir.'/'.$entry)) {
                $properties['removedFiles'][] = $cacheDir.'/'.$entry;
            }
        }
        if(@rmdir($cacheDir)) {
            $properties['removedDirs'][] = $cacheDir;
        }
    }

    // Remove related data, if requested to
    if($removeData) {
        $datadir = $instanceData['datadir'];
        if(is_dir($datadir)) {
            foreach(scandir($datadir) as $entry) {
                if(!preg_match('~^((VHUB4WEB.*)|([A-Z0-9]{8}-.*\.(tar|req|trace)))$~', $entry)) continue;
                if(@unlink($datadir.'/'.$entry)) {
                    $properties['removedFiles'][] = $datadir.'/'.$entry;
                }
            }
            if(@rmdir($datadir)) {
                $properties['removedDirs'][] = $datadir;
            }
        }
    }

    // Attempt to remove the instance directory if it is not the data directory itself
    if($dirPath != $instanceData['datadir']) {
        if(@rmdir($dirPath)) {
            $properties['removedDirs'][] = $dirPath;
        }
    }

    return true;
}

function uninstallCode(string $codeDir, string $version, array &$properties, array &$errors): bool
{
    $isEmpty = true;
    $failed = false;
    foreach(scandir($codeDir) as $entry) {
        if($entry[0] == '.') continue;
        if(preg_match('~^(YFSImg\.yfs|vhub4web-(init|php[0-9]+)\.php)$~', $entry)) {
            if(@unlink($codeDir.'/'.$entry)) {
                $properties['removedFiles'][] = $codeDir.'/'.$entry;
            } else {
                $errors[] = [
                    'error' => 'unlinkFailed',
                    'msg' => 'Failed to remove '.$entry.' when removing version '.$version,
                    'cause' => 'The unlink command has failed. You will have to delete this instance manually.'
                ];
                $isEmpty = false;
                $failed = true;
            }
        } else {
            $isEmpty = false;
        }
    }
    if($isEmpty && @rmdir($codeDir)) {
        $properties['removedDirs'][] = $codeDir;
    }
    return !$failed;
}

/*
 * Installer state machine
 */
function processInstall(string $func): array
{
    $properties = [];
    $errors = [];
    $accessURL = dirname($_SERVER['SCRIPT_NAME']);
    $scanURL = $accessURL;
    $serverRoot = __DIR__;
    while(basename($scanURL) && basename($scanURL) == basename($serverRoot) && $scanURL != dirname($scanURL)) {
        $scanURL = dirname($scanURL);
        $serverRoot = dirname($serverRoot);
    }
    $wwwdir = basename($serverRoot);
    $advInstallPath = substr($serverRoot, 0, -strlen($wwwdir)).'vhub4web';
    $testdirName = 'installer-testdir';
    $testdir = __DIR__.'/'.$testdirName;
    switch($func) {
        case 'testURL': // basic URL test, to make sure the installer is running as expected
            $properties['accessURL'] = $accessURL;
            $properties['systemPath'] = __DIR__;
            $properties['serverRoot'] = $serverRoot;
            $properties['advancedInstallPath'] = $advInstallPath;
            break;
        case 'testPHP': // test important PHP serrtings
            $bitSize = round(log(PHP_INT_MAX) / log(2) / 8) * 8;
            $properties['phpVersion'] = 'PHP '.phpversion().' '.php_sapi_name()." (with {$bitSize} bit integers)";
            $properties['phpIntBits'] = $bitSize;
            $properties['allowUrlFopen'] = ini_get('allow_url_fopen');
            $properties['enablePostDataReading'] = ini_get('enable_post_data_reading');
            $max_post = ini_get('post_max_size');
            $max_post_kb = str_replace(['K', 'M', 'G'], ['', '000', '000000'], $max_post);
            $properties['postMaxSize'] = $max_post_kb.' KB';
            $max_upload = ini_get('upload_max_filesize');
            $max_upload_kb = str_replace(['K', 'M', 'G'], ['', '000', '000000'], $max_upload);
            $properties['uploadMaxFilesize'] = $max_upload_kb.' KB';
            break;
        case 'testRW': // test read-write access in web-facing install dir
            $testfile = __DIR__.'/installer-testfile.txt';
            $res = @file_put_contents($testfile, 'test!');
            if($res === FALSE) {
                $properties['writeAccess'] = '<strong>forbidden</strong>';
                $errors[] = [
                    'error' => 'writeAccess',
                    'msg' => 'The installer requires <b>write access</b> to proceed to installation.',
                    'cause' => 'Change the access rights of the installation directory to allow PHP to write to it.'.OR_INSTALL_MANUALLY
                ];
            } else {
                @unlink($testfile);
                $properties['writeAccess'] = 'allowed';
            }
            break;
        case 'testExisting': // detect any existing .htaccess files and look for existing Virtualhub-4web instances
            $directories = [];
            $properties['htAccessFound'] = 'no';
            if(file_exists(__DIR__.'/.htaccess')) {
                $properties['htAccessFound'] = 'yes';
                foreach(scandir(__DIR__) as $entry) {
                    if($entry[0] == '.') continue;
                    if($entry == $testdirName) continue;
                    if(!is_dir(__DIR__.'/'.$entry)) continue;
                    $directories[] = $entry;
                }
            }
            $properties['instances'] = [];
            foreach($directories as $dir) {
                $dirPath = __DIR__.'/'.$dir;
                $entryPoint = $dirPath.'/index.php';
                if(file_exists($entryPoint)) {
                    $properties['instances'][$dir] = getVhub4webConfig($entryPoint);
                }
            }
            $installed = "no";
            $installedVersion = "none";
            $nInstance = sizeof($properties['instances']);
            if($nInstance > 0) {
                $instanceNames = array_keys($properties['instances']);
                $installedVersion = $properties['instances'][$instanceNames[0]]['version'];
                if($nInstance == 1) {
                    $installed = '<b>yes, one instance</b>: '.$instanceNames[0];
                } else {
                    $installed = "<b>yes, {$nInstance} instances</b>: {$instanceNames[0]}";
                    for($i = 1; $i < $nInstance && $installedVersion != 'unknown' && $installedVersion != 'various'; $i++) {
                        $instance = $properties['instances'][$instanceNames[$i]];
                        if($installedVersion != $instance['version']) {
                            $installedVersion = 'various';
                        }
                    }
                    for($i = 1; $i < $nInstance; $i++) {
                        $installedMore = $installed . ", {$instanceNames[$i]}";
                        if(strlen($installedMore) > 80) {
                            $installed .= '...';
                            break;
                        }
                        $installed = $installedMore;
                    }
                }
            }
            $properties['alreadyInstalled'] = $installed;
            $properties['installedVersion'] = $installedVersion;
            break;
        case 'createTestDir': // create a subdirectory to test .htaccess without risk
            $properties['dirname'] = $testdirName;
            try {
                if(!is_dir($testdir)) {
                    if(!@mkdir($testdir, 0755)) {
                        $errors[] = [
                            'error' => 'createTestDir',
                            'msg' => 'The installer failed to create a test directory to proceed to installation.',
                            'cause' => 'Change the access rights of the installation directory to fix this.'.OR_INSTALL_MANUALLY
                        ];
                        break;
                    }
                    $properties['testDir'] = 'created';
                } else if(file_exists($testdir.'/.user.ini')) {
                    @unlink($testdir.'/.user.ini');
                }
                file_put_contents($testdir.'/index.php', normalizeLineEndings(HTACCESS_TEST_INDEX_PHP));
                $properties['testIndex'] = 'created';
                file_put_contents($testdir.'/.user.ini', normalizeLineEndings(USER_INI_VALUES));
                $properties['testUserIni'] = 'created';
                file_put_contents($testdir.'/.htaccess', normalizeLineEndings(HTACCESS_REWRITE_RULES_SINGLE));
                $properties['testHTAccess'] = 'created';
            } catch(Throwable $e) {
                $errors[] = [
                    'error' => 'createTestDir',
                    'msg' => 'The installer failed to create a test directory to proceed to installation.',
                    'cause' => 'The error was: '.$e->getMessage().'<br>'.
                        'Change the access rights of the installation directory to fix this.'.OR_INSTALL_MANUALLY
                ];
            }
            break;
        case 'createPhpValue': // add php_value to testdir .htaccess
            $properties['dirname'] = $testdirName;
            file_put_contents($testdir.'/.htaccess', normalizeLineEndings(HTACCESS_PHP_VALUES.HTACCESS_REWRITE_RULES_SINGLE));
            $properties['testPHPValue'] = 'created';
            break;
        case 'setupWithPhpValue':    // setup final configuration
        case 'setupWithoutPhpValue':
            // setup php config files in the common install directory
            if(file_exists(__DIR__.'/.user.ini')) {
                $newUserIni = file_get_contents(__DIR__.'/.user.ini');
                if(!preg_match('/VirtualHub-4web/', $newUserIni)) {
                    $newUserIni .= "\r\n".USER_INI_VALUES;
                }
            } else {
                $newUserIni = USER_INI_VALUES;
            }
            if(file_exists(__DIR__.'/.htaccess')) {
                $newHtAccess = file_get_contents(__DIR__.'/.htaccess');
                if(!preg_match('/VirtualHub-4web/', $newHtAccess)) {
                    if ($func == 'setupWithPhpValue') {
                        $newHtAccess .= "\r\n" . HTACCESS_PHP_VALUES.HTACCESS_REWRITE_RULES_MULTI;
                    } else {
                        $newHtAccess .= "\r\n" . HTACCESS_REWRITE_RULES_MULTI;
                    }
                }
            } else {
                if ($func == 'setupWithPhpValue') {
                    $newHtAccess = HTACCESS_PHP_VALUES.HTACCESS_REWRITE_RULES_MULTI;
                } else {
                    $newHtAccess = HTACCESS_REWRITE_RULES_MULTI;
                }
            }
            file_put_contents(__DIR__.'/.user.ini', normalizeLineEndings($newUserIni));
            $properties['commonUserIni'] = 'created';
            file_put_contents(__DIR__.'/.htaccess', normalizeLineEndings($newHtAccess));
            $properties['commonHTAccess'] = 'created';
            // remove the test configuration files in testdir to use the common files only
            foreach([ '.user.ini', '.htaccess' ] as $fname) {
                if (file_exists("{$testdir}/${fname}")) {
                    @unlink("{$testdir}/${fname}");
                }
            }
            $properties['testUserIni'] = 'removed';
            $properties['testHTAccess'] = 'removed';
            break;
        case 'removeTestDir': // cleanup test subdirectory
            if(is_dir($testdir)) {
                foreach([ '.user.ini', '.htaccess', 'index.php' ] as $fname) {
                    if (file_exists("{$testdir}/${fname}")) {
                        @unlink("{$testdir}/${fname}");
                    }
                }
                @rmdir($testdir);
                $properties['testDir'] = 'removed';
            }
            break;
        case 'install':
            $basicInstall = (isset($_GET['installType']) && $_GET['installType'] == 'basic');
            $instances = (isset($_GET['instances']) ? json_decode($_GET['instances']) : []);
            if(!$instances || !preg_match('~^[\w-]+$~', implode('', $instances))) {
                $errors[] = [
                    'error' => 'noInstance',
                    'msg' => 'Invalid instance names specified',
                    'cause' => 'The installer cannot recognize specified instance names<br>'.
                        'Make sure to specify at least one instance, and use simple latin characters.'
                ];
                break;
            }
            if($basicInstall) {
                $codeDir = __DIR__;
                $dataDir = __DIR__;
                $indexPhp = BASIC_INSTALL_INDEX_PHP;
            } else {
                $codeDir = $advInstallPath.'/dist/'.VERSION;
                $dataDir = $advInstallPath.'/data';
                $indexPhp = str_replace('_%_ROOT_%_', $advInstallPath, ADVANCED_INSTALL_INDEX_PHP);
                if(!is_dir($advInstallPath)) {
                    if (!@mkdir($advInstallPath, 0755)) {
                        $errors[] = [
                            'error' => 'createInstallDir',
                            'msg' => 'The installer failed to create a directory outside of the HTTP Server document tree.',
                            'cause' => "You can try to create the directory <b>$advInstallPath</b> manually on the server, " .
                                'and set proper access rights on it. If this is not possible, try a basic install.'
                        ];
                        break;
                    }
                }
                if(!is_dir($codeDir)) {
                    if (!@mkdir($codeDir, 0755, true)) {
                        $errors[] = [
                            'error' => 'createCodeDir',
                            'msg' => 'The installer failed to create VirtualHub-4web code directory.',
                            'cause' => "You should try to fix access rights on directory <b>$advInstallPath</b>. " .
                                'If this is not possible, try a basic install.'
                        ];
                        break;
                    }
                }
                if(!is_dir($dataDir)) {
                    if (!@mkdir($dataDir, 0755)) {
                        $errors[] = [
                            'error' => 'createDataDir',
                            'msg' => 'The installer failed to create VirtualHub-4web data directory.',
                            'cause' => "You should try to fix access rights on directory <b>$advInstallPath</b>. " .
                                'If this is not possible, try a basic install.'
                        ];
                        break;
                    }
                }
            }
            installFiles($codeDir);
            $instanceURLs = [];
            foreach($instances as $instance) {
                if(!$basicInstall && !is_dir($dataDir . '/' . $instance)) {
                    // create a separate data directory outside of document root
                    @mkdir($dataDir . '/' . $instance, 0755);
                }
                $instDir = __DIR__.'/'.$instance;
                if(!is_dir($instDir)) {
                    @mkdir($instDir, 0755);
                }
                $fpath = $instDir.'/index.php';
                file_put_contents($fpath, normalizeLineEndings(str_replace('_%_INSTANCE_%_', $instance, $indexPhp)));
                $instanceURLs[] = $accessURL.'/'.$instance;
            }
            $properties['urls'] = $instanceURLs;
            break;
        case 'fullUninstall':
            $removeData = (isset($_GET['removeData']) && $_GET['removeData'] == '1');
            $instances = (isset($_GET['instances']) ? json_decode($_GET['instances']) : []);
            if(!$instances || sizeof($instances) == 0) {
                $errors[] = [
                    'error' => 'noInstance',
                    'msg' => 'Invalid instance names specified',
                    'cause' => 'The installer cannot recognize specified instance names<br>'.
                        'Make sure to specify at least one instance, and use simple latin characters.'
                ];
                break;
            }
            $uninstalled = [];
            foreach($instances as $instance) {
                $entryPoint = __DIR__.'/'.$instance.'/index.php';
                if(uninstallInstance($instance, $entryPoint, $removeData, $properties, $errors)) {
                    $uninstalled[] = $instance;
                }
            }
            $properties['uninstalledInstances'] = $uninstalled;
            $uninstalled = [];
            foreach($properties['linkedCodeDirs'] as $codedir => $version) {
                if(uninstallCode($codedir, $version, $properties, $errors)) {
                    $uninstalled[] = $version;
                }
            }
            $properties['uninstalledVersions'] = $uninstalled;
            break;
        case 'updateInstances':
            $instances = (isset($_GET['instances']) ? json_decode($_GET['instances']) : []);
            if(!$instances || !preg_match('~^[\w-]+$~', implode('', $instances))) {
                $errors[] = [
                    'error' => 'noInstance',
                    'msg' => 'Invalid instance names specified',
                    'cause' => 'The installer cannot recognize specified instance names<br>'.
                        'Make sure to specify at least one instance, and use simple latin characters.'
                ];
                break;
            }
            $updatedCodeDirs = [];
            $updatedInstances = [];
            foreach($instances as $instance) {
                $updatedInstances[$instance] = false;
                $entryPoint = __DIR__.'/'.$instance.'/index.php';
                $instanceData = getVhub4webConfig($entryPoint);
                if(isset($instanceData['errmsg'])) {
                    $errors[] = [
                        'error' => 'badInstance',
                        'msg' => 'Cannot retrieve instance configuration for ['.$instance.']: '.$instanceData['errmsg'],
                        'cause' => 'The installer cannot recognize the setup of this instance.<br>'.
                            'You may have to upgrade it manually.'
                    ];
                } else {
                    $currVersion = $instanceData['version'];
                    $currCodeDir = $instanceData['codedir'];
                    if(basename($currCodeDir) == $currVersion && $currVersion != VERSION) {
                        // must install in a new version-specific code directory
                        $codeDir = dirname($currCodeDir).'/'.VERSION;
                        if(!isset($updatedCodeDirs[$codeDir])) {
                            if(!is_dir($codeDir)) {
                                if (!@mkdir($codeDir, 0755, true)) {
                                    $errors[] = [
                                        'error' => 'createCodeDir',
                                        'msg' => 'The installer failed to create a new VirtualHub-4web code directory.',
                                        'cause' => "You should try to fix access rights on directory <b>".dirname($currCodeDir)."</b>."
                                    ];
                                    $updatedCodeDirs[$codeDir] = false;
                                    continue;
                                }
                            }
                            if(installFiles($codeDir, $instanceData)) {
                                $updatedCodeDirs[$codeDir] = true;
                            }
                        }
                        if(!isset($updatedCodeDirs[$codeDir])) {
                            continue;
                        }
                        // now patch the index file to point to new version
                        $matchExpr = '~([^0-9])'.preg_quote($currVersion).'([^0-9])~';
                        $currIndex = file_get_contents($entryPoint);
                        $newIndex = preg_replace($matchExpr, '${1}'.VERSION.'${2}', $currIndex);
                        file_put_contents($entryPoint, normalizeLineEndings(downgradePHP($newIndex)));
                        $updatedInstances[$instance] = true;
                    } else {
                        // simply replace files of existing install
                        $codeDir = $currCodeDir;
                        installFiles($codeDir, $instanceData);
                    }
                    $updatedCodeDirs[$codeDir] = true;
                }
            }
            $properties['updatedCodeDirs'] = $updatedCodeDirs;
            $properties['updatedInstances'] = $updatedInstances;
            break;
        case 'removeInstaller':
            if(!@unlink(__FILE__)) {
                $errors[] = [
                    'error' => 'cannotRemoveInstaller',
                    'msg' => 'Unable to remove the installer automatically !',
                    'cause' => "Your only option is to remove this file using the same tool that you " .
                        'used to put it on the server. Make sure to do it now.'
                ];
            } else {
                $properties['removeInstaller'] = 'done';
            }
            break;
        case 'testPHPConf':
            check_php_conf(false);
            break;
        default:
            $errors[] = [
                'error' => 'unknownCommand',
                'msg' => 'Internal error in the installer: unknown command',
                'cause' => 'The unknown command was: '.$func.'<br>'.
                    'This is so odd that you will need to contact Yoctopuce support...'
            ];
    }
    return [
        'props' => $properties,
        'errors' => $errors
    ];
}

if(isset($_GET['func'])) {
    $obj = processInstall($_GET['func']);
    die(json_encode($obj));
}

/*
 * Installer entry point starts below
 */
?>
<script>
    function wdg(id)
    {
        return document.getElementById(id);
    }

    function shtml(id,content)
    {
        wdg(id).innerHTML = content;
    }

    function show(id)
    {
        let elem = wdg(id);
        if(elem) {
            elem.style.display = 'block';
        } else {
            console.log('Cannot show div ['+id+'] (unknown)');
        }
    }

    function hide(id)
    {
        let elem = wdg(id);
        if(elem) {
            elem.style.display = 'none';
        } else {
            console.log('Cannot hide div ['+id+'] (unknown)');
        }
    }

    function showError(ident,msg,details)
    {
        wdg('errors').style.display = 'block';
        wdg('errorList').innerHTML +=
            `<li>${msg} <a href='javascript:show(\"${ident}Details\")'>tell me more</a><div class='more' id='${ident}Details'>${details}</div></li>`;
    }

    function setErrorDetails(ident,details)
    {
        shtml(ident+'Details', details);
    }

    function hideClass(classname)
    {
        let elements = document.getElementsByClassName(classname);
        for (let el of elements) {
            el.style.display = 'none';
        }
    }

    function showClass(classname)
    {
        let elements = document.getElementsByClassName(classname);
        for (let el of elements) {
            el.style.display = 'block';
        }
    }

    function wizNext(fromPage, toPage, nextFunc)
    {
        let fromPageEl = wdg('wizPage'+fromPage);
        let toPageEl = wdg('wizPage'+toPage);
        fromPageEl.style.display = 'none';
        toPageEl.style.display = 'block';
        let inputWdgs = toPageEl.getElementsByTagName('input');
        if(inputWdgs && inputWdgs.length > 0) {
            inputWdgs[0].focus();
        }
        if(nextFunc) nextFunc();
    }

    function selectTab(tabIdx)
    {
        let i = 1;
        while(1) {
            let tab = wdg('tab'+i.toString());
            if(!tab) break;
            let tabh = wdg('tabHeader'+i.toString());
            if(i === tabIdx) {
                tab.style.display = 'block';
                tabh.classList.remove("UnselectedTab");
                tabh.classList.add("SelectedTab");
            } else {
                tab.style.display = 'none';
                tabh.classList.remove("SelectedTab");
                tabh.classList.add("UnselectedTab");
            }
            i++;
        }
    }

    async function tryFunc(query)
    {
        let ident = query.replace(/[^\w]/g,'');
        let fetchUrl = <?php Print(json_encode($_SERVER['SCRIPT_NAME'])); ?>;
        if(query[0] !== '?') {
            fetchUrl = fetchUrl.slice(0, fetchUrl.lastIndexOf('/')+1);
        }
        let response = await fetch(fetchUrl+query);
        let responseText = await response.text()
        let res = null;
        if(!response.ok) {
            let status = 'HTTP '+response.status+' '+response.statusText;
            console.log(ident+': '+status);
            res = {
                props: { status: response.status },
                errors: [{ error: ident+'Error', msg: query+' caused an '+status, cause: 'Full response was:<br>' + responseText }]
            }
        } else {
            try {
                res = JSON.parse(responseText);
                console.log(ident+':', res);
                for (key in res.props) {
                    let el = wdg(key);
                    if (el) {
                        el.innerHTML = res.props[key];
                    }
                }
            } catch (e) {}
            if (res == null) {
                console.log(ident+': invalid JSON', responseText);
                res = {
                    props: {},
                    errors: [{
                        error: ident + 'Error',
                        msg: (fetchUrl+query) + ' did not return valid JSON',
                        cause: 'Response was:<br>' + responseText
                    }]
                };
            }
        }
        for(let error of res.errors) {
            showError(error.error, error.msg, error.cause);
        }
        return res;
    }

    /*
     * Main code for wizard page 1 (server checks)
     */
    async function checkInstall()
    {
        let testURL = await tryFunc('?func=testURL');
        if(testURL.errors.length > 0) return;
        let testPHP = await tryFunc('?func=testPHP');
        if(testPHP.errors.length > 0) return;
        let testRW = await tryFunc('?func=testRW');
        if(testRW.errors.length > 0) return;
        let testExisting = await tryFunc('?func=testExisting');
        if(testExisting.errors.length > 0) return;
        if(testExisting.props.alreadyInstalled === 'no') {
            // New install, we must test mod_rewrite
            let createTestDir = await tryFunc('?func=createTestDir');
            if(createTestDir.errors.length > 0) return;
            let testIndexPHP = await tryFunc(createTestDir.props.dirname+'/index.php?node=Subdir');
            if(testIndexPHP.props.status === 500) {
                setErrorDetails(testIndexPHP.errors[0].error, 'Most probably your Web server does not have '+
                    '<b>mod_rewrite</b> enabled, or <b>AllowOverride</b> is not set for this directory tree.'+
                    'This is a fatal error, as this software relies on URL rewriting to work properly.');
            }
            if(testIndexPHP.errors.length > 0) return;
            if(testIndexPHP.props.node !== 'Subdir') {
                showError('nonode', 'Test index.php does not work as expected',
                    'The test script '+createTestDir.props.dirname+'/index.php created by the installer did not '+
                    'produce the expected result. This is so odd that you will need to contact Yoctopuce support...');
                return;
            }
            let testModRewrite = await tryFunc(createTestDir.props.dirname+'/Subdir/Node/Test/RewriteRules');
            if(testModRewrite.props.status === 404) {
                setErrorDetails(testModRewrite.errors[0].error,
                    'The file '+createTestDir.props.dirname+'/.htaccess created by the installer did not '+
                    'produce the expected result. Most probably your Web server does not have '+
                    '<b>mod_rewrite</b> enabled, or <b>AllowOverride</b> is not set for this directory tree.'+
                    'This is a fatal error, as this software relies on URL rewriting to work properly.');
            }
            if(testModRewrite.errors.length > 0) return;
            if(testModRewrite.props.node !== 'Subdir/Node/Test/RewriteRules') {
                showError('nonode', 'Rewrite rules in .htaccess do not work as expected',
                    'The file '+createTestDir.props.dirname+'/.htaccess created by the installer did not '+
                    'produce the expected result. This is so odd that you will need to contact Yoctopuce support...');
                return;
            }
            if(testExisting.props.htAccessFound === 'no') {
                shtml('htAccessFound', 'no, but mod_rewrite is working as expected');
            } else {
                shtml('htAccessFound', 'yes, and mod_rewrite is working as expected');
            }

            // try to fix php settings via .htaccess if needed
            let usePhpValueInHTAccess = false;
            let allowUrlFopen = String(testIndexPHP.props.allowUrlFopen).match(/^(1|On)/i);
            let postDataReading = String(testIndexPHP.props.enablePostDataReading).match(/1|On/i);
            let postMaxSize = parseInt(testIndexPHP.props.postMaxSize);
            let uploadMaxFilesize = parseInt(testIndexPHP.props.postMaxFilesize);
            let perDirSettingsNeeded = (postMaxSize<4000 || uploadMaxFilesize < 4000 || postDataReading);
            if(!allowUrlFopen || perDirSettingsNeeded) {
                let createPhpValue = await tryFunc('?func=createPhpValue');
                if(createPhpValue.errors.length > 0) return;
                let testPhpValue = await tryFunc(createTestDir.props.dirname+'/index.php?node=Subdir');
                if(testPhpValue.props.status === 500) {
                    if(perDirSettingsNeeded) {
                        setErrorDetails(testPhpValue.errors[0].error, 'Your Web server does not process '+
                            'per-dir PHP settings in <b>.user.ini</b>, but it does not accept per-dir '+
                            '<b>php_value</b> in the <b>.htaccess</b> file neither.');
                    } else {
                        setErrorDetails(testPhpValue.errors[0].error, 'Your Web server does not enable '+
                            'PHP <b>allow_url_fopen</b> setting, as required by this software. '+
                            'The installer has tried to enable it locally using <b>.htaccess</b>, but '+
                            'this did not work. You should therefore find out how to enable this '+
                            'PHP setting in the global server configuration.');
                    }
                    // Restore the first version of .htaccess without php_value tags
                    await tryFunc('?func=createTestDir');
                    return;
                } else {
                    usePhpValueInHTAccess = true;
                }
            }

            // Everything appears to work in test directory, move configuration to common directory
            let setupConf = await tryFunc('?func='+(usePhpValueInHTAccess ? 'setupWithPhpValue' : 'setupWithoutPhpValue'));
            if(setupConf.errors.length > 0) return;
            let testCommonConf = await tryFunc(createTestDir.props.dirname+'/Common/Node/Test/RewriteRules');
            if(testCommonConf.errors.length > 0 || testCommonConf.props.node !== 'Common/Node/Test/RewriteRules') {
                showError('commonConf', 'VirtualHub-4web RewriteRule does not work as expected',
                    'Although the URL RewriteRule worked in a test directory, the production version does not seems to '+
                    'produce the expected result. This is so odd that you will need to contact Yoctopuce support...');
                return;
            }

            // Still working, perfect ! We can now remove the test directory, as we will not need it anymore
            hideClass('phase1more');
            wdg('readyToInstall').style.display = 'block';
            await tryFunc('?func=removeTestDir');

            // Everything is now in place to make a real install
            shtml('basicCodeDir', testURL.props.accessURL);
            shtml('basicDataDir', testURL.props.accessURL+'/[instanceName]');
            let accessRootURL = window.location.href.replace(/[^\/]*$/,'');
            shtml('commonAccessURL', accessRootURL + '[instanceName]');
            for(let el of document.getElementsByClassName('commonAccessRootURL')) {
                el.innerHTML = accessRootURL;
            }
        } else {
            // Existing install found: don't touch the server configuration, just prepare for the upgrade
            hideClass('phase1more');
            wdg('readyToUpdate').style.display = 'block';
            shtml('installedVersion', testExisting.props.installedVersion);
            shtml('thisVersion', '<?php Print(VERSION); ?>');
            window.Vhub4webInstances = testExisting.props.instances;
        }
    }

    function selectUpdateWizPage(wizPage)
    {
        let callback = null;
        if(wizPage === 8) {
            shtml('updateButton', 'Update!');
            callback = fullUpdate
        } else {
            shtml('updateButton', 'Next >');
        }
        wdg('updateButton').onclick = () => { wizNext(1,wizPage,callback); };
        wdg('updateButton').disabled = false;
    }

    /*
     * Code for wizard page 2 (instance names)
     */
    function addInstance(listName)
    {
        let list = wdg(listName);
        let nChildren = list.childElementCount;
        let placeHolders = (listName.slice(0,3) !== 'add' ?
            [ 'sensorHub', 'plant42', 'relayController', 'allFridges', 'experimentControl' ] :
            [ 'oneMoreHub', 'plant43', 'rocketLauncher', 'coffeeMachine' ]);
        let li = document.createElement('li');
        let span = document.createElement('span');
        let input = document.createElement('input');
        span.className = 'commonAccessRootURL';
        span.innerText = window.location.href.replace(/[^\/]*$/,'');
        input.className = 'instanceName';
        if(nChildren < placeHolders.length) {
            input.placeholder = placeHolders[nChildren];
        }
        input.addEventListener('change', updateWiz2next);
        input.addEventListener('keyup', keyupTimer);
        li.appendChild(span);
        li.appendChild(input);
        list.appendChild(li);
    }

    var globalTimeout = null;
    function keyupTimer(event = null)
    {
        if(!event) event = window.event;
        let callback = event.target.onchange;
        if(globalTimeout) {
            clearTimeout(globalTimeout);
            globalTimeout = null;
        }
        globalTimeout = setTimeout(callback, 1000);
    }

    function updateWiz2next()
    {
        if(globalTimeout) {
            clearTimeout(globalTimeout);
        }

        let wiz2nextBtn = wdg('wiz2next');
        wiz2nextBtn.disabled = true;
        for(let el of document.getElementsByClassName('instanceName')) {
            let name = el.value.replace(/[^\w\-]/g, '');
            if(el.value !== name) {
                el.value = name;
            }
            if(name !== '') {
                wiz2nextBtn.disabled = false;
            }
        }
    }

    /*
     * Code for wizard page 3 (choosing password)
     */
    function updateWiz3next()
    {
        if(globalTimeout) {
            clearTimeout(globalTimeout);
        }

        let wiz3nextBtn = wdg('wiz3next');
        let pwd = wdg('pwd');
        let pwd2 = wdg('pwd2');
        wiz3nextBtn.disabled = true;
        if(pwd.value === pwd2.value) {
            wiz3nextBtn.disabled = false;
            if(pwd.value === '') {
                shtml('wiz3hint', 'It would be wiser to set a non-empty password');
            } else if(pwd.value.length <= 6) {
                shtml('wiz3hint', 'It would be wiser to set a longer password...');
            }
        } else if(pwd2.value !== '') {
            shtml('wiz3hint', 'Passwords do not match !');
        }
    }

    /*
     * Code for wizard page 4 (install mode selection)
     */
    function selectInstallType(mode)
    {
        wdg('installButton').disabled = false;
    }

    /*
     * Code for wizard page 4 (install)
     */
    async function testInstall(hostPath, pwd)
    {
        console.log('Testing '+hostPath);
        let url = window.location.protocol + '//' + window.location.host + hostPath + '/api/network.json';
        let response = await fetch(url);
        let responseText = await response.text()
        let network = null;
        if(response.ok) try {
            network = JSON.parse(responseText);
        } catch (e) {}
        if(!response.ok || !network) {
            let errmsg = 'New VirtualHub-4web instance appears not to work properly';
            let status = 'HTTP '+response.status+' '+response.statusText;
            let details = 'Full response was:<br>' + responseText;
            if(response.status !== 200) {
                details = hostPath+' returned '+status+'<br>'+details;
            }
            console.log('Testing '+hostPath+': '+status);
            return `<li>${errmsg} <a href='javascript:show(\"${hostPath}TestInstallDetails\")'>tell me more</a><div class='more' id='${hostPath}TestInstallDetails'>${details}</div></li>`;
        }
        if(pwd) {
            await fetch(url+'?userPassword='+pwd);
        }
        return '';
    }

    async function freshInstall()
    {
        // trigger install
        let isBasic = wdg('basicRadio').checked;
        let instances = [];
        for(let el of document.getElementsByClassName('instanceName')) {
            let name = el.value.trim();
            if(name !== '') {
                instances.push(name);
            }
        }
        let args = '&installType='+(isBasic ? 'basic' : 'adv')+'&instances='+encodeURIComponent(JSON.stringify(instances));
        let install = await tryFunc('?func=install'+args);
        if(install.errors.length > 0) {
            for(let error of install.errors) {
                wdg('installErrorList').innerHTML +=
                    `<li>${error.msg} <a href='javascript:show(\"${error.error}InstDetails\")'>tell me more</a><div class='more' id='${error.error}InstDetails'>${error.cause}</div></li>`;
            }
            wdg('installErrors').style.display = 'block';
            return;
        }

        // test install, setup password
        let pwd = wdg('pwd').value;
        for(let hostPath of install.props.urls) {
            let res = await testInstall(hostPath, pwd);
            if(res !== '') {
                wdg('installErrorList').innerHTML += res;
                wdg('installErrors').style.display = 'block';
                return;
            }
        }

        // report success
        wdg('installSuccess').style.display = 'block';
        let accessUrls = '';
        let callbackUrls = '';
        for(let hostPath of install.props.urls) {
            let url = window.location.protocol + '//' + window.location.host + hostPath;
            let cburl = url+'/HTTPCallback';
            accessUrls += `<li class="stt"><a href="${url}" target="_blank">${url}</a></li>`;
            callbackUrls += `<li class="stt">${cburl}</li>`;
        }
        wdg('newURLs').innerHTML = accessUrls;
        wdg('cbURLs').innerHTML = callbackUrls;
    }

    /*
     * Code for wizard page 6 (uninstall mode selection)
     */
    function selectUninstallType()
    {
        wdg('uninstallButton').disabled = false;
    }

    /*
     * Code for wizard page 7 (full uninstall)
     */
    async function fullUninstall()
    {
        // trigger uninstall of all instances
        let killData = wdg('killDataRadio').checked;
        let instances = Object.keys(window.Vhub4webInstances);
        let args = '&removeData='+(killData ? '1' : '0')+'&instances='+encodeURIComponent(JSON.stringify(instances));
        let uninstall = await tryFunc('?func=fullUninstall'+args);
        if(uninstall.errors.length > 0) {
            for(let error of uninstall.errors) {
                wdg('uninstallErrorList').innerHTML +=
                    `<li>${error.msg} <a href='javascript:show(\"${error.error}UninstDetails\")'>tell me more</a><div class='more' id='${error.error}UninstDetails'>${error.cause}</div></li>`;
            }
            wdg('uninstallErrors').style.display = 'block';
            return;
        }
        // report success
        wdg('uninstallSuccess').style.display = 'block';
    }

    /*
     * Code for wizard page 8 (full update)
     */
    async function fullUpdate()
    {
        // trigger update of all instances
        let instances = Object.keys(window.Vhub4webInstances);
        let args = '&instances='+encodeURIComponent(JSON.stringify(instances));
        let update = await tryFunc('?func=updateInstances'+args);
        if(update.errors.length > 0) {
            for(let error of update.errors) {
                wdg('updateErrorList').innerHTML +=
                    `<li>${error.msg} <a href='javascript:show(\"${error.error}UpdDetails\")'>tell me more</a><div class='more' id='${error.error}UpdDetails'>${error.cause}</div></li>`;
            }
            wdg('updateErrorList').style.display = 'block';
            return;
        }
        // report success
        wdg('updateSuccess').style.display = 'block';
    }

    /*
     * Code for wizard page 9 (adding instances)
     */
    function updateWiz9next()
    {
        if(globalTimeout) {
            clearTimeout(globalTimeout);
        }

        let wiz9nextBtn = wdg('wiz9next');
        wiz9nextBtn.disabled = true;
        for(let el of document.getElementsByClassName('instanceName')) {
            let name = el.value.replace(/[^\w\-]/g, '');
            if(el.value !== name) {
                el.value = name;
            }
            if(name !== '') {
                wiz9nextBtn.disabled = false;
            }
        }
    }

    /*
     * Code for wizard page 10 (choosing password)
     */
    function updateWiz10next()
    {
        if(globalTimeout) {
            clearTimeout(globalTimeout);
        }

        let wiz10nextBtn = wdg('wiz10next');
        let pwd = wdg('addpwd');
        let pwd2 = wdg('addpwd2');
        wiz10nextBtn.disabled = true;
        if(pwd.value === pwd2.value) {
            wiz10nextBtn.disabled = false;
            if(pwd.value === '') {
                shtml('wiz10hint', 'It would be wiser to set a non-empty password');
            } else if(pwd.value.length <= 6) {
                shtml('wiz10hint', 'It would be wiser to set a longer password...');
            }
        } else if(pwd2.value !== '') {
            shtml('wiz10hint', 'Passwords do not match !');
        }
    }

    /*
     * Code for wizard page 11 (adding instances to an existing install)
     */
    async function createInstances()
    {
        let instances = [];
        for(let el of document.getElementsByClassName('instanceName')) {
            let name = el.value.trim();
            if(name !== '') {
                instances.push(name);
            }
        }
        let args = '&instances='+encodeURIComponent(JSON.stringify(instances));
        let addInstances = await tryFunc('?func=install'+args);
        if(addInstances.errors.length > 0) {
            for(let error of addInstances.errors) {
                wdg('addInstancesErrorList').innerHTML +=
                    `<li>${error.msg} <a href='javascript:show(\"${error.error}AddInstDetails\")'>tell me more</a><div class='more' id='${error.error}AddInstDetails'>${error.cause}</div></li>`;
            }
            wdg('addInstancesErrors').style.display = 'block';
            return;
        }
        // test install, setup password
        let pwd = wdg('addpwd').value;
        for(let hostPath of addInstances.props.urls) {
            let res = await testInstall(hostPath, pwd);
            if(res !== '') {
                wdg('addInstancesErrorList').innerHTML += res;
                wdg('addInstancesErrors').style.display = 'block';
                return;
            }
        }

        // report success
        wdg('addInstancesSuccess').style.display = 'block';
        let accessUrls = '';
        let callbackUrls = '';
        for(let hostPath of addInstances.props.urls) {
            let url = window.location.protocol + '//' + window.location.host + hostPath;
            let cburl = url+'/HTTPCallback';
            accessUrls += `<li class="stt"><a href="${url}" target="_blank">${url}</a></li>`;
            callbackUrls += `<li class="stt">${cburl}</li>`;
        }
        wdg('addnewURLs').innerHTML = accessUrls;
        wdg('addcbURLs').innerHTML = callbackUrls;
    }

    /*
     * Common code to remove the installer itself
     */
    async function killInstaller()
    {
        let killme = await tryFunc('?func=removeInstaller');
        if (killme.errors.length > 0) {
            for (let error of killme.errors) {
                wdg('installErrorList').innerHTML +=
                    `<li>${error.msg} <a href='javascript:show(\"${error.error}InstDetails\")'>tell me more</a><div class='more' id='${error.error}InstDetails'>${error.cause}</div></li>`;
            }
            wdg('installErrors').style.display = 'block';
            return;
        }
        let wizardDiv = wdg('wizardDiv');
        wizardDiv.style.animationPlayState = 'running';
        let firstUrl = wdg('newURLs').getElementsByTagName('a')[0].href;
        setTimeout(() => {
            window.location = firstUrl;
        }, 1000);
    }
</script>
<style>
    body { font-family: sans-serif; display: flex; background-color: lightgray; }
    div.wizard { position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%);
        width: 780px; height: 660px; padding: 10px;
        background-color: #e1e9f3; border: 2px solid navy; border-radius: 20px;
        animation-name: fadeOut; animation-duration: 1s; animation-play-state: paused; animation-fill-mode: forwards;
    }
    h1 { font-size: 1.5em; }
    h2 { text-align: center; }
    p { text-align: justify; }
    .table { display: grid; margin: 3px 0 3px 0; width: 100%; grid-template-columns: 240px 1fr; }
    .th { grid-column: 1; border: 1px solid black; padding: 3px 10px 3px 10px; background-color: #c7dcf6; margin:0 -1px -1px 0; }
    .td { grid-column: 2; border: 1px solid black; padding: 3px 10px 3px 10px; background-color: #c7dcf6; margin:0 -1px -1px 0; }
    button { border: 1px solid black; border-radius: 5px; font-size: large; cursor: pointer; padding: 3px 8px 3px 8px; }
    #errors { border: 1px solid black; padding: 5px; background-color: lightcoral; width: 650px; margin: 30px; }
    #installErrors { border: 1px solid black; padding: 5px; background-color: lightcoral; width: 650px; margin: 30px; }
    .hidden { display:none; }
    .more { display:none; font-style:italic; padding:6px; }
    .hint { font-style: italic; color: #006ab0; }
    .stt { font-family: monospace; font-size: 13.5px; font-weight: bold; }
    .tt { font-family: monospace; font-size: large; font-weight: bold; }
    .WizButtons { position: absolute; bottom: 20px; right: 20px; }
    .TabSpacer { display: inline-block; margin-bottom: -1.1px; width: 10px; }
    .TabHeader { display: inline-block; margin-bottom: -1.1px; border: 1px solid black; border-radius: 5px 5px 0 0; padding: 5px; cursor: pointer;  }
    .SelectedTab { background-color: aliceblue; border-bottom: 1px solid aliceblue; }
    .UnselectedTab { background-color: lightgray; border-bottom: 1px solid black; }
    .Tab { border: 1px solid black; border-radius: 10px; background-color: aliceblue; padding: 0 10px 0 10px; }
    @keyframes fadeOut { 0% {opacity: 1;} 100% {opacity: 0;} }
</style>
<body onload="checkInstall()">
<div class="wizard" id="wizardDiv">
    <div id="wizPage1">
        <h2>Welcome to Yoctopuce's VirtualHub (for web) installer !</h2>
        <div>
            This tool will help you to setup VirtualHub (for web) version <b><?php Print(VERSION); ?></b> on this web server.
        </div>
        <h3>1. Checking server configuration</h3>
        <div class="table">
            <div class="th important">Installation file URL path:</div><div id="accessURL" class="td stt important"></div>
            <div class="th phase1more">Corresponding system path:</div><div id="systemPath" class="td stt phase1more"></div>
            <div class="th phase1more">File write access ?</div><div id="writeAccess" class="td phase1more"></div>
            <div class="th important">Existing .htaccess file found ?</div><div id="htAccessFound" class="td important"></div>
            <div class="th important">Existing installation found ?</div><div id="alreadyInstalled" class="td important"></div>
            <div class="th important">Current PHP version</div><div id="phpVersion" class="td important"></div>
            <div class="th phase1more">Allow URL fopen</div><div id="allowUrlFopen" class="td phase1more"></div>
            <div class="th phase1more">Enable POST data reading</div><div id="enablePostDataReading" class="td phase1more"></div>
            <div class="th phase1more">POST max size</div><div id="postMaxSize" class="td phase1more"></div>
            <div class="th phase1more">Upload max filesize</div><div id="uploadMaxFilesize" class="td phase1more"></div>
        </div>
        <div id="chkMore" style="width: 100%; text-align: right; font-size:small;"><a href="javascript:showClass('phase1more');hide('chkMore')">show more...</a></div>
        <div id="errors" style="display:none">
            <h3>Oops, we have a problem...</h3>
            <ul id="errorList"></ul>
            <div class="WizButtons"><button onclick="window.location.reload(true)">Retry</button></div>
        </div>
        <div id="readyToInstall" style="display:none">
            <h4>Everything OK for install, press Next to start configuring...</h4>
            <div class="WizButtons">
                <button onclick="wizNext(1,2)">Next &gt;</button>
            </div>
        </div>
        <div id="readyToUpdate" style="display:none">
            <p><span style="font-size: x-large; font-weight: bold;">&#8680;</span> What would you like to do ?</p>
            <div><label><input type="radio" id="updateRadio" name="updateAction" onclick="selectUpdateWizPage(8)"/> Update all instances from <span id="installedVersion"></span> to <span id="thisVersion"></span></label></div>
            <div><label><input type="radio" id="addRadio" name="updateAction" onclick="selectUpdateWizPage(9)"/> Add new instances</label></div>
            <div><label><input type="radio" id="modifyRadio" name="updateAction" onclick="selectUpdateWizPage(12)"/> Update or delete some existing instances</label></div>
            <div><label><input type="radio" id="uninstallRadio" name="updateAction" onclick="selectUpdateWizPage(6)"/> Uninstall VirtualHub-4web completely</label></div>
            <div class="WizButtons">
                <button id="updateButton" disabled>Next &gt;</button>
            </div>
        </div>
    </div>
    <div id="wizPage2" style="display:none">
        <h3>2: Choose instance name(s)</h3>
        <p>
            To avoid mixing up multiple YoctoHubs, you may want to create separate <i>instances</i> of
            VirtualHub-4web. Each <i>instance</i> will have its own device list, stored in a separate subdirectory.
        </p>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Enter one or more instance name(s) below to be created for your initial install:
        </p>
        <ol id="instanceList" style="margin-block-end: 3px;">
            <li><span class="commonAccessRootURL"></span><input class="instanceName" onchange="updateWiz2next()" onkeyup="keyupTimer()" placeholder="sensorHub"/></li>
        </ol>
        <div style="margin-left: 22px; font-size:small;"><a href="javascript:addInstance('instanceList')">add one instance...</a></div>
        <p>
            Note: the installer will only create instances to which you give a name. Placeholder names are just examples,
            but will not be used to create instances.
        </p>
        <div class="WizButtons">
            <button onclick="wizNext(2,1)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(2,3)" id="wiz2next" disabled>Next &gt;</button>
        </div>
    </div>
    <div id="wizPage3" style="display:none">
        <h3>3: Choose a password for access control</h3>
        <p>
            You should now provide a password that will prevent unauthorized access to your VirtualHub (for web).
        </p>
        <p>
            At this point the installer will set the same password for all new instances, but you will
            be able to later customize them individually using VirtualHub-4web UI, and/or set a different
            administrator password to only grand read-only access by default.
        </p>
        <p>
            The login username will be: <span class="tt">user</span>
        </p>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Enter desired password: <input type="password" id="pwd" onchange="updateWiz3next()" onkeyup="keyupTimer()"/>
        </p>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Re-enter same password: <input type="password" id="pwd2" onchange="updateWiz3next()" onkeyup="keyupTimer()"/>
        </p>
        <p id="wiz3hint" class="hint"></p>
        <div class="WizButtons">
            <button onclick="wizNext(3,2)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(3,4)" id="wiz3next" disabled>Next &gt;</button>
        </div>
    </div>
    <div id="wizPage4" style="display:none">
        <h3>4: Select installation type</h3>
        <p>You can choose betwen two installation types:</p>
        <div class="TabPanel">
            <div class="TabSpacer"></div>
            <div id="tabHeader1" class="TabHeader SelectedTab" onclick="selectTab(1)">Basic</div>
            <div id="tabHeader2" class="TabHeader UnselectedTab" onclick="selectTab(2)">Advanced</div>
            <div id="tab1" class="Tab" style="display:block">
                <p>Using a basic install, VirtualHub-4web code and data will reside directly in the HTTP server document tree:</p>
                <div class="table">
                    <div class="th">VirtualHub-4web code dir:</div><div id="basicCodeDir" class="td stt"></div>
                    <div class="th">Instance-specific data dir:</div><div id="basicDataDir" class="td stt"></div>
                </div>
                <p>
                    The advantage of the <i>Basic</i> configuration is simplicity: everything is in one directory tree.
                    Direct access to data is prevented by URL rewriting rules and software checks.
                </p>
            </div>
            <div id="tab2" class="Tab" style="display:none">
                <p>In advanced mode, a separate directory will be created outside of the HTTP server public document root
                    tree to store VirtualHub-4web PHP code and instance-specific data in a separate directories.</p>
                <div class="table">
                    <div class="th">Server document root path:</div><div id="serverRoot" class="td stt"></div>
                    <div class="th">VirtualHub-4web root path:</div><div id="advancedInstallPath" class="td stt"></div>
                    <div class="th">VirtualHub-4web code dir:</div><div class="td stt">[...]/vhub4web/dist/[versionNumber]</div>
                    <div class="th">Instance-specific data dir:</div><div class="td stt">[...]/vhub4web/data/[instanceName]</div>
                </div>
                <p>
                    In this case, the HTTP server public document tree will only contain minimal configuration files
                    plus one <span class="tt">index.php</span> per instance, which
                    will run the main code from the non-public directory.
                </p>
                <p>
                    The advantage of the <i>Advanced</i> configuration is that data resides outside of the HTTP server
                    document root tree: there is no risk of unauthorised access in case of server configuration
                    that would accidentally disable the URL rewriting rules protecting data. It also makes
                    it also to run multiple releases of the software on the same server, as each version is
                    kept in a separate subdirectory of <span class="tt">dist</span>.
                </p>
            </div>
        </div>
        <p>
            Note that this is purely a system admin choice:<br>
            In both cases, the URL to access VirtualHub-4web instances will be:
        </p>
        <div style="text-align: center" class="stt" id="commonAccessURL"></div>
        <p style="position: absolute; bottom: 50px;">
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span> Select install mode:
            <label><input type="radio" id="basicRadio" name="installType" onclick="selectInstallType('basic')"/> Basic</label> &nbsp;
            <label><input type="radio" id="advancedRadio" name="installType" onclick="selectInstallType('advanced')"/> Advanced</label>
        </p>
        <div class="WizButtons">
            <button onclick="wizNext(4,3)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(4,5,freshInstall)" id="installButton" disabled>Install now!</button>
        </div>
    </div>
    <div id="wizPage5" style="display:none">
        <h3>Installing...</h3>
        <div id="installSuccess" style="display:none">
            <p><b>Success !</b></p>
            <p>You can now use the URL belows to connect to your new VirtualHub (for web) instances:</p>
            <ul id="newURLs"></ul>
            <p>
                To get data flowing to these VirtualHub (for web) instances, you should configure in
                your YoctoHubs an HTTP Callback of type <i>Yocto-API</i> pointing to one of the following URLs:
            </p>
            <ul id="cbURLs"></ul>
            <p>
                If you are done with your setup, it would be wise to remove <b>now</b> this installer
                from the server to prevent any unauthorized access to it. You can do this using the
                the button below.
            </p>
        </div>
        <div id="installErrors" style="display:none">
            <h3>Oops, we have a problem...</h3>
            <ul id="installErrorList"></ul>
        </div>
        <div class="WizButtons">
            <button onclick="window.location.reload(true)">Restart</button>
            <button onclick="killInstaller()">Remove installer</button>
        </div>
    </div>
    <div id="wizPage6" style="display:none">
        <h3>Full uninstall</h3>
        <p>
            You are about to uninstall VirtualHub (for web) software from this server.
            If you proceed, any link pointing to these instance will return an HTTP 404 error.
        </p>
        <p>
            You can however choose whether you want to keep data, including sensor measurements
            history, in order to recover data if you later reinstall VirtualHub (for web).
            If you choose to delete data, there will be no recovery possible.
        </p>
        <div><label><input type="radio" id="keepDataRadio" name="uninstallType" onclick="selectUninstallType()"/> Keep data</label></div>
        <div><label><input type="radio" id="killDataRadio" name="uninstallType" onclick="selectUninstallType()"/> Delete data as well</label></div>
        <div class="WizButtons">
            <button onclick="wizNext(6,1)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(6,7,fullUninstall)" id="uninstallButton" disabled>Uninstall !</button>
        </div>
    </div>
    <div id="wizPage7" style="display:none">
        <h3>Uninstalling...</h3>
        <div id="uninstallSuccess" style="display:none">
            <p><b>Success !</b></p>
            <p>VirtualHub (for web) has now been removed from this server</p>
            <p>
                If you are done with your setup, it would be wise to remove <b>now</b> this installer
                from the server to prevent any unauthorized access to it. You can do this using the
                the button below.
            </p>
        </div>
        <div id="uninstallErrors" style="display:none">
            <h3>Oops, we have a problem...</h3>
            <ul id="uninstallErrorList"></ul>
        </div>
        <div class="WizButtons">
            <button onclick="window.location.reload(true)">Restart</button>
            <button onclick="killInstaller()">Remove installer</button>
        </div>
    </div>
    <div id="wizPage8" style="display:none">
        <h3>Updating...</h3>
        <div id="updateSuccess" style="display:none">
            <p><b>Success !</b></p>
            <p>VirtualHub (for web) has been updated to version <?php Print(VERSION); ?> on this server</p>
            <p>
                If you are done with your setup, it would be wise to remove <b>now</b> this installer
                from the server to prevent any unauthorized access to it. You can do this using the
                the button below.
            </p>
        </div>
        <div id="updateErrors" style="display:none">
            <h3>Oops, we have a problem...</h3>
            <ul id="updateErrorList"></ul>
        </div>
        <div class="WizButtons">
            <button onclick="window.location.reload(true)">Restart</button>
            <button onclick="killInstaller()">Remove installer</button>
        </div>
    </div>
    <div id="wizPage9" style="display:none">
        <h3>2: About to add new VirtualHub (for web) instances</h3>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Enter one or more instance name(s) below to be created:
        </p>
        <ol id="addInstanceList" style="margin-block-end: 3px;">
            <li><span class="commonAccessRootURL"></span><input class="instanceName" onchange="updateWiz9next()" onkeyup="keyupTimer()" placeholder="sensorHub"/></li>
        </ol>
        <div style="margin-left: 22px; font-size:small;"><a href="javascript:addInstance('addInstanceList')">add one instance...</a></div>
        <p>
            Note: the installer will only create instances to which you give a name. Placeholder names are just examples,
            but will not be used to create instances.
        </p>
        <div class="WizButtons">
            <button onclick="wizNext(9,1)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(9,10)" id="wiz9next" disabled>Next &gt;</button>
        </div>
    </div>
    <div id="wizPage10" style="display:none">
        <h3>3: Choose a password for access control</h3>
        <p>
            You should now provide a password to protect these new instances.
        </p>
        <p>
            At this point the installer will set the same password for all new instances, but you will
            be able to later customize them individually using VirtualHub-4web UI, and/or set a different
            administrator password to only grand read-only access by default.
        </p>
        <p>
            The login username will be: <span class="tt">user</span>
        </p>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Enter desired password: <input type="password" id="addpwd" onchange="updateWiz10next()" onkeyup="keyupTimer()"/>
        </p>
        <p>
            <span style="font-size: x-large; font-weight: bold;">&#8680;</span>
            Re-enter same password: <input type="password" id="addpwd2" onchange="updateWiz10next()" onkeyup="keyupTimer()"/>
        </p>
        <p id="wiz10hint" class="hint"></p>
        <div class="WizButtons">
            <button onclick="wizNext(10,9)">&lt; Back</button>&nbsp;
            <button onclick="wizNext(10,11,createInstances)" id="wiz10next" disabled>Create instances</button>
        </div>
    </div>
    <div id="wizPage11" style="display:none">
        <h3>Adding instances...</h3>
        <div id="addInstancesSuccess" style="display:none">
            <p><b>Success !</b></p>
            <p>You can now use the URL belows to connect to your new VirtualHub (for web) instances:</p>
            <ul id="addnewURLs"></ul>
            <p>
                To get data flowing to these VirtualHub (for web) instances, you should configure in
                your YoctoHubs an HTTP Callback of type <i>Yocto-API</i> pointing to one of the following URLs:
            </p>
            <ul id="addcbURLs"></ul>
            <p>
                If you are done with your setup, it would be wise to remove <b>now</b> this installer
                from the server to prevent any unauthorized access to it. You can do this using the
                the button below.
            </p>
        </div>
        <div id="addInstancesErrors" style="display:none">
            <h3>Oops, we have a problem...</h3>
            <ul id="addInstancesErrorList"></ul>
        </div>
        <div class="WizButtons">
            <button onclick="window.location.reload(true)">Restart</button>
            <button onclick="killInstaller()">Remove installer</button>
        </div>
    </div>
</div>
</body>