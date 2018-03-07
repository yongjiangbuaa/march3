<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$showData = false;

$dbIndex=array(
		'uid',
		'name',
		'orderId',
		'productId',
		'payPf',
		'type',
		'regTime',
		'payTime',
		'spend',
		'payLevel',
		'payDeviceId',
		'payIp',
		'regPf',
		'country',
		'regIp',
		'stat_regTime'
);

if($_REQUEST['event']=='refund'){
	$paymentid = $_REQUEST ['paymentid'];
	$fbUid = $_REQUEST ['fbUid'];
	$amount = $_REQUEST ['amount'];
	$currency = $_REQUEST ['currency'];
	$reason = $_REQUEST ['reason'];
	$ret = file_get_contents("https://p2cok.elexapp.com/COKFB/cokfbpay/handle_dispute.php?cmd=refund&paymentid=$paymentid&amount=$amount&currency=$currency&reason=$reason");
	if ($ret=='OK') {
		$sql="select sid,orderId from global_paylog where orderId='$paymentid';";
//		$link=mysqli_connect('STATISTICSIP','root','DBPWD','global');
		$link=get_stats_global_connection();
		$res = mysqli_query($link,$sql);
		$row = mysqli_fetch_assoc($res);
		$nowTime=time()*1000;
		if (isset($row['sid']) && !empty($row['sid'])){
			$server='s'.$row['sid'];
			$sql = "update paylog set status=4 where orderId='$paymentid';";
			$page->executeServer($server, $sql, 2);
			
			$sql = "update global_paylog set status=4 where orderId='$paymentid';";
			$res = mysqli_query($link,$sql);
			
			$sql="select p.uid uid,u.name name,p.orderId orderId,p.productId productId,p.pf payPf,r.type type,u.regTime regTime,p.time payTime,p.spend spend,p.payLevel payLevel,p.deviceId payDeviceId,p.ip payIp,r.pf regPf,r.country country,r.ip regIp,r.time stat_regTime from paylog p inner join userprofile u on p.uid=u.uid inner join stat_reg r on p.uid=r.uid where p.orderId='$paymentid' order by stat_regTime desc;";
			$ret=$page->executeServer($server, $sql, 3);
			$serverId=substr($server, 1);
			if (!$ret['error'] && $ret['ret']['data']){
				$col="sid,";
				$colVal="$serverId,";
				$dupVal="sid=$serverId,";
				foreach ($dbIndex as $index){
					$col.=$index.',';
					$colVal.=$ret['ret']['data'][0][$index]?("'{$ret['ret']['data'][0][$index]}',"):("'',");
					$dupVal.=$ret['ret']['data'][0][$index]?("$index='{$ret['ret']['data'][0][$index]}',"):("$index='',");
				}
				$col.='operateTime';
				$colVal.=$nowTime;
				$dupVal.="operateTime=$nowTime";
				$sql="insert into refund_info($col) values($colVal) ON DUPLICATE KEY UPDATE $dupVal;";
				$res = mysqli_query($link,$sql);
			}
			mysqli_close($link);
		}else{
			$result = cobar_getAllAccountList('device', $fbUid);
			if (empty($result)){
				$result = cobar_getAllAccountList('facebook', $fbUid);
			}
			foreach ($result as $curRow){
				$server='s'.$curRow['server'];
				$sql="select uid,orderId from paylog where orderId='$paymentid';";
				$ret=$page->executeServer($server, $sql, 3);
				if (!$ret['error'] && $ret['ret']['data'] && $ret['ret']['data'][0]['uid']){
					$sql="update paylog set status=4 where orderId='$paymentid';";
					$page->executeServer($server, $sql, 2);
					
					$sql = "update global_paylog set status=4 where orderId='$paymentid';";
					$res = mysqli_query($link,$sql);
					
					$sql="select p.uid uid,u.name name,p.orderId orderId,p.productId productId,p.pf payPf,r.type type,u.regTime regTime,p.time payTime,p.spend spend,p.payLevel payLevel,p.deviceId payDeviceId,p.ip payIp,r.pf regPf,r.country country,r.ip regIp,r.time stat_regTime from paylog p inner join userprofile u on p.uid=u.uid inner join stat_reg r on p.uid=r.uid where p.orderId='$paymentid' order by stat_regTime desc;";
					$ret=$page->executeServer($server, $sql, 3);
					$serverId=substr($server, 1);
					if (!$ret['error'] && $ret['ret']['data']){
						$col="sid,";
						$colVal="$serverId,";
						$dupVal="sid=$serverId,";
						foreach ($dbIndex as $index){
							$col.=$index.',';
							$colVal.=$ret['ret']['data'][0][$index]?("'{$ret['ret']['data'][0][$index]}',"):("'',");
							$dupVal.=$ret['ret']['data'][0][$index]?("$index='{$ret['ret']['data'][0][$index]}',"):("$index='',");
						}
						$col.='operateTime';
						$colVal.=$nowTime;
						$dupVal.="operateTime=$nowTime";
						$sql="insert into refund_info($col) values($colVal) ON DUPLICATE KEY UPDATE $dupVal;";
						$res = mysqli_query($link,$sql);
					}
					mysqli_close($link);
				}
			}
		}
		exit($reason);
	}else{
		exit($ret);
	}
}

