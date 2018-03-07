<?php
!defined('IN_ADMIN') && exit('Access Denied');
include ADMIN_ROOT . '/language/exchangeLang.php';

$exceptGold=false;
$type = $_REQUEST['action'];
if($_REQUEST['uid'])
	$uid = $_REQUEST['uid'];
if($_REQUEST['orderId'])
	$orderId = $_REQUEST['orderId'];
//$seletedpf支付渠道
if (!$_REQUEST['selectPayMethod']) {
	$payPf = 'all';
}else{
	$payPf = $_REQUEST['selectPayMethod'];
}
unset($optionsArr['all']);//去掉默认的all值,防止忘记选择
foreach ($optionsArr as $pf => $pfdisp){
	$flag = ($payPf==$pf)?'selected="selected"':'';
	$pfOptions .= "<option value='{$pf}' $flag>{$pfdisp}</option>";
}
$member = array('qinbinbin','maxiaoyu','chenhounan','dongbochen','admin');
if(!in_array($_COOKIE['u'],$member)){
	exit();
}


if($_REQUEST['packageId'])
	$packageId = $_REQUEST['packageId'];
if($_REQUEST['money'])
	$money = $_REQUEST['money'];
$moneArr = array(
	'0.99' =>'0.99',
	'4.99' =>'4.99',
	'9.99' =>'9.99',
	'19.99' =>'19.99',
	'24.99' =>'24.99',
	'49.99' =>'49.99',
	'99.99' =>'99.99',
);
foreach ($moneArr as $mkey =>$mValue){
	$mFlag = ($money==$mkey)?'selected="selected"':'';
	$moneyOption .= "<option value='{$mkey}' $mFlag>{$mValue}</option>";
}

if ($type=='pay') {
	$uid = trim($_REQUEST['uid']);
	$account_list = cobar_getAccountInfoByGameuids($uid);
	$server = 's'.$account_list[0]['server']; //只看第一个uid的server; 也就是说所有uid必须在一个服
	$orderId = trim($_REQUEST['orderId']);
	$payPf = trim($_REQUEST['selectPayMethod']);
	$packageId = trim($_REQUEST['packageId']);
	$money = trim($_REQUEST['money']);
	if($_REQUEST['exceptGold']){
		$exceptGold =true;  //只发道具
	}
	$productArray = getPackageInfo();
	$tempArray=array();
	$props='';
	$flag=false;
	foreach ($productArray as $valueArray){
		if($valueArray['id']==$packageId){
			if(!$exceptGold){//不是只发道具的话,会把金币也发过去
				$props='gold,0,'.$valueArray['gold_doller'].'|';
			}
			if($valueArray['item']){
				$tempArray=explode('|', $valueArray['item']);
				foreach ($tempArray as $tempValue){
					$value=explode(';', $tempValue);
					$props.='goods,'.$value[0].','.$value[1].'|';
				}
			}
			$props=trim($props,'|');
			$productType=$valueArray['type'];
			$flag =true;
			break;
		}
	}
	if(!$props){
		$headAlert="礼包 $packageId 信息获取失败";
	}else {
		$sql="select level,lang from userprofile where uid='$uid';";
		$result=$page->executeServer($server, $sql, 3);
		$lang='';
		if(!$result['error'] && $result['ret']['data']){
			$payLevel=$result['ret']['data'][0]['level'];
			$lang=$result['ret']['data'][0]['lang'];
		}
		if (empty($lang) || (!isset($contentsArray[$lang]))){
			$lang='en';
		}
		
		
		if($productType==5){
			$buyTime=strtotime(date('Ymd',time()))*1000;
			$sql="insert into monthly_card(uid,itemId,accept,time,available,buyTime) values('$uid','$packageId',0,0,1,$buyTime) ON DUPLICATE KEY UPDATE accept=0,time=0,available=1,buyTime=$buyTime;";
			$result=$page->executeServer($server, $sql, 2);
			$time=time()*1000;
			$sql="insert into paylog(uid,orderId,pf,productId,time,spend,payLevel) values('$uid','$orderId','$payPf','$packageId',$time,$money,$payLevel);";
			$result=$page->executeServer($server, $sql, 2);
		}else {
			$ret = $page->webRequest("refund",array('uid'=>$uid,'orderId'=>$orderId,'refund'=>$props,'type'=>1),$server);
			if($ret != 'ok'){
				$html="调用Java接口补发道具失败!";
			}else{

				$time=time()*1000;
				$sql="insert into paylog(uid,orderId,pf,productId,time,spend,payLevel) values('$uid','$orderId','$payPf','$packageId',$time,$money,$payLevel);";	
				$page->executeServer($server, $sql, 2);
				if($productType==3){
					$sql="insert into exchange(uid,id) values('$uid','$packageId');";
					$page->executeServer($server, $sql, 2);
				}
			}
		}
		$sendBy = $page->getAdmin();
		$sendTime = microtime(true)*1000;
		$title = addslashes($titleArray[$lang]['1']);
		$contents = addslashes(sprintf($contentsArray[$lang]['1'],$orderId));
		$mailUid = md5($uid.$server.$orderId.time());
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0,2)";
		$result2=$page->executeServer($server, $sql, 2);
		sendReward2($mailUid,$server);
		adminLogUser($adminid, $uid, $server, array('refund_orderId'=>$orderId,'productId'=>$packageId));
		$html="订单号为:{$orderId} 的礼包，已经补发给{$uid}玩家,并且已向玩家发送邮件通知!";
	}
}

