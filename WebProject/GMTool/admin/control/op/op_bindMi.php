<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
if($_REQUEST['action'] == 'query'){
	$url = getUrl($_REQUEST['user']);
	$httpResult = $page->post_request($url, $params, 30, true);
	$httpObj = json_decode($httpResult,true);
	if($httpObj['errcode'] == 0){
		$miUid = $httpObj['result'];
// 		$miUid = '83001988';
		showMiAccount($miUid);
		exit;
	}else{
		echo '查询小米帐号出错: '.$httpResult;
		exit;
	}
}
if($_REQUEST['action'] == 'bind'){
	$miUid = $_REQUEST['miUid'];
	$fromUid = $_REQUEST['fromUid'];
	$targetUid = $_REQUEST['targetUid'];
	if($targetUid){
		$accountInfo = cobar_getAccountInfoByGameuids($targetUid);
		if($accountInfo){
			$sql = "update userbindmapping set gameUid = '$targetUid' where mappingType = 'cn_mi' and mappingValue = '$miUid' and gameUid = '$fromUid' limit 1";
			cobar_query_global_db_cobar($sql);
			$sql = "update userbindmappingreverse set gameUid = '$targetUid' where mappingType = 'cn_mi' and mappingValue = '$miUid' and gameUid = '$fromUid' limit 1";
			cobar_query_global_db_cobar($sql);
			
			echo "帐号绑定切换完成，以下为更新后的数据";
			adminLogUser ( $adminid, $fromUid, '', array (
				'fromUid' => $fromUid,
				'miUid' => $miUid,
				'targetUid' => $targetUid
			) );
		}else{
			echo "<font color='red'>目标uid无对应玩家</font>";
		}
	}else{
		echo "<font color='red'>目标uid为空</font>";
	}
	showMiAccount($miUid);
	exit;
}

function showMiAccount($miUid){
	echo "小米帐号uid: $miUid<br />";
	$sql = "select * from userbindmapping where mappingType = 'cn_mi' and mappingValue = '$miUid'";
	$accounts = cobar_query_global_db_cobar($sql);
	if(!$accounts){
		echo '当前小米帐号未绑定角色';
		exit;
	}
	$target_gameuids = array();
	foreach ($accounts as $account){
		$target_gameuids[] = $account['gameUid'];
	}
	$accountsInfo = cobar_getAccountInfoByGameuids($target_gameuids);
	
	foreach ($accountsInfo as $curRow)
	{
		$logItem = array();
		$logItem['王国'] = $curRow['server'];
		$logItem['uid'] = $curRow['gameUid'];
		$logItem['名字'] = $curRow['gameUserName'];
		$logItem['等级'] = $curRow['gameUserLevel'];
		$logItem['上次离线时间'] = date('Y-m-d H:i:s',$curRow['lastTime']/1000);
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 0;
	foreach ($log as $curRow)
	{
		if(!$title){
			foreach ($curRow as $key=>$value)
				$html .= "<th style='text-align:center;'>" . $key . "</th>";
			$html .= "<th style='text-align:center;'>" . '切换绑定到指定uid' . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$i++;
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($curRow as $key=>$value){
			$html .= "<td>" . $value . "</td>";
		}
		$fromUid = $curRow['uid'];
		$inputId = "target$i";
		$html .= "<td><input class='input-medium focused' id='$inputId' name='$inputId' type='text' placeholder='输入目标玩家uid' value=''></td>";
		$html .= "<td><input class='btn js-btn btn-primary' type='button' value='切换' onclick=changeBindData('$fromUid','$miUid','$inputId')></td>";
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
function getUrl($accountName){
	$appId = '2882303761517366668';
	$encryptText = "appId=$appId&username=$accountName";
	$secret = 'Jyj3M4inF5OFJBsqV8R0Eg==';
	$sig = hash_hmac('sha1', $encryptText, $secret);
	$url = "http://hy.game.xiaomi.com/fpassport/api/getFuid?appId=$appId&username=$accountName&sig=$sig";
	return $url;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>