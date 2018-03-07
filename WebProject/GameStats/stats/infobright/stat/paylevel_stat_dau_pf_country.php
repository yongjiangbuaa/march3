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

//date,serverid, 平台,country,paylevel ,日活跃(dau),付费的用户数,付费总额
$client = getInfobrightConnect('paylevel_stat_dau_pf_country.php');
if(!$client){
	echo 'mysql error paylevel_stat_dau_pf_country.php'.PHP_EOL;
	return;
}

//这主要是统计 日活跃(不同付费等级人群)
$sql="select l.date,r.pf,r.country,
case
when p.allpay<=0 or p.uid is null then 0
when p.allpay>0 and p.allpay<=5 then 1
when p.allpay>5 and p.allpay<=500 then 2
when p.allpay>500 and p.allpay<=1000 then 3
when p.allpay>1000 and p.allpay<=5000 then 4
when p.allpay>5000 and  p.allpay<=10000 then 5
when p.allpay>10000 and  p.allpay<=20000 then 6
when p.allpay>20000 and  p.allpay<=30000 then 7
when p.allpay>30000 then 8 end as payLevel, count(distinct(l.uid)) dau
from $snapshotdb.userprofile_full u
INNER JOIN $snapshotdb.stat_login_full l on l.uid=u.uid
INNER join (select distinct uid,pf,country from $snapshotdb.stat_reg) r on u.uid=r.uid
left join (select uid,sum(spend) allpay from $snapshotdb.paylog where pf!='iostest' and date<=$req_date_end group by uid) p on p.uid=r.uid
where u.banTime <2422569600000 and u.gmFlag != 1 and l.date >= $req_date_start and l.date <= $req_date_end
group by l.date,r.pf,r.country,payLevel;";


$ret = query_infobright_new($client,$sql);
$regArray=array();
foreach( $ret as $row){
	if(empty($row['pf'])){
		continue;
	}
	$date = $row['date'];

	$country = $row['country'] ? $row['country']:'unknow';
	$regArray[$date][$row['pf']][$country][$row['payLevel']] += $row['dau'];

}
//统计当日的 付费用户数,付费金额  分付费等级(此时付费等级已经是今天付完费的了)
$sql="select pp.date,r.pf,r.country,
case
when p.allpay<=0 or p.uid is null then 0
when p.allpay>0 and p.allpay<=5 then 1
when p.allpay>5 and p.allpay<=500 then 2
when p.allpay>500 and p.allpay<=1000 then 3
when p.allpay>1000 and p.allpay<=5000 then 4
when p.allpay>5000 and  p.allpay<=10000 then 5
when p.allpay>10000 and  p.allpay<=20000 then 6
when p.allpay>20000 and  p.allpay<=30000 then 7
when p.allpay>30000 then 8 end as payLevel,count(DISTINCT pp.uid) users,sum(pp.spend) money
from $snapshotdb.userprofile_full u
INNER join (select distinct uid,pf,country from $snapshotdb.stat_reg) r on u.uid=r.uid
left join (select uid,sum(spend) allpay from $snapshotdb.paylog where pf!='iostest' and date<=$req_date_end group by uid) p on p.uid=r.uid
INNER join $snapshotdb.paylog pp on pp.uid=u.uid
where u.banTime <2422569600000 and u.gmFlag != 1 and pp.date >= $req_date_start and pp.date <= $req_date_end
group by pp.date,r.pf,r.country,payLevel;";


$ret = query_infobright_new($client,$sql);
$payArray=array();
foreach( $ret as $row){
	if(empty($row['pf'])){
		continue;
	}
	$date = $row['date'];

	$country = $row['country'] ? $row['country']:'unknow';
	$payArray[$date][$row['pf']][$country][$row['payLevel']]['users'] += $row['users'];
	$payArray[$date][$row['pf']][$country][$row['payLevel']]['money'] += $row['money'];
}


$records = array();
foreach ($regArray as $dateKey=>$pfCountryDau){
	foreach ($pfCountryDau as $pfKey =>$countrypaylvDau){
		foreach ($countrypaylvDau as $countryKey=>$paydau){
			foreach($paydau as $paylevel=>$dau) {
				$one = array();
				$one['date'] = $dateKey;
				$one['pf'] = "'{$pfKey}'";
				$one['country'] = "'{$countryKey}'";
				$one['paylevel'] = intval($paylevel);
                $one['dau'] = intval($dau);

                $one['users'] = intval($payArray[$dateKey][$pfKey][$countryKey][$paylevel]['users']);
				$one['money'] = intval($payArray[$dateKey][$pfKey][$countryKey][$paylevel]['money']);

				$records[] = $one;
			}
		}
	}
}


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

		$db_tbl = "$statdb_allserver.pay_userdata_dau";
		$sql = sprintf($insertSql, $db_tbl);
		//echo $sql."\n";
		query_infobright_new($client,$sql);
	}
