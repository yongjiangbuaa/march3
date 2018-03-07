<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
if (isset($_REQUEST['getData'])) {
	$start = strtotime($_REQUEST['start'])*1000;
	$end = strtotime($_REQUEST['end'])*1000;
	$sql = "SELECT type, COUNT(uid), SUM(param1), SUM(data1) FROM logstat WHERE type >= 16 AND type <= 18 AND `timeStamp` >= $start AND `timeStamp` < $end GROUP BY type";
	$result = $page->execute($sql,3);
    $openTimes = 0;
    $digGold = 0;
    $shareTimes = 0;
    $inviteTimes = 0;
	foreach($result['ret']['data'] as $value){
	    $type = $value['type'];
	    switch ($type) {
	        case 16:
	            $openTimes = $value['COUNT(uid)'];
	            $digGold = $value['SUM(data1)'];
	            break;
	        case 17:
	            $shareTimes = $value['COUNT(uid)'];
	            break;
	        case 18:
	            $inviteTimes = $value['SUM(param1)'];
	            break;
	    }
	}
	$index = array ("类型", "数目");
	$type = array("开启宝箱", "获得金币", "分享次数", "邀请数量");
	$data = array($openTimes, $digGold, $shareTimes, $inviteTimes);
	$html .= "<div style='float:left;width:100%;text-align:center;overflow-x:auto;overflow-y:auto;'>
				<table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		$html .= "<td>" . $value. "</td>";
	}
	$html .= "</tr>";
	foreach ($type as $key => $value)
	{
		$sqlData['no'] = ++$no;
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>" . $value . "</td>";
		$html .= "<td>" . $data[$key] . "</td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";//
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>