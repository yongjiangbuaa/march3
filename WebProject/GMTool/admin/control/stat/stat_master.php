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

$statType_title = array('奖励次数');
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
			$sql_pass = "select date, int_data1 ,count(1) times from $db_start where category=4 and type=2 $wheretime $whereSql group by date,int_data1 order by date,int_data1 desc ";
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
		$alldata = $datearr = $sum =$timesArr=array();
		foreach ($result_pass['ret']['data'] as $currow) {
			$date = $currow['date'];
			$datearr[$date] = $date;

			$timesArr[$currow['int_data1']] = $currow['int_data1'];//等级
			$alldata[$date][$currow['int_data1']] += $currow['times'];

			$sum[$currow['int_data1']] += $currow['times'];//总和
		}

		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
		foreach ($datearr as $date) {
			$html .= "<th colspan='2'>$date</th>";
		}
		$html .= "</tr></thead>";
		//副标题
		$html .= "<tr><th>大本等级</th><th>个数</th>";
		foreach ($datearr as $date) {
			$html .= "<th>大本等级</th><th>人数</th>";
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