<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
if ($_REQUEST ['user'])
	$user = $_REQUEST ['user'];
if (! $_REQUEST ['end'])
	$end = date ( "Y-m-d 23:59:59", time () );
if (!$_REQUEST['country']) {
	$country = 'ALL';
}else{
	$country = $_REQUEST['country'];
}
if (!$_REQUEST['pf']) {
	$pf = 'ALL';
}else{
	$pf = $_REQUEST['pf'];
}
if (!$_REQUEST['referrer']) {
	$currReferrer = 'ALL';
}else{
	$currReferrer = $_REQUEST['referrer'];
}
if (!$_REQUEST['zone']) {
	$appVersion = 'ALL';
}else{
	$appVersion = $_REQUEST['zone'];
}
$notNeddArrId=array(3000000,3073601,3031201,3030801,3171301,14010801,3170901,14010201,3190201,3076101);
if ($_REQUEST ['analyze'] == 'platform') {
	$country=$_REQUEST['country'];
	$pf=$_REQUEST['pf'];
	$whereSql='';
	if($country && $country!='ALL'){
		$whereSql.=" and r.country='$country' ";
	}
	if($pf && $pf!='ALL'){
		$whereSql.=" and r.pf='$pf' ";
	}
	$referrerSql = "";

	if($currReferrer&&$currReferrer!='ALL'){
		if($currReferrer=='nature') {
			$referrerSql .= " and (r.referrer is null or r.referrer='' or r.referrer='Organic') ";
		}else if($currReferrer=='facebook'){
			$referrerSql .= " and (r.referrer='facebook' or r.referrer like '%app%')";
		}else if($currReferrer=='Unknown'){
			$referrerSql .=" and (r.referrer!='facebook' and r.referrer != 'nature' and r.referrer != 'adwords' and r.referrer != 'yeahmobi' and r.referrer != 'ndb mobi' and r.referrer not like '%app%' and r.referrer is not null and r.referrer!='' and r.referrer !='Organic')  ";
		}
		else{
			$referrerSql .=" and (r.referrer = '$currReferrer')  ";
		}
	}
	$start_date = $_REQUEST['start'] ? strtotime($_REQUEST ['start']) : 0;
	$end_date = $_REQUEST['end'] ? strtotime($_REQUEST ['end']) : 0;
	$sDdate= date('Ymd',$start_date);
	$eDdate= date('Ymd',$end_date);
	$start = strtotime($sDdate)*1000;
	$end = strtotime($eDdate)*1000;
	if($appVersion&&$appVersion!='ALL'){
		$appVersionSql = " and ur.appVersion = '$appVersion'";
	}
	if ($_REQUEST['organicNums']==1 && (substr($currentServer, 1)>=416 && substr($currentServer, 1)<=445)){
//		$regSql = "select count(1) DataCount,ur.appVersion from stat_reg r inner join user_reg ur on r.uid = ur.uid inner join userprofile u on r.uid=u.uid inner join stat_organic so on u.gaid=so.gaid where r.type!=2 and r.time > $start and r.time < $end $whereSql $appVersionSql and u.banTime!=9223372036854775807 group by ur.appVersion";
		$regSql = "select count(1) DataCount,ur.appVersion from stat_reg r inner join user_reg ur on r.uid = ur.uid inner join userprofile u on r.uid=u.uid inner join stat_organic so on u.gaid=so.gaid where r.type!=2 and r.time > $start and r.time < $end $whereSql $appVersionSql group by ur.appVersion DESC ";
	}else {
		//$regSql = "select count(1) DataCount,u.appVersion from stat_reg r inner join userprofile u on r.uid = u.uid where r.time > $start and r.time < $end $whereSql $appVersionSql group by u.appVersion";
//		$regSql = "select count(r.uid) DataCount,ur.appVersion from stat_reg r inner join user_reg ur on r.uid=ur.uid inner join userprofile u on r.uid=u.uid where r.type!=2 and r.time > $start and r.time < $end $whereSql $appVersionSql and u.banTime!=9223372036854775807 group by ur.appVersion;";
		$regSql = "select count(r.uid) DataCount,ur.appVersion from stat_reg r inner join user_reg ur on r.uid=ur.uid inner join userprofile u on r.uid=u.uid where r.type!=2 and r.time > $start and r.time < $end $whereSql $appVersionSql $referrerSql  group by ur.appVersion DESC ;";
	}
	$result = $page->execute($regSql,3);
//	$html .= json_encode($result);

	foreach ( $result ['ret'] ['data']  as $row){
		$abCount[$row['appVersion']] = $row['DataCount'];
		$count += $row['DataCount'];
		$ab[] = $row['appVersion'];
	}
	usort($ab,'_versionCompare');
	$eventStr = $sortArr = array();
	$tutorialquestItems = loadXml('mark','Market');
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
	//$i = 0;
	foreach ($sortArr as $sortData){
		$eventStr[] = array('id'=>$sortData['id'],'Explanation'=>$sortData['Explanation'],'name_quest'=>$sortData['name_quest'],'id_quest'=>$sortData['id_quest']);
		//if($i > 0)
		//	$eventStr[$i-1]['next'] = $sortData['id'];
		//$i++;
	}
	$i=0;
	foreach ( $eventStr as $value ) {
		$index = $value['id'];
		if ($sql == '')
			$sql .= '(' . $index;
		else
			$sql .= ',' . $index;
		if($i < count($eventStr)-1) {
			if(in_array($index,$notNeddArrId)){
				$eventStr[$i + 1]['pre'] = $eventStr[$i - 1]['id'];
			}else{
				$eventStr[$i + 1]['pre'] = $index;
			}
		}
		$i++;
	}
	$sql .= ')';
	if ($_REQUEST['organicNums']==1 && (substr($currentServer, 1)>=416 && substr($currentServer, 1)<=445)){
//		$tutorialSql = "select count(distinct(t.uid)) as total,t.tutorial,ur.appVersion from stat_tutorial_v2 t inner join stat_reg r on t.uid = r.uid inner join user_reg ur on t.uid = ur.uid inner join userprofile u on r.uid=u.uid inner join stat_organic so on u.gaid=so.gaid where r.type!=2 and r.time > $start and r.time < $end and t.time >= $start and t.time < $end and tutorial in $sql $whereSql $appVersionSql and u.banTime!=9223372036854775807 group by tutorial,ur.appVersion";
		$tutorialSql = "select count(distinct(t.uid)) as total,t.tutorial,ur.appVersion from stat_tutorial_v2 t inner join stat_reg r on t.uid = r.uid inner join user_reg ur on t.uid = ur.uid inner join userprofile u on r.uid=u.uid inner join stat_organic so on u.gaid=so.gaid where r.type!=2 and r.time > $start and r.time < $end and t.time >= $start and t.time < $end and tutorial in $sql $whereSql $appVersionSql  group by tutorial,ur.appVersion";
	}else {
		//$tutorialSql = "select count(distinct(t.uid)) as total,tutorial,u.appVersion from stat_tutorial_v2 t inner join userprofile u on t.uid = u.uid inner join stat_reg r on t.uid = r.uid where r.time > $start and r.time < $end and tutorial in $sql $whereSql $appVersionSql group by tutorial,u.appVersion";

		$tutorialSql = "select count(distinct(t.uid)) as total,t.tutorial,ur.appVersion from stat_tutorial_v2 t inner join stat_reg r on t.uid = r.uid inner join user_reg ur on t.uid = ur.uid inner join userprofile u on r.uid=u.uid where r.type!=2 and r.time >= $start and r.time < $end and t.time >= $start and t.time < $end and t.tutorial in $sql $whereSql $appVersionSql $referrerSql and u.banTime!=9223372036854775807 group by t.tutorial,ur.appVersion";
//		$referrerSql = "";

//		if($currReferrer&&$currReferrer!='ALL'){
//			$referrerSql  =" and r.referrer='$currReferrer'";
//		}
//		if($appVersion&&$appVersion!='ALL'){
//			$appVersionSql = " and r.appVersion = '$appVersion'";
//		}
//		$tutorialSql = "select appVersion,tutorial,sum(perTutCount) total from stat_allserver.stat_tutorial_pf_country_appVersion_referrer r where r.date >= $sDdate and r.date < $eDdate and r.sid=".substr($currentServer, 1)." $whereSql $appVersionSql $referrerSql group by appVersion,tutorial;";
	}
//	$result = query_infobright($tutorialSql);
	//file_put_contents("/data/log/nginx/tutorialSql.log", $tutorialSql);
	$result = $page->execute($tutorialSql,3);
	$tutorialFight = $result ['ret'] ['data'];
	foreach ( $tutorialFight as $curRow ) {
		$event[$curRow['appVersion']]['IK2'.$curRow ['tutorial']] += $curRow ['total'];
	}
	$html .= $regSql."<br/>";
	$html .= $tutorialSql;
	$html .= "<br /><table class='listTable' style='text-align:center'><thead><th>ID</th><th>说明</th>";
	foreach ($ab as $abtest){
		$html .="<th></th><th>版本号</th><th>注册人数</th><th>通过人数</th><th>保留率</th><th>流失人数</th><th>流失比例</th>";
	}
	foreach ($ab as $abtest){
		$lastCount[$abtest] = $abCount[$abtest];
		$lastRate[$abtest] = 100;
	}
	foreach ($eventStr as $info){
		$id = $info['id'];
		$html .= "<tbody><tr class='listTr'>";
		/*
		$next = $info['next']; 
		if($next)
			$html .= "<td><a href='#show2' onclick=getLostUser('$id','$next')>" . $id . "</a></td>";
		else
			$html .= "<td>$id</td>";
		*/
		$pre = $info['pre'];
		if($pre && !in_array($id,$notNeddArrId))
			$html .= "<td><a href='#show2' onclick=getLostUser('$pre','$id')>" . $id . "</a></td>";
		else
			$html .= "<td>$id</td>";
		$html .= "<td>{$info['Explanation']}</td>";
		foreach ($ab as $abtest){
			$abAUser = $abCount[$abtest];
			$abUser = $event[$abtest]['IK2'.$id];
			$countRed = $rateRed = false;
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
	echo $html;
	exit ();
}
if ($_REQUEST ['analyze'] == 'lost') {
	$country=$_REQUEST['country'];
	$pf=$_REQUEST['pf'];
	$whereSql='';
	if($country && $country!='ALL'){
		$whereSql.=" and r.country='$country' ";
	}
	if($pf && $pf!='ALL'){
		$whereSql.=" and r.pf='$pf' ";
	}
	if (!$_REQUEST['zone']) {
		$appVersion = 'ALL';
	}else{
		$appVersion = $_REQUEST['zone'];
	}
	$finish = $_REQUEST['finish'];
	$notFinish = $_REQUEST['notFinish'];
	$start = $_REQUEST['start'] ? strtotime($_REQUEST ['start'])*1000 : 0;
	$end = $_REQUEST['end'] ? strtotime($_REQUEST ['end'])*1000 : 0;
	if($appVersion&&$appVersion!='ALL'){
		$appVersionSql = " and r.appVersion = '$appVersion'";
	}
	$sql = "select u.* from (select u.* from stat_reg r inner join userprofile u on r.uid = u.uid where r.type!=2 and r.time > $start and r.time < $end $whereSql $appVersionSql) u  inner join stat_tutorial_v2 t1 on t1.uid = u.uid and t1.tutorial = $finish left join stat_tutorial_v2 t2 on t2.uid = u.uid and t2.tutorial = $notFinish where t2.uid is null";
	//file_put_contents("/data/log/nginx/lostsql.log", $sql);
	$result = $page->execute($sql,3);
	echo "<br />";
	echo "流失节点：完成$finish  未完成$notFinish";
	echo $appVersion?"版本号$appVersion":"";
	echo "sql $sql";
	//玩家当前版本号,玩家ID,玩家名字,当前等级
	$nameLink['uid'] = 'UID';
	//$nameLink['name'] = '游戏昵称';
	$nameLink['gmail'] = '邮箱';
	$nameLink['level'] = '当前等级';
	$nameLink['appVersion'] = '玩家版本号';
	foreach ($result['ret']['data'] as $curRow){
		$yIndex = $curRow['uid'];
		$eventAll[$yIndex]['uid'] = $curRow['uid'];
		//$eventAll[$yIndex]['name'] = $curRow['name'];
		$eventAll[$yIndex]['gmail'] = $curRow['gmail'];
		$eventAll[$yIndex]['level'] = $curRow['level'];
		$eventAll[$yIndex]['appVersion'] = $curRow['appVersion'];
	}
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
}
function getTutorialGroup($langItems, $id, &$sortArr, &$indexArr, &$index) {
	for($i = 0; $i < 10; $i ++) {
		$itemId = $id + $i;
		if ($langItems [$itemId]) {
			$sortArr [$index] = $itemId;
			$indexArr [$itemId] = $index ++;
		}
	}
}
function getTutorialGroupOld($langItems, $id, &$sortArr, &$indexArr, &$index) {
	for($i = 0; $i < 10; $i ++) {
		$itemId = $id + $i;
		if ($langItems [$itemId]) {
			$sortArr [$index] = $itemId;
			$indexArr [$itemId] = $index ++;
		}
	}
}
// 获得第几行第几列的值，step表示每列个数
function getRowData($data, $row, $line, $step) {
	$i = 1;
	foreach ( $data as $key => $value ) {
		if ($i ++ >= $row + ($line - 1) * $step)
			return $value;
	}
}
function _versionCompare($a,$b) {
	if(version_compare($a,$b) == 0) return 0;
	elseif(version_compare($a,$b) > 0) return -1;
	else return 1;
}

include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>
