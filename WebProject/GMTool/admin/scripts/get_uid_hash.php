<?php
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);
if(!defined('APP_PATH')){
	define ( 'APP_PATH', realpath ( dirname(__FILE__) . '/../../' ) );
}
define ( 'API_PATH',APP_PATH.'/api');
define ( 'LIB_PATH',API_PATH.'/PHPLib');
define ( 'MODEL_PATH',API_PATH.'/model');
require_once APP_PATH.'/api/citylife_config.php';
require_once API_PATH.'/Path.php';

$config = array('shop'=>              array('host'=>'10.61.5.68','db_num'=>'10', 'table_num'=>'20'),
				'sidewalk'=>          array('host'=>'10.61.5.68','db_num'=>'10', 'table_num'=>'100'),
				'road'=>              array('host'=>'10.61.5.70','db_num'=>'10', 'table_num'=>'100'),
				'farmland'=>          array('host'=>'10.61.5.70','db_num'=>'10', 'table_num'=>'100'),
				'decoration'=>        array('host'=>'10.61.5.80','db_num'=>'10', 'table_num'=>'100'),
				'house'=>             array('host'=>'10.61.5.80','db_num'=>'10', 'table_num'=>'20'),
				'crewmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'giftmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'messagecenter'=>     array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'giftrequestmessage'=>array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'warehouse'=>         array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'waterdefine'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'kfq'=>               array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'boatstation'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'airport'=>           array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'userprofile'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'usercontext'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'constructionsite'=>  array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'facility'=>          array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'headquarter'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'aircraft'=>          array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'waterfeature'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'lostmessage'=>       array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'manufacturer'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'neighbornavigator'=> array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'permitmessage'=>     array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'pier'=>              array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'playernews'=>        array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'playground'=>        array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'specialdecoration'=> array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'stadium'=>           array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				'trainmessage'=>      array('host'=>'10.61.5.84','db_num'=>'10', 'table_num'=>'20'),
				);
$uid = $_REQUEST['uid'];
$world = Utilities::getObject('World', $uid, $uid);
$objects = $world->objects;	
foreach ($objects as $value){
	$tmp_arr = array();
    $tmp_arr = split("-", $value);
    $table = strtolower($tmp_arr[0]);
    $db_num = $config[$table]['db_num'];
	$table_num = $config[$table]['table_num'];	
    $uid = $tmp_arr[1]; 
    $index = abs(crc32($uid));
	$db_index = intval($index / $table_num % $db_num);
	$db_name = sprintf("%s_%d",$table,$db_index);
	$table_name = sprintf("%s_%d",$table,intval($index % $table_num));
	$key = $db_name.'_'.$table_name;
    
    
}
?>