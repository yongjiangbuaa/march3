<?php
!defined('IN_ADMIN') && exit('Access Denied');
$developer = in_array($_COOKIE['u'],$privilegeArr);
$showData=false;
$headAlert='';

$sttt = $_REQUEST['selectServer'];
if (!$sttt){
	$sttt="60-70";
}
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$dbIndex=array(
	'CN',
	'JP',
	'US',
	'KR',
	'RU',
	'TW',
	'GB',
	'FR',
	'DE',
	'ES',
	'BR',
	'TR',
	'SA',
	'HK',
	'AE',
	'AU',
	'TH',
);
//if($_REQUEST['analyze']=='user'){
	$data=array();
	$allUsers=array();
	$sql="select country,type,count(uid) cnt from stat_reg group by country,type;";
	foreach ($selectServerids as $server){
		$server = 's'.$server;
		$ret=$page->executeServer($server, $sql, 3);
		foreach ($ret['ret']['data'] as $row){
			if ($row['type']==0){
				foreach ($dbIndex as $countryVal){
					if ($row['country']==$countryVal){
						$data[$server][$countryVal]+=$row['cnt'];
					}
				}
				$data[$server]['total']+=$row['cnt'];
			}
			$allUsers[$server]+=$row['cnt'];
		}
		if ($data[$server]){
			foreach ($dbIndex as $countryVal){
				$data[$server]['percent'.$countryVal]=intval($data[$server][$countryVal]*10000/$data[$server]['total'])/100;
			}
		}
	}
	
	$client = new Redis();
	$r = $client->connect(GLOBAL_REDIS_SERVER_IP, 6379, 3);//conn 3 sec timeout.
	$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
	
	$countryConfigKey = "COUNTRY_OF_CHOOSE_SERVER";
	$curr_country_arr = $client->hGetAll($countryConfigKey);
	
	$client->close();
	$temp1=explode(';', $serverRatioConf);
	$daoliangArray=array();
	foreach ($temp1 as $val){
		$temp2=explode(':', $val);
		$daoliangArray[]='s'.$temp2[0];
	}
	
	$countryArray=array();
	foreach ($curr_country_arr as $cid => $ccountry) {
		$countryArray['s'.$cid]=$ccountry;
	}
	
	if ($data || $allUsers){
		$showData=true;
		uksort($allUsers,'cmp');
	}else {
		$headAlert='没有查到相关数据';
	}
//}
function cmp($a,$b){
	$a=substr($a,1);
	$b=substr($b,1);
	return $a>$b?-1:1;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
