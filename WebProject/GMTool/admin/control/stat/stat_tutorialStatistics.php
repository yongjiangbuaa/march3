<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*1);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;

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
if (!$_REQUEST['appVersionName']) {
	$appVersion = 'ALL';
}else{
	$appVersion = $_REQUEST['appVersionName'];
}
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql = " where date >=$sDdate and date < $eDate ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and pf='$currPf' ";
	}
	if($appVersion&&$appVersion!='ALL'){
		$whereSql .=" and appVersion='$appVersion' ";
	}else{
		$allversion=true;
	}
	if($currReferrer&&$currReferrer!='ALL'){
		$whereSql .=" and referrer='$currReferrer' ";
	}
		$sids = implode(',', $selectServerids);
		$whereSql .= " and sid in ($sids) ";
	if($allversion){
		$sql = "select tutorial,sum(regCount) regCount, sum(perTutCount) tutCount from stat_allserver.stat_tutorial_pf_country_appVersion_referrer $whereSql group by tutorial ;";
	}else {
		$sql = "select appVersion,tutorial,sum(regCount) regCount, sum(perTutCount) tutCount from stat_allserver.stat_tutorial_pf_country_appVersion_referrer $whereSql group by appVersion ,tutorial  ORDER BY appVersion DESC ;";
	}

	$result = query_infobright($sql);
//	$qintime2 = date('Ymd--H:i:s',time());
//	file_put_contents('/tmp/qinbin_test' ,"-2---$qintime2-----".PHP_EOL,FILE_APPEND);

	$ab=$abCount=array();
	foreach ( $result ['ret'] ['data']  as $row){
		if($allversion){
			$row['appVersion'] = 'ALL';
		}
		$abCount[$row['appVersion']] += $row['regCount'];
		if($row ['tutorial']!=999999999){
			$event[$row['appVersion']]['IK2'.$row ['tutorial']] += $row ['tutCount'];
		}
		if(!in_array($row['appVersion'], $ab)){
			$ab[] = $row['appVersion'];
		}
	}
//	usort($ab,'_versionCompare');
//	$qintime2 = date('Ymd--H:i:s',time());
//	file_put_contents('/tmp/qinbin_test' ,"--3--$qintime2-----".PHP_EOL,FILE_APPEND);

	$eventStr = $sortArr =$showDesc= array();
	$tutorialquestItems = loadXml('mark','Market');

//	$qintime2 = date('Ymd--H:i:s',time());
//	file_put_contents('/tmp/qinbin_test' ,"--4--$qintime2-----".PHP_EOL,FILE_APPEND);

	foreach ($tutorialquestItems as $tutorialquestItem){
		$order = (int)$tutorialquestItem['id'];
		$order2 = (int)$tutorialquestItem['id'];
		if($order2){
			$sortArr[$order2] = array('id'=>(string)$tutorialquestItem['item']
					,'Explanation'=>(string)$tutorialquestItem['doing'] .' '. (string)$tutorialquestItem['what']
					,'id_quest'=>(string)$tutorialquestItem['id_quest']
					,'name_quest'=>(string)$tutorialquestItem['name_quest']
			);
		}
		if($order){
			$showDesc['IK2'.(string)$tutorialquestItem['item']] = 1;
		}
	}
	ksort($sortArr);

	$i = 0;
	foreach ($sortArr as $sortData){
		$eventStr[] = array('id'=>$sortData['id'],'Explanation'=>$sortData['Explanation'],'name_quest'=>$sortData['name_quest'],'id_quest'=>$sortData['id_quest']);
		if($i > 0)
			$eventStr[$i-1]['next'] = $sortData['id'];
		$i++;
	}
	if (in_array($_COOKIE['u'],$privilegeArr)) {
		$html .=$sql;
	}

	$html .= "<br /><table class='listTable' style='text-align:center'><thead><th>ID</th><th>说明</th>";
	foreach ($ab as $abtest){
		$html .="<th></th><th>版本号</th><th>注册人数</th><th>通过人数</th><th>保留率</th><th>流失人数</th><th>流失比例</th>";
	}
	$lastCount=array();
	foreach ($ab as $abtest){
		$lastCount[$abtest] = $abCount[$abtest];
		$lastRate[$abtest] = 100;
	}
	foreach ($eventStr as $info){
		$id = $info['id'];
		$html .= "<tbody><tr class='listTr'>";
		$next = $info['next'];
		$html .= "<td>$id</td>";
		$html .= "<td>{$info['Explanation']}</td>";
		foreach ($ab as $abtest){
			$abAUser = $abCount[$abtest];
			$abUser = $event[$abtest]['IK2'.$id];
			$countRed = $rateRed = false;
			$countDesc=0;
			if($abAUser){
				$rate = floor ( $abUser * 10000 / $abAUser ) / 100;
				if($showDesc['IK2'.$id]){
					$rateDesc = floor ( ($lastCount[$abtest] - $abUser) * 10000 / $lastCount[$abtest] ) / 100;
					if($rateDesc > 1){
						$rateRed = true;
					}
					if($abUser > $lastCount[$abtest]){
						$lastCount[$abtest] = $abUser;
						$countRed = true;
					}
					if($rateDesc > 0 && $rateDesc < 99){
						$countDesc = $lastCount[$abtest] - $abUser;
						$lastCount[$abtest] = $abUser;
						$lastRate[$abtest] = $rate;
					}else{
						$rateDesc = 0;
						$countDesc = 0;
					}
					$lastStep[$abtest] = $abUser;
				}
			}
			else{
				$rate = 0;
			}
			if($countRed){
				$abUser = "<font color='red'>$abUser</font>";
			}
			if($rateRed){
				$rateDesc = "<font color='red'>$rateDesc</font>";
			}
			$html .= "<td></td><td><font color='blue'>$abtest</font></td><td>$abAUser</td><td>$abUser</td><td>$rate%</td>";
			if($showDesc['IK2'.$id])
				$html .= "<td>$countDesc</td><td>$rateDesc%</td>";
			else
				$html .= "<td></td><td></td>";
		}
		$html .= "</tr></tbody>";
	}
	$html .= "</table>";
	//看国家的注册与打点不同
//	select country,sum(regCount) regCount, sum(perTutCount) tutCount from stat_allserver.stat_tutorial_pf_country_appVersion_referrer where date >=20160825 and date < 20160826 and pf='AppStore' and sid in (75) and tutorial in (5,999999999) group by country having regCount<tutCount;
//看版本的注册与打点不同
//	select appversion,sum(regCount) regCount, sum(perTutCount) tutCount from stat_allserver.stat_tutorial_pf_country_appVersion_referrer where date >=20160825 and date < 20160826 and sid in (75) and tutorial in(5,999999999) group by appversion;
}
function _versionCompare($a,$b) {
	if(version_compare($a,$b) == 0) return 0;
	elseif(version_compare($a,$b) > 0) return -1;
	else return 1;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>