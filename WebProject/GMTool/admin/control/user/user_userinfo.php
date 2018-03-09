<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['facebook'])
	$facebookname = $_REQUEST['facebook'];
if($_REQUEST['facebookid'])
	$facebookid = $_REQUEST['facebookid'];
if($_REQUEST['googleAccountName'])
	$googleAccountName = $_REQUEST['googleAccountName'];
if($_REQUEST['gameCenter'])
	$gameCenter = $_REQUEST['gameCenter'];

if($facebookid){
	$where = "facebookAccount=$facebookid";
}elseif($facebookname){
	$where = "facebookAccountName='$facebookname'";
}elseif($googleAccountName){
	$where = "googleAccountName='$googleAccountName'";
}elseif($gameCenter){
	$sql = "select n.gameUserName from account_new n INNER  JOIN gameCenter_info c on n.gameUid=c.gameUid where n.pf='AppStore' and c.gcName='$gameCenter' ";
}

if(isset($gameCenter)){
	$find_name_sql = $sql;
}else if(isset($where)){
	$find_name_sql = "select gameUserName from account_new where {$where} ;";
}
if(isset($where)) {
	$result_find = $page->globalExecute($find_name_sql, 3);
	$usernames = $result_find['ret']['data'];
	$sum = count($usernames);
	$html = "当前facebook账号下有" . $sum . "个账号：" . json_encode($usernames);
	$username = $result_find['ret']['data'][0]['gameUserName'];
}
//echo json_encode($result_find);
if($_REQUEST['username']) {
    $username = $_REQUEST['username'];
}
if($_REQUEST['useruid']){
    $useruid = $_REQUEST['useruid'];
}

$dbArray = array(
	'useruid' => array('name'=>'UID',),
	'name' => array('name'=>'名字',),
	'level' => array('name'=>'等级',),
    'heart' => array('name'=>'红心',),
    'star' => array('name'=>'星星',),
    'gold' => array('name'=>'金币',),
	'exp' => array('name'=>'经验',),
/*	'alliancename' => array('name'=>'联盟',),
	'VIP' => array('name'=>'VIP等级',),
	'vipscore' => array('name'=>'VIP积分',),
	'vipstatus' => array('name'=>'VIP激活状态',),
	'accPoint' => array('name'=>'个人联盟荣誉',),

	'paidGold' => array('name'=>'充值金币',),
	'payTotal' => array('name'=>'总充值金币',),
//	'invite' => array('name'=>'邀请码使用次数:'),
	'x' => array('name'=>'坐标X',),
	'y' => array('name'=>'坐标Y',),
	// 'energy' => array('name'=>'体力',),
	// 'stone' => array('name'=>'石头',),
	'wood' => array('name'=>'木材',),
	'iron' => array('name'=>'铁矿',),
	'food' => array('name'=>'粮食',),
	'silver' => array('name'=>'银币',),
	// 'country' => array('name'=>'国家',),*/
	'deviceId' => array('name'=>'设备Id'),
	'regTime' => array('name'=>'注册时间',),
	'lastOnlineTime' => array('name'=>'上次登陆时间',),
//	'offLineTime' => array('name'=>'离线时间',),
	// 'openedSystem' => array('name'=>'开启功能',),
	'gmFlag' => array('name'=>'GM标记',),
	'country' => array('name'=>'国家',),
	'pf' => array('name'=>'注册平台',),
	'currentPf' => array('name'=>'当前平台',),
	'accountNumber' => array('name'=>'平台账号',),
	'appVersion'=>array('name'=>'版本号'),	
	'lang' => array('name'=>'语言',),
	'phoneDevice' => array('name'=>'设备型号',),
	'model' => array('name'=>'机型',),
	'version' => array('name'=>'手机版本',),
	'width' => array('name'=>'屏幕宽',),
	'height' => array('name'=>'屏幕高',),
    'googleAccount' => array('name'=>'绑定Google账户',),
    'googleAccountName' => array('name'=>'Google账户名',),
    'facebookAccount' => array('name'=>'绑定Facebook账户',),
    'facebookAccountName' => array('name'=>'Facebook账户名',),
//    'chatBanTime' => array('name'=>'禁言结束时间',),
//	'noticeBanTime' => array('name'=>'禁封大喇叭结束时间',),
//	'banTime'=>array('name'=>'禁止登陆'),
//	'isBusinessman'=>array('name'=>'资源商标识(0:普通用户;1:资源商)'),
);
$headLine = "查看玩家信息";
$headAlert = "";
if($_REQUEST['dochatban']) { //禁言 解禁都是这个
	$uid = $_REQUEST['banuid'];
	$time = $_REQUEST['bantime'];
	$dotype = $_REQUEST['dotype'];

	if ($_REQUEST['userreason']) {
		$reason = $_REQUEST['userreason'];
		print_r($reason);
	}

    if($dotype == 1){
        $bantime = strtotime($time)*1000;
    }
    elseif($dotype == 2){
        $bantime = 0;
    }
    
    $account_list = cobar_getAccountInfoByGameuids($uid);
	$deviceId = $account_list[0]['deviceId'];
    $result['ret']['data'] = cobar_getAllAccountList('device', $deviceId);
	
	if(count($result['ret']['data']) > 0){
		foreach ($result['ret']['data'] as $curRow){
			$data = $curRow;
			$logItem['服务器'] = 's'.$data['server'];
			$logItem['UID'] = $data['gameUid'];
			
			$ret = $page->webRequest('kickuser',array('uid'=>$data['gameUid']),'s'.$data['server']);
			
			$sql="update userprofile set chatBanTime=$bantime where uid='".$logItem['UID']."'";
			$page->executeServer($logItem['服务器'], $sql, 2);
			if($dotype == 2){
				$uid = $logItem['UID'];
				$sql="delete from banWordRecord WHERE uid='$uid' and server=".$data['server'].";";
				$result = $page->globalExecute($sql,2);
			}elseif($dotype == 1){
				createBanReason($logItem['UID'],$reason,$data['server']);
			}

			$sendBy = $page->getAdmin();
			if($bantime > time()*1000){
				$ban = $bantime - time()*1000;
			}else{
				$ban = 0;
			}
			$page->webRequest('gmchatban', array('uid'=>$logItem['UID'], 'time'=>$ban, 'gmName'=>$sendBy, 'reason'=>"user_userinfo", 'content'=>'0'));
		}
	}

    adminLogUser($adminid,$uid,$currentServer,array('chatBanTime'=>$bantime));
    exit();
}

