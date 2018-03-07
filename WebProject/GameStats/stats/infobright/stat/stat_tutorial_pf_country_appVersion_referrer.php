<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau

$tempAppVer="010099";

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
$client = getInfobrightConnect('stat_tutorial_pf_country_appVersion_referrer.php');
if(!$client){
	echo 'mysql error stat_tutorial_pf_country_appVersion_referrer.php'.PHP_EOL;
	return;
}
$tutorialArray=array(
	"4",
	"5",
	"3000000",
	"3230101",
	"3230102",
	"3230201",
	"3230202",
	"3230301",
	"3230302",
	"3230401",
	"3230402",
	"3230501",
	"3230502",
	"3230601",
	"3230602",
	"3230701",
	"3230702",
	"3230801",
	"3230802",
	"3230901",
	"3230902",
	"3231001",
	"3231002",
	"3000000",
	"3030101",
	"3030102",
	"3030201",
	"3030202",
	"3031301",
	"3031302",
	"3031401",
	"3031402",
	"3031201",
	"3031202",
	"3030301",
	"3030302",
	"3030401",
	"3030402",
	"3030501",
	"3030502",
	"3030701",
	"3030702",
	"3030801",
	"3030802",
	"3030901",
	"3030902",
	"14012001",
	"14012002",
	"14010901",
	"14010902",
	"3171001",
	"3171002",
	"3171201",
	"3171202",
	"14010501",
	"14010502",
	"14010511",
	"14010512",
	"14010531",
	"14010532",
	"14010521",
	"14010522",
	"14010541",
	"14010542",
	"14010551",
	"14010552",
	"14010561",
	"14010562",
	"14010571",
	"14010572",
	"14010581",
	"14010582",
	"14010611",
	"14010611",
	"14010621",
	"14010621",
	"3000000",
	"3171301",
	"3171302",
	"3171311",
	"3171312",
	"3171321",
	"3171322",
	"3171401",
	"3171402",
	"3170601",
	"3170602",
	"3170701",
	"3170702",
	"3170801",
	"3170802",
	"3170901",
	"3170902",
	"3171501",
	"3171502",
	"3171601",
	"3171602",
	"14010801",
	"14010802",
	"3171701",
	"3171702",
	"3171801",
	"3171802",
	"3171901",
	"3171902",
	"3171911",
	"3171912",
	"3190101",
	"3190102",
	"3190111",
	"3190112",
	"3000000",
	"3190121",
	"3190122",
	"3190201",
	"3190202",
	"3190301",
	"3190302",
	"3190401",
	"3190402",
	"3000000",
	"3075901",
	"3075902",
	"3076001",
	"3076002",
	"3000000",
	"14010691",
	"14010692",
	"14010701",
	"14010702",
	"3180101",
	"3180102",
	"3180201",
	"3180202",
	"3180301",
	"3180302",
	"3180401",
	"3180402",
	"3180501",
	"3180502",
	"3180521",
	"3180522",
	"3180531",
	"3180532",
	"3180541",
	"3180542",
	"3000000",
	"3210301",
	"3210302",
	"3210401",
	"3210402",
	"3210501",
	"3210502",
	"3210601",
	"3210602",
	"3210701",
	"3210702",
	"3210801",
	"3210802",
	"3210901",
	"3210902",
	"3000000",
	"3211001",
	"3211002",
	"3211011",
	"3211012",
	"3211021",
	"3211022",
	"3211051",
	"3211052",
	"3211061",
	"3211062",
	"3211071",
	"3211072",
	"3000000",
	"3100101",
	"3100102",
	"3100201",
	"3100202",
	"3100301",
	"3100302",
	"3100401",
	"3100402",
	"3000000",
	"3211301",
	"3211302",
	"3211311",
	"3211312",
	"3211321",
	"3211322",
	"3211201",
	"3211202",
	"3211211",
	"3211212",
	"3211221",
	"3211222",
	"3000000",
	"3090101",
	"3090102",
	"3090201",
	"3090202",
	"3090301",
	"3090302",
	"3090401",
	"3090402",
	"3000000",
	"3180601",
	"3180602",
	"3180701",
	"3180702",
	"3180801",
	"3180802",
	"3180901",
	"3180902",
	"3000000",
	"3040101",
	"3040102",
	"3040201",
	"3040202",
	"3040301",
	"3040302",
	"3040401",
	"3040402",
	"3040501",
	"3040502",
	"3040601",
	"3040602",
	"3040701",
	"3040702",
	"3040801",
	"3040802",
	"3000000",
	"15011101",
	"15011102",
	"15011201",
	"15011202",
	"15011301",
	"15011302",
	"15011401",
	"15011402",
	"15011501",
	"15011502",
	"15011601",
	"15011602",
);
$tutorials=implode(',', $tutorialArray);

