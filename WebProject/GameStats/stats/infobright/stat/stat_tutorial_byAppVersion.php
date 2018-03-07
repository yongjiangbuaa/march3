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

$s = microtime(true);

$versionArr=array('1.0.95');
foreach ($versionArr as $appVer){
	$sql="select count(1) regCount,u.date,r.country,r.pf from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid = u.uid where u.appVersion=$versionArr and u.date between $req_date_start and $req_date_end group by date,country,pf;";
	$ret = query_infobright($sql);
	$regArray=array();
	foreach( $ret as $value){
		if($value['pf']==null){
			$pf='roll';
			$country='roll';
		}else{
			$pf=$value['pf'];
			if($value['country']==null){
				$country = "Unknown";
			}else{
				$country = $value['country'];
			}
		}
		$regArray[$value['date']][$country][$pf][$value['appVersion']]=$value['regCount'];
	}
	
	$sql="select count(distinct(t.uid)) as perTutCount,t.tutorial,u.date,u.appVersion,r.country,r.pf from $snapshotdb.stat_tutorial t inner join $snapshotdb.userprofile_full u on t.uid = u.uid inner join $snapshotdb.stat_reg r on t.uid = r.uid where u.date between $req_date_start and $req_date_end group by date,tutorial,appVersion,country,pf;";
	$ret = query_infobright($sql);
	$perTutArray=array();
	$tutorialIdArray=array();
	foreach( $ret as $value){
		if($value['pf']==null){
			$pf='roll';
			$country='roll';
		}else{
			$pf=$value['pf'];
			if($value['country']==null){
				$country = "Unknown";
			}else{
				$country = $value['country'];
			}
		}
		$perTutArray[$value['date']][$country][$pf][$value['appVersion']][$value['tutorial']]=$value['perTutCount'];
		if(!in_array($value['tutorial'], $tutorialIdArray)){
			$tutorialIdArray[]=$value['tutorial'];
		}
	}
	$tutorialIdArray[]=999999999;
	$records[] = array();
	foreach ($regArray as $dateKey=>$cValue){
		foreach ($cValue as $countryKey=>$pValue){
			foreach ($pValue as $pfKey=>$aValue){
				foreach ($aValue as $appversionKey=>$value){
					foreach ($tutorialIdArray as $tutId){
						$one=array();
						$one['date']=$dateKey;
						$one['country']="'{$countryKey}'";
						$one['pf']="'{$pfKey}'";
						$one['appVersion']="'{$appversionKey}'";
						$one['tutorial']=$tutId;
						if($tutId==999999999){
							$one['regCount']=intval($regArray[$dateKey][$countryKey][$pfKey][$appversionKey]);
							$one['perTutCount']=0;
						}else {
							$one['regCount']=0;
							$one['perTutCount']=intval($perTutArray[$dateKey][$countryKey][$pfKey][$appversionKey][$tutId]);
						}
						$records[] = $one;
					}
				}
			}
		}
	}
	
	// print_r($records);
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
		
		$db_tbl = "$statdb_allserver.stat_tutorial_pf_country_appVersion";
		$sql = sprintf($insertSql, $db_tbl);
		//echo $sql."\n";
		query_infobright($sql);
	}
}