function createBanReason($useruid,$bandreason,$serverId){
	$time=time()*1000;
	global $page;
	$sendBy = $page->getAdmin();
	$sql="insert into banWordRecord(uid,server) values('$useruid',$serverId) ON DUPLICATE KEY UPDATE banTimes=banTimes+1, operator='$sendBy', opTime=$time, bandreason='$bandreason';";
	$result = $page->globalExecute($sql, 2);
	if($result['error']){
		exit("banWordRecord表插入失败");
	}

	$tmp = "update banWordRecord set operator='$sendBy', opTime=$time ,bandreason='$bandreason' where uid='$useruid' and server=$serverId;";
	$page->globalExecute($tmp, 2);
}
if($_REQUEST['donoticeban']){
	$uid = $_REQUEST['banuid'];
	$time = $_REQUEST['bantime'];
	$dotype = $_REQUEST['dotype'];
	if($dotype == 1){
		$bantime = strtotime($time)*1000;
	}
	elseif($dotype == 2){
		$bantime = 0;
	}
	
	$ret = $page->webRequest('kickuser',array('uid'=>$uid),$currentServer);
	
	$sql = "update userprofile set noticeBanTime=$bantime where uid='$uid'";
	$page->execute($sql,2);

	adminLogUser($adminid,$uid,$currentServer,array('noticeBanTime'=>$bantime));
	exit();
}

