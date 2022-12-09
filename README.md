VirtualHub (for Web), what's that?
==================================

The purpose of the VirtualHub (for Web) is to provide a remote access to your Yoctopuce modules, 
through Internet. It is therefore a software that must be installed on a Web server. 
You can install it on any reasonably up-to-date standard PHP host, by following the procedure 
described below.

To connect a Yoctopuce system to the VirtualHub (for Web), you just need to configure an HTTP 
callback on the YoctoHub that controls it pointing to this Web server. During each callback,
the VirtualHub (for Web) automatically downloads all the necessary information from the 
YoctoHub, including the configuration of the modules, the latest sensor data, the log 
messages from the modules, and even the files needed for the user interface.
![screenshot](https://www.yoctopuce.com/pubarchive/2022-11/vhub4web-device-list-big_1.png)

## Installation

### Installation on PHP server

To simplify the installation of this tool as much as possible, we have created a small
stand-alone PHP installer. You will find it in the subdirectory **PHP-Version/installer**.
Upload it on your web server in the subdirectory where you want to see the VirtualHub 
(for Web). If you do this using FTP, make sure to use <i>binary</i> mode. Then open a 
browser window pointing to this file and follow the few steps of the installation wizard.

It is not necessary to have a database on the Web server to use the VirtualHub (for Web): 
all the data retrieved from the Yoctopuce modules are stored by the PHP code directly in 
files on the server, in the form of a TAR archive, at the rate of one TAR file per 
Yoctopuce module.

The installer offers you to create several instances of the VirtualHub (for Web), 
if you wish. Without duplicating the code on the web server, this option allows 
you to separate several subsystems of Yoctopuce modules that are displayed 
separately, and their data are stored in separate directories.

As of today, we have tested the installer and the software on PHP 7.3, 7.4, 8.0 and 8.1,
running in the following modes: apache2handler, fpm-fcgi, cgi-fcgi and lightspeed.

### Configuration of HTTP callbacks on YoctoHub or VirtualHub

First, make sur to **update your YoctoHub firmware** (or
update your VirtualHub) **to version 51900 or higher**, and the **other Yoctopuce modules to
firmware 50730 or higher**.

You should then configure your outgoing callbacks as follow:
1. Type of callback: Yocto-API callback
2. Callback URL: *_virtualhub-4web_instance_path_*/HTTPCallback
3. Type of security: MD5 signature
4. Password: *the same as you will set on the server for incoming callbacks*

## Limitations

If you want to connect to a VirtualHub (for Web) using Yocto-Visualization or
Yoctopuce programming library, use version 51900 or higher.

When you use the VirtualHub (for Web), don't forget that the application of the settings 
is delayed to the next HTTP callback, which necessarily implies a slightly different 
behavior than a direct connection: for example, if you change an attribute and read 
back its value directly, you get the previous value until the time when the setting 
is actually applied to the module and transmitted back to the VirtualHub (for Web).

Some complex features are not yet emulated by the VirtualHub (for Web). It is the 
case for the remote update of modules firmware, and the access to the communication 
buffer of serial transmission modules (Yocto-Serial, Yocto-RS232, Yocto-RS485, ...). 
With a little luck, these features should appear progressively - as far as possible.
