<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	//$startTime= strtotime($startDate)*1000;
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	//$endTime = (strtotime($endDate)+86400)*1000;
	$eDate =date('Ymd',strtotime($endDate)+86400);
	//$sql = "select DATE_FORMAT(FROM_UNIXTIME(p.`time`/1000),'%Y%m%d') date,ph.model,ph.version,sum(p.spend) sumSpend from paylog p inner join stat_phone ph on p.uid=ph.uid where p.time between $startTime and $endTime and ph.model like '%iphone%' or ph.model like '%ipad%' group by date,model,version;";
	$sids = implode(',', $selectServerids);
	$sql = "select * from stat_allserver.iosPay_allServer where date between $sDdate and $eDate and sid in($sids);";
	//$iosSpend=array();
	$dateSpend=array();
	//$serverSpend=array();
	$total=array();
	$dateArray=array();
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		//$iosSpend[$server][$curRow['date']][$curRow['model']][$curRow['version']]=$curRow['sumSpend'];
		$dateSpend[$curRow['date']][$curRow['model']][$curRow['version']]+=$curRow['sumSpend'];
		//$serverSpend[$server][$curRow['model']][$curRow['version']]+=$curRow['sumSpend'];
		$total[$curRow['model']][$curRow['version']]+=$curRow['sumSpend'];
		if(in_array($curRow['date'], $dateArray)){
			continue;
		}
		$dateArray[]=$curRow['date'];
	}
	sort($dateArray);
	$html = "<table class='listTable' style='text-align:center'><thead><th>合计</th>";
	foreach ($dateArray as $dateValue){
		$html .="<th>$dateValue</th>";
		$h1.="<td valign=top><table class='listTable' style='text-align:center'>";
		foreach ($dateSpend[$dateValue] as $keyModel=>$versionValue){
			foreach ($versionValue as $keyVersion=>$value){
				$h1.="<tr><td>$keyModel</td><td>$keyVersion</td><td>$value</td></tr>";
			}
		}
		$h1 .="</thead></table></td>";
	}
	$html .="<tr><td valign=top><table class='listTable' style='text-align:center'>";
	foreach ($total as $keyModel=>$versionValue){
		foreach ($versionValue as $keyVersion=>$value){
			$html .="<tr><td>$keyModel</td><td>$keyVersion</td><td>$value</td></tr>";
		}
	}
	$html.="</table></td>";
	$html.=$h1."</tr>";
/* 	foreach ($selectServer as $server){
		$html.="<tr><td>$server</td><td><table class='listTable' style='text-align:center'>";
		foreach ($serverSpend[$server] as $keyModel=>$versionValue){
			foreach ($versionValue as $keyVersion=>$value){
				$html .="<tr><td>$keyModel</td><td>$keyVersion</td><td>$value</td></tr>";
			}
		}
		$html.="</table></td>";
		foreach ($dateArray as $dateValue){
			$html.="<td><table class='listTable' style='text-align:center'>";
			foreach ($iosSpend[$server][$dateValue] as $keyModel=>$versionValue){
				foreach ($versionValue as $keyVersion=>$value){
					$html .="<tr><td>$keyModel</td><td>$keyVersion</td><td>$value</td></tr>";
				}
			}
			$html.="</table></td>";
		}
		$html.="</tr>";
	} */
	$html.="</table>";
}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>