if($_REQUEST['dochangename']){

	$uid = $_REQUEST['changeuid'];
	$name = addslashes(trim($_REQUEST['changename']));

	if (empty($name)) {
		exit('ERROR: name is empty!');
	}

// 	$sql = "select count(gameuid) cnt from account_new where gameUserName='$name'";
// 	$result = $page->globalExecute($sql, 3);
// 	if ($result['ret']['data'][0]['cnt'] > 0) {
// 		exit("ERROR: 该用户名[".$name."]已经存在!");
// 	}
	
	$account_list = cobar_getAllAccountList('name', $name,$uid);

	if (count($account_list) > 0) {
		exit("ERROR: 该用户名[".$name."]已经存在!");
	}
	
//	$sql = "select pointid from user_world where uid='$uid'";
//	$result = $page->execute($sql, 3);
//	$pointid = $result['ret']['data'][0]['pointid'];
/*	$currserver = 1;//$page->getAppId();
	$serverinfo = $servers[$currserver];
	if ($currserver == 'test' || $currserver == 'localhost') {
		$t = explode(':', $serverinfo['webbase']);//http://IPIPIP:8080/gameservice/
		$ip = substr($t[1], 2);
		$rediskey = 'world0';
	}else{
		$ip = $serverinfo['ip_inner'];//内网ip
		$rediskey = 'world'.substr($currserver, 1);
	}

	$redis = new Redis();
	$r = $redis->connect($ip,6379);
	if($r === false){
		exit('connect redis error!');
	}
	$rd_json = $redis->hGet($rediskey, $pointid);
	if (!empty($rd_json)) {
		$rd_arr = json_decode($rd_json, true);
		$rd_arr['o'] = $name;
		$rd_json = json_encode($rd_arr);
		$redis->hSet($rediskey, $pointid, $rd_json);
	}*/

	$sql = "select name from userprofile where uid='$uid'";
	$result = $page->execute($sql, 3, true);
	$oldName = $result['ret']['data'][0]['name'];
	
	$sql = "update userprofile set name='$name' where uid='$uid'";
	$result =  $page->execute($sql, 2, true);

	$sql = "update worldpoint set ownerName='$name' where ownerId='$uid'";
	$result = $page->execute($sql, 2, true);

	cobar_changeUserName($uid, $oldName, $name);

	/*$toUser = $uid;
	$sendTime = microtime(true)*1000;
	$title = addslashes("Rename Explanation Email");//
	$contents = addslashes("My lord,
We received report from other player that you are using illegal account name.
After verifying,we changed your name temporarily and sent you a rename card as compensation.
We hope you won't use illegal in the future.
Have fun in COK.
Clash of Kings studio");
	$mailUid = md5($toUser.$sendTime.$title.$contents.time());
	$reward='goods,200021,1';
	$rewardStatus = 0;
	$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply,srctype) values ('$mailUid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 1,106)";
	$page->execute($sql,2);
	sendReward($mailUid);*/
	
    adminLogUser($adminid,$uid,$currentServer,array('name'=>$name));
//	exit('操作成功！已给玩家发送解释邮件，且赠送改名卡');
    exit('操作成功！');
}
function sendReward($mailUid){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid));
}
if ($type) {
    $global = 'cokdb_global';
// 	if($username){
// 	    $sql = "select *,u.level, u.uid useruid ,u.appVersion ,if(vip.score > 0 ,vip.score,0) vipscore,vip.vipEndTime
// 	     from userprofile u left join
// 	    user_resource c on u.uid = c.uid left join user_lord l on u.uid = l.uid left join stat_phone sp
// 	    on u.uid = sp.uid left join user_world uw on u.uid = uw.uid left join worldpoint p on uw.pointId = p.id
// 	    left join user_vip vip on u.uid = vip.uid left JOIN alliance al on u.allianceId=al.uid
// 	    where name = '{$username}'";
// 	}
// 	else{
// 	    $sql = "select *,u.level,u.uid useruid,u.appVersion ,if(vip.score > 0 ,vip.score,0) vipscore,vip.vipEndTime
// 	    from userprofile u left join user_resource c on u.uid = c.uid left join user_lord l on u.uid = l.uid
// 	    left join stat_phone sp on u.uid = sp.uid left join user_world uw on u.uid = uw.uid
// 	    left join worldpoint p on uw.pointId = p.id  left join user_vip vip on u.uid = vip.uid
// 	    left JOIN alliance al on u.allianceId=al.uid 
// 	    where u.uid = '{$useruid}'";
// 	}

	if(!$useruid && $username){
		$account_list = cobar_getAllAccountList('name', $username);
		$useruid = $account_list[0]['gameUid'];
		if (!$useruid){
			$sql="select uid from userprofile where binary name ='$username'";//大小写敏感
			$ret=$page->execute($sql, 3);
			$useruid=$ret['ret']['data'][0]['uid'];
		}
    }
    $sql = "select u.*
     	    from userprofile u 
     	    where u.uid = '{$useruid}'";
	if ($type == 'viewmaster'){
		$result = $page->execute($sql, 3, true);
	}else{
		$result = $page->execute($sql, 3);
	}
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
        $item['useruid'] = $useruid;
//		echo print_r($item);
//		$item['invite']=$result_cat7[0]['invite']==null?0:$result_cat7[0]['invite'];
		$item['name']=str_replace(' ', '&nbsp;', $item['name']);

		$uidmy = $useruid;
		$item['name'] = $item['name']."<input class='input-medium' id='changename' name='changename' type='text' value='COK". intval(microtime(true)*1000)
				        ."' size='50' /><input class='btn js-btn btn-primary' type='button' value='修改' name='btn_view' onclick='dochangename(\"".$uidmy."\");' />";
		$item['regTime'] = date('Y-m-d H:i:s',$item['regTime']/1000);
		$item['vipstatus'] = ( $item['vipEndTime'] > time()*1000   ? '激活' :'否');
		$item['lastOnlineTime'] = date('Y-m-d H:i:s',$item['lastOnlineTime']/1000);
		$item['offLineTime'] = date('Y-m-d H:i:s',$item['offLineTime']/1000);
		if( $item['banTime'] == PHP_INT_MAX || $item['banTime']/1000 > 9999999999){
			$item['banTime'] = "永久封禁";
		}else if($item['banTime']/1000 > time() ){
			$item['banTime'] = date('Y-m-d H:i:s',$item['banTime']/1000);
		}else{
			$item['banTime'] = '无';
		}
		$countrySql = "select uid, country,pf from stat_reg where uid='{$item['useruid']}';";
		$countryResult = $page->execute($countrySql,3);
		$item['country']=$countryResult['ret']['data'][0]['country'];
		$item['pf']=$countryResult['ret']['data'][0]['pf'];

		$accountSql="select mappingValue from userbindmapping where gameUid='".$item['useruid']."' and mappingType='".$item['pf']."';";
		$rows = cobar_query_global_db_cobar($accountSql);
		if(empty($rows)){
			$item['accountNumber']= '';
		}else{
			$row1 = $rows[0];
			$item['accountNumber']=$row1['mappingValue']?$row1['mappingValue']:'';
		}
		$item['VIP'] = getVipLevel($item['vipscore']);
		if($item['chatBanTime'] > time()*1000){
		    $item['chatBanTime'] = date('Y-m-d H:i:s',$item['chatBanTime']/1000)."<input class='btn js-btn btn-primary' type='button' value='立即解禁' name='btn_view' onclick='dochatban(\"".
		        $item['useruid']."\",2);' />";
		}
		else{
		    $item['chatBanTime'] = "当前未禁言        
		        <input class='input-medium' id='dateMax' name='dateMax' type='text' value='".date('Y-m-d',strtotime('+5 day'))
		        ."' size='50' /><input class='btn js-btn btn-primary' type='button' value='禁言' name='btn_view' onclick='dochatban(\"".
		        $item['useruid']."\",1);' />禁言理由<input type='text' id='userreason' name='userreason' value=''/>";
		}
		
		if($item['noticeBanTime'] > time()*1000){
			$item['noticeBanTime'] = date('Y-m-d H:i:s',$item['noticeBanTime']/1000)."<input class='btn js-btn btn-primary' type='button' value='立即解禁' name='btn_view' onclick='donoticeban(\"".
					$item['useruid']."\",2);' />";
		}
		else{
			$item['noticeBanTime'] = "当前未禁言
		        <input class='input-medium' id='dateNoticeMax' name='dateMax' type='text' value='".date('Y-m-d',strtotime('+5 day'))
				        ."' size='50' /><input class='btn js-btn btn-primary' type='button' value='禁言' name='btn_view' onclick='donoticeban(\"".
				        $item['useruid']."\",1);' />";
		}
		

		//帐号绑定信息
// 		$sql = "select facebookAccount  ,facebookAccountName,googleAccount,googleAccountName from `$global`.`account_new` 
// 		         where gameUid = '".$item['useruid']."' limit 1";
// 		$ret = $page->globalExecute($sql, 3);
		$ret['ret']['data'] = cobar_getAccountInfoByGameuids($item['useruid']);
		$result = $ret['ret']['data'][0];
		$item['facebookAccount'] = $result['facebookAccount'];
		$item['facebookAccountName'] = $result['facebookAccountName'];
		$item['googleAccount'] = $result['googleAccount'];
		$item['googleAccountName'] = $result['googleAccountName'];
		$showData = true;
	}else{
        $headAlert = '用户不存在或不属于当前选择的服务器！';
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>