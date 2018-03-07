<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
$showall = true;

global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectId=$erversAndSidsArr['onlyNum'];
$showData=false;
$alertHeader='';

if ($_REQUEST['action'] == 'view') {
	$payGrade=trim($_REQUEST['payGrade']);
	$paytype=trim($_REQUEST['paytype']);
	if($_REQUEST['showall']){
		$showall =true;
	}else{
		$showall=false;
	}
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$serverIds=implode(",", $selectId);

	if($payGrade == 'all'){
		$wheresql = '';
	}else{
		$wheresql = " level=$payGrade and ";
	}
	if($showall){
		$sql = "select date,level,sum(users) as users from stat_allserver.stat_recharge_cumulative where $wheresql type=$paytype and date between $sDdate and $eDate and sid in($serverIds) group by date,level;";
	}else {
		$sql = "select * from stat_allserver.stat_recharge_cumulative where $wheresql type=$paytype and date between $sDdate and $eDate and sid in($serverIds);";
	}


	if(in_array($_COOKIE['u'],$privilegeArr)){
		echo $sql.PHP_EOL;
	}
	$result = query_infobright($sql);

	$data=array();
	$dates=array();
	$sids=array();
	$levels=array();
	$total=array();
	foreach ($result['ret']['data'] as $curRow){
		$date=$curRow['date'];
		if(!$showall) {
			$sid = $curRow['sid'];
			$data[$date][$sid][$level] = $curRow['users'];
			if(!in_array($sid, $sids)){
				$sids[]=$sid;
			}
		}
		$level=$curRow['level'];
		$total[$date][$level]+=$curRow['users'];
		if(!in_array($date, $dates)){
			$dates[]=$date;
		}

		if (!in_array($level, $levels)){
			$levels[]=$level;
		}
	}
	rsort($dates);
	if(!$showall) {
		sort($sids);
	}
	sort($levels);
	$len=count($levels);
	if ($data || $total){ ///选了只显示合计,这也得改
		$showData=true;
	}else {
		$alertHeader="没有查询到相关数据信息";
	}

//	$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
//	$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
//	foreach ($datearr as $date) {
//		$html .= "<th colspan='2'>$date</th>";
//	}
//	$html .= "</tr></thead>";
//	//副标题
//	$html .= "<tr><th>祝福次数</th><th>人数</th>";
//	foreach ($datearr as $date) {
//		$html .= "<th>祝福次数</th><th>人数</th>";
//	}
//	$html .= "</tr><tbody id='adDataTable'>";
//
//	sort($timesArr);
//	foreach ($timesArr as $value) {
//		$htmltmp = '';
//		$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
//			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$value}</font></td><td>{$sum[$value]}</td>";
//		foreach ($datearr as $date) {
//			$showvalue = $alldata[$date][$value]>0?$alldata[$date][$value]:0;
//			$htmltmp .= "<td>{$value}</td><td>{$showvalue}</td>";
//		}
//		$htmltmp .= "</tr>";
//		$html .= $htmltmp;
//	}
//	$html .= '</tbody></table></div>';
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>