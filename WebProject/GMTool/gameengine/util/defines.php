<?php
defined('__DEV__') or define("__DEV__", false);

defined('__DS__') or define("__DS__", DIRECTORY_SEPARATOR);
define("NEW_LINE", strtoupper(substr(PHP_OS,0,2))=='WIN' ? "\r\n" : "\n");
define("SERVER_UID", md5((isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:'localhost').'_'.$_SERVER['DOCUMENT_ROOT']));
define("APC_ENABLED", ini_get('apc.enabled'));

defined('XINGCLOUD_SDK_DIR') or define("XINGCLOUD_SDK_DIR", dirname(dirname(__FILE__)));
defined('XINGCLOUD_UTIL_DIR') or define('XINGCLOUD_UTIL_DIR', XINGCLOUD_SDK_DIR.__DS__.'util');
defined('GAME_CONFIG_DIR') or define("GAME_CONFIG_DIR", XINGCLOUD_UTIL_DIR.__DS__.'config'.__DS__.'config_files');
?>