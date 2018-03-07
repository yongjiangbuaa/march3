<?php
define("__DEV__", true);
//include framework code from PHAR file, default value is false
define("__PHAR__", true);

define("XINGCLOUD_GAMEENGINE_DIR", dirname(dirname(dirname(__FILE__))).'/sdk');

require XINGCLOUD_GAMEENGINE_DIR."/framework/framework.php";

XingCloudApp::singleton()->start(false);
define("XINGCLOUD_TESTCASE_DIR", XINGCLOUD_GAMEENGINE_DIR.__DS__.'public'.__DS__.'test'.__DS__.'testcase');
//import_all();
import_dir(XINGCLOUD_SERVICE_DIR);
import_dir(GAME_DIR);
?>