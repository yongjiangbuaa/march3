<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d 00:00",time()-86400*6);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d 23:59",time());
if($_REQUEST['userId'])
	$userId = $_REQUEST['userId'];
if($_REQUEST['itemId'])
	$itemId = $_REQUEST['itemId'];

if($_REQUEST['param1'] != 'ALL'){
	$getUseType = 1;
	$param1 = $_POST['param1'];
}

if($_REQUEST['param2'] != 'ALL'){
	$getUseType = 0;
        $param1 = $_POST['param2'];
}


/**
 * @param $start
 * @param $userId
 * @param $itemId
 * @param $end
 * @param $page
 * @return array
 */
function get_goods_cost_log($start, $userId, $itemId, $end, $page,$getUseType,$param1)
{
	global $GoodsUseType,$GoodsGetType;
	$table_name = 'goods_cost_record';
	$split_time = strtotime("2016-08-04")*1000;
	if ($start > $split_time) {
		$table_name .= '_' . date('Ym', $end / 1000);
	}
	$sql = "select time,userId,itemId,type,original,cost,remain,param1,param2 from $table_name where userId='$userId' ";
	if($itemId) $sql .= " and itemId=$itemId ";
	if(is_numeric($param1))	 $sql .= " and type=$getUseType and param1=$param1 ";
	$sql .= "and time between $start and $end;";
	$result = $page->execute($sql, 3);
	$data = array();
	foreach ($result['ret']['data'] as $curRow) {
		$rowData = $curRow;
		$rowData['time'] = $curRow['time'] ? date('Y-m-d H:i:s', ($curRow['time'] / 1000)) : 0;
		if ($curRow['remain'] >= $curRow['original']) {
			$rowData['trend'] = '↑';
			$rowData['trendColor'] = 'red';
		} else {
			$rowData['trend'] = '↓';
			$rowData['trendColor'] = 'green';
		}
		if ($curRow['type'] == 1) {
			$rowData['type'] = '-';
			$rowData['typeColor'] = 'green';
			$rowData['param1'] = $GoodsUseType[$curRow['param1']] ? $GoodsUseType[$curRow['param1']] : 'none';
		} else {
			$rowData['type'] = '+';
			$rowData['typeColor'] = 'red';
			$rowData['param1'] = $GoodsGetType[$curRow['param1']] ? $GoodsGetType[$curRow['param1']] : 'none';
		}
		
		$data[] = $rowData;
	}
	return $data;
}
//消耗类型
$eventOptions = '<option>ALL</option>';
foreach ($GoodsUseType as $eventType => $eventName){
	$eventOptions .= "<option value={$eventType} ";
	if($getUseType == 1 && is_numeric($param1) && $param1 == $eventType) $eventOptions .= "selected";
	$eventOptions .= ">{$eventName}</option>";
}
//获得类型
$eventOptions2 = '<option>ALL</option>';
foreach ($GoodsGetType as $eventType => $eventName){
        $eventOptions2 .= "<option value={$eventType} ";
        if($getUseType == 0 && is_numeric($param1) && $param1 == $eventType) $eventOptions2 .= "selected";
        $eventOptions2 .= ">{$eventName}</option>";
}
if ($type=='view') {
	$start = $_REQUEST['start_time']?strtotime($_REQUEST['start_time'])*1000:strtotime($start)*1000;
	$end = $_REQUEST['end_time']?strtotime($_REQUEST['end_time'])*1000:strtotime($end)*1000;
	// modified by duzhigao. 
	// if inputItemId is null, select all the goods of this user.
	$data = get_goods_cost_log($start, $userId, $itemId, $end, $page,$getUseType,$param1);
	if($data){
		$showData=true;
	}else {
		$headAlert="查询失败";
	}
	$start=date('Y-m-d H:i:s',$start/1000);
	$end=date('Y-m-d H:i:s',$end/1000);
}

if (!$privileges['dropdownlist_view']) {
	$eventOptions = '<option>ALL</option>';
	$selectEventCtl = '<select id="param1" name="param1"  onchange="" style="visibility: hidden;">
			'.$eventOptions.'
	</select><br>
	';
	$eventOptions2 = '<option>ALL</option>';
        $selectEventCtl2 = '<select id="param2" name="param2"  onchange="" style="visibility: hidden;">
                        '.$eventOptions2.'
        </select><br>
        ';
}else{
	$selectEventCtl = '<br>
	消费类型
	<select id="param1" name="param1"  onchange="">
			'.$eventOptions.'
	</select>
	';
	
	$selectEventCtl2 = '<br>
        获得途径
        <select id="param2" name="param2"  onchange="">
                        '.$eventOptions2.'
        </select>
        ';
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
