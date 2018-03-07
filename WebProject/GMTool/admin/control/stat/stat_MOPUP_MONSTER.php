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
		//统计付费人数,次数,钱数,体力值
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select date,sum(int_data3) costcnt ,count(DISTINCT userid) usercnt,sum(var_data1) costmoney,sum(var_data2) costbody from $db_start where category=23 and type=0 $wheretime $whereSql and int_data3>0 group by date ";

			if (isset($sql_sum)) {
				$sql_sum = $sql_sum . " union all " . $sql_pass;
			} else {
				$sql_sum = $sql_pass;
			}
		}
		$sql_sum .= ' order by date desc';
		//统计免费人数,次数,体力值
		foreach ($monthArr as $i) {
			$db_start = 'coklog_function.function_log_' . $i;
			$sql_pass = "select date,sum(int_data2) freecnt,count(DISTINCT userid) usercnt ,sum(var_data2) costbody from $db_start where category=23 and type=0 $wheretime $whereSql and int_data2>0 group by date ";
//			LoggerUtil.getInstance().recordFunctionPointLog(userProfile.getUid(), LoggerUtil.FunctionPoint.MOPUP_MONSTER, 0, new int[]{fightNum, costFreeNum, costNum}, new String[]{""+cost,""+costStamina});

			if (isset($sql_sum1)) {
				$sql_sum1 = $sql_sum1 . " union all " . $sql_pass;
			} else {
				$sql_sum1 = $sql_pass;
			}
		}
		$sql_sum1 .= ' order by date desc';

		$result_pass = query_infobright($sql_sum);
		$result_pass1 = query_infobright($sql_sum1);
		if (in_array($_COOKIE['u'],$privilegeArr)) {
			$html .= $sql_sum.'======'.$sql_sum1;
		}
		$alldata = $datearr = $sum = $timesArr = array();
		//付费统计
		foreach ($result_pass['ret']['data'] as $currow) {
			$date = $currow['date'];
			$datearr[$date] = $date;

			$alldata[0][$date]['costcnt'] += $currow['costcnt'];//次数
			$alldata[0][$date]['usercnt'] += $currow['usercnt'];//人数
			$alldata[0][$date]['costmoney'] += $currow['costmoney'];//花费
			$alldata[0][$date]['costbody'] += $currow['costbody'];//体力值消耗

			$sum[0]['costcnt'] += $currow['costcnt'];//总和
			$sum[0]['usercnt'] += $currow['usercnt'];
			$sum[0]['costmoney'] += $currow['costmoney'];
			$sum[0]['costbody'] += $currow['costbody'];
		}
		//免费统计
		foreach ($result_pass1['ret']['data'] as $currow) {
			$date = $currow['date'];
			if(!array_key_exists($date,$datearr)){
				$datearr[$date] = $date;
			}

			$alldata[1][$date]['freecnt'] += $currow['freecnt'];//次数
			$alldata[1][$date]['usercnt'] += $currow['usercnt'];//人数
			$alldata[1][$date]['costbody'] += $currow['costbody'];//体力值消耗

			$sum[1]['freecnt'] += $currow['freecnt'];//总和
			$sum[1]['usercnt'] += $currow['usercnt'];
			$sum[1]['costbody'] += $currow['costbody'];
		}
		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>日期</th><th>免费次数</th><th>免费人数</th><th>体力值消耗</th><th>付费次数</th><th>付费人数</th><th>付费金币</th><th>体力值消耗</th></tr></thead>";

		$html .="<tr><td>合计</td><td>{$sum[1]['freecnt']}</td><td>{$sum[1]['usercnt']}</td><td>{$sum[1]['costbody']}</td><td>{$sum[0]['costcnt']}</td><td>{$sum[0]['usercnt']}</td><td>{$sum[0]['costmoney']}</td><td>{$sum[0]['costbody']}</td></tr>";
		foreach ($datearr as $date) {
			$html .= "<tr><td>{$date}</td>
					<td>{$alldata[1][$date]['freecnt']}</td>
					<td>{$alldata[1][$date]['usercnt']}</td>
					<td>{$alldata[1][$date]['costbody']}</td>
					<td>{$alldata[0][$date]['costcnt']}</td>
					<td>{$alldata[0][$date]['usercnt']}</td>
					<td>{$alldata[0][$date]['costmoney']}</td>
					<td>{$alldata[0][$date]['costbody']}</td>
					</tr>";
		}

		$html .= '</table></div>';
	}
	echo $html;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>