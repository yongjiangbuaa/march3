<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$headAlert='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if (!$_REQUEST['selectPf']) {
	$regPf = 'ALL';
}else{
	$regPf = $_REQUEST['selectPf'];
}
$dbIndex=array(
	'sid'=>'服',
	'uid'=>'uid',
	'name'=>'name',
	'orderId'=>'订单号',
	'payPf'=>'支付平台',
	'payTime'=>'支付时间',
	'spend'=>'金额',
	'regTime'=>'注册时间',
	'regPf'=>'平台',
	'country'=>'国家',
	'operateTime'=>'退款时间',
);
if($_REQUEST['analyze']=='user'){
	$regPf = $_REQUEST['selectPf'];
	if($_REQUEST['fbFlag']){
		$isFbRefund = true;
	}
	$startDate = substr($_REQUEST['startDate'],0,10);
	$startTime= strtotime($startDate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$endTime = (strtotime($endDate)+86400)*1000;
	if ($isFbRefund){
		$whereSql="where fbFlag=1 and operateTime between $startTime and $endTime ";
	}else {
		$whereSql="where operateTime between $startTime and $endTime ";
	}
	if ($regPf && $regPf!='ALL'){
		$whereSql.=" and regPf='$regPf' ";
	}
//	$link=mysqli_connect('STATISTICSIP','root','DBPWD','global');
	$link=get_stats_global_connection();;

	$sql="select sid,uid,name,orderId,payPf,payTime,spend,regTime,regPf,country,operateTime from refund_info $whereSql;";
	$res = mysqli_query($link,$sql);
	$count=0;
	$sumSpend=0;
	$data=array();
	while ($row = mysqli_fetch_assoc($res)){
		$count++;
		$sumSpend+=$row['spend'];
		$one=array();
		foreach ($dbIndex as $key=>$val){
			if ($key=='payTime' || $key=='regTime' || $key=='operateTime'){
				$one[$key]=$row[$key]?date('Y-m-d H:i:s',($row[$key]/1000)):0;
			}else {
				$one[$key]=$row[$key]?$row[$key]:'';
			}
		}
		$data[]=$one;
	}
	if ($data){
		$showData = true;
		$sumSpend=number_format($sumSpend,2);
	}else {
		$headAlert='没有查到相关数据';
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>