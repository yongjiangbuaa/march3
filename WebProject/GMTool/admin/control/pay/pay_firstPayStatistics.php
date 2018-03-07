<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d",time()-3*86400);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if (isset($_REQUEST['startDate'])) {
	$currPf = $_REQUEST['selectPf'];
	if ($currPf && $currPf!='ALL'){
		$miSql=" and pf='$currPf' ";
	}else {
		$miSql=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	$startDate = $_REQUEST['startDate'];
	$endDate = $_REQUEST['endDate'];
	$dayStart = strtotime($startDate,time())*1000;
	$dayEnd = strtotime($endDate,time())*1000;
	if ($_COOKIE['u']=='xiaomi'){
		if($_REQUEST['action'] == 'buildings'){
			$sql = "select count(p.uid) payTimes, DATE_FORMAT(FROM_UNIXTIME(p.`mTime`/1000),'%Y%m%d') mDate,p.buildingLv from (select uid,orderId,min(time) mTime,buildingLv from paylog group by uid) p inner join (select uid from stat_reg where 1=1 $miSql) r on p.uid=r.uid where p.mTime >$dayStart and p.mTime <=$dayEnd group by mDate,buildingLv;";
			$title = "'付费次数'";
			$title_x="'大本等级'";
		}else{
			$sql = "select p.uid, count(p.uid) payTimes,DATE_FORMAT(FROM_UNIXTIME(p.`mTime`/1000),'%Y%m%d') mDate,(DATE_FORMAT(FROM_UNIXTIME(p.`mTime`/1000),'%Y%m%d')-DATE_FORMAT(FROM_UNIXTIME(r.`time`/1000),'%Y%m%d')) buildingLv from (select uid,orderId,min(time) mTime from paylog group by uid) p inner join (select uid,time from stat_reg where 1=1 $miSql) r on p.uid=r.uid where p.mTime >$dayStart and p.mTime <=$dayEnd group by mDate,buildingLv;";
			$title = "'付费次数'";
			$title_x="'付费天数'";
		}
	}else {
		if($_REQUEST['action'] == 'buildings'){
			$sql = "select count(uid) payTimes, DATE_FORMAT(FROM_UNIXTIME(`mTime`/1000),'%Y%m%d') mDate,buildingLv from (select uid,orderId,min(time) mTime,buildingLv from paylog group by uid) p where mTime >$dayStart and mTime <=$dayEnd group by mDate,buildingLv;";
			$title = "'付费次数'";
			$title_x="'大本等级'";
		}else{
			$sql = "select p.uid, count(p.uid) payTimes,DATE_FORMAT(FROM_UNIXTIME(p.`mTime`/1000),'%Y%m%d') mDate,(DATE_FORMAT(FROM_UNIXTIME(p.`mTime`/1000),'%Y%m%d')-DATE_FORMAT(FROM_UNIXTIME(r.`time`/1000),'%Y%m%d')) buildingLv from (select uid,orderId,min(time) mTime from paylog group by uid) p inner join stat_reg r on p.uid=r.uid where p.mTime >$dayStart and p.mTime <=$dayEnd group by mDate,buildingLv;";
			$title = "'付费次数'";
			$title_x="'付费天数'";
		}
	}
	$sqlData = array();
	$dateData=array();
	foreach ($selectServer as $server=>$serInfo)
	{
		$result = $page->executeServer($server,$sql,3,false);
		$recall = 3;
		//如果没有查询到结果循环调用3次
		while(!$result['ret']['data'] && $recall++<3){
			$result = $page->executeServer($server,$sql,3,false);
		}
		if($result['ret']['data']){
			foreach ($result['ret']['data'] as $curRow){
				$dateData[$curRow['mDate']][$curRow['buildingLv']] += $curRow['payTimes'];
			}
		}
	}
	$data=array();
	$flag=false;
	foreach ($dateData as $date=>$payLevelValue){
		if($_REQUEST['action'] == 'buildings'){
			for($i=1;$i<=30;$i++){
				$data[$date][$i] = array('x'=>$i,'y'=>0);
			}
			foreach ($payLevelValue as $payLevelKey=>$paysum){
				$data[$date][$payLevelKey] = array('x'=>$payLevelKey,'y'=>$paysum);
				$total[$date] = $paysum;
			}
			$flag=false;
		}else{
			for($i=1;$i<=15;$i++){
				$data[$date][$i] = array('x'=>$i,'y'=>0);
			}
			foreach ($payLevelValue as $payLevelKey=>$paysum){
				if($payLevelKey>15||$payLevelKey<0){
					continue;
				}
				$data[$date][$payLevelKey] = array('x'=>$payLevelKey,'y'=>$paysum);
				$total[$date] = $paysum;
			}
			$flag=true;
		}
	}
	//print_r($data);
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>