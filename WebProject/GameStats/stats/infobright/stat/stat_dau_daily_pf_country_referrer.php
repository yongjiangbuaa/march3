<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$tempServer=530;
$crossServer=571;

$s = microtime(true);
$client = getInfobrightConnect('stat_dau_daily_pf_country_referrer.php');
if(!$client){
	echo 'mysql error stat_dau_daily_pf_country_referrer.php'.PHP_EOL;
	return;
}
// **************0-新注册 1-重玩 2-迁服
//$sql="select date,pf,country,type, count(distinct(uid)) reg from $snapshotdb.stat_reg where date between $req_date_start and $req_date_end group by date,pf,country,type;";

if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql="select r.date,r.pf,r.country,r.type,count(distinct r.uid) reg from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid=u.uid left join snapshot_global.cheat_deviceId cd on u.deviceId=cd.deviceId where r.date >= $req_date_start and r.date <= $req_date_end and cd.deviceId is null group by date,pf,country,type;";
}else {
	$sql="select date,pf,country,type,count(distinct uid) reg,referrer from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end group by date,pf,country,type,referrer;";
}

$ret = query_infobright_new($client,$sql);
$regArray=array();
foreach( $ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	if ($fields['type']==0){
		$regArray[$fields['date']][$pf][$country][$referrer]['newUsers'] += $fields['reg'];
	}elseif ($fields['type']==1){
		$regArray[$fields['date']][$pf][$country][$referrer]['replay'] += $fields['reg'];
	}else {
		$regArray[$fields['date']][$pf][$country][$referrer]['relocation'] += $fields['reg'];
	}
}
//计算DAU
//$sql = "SELECT l.date ,count(distinct(l.uid)) dau, r.pf, r.country from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date between $req_date_start and $req_date_end group by date,pf,country";

if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql = "select l.date,count(distinct(l.uid)) dau,count(distinct(deviceId)) deviceDau,l.pf,l.country from $snapshotdb.stat_login_full l left join snapshot_global.cheat_deviceId cd on l.deviceId=cd.deviceId where l.date between $req_date_start and $req_date_end and cd.deviceId is null group by date,pf,country;";
}else {
	$sql = "select l.date,count(distinct(l.uid)) dau,count(distinct(l.deviceId)) deviceDau,l.pf,l.country,r.referrer from $snapshotdb.stat_login_full l INNER join (SELECT uid,referrer FROM $snapshotdb.stat_reg )r ON l.uid=r.uid where date between $req_date_start and $req_date_end group by l.date,l.pf,l.country,r.referrer;";
}
$ret = query_infobright_new($client,$sql);
$tempDau=array();
foreach( $ret as $value){
	$major_key = dau_referrer($value);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$tempDau[$value['date']][$pf][$country][$referrer]['dau']+=$value['dau'];
	$tempDau[$value['date']][$pf][$country][$referrer]['deviceDau']+=$value['deviceDau'];
}

//计算付费DAU

// $sql = "SELECT l.date ,count(distinct(l.uid)) paid_dau, r.pf, r.country from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid  left join $snapshotdb.paylog p on l.uid= p.uid where l.date between $req_date_start and $req_date_end and p.payLevel>0 group by date,pf,country";
$sql = "select l.date,count(distinct l.uid) paid_dau,l.pf,l.country,r.referrer ,r.type from $snapshotdb.stat_login_full l INNER join (SELECT uid,referrer,type FROM $snapshotdb.stat_reg )r ON l.uid=r.uid where date between $req_date_start and $req_date_end and payTotal>0 group by l.date,l.pf,l.country,r.referrer,r.type;";
$ret = query_infobright_new($client,$sql);
$tempPaid=array();
foreach( $ret as $value){
	$major_key = dau_referrer($value);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
    $tempPaid[$value['date']][$pf][$country][$referrer][0]+=$value['paid_dau'];
	if($value['type'] == 2){
		$tempPaid[$value['date']][$pf][$country][$referrer][1]+=$value['paid_dau'];
	}
}

