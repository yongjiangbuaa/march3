<?php
/**
 * environment must be one of them: dev, prod. dev is default.
 * @ignore
 */
defined("__DEV__") or define("__DEV__", true);
/**
 * MVC mode is turned off by default.
 * @ignore
 */
defined("__MVC__") or define("__MVC__", false);
/**
 * @ignore
 */
defined("XINGCLOUD_GAMEENGINE_DIR") or define("XINGCLOUD_GAMEENGINE_DIR", dirname(__FILE__));

require XINGCLOUD_GAMEENGINE_DIR."/framework/framework.php";

$xingcloudRequestStartTime = microtime(true);
XingCloudApp::singleton()->start(FALSE);
?>