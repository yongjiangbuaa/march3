<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d');
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$statType_title = array('统计数据');
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
	if($_REQUEST['userId']) {
		$user_id = $_REQUEST['userId'];
		$whereSql .= " and userid='$user_id' ";
	}
	$html = '';
	$wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
	$statType = $_REQUEST['statType'];
	//int_data5是祝福次数 int_data3是对应次数总消耗. int_data2是每次消耗
	if($statType == 0) {
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select date,count(DISTINCT var_data1) allcnt,count(DISTINCT userid) usercnt from $db_start where category=31 and type=0 $wheretime $whereSql group by date order by date desc ";
			if (isset($sql_sum)) {
				$sql_sum = $sql_sum . " union all " . $sql_pass;
			} else {
				$sql_sum = $sql_pass;
			}
		}
		$result_pass = query_infobright($sql_sum);
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			$html .= $sql_sum;
		}
		$alldata = $datearr = $sum = $timesArr = array();
		foreach ($result_pass['ret']['data'] as $currow) {
			$date = $currow['date'];
			$datearr[$date] = $date;

			$alldata[$date]['allcnt'] += $currow['allcnt'];
			$alldata[$date]['usercnt'] += $currow['usercnt'];

			$sum['usercnt'] += $currow['usercnt'];//总和
			$sum['allcnt'] += $currow['allcnt'];
		}

		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>日期</th><th>参与联盟</th><th>参与人数</th></tr></thead>";

		$html .="<tr><td>合计</td><td>{$sum['allcnt']}</td><td>{$sum['usercnt']}</td></tr>";
		foreach ($datearr as $date) {
			$html .= "<tr><td>{$date}</td><td>{$alldata[$date]['allcnt']}</td><td>{$alldata[$date]['usercnt']}</td></tr>";
		}

		$html .= '</table></div>';
	}
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>