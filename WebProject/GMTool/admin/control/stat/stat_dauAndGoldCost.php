<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if (!$_REQUEST['levelMin']){
	$levelMin=1;
}
if (!$_REQUEST['levelMax']){
	$levelMax=99;
}
set_time_limit(0);
$showData = false;
$allServerFlag=false;
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['allServers']){
	$allServerFlag =true;
}
if($_REQUEST['analyze']=='view'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	//$sTime=strtotime($sDdate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate));
	//$eTime=strtotime($eDate)*1000;
	
	$users=$_REQUEST['users'];
	$whereSql='';
	if ($users && $users!='all'){
		if($users=='payUser'){
			$whereSql .=" and paidFlag=1 ";
			$paySelected='selected="selected"';
		}elseif ($users=='ordinaryUser'){
			$whereSql .=" and paidFlag=0 ";
			$ordinarySelected='selected="selected"';
		}
	}
	
	$levelMin=$_REQUEST['levelMin'];
	$levelMax=$_REQUEST['levelMax'];
	
	$sids=implode(",", $selectServerids);
// 	if($_COOKIE['u']=='yd'){
		$sql="select sid,date,sum(dau) sDau,sum(forgingUsers) fUsers,sum(forgingTimes) fTimes,sum(steelCost) stCost,sum(cdCost) cCost,sum(materialCost) maCost from stat_allserver.stat_equipmentForgingTimes_v2 where sid in($sids) and date between $sDdate and $eDate and blevel>=$levelMin and blevel<=$levelMax $whereSql group by sid,date;";
// 	}else {
// 		$sql="select sid,date,sum(dau) sDau,sum(forgingUsers) fUsers,sum(forgingTimes) fTimes,sum(steelCost) stCost,sum(cdCost) cCost,sum(materialCost) maCost from stat_allserver.stat_equipmentForgingTimes where sid in($sids) and date between $sDdate and $eDate and blevel>=$levelMin and blevel<=$levelMax group by sid,date;";
// 	}
	$result = query_infobright($sql);
	$dates=array();
	$serverMap=array();
	foreach ($result['ret']['data'] as $curRow){
		$server = 's'.$curRow['sid'];
		$date = $curRow['date'];
		if(!in_array($date, $dates)){
			$dates[]=$curRow['date'];
		}
		if(!in_array($curRow['sid'], $serverMap)){
			$serverMap[]=$curRow['sid'];
		}
		$dau[$date][$server]=$curRow['sDau'];
		$fUsers[$date][$server]=$curRow['fUsers'];
		$fTimes[$date][$server]=$curRow['fTimes'];
		$stCost[$date][$server]=$curRow['stCost'];
		$cCost[$date][$server]=$curRow['cCost'];
		$maCost[$date][$server]=$curRow['maCost'];
	}
	
	$sql="select sid,date,sum(totalUsers) totalUsers,sum(u0) u0,sum(u1) u1,sum(u2) u2,sum(u3) u3,sum(u4) u4,sum(u5) u5,sum(s0) s0,sum(s1) s1,sum(s2) s2,sum(s3) s3,sum(s4) s4,sum(s5) s5 from stat_allserver.stat_equipUsedTimes_daily where sid in($sids) and date between $sDdate and $eDate and ublevel>=$levelMin and ublevel<=$levelMax $whereSql group by sid,date;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		$server = 's'.$curRow['sid'];
		$date = $curRow['date'];
		if(!in_array($date, $dates)){
			$dates[]=$curRow['date'];
		}
		if(!in_array($curRow['sid'], $serverMap)){
			$serverMap[]=$curRow['sid'];
		}
		$totalUsers[$date][$server]=$curRow['totalUsers'];
		for ($i=0;$i<=5;$i++){
			$equipUsers[$date][$i][$server]=$curRow['u'.$i];
			$equipScore[$date][$i][$server]=$curRow['s'.$i];
		}
	}
	
	rsort($dates);
	sort($serverMap);
	foreach ($dates as $dateValue) {
		$totalDau = array_sum($dau[$dateValue]);
		$dau[$dateValue]['合计'] = $totalDau;
		$totalFUsers = array_sum($fUsers[$dateValue]);
		$fUsers[$dateValue]['合计'] = $totalFUsers;
		$totalFTimes = array_sum($fTimes[$dateValue]);
		$fTimes[$dateValue]['合计'] = $totalFTimes;
		$totalStCost = array_sum($stCost[$dateValue]);
		$stCost[$dateValue]['合计'] = $totalStCost;
		$totalcCost = array_sum($cCost[$dateValue]);
		$cCost[$dateValue]['合计'] = $totalcCost;
		$tatolMaCost = array_sum($maCost[$dateValue]);
		$maCost[$dateValue]['合计'] = $tatolMaCost;
		
		$totalUsers[$dateValue]['合计']=array_sum($totalUsers[$dateValue]);
		for ($i=0;$i<=5;$i++){
			$equipUsers[$dateValue][$i]['合计']=array_sum($equipUsers[$dateValue][$i]);
			$equipScore[$dateValue][$i]['合计']=array_sum($equipScore[$dateValue][$i]);
		}
		
	}
	$serverMap[]='合计';
	$title = array('日期','服','活跃用户人数','锻造装备人数','锻造装备次数','使用率','祭祀钢材金币消耗','秒锻造CD金币消耗','购买小材料宝箱金币消耗','总人数','site0人数','site0穿戴率','site1人数','site1穿戴率','site2人数','site2穿戴率','site3人数','site3穿戴率','site4人数','site4穿戴率','site5人数','site5穿戴率','site0分数','site0平均分','site1分数','site1平均分','site2分数','site2平均分','site3分数','site3平均分','site4分数','site4平均分','site5分数','site5平均分',);
	$html = "<div style='float:left;width:95%;height:auto;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	foreach ($title as $width=>$value){
		if(is_numeric($width)){
			$width = "2%";
		}
		$html .= "<th width=$width>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($dates as $date){
		if($allServerFlag){
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			$html .= "<td>" . $date . "</td>";
			$html .= "<td>" . '合计' . "</td>";
			$html .= "<td>" . $dau[$date]['合计'] . "</td>";
			$html .= "<td>" . $fUsers[$date]['合计'] . "</td>";
			$html .= "<td>" . $fTimes[$date]['合计'] . "</td>";
			$html .= "<td>" . intval($fUsers[$date]['合计']*10000/$dau[$date]['合计'] )/100 ."%". "</td>";
			$html .= "<td>" . $stCost[$date]['合计'] . "</td>";
			$html .= "<td>" . $cCost[$date]['合计'] . "</td>";
			$html .= "<td>" . $maCost[$date]['合计'] . "</td>";
			$html .= "<td>" . $totalUsers[$date]['合计'] . "</td>";
			for ($i=0;$i<=5;$i++){
				$html .= "<td>" . $equipUsers[$date][$i]['合计'] . "</td>";
				$html .= "<td>" . intval($equipUsers[$date][$i]['合计']*10000/$totalUsers[$date]['合计'])/100 ."%". "</td>";
			}
			for ($i=0;$i<=5;$i++){
				$html .= "<td>" . $equipScore[$date][$i]['合计'] . "</td>";
				$html .= "<td>" . intval($equipScore[$date][$i]['合计']/$equipUsers[$date][$i]['合计']) . "</td>";
			}
			$html .= "</tr>";
			continue;
		}
		foreach ($serverMap as $serverKey){
			if($serverKey=='合计'){
				$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff' style=".'"font-weight: bold; color: rgb(119, 125, 237);"'.">";
			}else{
				$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
				$serverKey='s'.$serverKey;
			}
			$html .= "<td>" . $date . "</td>";
			$html .= "<td>" . $serverKey . "</td>";
			$html .= "<td>" . $dau[$date][$serverKey] . "</td>";
			$html .= "<td>" . $fUsers[$date][$serverKey] . "</td>";
			$html .= "<td>" . $fTimes[$date][$serverKey] . "</td>";
			$html .= "<td>" . intval($fUsers[$date][$serverKey]*10000/$dau[$date][$serverKey] )/100 ."%". "</td>";
			$html .= "<td>" . $stCost[$date][$serverKey] . "</td>";
			$html .= "<td>" . $cCost[$date][$serverKey] . "</td>";
			$html .= "<td>" . $maCost[$date][$serverKey] . "</td>";
			$html .= "<td>" . $totalUsers[$date][$serverKey] . "</td>";
			for ($i=0;$i<=5;$i++){
				$html .= "<td>" . $equipUsers[$date][$i][$serverKey] . "</td>";
				$html .= "<td>" . intval($equipUsers[$date][$i][$serverKey]*10000/$totalUsers[$date][$serverKey])/100 ."%" . "</td>";
			}
			for ($i=0;$i<=5;$i++){
				$html .= "<td>" . $equipScore[$date][$i][$serverKey] . "</td>";
				$html .= "<td>" . intval($equipScore[$date][$i][$serverKey]/$equipUsers[$date][$i][$serverKey]) . "</td>";
			}
			$html .= "</tr>";
		}
	}
	$html .= "</table></div><br/>";
}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>