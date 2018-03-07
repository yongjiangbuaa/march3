<?php
/**
 * Created by PhpStorm.
 * User: qinbinbin
 * Date: 16/7/5
 * Time: 16:12
 * 导入以前渠道安装log数据用
 */
date_default_timezone_set('UTC');
ini_set('mbstring.internal_encoding', 'UTF-8');
define('SCRIPT_ROOT', __DIR__);
define('ROOT', dirname(__DIR__));
require_once ROOT.'/db/db.inc.php';

set_time_limit(0);
error_reporting(0);
define('MODULE',basename(__FILE__, '.php'));


$log_file_base = '/data/htdocs/gameservice/installCallBackLog';


//日志文件
$log_file = array(20160706,20160713);
$table_suffix = substr(strval($log_file[0]),0,6);

file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."----start----"."\n",FILE_APPEND);

$table = 'install_callback_' . $table_suffix;
$database = 'installcallback';
$dump_config = array();

$dbInfo = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);
$dbInfo['dbname'] = $database;

$table_exist = check_stat_table_exist($table);
file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====check table exist-$database:$table=$table_exist---"."\n",FILE_APPEND);
if($table_exist === 'cerr'){
    return;
}

if($table_exist === false){
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
  KEY `name_index` (`date`,`os_name`,`country`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $re = query_game_db1($dbInfo,$table_sql);
    if($re == false){
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====create table $table fail----"."\n",FILE_APPEND);
    }else{
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====create table $table success----"."\n",FILE_APPEND);
    }
}
for($i=intval($log_file[0]);$i<=intval($log_file[1]);++$i){

    $filename = $log_file_base . '/install.' . strval($i) . '.log';
    file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."==文件名==$filename----"."\n",FILE_APPEND);
    $p = fopen($filename, 'r');
    if(!$p) continue;
    $mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
    if ($mysqli->connect_errno) {
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====connect fail----"."\n",FILE_APPEND);
        continue;
    }
    while(!feof($p)){
        $line = trim(fgets($p));
//        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."-----$line----"."\n",FILE_APPEND);

        $linearray = array();
        $linearray = preg_split("/[\{]/",$line);
        $json1='{'.$linearray[1];
        $json = array();
        $json = json_decode($json1,true);
        if ($json['idfa']){
            $gaid = $json['idfa'];
        }
        if ($json['gps_adid']){
            $gaid = $json['gps_adid'];
        }
        if ($json['android_id']){
            $gaid = $json['android_id'];
        }
        $date = date('Ymd',$json['time']?$json['time']:'0');
        if($date == '0'){
            continue;
        }
	    if(count($json['tracker_name']) > 200){
		    $json['tracker_name'] = substr($json['tracker_name'],0,150);
	    }

        $dbinsert_info = array (
            'date' =>$date,
            'time' => $json['time'],
            'os_name' => $json['os_name'],
            'country' => $json['country'],
            'gaid' => empty($gaid) ? 'none':$gaid,
            'network_name' => $json['network_name'],
            'version' => $json['version'],
            'ip' => $json['ip'],
            'device_name' => $json['device_name'],
            'device_type' => $json['device_type'],
            'os_version' => $json['os_version'],
            'tracker' => $json['tracker'],
            'tracker_name' => $json['tracker_name'],
            'app' => $json['app'],
            'activity' => $json['activity']
        );
        $dbinsert_info = escape_mysql_special_char($dbinsert_info);
        $sql = build_insert_sql1($table, $dbinsert_info );
        $result = $mysqli->query($sql);
        if(!$result){
            file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====insert into $table fail----".$sql."\n",FILE_APPEND);
        }else{
            file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."====insert into $table success----"."\n",FILE_APPEND);
//            $result->free();
        }
    }
    fclose($p);
    $mysqli->close();
}


function check_stat_table_exist($table_name){
    global $dbInfo;
    $mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
    if ($mysqli->connect_errno) {
        $dblog = print_r($dbInfo,true);
        file_put_contents(SCRIPT_ROOT ."/installCallBackLog/".'loaddata_fromlog' ,date("Y-m-d H:i:s") ."[Connect failed]:".$dblog . $mysqli->connect_error."\n",FILE_APPEND);
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
function build_insert_sql1($dbtbl, $info) {
    $keys = array_keys ( $info );
    $vals = array_values ( $info );
    array_walk ( $keys ,  'trim_value' );
    array_walk ( $vals ,  'trim_value' );
    foreach ($vals as &$v) {
        $v = addslashes($v);
    }

    $fields = implode ( ',', $keys );
    $values = "'" . implode ( "','", $vals ) . "'";
    $sql = "replace into $dbtbl ($fields) values($values) ";
    return $sql;
}
function  trim_value (& $value )
{
    $value  =  trim ( $value );
}
function escape_mysql_special_char($val){
    $val = preg_replace('/select|update|drop|truncate|insert|delete|show|desc|ALTER|create| and | or |sleep|union|order/i','',$val);
    $pattern = '/[\']/';
    $replacement = '\\\\${0}';
    $val = preg_replace($pattern,$replacement,$val);
    return $val;
}
//select r.date,r.pf,count(1) cnt from coq_reginfo.reginfo_2016076 r inner join installcallback.install_callback_201607 ss on ss.gaid=r.gaid where (r.referrer='' or r.referrer is NULL or r.referrer='organic') and ss.network_name!='Organic' group by r.date,r.pf;