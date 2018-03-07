<?php
//added by qinbin
// 20160802

define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit','1024M');

require "IP.class.php";

$sql = "select ip from test.t_ip";
$ret = query_infobright($sql);
$result = $ret['ret']['data'];
//$i=0;
echo count($result).PHP_EOL;
foreach($result as $item){
    $gameip = trim($item['ip']);
    $ip=IP::find($gameip);
//    echo $ip[0].$ip[1].$ip[2];
    $addr= trim($ip[0].$ip[1].$ip[2]);
    $sql = "update test.t_ip set addr='$addr' where ip='".$item['ip']."'";
    $ret2 = query_infobright($sql);
    if(!$ret2){
        echo $gameip.PHP_EOL;
    }
}
