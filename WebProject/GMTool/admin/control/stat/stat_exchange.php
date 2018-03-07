<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

if (!$_REQUEST['country']) {
	$country = 'ALL';
}else{
	$country = $_REQUEST['country'];
}
if (!$_REQUEST['pf']) {
	$pf = 'ALL';
}else{
	$pf = $_REQUEST['pf'];
}


if ($_REQUEST['event']=='add') {
	exit();
	$contents=$_REQUEST['contents'];
	$contents=trim($contents,'|');
	$contents=str_replace('，', ',', $contents);
	$packageArray=explode('|', $contents);
	$exchangeName = require ADMIN_ROOT . '/etc/packageArray.php';
	copy(ADMIN_ROOT . '/etc/packageArray.php', ADMIN_ROOT . '/etc/packageArray.php_'.time());
	foreach ($packageArray as $packageValue){
		$temp=explode(',', $packageValue);
		$exchangeName[$temp[0]]=array($temp[1],$temp[2]);
	}
	$strarr = var_export ( $exchangeName, true );
	file_put_contents ( ADMIN_ROOT . '/etc/packageArray.php', "<?php\n \$exchangeName= " . $strarr . ";\nreturn \$exchangeName;\n?>" );
	exit('礼包添加成功');
}


if (isset($_REQUEST['getData'])) {
	$erversAndSidsArr=getSelectServersAndSids($_REQUEST['selectServer']);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	
	$start = strtotime($_REQUEST['start'])*1000;
	$end = strtotime($_REQUEST['end'])*1000;
	
	$startDate = substr($_REQUEST['start'],0,10);
	$endDate = substr($_REQUEST['end'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$eDate =date('Ymd',strtotime($endDate)+86400);
	
	if($_REQUEST['allServers']){
		$allServerFlag =true;
	}
	
	$country=$_REQUEST['country'];
	$pf=$_REQUEST['pf'];
	$whereSql='';
	if($country && $country!='ALL'){
		$whereSql.=" and country='$country' ";
	}
	if($pf && $pf!='ALL'){
		$whereSql.=" and pf='$pf' ";
	}
	global $servers;
	$colDate = array();
	for ($i=$eDate;$i>= $sDdate;){
		$colDate[] = $i;
		$i=date('Ymd',strtotime($i)-86400);	
		/* if($j++ > 30){
			break;exit('死循环检查');
		} */
	}
	/* $paySql = "SELECT productId, COUNT(*) num,date_format(from_unixtime(time/1000),'%Y-%m-%d') date 
	           FROM paylog WHERE time > $start AND time < $end GROUP BY productId,date"; */
	$result = array();
	$total = array();
	$sendNum = array();
	$totalSendNum = array();
	$sidArray = array();
	$dateArray = array();
	/*foreach ($servers as $server=>$serverInfo){
		if(substr($server, 0 ,1) != 's'){
			continue;
		}
		$sid=substr($server, 1);
		$sidArray[]=$sid;
	}
		$sids=implode(',', $sidArray);
	*/
	
	$sids = implode(',', $selectServerids);
	
		//$sql ="select sid, productId,sum(num) num, date from stat_allserver.stat_exchange_pf_country where sid in($sids) and date between $sDdate and $eDate $whereSql GROUP BY sid,date,productId;";
		$sql ="select sid, productId,sum(num) num,sum(sendNum) sendNum, date from stat_allserver.stat_exchange_pf_country_send where sid in($sids) and date between $sDdate and $eDate $whereSql GROUP BY sid,date,productId;";
		$payResult = query_infobright($sql);
		//$payResult = $page->executeServer($server,$paySql,3);
		//$payResult = $payResult['ret']['data'];
		foreach ($payResult['ret']['data'] as $payRow) {
			$result['s'.$payRow['sid']][$payRow['productId']][$payRow['date']] += $payRow['num'];
			$total[$payRow['productId']][$payRow['date']] +=  $payRow['num'];
			
			$sendNum['s'.$payRow['sid']][$payRow['productId']][$payRow['date']] += $payRow['sendNum'];
			$totalSendNum[$payRow['productId']][$payRow['date']] += $payRow['sendNum'];
			
			if(in_array($payRow['date'], $dateArray)){
				continue;
			}
			$dateArray[]=$payRow['date'];
		}
		rsort($dateArray);
// 	var_dump($result);
// 	exit($paySql);
// 	$sql = "SELECT type, param1 id, COUNT(*) num FROM (SELECT type, param1  FROM logstat WHERE `timeStamp` > $start AND `timeStamp` < $end AND type > 11 AND type < 14 GROUP BY `user`, type, param1) a GROUP BY type, param1";
// 	$result = $page->execute($sql,3);
// 	$result = $result['ret']['data'];
// 	$exchangeData = array();
// 	foreach ($result as $oneRow) {
// 	    $exchangeData[$oneRow['id']][$oneRow['type']] = $oneRow['num'];
// 	    $idArray[] = $oneRow['id'];
// 	}
// 	$idArray = array_unique($idArray);
// 	asort($idArray);
	$colNum = 4*count($servers)+2;
	$html = "<div style='float:left;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	foreach ($exchangeName as $idKey=>$namePrice){
		$numtotal = 0;
		$c3 = '';
		$all_c1 = '';
		foreach ($dateArray as $dateValue){
			$priceTotal = $total[$idKey][$dateValue]*$namePrice[1];
			
			$sendPriceTotal = $totalSendNum[$idKey][$dateValue]*$namePrice[1];
			
			$numtotal +=$total[$idKey][$dateValue];
			if($total[$idKey][$dateValue]==0){
				continue;
			}
			$c1 ="<tr><td width=20>$dateValue</td><td width=15></td><td>".$total[$idKey][$dateValue]."</td><td>$priceTotal</td><td>".$totalSendNum[$idKey][$dateValue]."</td><td>$sendPriceTotal</td>";
			$c2 = $h1 = $h2 = '';
			foreach ($result as $serverKey=>$data){
				$h1 .="<td colspan='4'>$serverKey</td>";
				$h2 .="<td>个数</td><td>金额</td><td>send个数</td><td>send金额</td>";
				$pricePerServer = $data[$idKey][$dateValue]*$namePrice[1];
				$sendPricePerServer = $sendNum[$serverKey][$idKey][$dateValue]*$namePrice[1];
				$c2 .="<td>".$data[$idKey][$dateValue]."</td><td>$pricePerServer</td><td>".$sendNum[$serverKey][$idKey][$dateValue]."</td><td>".$sendPricePerServer."</td>";
			}
			//$c2 .= "</tr><tr><td colspan='$colNum+2'></td></tr>";
			$c3 .=$c1.$c2."</tr>";
			$all_c1 .=$c1."</tr>";
		}
		if($numtotal == 0){
			continue;
		}
		$html .="<thead><th colspan='$colNum+6' align='left'>".$idKey."-".$namePrice[0]."-$".$namePrice[1]."</th></thead>";
		if ($allServerFlag){
			$html .="<tr><td width=20></td><td width=15></td><td colspan='4'>总计</td></tr><tr><td></td><td></td><td>个数</td><td>金额</td><td>send个数</td><td>send金额</td></tr>";
			$html .=$all_c1;
		}else {
			$html .="<tr><td width=20></td><td width=15></td><td colspan='4'>总计</td>";
			$html .= $h1."</tr><tr><td></td><td></td><td>个数</td><td>金额</td><td>send个数</td><td>send金额</td>".$h2."</tr>".$c3."<tr><td colspan='$colNum+6'></td></tr><tr><td colspan='$colNum+6'></td></tr>";
		}
	}
	$html .= "</table></div><br/>";	
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>