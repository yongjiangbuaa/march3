<?php 

//exportGoldCostFull.php

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$req_date_end = date('Ymd',time());
	$span = 0;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
//
$s = microtime(true);
// **************
//
$colArray=array('date','userId','goldType','type','param1','param2','originalGold','cost','remainGold',
				'time','payTotal','gmFlag','level','gold','paidGold','country','allianceId','pf','lang',
				'regTime','deviceId','serverId','gmail');
$col=implode(",", $colArray);
$ym =  date('Ym',strtotime($req_date_end));
$allTable="gold_cost_record_full_$ym";
$dump_file="gold_cost_record_full_$req_date_end.csv";
$sql = "SELECT * FROM $snapshotdb.gold_cost_record_full where date between $req_date_start and $req_date_end 
		into outfile '/data/log/export/$dump_file' fields terminated by ',' lines terminated by '\n'";
query_infobright($sql);

$cmd= "sed -i 's/^/".SERVER_ID.",/' /data/log/export/".$dump_file;
$re = system($cmd, $retval);

$cmd = "sed -i '".'s/"//g'. "' /data/log/export/$dump_file";
$re = system($cmd, $retval);
//     sed -i 's/"//g' /data/log/export/gold_cost_record_full_20141130.csv

$sql="LOAD DATA LOCAL INFILE '/data/log/export/$dump_file' INTO TABLE snapshot_allserver.$allTable FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n';";
query_infobright($sql);

unlink("/data/log/export/".$dump_file);



/* $sql = "select * from $snapshotdb.gold_cost_record_full where date between $req_date_start and $req_date_end;";
$ret = query_infobright($sql);
echo $sql;
$records = array();
foreach ($ret as $fields){
	$one = array();
	$one['date'] = $fields['date'];
	$one['userId'] = $fields['userId'];
	$one['goldType'] = $fields['goldType'];
	$one['type'] = $fields['type'];
	$one['param1'] = $fields['param1'];
	$one['param2'] = $fields['param2'];
	$one['originalGold'] = $fields['originalGold'];
	$one['cost'] =  $fields['cost'];
	$one['remainGold'] = $fields['remainGold'];
	$one['time'] = $fields['time'];
	$one['payTotal'] = $fields['payTotal'];
	$one['gmFlag'] = $fields['gmFlag'];
	$one['level'] = $fields['level'];
	$one['gold'] = $fields['gold'];
	$one['paidGold'] = $fields['paidGold'];
	$one['country'] = $fields['country'];
	$one['allianceId'] = $fields['allianceId'];
	$one['pf'] = $fields['pf'];
	$one['lang'] = $fields['lang'];
	$one['regTime'] = $fields['regTime'];
	$one['deviceId'] = $fields['deviceId'];
	$one['serverId'] = $fields['serverId'];
	$one['gmail'] = $fields['gmail'];
	$records[] = $one;
}
echo $records;

foreach ($records as $fieldvalue) {
	$date=$fieldvalue['date'];
	$keys = array_keys($fieldvalue);
	$updKv = buildUpdateSql($fieldvalue);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);
	$f = 'sid,'.$f;
	$str = SERVER_ID.','.$str;
	
	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";
	
	$ym =  date('Ym',strtotime($date));
	$allTable="gold_cost_record_full_$ym";
	
	$db_tbl = "snapshot_allserver.$allTable";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);

} */
