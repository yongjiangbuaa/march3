<?php
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
if(!defined('APP_PATH')){
	define ( 'APP_PATH', realpath ( dirname(__FILE__) . '/../../' ) );
}
define ( 'API_PATH',APP_PATH.'/api');
define ( 'LIB_PATH',API_PATH.'/PHPLib');
define ( 'MODEL_PATH',API_PATH.'/model');
require_once APP_PATH.'/api/citylife_config.php';
require_once API_PATH.'/Path.php';

$uid = '2:e717e2353f608d33';
$world = Utilities::getObject('World',$uid,$uid);
$world->objects=Utilities::getWorldObjects($uid);
file_put_contents('/tmp/checkmc.log', 'uid='.$uid.' world='.print_r($world,true)."\n", FILE_APPEND);
	
echo "script completed";
?>