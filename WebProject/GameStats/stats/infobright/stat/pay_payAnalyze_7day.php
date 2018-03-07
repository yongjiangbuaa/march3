<?php 
//日期	付费总用户数	流失付费用户(3日)	沉默付费用户(7日)	周期付费用户(7日)	新增付费用户(7日)	1天	2天	3天	4天	5天	6天	7天
//date	totalUsers	loseUsers		silenceUsers	repeatUsers		firsetUsers		r1	r2	r3	r4	r5	r6	r7


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
//
$client = getInfobrightConnect('pay_payAnalyze_7day.php');
if(!$client){
	echo 'mysql error pay_payAnalyze_7day.php'.PHP_EOL;
	return;
}
$s = microtime(true);
// **************
$records = array();
for($date=$req_date_start;$date<=$req_date_end;){
	$before7Day=date('Ymd',strtotime($date)-86400 * 7);
	$before3Day=date('Ymd',strtotime($date)-86400 * 3);
	$result=array('r1'=>0,'r2'=>0,'r3'=>0,'r4'=>0,'r5'=>0,'r6'=>0,'r7'=>0,);
	$sql = "select count(distinct(uid)) tUsers from $snapshotdb.paylog p where p.pf!='iostest' and date<=$date;";
	$ret = query_infobright_new($client,$sql);
	$tusers =$ret[0]['tUsers'];
	$totalUsers =$tusers;
	$sql = "select count(distinct l.uid) cnt from $snapshotdb.stat_login l inner join $snapshotdb.paylog p on p.uid=l.uid where p.pf!='iostest' and l.date>$before3Day and l.date<=$date and p.date<=$date;";
	$ret = query_infobright_new($client,$sql);
	//select count(distinct(p.uid)) lUsers from snapshot_s97.paylog p inner join (select uid, max(date) date from snapshot_s97.stat_login group by uid) l on p.uid=l.uid where p.date<=20150111 and l.date<=20150108;
	$loseUsers =($tusers-$ret[0]['cnt']);
	$sql = "select count(distinct l.uid) cnt from $snapshotdb.stat_login l inner join $snapshotdb.paylog p on l.uid=p.uid where p.pf!='iostest' and l.date>$before7Day and l.date<=$date and p.date<=$date;";
	$ret1 = query_infobright_new($client,$sql);
	$sql = "select count(distinct l.uid) cnt from $snapshotdb.stat_login l inner join $snapshotdb.paylog p on l.uid=p.uid where p.pf!='iostest' and l.date>$before7Day and p.date>$before7Day and l.date<=$date and p.date<=$date;";
	$ret2 = query_infobright_new($client,$sql);
	$silenceUsers = $ret1[0]['cnt'] - $ret2[0]['cnt'];
	
	$sql = "select count(distinct(p.uid)) rUsers from $snapshotdb.paylog p inner join (select uid, min(date) date from $snapshotdb.paylog group by uid) minp on p.uid=minp.uid inner join (select uid, max(date) date from $snapshotdb.paylog group by uid) maxp on minp.uid=maxp.uid where p.pf!='iostest' and minp.date<=$before7Day and maxp.date>$before7Day and minp.date<=$date and maxp.date<=$date;";
	$ret = query_infobright_new($client,$sql);
	$repeatUsers =$ret[0]['rUsers'];
	$sql = "select count(distinct(p.uid)) fUsers from $snapshotdb.paylog p inner join (select uid, min(date) date from $snapshotdb.paylog group by uid) minp on p.uid=minp.uid inner join (select uid, max(date) date from $snapshotdb.paylog group by uid) maxp on minp.uid=maxp.uid where p.pf!='iostest' and minp.date>$before7Day and maxp.date>$before7Day and minp.date<=$date and maxp.date<=$date;";
	$ret = query_infobright_new($client,$sql);
	$firsetUsers =$ret[0]['fUsers'];
	$sql = "select paydaycnt, count(uid) ucnt from (select uid, count(distinct date) paydaycnt from $snapshotdb.paylog p where p.pf!='iostest' and date>$before7Day and date<=$date group by uid) b group by paydaycnt;";
	$ret = query_infobright_new($client,$sql);
	foreach ($ret as $row) {
		$result['r'.$row['paydaycnt']] = $row['ucnt'];
	}
	$one = array();
	$one['date']=$date;
	$one['totalUsers']=$totalUsers;
	$one['loseUsers']=$loseUsers;
	$one['silenceUsers']=$silenceUsers;
	$one['repeatUsers']=$repeatUsers;
	$one['firsetUsers']=$firsetUsers;
	foreach ($result as $rKey=>$value){
		$one[$rKey]=$value;
	}
	$records[] = $one;
	$date=date("Ymd",strtotime($date)+86400);
}
	//echo  $records;

/////////////

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
	
	$db_tbl = "$statdb_allserver.pay_payAnalyze_7day";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



