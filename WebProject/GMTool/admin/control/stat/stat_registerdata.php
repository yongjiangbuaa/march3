<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }
$maxServer=0;
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
	 continue;
	}
	if (substr($server,1)>900000){
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
					$selectServer['s'.$i] = 's'.$i;
					$selectId[]=$i;
				}
			}else {
				if($tt<=$maxServer){
					$selectServer['s'.$tt] = 's'.$tt;
					$selectId[]=$tt;
				}
			}
		}
	}
}else{
	$server_offset = 0;
	if($maxServer > 10) {
		$server_offset = 10;
	}
	$defaultselectServer = ($maxServer - $server_offset) . '-' . $maxServer;
	$sttt = $defaultselectServer;
//
//	$client = new Redis();
//	$client->connect(GLOBAL_REDIS_SERVER_IP);
//	$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
//	if (!empty($serverRatioConf)) {
//		$serverConfArr = explode(';',$serverRatioConf);
//		foreach($serverConfArr as $serverItem) {
//			$idRatioArr = explode(':', $serverItem);
//			$keyList[] = $idRatioArr[0];
//		}
//		$defaultselectServer = (min($keyList)-10).'-'.max($keyList);
//	}
//	$sttt = $defaultselectServer;
}

if (!$_REQUEST['appVersionName']) {
	$appVersion = 'ALL';
}else{
	$appVersion = $_REQUEST['appVersionName'];
}
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
if(!$_REQUEST['date'])
	$date = date("Y-m-d",strtotime('-6 day'));
if(!$_REQUEST['dateEnd'])
	$dateEnd = date("Y-m-d");
$timeFix = strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'));

if ($currCountry&&$currCountry!='ALL') {
	$country_sql = "and r.country ='$currCountry'";
}
if ($currPf&&$currPf!='ALL'){
	$country_sql .= " and r.pf='$currPf' ";
}
if ($currReferrer&&$currReferrer!='ALL'){
	if($currReferrer=='nature'){
		$country_sql .= " and (r.referrer='' or r.referrer is null or r.referrer='Organic') ";
	}else {
		$country_sql .= " and r.referrer='$currReferrer' ";
	}
}

if($appVersion && $appVersion != 'ALL'){
	$innersql = " inner join user_reg ur on ur.uid=r.uid ";
	$innerwhere = " ur.appVersion='$appVersion' and  ";
}else{
	$innersql='';
	$innerwhere='';
}
for ($i = 0; $i < 24; $i++) {
	$hours24[] = $i;
}

$type_sql = '1=1';
$type = $_REQUEST['regtype'];
if ($type) {
	$types = implode(',', $_REQUEST['regtype']);
	$type_sql .= " and r.type in ($types) ";
}

$pnlist = array('hour'=>'小时', 'counrty'=>'国家','pf'=>'平台','ip'=>'IP','referrer'=>'渠道');
$partition_by = 'hour';
if ($_REQUEST['partition']) {
	$partition_by = $_REQUEST['partition'];
}
$mpart = "mark$partition_by";
$$mpart = "checked";
$part_name = $pnlist[$partition_by];

if ($_REQUEST['event']) {
	$event = $_REQUEST['event'];
}