if ($type=='batchPay') {
	$html='';
	if($_REQUEST['exceptGold']){
		$exceptGold =true;
	}
	
	//信息格式:uid1,订单号1,支付渠道1,礼包id1|uid2,订单号2,支付渠道2,礼包id2|...
	$infoStr = $_REQUEST ['serverAndorderIds'];
	$infoStr = trim ( $infoStr, '|' );
	$temp1 = explode ( '|', $infoStr );
	foreach ( $temp1 as $row ) {
		$temp2 = explode ( ',', $row );
		$uid = $temp2 [0];
		$orderId = $temp2 [1];
		$payPf = $temp2 [2];
		$packageId = $temp2 [3];
		$dollar=0;
		
		//玩家uid所在服
		$account_list = cobar_getAccountInfoByGameuids($uid);
		$server = 's'.$account_list[0]['server'];
		
		$productArray = getPackageInfo();
		$tempArray = array ();
		$props = '';
		$flag = false;
		foreach ( $productArray as $valueArray ) {
			if ($valueArray ['id'] == $packageId) {
				$dollar=$valueArray['dollar'];
				if(!$exceptGold){
					$props='gold,0,'.$valueArray['gold_doller'].'|';
				}
				if ($valueArray ['item']) {
					$tempArray = explode ( '|', $valueArray ['item'] );
					foreach ( $tempArray as $tempValue ) {
						$value = explode ( ';', $tempValue );
						$props .= 'goods,' . $value [0] . ',' . $value [1] . '|';
					}
				}
				$props = trim ( $props, '|' );
				$productType = $valueArray ['type'];
				$flag = true;
				break;
			}
		}
		
		
		if(!$props){
			$headAlert="礼包 $packageId 信息获取失败";
		}else {
			$sql="select level,lang from userprofile where uid='$uid';";
			$result=$page->executeServer($server, $sql, 3);
			$lang='';
			if(!$result['error'] && $result['ret']['data']){
				$payLevel=$result['ret']['data'][0]['level'];
				$lang=$result['ret']['data'][0]['lang'];
			}
			if (empty($lang) || (!isset($contentsArray[$lang]))){
				$lang='en';
			}
		
			if($productType==5){ //月卡
				$buyTime=strtotime(date('Ymd',time()))*1000;
				$sql="insert into monthly_card(uid,itemId,accept,time,available,buyTime) values('$uid','$packageId',0,0,1,$buyTime) ON DUPLICATE KEY UPDATE accept=0,time=0,available=1,buyTime=$buyTime;";
				$result=$page->executeServer($server, $sql, 2);
				$time=time()*1000;
				$sql="insert into paylog(uid,orderId,pf,productId,time,spend,payLevel) values('$uid','$orderId','$payPf','$packageId',$time,$dollar,$payLevel);";
				$result=$page->executeServer($server, $sql, 2);
			}else { //3一次性促销
				$ret = $page->webRequest("refund",array('uid'=>$uid,'orderId'=>$orderId,'refund'=>$props,'type'=>1),$server);
				if($ret != 'ok'){
					$html.="给玩家$uid补发漏单时,调用Java接口补发道具失败!<br>";
				}else{
		
					$time=time()*1000;
					$sql="insert into paylog(uid,orderId,pf,productId,time,spend,payLevel) values('$uid','$orderId','$payPf','$packageId',$time,$dollar,$payLevel);";
					$page->executeServer($server, $sql, 2);
					if($productType==3){
					$sql="insert into exchange(uid,id) values('$uid','$packageId');";
					$page->executeServer($server, $sql, 2);
					}
				}
			}
			$sendBy = $page->getAdmin();
			$sendTime = microtime(true)*1000;
			$title = addslashes($titleArray[$lang]['1']);
			$contents = addslashes(sprintf($contentsArray[$lang]['1'],$orderId));
					$mailUid = md5($uid.$server.$orderId.time());
					$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0,2)";
					$result2=$page->executeServer($server, $sql, 2);
					sendReward2($mailUid,$server);
					adminLogUser($adminid, $uid, $server, array('refund_orderId'=>$orderId,'productId'=>$packageId));
			$html.="订单号为:{$orderId} 的礼包，已经补发给{$uid}玩家,并且已向玩家发送邮件通知!<br>";
		}
	}
}

function sendReward($mailUid) {
	$page = new BasePage ();
	$page->webRequest ( 'sendmail', array (
			'uid' => $mailUid
	) );
}
function sendReward2($mailUid,$serv){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid),$serv);
}

function getPackageInfo(){
// 	$filePath='/usr/local/cok/SFS2X/resource/GMstatistics/exchange.xml';
// 	if((!file_exists(ADMIN_ROOT . '/language/package.php'))||(filemtime(ADMIN_ROOT . '/language/package.php')<filemtime($filePath))){
// 		writePackageInfo();
// 	}
	$exchageXml = loadXml('exchange','exchange');
	return  $exchageXml;
}
/*
 function writePackageInfo() {
 	$filePath = '/usr/local/cok/SFS2X/resource/GMstatistics/exchange.xml';
 	if (file_exists ( $filePath )) {
 		$xml = ( array ) simplexml_load_file ( $filePath );
 		$array1 = ( array ) $xml ['Group'];
 		foreach ( $array1 ['ItemSpec'] as $x ) {
 			$array2 = ( array ) $x;
 			$array3 [] = $array2 ['@attributes'];
 		}
 		$strarr = var_export ( $array3, true );
 		file_put_contents ( ADMIN_ROOT . '/language/refound/package.php', "<?php\n \$productArray= " . $strarr . ";\nreturn \$productArray;\n?>" );
 	} else {
 		exit ( 'Failed to open /usr/local/cok/SFS2X/resource/GMstatistics/exchange.xml' );
 	}
 }
 */
include( renderTemplate("{$module}/{$module}_{$action}") );
?>