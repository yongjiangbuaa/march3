<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau

$tempAppVer="010099";

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$req_date_end = date('Ymd',time());
	$span = 2;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$s = microtime(true);

$tutorialArray=array(
		'4',
		'5',
		'3000000',
		'3070100',
		'3070089',
		'3070090',
		'3070091',
		'3070092',
		'3070093',
		'3070102',
		'3070702',
		'3010101',
		'3010102',
		'3000000',
		'3070901',
		'3070902',
		'3071001',
		'3071002',
		'3071101',
		'3071102',
		'3071302',
		'3000000',
		'3071801',
		'3071802',
		'3071901',
		'3071902',
		'3072002',
		'3072101',
		'3072102',
		'3072201',
		'3072202',
		'3000000',
		'3072401',
		'3072402',
		'3072501',
		'3072502',
		'3072702',
		'3000000',
		'3073201',
		'3073202',
		'3073301',
		'3073302',
		'3073401',
		'3073402',
		'3073501',
		'3073502',
		'3073601',
		'3073602',
		'3073701',
		'3073702',
		'3073801',
		'3073802',
		'3073901',
		'3073902',
		'3074001',
		'3074002',
		'3074101',
		'3074102',
		'3074201',
		'3074202',
		'3000000',
		'3074301',
		'3074302',
		'3074401',
		'3074402',
		'3074501',
		'3074502',
		'3074702',
		'3000000',
		'3075901',
		'3075902',
		'3076001',
		'3076002',
		'3076901',
		'3076902',
		'3076301',
		'3076302',
		'3077001',
		'3077002',
		'3000000',
		'3030101',
		'3030102',
		'3030201',
		'3030202',
		'3031301',
		'3031302',
		'3031401',
		'3031402',
		'3031201',
		'3031202',
		'3030301',
		'3030302',
		'3030401',
		'3030402',
		'3030702',
		'3030801',
		'3030802',
		'3030901',
		'3030902',
		'3031001',
		'3031002',
		'3080101',
		'3080102',
		'3080201',
		'3080202',
		'3000000',
		'3040101',
		'3040102',
		'3040201',
		'3040202',
		'3040301',
		'3040302',
		'3040401',
		'3040402',
		'3040501',
		'3040502',
		'3040601',
		'3040602',
		'3040701',
		'3040702',
		'3040801',
		'3040802',
		'3000000',
		'3090101',
		'3090102',
		'3090201',
		'3090202',
		'3090301',
		'3090302',
		'3090401',
		'3090402',
		'3000000',
		'3100101',
		'3100102',
		'3100201',
		'3100202',
		'3100301',
		'3100302',
		'3100401',
		'3100402',
);
$tutorials=implode(',', $tutorialArray);

$sql="select count(distinct u.uid) regCount,u.date,ur.appVersion,r.country,r.pf from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid = u.uid inner join $snapshotdb.user_reg ur on r.uid=ur.uid where r.type=0 and u.date = $req_date_start and u.banTime!=9223372036854775807 group by date,appVersion,country,pf;";
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
	if (getAppVersionNum($value['appVersion'])<$tempAppVer){
		continue;
	}
	$regArray[$value['date']][$country][$pf][$value['appVersion']]=$value['regCount'];
}

$sql="select count(t.uid) as perTutCount,t.tutorial,u.date,ur.appVersion,r.country,r.pf from $snapshotdb.stat_tutorial t inner join $snapshotdb.userprofile_full u on t.uid = u.uid inner join $snapshotdb.stat_reg r on t.uid = r.uid inner join $snapshotdb.user_reg ur on t.uid = ur.uid where r.type=0 and u.date = $req_date_start and u.banTime!=9223372036854775807 and t.tutorial in($tutorials) group by date,tutorial,appVersion,country,pf;";

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
	if (getAppVersionNum($value['appVersion'])<$tempAppVer){
		continue;
	}
	$perTutArray[$value['date']][$country][$pf][$value['appVersion']][$value['tutorial']]+=$value['perTutCount'];
	if(!in_array($value['tutorial'], $tutorialIdArray)){
		$tutorialIdArray[]=$value['tutorial'];
	}
}
$tutorialIdArray[]=999999999;
$records = array();
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
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);
	if(empty($f) || empty($str)){
		continue;
	}
	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE regCount=regCount+'.$fieldvalue['regCount'].',perTutCount=perTutCount+'.$fieldvalue['perTutCount'].";";
	$insertSql .= " $ondup;";
	
	$db_tbl = "$statdb_allserver.stat_tutorial_pf_country_appVersion_new";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}

if(!function_exists('getAppVersionNum')){


function getAppVersionNum($appVer){
	$temp=explode(".", $appVer);
	$numStr='';
	foreach ($temp as $trow){
		if(strlen($trow)==1){
			$trow='0'.$trow;
		}
		$numStr.=$trow;
	}
	return $numStr;
}

}

