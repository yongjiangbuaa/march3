<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate'])
	$startDate = date("Y-m-d 00:00",time()-86400*3);
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59",time());
if($_REQUEST['analyze']=='user'){
	//激活 点击 绑定
	$start = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
	$end  = strtotime($_REQUEST['endDate'])*1000;
	$sql = "select count(1) sum ,date_format(from_unixtime(time/1000),'%Y-%m-%d') as regdate from stat_reg where time >= $start and time < $end group by regdate";
	$result = $page->execute($sql,3);
	$title = array();
	$today = strtotime(date("Y-m-d",time()));
	foreach ($result['ret']['data'] as $key => $value) {
		$log[$value['regdate']]['reg'] = $value['sum'];
		$regDay = $today - strtotime($value['regdate']); 
		$title[$regDay] = $value['regdate'];
		$regStart = max($regStart,$regDay);
	}
	$regStart = $today - $regStart;
	$yIndex = array('reg'=>'注册人数');
	$now = time();
	$day = 0;
	while ($regStart + $day*86400 < $now) {
		$yIndex[$day++] = "第".$day."天";
	}
	ksort($title);
	$sql = "select count(distinct(r.uid)) sum,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regdate,floor((p.time/1000 - unix_timestamp(date_format(from_unixtime(r.time/1000),'%Y-%m-%d')))/86400) as payday from (select * from stat_reg where time >= $start and time < $end) r inner join paylog p on r.uid = p.uid group by regdate,payday";
	$result = $page->execute($sql,3);
	foreach ($result['ret']['data'] as $key => $value) {
		$log[$value['regdate']][$value['payday']] = $value['sum'];
	}
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th></th>";
	foreach ($title as $key=>$value)
		$html .= "<th>" . $value . "</th>";
	$html .= "</tr>";
	foreach ($yIndex as $ykey=>$yname){
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>{$yname}</td>";
		foreach ($title as $date)
		{
			$html .= "<td>" . $log[$date][$ykey] . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>