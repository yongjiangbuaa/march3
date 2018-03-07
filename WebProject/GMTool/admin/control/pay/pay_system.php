<?php
!defined('IN_ADMIN') && exit('Access Denied');
include ADMIN_ROOT.'/include/pay/payment.php';
$startDate = $_REQUEST['startDate'] ? $_REQUEST['startDate'] : date("Y-m-d",time()-86400*3);
$endDate = $_REQUEST['endDate'] ? $_REQUEST['endDate'] : date("Y-m-d 23:59:59",time());
if($_REQUEST['action'] == 'query'){
	try {
		$queryPage = $_REQUEST['page'];
		$onlyFail = $_REQUEST['onlyFail'];
		$payment = payment::singleton();
		$pageLimit = 10;
		$pageIndex = $pageLimit * ($queryPage - 1);
		$payRecords = $payment->getPayRecords($startDate,$endDate,$pageIndex,$pageLimit,$onlyFail);
		showRecords($payRecords);
		$paycount = $payment->getSumRecords($startDate,$endDate,$onlyFail);
		$pager = page($paycount, $queryPage, $pageLimit);
		if($pager['pager'])
			echo "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	exit;
}
if($_REQUEST['action'] == 'dropped'){
	$msg = '';
	try {
		$payment = payment::singleton();
		$dbInfo = json_decode(base64_decode($_REQUEST['dbInfo']),true);
		$payUid = $dbInfo[0];
		$payOrderId = $dbInfo[1];
		$payMethod = $dbInfo[2];
		adminLogUser ( $adminid, $payUid, '', array (
				'action' => 'paymentcallback',
				'uid' => $payUid,
				'orderId' => $payOrderId,
				'method' => $payMethod
		) );
// 		$payUid = '819338121000602';
// 		$payOrderId = '21144068957511625429';
// 		$payMethod = '/cn_mi';
		$callResult = $payment->redoCallback($payUid,$payOrderId,$payMethod);
		if($callResult[0] == 1){
			echo '<td>成功</td>';
			exit;
		}
		$msg = $callResult[1];
	} catch (Exception $e) {
		$msg = $e->getMessage();
	}
	echo "<font color='red'>$msg</font>";
	exit;
}

if($_REQUEST['action'] == 'detail'){
	try {
		$payment = payment::singleton();
		$dbInfo = json_decode(base64_decode($_REQUEST['dbInfo']),true);
		$payUid = $dbInfo[0];
		$payOrderId = $dbInfo[1];
		$payMethod = $dbInfo[2];
		$callResult = $payment->showDetail($payUid,$payOrderId,$payMethod);
		$callResult['request'] = json_decode($callResult['request'],true);
		$callResult['postParam'] = json_decode($callResult['postParam'],true);
		echo '<div class="alert alert-info"><strong>'.search($callResult).'</strong></div>';
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	exit;
}

if($_REQUEST['action'] == 'manual'){
	try {
		$payment = payment::singleton();
		$dbInfo = json_decode(base64_decode($_REQUEST['dbInfo']),true);
		$payUid = $dbInfo[0];
		$payOrderId = $dbInfo[1];
		$payMethod = $dbInfo[2];
		$payment->runManual($payUid,$payOrderId,$payMethod);
		echo '<td>手动处理</td>';
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	exit;
}

function showRecords($payRecords){
	foreach ($payRecords as $payRecord)
	{
		$logItem = array();
		$logItem['orderId'] = $payRecord['realOrderId'];
		$logItem['time'] = date('Y-m-d H:i:s',$payRecord['time']/1000);
		$logItem['uid'] = $payRecord['uid'];
		$logItem['method'] = $payRecord['method'];
// 		$logItem['param1'] = $payRecord['request'];
// 		$logItem['param2'] = $payRecord['postParam'];
		$logItem['orderInfo'] = $payRecord['orderInfo'];
		if($payRecord['checkResult'] == 1){
			$logItem['checkResult'] = '成功';
		}else if($payRecord['checkResult'] == 2){
			$logItem['checkResult'] = '手动处理';
		}else{
			$logItem['checkResult'] ='<font color=\'red\'>失败</font>';
		}
		$dbInfo = base64_encode(json_encode(array(
						$payRecord['uid'],
						$payRecord['orderId'],
						$payRecord['method'],
				)));
		$logItem['info'] = array(
				'dbInfo'=>$dbInfo,
				'checkTimes'=>$payRecord['checkTimes'],
				'checkResult'=>$payRecord['checkResult'],
		);
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:100%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 0;
	foreach ($log as $curRow)
	{
		$dbInfo = $curRow['info']['dbInfo'];
		$checkTimes = $curRow['info']['checkTimes'];
		$checkResult = $curRow['info']['checkResult'];
		unset($curRow['info']);
		if(!$title){
			$title = array(
				'orderId'=>'订单号'	,
				'time'=>'数据导入时间',
				'uid'=>'uid',
				'method'=>'支付方式',
				'orderInfo'=>'关键参数',
				'checkResult'=>'回调结果',
			);
			foreach ($curRow as $key=>$value)
				$html .= "<th style='text-align:center;'>" . $title[$key] . "</th>";
			$html .= "<th></th></tr>";
			$title = true;
		}
		$i++;
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($curRow as $key=>$value){
			if($key == 'checkResult')
				$html .= "<td id='result_$i'>" . $value . "</td>";
			else
				$html .= "<td>" . $value . "</td>";
		}
		$showDetail = "<input class='btn js-btn btn-primary' type='button' value='查看详细数据' onclick=showDetail('$dbInfo')>";
		$redoCallback  = "<input class='btn js-btn btn-primary' type='button' value='尝试重新回调' onclick=redoCallback('$i','$dbInfo')>";
		$runManual  = "<input class='btn js-btn btn-primary' type='button' value='标记为手动处理' onclick=runManual('$i','$dbInfo')>";
		if($checkResult == 1){
			$html .= "<td>$showDetail</td>";
		}else if($checkTimes >= 5){
			$html .= "<td id='call_$i'>$redoCallback $showDetail $runManual</td>";
		}else{
			$html .= "<td id='call_$i'>$redoCallback $showDetail</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>