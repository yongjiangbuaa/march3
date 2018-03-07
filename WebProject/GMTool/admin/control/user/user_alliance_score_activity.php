<?php
!defined('IN_ADMIN') && exit('Access Denied');
$weekList="<option value=''>未选</option>";
for ($i=1;$i<52;$i++){
	$weekList .= "<option value='$i'>第 $i 周</option>";
}

if($_REQUEST['event']=='getTimeByWeek'){
	$time=getStartAndEndDate($_REQUEST['weekNum']);
	$timeStr=implode('|', $time);
	exit($timeStr);
}
$headAlert='';
$userId = $_REQUEST['userId'];
$allianceName = $_REQUEST['allianceName'];
$startTime = strtotime($_REQUEST['startDate'])*1000-28800000;
$endTime = strtotime($_REQUEST['endDate'])*1000+28800000+86400000;
$type = $_REQUEST['type'];

if (isset($_REQUEST['getData'])) {
	    //默认type==1
	    $queryDB = 'alactphase_user_score';
	    $appendWhere = " and au.uid='$userId'";
		if ($type == 2) {
			//查询联盟积分
			$queryDB = 'alactphase_alliance_score';
			//根据allianceName查询得到alliance uid，强制区分大小写
			$allianceSql = "select uid from alliance where binary alliancename='$allianceName';";
			$result = $page->execute($allianceSql, 3);
			$allianceUid = '';
			if(!$result['error'] && $result['ret']['data']){
				$item = $result['ret']['data'][0];
				$allianceUid = $item['uid'];
			}
			$appendWhere = " and au.alliance='$allianceUid'";
		}
		//积分活动详情
		$now = time()*1000;
		if($now<$startTime || $now>$endTime) {
			$queryDB .= "_bak";
		}
		$sql = "SELECT ss.round,ss.id,au.reward,au.score
 			FROM $queryDB au INNER JOIN alliance_score_phase ss on au.phase=ss.uid
 			where ss.beginTime>=$startTime and ss.endTime<=$endTime$appendWhere order by ss.round";
		$result = $page->execute($sql,3);
		$ItemResult = $result['ret']['data'];
		$lang = loadLanguage();
		$clientXml = loadXml('alliance_phase',false);

		if (in_array($_COOKIE['u'],$privilegeArr)) {
			echo $sql.PHP_EOL;
		}
		$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'>
		<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	    $typeArray = array(
			1 => '个人积分',
			2 => '联盟积分',
		);
		$index = array(
			    'type' =>'类型',
				'round'=>'阶段',
		        'id'=>'阶段id',
				'name'=>'阶段名称',
	            'score'=>'积分',
				'reward'=>'奖励(111分别对应奖励3,2,1等级)',
//				'alliance'=>'当时所在联盟id',
		);
		$html .= "<tr class='listTr'>";
		foreach ($index as $key=>$value)
		{
			$html .= "<td>" . $value. "</td>";
		}
		$html .= "</tr>";
		foreach ($ItemResult as $no=>$sqlData)
		{
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($index as $key=>$title){
				if ($key == 'type') {
					$html .= "<td>" . ($typeArray[$type]) . "</td>";
				}
				else if($key=='name') {
					$html .= "<td>" . ($lang[(String)$clientXml[$sqlData['id']]['name']]) . "</td>";
				}else {
					$html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : '0') . "</td>";
				}
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";
	echo $html;
	exit();
}


function getStartAndEndDate($week, $year)
{

	$time = strtotime("1 January $year", time());
	$day = date('w', $time);
	$time += ((7*$week)+1-$day)*24*3600;
	$return[0] = date('Y-m-d', $time);
	$time += 6*24*3600;
	$return[1] = date('Y-m-d', $time);
	return $return;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>