$sql="select count(1) regCount,u.date,ur.appVersion,r.country,r.pf,r.referrer from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid = u.uid inner join $snapshotdb.user_reg ur on r.uid=ur.uid where r.type!=2 and u.date >= $req_date_start and u.date<=$req_date_end and u.banTime!=9223372036854775807 group by date,appVersion,country,pf,referrer;";
$ret = query_infobright_new($client,$sql);
$regArray=array();
foreach( $ret as $value){
	$choose =dau_referrer($value);
	$pf = $choose[0];
	$country = $choose[1];
	$referrer = $choose[2];
	if (getAppVersionNum($value['appVersion'])<$tempAppVer){
		continue;
	}
	$regArray[$value['date']][$country][$pf][$referrer][$value['appVersion']]=$value['regCount'];
}

$sql="select count(distinct(t.uid)) as perTutCount,t.tutorial,u.date,ur.appVersion,r.country,r.pf,r.referrer from $snapshotdb.stat_tutorial t inner join $snapshotdb.userprofile_full u on t.uid = u.uid inner join $snapshotdb.stat_reg r on t.uid = r.uid inner join $snapshotdb.user_reg ur on t.uid = ur.uid where r.type!=2 and u.date >= $req_date_start and u.date<=$req_date_end and u.banTime!=9223372036854775807 and t.tutorial in($tutorials) group by date,tutorial,appVersion,country,pf,referrer;";

$ret = query_infobright_new($client,$sql);
$perTutArray=array();
$tutorialIdArray=array();
foreach( $ret as $value){
	$choose =dau_referrer($value);
	$pf = $choose[0];
	$country = $choose[1];
	$referrer = $choose[2];
	if (getAppVersionNum($value['appVersion'])<$tempAppVer){
		continue;
	}
	$perTutArray[$value['date']][$country][$pf][$referrer][$value['appVersion']][$value['tutorial']]+=$value['perTutCount'];
	if(!in_array($value['tutorial'], $tutorialIdArray)){
		$tutorialIdArray[]=$value['tutorial'];
	}
}
$tutorialIdArray[]=999999999;
$records = array();
foreach ($regArray as $dateKey=>$cValue){
	foreach ($cValue as $countryKey=>$pValue){
		foreach ($pValue as $pfKey=>$rValue){
			foreach($rValue as $referrerKey=>$aValue) {
				foreach ($aValue as $appversionKey => $value) {
					foreach ($tutorialIdArray as $tutId) {
						$one = array();
						$one['date'] = $dateKey;
						$one['country'] = "'{$countryKey}'";
						$one['pf'] = "'{$pfKey}'";
						$one['referrer'] = "'{$referrerKey}'";
						$one['appVersion'] = "'{$appversionKey}'";
						$one['tutorial'] = $tutId;
						if ($tutId == 999999999) {
							$one['regCount'] = intval($regArray[$dateKey][$countryKey][$pfKey][$referrerKey][$appversionKey]);
							$one['perTutCount'] = 0;
						} else {
                            $one['regCount'] = 0;
                            $one['perTutCount'] = intval($perTutArray[$dateKey][$countryKey][$pfKey][$referrerKey][$appversionKey][$tutId]);
						}
						$records[] = $one;
					}
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
	$f = 'sid,'.$f;
	$str = SERVER_ID.','.$str;

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE regCount='.$fieldvalue['regCount'].',perTutCount='.$fieldvalue['perTutCount'].";";
	$insertSql .= " $ondup;";
	
	$db_tbl = "$statdb_allserver.stat_tutorial_pf_country_appVersion_referrer";
	$sql = sprintf($insertSql, $db_tbl);
//	echo $sql."\n";
	query_infobright_new($client,$sql);
}


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

