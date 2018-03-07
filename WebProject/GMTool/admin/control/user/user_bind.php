<?php
!defined('IN_ADMIN') && exit('Access Denied');
//开发者debug
$developer = in_array($adminid,array('liuyi'));

$showData = false;
$type = $_GET['action'];
if($_GET['username'])
	$uname = trim($_GET['username']);
if($_GET['useruid'])
	$uuid = trim($_GET['useruid']);
if($_GET['deviceId'])
	$deviceId = trim($_GET['deviceId']);
$dbArray = array(
    'server' => array('name'=>'服务器',),
	'gameUid' => array('name'=>'UID',),
	'gameUserName' => array('name'=>'名字',),
	'gameUserLevel' => array('name'=>'等级',),
	'lastTime' => array('name'=>'上次登录时间',),
    'deviceId' => array('name'=>'设备ID',),
    'googleAccount' => array('name'=>'绑定Google账户',),
    'facebookAccount' => array('name'=>'绑定Facebook账户',),
    'AppStore' => array('name'=>'绑定IOS账户',),
	'weibo' => array('name'=>'绑定微博账户',),
	'vk' => array('name'=>'绑定VK账户',),
    'active' => array('name'=>'状态',),
);
$headLine = "玩家绑定信息";
$headAlert = "";
if ($type == 1) {
    $global = 'cokdb_global';
    
//     if($deviceId){
//     	$sql = "select * from `account_new` where deviceId='$deviceId' ORDER BY lastTime DESC";
//     }else {
// 		if($uname){
// 		    $sql = "select * from `$global`.`account_new` where deviceId in (select deviceId from `$global`.`account_new` where 
// 		    gameUserName = '{$uname}' ) ORDER BY lastTime DESC ";
// 		}
// 		else{
// 		    $sql = "select * from `$global`.`account_new` where deviceId in (select deviceId from `$global`.`account_new` 
// 		    where gameUid = '{$uuid}' ) ORDER BY lastTime DESC ";
// 		}
//     }
// 	$result = $page->globalExecute($sql,3);
	
	if (empty($deviceId)) {
		if($uname){
			$account_list = cobar_getAllAccountList('name', $uname);
			if (count($account_list) > 1) {
				//TODO WARNING.
			}
			$deviceId = $account_list[0]['deviceId'];
		}else{
			$account_list = cobar_getAccountInfoByGameuids($uuid);
			$deviceId = $account_list[0]['deviceId'];
		}
	}
	$result['ret']['data'] = cobar_getAllAccountList('device', $deviceId);
	
	if(count($result['ret']['data']) < 1){
	    var_dump($result['ret']['data']);
	    $headAlert = 'no data';
	}
	$log = array();
	$i=1;
	foreach ($result['ret']['data'] as $curRow)
		{
			$data = $curRow;
			if($i==1){
				$firstUid=$data['gameUid'];
				$firstServer='s'.$data['server'];
			}
			foreach ($dbArray as $key=>$dbVal){
				if ($key=='server'){
					$logItem[$dbVal['name']] = 's'.$data[$key];
				}elseif ($key=='lastTime'){
					$logItem[$dbVal['name']] = date('Y-m-d H:i:s',$data[$key]/1000);
				}elseif($key=='AppStore') {
					if ($data['pf']=='AppStore'){
						$logItem[$dbVal['name']] = $data['pfId']?$data['pfId']:'';
					}else {
						$logItem[$dbVal['name']] = '';
					}
				}elseif($key=='weibo' || $key=='vk') {
					$weiboSql="select mappingValue from userbindmappingreverse where gameUid='".$data['gameUid']."' and mappingType='$key';";
					$weiboRet=cobar_query_global_db_cobar($weiboSql);
					$logItem[$dbVal['name']]=$weiboRet[0]['mappingValue']?$weiboRet[0]['mappingValue']:'';
				}elseif ($key=='active'){
					if($data[$key] == 0){
						$logItem[$dbVal['name']] = '激活';
					}else if ($data['active'] == 1){
						$logItem[$dbVal['name']] = '冻结';
					}else{
						$logItem[$dbVal['name']] ='new game';
					}
				}elseif ($key=='gameUserName'){
					$logItem[$dbVal['name']]=str_replace("'", '&#39;', $data[$key]);
					$logItem[$dbVal['name']]=str_replace(">", '&gt;', $data[$key]);
					$logItem[$dbVal['name']]=str_replace("<", '&lt;', $data[$key]);
				}else{
					$logItem[$dbVal['name']]=$data[$key];
				}
				
			}
			/*$logItem['服务器'] = 's'.$data['server'];
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
			}*/
			
			//$banSql="select banTime from userprofile where uid='".$data['gameUid']."';";
			$banSql="select uid from user_task where uid='".$data['gameUid']."' and id like '%ban%'";
			$banRet=$page->executeServer($logItem['服务器'], $banSql, 3);
			$banTime=0;
			if (!$banRet['error'] && $banRet['ret']['data'] && $banRet['ret']['data'][0]['uid']){
					$banStateSql="select operator,reason,opeDate from banTime_reason where serverId='".$logItem['服务器']."' and uid='".$data['gameUid']."';";
					$ret=$page->globalExecute($banStateSql, 3);
					if (!$ret['error'] && $ret['ret']['data']){
						$logItem['封号详情']='<strong><font color="red">已封号!</font></strong><br>封号原因:'.$ret['ret']['data'][0]['reason'].'<br>操作人:'.$ret['ret']['data'][0]['operator'].'<br>操作时间:'.$ret['ret']['data'][0]['opeDate'];
					}else {
						$logItem['封号详情']='<strong><font color="red">已封号!</font></strong><br>封号时没有填写原因';
					}
			}else {
				$logItem['封号详情']='';
			}
			
			$log[] = $logItem;
			$i++;
		}
	$title = false;
	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i=0;
	foreach ($log as $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'><th>编号</th>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "<th>操作</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>".(++$i)."</td>";
		foreach ($sqlData as $key=>$value){
            if($key == '绑定Google账户' && !empty($value) /*&& $developer*/){
                $html .= "<td>" . $value . "<input type='button' onclick=\"edit('".$sqlData['UID']."','3')\" value='解绑Google'></input></td>";
            }elseif($key == '绑定Facebook账户'  && !empty($value) /*&& $developer*/){
                $html .= "<td>" . $value . "<input type='button' onclick=\"edit('".$sqlData['UID']."','4')\" value='解绑Facebook'></input></td>";
            }elseif($key == '绑定IOS账户'  && !empty($value) /*&& $_COOKIE['u']=='yaoduo'*/){
                $html .= "<td>" . $value . "<input type='button' onclick=\"edit('".$sqlData['UID']."','6')\" value='解绑IOS'></input></td>";
            }elseif($key == '绑定微博账户'  && !empty($value) /*&& $_COOKIE['u']=='yaoduo'*/){
                $html .= "<td>" . $value . "<input type='button' onclick=\"edit('".$sqlData['UID']."','7')\" value='解绑微博'></input></td>";
            }elseif($key == '绑定VK账户'  && !empty($value) /*&& $_COOKIE['u']=='yaoduo'*/){
                $html .= "<td>" . $value . "<input type='button' onclick=\"edit('".$sqlData['UID']."','8')\" value='解绑VK'></input></td>";
            }else{
    			$html .= "<td>" . $value . "</td>";
            }
		}
		$html .= "<td><input type='button' onclick=\"edit('".$sqlData['UID']."','2')\" value='激活'></input>&nbsp;&nbsp;<input type='button' onclick=\"find('".$sqlData['UID']."','".$sqlData['设备ID']."','".$firstUid."','".$firstServer."','".$sqlData['服务器']."',this)\" value='找回账号'></input>&nbsp;&nbsp;<input type='button' onclick=\"edit('".$sqlData['UID']."','10')\" value='删除'></input></td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}
//激活
if($type == 2){
    $global = 'cokdb_global';
    $muid = trim($_GET['muid']);
    if(empty($muid)) exit('no muid');
    if ($host == 'IPIPIP') {
    		$server = 'localhost';
    }elseif ($host == 'IPIPIP'){
   	 	$server = 'test';
    }else {
	    	$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
	    	$serverResult = $serverResult['ret']['data'][0];
	    	$server = $serverResult['server'];
	    	if(empty($server)) exit('no server');
	    	$server ='s'.$server;
    }
    
	$updateCurSql = "update userprofile set banTime=0 where uid = '$muid';";
	$result = $page->executeServer($server,$updateCurSql,2);
    
    $lastTime = time()*1000;
    $sql = "update `$global`.`account_new` set active = 0 , lastTime = {$lastTime}  where gameUid='{$muid}'";
//	$result = $page->globalExecute($sql,2);
    cobar_query_global_db_cobar($sql);
    
    	$taskSql="update user_task set id=SUBSTRING_INDEX(id,'_ban',1) where uid='$muid' and state=0 and id like '%_ban';";
    	$page->executeServer($server,$taskSql, 2);
    	
    	$time=time()*1000;
    	$opeDate=date('Y-m-d H:i:s');
    	$serverId=substr($server, 1);
    	$uuid=md5($serverId.$muid.$time);
    	$operator=$_COOKIE['u'];
    	$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$muid',$time,'$operator','解封','$opeDate')";
    	$page->globalExecute($sql, 2);
    	
    	$sql="update banTime_reason set status=0 where serverId='$server' and uid='$muid';";
    	$page->globalExecute($sql, 2);

    adminLogUser($adminid,$muid,$currentServer,array('user_bind_active'=>$lastTime));
    exit($sql);
}
//解绑google账号
if($type == 3){
    $global = 'cokdb_global';
    $muid = trim($_GET['muid']);
    if(empty($muid)) exit('no muid');
    if ($host == 'IPIPIP') {
    		$server = 'localhost';
    }elseif ($host == 'IPIPIP'){
    		$server = 'test';
    }else {
	    	$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
	    	$serverResult = $serverResult['ret']['data'][0];
	    	$server = $serverResult['server'];
	    	if(empty($server)) exit('no server');
	    	$server ='s'.$server;
    }

    $lastTime = time()*1000;
    $sql = "update `$global`.`account_new` set googleAccount = '',googleAccountName = '' , lastTime = {$lastTime}  where gameUid='{$muid}';";
//	$result = $page->globalExecute($sql,2);
    cobar_query_global_db_cobar($sql);
    $binded_acc = $serverResult['googleAccount'];
    cobar_delUserMapping('google', $binded_acc, $muid);
    
    AdminAuditLog::getInstance()->writeLog($adminid,$muid,$server,AdminAuditLog::ACTION_CHANGE_USER,json_encode(array('user_unbind_google'=>$lastTime)));
    exit($sql);
}

//解绑facebook账号
if($type == 4){
    $global = 'cokdb_global';
    $muid = trim($_GET['muid']);
    if(empty($muid)) exit('no muid');
    $host = gethostbyname(gethostname());
    if ($host == 'IPIPIP') {
   	 	$server = 'localhost';
    }elseif ($host == 'IPIPIP'){
    		$server = 'test';
    }else {
	    $serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
	    $serverResult = $serverResult['ret']['data'][0];
	    $server = $serverResult['server'];
	    if(empty($server)) exit('no server');
	    $server ='s'.$server;
    }
    
    $lastTime = time()*1000;
    $sql = "update `$global`.`account_new` set facebookAccount = '',facebookAccountName = '' , lastTime = {$lastTime}  where gameUid='{$muid}';";
//     $result = $page->globalExecute($sql,2);
    cobar_query_global_db_cobar($sql);
    $binded_acc = $serverResult['facebookAccount'];
    cobar_delUserMapping('facebook', $binded_acc, $muid);
    
    AdminAuditLog::getInstance()->writeLog($adminid,$muid,$server,AdminAuditLog::ACTION_CHANGE_USER,json_encode(array('user_unbind_fb'=>$lastTime)));
    exit($sql);
}

//解绑IOS账号
if($type == 6){
	$global = 'cokdb_global';
	$muid = trim($_GET['muid']);
	if(empty($muid)) exit('no muid');
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}else {
		$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
		$serverResult = $serverResult['ret']['data'][0];
		$server = $serverResult['server'];
		if(empty($server)) exit('no server');
		$server ='s'.$server;
	}

	$lastTime = time()*1000;
	$sql = "update `$global`.`account_new` set pf = '',pfId = '' , lastTime = {$lastTime}  where gameUid='{$muid}';";
	//     $result = $page->globalExecute($sql,2);
	cobar_query_global_db_cobar($sql);
	$binded_acc = $serverResult['pfId'];
	cobar_delUserMapping('AppStore', $binded_acc, $muid);

	AdminAuditLog::getInstance()->writeLog($adminid,$muid,$server,AdminAuditLog::ACTION_CHANGE_USER,json_encode(array('user_unbind_ios'=>$lastTime)));
	exit($sql);
}

//解绑微博账号
if($type == 7){
	$global = 'cokdb_global';
	$muid = trim($_GET['muid']);
	if(empty($muid)) exit('no muid');
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}else {
		$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
		$serverResult = $serverResult['ret']['data'][0];
		$server = $serverResult['server'];
		if(empty($server)) exit('no server');
		$server ='s'.$server;
	}
	
	$lastTime = time()*1000;
	$sql="delete from userbindmappingreverse where gameUid='$muid' and mappingType='weibo';";
	cobar_query_global_db_cobar($sql);
	
	$sql="delete from userbindmapping where gameUid='$muid' and mappingType='weibo';";
	cobar_query_global_db_cobar($sql);

	AdminAuditLog::getInstance()->writeLog($adminid,$muid,$server,AdminAuditLog::ACTION_CHANGE_USER,json_encode(array('user_unbind_weibo'=>$lastTime)));
	exit($sql);
}

