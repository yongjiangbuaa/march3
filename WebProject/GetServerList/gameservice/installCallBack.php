<?php
ini_set('mbstring.internal_encoding', 'UTF-8');
date_default_timezone_set('UTC');
set_time_limit(0);
error_reporting(0);

define('SCRIPT_ROOT', __DIR__);
define('ROOT', dirname(__DIR__));

require_once ROOT.'/db/db.inc.php';

if(empty($_REQUEST)){
    return;
}

$valid_app = array('com.elex.coq.gp','com.elex-tech.ClashOfQueens');//modied by qinbin
$log_file = 'install.' . date("Ymd");
if(!in_array($_REQUEST['app'], $valid_app)){
    $log_file = 'install_invalid';
}

if ($_REQUEST['idfa']){
    $gaid = $_REQUEST['idfa'];
}
if ($_REQUEST['gps_adid']){
    $gaid = $_REQUEST['gps_adid'];
}
if ($_REQUEST['android_id']){
    $gaid = $_REQUEST['android_id'];
}


writeLog($log_file, $gaid, $_REQUEST);
//return;

//写入数据库 added by qinbin
$insertlog = 'referrer_install'.date('Ym').'.log';
$database = 'installcallback';
$table = 'install_callback_'.date('Ym');
file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."[start]----"."\n",FILE_APPEND);

$tables_exit = check_stattable_exist($database,$table);
file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."[table_exit]---$tables_exit=="."\n",FILE_APPEND);

if($table_exist === 'cerr'){
    return;
}
$db_info = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);
$db_info['dbname'] = $database;

if($tables_exit === false){
    // create the table
    file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."[create table $table]"."\n",FILE_APPEND);
    $table_sql = "CREATE TABLE IF NOT EXISTS `$table` (
  `date` int(8) NOT NULL DEFAULT '0',
  `time` bigint(20) DEFAULT NULL,
  `os_name` VARCHAR(20) DEFAULT NULL ,
  `country` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gaid` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `network_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'referrer',
  `version` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_name`  VARCHAR(20) DEFAULT NULL ,
  `device_type` VARCHAR(20) DEFAULT NULL ,
  `os_version` VARCHAR(20) DEFAULT NULL ,
  `tracker` varchar(20)  NOT NULL DEFAULT '',
  `tracker_name` varchar(200)  NOT NULL DEFAULT '' ,
  `app` varchar(40)  NOT NULL DEFAULT '' ,
  `activity` VARCHAR(20) DEFAULT NULL ,
  PRIMARY KEY (`gaid`,`time`),
  KEY `name_index` (`date`,`os_name`,`country`,`network_name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $re = query_game_db1($db_info,$table_sql);
    if($re == false){
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."create table fail $table "."\n",FILE_APPEND);
    }else{
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."create table success $table "."\n",FILE_APPEND);
    }
}
$dbinsert_info = array (
    'date' => date('Ymd'),
    'time' => $_REQUEST['time'],
    'os_name' => $_REQUEST['os_name'],
    'country' => $_REQUEST['country'],
    'gaid' => empty($gaid)?'none':$gaid,
    'network_name' => $_REQUEST['network_name'],
    'version' => $_REQUEST['version'],
    'ip' => $_REQUEST['ip'],
    'device_name' => $_REQUEST['device_name'],
    'device_type' => $_REQUEST['device_type'],
    'os_version' => $_REQUEST['os_version'],
    'tracker' => $_REQUEST['tracker'],
    'tracker_name' => $_REQUEST['tracker_name'],
    'app' => $_REQUEST['app'],
    'activity' => $_REQUEST['activity']
);
$dbinsert_info = escape_mysql_special_char($dbinsert_info);
$sql = build_insert_sql ( $table, $dbinsert_info );//
$re = query_game_db1($db_info,$sql);
if($re == false){
    file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."====insert into $table fail----".$sql."\n",FILE_APPEND);
}


function writeLog($file, $gaid, $log)
{
    if (!file_exists(SCRIPT_ROOT . "/installCallBackLog")) {
        mkdir(SCRIPT_ROOT . "/installCallBackLog", 0777);
    }
    $msg = date("Y-m-d H:i:s") . '	' . time() . '	' . $gaid . '	' . json_encode($log);
    file_put_contents(SCRIPT_ROOT . "/installCallBackLog/$file.log", $msg . "\n", FILE_APPEND);
}

function check_stattable_exist($dbname,$table_name){
    $dbInfo = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);
    $dbInfo['dbname'] = $dbname;

    $mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
    if ($mysqli->connect_errno) {
        global $insertlog ;
        $dblog = print_r($dbInfo,true);
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".$insertlog ,date("Y-m-d H:i:s") ."[Connect failed]:".$dblog . $mysqli->connect_error."\n",FILE_APPEND);
        return 'cerr';
    }
    $sql = "show tables like '$table_name'";
    $result = $mysqli->query($sql);
    $data = array();
    if ($result && is_object($result)) {
        while ($row = $result->fetch_assoc()) {
            $data [] = $row;
        }
        $result->free();
    }
    $mysqli->close();
    return !empty($data);
}
function query_game_db1($dbInfo,$sql) {
    $mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
    $result = $mysqli->query($sql);
    if(is_bool($result)){
        return $result;
    }
    $data = array();
    if ($result && is_object($result)) {
        /* fetch associative array */
        while ($row = $result->fetch_assoc()) {
            $data [] = $row;
        }
        /* free result set */
        $result->free();
    }
    $mysqli->close();
    return $data;

}
//function build_insert_sql1($dbtbl, $info) {
//    $keys = array_keys ( $info );
//    $vals = array_values ( $info );
//    foreach ($vals as &$v) {
//        $v = addslashes($v);
//    }
//
//    $fields = implode ( ',', $keys );
//    $values = "'" . implode ( "','", $vals ) . "'";
//    $sql = "INSERT into $dbtbl ($fields) values($values) ";
//    return $sql;
//}

function escape_mysql_special_char($val){
    $val = preg_replace('/select|update|drop|truncate|insert|delete|show|desc|ALTER|create| and | or |sleep|union|order/i','',$val);
    $pattern = '/[\']/';
    $replacement = '\\\\${0}';
    $val = preg_replace($pattern,$replacement,$val);
    return $val;
}