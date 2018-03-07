<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'log_rbi';
$dbArray = array(
    'date' => array('name'=>'日期',),
    'food' => array('name'=>'粮食',),
    'wood' => array('name'=>'木头',),
    'iron' => array('name'=>'铁矿',),
    'crystal' => array('name'=>'水晶',),
    'count(1)' => array('name'=>'点击次数',),
);
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
$start = substr($start,0,10);
$end = substr($end,0,10);
$time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
$redis = new Redis();
$currserver = $page->getAppId();
$serverinfo = $servers[$currserver];
if ($currserver == 'test' || $currserver == 'localhost') {
    $t = explode(':', $serverinfo['webbase']);//http://IPIPIP:8080/gameservice/
    $ip = substr($t[1], 2);
    $rediskey = 'world0';
}else{
    $ip = $serverinfo['ip_inner'];
    $rediskey = 'world'.substr($currserver, 1);
}
$redis->connect($ip,6379);

if($type){
    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
    }else{
        $uid = $useruid;
    }
    $totalNum=$redis->hGet("hunting.user.chat",$uid);
    $totalNum=json_decode($totalNum, true);
    $html = "哥布林剩余能量".$totalNum['integral'];
//    $html = "哥布林剩余能量".$currentIP;
    $redis ->close();
//    $sql = "select date,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal,count(1) from log_rbi
//            where  category=13 and type=1 and {$time} and userid = '{$uid}' group by date;";
//    $result=$page->execute($sql,3);
//    $html .=$sql;

    $dbStart = date('Ym', time() - 7 * 86400);
    $dbEnd = date('Ym',time());
    if($dbEnd = $dbStart){
        $sql = "select date,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal,count(1) from coklog_function.function_log_{$dbEnd} WHERE
            category=13 and type=1 and {$time} and userid = '{$uid}' group by date;";
    }else{
        $sql = "select date,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal,count(1) from coklog_function.function_log_{$dbStart} WHERE
            category=13 and type=1 and {$time} and userid = '{$uid}' group by date union select date,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal,count(1) from coklog_function.function_log_{$dbEnd} WHERE
            category=13 and type=1 and {$time} and userid = '{$uid}' group by date;";
    }

    $result = query_infobright($sql);
    if(!$result['error'] && $result['ret']['data']){
        $result_debris = $result['ret']['data'];
    }else{
        $error_msg = search($result);
        $result_debris = array();
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>