//解绑VK账号
if($type == 8){
	$global = 'cokdb_global';
	$muid = trim($_GET['muid']);
	if(empty($muid)) exit('no muid');
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP'  || $host == 'IPIPIP' || $host == 'URLIP') {
		$server = 's1';
	}elseif ($host == 'IPIPIP'){
		$server = 's0';
	}else {
		$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
		$serverResult = $serverResult['ret']['data'][0];
		$server = $serverResult['server'];
		if(empty($server)) exit('no server');
		$server ='s'.$server;
	}

	$lastTime = time()*1000;
	$sql="delete from userbindmappingreverse where gameUid='$muid' and mappingType='vk';";
	cobar_query_global_db_cobar($sql);

	$sql="delete from userbindmapping where gameUid='$muid' and mappingType='vk';";
	cobar_query_global_db_cobar($sql);

	AdminAuditLog::getInstance()->writeLog($adminid,$muid,$server,AdminAuditLog::ACTION_CHANGE_USER,json_encode(array('user_unbind_vk'=>$lastTime)));
	exit($sql);
}

//找回账号
if($type == 5){
	$newUid = trim($_GET['newUid']);
	$oldUid = trim($_GET['oldUid']);
	$deciceID = trim($_GET['deciceID']);
	$newServer =trim($_GET['server']);
	$oldServer =trim($_GET['oldServer']);
	
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$newServer = 'localhost';
		$oldServer = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$newServer = 'test';
		$oldServer = 'test';
	}
	
	$updateCurSql = "update userprofile set banTime= 9223372036854775807 where uid = '$oldUid';";
	$result = $page->executeServer($oldServer,$updateCurSql,2);
	
	$operator=$_COOKIE['u'];
	$opeDate=date('Y-m-d H:i:s');
	$opeTime=time()*1000;
	
	$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('$oldServer','$oldUid','$operator','找回账号时封禁旧账号','$opeDate',1) ON DUPLICATE KEY UPDATE operator='$operator',reason='找回账号时封禁旧账号',opeDate='$opeDate',status=1;";
	$page->globalExecute($reasonSql, 2);
		
	$oldServerId=substr($oldServer, 1);
	$uuid=md5($oldServerId.$oldUid.$opeTime);
	$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$oldServerId,'$oldUid',$opeTime,'$operator','找回账号时封禁旧账号','$opeDate')";
	$page->globalExecute($sql, 2);
	
	//$time = (time()+7200)*1000;
	$time = (time()+86400)*1000;
	$updateWantSql = "update cokdb_global.account_new set active = 0, lastTime = $time where gameUid = '$newUid';";
