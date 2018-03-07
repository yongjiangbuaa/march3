<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$developer = in_array($_COOKIE['u'],$privilegeArr);
if($_REQUEST['userUid'])
	$userUid = $_REQUEST['userUid'];
if(!$_REQUEST['end'] || !$_REQUEST['start']){
	$start = date("Y-m-d 00:00:00",time() -86400 * 2);
	$end = date("Y-m-d 23:59:59",time());
	$_REQUEST['start'] = $start; 
	$_REQUEST['end'] = $end;
}

if($_REQUEST['analyze']=='view'){
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($start)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
	$userUid=$_REQUEST['userUid'];
	
	$sql="select type,sum(cost) costSum from gold_cost_record where  userId ='$userUid' and time >=$start and time<$end and cost<0 group by userId,type;";
	$ret=$page->execute($sql, 3);
	$i=1;
	$arrs=array();
	foreach ($ret['ret']['data'] as $row){
		if($i==1){
			$arr1[] = array(
					"name" =>  $goldLink[$row['type']],
					"y" => intval(-$row['costSum']),
					"sliced" => true,
					"selected" => true
			);
		}else{
			$arr[] = array(
					$goldLink[$row['type']],intval(-$row['costSum'])
			);
		}
		$i++;
	}
	//合并数组
	$arrs = array_merge($arr1,$arr);
	$data = json_encode($arrs);

}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>