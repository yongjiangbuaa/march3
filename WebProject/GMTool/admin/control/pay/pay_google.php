<?php
!defined('IN_ADMIN') && exit('Access Denied');

//require_once GEO_ROOT.'/geo.inc.php';

$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
$showData = false;

$pageNum = $_REQUEST['pageNum'];
if(empty($pageNum) || $pageNum <= 0){
	$pageNum = 0;
}
$pageNumGo = $_REQUEST['pageNumGo'];
if(!empty($pageNumGo)){
	$pageNum = $pageNumGo;
}
$countryShow=false;
$countryPay=false;
$mode = false;
if ($_REQUEST['action']) {
	$start = $_REQUEST['start_time']?substr($_REQUEST['start_time'],0,10):substr($start,0,10);
	$end = $_REQUEST['end_time']?substr($_REQUEST['end_time'],0,10):substr($end,0,10);

	$startTime = strtotime($start);
	$endTime = strtotime($end);
	$allpay = $_REQUEST['allpay'];
	if($allpay){
		$wheresql = '';
	}else{
		$wheresql = " where time>$startTime and time<$endTime ";
		$mode = true;

	}

	$num=10;
	$country = $_REQUEST['show_country'];
	$country_pay=$_REQUEST['show_pay_country'];
	if(empty($country))
	{
		$num=700;
		$countryShow=false;
	}else
	{
		$num=10;
		$countryShow=true;
	}

	if(empty($country_pay))
	{
		$countryPay=false;
	}else
	{
		$num=10;
		$countryPay=true;
	}

	if($_REQUEST['updateAl']=='update')
	{
		$start1 = $_REQUEST['update_start_time']?substr($_REQUEST['update_start_time'],0,10):$start;
		$end1 = $_REQUEST['update_end_time']?substr($_REQUEST['update_end_time'],0,10):$end;

		$startTime1 = strtotime($start1);
		$endTime1 = strtotime($end1);
		$adminid = $_COOKIE['u'];
		$userName= $_REQUEST['userName']?$_REQUEST['userName']:$adminid;
		$sql = "update google_pay_check set ext='$userName' , status =1 where time>$startTime1 and time<$endTime1 ";
		$ret = cobar_query_global_db_cobar($sql);
	}

	if($_REQUEST['action'] =='search' && $_REQUEST['action_op']){
		$content = $_REQUEST['action_op'];
		$payid = $_REQUEST['payid'];
		$sql = "update google_pay_check set ext='$content' , status =1 WHERE payId='$payid' ";
		$ret = cobar_query_global_db_cobar($sql);
	}
	if($_REQUEST['action']=='markdone'){ //勾选完成
		$payid = $_REQUEST['payid'];
		$adminid = $_COOKIE['u'];
		$sql = "update google_pay_check set status =1 , ext='$adminid' WHERE payId='$payid' ";
		$ret = cobar_query_global_db_cobar($sql);
	}

	$beg=$pageNum*$num;
	$sql = "select payId,uid,serverId,productid,orderid,from_unixtime(time) as timeshow,payData,status,ext from google_pay_check $wheresql order by time desc limit $beg,$num";
	$data = cobar_query_global_db_cobar($sql);
	$page = new BasePage();
	$time = time();
	$year1=date('Y',$time);
	$month=date('m',$time)-1;
	foreach($data as &$row){
		$tmp = $row['payData'];
		$tmp = json_decode($tmp,true);
		$arr = explode('|',$tmp['developerPayload']);
		$row['payData'] = $arr[1];
		$uid=$row['uid'];
		$orderId=$row['orderid'];
		$serverId=$row['serverId'];

		if($countryShow==true)
		{
			$sql1="SELECT country FROM stat_login_".$year1."_".$month." WHERE uid='$uid' ORDER BY time DESC LIMIT 1";
			$country=$page->executeServer("s".$serverId,$sql1,3);
			$row['loginCountry']= $country['ret']['data'][0]['country'];

			$sql_reg="SELECT country FROM stat_reg WHERE uid='$uid' LIMIT 1";
			$country_reg=$page->executeServer("s".$serverId,$sql_reg,3);
			$row['con_reg']= $country['ret']['data'][0]['country'];
		}

		if($countryPay == true)
		{
			$sql_payLog="SELECT ip FROM paylog WHERE orderId='$orderId'";
			$country=$page->executeServer("s".$serverId,$sql_payLog,3);
			$IP=$country['ret']['data'][0]['ip'];
//			echo $IP;
			$row['payIp']= $IP;
//			echo geo_get_city_by_ip($IP);
//			$row['payIpCountry']=geo_get_city_by_ip($IP);
		}

	}
	if(count($data) >0){
		$showData = true;
	}
}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>