// 	$result = $page->globalExecute($updateWantSql, 2);
	cobar_query_global_db_cobar($updateWantSql);
	
	$updateWantSql = "update userprofile set banTime=0 where uid = '$newUid';";
	$result = $page->executeServer($newServer,$updateWantSql,2);
	
	
	
	$newServerId=substr($newServer, 1);
	$uuid=md5($newServerId.$newUid.$opeTime);
	$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$newServerId,'$newUid',$opeTime,'$operator','找回账号时激活新账号','$opeDate')";
	$page->globalExecute($sql, 2);
	
	$sql="update banTime_reason set status=0 where serverId='$newServer' and uid='$newUid';";
	$page->globalExecute($sql, 2);
	
	adminLogUser($adminid,$newUid,$newServer,array("uid=$newUid"=>'banTime=0',"gameUid =$newUid"=>"active = 0,lastTime = $time"));
	exit(json_encode($result));
}

//删除uid对应设备(解除,修改设备号)
if($type == 10){
	$global = 'cokdb_global';
	$muid = trim($_GET['muid']);
	if(empty($muid)) exit('no muid');
	$old_deviceId = '';
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}else {
		$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($muid);
		$serverResult = $serverResult['ret']['data'][0];
		$old_deviceId = $serverResult['deviceId'];
	}

	if(!empty($old_deviceId)){
		$new_deviceId = $old_deviceId.'_bak';
		$sql = "update usermapping set mappingValue='$new_deviceId' where gameUid='$muid' and mappingType='device'";
		cobar_query_global_db_cobar($sql);
		$sql = "update account_new set deviceId='$new_deviceId' where gameuid='$muid'";
		cobar_query_global_db_cobar($sql);

		adminLogUser($adminid,$muid,$currentServer,array('user_bind_active'=>$lastTime));
	}
	exit($sql);
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>