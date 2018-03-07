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
if($type){
    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
    }else{
        $uid = $useruid;
    }
    $sql = "select date,sum(int_data1) food,sum(int_data2) wood,sum(int_data3) iron,sum(int_data4) crystal,count(1) from log_rbi
            where userid = '{$uid}' and category=12 and type=1 group by date;";
    $result=$page->execute($sql,3);
    if(!$result['error'] && $result['ret']['data']){
        $result_debris = $result['ret']['data'];
//        $titans = Array(
//          'curMission' => $titan[curMission],
//            'remainBlood' => $titan[remainBlood],
//            'killBlood' => $titan[killBlood],
//            'rewardTimes' => $titan[rewardTimes],
//            'isBegin' => $titan[isBegin],
//        );
//      foreach($titans as $key =>$titan){
//              $titans[$key] = (int)$clientXml[$titan['uid']];
//      }
    }else{
        $error_msg = search($result);
        $result_debris = array();
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>