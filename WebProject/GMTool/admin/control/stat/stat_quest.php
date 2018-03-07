<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
if ($_REQUEST ['user'])
	$user = $_REQUEST ['user'];
if (! $_REQUEST ['end'])
	$end = date ( "Y-m-d 23:59:59", time () );
if ($_REQUEST ['analyze'] == 'platform') {
	$start = $_REQUEST['start'] ? strtotime($_REQUEST ['start'])*1000 : 0;
	$end = $_REQUEST['end'] ? strtotime($_REQUEST ['end'])*1000 : 0;
	if($_REQUEST['levelMin'] != 1 || $_REQUEST['levelMax'] != 99)
		$ubWhere =  "inner join user_building ub on ub.uid = u.uid and ub.itemId = 400000 where ub.level >=".$_REQUEST['levelMin']." and ub.level <=".$_REQUEST['levelMax']." and ";
	else
		$ubWhere =  "where ";
	if ($_REQUEST['senttd']) {
		$types = substr($_REQUEST['senttd'],0,strlen($_REQUEST['senttd'])-1);
		$ubWhere .= " r.type in ($types) and ";
	}
	$appVersion = "";
	if($_REQUEST['zone'])
		$appVersion = " and u.appVersion = '".$_REQUEST['zone']."'";
	$regSql = "select count(1) DataCount,u.appVersion from stat_reg r inner join user_reg u on r.uid = u.uid inner join userprofile p on r.uid=p.uid "
			.$ubWhere
			."r.time > $start and r.time < $end $appVersion group by u.appVersion";
	$result = $page->execute($regSql,3);
	foreach ( $result ['ret'] ['data']  as $row){
		$abCount[$row['appVersion']] = $row['DataCount'];
		$ab[] = $row['appVersion'];
	}
	$eventStr = $sortArr = array();
// 	$questXml = loadXml('quest');
// 	$questClientXml = loadXml('database.local','quest');
// 	$lang = loadLanguage();
// 	foreach($questXml as $questId=>$questInfo){
// 		$questLogId = (int)$questInfo['id'];
// 		$questName = (int)$questClientXml[$questLogId]['name'];
// 		$targetName = (int)$questClientXml[$questLogId]['target'];
// 		$targetPara = (int)$questClientXml[$questLogId]['para2'];
// 		$desc = strreplace($lang[$targetName],$targetPara);
// 		$sortArr[$questLogId] = array('id'=>$questLogId,'str'=>$lang[$questName],'desc'=>$desc);
// 	}

	$questMarkXmls = loadXml('quest_mark');
	//任务顺序
	foreach ($questMarkXmls as $questMarkXml){
		$order = (int)$questMarkXml['order'];
		$sortArr[$order] = array(
				'order'=>$order
				,'info'=>(string)$questMarkXml['quest']
				,'questId'=>(string)$questMarkXml['id']
		);
		$showDesc[$order] = 1;
	}
	ksort($sortArr);
	$sql = '';
	foreach ( $sortArr as $value ) {
		$index = $value['questId'];
		if ($sql == '')
			$sql .= '(' . $index;
		else
			$sql .= ',' . $index;
	}
	$sql .= ')';
	//从logstat读取
// 	$sql = "select count(*) as total,param1 as questId from logstat l inner join (select * from stat_reg where time > $start and time < $end) r on r.uid = l.user where l.type = {$gameDataType['QUEST_REWARD']} group by param1 order by param1 asc";
	//从user_task读取
	$sql = "select count(*) as total,t.id as questId,u.appVersion from user_task t inner join user_reg u on u.uid = t.uid inner join stat_reg r on r.uid = u.uid inner join userprofile p on r.uid=p.uid $ubWhere r.time >= $start and r.time < $end $appVersion and t.id in $sql and t.state>0 GROUP BY questId,u.appVersion;";
	$result = $page->execute($sql,3);
	echo $regSql;
	$questDetail = $result ['ret'] ['data'];
	foreach ( $questDetail as $curRow ) {
		$event[$curRow['appVersion']]['id'.$curRow ['questId']] += $curRow ['total'];
// 		$sortArr[$curRow ['questId']]['on'] = 1;
	}
	$html .= "<br /><table class='listTable' style='text-align:center'><thead><th>任务ID</th><th>任务说明</th>";
	foreach ($ab as $abtest){
		$html .="<th></th><th>分组</th><th>注册人数</th><th>完成任务人数(非领奖)</th><th>完成比例</th>";
		// "<th>流失人数</th><th>流失比例</th>";
	}
	foreach ($ab as $abtest){
		$lastCount[$abtest] = $abCount[$abtest];
		$lastRate[$abtest] = 100;
	}
	$maxRate = 99;
	foreach ($sortArr as $info){
		$id = $info['questId'];
		$html .= "<tbody><tr class='listTr'>";
		$html .= "<td>$id</td>";
		$html .= "<td>{$info['info']}</td>";
		foreach ($ab as $abtest){
			$abAUser = $abCount[$abtest];
			$abUser = $event[$abtest]['id'.$id];
			$countRed = $rateRed = false;
			if($abAUser){
				$rate = floor ( $abUser * 10000 / $abAUser ) / 100;
				if($showDesc['id'.$id]){
					$rateDesc = floor ( ($lastCount[$abtest] - $abUser) * 10000 / $lastCount[$abtest] ) / 100;
					if($rateDesc > 1){
						$rateRed = true;
					}
					if($abUser > $lastCount[$abtest]){
						$lastCount[$abtest] = $abUser;
						$countRed = true;
					}
					if($rateDesc > 0 && $rateDesc < $maxRate){ 
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
			// if($showDesc['id'.$id])
			// 	$html .= "<td>$countDesc</td><td>$rateDesc%</td>";
			// else
			// 	$html .= "<td></td><td></td>";
		}
		$html .= "</tr></tbody>";
	}
	$html .= "</table>";
	echo $html;
	exit ();
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
include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>
