<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d');
global $servers;
$allServerFlag=true;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$statType_title = array('新手三日','新手七日');
foreach ($statType_title as $key=>$value){
	$options .= "<option value='$key'>$value</option>";
}
if($_REQUEST['allServers']){
	$allServerFlag =true;
}
if ($_REQUEST['dotype'] == 'getPageData') {

	$start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
	$end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);

	$monthArr = monthList(strtotime($start),strtotime($end));
	$sids = implode(',', $selectServerids);
	$wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
	$statType = $_REQUEST['statType'];
	if ($statType == 0) {
		if($allServerFlag) {
			foreach ($monthArr as $i) {
				$db_start = 'coklog_function.function_log_' . $i;
				$sql_pass = "select l.date `date`,l.int_data1 `interVal`,count(*) `num` from $db_start l where l.category=21 and l.type=3 $wheretime  group by `interVal`,`date` ";
				if (isset($sql_sum)) {
					$sql_sum = $sql_sum . " union " . $sql_pass;
				} else {
					$sql_sum = $sql_pass;
				}
			}
		}else{
			$whereSql .= " and server_id in ($sids) ";
			foreach ($monthArr as $i) {
				$db_start = 'coklog_function.function_log_' . $i;
				$sql_pass = "select l.date `date`,l.server_id `server`,l.int_data1 `interVal`,count(*) `num` from $db_start l where l.category=21 and l.type=3 $wheretime $whereSql group by `interVal`,`server`,`date` ";
				if (isset($sql_sum)) {
					$sql_sum = $sql_sum . " union " . $sql_pass;
				} else {
					$sql_sum = $sql_pass;
				}
			}
		}
		$sql_sum = $sql_sum.'order by date desc';
		$result_pass = query_infobright($sql_sum);
		$count = $result_pass['ret']['data'][0]['date'];
		if ($count == null) {
			exit($sql_sum . '<h3>无数据！</h3>');
		}
		$three_log = array();
		foreach ($result_pass['ret']['data'] as $result_value) {
			$date  = $result_value['date'];
			$sid = $result_value['server']?$result_value['server']:'合计';
			switch ($result_value['interVal']) {
				case 0:
					$three_log[$date][$sid]['one_day'] = $result_value['num'];
					break;
				case 1:
					$three_log[$date][$sid]['two_day'] = $result_value['num'];
					break;
				case 2:
					$three_log[$date][$sid]['three_day'] = $result_value['num'];
					break;
				default:
					$three_log[$date][$sid]['un_day'] = $result_value['num'];
					break;
			}
		}
		foreach($three_log as $dateKey=>$dateVal ){
			foreach($dateVal as $sidKey=>$sidVal){
				$one = array();
				$one['date'] = $dateKey;
				$one['sid'] = $sidKey;
				$one['one_day'] = $three_log[$dateKey][$sidKey]['one_day'];
				$one['two_day'] = $three_log[$dateKey][$sidKey]['two_day'];
				$one['three_day'] = $three_log[$dateKey][$sidKey]['three_day'];
				$one['un_day'] = $seven_log[$dateKey][$sidKey]['un_day'];
				$records[] = $one;
			}
		}
		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date' => '日期', 'sid' => '服', 'one_day' => '注册第一天购买人数', 'two_day' => '注册第二天购买人数', 'three_day' => '注册第三天购买人数', 'un_day' => '未知注册天数购买人数');
		$html .= "<tr class='listTr'>";
		foreach ($_index as $key => $value) {
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
		foreach ($records as $no => $sqlData) {
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($_index as $key => $title) {
				if(!$allServerFlag) {
					$value = $sqlData[$key];
					$html .= "<td>" . $value . "</td>";
				}else{
					if($key!='sid') {
						$value = $sqlData[$key];
						$html .= "<td>" . $value . "</td>";
					}else{
						$html .= "<td> 合计</td>";
					}
				}
			}
			$html .= "</tr>";
		}
		$html .= "</table></div><br/>";
	}

else if($statType == 1){
	if($allServerFlag) {

		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql = "select l.var_data1 `id`,l.int_data1 state,l.date `date`,l.int_data2  `interVal`,count(*) `num` from $db_start l where l.category=21 and l.type=7 $wheretime  group by `date`,`id`,`state`,`interVal` ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql ;
			}else{
				$sql_sum = $sql;
			}
		}
	}else{
		$whereSql .= " and server_id in ($sids) ";
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql = "select l.var_data1 `id`,l.int_data1 state,l.date `date`,l.server_id `server`,l.int_data2  `interVal`,count(*) `num` from $db_start l where l.category=21 and l.type=7 $wheretime  $whereSql group by `date`,`server`,`id`,`state`,`interVal` ";
			if(isset($sql_sum)){
				$sql_sum = $sql_sum . " union " . $sql ;
			}else{
				$sql_sum = $sql;
			}
		}
	}
	$sql_sum = $sql_sum."order by date desc ,id asc, state asc;";
	$result_stats =query_infobright($sql_sum);
		$count = $result_stats['ret']['data'][0]['id'];
	$seven_log = array();
	foreach ($result_stats['ret']['data'] as $result_value) {
		$date  = $result_value['date'];
		$sid = $result_value['server']?$result_value['server']:'合计';
		$id = $result_value['id'];
		$state = $result_value['state'];
		switch ($result_value['interVal']) {
			case 0:
				$seven_log[$date][$sid][$id][$state]['one_day1'] = $result_value['num'];
				break;
			case 1:
				$seven_log[$date][$sid][$id][$state]['two_day2'] = $result_value['num'];
				break;
			case 2:
				$seven_log[$date][$sid][$id][$state]['three_day3'] = $result_value['num'];
				break;
			case 3:
				$seven_log[$date][$sid][$id][$state]['four_day4'] = $result_value['num'];
				break;
			case 4:
				$seven_log[$date][$sid][$id][$state]['five_day5'] = $result_value['num'];
				break;
			case 5:
				$seven_log[$date][$sid][$id][$state]['six_day6'] = $result_value['num'];
				break;
			case 6:
				$seven_log[$date][$sid][$id][$state]['seven_day7'] = $result_value['num'];
				break;
			default:
				$seven_log[$date][$sid][$id][$state]['un_day'] = $result_value['num'];
				break;
		}
	}
	foreach($seven_log as $dateKey=>$dateVal ){
		foreach($dateVal as $sidKey=>$sidVal){
			foreach($sidVal as $idKey=>$idVal){
				foreach($idVal as $stateKey=>$stateVal){
					$one = array();
					$one['date'] = $dateKey;
					$one['sid'] = $sidKey;
					$one['id'] = $idKey;
					$one['state'] = $stateKey;
					$one['one_day1'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['one_day1'];
					$one['two_day2'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['two_day2'];
					$one['three_day3'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['three_day3'];
					$one['four_day4'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['four_day4'];
					$one['five_day5'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['five_day5'];
					$one['six_day6'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['six_day6'];
					$one['seven_day7'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['seven_day7'];
					$one['un_day'] = $seven_log[$dateKey][$sidKey][$idKey][$stateKey]['un_day'];
					$records[] = $one;
				}
			}
		}
	}
		$html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>
			<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
		$_index = array('date' => '日期', 'sid' => '服', 'id' => '任务类型', 'state'=>'阶段', 'one_day1' => '第1天', 'two_day2' => '第2天','three_day3' => '第3天','four_day4' => '第4天','five_day5' => '第5天','six_day6' => '第6天','seven_day7' => '第7天','un_day' => '未知天');
		$html .= "<tr class='listTr'>";
		foreach ($_index as $key=>$value)
		{
			$html .= "<th>" . $value . "</th>";
		}
		$html .= "</tr>";
		foreach ($records as $no=>$sqlData)
		{
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			foreach ($_index as $key_index=>$title){
				if(!$allServerFlag) {
					$value = $sqlData[$key_index];
					$html .= "<td>" . $value . "</td>";
				}else{
					if($key_index!='sid') {
						$value = $sqlData[$key_index];
						$html .= "<td>" . $value . "</td>";
					}else{
						$html .= "<td> 合计</td>";
					}
				}
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