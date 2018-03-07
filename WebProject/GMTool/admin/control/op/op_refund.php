<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
include ADMIN_ROOT . '/language/exchangeLang.php';

set_time_limit(0);

$productArray = getPackageInfo ();

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
global $servers;
$type = $_REQUEST ['action'];
if ($_REQUEST ['orderId'])
	$orderId = $_REQUEST ['orderId'];
if ($_REQUEST ['serverAndorderIds'])
	$serverAndorderIds = $_REQUEST ['serverAndorderIds'];
if ($_REQUEST ['uid'])
	$uid = $_REQUEST ['uid'];
$uid=trim($uid);
if ($_REQUEST ['startDate']){
	$startDate = $_REQUEST ['startDate'];
}else{
	$startDate = date("Y-m-d",time()-86400*7);
}
$readfail = $_REQUEST['readfail'];

$startTime=strtotime($startDate)*1000;
/* echo $startTime; */
if ($type == 'view') {
	$showBtn = false;
	$refundStr = '';
	$orderId = $_REQUEST ['orderId'];
	$serverAndorderIds = $_REQUEST ['serverAndorderIds'];
	$data = array ();

	if($uid){
		$lang = loadLanguage();
		$clientXml = loadXml('goods','goods');

		$btnFlag = false;
		$account_list = cobar_getAccountInfoByGameuids($uid);

		$server="s".$account_list[0]["server"];

		//现有的物品
		$sql = "select * from user_item where ownerId = '$uid'";
		$result = $page->executeServer ( $server, $sql, 3 ,true);
		$goodList = array();
		if(!$result['error'] && $result['ret']['data']){
			foreach ($result['ret']['data'] as $key => $item) {
				$goodList[$item['itemId']] = $item['count'];
			}
		}

		/* echo $server; */
		$where = '';
		if($readfail == true){
			$sql = " select * from google_pay_check where uid='$uid' ";
			$ret = cobar_query_global_db_cobar($sql);
			$tmpArr = array();
			$googlePaycheckArr = array();
			foreach($ret as $row){
				$tmpArr[] = $row['orderId'];
				$googlePaycheckArr[$row['orderId']] = $row;
			}
			$where = 'and orderId in ' . '(\'' . implode('\',\'',$tmpArr) .'\') ';

		} else {
			$where = " and time>'$startTime' ";
		}
		$sql = "select uid,orderId,pf,productId,time,spend,payLevel,status from paylog where uid='$uid' $where order by time desc;";
		$result = $page->executeServer ( $server, $sql, 3 );
		if(in_array($_COOKIE['u'],$privilegeArr)){
			echo $sql.PHP_EOL;
		}
		$uidList[$server][] = $uid;
		/* print_r($data); */

		/* print_r($result); */
		/* print_r($result); */
		foreach ( $result ['ret'] ['data'] as $curRow ) {
			$ordId = $curRow ['orderId'];
			$data [$server] [$ordId] ['btn'] = false;
			$data [$server] [$ordId] ['uid'] = $curRow ['uid'];
			$data [$server] [$ordId] ['orderId'] = $curRow ['orderId'];
			$data [$server] [$ordId] ['pf'] = $curRow ['pf'];
			$data [$server] [$ordId] ['productId'] = $curRow ['productId'];
			$data [$server] [$ordId] ['packageName'] = $exchangeName [$curRow ['productId']] [0];

			$data [$server] [$ordId]['ishave'] = 0;
			if(!empty($productArray[$curRow['productId']]['item'])){
				$tlist = explode('|',$productArray[$curRow['productId']]['item']);
				foreach($tlist as $value){
					$tlist2 = explode(';',$value);
					if($goodList[$tlist2[0]] < $tlist2[1]){
						$data[$server][$ordId]['ishave'] = 1;
						$data[$server][$ordId]['ishavemsg'] .= $lang[(int)$clientXml[$tlist2[0]]['name']].':'.$tlist2[1].'-'.intval($goodList[$tlist2[0]])."\r\n";
					}
					$data [$server] [$ordId]['packageItemName'] .= $lang[(int)$clientXml[$tlist2[0]]['name']].':'.$tlist2[1].'<br />';
				}

				//$data [$server] [$ordId]['packageItemName'] = substr($data [$server] [$ordId]['packageItemName'],0,-1);
			}

			$data [$server] [$ordId] ['packageItem'] = $productArray[$curRow ['productId']]['item'];
			$data [$server] [$ordId] ['time'] = date ( 'Y-m-d H:i:s', ($curRow ['time'] / 1000) );
			$data [$server] [$ordId] ['spend'] = $curRow ['spend'];
			$data [$server] [$ordId] ['payLevel'] = $curRow ['payLevel'];
			if($curRow ['status'] == 4 || $googlePaycheckArr[$curRow ['orderId']]){
				$data [$server] [$ordId] ['status'] = '是';
			}else{
				$data [$server] [$ordId] ['status'] = '否';
			}
//			$data [$server] [$ordId] ['status'] = ($curRow ['status'] == 4) ? '是' : '否';
			if ($curRow ['status'] == 4) {
				// $showBtn=true;
				$data [$server] [$ordId] ['btn'] = true;
			}

			if ($curRow ['status'] == 3) {
				$data [$server] [$ordId] ['statusName']  = 1;
			}
		}
	}else {
		//if ($_COOKIE ['u'] == 'yaoduo') {
		// $serverAndorderIds="34:订单号1,订单号2,订单号3,订单号3,...
		// 35:订单号1,订单号2,订单号3,订单号3,...
		// 36:订单号1,订单号2,订单号3,订单号3,..."
		$btnFlag = true;
		$serverAndorderIds = str_replace ( '，', ',', $serverAndorderIds );
		$serverAndorderIds = str_replace ( '：', ':', $serverAndorderIds );
		$temp1 = explode ( "\n", $serverAndorderIds );
		foreach ( $temp1 as &$v ) {
			$v = trim ( $v, " \r" );
			$v = trim ($v);
		}


		foreach ( $temp1 as $row ) {
			$temp2 = explode ( ":", $row,2);
			$server = 's' . $temp2 [0];
			$orderIdArray = explode ( ",", $temp2 [1] );
			$orderIds = implode ( "','", $orderIdArray );

			$sql = " select * from google_pay_check where orderId in('$orderIds')";
			$ret = cobar_query_global_db_cobar($sql);
			$googlePaycheckArr = array();
			foreach($ret as $row1){
				$googlePaycheckArr[$row1['orderId']] = $row1;
			}

			$sql = "select uid,orderId,pf,productId,time,spend,payLevel,status from paylog where orderId in('$orderIds') order by payLevel desc;";
			$result = $page->executeServer ( $server, $sql, 3, true );
			foreach ( $result ['ret'] ['data'] as $curRow ) {
				$ordId = $curRow ['orderId'];
				$data [$server] [$ordId] ['btn'] = false;
				$data [$server] [$ordId] ['uid'] = $curRow ['uid'];
				$data [$server] [$ordId] ['orderId'] = $curRow ['orderId'];
				$data [$server] [$ordId] ['pf'] = $curRow ['pf'];
				$data [$server] [$ordId] ['productId'] = $curRow ['productId'];
				$data [$server] [$ordId] ['packageName'] = $exchangeName [$curRow ['productId']] [0];
				$data [$server] [$ordId] ['time'] = date ( 'Y-m-d H:i:s', ($curRow ['time'] / 1000) );
				$data [$server] [$ordId] ['spend'] = $curRow ['spend'];
				$data [$server] [$ordId] ['payLevel'] = $curRow ['payLevel'];
				if($curRow ['status'] == 4 || $googlePaycheckArr[$curRow ['orderId']]) {
					$data [$server] [$ordId] ['status'] = '是';
				}else{
					$data [$server] [$ordId] ['status'] = '否';
				}

				if ($curRow ['status'] == 4) {
					// $showBtn=true;
					$data [$server] [$ordId] ['btn'] = true;
					continue;
				}

				if ($curRow ['status'] == 3) {
					$data [$server] [$ordId] ['statusName']  = '未到账';
					continue;
				}

				$refundStr .= $server . ',' . $curRow ['uid'] . ',' . $curRow ['orderId'] . ',' . $curRow ['productId'] . ',' . date ( 'Y-m-d H:i:s', ($curRow ['time'] / 1000) ) . '|';

				$uidList[$server][] = $curRow['uid'];
			}
		}
		//}
	}

	foreach($uidList as $server=>$value){
		$str = '';
		foreach($value as $uid){
			$str = "'$uid',";
		}
		$str = substr($str,0,-1);

		$sqlRefund="select uid,count(orderId) nums,sum(spend) sumSpend from paylog where uid in ($str)and status=4 group by uid order by count(orderId) desc";
		$resultRefund=$page->executeServer($server, $sqlRefund, 3);

		if(empty($resultRefund['ret']['data'])) continue;

		foreach($resultRefund['ret']['data'] as $val){
			$dataRefund[$uid]['uid']=$val['uid'];
			$dataRefund[$uid]['nums']=$val['nums'];
			$dataRefund[$uid]['sumSpend']=$val['sumSpend'];
		}
	}

	if ($data) {
		$showData = true;
	} else {
		$headAlert = "订单信息查询失败";
	}
	if($dataRefund){
		$showDataRefund=true;
	}
}

