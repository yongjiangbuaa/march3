<?php
!defined('IN_ADMIN') && exit('Access Denied');
$date = date("Y-m-d H:00",time());
if (isset($_REQUEST['getData'])) {
	$startTime = strtotime(date("Y-m-d",strtotime($_REQUEST['date']))) * 1000;
	$endTime = $startTime + 86400000;
	$sql = "select time,count(1) total from world_res_record where time > $startTime and time < $endTime group by time";
	$result = $page->execute($sql,3);
	$nameLink['time'] = '时间';
	$nameLink['count'] = '资源数';
	$nameLinkSort = array_keys($nameLink);
	foreach ($result['ret']['data'] as $key=>$curRow){
		$yindex = $curRow['time'];
		$eventAll[$yindex]['time'] = date('Y-m-d H:i:s',$curRow['time']/1000);
		$eventAll[$yindex]['count'] = $curRow['total'];
	}
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit;
}
if (isset($_REQUEST['getImg'])) {
	$time = strtotime($_REQUEST['date']) * 1000  + $_REQUEST['hourFix'] * 3600 * 1000;
	$sql = "select * from world_res_record where time = " . $time;
	$result = $page->execute($sql,3);
	$title = date('Y-m-d H:i:s',$time/1000) . ' ' . count($result['ret']['data']);

	header('Content-Type: image/jpeg');
	$imgWidth = 50*4*4;
	$imgHeight = 50*4*4 + 20;
	$img = imagecreate($imgWidth, $imgHeight);
	imagecolorallocate($img, 251, 251, 251);//设置底色
	$pointSize = 5; //可从1到5
	imageString($img, 4, 0, 0, $title, imagecolorallocate($img, 0, 0, 0));
	$colors = array(
		imagecolorallocate($img, 0, 0, 230),//木 蓝
		imagecolorallocate($img, 230, 0, 0),//石 红
		imagecolorallocate($img, 0, 0, 0),//铁 黑
		imagecolorallocate($img, 0, 230, 0),//粮 绿
		imagecolorallocate($img, 200, 200, 200),//银 白
		imagecolorallocate($img, 230, 230, 0),//金 黄
		);
	foreach ($result['ret']['data'] as $key=>$curRow){
		$x = ($curRow['x'] - 200) * 8;
		$y = ($curRow['y'] - 200) * 8 + 20;
		imagefilledrectangle($img,$x - 1, $y - 1 , $x + 1, $y + 1, $colors[$curRow['pointItem']]);
	}
	imagepng($img);
	imagedestroy($img);
	exit;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>