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

$statType_title = array('统计数据','累计价值分布','花费分布','玩家记录');
foreach ($statType_title as $key=>$value){
	$options .= "<option value='$key'>$value</option>";
}

if ($_REQUEST['dotype'] == 'getPageData') {
	$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
	$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
	$monthArr = monthList(strtotime($start),strtotime($end));

	$sids = implode(',', $selectServerids);
	if(empty($_REQUEST['selectServer'])){
		$whereSql = "";
	}else {
		$whereSql = " and server_id in ($sids) ";
	}
	$wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
	$statType = $_REQUEST['statType'];
	if($statType == 0){
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select l.date `date`,l.type `type`, count(distinct userid) ucount,count(userid) ucount2 from $db_start l where l.category=20 and (l.type=2 or l.type=3) $wheretime $whereSql group by l.date,l.type ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql_pass ;
			}else{
				$sql_sum = $sql_pass;
			}
		}
		$sql_sum .= "order by l.date desc,l.type ";

		$result_pass =query_infobright($sql_sum);
		$count = $result_pass['ret']['data'][0]['date'];
		if($count == null){
			exit($sql_sum.'<h3>无数据！</h3>');
		}

		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date'=>'日期','type'=>'类型', 'ucount'=>'人数','ucount2'=>'人次');
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
			$sql_pass = "select l.date,case when l.int_data1<1000 then '小于1000' when l.int_data1<3000 then '小于3000' when l.int_data1<5000 then '小于5000' when l.int_data1<10000 then '小于10000' else '大于等于10000' end ctype,count(*) ccount
			from (select t.date,t.userid,sum(t.int_data1) int_data1 from $db_start t where t.category=20 and t.type=1 $wheretime $whereSql group by t.date,t.userid)l ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql_pass ;
			}else{
				$sql_sum = $sql_pass;
			}
		}
		$sql_sum .= "order by l.date desc ";

		$result_pass =query_infobright($sql_sum);
		$count = $result_pass['ret']['data'][0]['date'];
		if($count == null){
			exit($sql_sum.'<h3>无数据！</h3>');
		}

		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date'=>'日期', 'csum'=>'分段','ccount'=>'人数');
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
	}else if($statType == 2){
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select l.date,case when l.int_data1<500 then '小于500' when l.int_data1<1000 then '小于1000' when l.int_data1<2000 then '小于2000' when l.int_data1<8000 then '小于8000'
			when l.int_data1<14000 then '小于14000' else '大于等于14000' end ctype,count(*) ccount
			from (select t.date,t.userid,sum(t.int_data1) int_data1 from $db_start t where t.category=20 and (t.type=2 or l.type=3) $wheretime $whereSql group by t.date,t.userid)l ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql_pass ;
			}else{
				$sql_sum = $sql_pass;
			}
		}
		$sql_sum .= " order by l.date desc ";

		$result_pass =query_infobright($sql_sum);
		$count = $result_pass['ret']['data'][0]['date'];
		if($count == null){
			exit($sql_sum.'<h3>无数据！</h3>');
		}

		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date'=>'日期', 'csum'=>'分段','ccount'=>'人数');
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
	}else if($statType == 3){
		$user_id = $_REQUEST['userId'];
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql = "select l.var_data2 `uid`,from_unixtime(timestamp/1000 ,'%Y-%m-%d %H:%i:%s') t,var_data1 from $db_start l where l.category=20 and l.type=1 $wheretime  $whereSql and userid=$user_id ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql ;
			}else{
				$sql_sum = $sql;
			}
		}
		$sql_sum .= "order by timestamp desc";

		$result_stats =query_infobright($sql_sum);
		$count = $result_stats['ret']['data'][0]['uid'];
		if($count == null){
			exit($sql.'<h3>无数据！</h3>');
		}

		$html = "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('uid'=>'uid', 't'=>'时间','var_data1'=>'奖励ID');
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