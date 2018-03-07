<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit','512M');
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
	$startDate = date("Y-m-d",time()-7*86400);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if (isset($_REQUEST['startDate'])) {
	$currPf = $_REQUEST['selectPf'];
	if ($currPf && $currPf!='ALL'){
		$miSql=" and pf='$currPf' ";
	}else {
		$miSql=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	$startDate = substr($_REQUEST['startDate'],0,10);
	$endDate = substr($_REQUEST['endDate'],0,10);
	$dayStart = strtotime($startDate,time())*1000;
	$before30day=strtotime($startDate,time())*1000-86400000*30;
	//$sDate = date('Ymd',$dayStart/1000);
	$dayEnd = strtotime($endDate,time())*1000;
	//$eDate = date('Ymd',$dayEnd/1000);
	$dateData=array();
	if ($_REQUEST['action']=='grap'){
		$uidDateArr=array();
		if ($_COOKIE['u']=='xiaomi'){
			$sql="select p.uid,date_format(from_unixtime(p.time/1000),'%Y%m%d') date from paylog p inner join (select uid from stat_reg where 1=1 $miSql) r on p.uid=r.uid where p.time>=$before30day and p.time <= $dayEnd;";
		}else {
			$sql="select uid,date_format(from_unixtime(time/1000),'%Y%m%d') date from paylog where time>=$before30day and time <= $dayEnd;";
		}
		$title = "'付费人数'";
		$title_x="'支付时的间隔天数'";
		foreach ($selectServer as $server=>$serInfo){
			$result = $page->executeServer($server,$sql,3);
			if($result['ret']['data']){
				foreach ($result['ret']['data'] as $curRow){
					if (!in_array($curRow['date'], $uidDateArr[$curRow['uid']])){
						$uidDateArr[$curRow['uid']][]= $curRow['date'];
					}
				}
			}
		}
		
		foreach ($uidDateArr as $uidKey=>$dateArr){
			sort($dateArr);
			for($j=$dayStart;$j<=$dayEnd;){
				$tDate = date('Ymd',$j/1000);
				$index=array_search($tDate, $dateArr);
				if ($index){
					
					$lastPayDate=$dateArr[$index-1];
					$interval = date_diff(date_create($tDate), date_create($lastPayDate));
					$day = $interval->format('%a');
					if ($day>30 || $day==0){
						
					}else {
						$dateData[$tDate][$day]+=1;
					}
				}
				$j=$j+86400000;
			}
		}
	}else {
		if ($_COOKIE['u']=='xiaomi'){
			if($_REQUEST['action'] == 'pay'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(p.`time`/1000),'%Y-%m-%d') as date,sum(p.spend) paySum,p.buildingLv from paylog p inner join (select uid from stat_reg where 1=1 $miSql) r on p.uid=r.uid where p.time > $dayStart and p.time <= $dayEnd group by date,buildingLv;";
				$title = "'付费金额'";
			}else{
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(p.`time`/1000),'%Y-%m-%d') as date,count(distinct p.uid) paySum,p.buildingLv from paylog p inner join (select uid from stat_reg where 1=1 $miSql) r on p.uid=r.uid where p.time > $dayStart and p.time <= $dayEnd group by date,buildingLv;";
				$title = "'付费人数'";
			}
		}else {
			if($_REQUEST['action'] == 'pay'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,sum(spend) paySum,buildingLv from paylog where time > $dayStart and time <= $dayEnd group by date,buildingLv;";
				$title = "'付费金额'";
			}else{
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,count(distinct paylog.uid) paySum,buildingLv from paylog where time > $dayStart and time <= $dayEnd group by date,buildingLv;";
				$title = "'付费人数'";
			}
		}
		$title_x="'支付时的大本等级'";
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
					$dateData[$curRow['date']][$curRow['buildingLv']] += $curRow['paySum'];
				}
			}
		}
	}
	
	$data=array();
	ksort($dateData);
	foreach ($dateData as $date=>$payLevelValue){
		for($i=1;$i<=30;$i++){
			$data[$date][$i] = array('x'=>$i,'y'=>0);
		}
		foreach ($payLevelValue as $payLevelKey=>$paysum){
			$data[$date][$payLevelKey] = array('x'=>$payLevelKey,'y'=>$paysum);
			$total[$date] = $paysum;
		}
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>