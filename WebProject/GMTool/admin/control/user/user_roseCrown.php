<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d');
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$statType_title = array('道具使用','玫瑰排行');
foreach ($statType_title as $key=>$value){
	$options .= "<option value='$key'>$value</option>";
}

if ($_REQUEST['dotype'] == 'getPageData') {
	$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
	$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

	$monthArr = monthList(strtotime($start),strtotime($end));
	$sids = implode(',', $selectServerids);
	$whereSql=" and server_id in ($sids) ";
	$wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
	$statType = $_REQUEST['statType'];
	if($statType == 0){
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select l.var_data1 `type`,sum(int_data1) `count` from $db_start l where l.category=6 and l.type=1 $wheretime $whereSql group by l.var_data1 ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql_pass ;
			}else{
				$sql_sum = $sql_pass;
			}
		}
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			echo $sql_sum.PHP_EOL;
		}
		$result_pass =query_infobright($sql_sum);
		$count = $result_pass['ret']['data'][0]['type'];
		if($count == null){
			exit($sql_sum.'<h3>无数据！</h3>');
		}

		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('type'=>'type', 'count'=>'三种道具的使用数量');
		$html .= "<tr class='listTr'>";
		foreach ($_index as $key=>$value)
		{
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
		foreach ($result_pass['ret']['data'] as $no=>$sqlData)
		{
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($_index as $key=>$title){
				$value = $sqlData[$key];
				$html .= "<td>" .$value . "</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";
	}else if($statType == 1){
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql = "select * from (select l.var_data2 `uid`,sum(int_data2) `count` from $db_start l where l.category=6 and l.type=1 $wheretime  $whereSql group by l.var_data2) info ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql ;
			}else{
				$sql_sum = $sql;
			}
		}
		$sql_sum .= "order by info.count desc limit 100 ";

		$result_stats =query_infobright($sql_sum);
		$count = $result_stats['ret']['data'][0]['uid'];
		if($count == null){
			exit($sql.'<h3>无数据！</h3>');
		}

		$html = "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('uid'=>'uid', 'count'=>'玩家收到玫瑰总量');
		$html .= "<tr class='listTr'>";
		foreach ($_index as $key=>$value)
		{
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
		foreach ($result_stats['ret']['data'] as $no=>$sqlData)
		{
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($_index as $key=>$title){
				$value = $sqlData[$key];
				$html .= "<td>" .$value . "</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";
	}
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>