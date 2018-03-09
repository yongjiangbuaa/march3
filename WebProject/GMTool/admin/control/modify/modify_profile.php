<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;

$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = trim($_REQUEST['username']);
if($_REQUEST['useruid'])
	$useruid = trim($_REQUEST['useruid']);
$dbArray = array(
	'uid' => array('name'=>'uid','uneditable'=>1,'note'=>''),
    'name' => array('name'=>'名字',),
    'level' => array('name'=>'等级',),
    'heart' => array('name'=>'红心',),
    'star' => array('name'=>'星星',),
    'gold' => array('name'=>'金币',),
	'exp' => array('name'=>'经验',),
//	'stamina' => array('name' => '体力'),	// added by duzhigao
//	'alliancename' => array('name'=>'联盟','uneditable'=>1),
//	'VIP' => array('name'=>'VIP等级','uneditable'=>1,),
//	'vipscore' => array('name'=>'VIP积分',),
//	'vipstatus' => array('name'=>'VIP激活状态','uneditable'=>1,),
//	'accPoint' => array('name'=>'个人联盟荣誉',),
//	'paidGold' => array('name'=>'充值金币',),
//	'payTotal' => array('name'=>'总充值金币',),
//	'glory' => array('name'=>'荣誉值',),
//	'country' => array('name'=>'国家'),
//	'regTime' => array('name'=>'注册时间',),
//	// 'openedSystem' => array('name'=>'开启功能',),
//	'gmFlag' => array('name'=>'GM标记(1gm,2mod,3gm带标识,4smod,5实习mod)',),
//	'banTime' => array('name'=>'封号结束时间',),
//	'chatBanTime' => array('name'=>'禁言结束时间',),
//	'noticeBanTime' => array('name'=>'禁封大喇叭结束时间',),
//	'parseRegisterId' => array('name'=>'推送帐号信息','uneditable'=>1,),
//	// 'platformId' => array('name'=>'关联帐号',),
//	'model' => array('name'=>'机型','uneditable'=>1,),
//	'version' => array('name'=>'手机版本','uneditable'=>1,),
//	'width' => array('name'=>'屏幕宽','uneditable'=>1,),
//	'height' => array('name'=>'屏幕高','uneditable'=>1,),
//	'isBusinessman'=>array('name'=>'资源商标识(0:普通用户;1:资源商)',),
);

//require_once ADMIN_ROOT . "/include/PHPMailer/PHPMailerAutoload.php";
//date_default_timezone_set('Etc/UTC');
////Create a new PHPMailer instance
//$mail = new PHPMailer();
////Tell PHPMailer to use SMTP
//$mail->isSMTP();
////Enable SMTP debugging
//// 0 = off (for production use)
//// 1 = client messages
//// 2 = client and server messages
//$mail->SMTPDebug = 0;
////Ask for HTML-friendly debug output
//$mail->Debugoutput = 'html';
////Set the hostname of the mail server
//$mail->Host = "smtp.mail.aliyun.com";
////Set the SMTP port number - likely to be 25, 465 or 587
//$mail->Port = 25;
////Whether to use SMTP authentication
//$mail->SMTPAuth = true;
////Username to use for SMTP authentication
//$mail->Username = "wzhy642@aliyun.com";
////Password to use for SMTP authentication
//$mail->Password = "password";
////Set who the message is to be sent from
//$mail->setFrom('wzhy642@aliyun.com', '发件人名字');
////Set an alternative reply-to address
//$mail->addReplyTo('wzhy642@aliyun.com', '快捷回复目标名字');
////Set who the message is to be sent to
//$mail->addAddress('441953186@qq.com', '目标名字');
////Set the subject line
//$mail->Subject = 'PHPMailer SMTP test';
////Read an HTML message body from an external file, convert referenced images to embedded,
//$mail->Body = "邮件内容";
//Replace the plain text body with one created manually
// $mail->AltBody = 'This is a plain-text message body';

// if (!$mail->send()) {
// 	echo "Mailer Error: " . $mail->ErrorInfo;
// } else {
// 	echo "Message sent!";
// }


$headLine = "修改userprofile";
$headAlert = "";
if($mongoTest){
	$connection = new Mongo('10.1.5.59:27017');
	print_r($connection->listDBs());
}
if($_REQUEST['uid'])
	$oraUid = $_REQUEST['uid'];
