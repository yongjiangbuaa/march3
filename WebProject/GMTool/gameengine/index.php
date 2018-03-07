<?php
/**
 * environment must be one of them: dev, prod. dev is default.
 * @ignore
 */
defined("__DEV__") or define("__DEV__", false);
/**
 * MVC mode is turned off by default.
 * @ignore
 */
defined("__MVC__") or define("__MVC__", true);
/**
 * LOG mode is turned on by default.
 * @ignore
 */
defined("__LOG__") or define("__LOG__", true);
/**
 * LOGSERVER mode is turned on by default.
 * @ignore
 */
defined("__LOGSERVER__") or define("__LOGSERVER__", false);
/**
 * DBVER mode is turned on by default.
 * @ignore
 */
defined("__DBVER__") or define("__DBVER__", 1.3);
/**
 * @ignore
 */
defined("XINGCLOUD_GAMEENGINE_DIR") or define("XINGCLOUD_GAMEENGINE_DIR", dirname(__FILE__));
defined("GAME_LOG_DIR") or define("GAME_LOG_DIR", dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'log');

require XINGCLOUD_GAMEENGINE_DIR."/framework/framework.php";
require XINGCLOUD_GAMEENGINE_DIR."/etc/db.inc.php";

$xingcloudRequestStartTime = microtime(true);
XingCloudApp::singleton()->start();
?>