if ($_REQUEST ['deductAll'] == 'all') {
	$infoStr = $_REQUEST ['infoStr'];
	$infoStr = trim ( $infoStr, '|' );
	$temp1 = explode ( '|', $infoStr );
	$refun_infoSql='';
	$global_paylog_sql='';
	$nowTime=time()*1000;
	$rewardXml = loadXml('reward',false);

	foreach ( $temp1 as $row ) {
		$temp2 = explode ( ',', $row );
		$server = $temp2 [0];
		$uid = $temp2 [1];
		$orderId = $temp2 [2];
		$productId = $temp2 [3];
		$time = $temp2 [4];

		$tempArray = array ();
		$props = '';
		$flag = false;
		foreach ( $productArray as $valueArray ) {
			if (intval($valueArray ['id']) == $productId) {
				$productType = $valueArray ['type'];
				if ($productType==5){

					$accept=0;
					$sql="select accept from  monthly_card where uid='$uid';";
					$ret=$page->executeServer($server, $sql, 3);
					if (!$ret['error'] && $ret['ret']['data']){
						$accept=$ret['ret']['data'][0]['accept'];
					}
					if ($accept>0){
						$type5money = intval($rewardXml[$productId]['gold']);
						$refundGold=$accept*$type5money;
						$props = 'gold,0,' . $refundGold . '|';
					}
				}else {
					$props = 'gold,0,' . $valueArray ['gold_doller'] . '|';
				}

				if ($valueArray ['item']) {
					$tempArray = explode ( '|', $valueArray ['item'] );
					foreach ( $tempArray as $tempValue ) {
						$value = explode ( ';', $tempValue );
						$props .= 'goods,' . $value [0] . ',' . $value [1] . '|';
					}
				}
				$props = trim ( $props, '|' );
				$flag = true;
				break;
			}
		}
		if (! $flag) {
			exit ( "礼包ID为" . $productId . "的礼包已经下线，无法扣除道具!" );
		} else {
			$ret = $page->webRequest ( "refund", array (
				'uid' => $uid,
				'orderId' => $orderId,
				'refund' => $props,
				'type' => 0
			), $server );
			if ($ret != 'ok') {
				exit ( "调用Java接口扣除道具失败!" );
			}
			$sql = "update paylog set status=4 where uid='$uid' and orderId='$orderId';";
			$page->executeServer ( $server, $sql, 2 );

			$global_paylog_sql .= "update global_paylog set status=4 where orderId='$orderId';";

			if ($productType == 3) {
				$sql = "DELETE FROM exchange WHERE uid = '$uid' AND id = '$productId'";
				$page->executeServer ( $server, $sql, 2 );
			}
			if ($productType == 5) {
				$sql = "DELETE FROM monthly_card WHERE uid = '$uid' AND itemId = '$productId'";
				$page->executeServer ( $server, $sql, 2 );
			}

			$sql="select p.uid uid,u.name name,u.lang lang,p.orderId orderId,p.productId productId,p.pf payPf,r.type type,u.regTime regTime,p.time payTime,p.spend spend,p.payLevel payLevel,p.deviceId payDeviceId,p.ip payIp,r.pf regPf,r.country country,r.ip regIp,r.time stat_regTime from paylog p inner join userprofile u on p.uid=u.uid inner join stat_reg r on p.uid=r.uid where p.uid='$uid' and p.orderId='$orderId' order by stat_regTime desc;";
			$ret=$page->executeServer($server, $sql, 3);
			$lang='';
			$serverId=substr($server, 1);
			if (!$ret['error'] && $ret['ret']['data']){
				$lang=$ret['ret']['data'][0]['lang'];

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
				$refun_infoSql.="insert into refund_info($col) values($colVal) ON DUPLICATE KEY UPDATE $dupVal;";
			}

			if (empty($lang) || (!isset($contentsArray[$lang]))){
				$lang='en';
			}

			$sendBy = $page->getAdmin ();
			$sendTime = microtime ( true ) * 1000;
			$title = mysql_escape_string ( $titleArray[$lang]['0'] );
			$contents = mysql_escape_string ( sprintf($contentsArray[$lang]['0'],$orderId,$time) );
			$mailUid = md5 ( $uid . $server . $orderId . time () );
			$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0)";
			$result2 = $page->executeServer ( $server, $sql, 2 );
			sendReward2 ( $mailUid, $server );
			adminLogUser ( $adminid, $uid, $server, array (
				'refund_orderId' => $orderId,
				'productId' => $productId
			) );
		}
	}
	if (!empty($refun_infoSql)){
		file_put_contents('/tmp/refun_infoSql.log', $refun_infoSql."\n",FILE_APPEND);
		file_put_contents('/tmp/refun_infoSql.log', $props."\n",FILE_APPEND);
		file_put_contents('/tmp/global_paylog_sql.log', $global_paylog_sql."\n",FILE_APPEND);
//		$link=mysqli_connect('10.43.212.86','root','t9qUzJh1uICZkA','global');
//		if (mysqli_multi_query($link, $refun_infoSql)) {
//			do {
//				/* store first result set */
//				if ($result = mysqli_store_result($link)) {
//					while ($row = mysqli_fetch_row($result)) {
//						//printf("%s\n", $row[0]);
//					}
//					mysqli_free_result($result);
//				}
//				/* print divider */
//				if (mysqli_more_results($link)) {
//					//printf("-----------------\n");
//				}
//			} while (mysqli_next_result($link));
//		}
//		if (mysqli_multi_query($link, $global_paylog_sql)) {
//			do {
//				/* store first result set */
//				if ($result = mysqli_store_result($link)) {
//					while ($row = mysqli_fetch_row($result)) {
//						//printf("%s\n", $row[0]);
//					}
//					mysqli_free_result($result);
//				}
//				/* print divider */
//				if (mysqli_more_results($link)) {
//					//printf("-----------------\n");
//				}
//			} while (mysqli_next_result($link));
//		}
//
//
//		mysqli_close($link);
	}

	exit ( '道具扣除成功,且已向玩家发送邮件通知!' );
