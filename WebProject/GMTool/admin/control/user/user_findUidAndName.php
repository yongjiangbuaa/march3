<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['xpoint']){
	$xpoint = intval($_REQUEST['xpoint']);
}else {
	$xpoint=0;
}
if($_REQUEST['ypoint']){
	$ypoint = intval($_REQUEST['ypoint']);
}else {
	$ypoint=0;
}
if ($type) {
	$maxX=$xpoint+1;
	$minX=$xpoint-1;
	$maxY=$ypoint+1;
	$minY=$ypoint-1;
	$sql = "select x,y,ownerId,ownerName from worldpoint where x between $minX and $maxX and y between $minY and $maxY;";
	$result = $page->execute($sql, 3);
	$items=array();
	if($result['ret']['data']){
		$i = 0 ;
		foreach ($result['ret']['data'] as $curRow){
			$items[$i]['x'] = $curRow['x'];
			$items[$i]['y'] = $curRow['y'];
			$items[$i]['ownerId'] = $curRow['ownerId'];
			$items[$i]['ownerName'] = $curRow['ownerName'];
			$i++;
		}
		$showData = true;
	}else{
		$headAlert="该坐标上没有玩家,请重新输入坐标值";
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>