<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d 00:00",time()-86400*6);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d 23:59",time());
// if($_REQUEST['queryIp'])
// 	$queryIp = $_REQUEST['queryIp'];
if($_REQUEST['queryIps'])
 	$queryIps = $_REQUEST['queryIps'];

$dbIndex=array(
	'sid'=>'服',
	'uid'=>'uid',
	'orderId'=>'订单号',
	'pf'=>'支付渠道',
	'productId'=>'礼包号',
	'time'=>'支付时间',
	'spend'=>'金额',
	'payLevel'=>'支付时领主等级',
	'buildingLv'=>'支付时大本等级',
	'deviceId'=>'设备id',
	'ip'=>'ip'
);
if ($type=='view') {
	$startTime = $_REQUEST['start_time']?strtotime($_REQUEST['start_time'])*1000:0;
	$endTime = $_REQUEST['end_time']?strtotime($_REQUEST['end_time'])*1000:strtotime($end)*1000;
	$start=date('Y-m-d H:i:s',$startTime/1000);
	$end=date('Y-m-d H:i:s',$endTime/1000);
	if (empty($queryIps)){
		$headAlert='IP不能为空!';
	}else {
		$data=array();
		$info=array();
		$queryIps=str_replace('；', ';', $queryIps);
		$queryIps=trim($queryIps);
		$ipArray=explode(';', $queryIps);
		$ips=implode("','", $ipArray);
		//global有这个表,但是为空
		$sql="select ip,count(distinct deviceId) devCount,count(distinct uid) uidCount,sum(spend) sumSpend from global_paylog where ip in('$ips') and time >=$startTime and time<=$endTime group by ip;";
//		$link=mysqli_connect('STATISTICSIP','root','DBPWD','global');
		$link=get_stats_global_connection();
		$res = mysqli_query($link,$sql);
		while($row = mysqli_fetch_assoc($res)){
			$one=array();
			$one['ip']=$row['ip'];
			$one['devCount']=$row['devCount'];
			$one['uidCount']=$row['uidCount'];
			$one['sumSpend']=$row['sumSpend'];
			$data[]=$one;
		}
		
		$sql="select sid,uid,orderId,pf,productId,time,spend,payLevel,buildingLv,deviceId,ip from global_paylog where ip in('$ips') and time >=$startTime and time<=$endTime;";
		$res = mysqli_query($link,$sql);
		while($row = mysqli_fetch_assoc($res)){
			$line=array();
			foreach ($dbIndex as $key=>$val){
				if ($key=='time'){
					$line[$key]=$row[$key]?date('Y-m-d H:i:s',$row[$key]/1000):0;
				}else {
					$line[$key]=$row[$key]?$row[$key]:'';
				}
			}
			$info[]=$line;
		}
		mysqli_close($link);
		if($data || $info){
			$showData=true;
		}else {
			$headAlert="没有查到数据";
		}
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>