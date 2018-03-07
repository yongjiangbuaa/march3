<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/19
 * Time: 15:56
 */

!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
    $startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
    $endDate = date("Y-m-d",time());
if($_REQUEST['analyze']=='update'){

}
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if (!$_REQUEST['selectCountry']) {
    $currCountry = 'ALL';
}else{
    $currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
    $currPf = 'ALL';
}else{
    $currPf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['selectReferrer']) {
    $currReferrer = 'ALL';
}else{
    $currReferrer = $_REQUEST['selectReferrer'];
}
if($_REQUEST['allServers']){
    $allServerFlag =true;
}

if($_REQUEST['event']=='search'){
    $startTime = strtotime($startDate, time());
    $sids = implode(',', $selectServerids);
    $whereSql=" where sid in ($sids) ";
    $startDate = substr($_REQUEST['startDate'],0,10);
    $sDdate= date('Ymd',strtotime($startDate));
    $startTime = strtotime($sDdate, time());
    $endDate = substr($_REQUEST['endDate'],0,10);
    $eDate =date('Ymd',strtotime($endDate)+86400);
    $whereSql .= " and date >=$sDdate and date <= $eDate ";
    if($currCountry&&$currCountry!='ALL'){
        $whereSql .=" and country='$currCountry' ";
    }
    if($currPf&&$currPf!='ALL'){
        $whereSql .=" and pf='$currPf' ";
    }else if ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
        $whereSql .=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
    }
    if($currReferrer&&$currReferrer!='ALL'){
        $whereSql .=" and referrer='$currReferrer' ";
    }
    $sql= "select sid,date,sum(gold) gold,sum(paidGold) paidGold,sum(userNum) userNum from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by sid, date order by sid, date asc;";
    $result = query_infobright($sql);

    if(!$allServerFlag){
        foreach ($selectServerids as $sid){//初始化数据
            $start = $sDdate;
            while($start < $eDate){
                $eventAll['s'.$sid.'_free'][] = array('x' => $start, 'y'=>0);
                $eventAll['s'.$sid.'_paid'][] = array('x' => $start, 'y'=>0);
                $start= date('Ymd',strtotime($start) + 86400);
            }
        }
        foreach ($result['ret']['data'] as $curRow){
            $server='s'.$curRow['sid'];
            $yIndex = $curRow['date'];
            $index = (strtotime($yIndex) - strtotime($sDdate))/86400;
            $gold = $curRow['gold'] == null ? 0 : $curRow['gold'];
            $paidGold = $curRow['paidGold'] == null ? 0 : $curRow['paidGold'];
            $eventAll[$server.'_free'][$index] = array('x' => $yIndex, 'y' => $gold);
            $eventAll[$server.'_paid'][$index] = array('x' => $yIndex, 'y' => $paidGold);
//        $eventAll[$server][$index.'num'] = array('x' => $yIndex, 'y' => $curRow['userNum']);
        }
    }else{
        $start = $sDdate;
        while($start < $eDate){
            $eventAll['sAll_free'][] = array('x' => $start, 'y'=>0);
            $eventAll['sAll_paid'][] = array('x' => $start, 'y'=>0);
            $start= date('Ymd',strtotime($start) + 86400);
        }
        foreach ($result['ret']['data'] as $curRow){
            $server='s'.$curRow['sid'];
            $yIndex = $curRow['date'];
            $index = (strtotime($yIndex) - strtotime($sDdate))/86400;
            $gold = $curRow['gold'] == null ? 0 : $curRow['gold'];
            $gold += $eventAll['sAll_free'][$index]['y'];
            $paidGold = $curRow['paidGold'] == null ? 0 : $curRow['paidGold'];
            $paidGold += $eventAll['sAll_paid'][$index]['y'];
            $eventAll['sAll_free'][$index] = array('x' => $yIndex, 'y' => $gold);
            $eventAll['sAll_paid'][$index] = array('x' => $yIndex, 'y' => $paidGold);
//        $eventAll[$server][$index.'num'] = array('x' => $yIndex, 'y' => $curRow['userNum']);
        }
    }

}

include( renderTemplate("{$module}/{$module}_{$action}") );