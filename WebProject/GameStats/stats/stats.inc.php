<?php
define('ROOT', dirname(__DIR__));
define('STATS_ROOT', __DIR__);
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
ini_set('display_errors', true);

require_once ROOT.'/db/db.inc.php';

foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$k = ltrim($kv[0],'-');
	$_REQUEST [$k] = $kv [1];
}

$db_list = get_db_list();


//$stats_db = array('host'=>'10.1.16.211','user'=>'cok_stat','password'=>'1234567','port'=>5029);
$stats_db = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);

$dbLink = array();
//$dbLink['global'] = array('main'=>array('10.1.16.211','3306','cok','1234567','cokdb_global'),
//		'slave'=>array('10.1.16.211','3306','cok','1234567','cokdb_global'));
$dbLink['global'] = array('main'=>array(GLOBAL_DB_SERVER_IP,'3306',GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME),
	'slave'=>array(GLOBAL_DB_SLAVE_IP,'3306', GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,GLOBAL_DB_DBNAME));

foreach ($db_list as $dbinfo) {
	$sid = 's'.$dbinfo['db_id'];
	$def = array(
			'main'  => array($dbinfo['ip_inner'], $dbinfo['port'], GAME_DB_SERVER_USER, GAME_DB_SERVER_PWD, $dbinfo['dbname']),
			'slave' => array($dbinfo['slave_ip_inner'], $dbinfo['port'], GAME_DB_SERVER_USER,  GAME_DB_SERVER_PWD,  $dbinfo['dbname']),
	);
	$dbLink[$sid] = $def;
}


function get_sfs_server_info_list($type = null, $server_range = null){
	$xml_file = '/data/htdocs/resource/servers.xml';
//	$xml_file = 'servers.xml';
	$xml = simplexml_load_file($xml_file);
	$path = 'Group/ItemSpec';
	if($type == 'test'){
		$path .= '[@test="true"]';
	}else if($type != 'all'){
		$path .= '[@test="false"]';
	}
	$list = $xml->xpath($path);
	$ret = array();
	foreach($list as $server){
		$id = strval($server['id']);
		$ret[$id] = array(
			'svr_id' => $id,
			'ip_inner' => strval($server["inner_ip"]),
		);
	}
	if(is_array($server_range) && !empty($server_range)){
		foreach ($ret as $id => $info) {
			if(!in_array($id, $server_range)){
				unset($ret[$id]);
			}
		}

	}
	return $ret;
}
