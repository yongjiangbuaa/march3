<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST ['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectId=$erversAndSidsArr['onlyNum'];

if($_REQUEST['type'] == 'modify')
{
	$serverTemp = $_REQUEST['server'];
	$temp = explode('_', $serverTemp);
	$modifyServer = $temp[0];
	$uid = $temp[1];
	$modifyActName=$temp[2];
	$newValue = $_REQUEST['newValue'];
	if($modifyActName=='modGoldGetTimeInterval'){
		$newValue = $newValue*60*60*1000;
	}
	$modifySql = "update userprofile set $modifyActName ='$newValue' where uid = '{$uid}'";
	if($modifySql){
		$result = $page->executeServer($modifyServer,$modifySql,2, true);
        adminLogUser($adminid,$uid,$modifyServer,array('modify_mod'=>$modifyActName.':'.$newValue));
	}else{
		echo "  <font color='#FF0000'>SQL语句为空，查询异常</font>".'<br />';
	}
	$type='view';
}

if($_REQUEST['type'] == 'ban'){ //修改mod玩家的禁言权限
	$uid = $_REQUEST['uid'];
	$server = $_REQUEST['server'];
	$typeSwitch = $_REQUEST['typeSwitch'];
	if($typeSwitch=='on'){
		$sql = "insert into mod_authorize(uid,authorize) values('$uid',1) ON DUPLICATE KEY UPDATE authorize=1;";
	}else{
		$sql = "insert into mod_authorize(uid,authorize) values('$uid',0) ON DUPLICATE KEY UPDATE authorize=0;";
	}
	$result = $page->executeServer($server,$sql,2);
	if(!$result['error']){
		exit('OK');
	}else {
		exit('操作失败');
	}
}

if($_REQUEST['type'] == 'delete')//删除
{
	$uid = $_REQUEST['uid'];
	$server = $_REQUEST['server'];
	$gmFlag = $_REQUEST['gmFlag'];
	$sql = "update userprofile set gmFlag=0 WHERE uid='{$uid}'";
	$result = $page->executeServer($server,$sql,2);
	if($gmFlag==2||$gmFlag==5){
		$deleteSql="delete from mod_info where uid='{$uid}';";
		$deleteResult=$page->globalExecute($deleteSql, 2);
	}
    adminLogUser($adminid,$uid,$server,array('delect_mod'=>$gmFlag));
    exit("OK");
	//$type='view';
}
if($type=='view'){
// 	$currentServer = $_COOKIE['Gserver2'];
// 	$server = substr($currentServer, 1);
	if($username){
		$sql="SELECT u.uid,u.name,u.level,u.gmFlag,u.lastOnlineTime,ah.authorize,COUNT(distinct ma.fromUser) AS usersCount,COUNT(ma.toUser) AS cntAll,ma.mailType,u.modGoldGetTimeInterval/60/60/1000 AS modGoldGetTimeInterval,u.lastModGoldGetTime,u.modGoldAmount FROM userprofile u left join mod_authorize ah on u.uid=ah.uid LEFT JOIN mod_record ma ON (u.uid = ma.toUser AND ma.mailType=1) or (u.uid = ma.fromUser AND ma.mailType=0) WHERE u.name='{$username}' and u.gmFlag IN (2,4,5) GROUP BY uid, mailType, gmFlag,  lastModGoldGetTime,  modGoldGetTimeInterval,  modGoldAmount;";
	}else if($useruid){
		$sql="SELECT u.uid,u.name,u.level,u.gmFlag,u.lastOnlineTime,ah.authorize,COUNT(distinct ma.fromUser) AS usersCount,COUNT(ma.toUser) AS cntAll,ma.mailType,u.modGoldGetTimeInterval/60/60/1000 AS modGoldGetTimeInterval,u.lastModGoldGetTime,u.modGoldAmount FROM userprofile u left join mod_authorize ah on u.uid=ah.uid LEFT JOIN mod_record ma ON (u.uid = ma.toUser AND ma.mailType=1) or (u.uid = ma.fromUser AND ma.mailType=0) WHERE u.uid='{$useruid}' and u.gmFlag IN (2,4,5) GROUP BY uid, mailType, gmFlag,  lastModGoldGetTime,  modGoldGetTimeInterval,  modGoldAmount;";
	}/* else if($username&&$useruid){
			  WHERE u.name='{$username}' and u.gmFlag IN (2, 4) 
		WHERE u.uid='{$useruid}' and u.gmFlag IN (2, 4)
		$sql="SELECT u.uid,u.name,u.level,u.gmFlag,u.lastOnlineTime,COUNT(ma.toUser) AS cntAll,ma.type,u.modGoldGetTimeInterval/60/60/1000 AS modGoldGetTimeInterval,u.lastModGoldGetTime,u.modGoldAmount FROM userprofile u LEFT JOIN mail ma ON u.uid = ma.toUser AND ma.type in (23,24) WHERE u.gmFlag IN (2, 4) GROUP BY uid, type, gmFlag,  lastModGoldGetTime,  modGoldGetTimeInterval,  modGoldAmount;";
	} */else{
		$sql="SELECT u.uid,u.name,u.level,u.gmFlag,u.lastOnlineTime,ah.authorize,COUNT(distinct ma.fromUser) AS usersCount,COUNT(ma.toUser) AS cntAll,ma.mailType,u.modGoldGetTimeInterval/60/60/1000 AS modGoldGetTimeInterval,u.lastModGoldGetTime,u.modGoldAmount FROM userprofile u left join mod_authorize ah on u.uid=ah.uid LEFT JOIN mod_record ma ON u.uid = ma.toUser WHERE ma.mailType=1 and u.gmFlag IN (2,4,5) GROUP BY uid, mailType, gmFlag,  lastModGoldGetTime,  modGoldGetTimeInterval,  modGoldAmount 
				union SELECT u.uid,u.name,u.level,u.gmFlag,u.lastOnlineTime,ah.authorize,COUNT(distinct ma.fromUser) AS usersCount,COUNT(ma.toUser) AS cntAll,ma.mailType,u.modGoldGetTimeInterval/60/60/1000 AS modGoldGetTimeInterval,u.lastModGoldGetTime,u.modGoldAmount FROM userprofile u left join mod_authorize ah on u.uid=ah.uid LEFT JOIN mod_record ma ON u.uid = ma.fromUser WHERE ma.mailType=0 and u.gmFlag IN (2,4,5) GROUP BY uid, mailType, gmFlag,  lastModGoldGetTime,  modGoldGetTimeInterval,  modGoldAmount;";
	}
	//print_r($sql);
	$i=1;
	$uids=array();
	foreach ($selectServer as $server=>$serInfo){
	/* 	if(substr($server, 0 ,1) != 's'){
			continue;
		} */
		$result = $page->executeServer($server,$sql,3);
		foreach ($result['ret']['data'] as $curRow)
		{
			$pre=$data[$curRow['uid']];
			if(isset($pre)){
				if($curRow['mailType']==0){
					$data[$curRow['uid']]['cntReply']=$curRow['cntAll']?$curRow['cntAll']:0;
				}
				if($curRow['mailType']==1){
					$data[$curRow['uid']]['cntAll']=$curRow['cntAll']?$curRow['cntAll']:0;
					$data[$curRow['uid']]['usersCount']=$curRow['usersCount']?$curRow['usersCount']:0;
				}
				
				if($data[$curRow['uid']]['cntAll']!=0 && $data[$curRow['uid']]['cntAll']!=null){
					if($data[$curRow['uid']]['cntReply']!=null&&$data[$curRow['uid']]['cntReply']!=''){
						$data[$curRow['uid']]['rp']=(round($data[$curRow['uid']]['cntReply']/$data[$curRow['uid']]['cntAll'],2)*100)."%";
					}else{
						$data[$curRow['uid']]['rp']="0%";
					}
				}else{
					$data[$curRow['uid']]['rp']="00%";
				}
			}else {
				$uid=$curRow['uid'];
				$data[$uid]['num']=$i;
				$data[$uid]['server']=$server;
				$data[$uid]['uid']=$curRow['uid'];
				$data[$uid]['name']=$curRow['name'];
				$data[$uid]['level']=$curRow['level'];
				$data[$uid]['gmFlag']=$curRow['gmFlag'];
				$data[$uid]['authorize']=$curRow['authorize']?'on':'off';
				$data[$uid]['lastOnlineTime']=date('Y-m-d H:i:s', $curRow['lastOnlineTime']/1000);
				if($curRow['mailType']==0){
					$data[$uid]['cntReply']=$curRow['cntAll']?$curRow['cntAll']:0;
				}
				if($curRow['mailType']==1){
					$data[$uid]['cntAll']=$curRow['cntAll']?$curRow['cntAll']:0;
					$data[$uid]['usersCount']=$curRow['usersCount']?$curRow['usersCount']:0;
				}
				$data[$uid]['modGoldGetTimeInterval']=intval($curRow['modGoldGetTimeInterval']);
				$data[$uid]['modGoldAmount']=$curRow['modGoldAmount'];
				$i++;
			}
		}
	}
	if(!$data){
		echo "  <font color='#FF0000'>没有查到数据</font>".'<br />';
	}else{
		$showData = true;
	}
	//print_r($sql);
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>