if($_REQUEST['gmFlag'])
	$oragmFlag = $_REQUEST['gmFlag'];
if($_REQUEST['lang'])
	$lang = $_REQUEST['lang'];

if($_REQUEST['country'])
	$oldcountry = $_REQUEST['country'];

if($_REQUEST['vipscore'])
	$oldvipscore = $_REQUEST['vipscore'];

if($_REQUEST['accPoint'])
	$oldaccPoint = $_REQUEST['accPoint'];

if($_REQUEST['glory'])
	$oldGlory = $_REQUEST['glory'];

if ($type) {
	$serverId=substr($currentServer, 1);
	$operator=$_COOKIE['u'];
	
	if($username){
		$account_list = cobar_getAllAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
	}
	if($type == 'edit') 
	{
		$updateSql = "update userprofile set ";
		$flag = true;
		$changeGold = 0;
		$replace = array();
		$gmflag = null;
		//added by duzhigao
		$stamina = null;
		foreach($_REQUEST as $key => $value)
		{
			if(substr($key, 0, 5) == 'value')
			{
				if($value !== ''){
					if(substr($key, 6) == 'country') {
						$newcountry= $value;
						$sql = "update stat_reg set country='$newcountry' where uid='$oraUid'";
						$page->execute($sql,2);
						adminLogUser($adminid,$oraUid,$currentServer,"modify country from $oldcountry to $newcountry");
//						exit('success ,please refresh the web');
						continue;
					}
					if(substr($key, 6) == 'vipscore') {

						$sql = "update user_vip set score='$value' where uid='$oraUid'";
						$page->execute($sql,2);
						adminLogUser($adminid,$oraUid,$currentServer,"modify vipscore from $oldvipscore to $value");
						continue;
					}
					if(substr($key, 6) == 'accPoint') {//修改联盟荣誉值
						$sql = "update alliance_member set accPoint='$value' where uid='$oraUid'";
						$page->execute($sql,2);
						adminLogUser($adminid,$oraUid,$currentServer,"modify accPoint from $oldaccPoint to $value");
						continue;
					}
					if(substr($key, 6) == 'glory') {//修改荣誉值
						$sql = "update user_glory set glory='$value' where uid='$oraUid'";
						$page->execute($sql,2);
						adminLogUser($adminid,$oraUid,$currentServer,"modify glory from $oldGlory to $value");
						continue;
					}
					if(in_array(substr($key, 6),array('banTime','chatBanTime','noticeBanTime'))){
						$value = strtotime($value)*1000;
						if (substr($key, 6) == 'banTime'){
							$banReasonTime=$value;
						}
						if (substr($key, 6) == 'chatBanTime'){
							$account_list = cobar_getAccountInfoByGameuids($oraUid);
							$deviceId = $account_list[0]['deviceId'];
						    $result['ret']['data'] = cobar_getAllAccountList('device', $deviceId);
							
							if(count($result['ret']['data']) > 1){
								foreach ($result['ret']['data'] as $curRow){
									$data = $curRow;
									$logItem['服务器'] = 's'.$data['server'];
									$logItem['UID'] = $data['gameUid'];

									$page->webRequest('kickuser',array('uid'=>$logItem['UID']));
									$sql="update userprofile set chatBanTime=$value where uid='".$logItem['UID']."'";
									$page->executeServer($logItem['服务器'], $sql, 2);
									$ban = $value - time()*1000;
									if ($ban < 0) {
										$ban = 0;
									}
									$sendBy = $page->getAdmin();
									$page->webRequest('gmchatban', array('uid'=>$logItem['UID'], 'time'=>$ban, 'gmName'=>$sendBy, 'reason'=>"modify_profile", 'content'=>'0'));
								}
							}
						}
					}
					if(substr($key, 6) == 'gmFlag'){
						$gmflag =$value;
					}
					
					// added by duzhigao
					if(substr($key, 6) == 'stamina') {
						$stamina = $value;
						continue;
					}
					//更新名字等都从这走
					$replace[] = substr($key, 6) . " = '{$value}'";


					if(substr($key, 6) == 'platformId'){
						$sql = "select * from userprofile where platformId = '{$value}'";
						$result = $page->execute($sql);
						if(!$result['error'] && $result['ret']['data']){
							$error_msg = '关联数据冲突';
							$flag = false;
						}
					}
					if(substr($key, 6) == 'gold'){
						//$replace[] = "gmFlag = 1";
						$changeGold = $value;
					}
				}
			}
		}
        $modLang = transLang($lang);
		if($gmflag==2){
			if($oragmFlag!=2&&$oragmFlag!=5){
				$insertSql="insert into mod_info(uid,lang,server) values('$oraUid','$modLang','$currentServer');";
				$insertResult=$page->globalExecute($insertSql, 2);
			}
			$replace[] = "lastModGoldGetTime = ".time()*1000;
			$sec = 336*60*60*1000;
			$replace[] = "modGoldGetTimeInterval = ".$sec;
			$replace[] = "modGoldAmount = 3000";
		}else if ($gmflag==5){
			if($oragmFlag!=5&&$oragmFlag!=2){
				$insertSql="insert into mod_info(uid,lang,server) values('$oraUid','$modLang','$currentServer');";
				$insertResult=$page->globalExecute($insertSql, 2);
			}
			$replace[] = "lastModGoldGetTime = ".time()*1000;
			$sec = 504*60*60*1000;//改成3周
			$replace[] = "modGoldGetTimeInterval = ".$sec;
			$replace[] = "modGoldAmount = 3000";
			$replace[] = "beTrainingModTime = ".time()*1000;
		}else if($gmflag==4){
			if($oragmFlag==2||$oragmFlag==5){
				$deleteSql="delete from mod_info where uid=$oraUid;";
				$deleteResult=$page->globalExecute($deleteSql, 2,true);
			}
			$replace[] = "lastModGoldGetTime = ".time()*1000;
			$sec = 336*60*60*1000;
			$replace[] = "modGoldGetTimeInterval = ".$sec;
			$replace[] = "modGoldAmount = 6000";
		}else if($gmflag!=null && $gmflag!=2 && $gmflag!=4){
			if($oragmFlag==2||$oragmFlag==5){
				$deleteSql="delete from mod_info where uid=$oraUid;";
				$deleteResult=$page->globalExecute($deleteSql, 2,true);
			}
			$replace[] = "modGoldGetTimeInterval = 0";
			$replace[] = "modGoldAmount = 0";
		}
 		if($username)
 			$sql = "select * from userprofile where name = '{$username}'";
 		else
 			$sql = "select * from userprofile where uid = '{$useruid}'";

//		$sql = "select * from userprofile where uid = '{$useruid}'";

		$result = $page->execute($sql,3);
		if(!$result['error'] && $result['ret']['data']){
			$userId = $result['ret']['data'][0]['uid'];
			//先踢下线，然后更新数据库
//			$ret = $page->webRequest('kickuser',array('uid'=>$userId));
//			if($ret != 'ok') {
//				$replace = array();
//				$headAlert = 'kickuser error';
//				// added by duzhigao
//				$stamina = null;
//			}
			if($replace){
				$ori = $result['ret']['data'][0]['gold'];
				$change = $changeGold - $ori;
				$remain = $changeGold;
				if($changeGold > 0){
					addGoldLog($userId,$ori,$change,$remain);
				}
				$tmp .= implode(',', $replace);
// 				if($_POST['username'])
// 					$updateSql .= $tmp . " where name = '{$username}'";
// 				else 
// 					$updateSql .= $tmp . " where uid = '{$useruid}'";

				$updateSql .= $tmp . " where uid = '{$useruid}'";
				
				if($flag){
					$result = $page->execute($updateSql,2);
				}
			}
			
			// added by duzhigao
			if($stamina){
				$uSql = "update user_world set attMonsterStamina = '{$stamina}' where uid = '{$useruid}'";
				if($flag){
					$result_stamina = $page->execute($uSql,2);
				}
			}
		}
		
		
		$opeDate=date('Y-m-d H:i:s');
		if($banReasonTime && ($banReasonTime>(time()*1000))){
			//踢下线
			$ret = $page->webRequest('kickuser',array('uid'=>$useruid),$currentServer);

			$reason=$_REQUEST['banReason']?$_REQUEST['banReason']:'没有填写原因';
			$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('$currentServer','$useruid','$operator','$reason','$opeDate',1) ON DUPLICATE KEY UPDATE operator='$operator',reason='$reason',opeDate='$opeDate',status=1;";
			$page->globalExecute($reasonSql, 2);
			
			$time=time()*1000;
			$uuid=md5($serverId.$useruid.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$userId',$time,'$operator','$reason','$opeDate')";
			$page->globalExecute($sql, 2);
				
//			$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='$useruid' and state=0 limit 1;";
//			$page->execute($taskSql, 2);
			
			$sql = "select pointid from user_world where uid='$useruid'";
			$result = $page->execute($sql, 3);
			$pointid = $result['ret']['data'][0]['pointid'];
			
			$serverinfo = $servers[$currentServer];
			$ip = $serverinfo['ip_inner'];
			$rediskey = 'world'.substr($currentServer, 1);
			$redissfs = new Redis();
			$redissfs->connect($ip,6379);
			$redissfs->hDel($rediskey, $pointid);
			
			$sql="update worldpoint set pointType=8 where id=$pointid;";
			$re = $page->execute($sql,2);
			
		}else if($banReasonTime==0 || ($banReasonTime<(time()*1000))){
			$time=time()*1000;
			$uuid=md5($serverId.$useruid.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$userId',$time,'$operator','解封','$opeDate')";
			$page->globalExecute($sql, 2);
			
			$sql="update banTime_reason set status=0 where serverId='$currentServer' and uid='$useruid';";
			$page->globalExecute($sql, 2);
			
			$taskSql="update user_task set id=SUBSTRING_INDEX(id,'_ban',1) where uid='$useruid' and state=0 and id like '%_ban';";
			$page->execute($taskSql, 2);
		}

        if(!empty($replace) && $flag){
            $action_params = $replace;
            adminLogUser($adminid,$userId,$currentServer,$action_params);
        }
	}
// 	if($type == 'delete') 
// 	{
// 		if($username)
// 			$sql = "delete from `cokdb_global`.account_new where gameUserName = '{$username}'";
// 		else
// 			$sql = "delete from `cokdb_global`.account_new where gameUid = '{$useruid}'";
// 		$result = $page->execute($sql);
// 	}
	//unactive 封号
	if($type == 'unactive' || $type == 'active')
	{
// 		if($username){
// 			$sql = "select uid from userprofile where name = '{$username}'";
// 			$tmp = $page->execute($sql);
// 			$uid = $tmp['ret']['data'][0]['uid'];
// 		}else{
// 			$uid = $useruid;
// 		}

		$uid = $useruid;
		$opeDate=date('Y-m-d H:i:s');
		
		$active = 0;
		if($type == 'unactive'){
			
			$ret = $page->webRequest('kickuser',array('uid'=>$uid),$currentServer);
			
			$active = 1;
			$sql="update userprofile set banTime=9223372036854775806 where uid ='$uid'";
			$page->execute($sql,2);
			$reason=$_REQUEST['unactiveReason']?$_REQUEST['unactiveReason']:'没有填写原因';
			$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('$currentServer','$uid','$operator','$reason','$opeDate',1) ON DUPLICATE KEY UPDATE operator='$operator',reason='$reason',opeDate='$opeDate',status=1;";
			$page->globalExecute($reasonSql, 2);
			
			$time=time()*1000;
			$uuid=md5($serverId.$uid.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'$operator','$reason','$opeDate')";
			$page->globalExecute($sql, 2);
			
//			$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='$uid' and state=0 limit 1;";
//			$page->execute($taskSql, 2);
			
			$sql = "select pointid from user_world where uid='$uid'";
			$result = $page->execute($sql, 3);
			$pointid = $result['ret']['data'][0]['pointid'];
				
			$serverinfo = $servers[$currentServer];
			$ip = $serverinfo['ip_inner'];
			$rediskey = 'world'.substr($currentServer, 1);
			$redissfs = new Redis();
			$redissfs->connect($ip,6379);
			$redissfs->hDel($rediskey, $pointid);
				
			$sql="update worldpoint set pointType=8 where id=$pointid;";
			$re = $page->execute($sql,2);
			
		}else {
			$sql="update userprofile set banTime=0 where uid ='$uid'";
			$page->execute($sql,2);
			
			$time=time()*1000;
			$uuid=md5($serverId.$uid.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'$operator','解封','$opeDate')";
			$page->globalExecute($sql, 2);
			
			$sql="update banTime_reason set status=0 where serverId='$currentServer' and uid='$uid';";
			$page->globalExecute($sql, 2);
			
			$taskSql="update user_task set id=SUBSTRING_INDEX(id,'_ban',1) where uid='$uid' and state=0 and id like '%_ban';";
			$page->execute($taskSql, 2);
		}
		if($uid){
// 			$sql = "update account_new set active = $active where gameUid = '{$uid}'";
// 			$result = $page->globalExecute($sql);
			cobar_query_global_db_cobar("update account_new set active = $active where gameUid = '{$uid}'");

            adminLogUser($adminid,$uid,$currentServer,array('active'=>$active));
		}
	}
	
	if($type == 'unactiveAll' || $type == 'activeAll')
	{
			$uid = $useruid;
			$opeDate=date('Y-m-d H:i:s');
			$active = 0;
			
			$account_list = cobar_getAccountInfoByGameuids($uid);
			$deviceId = $account_list[0]['deviceId'];
			$result['ret']['data'] = cobar_getAllAccountList('device', $deviceId);
			
			if(count($result['ret']['data']) < 1){
				var_dump($result['ret']['data']);
				$headAlert = 'no data';
			}
			if($type == 'unactiveAll'){
				$active = 1;
				foreach ($result['ret']['data'] as $curRow){
					$sql="update userprofile set banTime=9223372036854775806 where uid ='".$curRow['gameUid']."';";
					$page->executeServer('s'.$curRow['server'], $sql, 2);
					
					$ret = $page->webRequest('kickuser',array('uid'=>$curRow['gameUid']),'s'.$curRow['server']);
					
					$reason=$_REQUEST['unactiveReason']?$_REQUEST['unactiveReason']:'没有填写原因';
					$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('s".$curRow['server']."','".$curRow['gameUid']."','$operator','$reason','$opeDate',1) ON DUPLICATE KEY UPDATE operator='$operator',reason='$reason',opeDate='$opeDate',status=1;";
					$page->globalExecute($reasonSql, 2);
					
					$time=time()*1000;
					$uuid=md5($curRow['server'].$curRow['gameUid'].$time);
					$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',".$curRow['server'].",'".$curRow['gameUid']."',$time,'$operator','$reason','$opeDate')";
					$page->globalExecute($sql, 2);
					
//					$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='".$curRow['gameUid']."' and state=0 limit 1;";
//					$page->executeServer('s'.$curRow['server'],$taskSql, 2);
					
					$sql = "select pointid from user_world where uid='".$curRow['gameUid']."'";
					$result = $page->executeServer('s'.$curRow['server'], $sql, 3);
					$pointid = $result['ret']['data'][0]['pointid'];
					
					$serverinfo = $servers['s'.$curRow['server']];
					$ip = $serverinfo['ip_inner'];
					$rediskey = 'world'.$curRow['server'];
					$redissfs = new Redis();
					$redissfs->connect($ip,6379);
					$redissfs->hDel($rediskey, $pointid);
					
					$sql="update worldpoint set pointType=8 where id=$pointid;";
					$re = $page->executeServer('s'.$curRow['server'],$sql,2);
					
					cobar_query_global_db_cobar("update account_new set active = $active where gameUid = '".$curRow['gameUid']."';");
					
					adminLogUser($adminid,$curRow['gameUid'],'s'.$curRow['server'],array('active'=>$active));
				}
			}else {
				foreach ($result['ret']['data'] as $curRow){
					$sql="update userprofile set banTime=0 where uid ='".$curRow['gameUid']."';";
					$page->executeServer('s'.$curRow['server'], $sql, 2);
					
					$time=time()*1000;
					$uuid=md5($curRow['server'].$curRow['gameUid'].$time);
					$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',".$curRow['server'].",'".$curRow['gameUid']."',$time,'$operator','解封','$opeDate')";
					$page->globalExecute($sql, 2);
					
					$sql="update banTime_reason set status=0 where serverId='s".$curRow['server']."' and uid='".$curRow['gameUid']."';";
					$page->globalExecute($sql, 2);
					
					$taskSql="update user_task set id=SUBSTRING_INDEX(id,'_ban',1) where uid='".$curRow['gameUid']."' and state=0 and id like '%_ban';";
					$page->executeServer('s'.$curRow['server'],$taskSql, 2);
					
					cobar_query_global_db_cobar("update account_new set active = $active where gameUid = '".$curRow['gameUid']."';");
						
					adminLogUser($adminid,$curRow['gameUid'],'s'.$curRow['server'],array('active'=>$active));
				}
			}
			
		}
	
	//其他表数据先取，防止uid取不到
// 	if($username)
// 		$sql = "select sp.*,if(vip.score > 0 ,vip.score,0) vipscore,vip.vipEndTime,a.alliancename,u.* from userprofile u left join stat_phone sp on u.uid = sp.uid left join user_vip vip on u.uid = vip.uid left JOIN alliance a on u.allianceId=a.uid where u.name = '{$username}'";
// 	else
// 		$sql = "select sp.*,if(vip.score > 0 ,vip.score,0) vipscore,vip.vipEndTime,a.alliancename,u.* from userprofile u left join stat_phone sp on u.uid = sp.uid  left join user_vip vip on u.uid = vip.uid left JOIN alliance a on u.allianceId=a.uid where u.uid = '{$useruid}'";

	// modified by duzhigao, join table user_world, for setting stamina
//	$sql = "select sp.*,if(vip.score > 0 ,vip.score,0) vipscore,vip.vipEndTime,am.accPoint,ug.glory,a.alliancename,uw.attMonsterStamina as stamina,u.*
//from userprofile u
//left join stat_phone sp on u.uid = sp.uid
//left join user_vip vip on u.uid = vip.uid
//left join alliance_member am on u.uid = am.uid
//left join user_glory ug on u.uid = ug.uid
//left JOIN alliance a on u.allianceId=a.uid
//LEFT JOIN user_world uw on u.uid=uw.uid where u.uid = '{$useruid}'";
    if($username)
        $sql = "select * from userprofile where name = '{$username}'";
    else
        $sql = "select * from userprofile where uid = '{$useruid}'";

	$result = $page->execute($sql);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$item['name']=str_replace(' ', '&nbsp;', $item['name']);
		$countrySql = "select uid, country from stat_reg where uid='{$item['uid']}';";

		$countryResult = $page->execute($countrySql,3);
		$item['country']=$countryResult['ret']['data'][0]['country'];
		$item['regTime'] = date('Y-m-d H:i:s',$item['regTime']/1000);
		$item['banTime'] = date('Y-m-d H:i:s',$item['banTime']/1000);
		$item['noticeBanTime'] = date('Y-m-d H:i:s',$item['noticeBanTime']/1000);
		$item['VIP'] = getVipLevel($item['vipscore']);
		$item['vipstatus'] = ( $item['vipEndTime'] > time()*1000   ? '激活' :'否');
		$showData = true;
// 		$sql = "select * from account_new where gameuid = '{$item['uid']}'";
// 		$result = $page->globalExecute($sql);
		$result['ret']['data'] = cobar_getAccountInfoByGameuids($item['uid']);
		if(is_array($result['ret']) && $result['ret']['data'][0]['active'] == 1)
			$activeFlag = 1;
	}else{
		$error_msg = search($result);
	}
}

function transLang($srcLang) {
    if (0 == strcasecmp($srcLang, "cn") || 0 == strcasecmp($srcLang, "zh-chs") || 0 == strcasecmp($srcLang, "zh_cn")) {
        return "zh-Hans";
    }
    if (0 == strcasecmp($srcLang, "zh-cht") || 0 == strcasecmp($srcLang, "tw") || 0 == strcasecmp($srcLang, "zh_tw")) {
        return "zh-Hant";
    }
    if (0 == strcasecmp($srcLang, "id") || 0 == strcasecmp($srcLang, "in")) {
        return "ms"; //印度和印尼都认为是马来语
    }
    if (0 == strcasecmp($srcLang, "jp")) {
        return "ja"; //日本
    }
    if (0 == strcasecmp($srcLang, "en_gb")) {
        return "en";
    }
    if (0 == strcasecmp($srcLang, "fr_fr")) {
        return "fr";
    }
    if (0 == strcasecmp($srcLang, "it_it")) {
        return "it";
    }
    return $srcLang;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>