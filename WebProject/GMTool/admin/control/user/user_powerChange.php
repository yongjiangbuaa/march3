<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d 00:00",time()-86400);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d 23:59",time());
if($_REQUEST['userId'])
	$userId = $_REQUEST['userId'];
if($_REQUEST['reason'])
	$reason = $_REQUEST['reason'];

if ($type=='view') {
	$startTime = $_REQUEST['start_time']?strtotime($_REQUEST['start_time'])*1000:strtotime($start)*1000;
	$endTime = $_REQUEST['end_time']?strtotime($_REQUEST['end_time'])*1000:strtotime($end)*1000;
	if($startTime < $endTime) {
		$whereSql = '';
		if ($reason != 'ALL') {
			$reason = intval($reason);
			$whereSql = " and reason=$reason ";
		}
		$data = array();
		for($ts=$startTime;$ts< $endTime + 30*86400*1000;$ts+=30*86400*1000){
			$powerLogTable = 'power_log_'.date("Ym", $ts / 1000);
			$sql = "select * from $powerLogTable where uid='$userId' $whereSql  and timeStamp between $startTime and $endTime order by timeStamp asc;";
			$result = $page->execute($sql, 3);
			if (!$result['error'] && $result['ret']['data']) $data = array_merge($data, $result['ret']['data']);
		}
		foreach ($data as $k=> $curRow){
			$data[$k]['timeStamp']=$curRow['timeStamp']?date("Y-m-d H:i:s",$curRow['timeStamp']/1000):0;
			$data[$k]['reason']=$POWER_TYPE[$curRow['reason']];
		}

		if($data){
			$showData=true;
		}else {
			$headAlert=date("Y-m-d H:i:s",$startTime/1000)."~".date("Y-m-d H:i:s",$endTime/1000).'该时间段没有查到记录!';
		}
	}else{
		$headAlert = '开始时间必须小于结束时间！';
	}
}

$eventOptions = '<option>ALL</option>';
foreach ($POWER_TYPE as $eventType => $eventName){
	$eventOptions .= "<option value={$eventType} ";
	if( $reason === $eventType) $eventOptions .= "selected";
	$eventOptions .= ">{$eventName}</option>";
}

if (!$privileges['dropdownlist_view']) {
}else{
	$selectEventCtl = '<br>
	战力变化原因
	<select id="reason" name="reason"  onchange="">
			'.$eventOptions.'
	</select>
	';
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
