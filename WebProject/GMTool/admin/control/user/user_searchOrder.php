<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
global $servers;
foreach ($_REQUEST as $server=>$value)
{
	if($servers[$server] && $value == 'on'){
		$selectServer[] = $server;
	}
}

$type = $_REQUEST['action'];
if($_REQUEST['orderId'])
	$orderId = $_REQUEST['orderId'];
if ($type=='view') {
	$orderId = $_REQUEST['orderId'];
	$data=array();
	foreach ($selectServer as $server){
		$sql = "select uid,orderId,pf,productId,time,spend,payLevel,status from paylog where orderId='$orderId';";
		$result = $page->executeServer($server, $sql, 3);
		foreach ($result['ret']['data'] as $curRow){
			$data[$server]['uid']=$curRow['uid'];
			$data[$server]['orderId']=$curRow['orderId'];
			$data[$server]['pf']=$curRow['pf'];
			//$data[$server]['productId']=$curRow['productId'];
			$data[$server]['packageName']=$exchangeName[$curRow['productId']][0];
			$data[$server]['time']=date('Y-m-d H:i:s',($curRow['time']/1000));
			$data[$server]['spend']=$curRow['spend'];
			$data[$server]['payLevel']=$curRow['payLevel'];
			$data[$server]['status']=($curRow['status']==4)?'是':'否';
		}
	}
	if($data){
		$showData=true;
	}else {
		$headAlert="订单信息查询失败";
	}
	$start=date('Y-m-d H:i:s',$start);
	$end=date('Y-m-d H:i:s',$end);
}
function getPackageInfo(){
// 	$filePath='/usr/local/cok/SFS2X/resource/exchange.xml';
// 	if((!file_exists(ADMIN_ROOT . '/language/package.php'))||(filemtime(ADMIN_ROOT . '/language/package.php')<filemtime($filePath))){
// 		writePackageInfo();
// 	}
	$a = require ADMIN_ROOT . '/language/package.php';
	return  $a;
}
/*
 function writePackageInfo(){
 	$filePath='/usr/local/cok/SFS2X/resource/exchange.xml';
 	if (file_exists($filePath)) {
 		$xml = (array)simplexml_load_file($filePath);
 		$array1=(array)$xml['Group'];
 		foreach ($array1['ItemSpec'] as $x){
 			$array2=(array)$x;
 			$array3[]=$array2['@attributes'];
 		}
 		$strarr = var_export($array3,true);
 		file_put_contents(ADMIN_ROOT . '/language/package.php', "<?php\n \$productArray= ".$strarr.";\nreturn \$productArray;\n?>");
 	} else {
 		exit('Failed to open test.xml.');
 	}
 }
*/

include( renderTemplate("{$module}/{$module}_{$action}") );
?>