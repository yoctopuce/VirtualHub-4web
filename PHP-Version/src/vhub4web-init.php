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
const FILES_MAX_SIZE = 0x3ff0000;    // Absolute maximal size of TAR files is ~64 MB
const USERFILE_MAX_SIZE = 0x7f0000;  // Max user file is ~8 MB
const DATAFILE_MAX_SIZE = 0x100000;  // Datalogger file chunk size is 1MB
const DATAFILE_MAX_COUNT = 30;       // Max 30 datalogger files per device before recycling old ones
const SERVERLOGS_MAX_SIZE = 512000;  // Up to 512KB of server logs before rotating
const DEVICELOGS_MAX_SIZE = 32768;   // Keep up to 32KB of device logs
const DEVICESTATS_MAX_DAYS = 400;    // Keep up to 400 days of summarized statistics about hub callbacks
const DEVICESTATS_MAX_CONN = 400;    // Keep up to 400 records of detailled informations about hub callbacks
const STATE_FILE = 'VHUB4WEB.json';  // Name of our own configuration file
const NOTIF_KEEPALIVE_DELAY = 3;     // Max long-polling time in sec. before close-to-flush
const SESSION_MAX_INACTIVITY = 15;   // Max inactivity timeout [s] before dropping a session
const SESSION_MAX_PENDING = 100;     // Max number of pending new sessions
////-- MARKER: New constants will be added here by the installer when upgrading VirtualHub-4web

include_once('VHubServer.php');
VHubServer::ProcessHTTPRequest();