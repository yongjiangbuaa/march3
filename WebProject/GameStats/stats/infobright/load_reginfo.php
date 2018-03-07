<?php
/**
 * Created by PhpStorm.
 * User: qinbin
 * Date: 16/7/12
 * Time: 10:42
 * 导入snapshot库数据到 allserver中,方便与installcallback.install_callback_201607连表查询
 */

defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));

$pidFile = '/tmp/load_reginfo'.SERVER_ID.'.pid';

write_pid_file(MODULE);

$span = 1;
if(isset($_REQUEST['fixdate'])){
    $req_date_end = $_REQUEST['fixdate'];
}else{
    $req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));


$table_suffix = $_REQUEST['table'];
if(empty($table_suffix)){
    $table_suffix = date('Ym',strtotime($req_date_start));
}

$snapshotdb = IB_DB_NAME_SNAPSHOT;

$table = 'reginfo_' . $table_suffix;
$database = 'coq_reginfo'; //数据库

$table_exist = check_stat_table_exist($database, $table);
echo "check table exist $database:$table====$table_exist   ",PHP_EOL;
if($table_exist === null){
    writeRunLog1("check table exist for $database $table fail, abort run script");
    remove_pid_file(MODULE);
    return;
}
if($table_exist === false){
    // create the table
    $table_sql = "CREATE TABLE `$table` (
  `date` int(8) NOT NULL DEFAULT '0',
  `uid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gaid` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pf` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `referrer` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' ,
  `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`date`,`uid`,`gaid`),
  KEY `gaid_ip` (`gaid`,`referrer`,`ip`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db_info = $stats_db;
    $db_info['dbname'] = $database;
    $re = query_game_db($db_info,$table_sql);
    if($re == false){
        writeRunLog1("create table $table fail");
    }
}

writeRunLog1("start to load data for table:$table log file: $log_file");

$sql = "replace into $database.$table(date,uid,gaid,pf,referrer,country,ip) select r.date as date,r.uid as uid,u.gaid as gaid ,r.pf as pf,r.referrer as referrer ,r.country as country ,r.ip as ip from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on u.uid=r.uid where r.type=0 and r.date>=$req_date_start and r.date<=$req_date_end;";
//echo $sql.PHP_EOL;
$ret = query_infobright($sql);
//echo print_r($ret,true);
if($ret == false ){
    writeRunLog1("replace into $sql fail");
}else {
    writeRunLog1("finished insert data for table:$table");
}

remove_pid_file(MODULE);

function writeRunLog1($msg){
    $logdir = '/home/data/log/scripts/runlog';
    if (!file_exists($logdir)) {
        mkdir($logdir,0775,TRUE);
    }
    file_put_contents($logdir.'/load_reginfo.log', date("[Y-m-d H:i:s]")." $msg\n", FILE_APPEND);
}