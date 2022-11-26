<?php
/*********************************************************************
 *
 *    VirtualHub-4web startup file:
 *    - setup global config parameters,
 *    - laod VirtualHub-4web Server PHP code
 *    - process current request
 *
 *    This file MAY be edited to change global defaults.
 *    However make sure to keep the marker comment after last
 *    constant definition to allow the installer to add new
 *    lines whenever needed for version upgrades
 *
 *********************************************************************/

// Setup timezone - make sure to adapt it to your location
date_default_timezone_set('Europe/Paris');

// Other global settings, usually safe to keep as-is
const UIFILE = __DIR__ . '/YFSImg.yfs';
const DEFAULT_LOGLEVEL = 3;          // Standard level of details in VirtualHub-4web file
const FILES_MAX_SIZE = 0x1ff0000;    // Max total size of user files is ~16 MB
const USERFILE_MAX_SIZE = 0x7f0000;  // Max user file is ~8 MB
const DATAFILE_MAX_SIZE = 0x100000;  // Max datalogger file (before splitting) is 1MB
const SERVERLOGS_MAX_SIZE = 512000;  // Up to 512KB of server logs before rotating
const DEVICELOGS_MAX_SIZE = 32768;   // Keep up to 32KB of device logs
const DEVICESTATS_MAX_DAYS = 400;    // Keep up to 400 days of summarized statistics about hub callbacks
const DEVICESTATS_MAX_CONN = 400;    // Keep up to 400 records of detailled informations about hub callbacks
const STATE_FILE = 'VHUB4WEB.json';  // Name of our own configuration file
const NOTIF_KEEPALIVE_DELAY = 3;     // Max long-polling time in sec. before close-to-flush
////-- MARKER: New constants may be added here when upgrading VirtualHub-4web

include_once('VHubServer.php');
VHubServer::ProcessHTTPRequest();