<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];


$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time()+86400);
$battleType = array('战力PK','FaceBook点赞');
foreach ($battleType as $key=>$value){
	$options .= "<option value='$key'>$value</option>";
}
		$rediskey = 'facebook.like.chat';
		$sid = $_COOKIE['Gserver2'];
		if($sid != null){
			$sid_num = substr($sid,1);
			if($sid_num != null){
				$inner_ip = get_server_ip_inner($sid_num);
			}
		}
		if($inner_ip != null){
			$client = new Redis();
			$client->connect ($inner_ip);
			$like_count = $client->hLen($rediskey );
		}
if ($_REQUEST['analyze'] == 'platform') {
	$start = $_REQUEST['start'] ? strtotime($_REQUEST['start']) * 1000 : strtotime($start) * 1000;
	$end = $_REQUEST['end'] ? strtotime($_REQUEST['end']) * 1000 : strtotime($end) * 1000;
	$wheretime = " and l.timeStamp >= $start and l.timeStamp <= $end ";
	$monthArr = monthList($start/1000,$end/1000);
	$sids = implode(',', $selectServerids);
	$whereSql=" and server_id in ($sids) ";
	if ($_REQUEST['battleType'] == 0) {
		foreach ($monthArr as $i) {
		$db_start = 'coklog_function.function_log_' . $i;
		$sql = "select l.date,l.int_data1 level,sum(case when l.type=1 then 1 else 0 end) type1,sum(case when l.type=2 then 1 else 0 end) type2, sum(case when l.type=3 then 1 else 0 end) type3, sum(case when l.type=4 then 1 else 0 end) type4
				from $db_start l where l.`category`=2 $wheretime $whereSql group by l.date,l.int_data1";
		$result_stats = query_infobright($sql);
		if($result_stats['ret']['data'][0]['date']==null){
			continue;
		}
		$con[] = $result_stats['ret']['data'];
	}
		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date' => 'date', 'level' => 'level', 'type1' => 'accept', 'type2' => '点击关闭', 'type3' => '到时间关闭', 'type4' => '重复关闭');
		$html .= "<tr class='listTr'>";
		foreach ($_index as $key => $value) {
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
				$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($con as $no => $Data) {
				foreach ($Data as $sqlData) {
					foreach ($_index as $key => $title) {
						$value = $sqlData[$key];
						$html .= "<td>" . $value . "</td>";
					}
					$html .= "</tr>";
				}
			}
		$html .= "</table></div><br/>";
	}
	if ($_REQUEST['battleType'] == 1) {
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_fb = "select date, count(1) as num from $db_start l where category =10 $wheretime $whereSql group by date;";
			$result_stats = query_infobright($sql_fb);
			if($result_stats['ret']['data'][0]['date']==null){
				continue;
			}
			$con[] = $result_stats['ret']['data'];
		}
		$html = $sql_fb;
		$title = array(
			'日期',
			'FaceBook点赞数'
		);
		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$html .= "<tr class='listTr'>";
		foreach ($title as  $value) {
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($con as $no => $value) {
				foreach($value as $sqlData) {
					$html .= "<td>" . $sqlData['date'] . "</td>";
					$html .= "<td>" . $sqlData['num'] . "次</td>";
					$html .= "</tr>";
				}
			}
	}

	$html .= "</table></div><br/>";
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>