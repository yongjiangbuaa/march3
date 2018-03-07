<?php
!defined('IN_ADMIN') && exit('Access Denied');
$roundList = '';
$server_list = get_server_list();
if($server_list[0]['ip_inner']=='10.1.16.211') {
	$ip='10.1.16.211:3306';
	$name = 'cok';
	$password = '1234567';
}else{
	$ip='10.41.81.97:3306';
	$name = 'gow';
	$password = 'ZPV48MZH6q9V8oVNtu';
}
$link = mysql_connect($ip, $name, $password);
if (!$link) {
	die('Could not connect: ' . mysql_error());
}
//-------------------------------------------1 获取所有阶段----------------------
$sql1 = "SELECT round from cokdb_alliance_service.alliance_activity_phase GROUP BY round order by round";
$result = mysql_query($sql1,$link);
while($item = mysql_fetch_assoc($result)) {
	$value_round = $item['round'];
	if (in_array($value_round, $roundValueArr)){
		continue;
	}
	$roundValueArr[]=$value_round;
	$roundList .= "<option value='$round'>第 $value_round 阶段</option>";
}
$totalRoundNum = count($item);
$roundValueArr=array();

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
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$sTime=(strtotime($startDate)-86400)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$eTime=(strtotime($endDate)+86400*2)*1000;
//------------------------------------------------2 获取绑定的服--------------
	$server_s =  $_COOKIE['Gserver2'];
	$server_s = str_replace('s','',$server_s);
	$sql2 = "SELECT team_id from cokdb_alliance_service.alliance_activity where start_time between $sTime and $eTime and end_time between $sTime and $eTime GROUP BY team_id ;";
	$result_team = mysql_query($sql2,$link);
	while($team = mysql_fetch_assoc($result_team)) {
		$teams = array();
		$value = $team['team_id'];
		$teams = explode('-',$value);
		if(in_array($server_s, $teams)){
			$html ='当前服所在的联盟组：'. $value.'服';
			break;
		}
	}

	if($_REQUEST['round'] != 'rank'){
		$round = $_REQUEST['round'];
		//-----------------------------------------------3 获取当前阶段的开始时间，结束时间，状态等--------------------
		$sql3 = "SELECT aa.act_uid,aa.status,ap.ready_time,ap.start_time,ap.end_time,pr.rank,pr.alliance_id,pr.alliance_abbr,pr.alliance_name,pr.score,pr.server_id
from cokdb_alliance_service.alliance_activity aa
INNER JOIN cokdb_alliance_service.alliance_activity_phase ap on aa.act_uid=ap.act_uid
INNER JOIN cokdb_alliance_service.alliance_phase_rank pr on ap.phase_uid=pr.phase_uid
where ap.start_time between $sTime and $eTime and ap.end_time between $sTime and $eTime  and aa.team_id = '".$value."' and ap.round = $round order by pr.rank;";
	}elseif($_REQUEST['round'] =='rank'){
		//-----------------------------------------------3 总榜--------------------
		$sql3 = "select * from cokdb_alliance_service.alliance_activity aa INNER JOIN cokdb_alliance_service.alliance_activity_rank ar  on aa.act_uid=ar.act_uid
where aa.start_time between $sTime and $eTime and ((aa.end_time between $sTime and $eTime ) or aa.end_time=0 ) and aa.team_id = '".$value."' order by ar.rank;";
	}
	$results = mysql_query($sql3,$link);
	$index = array(
				'rank'=>'玩家排名',
				'server_id'=>'服',
				'act_uid'=>"活动uid",
				'status'=>'活动状态',
				'ready_time'=>'准备时间',
				'start_time'=>'开始时间',
				'end_time'=>'结束时间',
				'alliance_id'=>'联盟id',
				'alliance_abbr'=>'联盟简称',
				'alliance_name'=>'联盟名称',
				'score'=>'总积分',
		);
		$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'><h3 style='text-align: center'> 积分活动总排名</h3>
				<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$html .= "<tr class='listTr'>";
		foreach ($index as $key=>$value)
		{
			$html .= "<td>" . $value. "</td>";
		}
		$html .= "</tr>";

		while($stauts = mysql_fetch_assoc($results)) {
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			$html .= "<td>" . $stauts['rank']  . "</td>";
			$html .= "<td>" . $stauts['server_id']  . "</td>";
			$html .= "<td>" . $stauts['act_uid']  . "</td>";
			$html .= "<td>" . $stauts['status']  . "</td>";
			$html .= "<td>" . ($stauts['ready_time']>0? $stauts['ready_time']:0)  . "</td>";
			$html .= "<td>" . $stauts['start_time']  . "</td>";
			$html .= "<td>" . $stauts['end_time']  . "</td>";
			$html .= "<td>" . $stauts['alliance_id']  . "</td>";
			$html .= "<td>" . $stauts['alliance_abbr']  . "</td>";
			$html .= "<td>" . $stauts['alliance_name']  . "</td>";
			$html .= "<td>" . $stauts['score']  . "</td>";
		}
	$html .= "</tr>";
	$html .= "</table></div><br/>";//
		echo $html;
		mysql_close($link);
		exit();
}else{
	mysql_close($link);
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