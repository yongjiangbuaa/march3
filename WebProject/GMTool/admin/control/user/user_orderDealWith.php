<?php
!defined('IN_ADMIN') && exit('Access Denied');
$headLine = "根据订单号和设备uid(或fbuid)查询玩家及相应的订单信息";
$headAlert = "";
$showData = false;
$eventNames = $goldLink;
$lang = loadLanguage();
$clientXml = loadXml('goods','goods');
if($_REQUEST['orderId'])
	$orderId = trim($_REQUEST['orderId']);
if($_REQUEST['deviceId'])
	$deviceId = trim($_REQUEST['deviceId']);
if($_REQUEST['uid'])
    $uid = trim($_REQUEST['uid']);
if ($_REQUEST['analyze']=='user') {
	if(empty($orderId) || (empty($deviceId) && empty($uid))){
		$headAlert='订单号和设备id不能为空!';
		return ;
	}

	if($deviceId){
    	$result = cobar_getValidAccountList('device', $deviceId);
    	if (empty($result)){
    		$result = cobar_getValidAccountList('facebook', $deviceId);
    	}
	} else {
	    $result = cobar_getAccountInfoByGameuids($uid);
	}

	if(count($result) < 1){
		var_dump($result);
		$headAlert = 'no data';
	}
	
	$log = array();
	$title=array();
	$i=1;
	foreach ($result as $curRow)
	{
		$data = $curRow;
		$logItem=array();
		$logItem['服务器'] = 's'.$data['server'];
		$logItem['UID'] = $data['gameUid'];
		$logItem['名字'] = $data['gameUserName'];
		$logItem['等级'] = $data['gameUserLevel'];
		$logItem['上次登录时间'] = date('Y-m-d H:i:s',$data['lastTime']/1000);
		$logItem['设备ID'] = $data['deviceId'];
		$logItem['绑定Google账户'] = $data['googleAccount'];
		$logItem['绑定Facebook账户'] = $data['facebookAccount'];
		if ($data['pf']=='AppStore'){
			$logItem['绑定IOS账户'] = $data['pfId']?$data['pfId']:'';
		}else {
			$logItem['绑定IOS账户'] = '';
		}
		if($data['active'] == 0){
			$logItem['状态'] = '激活';
		}else if ($data['active'] == 1){
			$logItem['状态'] = '冻结';
		}else{
			$logItem['状态'] ='new game';
		}
			
		$banSql="select banTime from userprofile where uid='".$data['gameUid']."';";
		$banRet=$page->executeServer($logItem['服务器'], $banSql, 3);
		$banTime=0;
		if (!$banRet['error'] && $banRet['ret']['data']){
			$banTime=$banRet['ret']['data'][0]['banTime'];
			if ($banTime && ($banTime>(time()*1000))){
				$banStateSql="select operator,reason,opeDate from banTime_reason where serverId='".$logItem['服务器']."' and uid='".$data['gameUid']."';";
				$ret=$page->globalExecute($banStateSql, 3);
				if (!$ret['error'] && $ret['ret']['data']){
					$logItem['封号详情']='<strong>已封号!</strong><br>封号原因:'.$ret['ret']['data'][0]['reason'].'<br>操作人:'.$ret['ret']['data'][0]['operator'].'<br>操作时间:'.$ret['ret']['data'][0]['opeDate'];
				}else{
					$logItem['封号详情']='封号信息查询出错';
				}
			}else {
				$logItem['封号详情']='当前没有被封号';
			}
		}else {
			$logItem['封号详情']='';
		}
		$log[$data['gameUid']] = $logItem;
		if ($i==1){
			foreach ($logItem as $k=>$v){
				$title[]=$k;
			}
		}
		$i++;
	}
	$payLog=array();
	if ($log){
		foreach ($log as $dbVal){
			$currUid=$dbVal['UID'];
			$currSer=$dbVal['服务器'];
			
			$sql = "select p.uid,p.orderId,p.orderInfo,p.pf ppf,p.time,p.receiverId,p.payLevel,p.spend,p.productId,u.name,u.level nowLevel,u.regTime,r.country,r.pf rpf from paylog p inner JOIN userprofile u ON p.uid = u.uid  inner join stat_reg r on p.uid=r.uid where p.orderId='$orderId' and p.uid='$currUid';";
			//echo $sql;
			$result = $page->executeServer($currSer, $sql, 3);
			$result = $result['ret']['data'];
			foreach ($result as $curRow)
			{
				$data = $curRow;
				$logItem=array();
				$logItem['时间'] = date('Y-m-d H:i:s',$data['time']/1000);
				$logItem['订单号'] = $data['orderId'];
				$logItem['订单信息'] = $data['orderInfo'];
				$logItem['支付渠道'] = $data['ppf'];
				$logItem['用户'] = $data['uid'];
				$logItem['名字'] = $data['name'];
				$logItem['送给用户'] = $data['receiverId'];
				$logItem['国家'] = $data['country'];
				$logItem['平台'] = $data['rpf'];
				$logItem['注册时间'] = date('Y-m-d H:i:s',$data['regTime']/1000);
				$logItem['当前等级'] = $data['nowLevel'];
				$logItem['支付等级'] = $data['payLevel'];
				$logItem['支付金额'] = $data['spend'];
				$logItem['购买物品'] = $data['productId'];
				$payLog[] = $logItem;
			}
		}
	}
	
	$packLog=array();
	if ($payLog){
		foreach ($payLog as $pVal){
			$packageId=$pVal['购买物品'];
			
			$productArray = getPackageInfo ();
			$tempArray = array ();
			foreach ( $productArray as $valueArray ) {
				if ($valueArray ['id'] == $packageId) {
					$logItem=array();
						$logItem['金币']=$valueArray['gold_doller'];
					if ($valueArray ['item']) {
						$tempArray = explode ( '|', $valueArray ['item'] );
						foreach ( $tempArray as $tempValue ) {
							$value = explode ( ';', $tempValue );
							$logItem[$lang[(int)$clientXml[$value [0]]['name']].'|'.$value [0]]=$value [1];
						}
					}
					$packLog[]=$logItem;
					break;
				}
			}
		}
		
		$goldLog=array();
		$itemLog=array();
		foreach ($payLog as $gVal){
			$guid=$gVal['用户'];
			$ser=$log[$guid]['服务器'];
			$startTime=strtotime(date('Ymd H:00:00',strtotime($gVal['时间'])))*1000;
			$endTime=strtotime(date('Ymd H:00:00',strtotime($gVal['时间'])))*1000+3600000;
			
			$sql="select g.*,u.uid as userUid,u.name as userName from gold_cost_record g inner join userprofile u on g.userId = u.uid where u.uid='$guid' and g.time>=$startTime and g.time <=$endTime;";
			$result=$page->executeServer($ser, $sql, 3);
			foreach ($result['ret']['data'] as $curRow){
				$logItem=array();
				$data = $curRow;
				$logItem['时间'] = date('Y-m-d H:i:s',$data['time']/1000);
				$logItem['用户UID'] = $data['userUid'];
				$logItem['用户'] = $data['userName'];
				$logItem['金币类型'] = $curRow['goldType']?'充值金币':'赠送';
				$logItem['类型'] = $eventNames[$curRow['type']];
				if($data['param1'] && $lang[(int)$clientXml[$data['param1']]['name']]){
					$logItem['参数1'] = $lang[(int)$clientXml[$data['param1']]['name']];
				}
				else{
					$logItem['参数1'] = $data['param1'];
				}
				
				$logItem['参数2'] = $data['param2'];
				$logItem['变化前'] = $data['originalGold'];
				$logItem['变化值'] = $data['cost'];
				$logItem['变化后'] = $data['remainGold'];
				$goldLog[] = $logItem;
			}
			//2016-08-05 之前也要查询goods_cost_record表
			if(strtotime($gVal['时间']) < strtotime('2016-08-05 ') ) {
				$sql = "select time,userId,itemId,original,cost,remain from goods_cost_record where userId='$guid' and time>=$startTime and time <=$endTime;";
				$result = $page->executeServer($ser, $sql, 3);
				foreach ($result['ret']['data'] as $curRow) {
					$logItem = array();
					$data = $curRow;
					$logItem['时间'] = date('Y-m-d H:i:s', $data['time'] / 1000);
					$logItem['用户UID'] = $data['userId'];
					$logItem['物品ID'] = $data['itemId'];
					$logItem['物品名称'] = $lang[(int)$clientXml[$data['itemId']]['name']];
					$logItem['变化前'] = $data['original'];
					$logItem['变化值'] = $data['cost'];
					$logItem['变化后'] = $data['remain'];
					$itemLog[] = $logItem;
				}
			}else {

				$sn = date("Ym", $startTime / 1000) ;
				$m = date("Ym", $endTime / 1000);
				for ($j = $sn; $j <= $m; $j++) {
					$table = "goods_cost_record_" .$j ;
					$sql = "select time,userId,itemId,original,cost,remain from $table where userId='$guid' and time>=$startTime and time <=$endTime;";
					$result = $page->executeServer($ser, $sql, 3);
					foreach ($result['ret']['data'] as $curRow) {
						$logItem = array();
						$data = $curRow;
						$logItem['时间'] = date('Y-m-d H:i:s', $data['time'] / 1000);
						$logItem['用户UID'] = $data['userId'];
						$logItem['物品ID'] = $data['itemId'];
						$logItem['物品名称'] = $lang[(int)$clientXml[$data['itemId']]['name']];
						$logItem['变化前'] = $data['original'];
						$logItem['变化值'] = $data['cost'];
						$logItem['变化后'] = $data['remain'];
						$itemLog[] = $logItem;
					}
				}
			}
		}
	}
	
	
	
	if($log){
		$showData=true;
	}else {
		$headAlert="没有相关数据";
	}
}

function getPackageInfo(){
	$a = require ADMIN_ROOT . '/language/refound/package.php';
	return  $a;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>