//}
}

if ($_REQUEST ['deduct'] == 'deduct') {

	$server = $_REQUEST ['server'];
	$uid = $_REQUEST ['uid'];
	$orderId = $_REQUEST ['orderId'];
	$productId = $_REQUEST ['productId'];
	$time = $_REQUEST ['time'];
	$tempArray = array ();
	$props = '';
	$flag = false;
	$rewardXml = loadXml('reward',false);

	foreach ( $productArray as $valueArray ) {
		if (intval($valueArray ['id']) == $productId) {
			$productType = intval($valueArray ['type']);
			if ($productType==5){
				$accept=0;
				$sql="select accept from  monthly_card where uid='$uid';";
				$ret=$page->executeServer($server, $sql, 3);
				if (!$ret['error'] && $ret['ret']['data']){
					$accept=$ret['ret']['data'][0]['accept'];
				}

				if ($accept>0){
					$type5money = intval($rewardXml[$productId]['gold']);

					$refundGold=$accept*$type5money;
					$props = 'gold,0,' . $refundGold . '|';
				}
			}else {
				$props = 'gold,0,' . intval($valueArray ['gold_doller']) . '|';
			}
			if ($valueArray ['item']) {
				$tempArray = explode ( '|', $valueArray ['item'] );
				foreach ( $tempArray as $tempValue ) {
					$value = explode ( ';', $tempValue );
					$props .= 'goods,' . $value [0] . ',' . $value [1] . '|';
				}
			}
			$props = trim ( $props, '|' );

			$flag = true;
			break;
		}
	}
	if (! $flag) {
		exit ( "礼包ID为" . $productId . "的礼包已经下线，无法扣除道具!" );
	} else {
		$ret = $page->webRequest ( "refund", array (
			'uid' => $uid,
			'orderId' => $orderId,
			'refund' => $props,
			'type' => 0
		), $server );
		if ($ret != 'ok') {
			exit ( "调用Java接口扣除道具失败!" );
		}
//		$link=mysqli_connect('10.43.212.86','root','t9qUzJh1uICZkA','global');

		$nowTime=time()*1000;
		$sql = "update paylog set status=4 where uid='$uid' and orderId='$orderId';";
		$page->executeServer ( $server, $sql, 2 );

		$global_paylog_sql= "update global_paylog set status=4 where orderId='$orderId';";
//		$res = mysqli_query($link,$sql);
		if ($productType == 3) {
			$sql = "DELETE FROM exchange WHERE uid = '$uid' AND id = '$productId'";
			$page->executeServer ( $server, $sql, 2 );
		}
		if ($productType == 5) {
			$sql = "DELETE FROM monthly_card WHERE uid = '$uid' AND itemId = '$productId'";
			$page->executeServer ( $server, $sql, 2 );
		}

		$sql="select p.uid uid,u.name name,u.lang lang,p.orderId orderId,p.productId productId,p.pf payPf,r.type type,u.regTime regTime,p.time payTime,p.spend spend,p.payLevel payLevel,p.deviceId payDeviceId,p.ip payIp,r.pf regPf,r.country country,r.ip regIp,r.time stat_regTime from paylog p inner join userprofile u on p.uid=u.uid inner join stat_reg r on p.uid=r.uid where p.uid='$uid' and p.orderId='$orderId' order by stat_regTime desc;";
		$ret=$page->executeServer($server, $sql, 3);
		$lang='';
		$serverId=substr($server, 1);
		if (!$ret['error'] && $ret['ret']['data']){
			$lang=$ret['ret']['data'][0]['lang'];
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
			$refun_infoSql = "insert into refund_info($col) values($colVal) ON DUPLICATE KEY UPDATE $dupVal;";
//			$res = mysqli_query($link,$sql);
		}

		if (empty($lang) || (!isset($contentsArray[$lang]))){
			$lang='en';
		}

		$sendBy = $page->getAdmin ();
		$sendTime = microtime ( true ) * 1000;
		$title = mysql_escape_string ( $titleArray[$lang]['0'] );
		$contents = mysql_escape_string ( sprintf($contentsArray[$lang]['0'],$orderId,$time) );
		$mailUid = md5 ( $uid . $server . $orderId . time () );
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0)";
		$result2 = $page->executeServer ( $server, $sql, 2 );
		sendReward2 ( $mailUid, $server );
		adminLogUser ( $adminid, $uid, $server, array (
			'refund_orderId' => $orderId,
			'productId' => $productId
		) );

		file_put_contents('/tmp/refun_infoSql.log', $refun_infoSql."\n",FILE_APPEND);
		file_put_contents('/tmp/refun_infoSql.log', $props."\n",FILE_APPEND);
		file_put_contents('/tmp/global_paylog_sql.log', $global_paylog_sql."\n",FILE_APPEND);
		exit ( '道具扣除成功,且已向玩家发送邮件通知!' );
	}
}
function sendReward2($mailUid, $serv) {
	$page = new BasePage ();
	$page->webRequest ( 'sendmail', array (
		'uid' => $mailUid
	), $serv );
}
function getPackageInfo() {
// 	$filePath = '/usr/local/cok/SFS2X/resource/GMstatistics/exchange.xml';
// 	if ((! file_exists ( ADMIN_ROOT . '/language/refound/package.php' )) || (filemtime ( ADMIN_ROOT . '/language/refound/package.php' ) < filemtime ( $filePath ))) {
// 		writePackageInfo ();
// 	}
	$exchageXml = loadXml('exchange','exchange');
//	$a = require ADMIN_ROOT . '/language/refound/package.php';
	return $exchageXml;
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
include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>