//老玩家机器码DAU
$i=$req_date_end;
$oldDevice=array();
while($i>=$req_date_start){
	if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
		$sql="select l.date as date,count(distinct(l.deviceId)) oldDeviceDau,l.pf as pf, l.country as country from $snapshotdb.stat_login_full l left join (select uid from $snapshotdb.stat_reg where date=$i and type=0) r on l.uid=r.uid left join snapshot_global.cheat_deviceId cd on l.deviceId=cd.deviceId where l.date=$i and r.uid is null and cd.deviceId is null group by date,pf,country;";
	}else {
			$sql="select ll.date as date,count(distinct(ll.deviceId)) oldDeviceDau,ll.pf as pf, ll.country as country,rr.referrer from (select l.date as date,l.deviceId,l.pf as pf, l.country as country,l.uid as uid from $snapshotdb.stat_login_full l left join (select uid from $snapshotdb.stat_reg where date=$i and type=0) r on l.uid=r.uid where l.date=$i and r.uid is null) ll left join $snapshotdb.stat_reg rr on ll.uid=rr.uid group by ll.date,ll.pf,ll.country,rr.referrer;";
	}
	//$sql = "select l.date as date,count(distinct(u.deviceId)) oldDeviceDau,r.pf as pf, r.country as country from $snapshotdb.stat_login l inner join $snapshotdb.userprofile u on l.uid=u.uid left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date=$i and r.date!=$i group by date,pf,country;";
	
	$ret = query_infobright_new($client,$sql);
	foreach( $ret as $value){
		$major_key = dau_referrer($value);
		$pf=$major_key[0];
		$country=$major_key[1];
		$referrer=$major_key[2];
		$oldDevice[$value['date']][$pf][$country][$referrer]+=$value['oldDeviceDau'];
	}
	$i = date("Ymd", strtotime("-1 day",strtotime($i)));
}

$records = array();
foreach ($tempDau as $dateKey=>$pfCountryDau){
	foreach ($pfCountryDau as $pfKey =>$countryDau){
		foreach ($countryDau as $countryKey=>$referrerDau){
			foreach($referrerDau as $referrerKey=>$dau) {
				$one = array();
				$one['date'] = $dateKey;
				$one['pf'] = "'{$pfKey}'";
				$one['country'] = "'{$countryKey}'";
				$one['referrer'] = "'{$referrerKey}'";
				$one['reg'] = intval($regArray[$dateKey][$pfKey][$countryKey][$referrerKey]['newUsers']);
				$one['replay'] = intval($regArray[$dateKey][$pfKey][$countryKey][$referrerKey]['replay']);
				$one['relocation'] = intval($regArray[$dateKey][$pfKey][$countryKey][$referrerKey]['relocation']);
				$one['dau'] = intval($dau['dau']);
				$one['paid_dau'] = intval($tempPaid[$dateKey][$pfKey][$countryKey][$referrerKey][0]);
				$one['pdau_relocation'] = intval($tempPaid[$dateKey][$pfKey][$countryKey][$referrerKey][1]);//付费迁服dau
				$one['deviceDau'] = intval($oldDevice[$dateKey][$pfKey][$countryKey][$referrerKey]);
				$one['totalDeviceDau'] = intval($dau['deviceDau']);
				$records[] = $one;
			}
		}
	}
}

// print_r($records);

insertRecord($records, $statdb_allserver,$client);

//计算金币总数总人数
$today = date('Ymd',time());
$tmpGold = array();
$sql = "select r.pf,r.country, count(u.uid) userNum, sum(u.gold) gold, sum(u.paidGold) paidGold from userprofile u left join stat_reg r on u.uid=r.uid group by r.pf,r.country";
$dbInfo = get_game_db(SERVER_ID);
$ret = query_game_db($dbInfo, $sql);
foreach( $ret as $value){
	$major_key = dau_referrer($value);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$tmpGold[$today][$pf][$country][$referrer]['gold']+=$value['gold'];
	$tmpGold[$today][$pf][$country][$referrer]['paidGold']+=$value['paidGold'];
	$tmpGold[$today][$pf][$country][$referrer]['userNum']+=$value['userNum'];
}

$records = array();
foreach ($tmpGold as $dateKey=>$pfCountryDau) {
	foreach ($pfCountryDau as $pfKey => $countryDau) {
		foreach ($countryDau as $countryKey => $referrerDau) {
			foreach ($referrerDau as $referrerKey => $dau) {
				$one = array();
				$one['date'] = $dateKey;
				$one['pf'] = "'{$pfKey}'";
				$one['country'] = "'{$countryKey}'";
				$one['referrer'] = "'{$referrerKey}'";
				$one['gold'] = intval($tmpGold[$dateKey][$pfKey][$countryKey][$referrerKey]['gold']);
				$one['paidGold'] = intval($tmpGold[$dateKey][$pfKey][$countryKey][$referrerKey]['paidGold']);
				$one['userNum'] = intval($tmpGold[$dateKey][$pfKey][$countryKey][$referrerKey]['userNum']);
				$records[] = $one;
			}
		}
	}
}

insertRecord($records, $statdb_allserver,$client);


function insertRecord($records, $statdb_allserver,$client){
	foreach ($records as $fieldvalue) {
		$keys = array_keys($fieldvalue);
		$updKv = buildUpdateSql($fieldvalue);
		$f = join(',', $keys);
		$str = join(',', $fieldvalue);
		$f = 'sid,'.$f;
		$str = SERVER_ID.','.$str;

		$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
		$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
		$insertSql .= " $ondup;";

		$db_tbl = "$statdb_allserver.stat_dau_daily_pf_country_referrer";
		$sql = sprintf($insertSql, $db_tbl);
		//echo $sql."\n";
		query_infobright_new($client,$sql);
	}
}