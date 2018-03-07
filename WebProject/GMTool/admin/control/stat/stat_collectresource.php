<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
$levelMin = $_REQUEST['levelMin'];
$levelMax = $_REQUEST['levelMax'];
$buildMin = $_REQUEST['buildMin'];
$buildMax = $_REQUEST['buildMax'];
$regDate = $_REQUEST['regDate'];
if (isset($_REQUEST['getData'])) {
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	$regDateTime = (time() - $regDate*86400) * 1000;
	//总人数
	$sql = "SELECT COUNT(DISTINCT (l.user)) as sum,count(1) as times from logstat l left join userprofile u on u.uid=l.user left join user_building b on b.uid=u.uid 
	 where  l.type=7  and l.timeStamp >= $start and l.timeStamp < $end and b.itemId=400000 and u.level >= $levelMin and u.level <= $levelMax and b.level >= $buildMin 
	  and b.level <= $buildMax and u.regTime >= $regDateTime ";
	$result = $page->execute($sql,3);
	$totalPeople = $result['ret']['data'][0]['sum'];
	$totalTimes = $result['ret']['data'][0]['times'];
	
	$sql = "select l.param1,COUNT(DISTINCT (l.user)) as usernum,SUM(l.param2)  as usersum,
	sum(l.param3) as usertime 
			 from logstat l left join userprofile u on u.uid=l.user left join user_building b on b.uid=u.uid 
	 where  l.type=7  and l.timeStamp >= $start and l.timeStamp < $end and b.itemId=400000 and u.level >= $levelMin and u.level <= $levelMax and b.level >= $buildMin 
	  and b.level <= $buildMax and u.regTime >= $regDateTime  GROUP BY l.param1 ";
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	$log=array();
	$resourceType=array('木材','秘银','铁矿','粮食','石头','金币');
function getStrTime($time){
	$temptime = ($time%60)."秒";
	$time = intval($time/60);
	if($time>0){
		$temptime = ($time%60)."分".$temptime;
		$time = intval($time/60);
	}
	if($time>0){
		$temptime = ($time%24)."时".$temptime;
		$time = intval($time/24);
	}
	if($time>0){
		$temptime = $time."天".$temptime;
	}
	return $temptime;
}
		
	$html = $sql."<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<h3> 共 $totalPeople 人, 采集 ".$totalTimes."次！</h3>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr><td>序号</td><td>资源类型</td><td>玩家数量</td><td>平均采集数量</td><td>平均采集时间</td></tr>";
	foreach ($result as $key=>$curRow)
	{
		$key++;
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$key</td>";
		$html .= "<td>" . $resourceType[intval($curRow['param1'])] . "</td>"
				."<td>" . $curRow['usernum'] . "</td>"
				."<td>" . round($curRow['usersum']/$curRow['usernum']) . "</td>"
				."<td>" . getStrTime(round($curRow['usertime']/$curRow['usernum'])) . "</td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>