<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;

// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on'){
// 		$selectServer[] = $server;
// 		$selectServerids[] = substr($server, 1);
// 	}
// }
$maxServer='';
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
	 continue;
	}
	$maxServer=max($maxServer,substr($server,1));
}
$sttt = $_REQUEST['selectServer'];
if (!empty($sttt)) {
	$sttt = str_replace('，', ',', $sttt);
	$sttt = str_replace(' ', '', $sttt);
	$tmp = explode(',', $sttt);
	foreach ($tmp as $tt) {
		$tt = trim($tt);
		if (!empty($tt)) {
			if(strstr($tt,'-')){
				$ttArray=explode('-', $tt);
				$min=min($ttArray[1],$maxServer);
				for ($i=$ttArray[0];$i<=$min;$i++){
					$selectServer['s'.$i] = '';
				}
			}else {
				if($tt<=$maxServer || $tt>900000){
					$selectServer['s'.$tt] = '';
				}
			}
		}
	}
}else{
	$client = new Redis();
	$client->connect(GLOBAL_REDIS_SERVER_IP);
	$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
	if (!empty($serverRatioConf)) {
		$serverConfArr = explode(';',$serverRatioConf);
		foreach($serverConfArr as $serverItem) {
			$idRatioArr = explode(':', $serverItem);
			$keyList[] = $idRatioArr[0];
		}
		$defaultselectServer = min($keyList).'-'.max($keyList);
	}
	$sttt = $defaultselectServer;
}

if (empty($selectServer)){
	$selectServer = $servers;
}
foreach ($selectServer as $key => $value) {
	$selectServerids[] = substr($key, 1);
}

$compare = false;
if(!$_REQUEST['date'])
	$rDate = date("Y-m-d",time());
$timeFix = strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'));
if (isset($_REQUEST['date'])) {
	try {
		$rDate = $_REQUEST['date'];
		$dayStart = strtotime($rDate,time());
		$dayEnd = $dayStart + 86400;
		$compareEnd = $dayStart;
		$compareStart = $dayStart - 86400;
		$compareEnd2 = $dayStart - 86400 * 6;
		$compareStart2 = $dayStart - 86400 * 7;
		$compare = $_REQUEST['compare'];
		if($compare){
			$daySal = "(`timeStamp`>={$dayStart} and `timeStamp`<{$dayEnd})||(`timeStamp`>={$compareStart} and `timeStamp`<{$compareEnd}||`timeStamp`>={$compareStart2} and `timeStamp`<{$compareEnd2})";
			$dayStart *= 1000;
			$dayEnd *= 1000;
			$compareStart *= 1000;
			$compareEnd *= 1000;
			$compareStart2 *= 1000;
			$compareEnd2 *= 1000;
			$DAUdaySal = "(`time`>={$dayStart} and `time`<{$dayEnd})||(`time`>={$compareStart} and `time`<{$compareEnd}||`time`>={$compareStart2} and `time`<{$compareEnd2})";
		}
		else{
			$daySal = "(`timeStamp`>={$dayStart} and `timeStamp`<{$dayEnd})";
			$dayStart *= 1000;
			$dayEnd *= 1000;
			$DAUdaySal = "(`time`>={$dayStart} and `time`<{$dayEnd})";
		}
		$dayStart = strtotime($rDate,time());
		$sql = "select * from fiveonlinedata where $daySal order by timeStamp desc";

		foreach ($selectServer as $server=>$serverInfo)
		{
			$sqlData = array();
			if($server=='s136'||$server=='s176'){
				$result = $page->executeServer($server,$sql,3,true);
			}else if ($server=='s900001') {
				$client900001 = mysql_connect ( '10.142.105.251', 'cok', 'DBPWD' );
				mysql_select_db('cokdb900001', $client900001);
				$result900001 = mysql_query ( $sql, $client900001 );
				$ret900001 = array ();
				while ( $row = mysql_fetch_assoc ( $result900001 ) ) {
					$ret900001 [] = $row;
				}
				$result['ret']['data'] = $ret900001;
			}else {
				$result = $page->executeServer($server,$sql,3);
			}
			foreach ($result['ret']['data'] as $everyFive){
				$timestamp = $everyFive['timeStamp'];
				$minutes = date('i',$timestamp);
				$last = substr($minutes,1);
				if ($last >= 0 && $last <=4){
					$fiveTime=strtotime(substr(date('Y-m-d H:i',$timestamp),0,15).'0');
				}else if ($last >= 5 && $last <= 9){
					$fiveTime=strtotime(substr(date('Y-m-d H:i',$timestamp),0,15).'5');
				}else {
					$fiveTime=$timestamp;
				}
				
				$sqlData[date('Ymd',$timestamp)][$fiveTime] = $everyFive['count'];
			}
			foreach ($sqlData as $date=>$dateData){
				$max[$server.'_'.$date] = 0;
				$temp2 = $temp = strtotime($date);
				do {
					$max[$server.'_'.$date] = max($max[$server.'_'.$date],$dateData[$temp]);
					$data[$server.'_'.$date][] = array('x'=>date('H:i',$temp)
									,'y'=>($dateData[$temp])?$dateData[$temp]:0);
					$temp += 300;
				}while ($temp < $temp2 + 86400);
//				foreach($dateData as $k => $val){
//					$max[$server.'_'.$date] = max($max[$server.'_'.$date],$val);
//					$data[$server.'_'.$date][] = array('x'=>date('H:i',$k)
//								,'y'=>$val);
//				}
			}
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>