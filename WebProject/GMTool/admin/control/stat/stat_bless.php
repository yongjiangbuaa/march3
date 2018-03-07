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
			$sql_pass = "select date,times,count(1) cnt from (select date,userid,case when max(int_data1) <20 then max(int_data1) when max(int_data1)>=20 then 20 end as times from $db_start where category=27 and type=1 $wheretime $whereSql group by date,userid) AA group by date,times  ";
			if (isset($sql_sum)) {
				$sql_sum = $sql_sum . " union all " . $sql_pass;
			} else {
				$sql_sum = $sql_pass;
			}
		}
		$sql_sum .= "order by date desc";

		$result_pass = query_infobright($sql_sum);
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			$html .= $sql_sum;
		}
		$alldata = $datearr = $sum =$timesArr=array();
		foreach ($result_pass['ret']['data'] as $currow) {
			$date = $currow['date'];
			$datearr[$date] = $date;
			$timesArr[$currow['times']] = $currow['times'];
			$alldata[$date][$currow['times']] += $currow['cnt'];
			$sum[$currow['times']] += $currow['cnt'];
		}

		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
		foreach ($datearr as $date) {
			$html .= "<th colspan='2'>$date</th>";
		}
		$html .= "</tr></thead>";
		//副标题
		$html .= "<tr><th>祝福次数</th><th>人数</th>";
		foreach ($datearr as $date) {
			$html .= "<th>祝福次数</th><th>人数</th>";
		}
		$html .= "</tr><tbody id='adDataTable'>";

		sort($timesArr);
		foreach ($timesArr as $value) {
				$htmltmp = '';
				$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$value}</font></td><td>{$sum[$value]}</td>";
				foreach ($datearr as $date) {
					$showvalue = $alldata[$date][$value]>0?$alldata[$date][$value]:0;
					$htmltmp .= "<td>{$value}</td><td>{$showvalue}</td>";
				}
				$htmltmp .= "</tr>";
				$html .= $htmltmp;
		}
		$html .= '</tbody></table></div>';
	}
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>