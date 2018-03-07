<?php 
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
$s = microtime(true);
// **************
$records = array();
$client = getInfobrightConnect('pay_ratio_analyze_pf_country.php');
if(!$client){
	echo 'mysql error pay_ratio_analyze_pf_country.php'.PHP_EOL;
	return;
}
//first day pay user
$sql = "select count(DISTINCT p.uid) as firstDayPay, r.date as date, r.pf as pf, r.country as country from $snapshotdb.paylog p
left join $snapshotdb.stat_reg r on p.uid=r.uid
where p.date=r.date and p.date between $req_date_start and $req_date_end and p.pf!='iostest'
group by date,pf,country;";
$Result = query_infobright_new($client,$sql);
$firstPay=array();
foreach ($Result as $temp){
	if($temp['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$temp['pf'];
		if($temp['country']==null){
			$country = "Unknown";
		}else{
			$country = $temp['country'];
		}
	}
	$firstPay[$temp['date']][$pf][$country] += $temp['firstDayPay'];
}

// new reg device day
$sql="select r.date as date,r.pf as pf,r.country as country,count(distinct(u.deviceId)) as regDevice from $snapshotdb.stat_reg r, $snapshotdb.userprofile u where r.uid = u.uid and r.date between $req_date_start and $req_date_end and type = 0 group by date,pf,country;";
$result = query_infobright_new($client,$sql);
$data=array();
foreach ($result as $temp){
	if($temp['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$temp['pf'];
		if($temp['country']==null){
			$country = "Unknown";
		}else{
			$country = $temp['country'];
		}
	}
	$data[$temp['date']][$pf][$country]['regDevice'] += $temp['regDevice'];
}

//old pay user dau
$sql = "select l.date as date,count(distinct(l.uid)) as oldPayDAU,l.pf as pf,l.country as country from $snapshotdb.stat_login_full l,$snapshotdb.paylog p where l.uid = p.uid and p.pf!='iostest' and p.date < $req_date_start and l.date between $req_date_start and $req_date_end group by date,pf,country;";
$result = query_infobright_new($client,$sql);
foreach ($result as $temp){
        if($temp['pf']==null){
                $pf='roll';
                $country='roll';
        }else{
                $pf=$temp['pf'];
                if($temp['country']==null){
                        $country = "Unknown";
                }else{
                        $country = $temp['country'];
                }
        }
        $data[$temp['date']][$pf][$country]['oldPayDAU'] += $temp['oldPayDAU'];
}

// new total pay
$sql = "select p.uid as uid,min(p.date) as date,sum(p.spend) as newTotalPay,r.pf,r.country from $snapshotdb.paylog p left join $snapshotdb.stat_reg r on p.uid = r.uid where p.pf!='iostest' group by uid having date between $req_date_start and $req_date_end";
$result = query_infobright_new($client,$sql);
foreach ($result as $temp){
        if($temp['pf']==null){
                $pf='roll';
                $country='roll';
        }else{
                $pf=$temp['pf'];
                if($temp['country']==null){
                        $country = "Unknown";
                }else{
                        $country = $temp['country'];
                }
        }
        $tempData[$temp['date']][$pf][$country]['newTotalPay'] += $temp['newTotalPay'];
}
$DAUSql = "select l.date as date,r.pf as pf, r.country as country, count(distinct(l.uid)) as total from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date between $req_date_start and $req_date_end GROUP BY date,pf,country;";
$DAUResult = query_infobright_new($client,$DAUSql);
$tempDau=array();
foreach ($DAUResult as $temp){
        if($temp['pf']==null){
                $pf='roll';
                $country='roll';
        }else{
                $pf=$temp['pf'];
                if($temp['country']==null){
                        $country = "Unknown";
                }else{
                        $country = $temp['country'];
                }
        }
        $tempDau[$temp['date']][$pf][$country] += $temp['total'];
}


foreach ($tempDau as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$value){
			$one = array();
			$one['date']=$dateKey;
			$one['pf']="'$pfKey'";
			$one['country']="'$countryKey'";
			$one['dau']=intval($value);
			$one['newTotalPay']=intval($tempData[$dateKey][$pfKey][$countryKey]['newTotalPay']);
			$one['firstDayPay']=intval($firstPay[$dateKey][$pfKey][$countryKey]);
			$one['regDevice']=intval($data[$dateKey][$pfKey][$countryKey]['regDevice']);
			$one['oldPayDAU']=intval($data[$dateKey][$pfKey][$countryKey]['oldPayDAU']);
			$records[] = $one;
		}
	}
}
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
	$db_tbl = "$statdb_allserver.pay_ratio_analyze_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}


