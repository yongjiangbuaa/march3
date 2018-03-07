<?php
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
if(!defined('APP_PATH')){
	define ( 'APP_PATH', realpath ( dirname(__FILE__) ) );
}
define ( 'API_PATH',APP_PATH.'/api');
define ( 'LIB_PATH',API_PATH.'/PHPLib');
define ( 'MODEL_PATH',API_PATH.'/model');
require_once APP_PATH.'/api/citylife_config.php';
require_once API_PATH.'/Path.php';
$max = 1000;
$pm = new PersistenceManager();
for($i=0; $i<$max; $i++){
	file_put_contents('/tmp/uid.log', $pm->getGUID()."\n", FILE_APPEND);
}
?>