if (isset($_REQUEST['date'])) {
	foreach ($_REQUEST['regtype'] as $value) {
		$ma = "mark$value";
		$$ma = "checked";
	}
	if($_REQUEST['noserver']) {
		$noserver = true;
	}else{
		$noserver = false;
	}

	try {
		$date = $_REQUEST['date'];
		$day = date("Y-m-d",strtotime($date));
		$dayStart = strtotime(substr($date, 0, 10));
		$dateEnd = $_REQUEST['dateEnd'];
		$dayEnd = strtotime(substr($dateEnd, 0, 10));
		$dayEnd = $dayEnd + 86400;
		$sqlDayStart = $dayStart * 1000;
		$sqlDayEnd = $dayEnd * 1000;
		if ($partition_by == 'hour') {
			$sql = "SELECT count(1) cnt,DATE_FORMAT(FROM_UNIXTIME(r.time/1000),'%Y-%m-%d %H') as datehour from stat_reg r $innersql where $innerwhere r.time>={$sqlDayStart} and r.time<{$sqlDayEnd}  $country_sql and $type_sql  GROUP BY datehour";
		}elseif ($partition_by == 'country') {
			$sql = "SELECT count(1) cnt,DATE_FORMAT(FROM_UNIXTIME(r.time/1000),'%Y-%m-%d') as datehour,r.country from stat_reg r $innersql where $innerwhere r.time>={$sqlDayStart} and r.time<{$sqlDayEnd}  $country_sql and $type_sql GROUP BY datehour,r.country";
		}elseif($partition_by == 'pf'){
			$sql = "SELECT count(1) cnt,DATE_FORMAT(FROM_UNIXTIME(r.time/1000),'%Y-%m-%d') as datehour, r.pf from stat_reg r $innersql where $innerwhere r.time>={$sqlDayStart} and r.time<{$sqlDayEnd}  $country_sql and $type_sql GROUP BY datehour, r.pf";
		}elseif ($partition_by == 'ip') {
			$sql = "SELECT count(1) cnt,DATE_FORMAT(FROM_UNIXTIME(r.time/1000),'%Y-%m-%d') as datehour,r.ip from stat_reg r $innersql where $innerwhere r.time>={$sqlDayStart} and r.time<{$sqlDayEnd}  $country_sql and $type_sql GROUP BY datehour,r.ip having cnt>5";
		}elseif ($partition_by == 'referrer') {
			$sql = "SELECT count(1) cnt,DATE_FORMAT(FROM_UNIXTIME(r.time/1000),'%Y-%m-%d') as datehour,r.referrer from stat_reg r $innersql where $innerwhere r.time>={$sqlDayStart} and r.time<{$sqlDayEnd}  $country_sql and $type_sql GROUP BY datehour,r.referrer having cnt>5";
		}else {
			die("invalid params.");
		}
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			echo $sql.PHP_EOL;
		}
//		echo $sql.PHP_EOL;
		$partition_all = array();
		$totalpart=array();
		$totaldate=array();
		$total = array(); //所有日期总和,第二行
		$IPServerArr = array();
		foreach ($selectServer as $server)
		{
			$sqlData = array();
			$dateArr = array();
			$result = $page->executeServer($server,$sql,3);
			foreach ($result['ret']['data'] as $row)
			{
				$temp = explode(' ', $row["datehour"]);//2016-07-12 06
				$indate = $temp[0];//date
//				$partition 就是国家,IP,时间
				if ($partition_by == 'hour') {
					$partition = (int)$temp[1];//时间点,小时 6,7,8
				}else{
					$partition = $row[$partition_by];
					if (empty($partition)) {
						$partition = '--';
					}
				}
				$partition_all[$partition] += $row['cnt'];

				$IPServerArr[$partition]['server'][$server] = $server;

				$dateArr[$indate] = $indate;
				$total[$indate] += $row['cnt'];
				$sqlData[$indate][$partition] += $row['cnt'];
			}

			foreach ($dateArr as $indate){
				foreach ($partition_all as $part=>$val) {
					$y = ($sqlData[$indate][$part])?$sqlData[$indate][$part]:0;
					$data[$indate][$part]['x'] = $part;
					$data[$indate][$part]['y'] += $y;
					$totalpart[$part] += $y;
					$totaldate[$indate] += $y; //显示第一行,第二行总数
// 					$timelist[$part] = $part;
				}
			}
		}
		$totalsum = array_sum($total);
		ksort($totaldate);
		if ($partition_by == 'hour') {
			ksort($partition_all);
		}else{
			arsort($partition_all);
		}
// 		foreach ($totalplat as $p=>$c) {
// 			if ($c < 100) {
// 				unset($totalplat[$p]);
// 			}
// 		}
		if($noserver){
			$IPServerArr = array();
		}else{
			foreach($IPServerArr as $part=>$item){
				$tmp = array_keys($item['server']);
				$str = implode('|',$tmp);
				$IPServerArr[$part]['s'] = $str;
			}
		}

		$sDdate = date("Ymd",strtotime($date));
		$eDate = date("Ymd",strtotime($dateEnd));
		if($_REQUEST['datekey']){
			$date_key = date("Ymd",strtotime($_REQUEST['datekey']));
			$sql = "INSERT into operation_log (date,logs) VALUES (" .$date_key . "," ."'" .$_REQUEST['num'] ."'". ") ";
			$sql .= " ON DUPLICATE KEY UPDATE date=" . $date_key . " ,logs=" . "'" .$_REQUEST['num']."'" ;
			$page->globalExecute($sql,2);
		}

		$log_sql = "select * from operation_log where date >=$sDdate and date <= $eDate;";
		$log_ret = $page->globalExecute($log_sql,3);
		foreach ($log_ret['ret']['data'] as $row) {
			$datekey = date("Y-m-d",strtotime($row['date']));
			$num[$datekey]=$row['logs'];
		}

	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}else{
	$mark0 = "checked";
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
