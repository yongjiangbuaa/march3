<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21
 * Time: 11:40
 */
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$kill_Titan_xml = loadXml('kill_Titan','kill');
$db = 'kill_Titan';
$dbArray = array(
    'curMission' => array('name'=>'已闯到关卡',),
    'remainBlood' => array('name'=>'剩余血量',),
    'killBlood' => array('name'=>'进入下一关所需总血量',),
    'rewardTimes' => array('name'=>'玩家接到奖励数目，已发邮件数',),
    'isBegin' => array('name'=>'是否开始点杀泰坦',),
);
if($type){
    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
        $sql = "select * from $db where uid = '{$uid}'";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }
    $result=$page->execute($sql,3);
    $result_curMission=$result['ret']['data'][0]['curMission']+7000;
    $result_rewardTimes=$result['ret']['data'][0]['rewardTimes']+7000;
    $sum=0;
    if($result_rewardTimes<$result_curMission-1) {
        for ($i = $result_rewardTimes; $i <= $result_curMission - 1; $i++) {
            $sum = $sum + $kill_Titan_xml[$i+1]['count'];
        }
    }else{
        $sum=0;
    }
    $sum_glod=0;
    if($result_rewardTimes!=null){
        for($a=7001;$a<=$result_rewardTimes;$a++){
            $sum_glod = $sum_glod + $kill_Titan_xml[$a]['count'];
        }
    }else{
        $sum_glod = 0;
    }
//    $result=json_encode($result,TRUE);
    if(!$result['error'] && $result['ret']['data']){
        $titans = $result['ret']['data'];
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
        $titans = array();
    }
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>