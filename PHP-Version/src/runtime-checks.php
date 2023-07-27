<?php
/*********************************************************************
 *
 * $Id: runtime-checks.php 48521 2022-02-03 10:56:31Z mvuilleu $
 *
 * Perform essential runtime checks to ensure good function
 * of VirtualHub-4web application on this server
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

function check_php_conf(bool $checkDataFolder = false): array
{
    $res = [];

    if(PHP_MAJOR_VERSION < 7) {
        $res[] = [
            'error' => 'PHP_MAJOR_VERSION',
            'msg' => 'This software requires PHP version version 7.x or 8.x.',
            'cause' => 'This server is running PHP version '.phpversion().', which is out of support for several years. '.
                'You should seriously consider tp upgrade your server.'
        ];
    }

    if(PHP_INT_MAX < 0x100000000) {
        $res[] = [
            'error' => 'PHP_INT_MAX',
            'msg' => 'This software requires 64-bit integers.',
            'cause' => 'On this server, <b>PHP_INT_MAX</b> = 0x'.dechex(PHP_INT_MAX).', which is less than 64 bit. '.
                'This is not enough for this software to work properly.'
        ];
    }

    $url_fopen = ini_get('allow_url_fopen');
    if ($url_fopen !== 'On' && $url_fopen !== '1') {
        $res[] = [
            'error' => 'allow_url_fopen',
            'msg' => 'This software requires <b>allow_url_fopen</b> to be enabled.',
            'cause' => '<b>allow_url_fopen</b> is currenlty set to '.$url_fopen.'. '.
                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.
                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',
            '.user.ini' => 'allow_url_fopen="1"',
            '.htaccess' => 'php_value allow_url_fopen 1'
        ];
    }

    $max_post = ini_get('post_max_size');
    $max_post_kb = intval(str_replace(['K', 'M', 'G'], ['', '000', '000000'], $max_post));
    if ($max_post_kb < 4000) {
        $res[] = [
            'error' => 'post_max_size',
            'msg' => 'This software requires <b>post_max_size</b> to be at least 4 MB (ideally at least 8 MB).',
            'cause' => '<b>post_max_size</b> is currenlty set to '.$max_post.'. '.
                'Depending on your server setup, this can be fixed by adding a line in the directory-specific '.
                'configuration files .user.ini or .htaccess, or might require a change to the global server configuration.',
            '.user.ini' => 'post_max_size="8M"',
            '.htaccess' => 'php_value post_max_size 8M'
        ];
    }

    if($checkDataFolder) {
        // make sure the caller has defined a VHUB4WEB_DATA
        if (!defined('VHUB4WEB_DATA')) {
            $res[] = [
                'error' => 'VHUB4WEB_DATA-undefined',
                'msg' => 'This software requires a constant VHUB4WEB_DATA pointing to the directory where data should be stored.',
                'cause' => 'The entry point (currently set to '.$_SERVER['SCRIPT_NAME'].') should be a simple script that '.
                    'defines VHUB4WEB_DATA before including <b>vhub4web-init.php</b>. '.
                    'This looks like an installation error, check the documentation or re-run the easy installer process.'
            ];
            return $res;
        }

        // check for data subfolder and server configuration
        if (!file_exists(VHUB4WEB_DATA) || !is_dir(VHUB4WEB_DATA)) {
            $res[] = [
                'error' => 'VHUB4WEB_DATA-missing',
                'msg' => 'This software was configured to store data in directory <b>'.VHUB4WEB_DATA.'</b>, which cannot be found.',
                'cause' => 'Folder <b>'.VHUB4WEB_DATA.'</b> does not seems to be a valid path this server. '.
                    'This looks like an installation error, check the documentation or re-run the easy installer process.'
            ];
            return $res;
        }

        if (!is_writable(VHUB4WEB_DATA)) {
            $res[] = [
                'error' => 'VHUB4WEB_DATA-readonly',
                'msg' => 'This software was configured to store data in directory <b>'.VHUB4WEB_DATA.'</b>, which is write-protected.',
                'cause' => 'Folder <b>'.VHUB4WEB_DATA.'</b> does not seems to be a writable for this PHP script. '.
                    'This looks like an installation error, check the documentation or re-run the easy installer process.'
            ];
            return $res;
        }
    }

    return $res;
}

