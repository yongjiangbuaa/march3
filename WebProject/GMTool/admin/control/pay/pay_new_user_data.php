<?php
!defined('IN_ADMIN') && exit('Access Denied');
$date = date("Y-m-d",time());
if (isset($_REQUEST['getData'])) {
	$startTime = strtotime($_REQUEST['date']) * 1000 + $_REQUEST['hourFix'] * 86400000;
	$endTime = $startTime + 86400000;

	//更新首次支付数据
	if($_REQUEST['updateData']){
		$sql = "replace into firstpaylog select * from (select * from paylog order by time) a group by uid";
		$page->execute($sql);
	}

	$sql = "select count(1) sum from stat_reg where time > $startTime and time < $endTime";
	$result = $page->execute($sql,3);
	echo date("Y-m-d",$startTime/1000). " 注册人数" . $result['ret']['data'][0]['sum'] ."<br />";

	$sql = "select count(distinct(r.uid)) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as payDate,date_format(from_unixtime(f.time/1000),'%Y-%m-%d') as firstDate from paylog p inner join stat_reg r on p.uid = r.uid inner join firstpaylog f on f.uid = p.uid where r.time > $startTime and r.time < $endTime group by firstDate,payDate order by p.time asc";
	$result = $page->execute($sql,3);
	//横向是新增付费用户的日期，纵向是某天新增付费用户持续付费
	if(is_array($result['ret']['data'])){
		$nameLink['time'] = '';
		$nameLinkSort = array_keys($nameLink);
		$namLinkSortEnd = 10000;//用于表头排序
		//预先生成yindex
		$days = ceil((time() - $startTime/1000)/86400);
		$dayIndex = 0;
		while(++$dayIndex<=$days){
			if($dayIndex == 1)
				$eventAll[$dayIndex]['time'] = "新增";
			else
				$eventAll[$dayIndex]['time'] = "第{$dayIndex}天持续付费";
		}
		foreach ($result['ret']['data'] as $key=>$curRow){
			$xindex = $curRow['firstDate'];
			$nameLink[$xindex] = $xindex;
			$nameLinkSort[$namLinkSortEnd - ($endTime/1000 - strtotime($xindex))/86400] = $xindex;
			$yindex = (strtotime($curRow['payDate'])-strtotime($xindex))/86400+1;
			$eventAll[$yindex][$xindex] = $curRow['sum'];
		}
	}else{
		echo "未获得支付数据";
	}
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>