if($_REQUEST['event']=='update'){
	$paymentid = $_REQUEST ['paymentid'];
	$reason = $_REQUEST ['reason'];
	$ret = file_get_contents("https://p2cok.elexapp.com/COKFB/cokfbpay/handle_dispute.php?cmd=update&paymentid=$paymentid&reason=$reason");
	if ($ret=='OK') {
		exit($reason);
	}else{
		exit($ret);
	}
}

$type = $_REQUEST ['action'];
if ($_REQUEST ['orderStatus'])
	$orderStatus = $_REQUEST ['orderStatus'];
if ($_REQUEST ['orderId'])
	$orderId = $_REQUEST ['orderId'];
if ($_REQUEST ['fbuid'])
	$fbuid = $_REQUEST ['fbuid'];
$flag=false;
if ($type == 'view') {
	$orderStatus = $_REQUEST ['orderStatus'];
	if($orderStatus=='pending'){
		$flag=true;
	}else {
		$flag=false;
	}
	$orderId = $_REQUEST ['orderId'];
	$fbuid = $_REQUEST ['fbuid'];
	if($fbuid){
		$sql = "select * from cokfb.dispute where fbuid='$fbuid';";
	}else if($orderId){
		$sql = "select * from cokfb.dispute where paymentid='$orderId';";
	}else {
		$sql = "select * from cokfb.dispute where status='$orderStatus';";
	}
	$link=mysql_connect('10.86.79.7', 'root', 'DBPWD');
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	$res = mysql_query($sql,$link);
	$data=array();
	while ($row = mysql_fetch_assoc($res)){
		$data[$row['paymentid']]['fbuid']=$row['fbuid'];
		$data[$row['paymentid']]['name']=urldecode($row['name']);
		$data[$row['paymentid']]['amount']=$row['amount'];
		$data[$row['paymentid']]['currency']=$row['currency'];
		$data[$row['paymentid']]['charge_time']=$row['charge_time'];
		$data[$row['paymentid']]['country']=$row['country'];
		$data[$row['paymentid']]['status']=$row['status'];
		$data[$row['paymentid']]['dispute_time']=$row['dispute_time'];
		$data[$row['paymentid']]['user_comment']=urldecode($row['user_comment']);
		$data[$row['paymentid']]['user_email']=$row['user_email'];
		$data[$row['paymentid']]['reason']=$row['reason']?$row['reason']:'';
	}
	mysql_close($link);
	if($data){
		$showData = true;
	}else {
		$headAlert = '无订单';
	}
}


include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>