<?php
// Identify location of CloudHub code, and data specific to this instance
const VHUB4WEB_ROOT = __DIR__.'/../../..';
const VHUB4WEB_DATA = VHUB4WEB_ROOT.'/data';

// Startup VirtualHub-4web
const RUN_FROM_SRC = true;
if(RUN_FROM_SRC) {
    define('VERSION', date('Y-m-d_H-i'));
    define('MOUNT_SERVER_FILES', [
        '..\..\..\..\..\..\..\projects\VirtualHub-4web\firmware\VirtualHub-4web\www',
        '..\..\..\..\..\embeddedUI'
    ]);
    include(VHUB4WEB_ROOT . '/PHP-Version/src/vhub4web-init.php');
} else {
    include(VHUB4WEB_ROOT . '/PHP-Version/dist/vhub4web-init.php');
}
