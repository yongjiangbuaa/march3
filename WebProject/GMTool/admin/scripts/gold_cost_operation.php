<?php
//added by qinbin
// 20170117 gold_cost_operation.php 根据注册ip查询登陆ip

define('IN_ADMIN', true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding', 'UTF-8');
include ADMIN_ROOT . '/config.inc.php';
include ADMIN_ROOT . '/admins.php';
include_once ADMIN_ROOT . '/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');


echo '----gold_operation start-------' . date('Ymd H:i:s') . PHP_EOL;

global $servers;

//TODO: 打开
//$selectSidArr= array(1);
//2月份
//$selectSidArr = array( 27, 38, 4, 42, 47, 56, 43, 26, 82, 32, 63, 54, 44, 45, 39, 40, 2, 3, 46, 64, 59, 28, 75, 41, 37, 36, 76, 66, 50, 57, 68, 33);
//3月份
//$selectSidArr=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,29,30,31,34,35,48,49,51,52,53,55,58,60,61,62,65,67,69,70,71,72,73,74,77,78,79,80,81,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,);

$lastonlinetime = strtotime("-31 day") *1000;
$data = array();
foreach ($servers as $server => $item) {

    $sid = substr($server, 1);
    if($sid > 123 || $sid < 1){
        continue;
    }
//    if (!in_array($sid, $selectSidArr)) {
//        continue;
//    }
    $file = '/tmp/qinbin_gold_'.$server;

    $host_info = $page->getMySQLInfo(true, $server);
//    print_r($host_info);

    $mysql = get_mysqli_connection($host_info);

//    $sql = "select uid,paidgold,gold  from userprofile where bantime < 2422569600000  and paidgold > 1500 and uid='10010524300000001' ";
    $sql = "select uid,paidgold,gold  from userprofile where bantime < 2422569600000 and lastOnlineTime < $lastonlinetime and paidgold > 10 ";

    $result = gold_query_from_db($mysql, $sql);

    foreach ($result as $row) {
        $paidGold = $row['paidgold'];
        $gold = $row['gold'];
        $userId = $row['uid'];

        //获取将要操作次数
        $operationTimes = gold_gettimes($paidGold);

        if($operationTimes == 0){
            continue;
        }
        //TODO: 操作前
        echo '--1--'.'  '.$server.'  '.$row['uid'].'  '.$gold.'   '.$paidGold.PHP_EOL;
        //获取操作时间戳
        $change = intval($paidGold/$operationTimes);
        $start_row = strtotime(date('Y-m-01 00:00:00', time()))*1000;
        $end_row = strtotime(date('Y-m-02 00:00:00', time()))*1000;

        for($i = 0; $i< $operationTimes; ++$i){

            $uuid = md5(uniqid(time()));
            $createtime = mt_rand($start_row,$end_row);//月初到今天 随机时间戳

            $ori = $paidGold;
            $paidGold -= $change;
            $remain = $paidGold  >0 ? $paidGold  : 0;
            //type 4是随机
            $opSql = "insert into gold_cost_record (`uid`,`userId`,`goldType`,`type`,`param1`,`param2`,`originalGold`,`cost`,`remainGold`,`time`) values('$uuid','$userId','1','4','0','0','$ori','-$change','$remain','$createtime')";

            gold_log($opSql,$file);
            $result = gold_query_from_db($mysql, $opSql);

            $uuid = md5(uniqid(time()));

            $ori = $gold;
            $gold += $change;
            $remain = $gold;

            $opSql = "insert into gold_cost_record (`uid`,`userId`,`goldType`,`type`,`param1`,`param2`,`originalGold`,`cost`,`remainGold`,`time`) values('$uuid','$userId','0','4','0','0','$ori','$change','$remain','$createtime')";
            gold_log($opSql,$file);
            $result = gold_query_from_db($mysql, $opSql);

        }
        $updatesql = "update userprofile set gold=$gold ,paidgold=$paidGold where uid='$userId' ";
        gold_log($updatesql,$file);
        $result = gold_query_from_db($mysql, $updatesql);

        //TODO: 操作后
        echo '--2--'.'  '.$server.'  '.$row['uid'].'  '.$gold.'   '.$paidGold.PHP_EOL;

    }
    $mysql->close();

}

function gold_log($msg,$file){
//        echo $msg.PHP_EOL;
        file_put_contents($file,$msg."\n",FILE_APPEND);
}

//最高 不超20000 (2.13 10:25 最高59万)
function gold_gettimes($allPaidGold) {
    if(!is_numeric($allPaidGold)){
        return 0;
    }

    if($allPaidGold <=0 ){
        return 0;
    }else if($allPaidGold <4000){
        return mt_rand(1,2);
    }else if($allPaidGold <8000){
        return mt_rand(1,3);
    }else if($allPaidGold <15000){
        return mt_rand(2,5);
    }else if($allPaidGold < 30000){
        return mt_rand(6,8);
    }else{
        $change = mt_rand(15000,20000);
        $tmp = intval($allPaidGold/$change);
        return $tmp;
    }
}

function gold_getOperateDate($times){
    $dateArr = array();

    while($times >0){
//        $tmp = strtotime("-$times day") * 1000;
        $tmp = strtotime(date('Ymd',strtotime("-$times day") ))*1000;

        --$times;
        $dateArr[] = $tmp;
    }
    return $dateArr;
}

function gold_query_from_db($mysqli,$sql) {
    $result = $mysqli->query($sql);
    if(is_bool($result)){
//        echo '======='.PHP_EOL;
        return $result;
    }
    $data = array();
    if ($result && is_object($result)) {
        while ($row = $result->fetch_assoc()) {
            $data [] = $row;
        }
        $result->free();
    }
    return $data;

}

echo '----gold_operation end-------' . date('Ymd H:i:s') . PHP_EOL;
