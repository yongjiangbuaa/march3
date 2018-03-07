<?php
!defined('IN_ADMIN') && exit('Access Denied');
$roundList = '';
$sql = "SELECT round from server_score order by round";
$result = $page->execute($sql,3);
$totalRoundNum = count($result['ret']['data']);
$roundValueArr=array();
foreach ($result['ret']['data'] as $item){
	$value = $item['round'];
	if (in_array($value, $roundValueArr)){
		continue;
	}
	$roundValueArr[]=$value;
	$roundList .= "<option value='$value'>第 $value 阶段</option>";
}

$weekList="<option value=''>未选</option>";
for ($i=1;$i<52;$i++){
	$weekList .= "<option value='$i'>第 $i 周</option>";
}

$xishu = '<table><tr>';
foreach ($roundValueArr as $item){
	$value = $item['round'];
	$xishu .= "<td>第 $value 阶段<input class='xishu' type='text' name='xishu_$value' value='1' /></td>";
}
$xishu .= '</tr></table>';
$roundList .= "<option value='rank'>总榜</option>";

if($_REQUEST['event']=='getTimeByWeek'){
	$time=getStartAndEndDate($_REQUEST['weekNum'],date('Y'));
	$timeStr=implode('|', $time);
	exit($timeStr);
}
$headAlert='';
if (isset($_REQUEST['getData'])) {
	if($_REQUEST['round'] != 'rank'){
		$round = $_REQUEST['round'];
	}

	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$sTime=strtotime($startDate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$eTime=(strtotime($endDate)+86400)*1000;

	$sql = "SELECT beginTime,endTime from server_score where beginTime between $sTime and $eTime and endTime between $sTime and $eTime and round = $round";
	$result = $page->execute($sql,3);
	$startTime = $result['ret']['data'][0]['beginTime'];
	$endTime = $result['ret']['data'][0]['endTime'];
	if (in_array($_COOKIE['u'],$privilegeArr)) {
		$html .= $sql.'======';
	}
	if(empty($startTime) || empty($endTime)){
		$html = $sql.'没有开始结束时间';
		echo $html;
		exit();
	}
	if($_REQUEST['type'] == 1){
		if(!empty($_REQUEST['userid'])){
			$userid=$_REQUEST['userid'];
			$wheresql = " us.uid= '{$userid}' and";
		}
		$ymArray=array();
		for ($i=$startTime;$i<=$endTime;){
			$yearMonth=date('Y',$i/1000).'_'.(date('m',$i/1000)-1);
			if (!in_array($yearMonth, $ymArray)){
				$ymArray[]=$yearMonth;
			}
			$i=$i+86400000;
		}

		//积分活动期间登陆过的所有玩家数量
		$tarArr = array();
		$allLogin = 0;
		foreach ($ymArray as $ym){
			$sql = "SELECT COUNT(DISTINCt(uid)) num,castlelevel from stat_login_$ym where time>= $startTime  and time<= $endTime group by castlelevel;";
			$result = $page->execute($sql,3);
			foreach($result['ret']['data'] as $value){
				if($value['castlelevel'] > 0){
					$allLogin += $value['num'];
					$tarArr[(int)$value['castlelevel']]['logNum'] = $value['num'];
					$tarArr[(int)$value['castlelevel']]['level'] = $value['castlelevel'];
				}

			}
		}
		//积分活动详情
		$sql = "SELECT ss.round,ss.id,us.level,us.score
 			FROM user_score_bak us INNER JOIN server_score ss on us.actId=ss.uid
 			where $wheresql ss.beginTime=$startTime and ss.endTime=$endTime and ss.round = $round";
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			$html .= $sql.'=======';
		}
		$result = $page->execute($sql,3);
		$ItemResult = $result['ret']['data'];
		$scoreXML =loadXml('events', false);
		$xmlId = $ItemResult[0]['id'];
		$target = $scoreXML[$xmlId]['target'];
		$LevelArr = explode('|', $target);
		function getTarget($score,$level){
			global $LevelArr;
			$scoreArr = explode(';', $LevelArr[$level-1]);
			foreach ($scoreArr as $key=>$value){
				if($score < $value){
					return $key;
				}
			}
			return 3;
		}
		$TotalResult =  array();
		foreach ($ItemResult as $value){
			if($tarArr[(int)$value['level']]['level'] < 1)
				$tarArr[(int)$value['level']]['level'] = (int)$value['level'];
			$tarArr[(int)$value['level']]['actNum'] ++;
			$TotalResult['actNum'] ++;
			$achieve = getTarget($value['score'],$value['level']);
			if($achieve >0){
				$tarArr[(int)$value['level']]['tar1'] ++;
				$TotalResult['tar1']++;
			}
			if($achieve >1){
				$tarArr[(int)$value['level']]['tar2'] ++;
				$TotalResult['tar2']++;
			}
			if($achieve >2){
				$tarArr[(int)$value['level']]['tar3']++;
				$TotalResult['tar3']++;
			}
		}
		foreach ($tarArr as $key=>$value){
			$tarArr[$key]['notAct'] =  $tarArr[$key]['logNum'] - $tarArr[$key]['actNum'];
			$tarArr[$key]['tar1Ratio'] =$value['actNum'] > 0 ?  round($value['tar1']  * 100 / $value['actNum'],2).'%' : '-';
			$tarArr[$key]['tar2Ratio'] = $value['actNum'] > 0 ?round($value['tar2']   * 100 / $value['actNum'],2).'%' : '-';;
			$tarArr[$key]['tar3Ratio'] =$value['actNum'] > 0 ?round($value['tar3']   * 100 / $value['actNum'],2).'%' : '-';;
		}
		$TotalResult['level'] = '总数';
		$TotalResult['logNum'] = $allLogin;
		$TotalResult['notAct'] = $allLogin - $TotalResult['actNum'];
		$TotalResult['tar1Ratio'] = $TotalResult['actNum'] > 0 ? round($TotalResult['tar1'] * 100 / $TotalResult['actNum'] ,2).'%' : '-';
		$TotalResult['tar2Ratio'] = $TotalResult['actNum'] > 0 ? round($TotalResult['tar2'] * 100 / $TotalResult['actNum'] ,2).'%' : '-';
		$TotalResult['tar3Ratio'] = $TotalResult['actNum'] > 0 ? round($TotalResult['tar3'] * 100 / $TotalResult['actNum'] ,2).'%' : '-';
		ksort($tarArr);
		array_unshift($tarArr, $TotalResult);
		$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'><h3>活动阶段 :{$round}</h3>
		<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$index = array(
			'level'=>'大本等级',
			'logNum'=>'期间登录人数',
			'actNum'=>'参与活动人数',
			'notAct'=>'未参与活动人数',
			'tar1'=>'完成目标1人数',
			'tar1Ratio'=>'完成目标1比例',
			'tar2'=>'完成目标2人数',
			'tar2Ratio'=>'完成目标2比例',
			'tar3'=>'完成目标3人数',
			'tar3Ratio'=>'完成目标3比例',
		);
		$html .= "<tr class='listTr'>";
		foreach ($index as $key=>$value)
		{
			$html .= "<td>" . $value. "</td>";
		}
		$html .= "</tr>";
		foreach ($tarArr as $no=>$sqlData)
		{
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($index as $key=>$title){
				if($key == 'level' || $key == 'tar1Ratio' || $key == 'tar2Ratio' || $key == 'tar3Ratio'){
					$html .= "<td>" . $sqlData[$key]  . "</td>";
				}
				else{
					$html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : '0') . "</td>";
				}
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";//
		echo $html;
		exit();
	}
	if($_REQUEST['type'] == 2){
		/**
		 * when ss.round=6 then  if(us.score>0,us.score,0) *0.05
		when ss.round=7 then  if(us.score>0,us.score,0) *0.18
		 */
		if(!empty($_REQUEST['userid'])){
			$userid=$_REQUEST['userid'];
			$wheresql = " us.uid= '{$userid}' and";
			$wheresql1 = " where us.uid= '{$userid}' ";
		}

		if( $_REQUEST['round'] !='rank'){
			$sql = "SELECT u.name,u.uid,a.alliancename,ss.round,us.score ,us.reward FROM user_score_bak us left JOIN userprofile u on us.uid=u.uid
			left JOIN alliance a on a.uid=u.allianceId   left JOIN server_score ss on ss.uid=us.actId
			 where $wheresql ss.beginTime=$startTime and ss.endTime=$endTime and ss.round= $round  group by us.uid
			ORDER BY score desc limit 100;";
		}
		else{
			$sql = "SELECT u.name,u.uid,a.alliancename,ss.round,
			sum(case ";
			$xishuArr = explode(',', trim( $_REQUEST['xishu'],','));
			foreach ($xishuArr as $key=>$value){
				$num = $key+1;
				$sql .= " when ss.beginTime between $sTime and $eTime and ss.endTime between $sTime and $eTime and ss.round=$num then  if(us.score>0,us.score,0) *$value ";
			}
			$sql .= " end	) score  FROM user_score_bak us left JOIN userprofile u on us.uid=u.uid
			left JOIN alliance a on a.uid=u.allianceId   left JOIN server_score ss on ss.uid=us.actId $wheresql1
			 group by us.uid
			ORDER BY score desc limit 100;";
		}
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			$html .= $sql.'=====';
		}
		$result = $page->execute($sql,3);
		$result = $result['ret']['data'];
		$uidStr = " where us.uid in ('' ";
		foreach ($result as $value){
			$uidStr .= ",'".$value['uid']."'";
		}
		$uidStr .= ') ';
		$index = array(
			'no'=>"玩家排名",
			'name'=>'玩家名称',
			'alliancename'=>'联盟名称',
			'score'=>'总积分',
			'reward'=>'奖励情况',
		);
		if($_REQUEST['round'] =='rank'){
			for($i=1;$i<=$totalRoundNum;$i++){
				$key='tar'.$i;
				$index[$key] = "第 {$i} 阶段";
			}
			$sql = "SELECT us.score,us.uid,ss.round FROM user_score_bak us left JOIN server_score ss on ss.uid=us.actId $uidStr and ss.beginTime between $sTime and $eTime and ss.endTime between $sTime and $eTime;";
			$Inforesult = $page->execute($sql,3);
			$Inforesult = $Inforesult['ret']['data'];
			$tarArr = array();
			foreach ($Inforesult as $value){
				$tarArr[$value['uid']][$value['round']] = $value['score'];
			}
			foreach ($result as $tmp=>$value){
				for($i=1;$i<=$totalRoundNum;$i++){
					$key='tar'.$i;
					$result[$tmp][$key] = $tarArr[$value['uid']][$i];
				}
			}
			if (in_array($_COOKIE['u'],$privilegeArr)) {
				$html .= $sql.'====';
			}
		}

		$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'><h3 style='text-align: center'> 积分活动总排名</h3>
				<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$html .= "<tr class='listTr'>";
		foreach ($index as $key=>$value)
		{
			$html .= "<td>" . $value. "</td>";
		}
		$html .= "</tr>";
		foreach ($result as $no=>$sqlData)
		{
			$sqlData['no'] = ++$no;
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($index as $key=>$title){
				if($key == 'name' || $key == 'alliancename' || $key == 'no'){
					$html .= "<td>" . $sqlData[$key]  . "</td>";
				}
				else{
					$html .= "<td>" . ($sqlData[$key] ? $sqlData[$key] : '0') . "</td>";
				}
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";//

		echo $html;
		exit();
	}
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