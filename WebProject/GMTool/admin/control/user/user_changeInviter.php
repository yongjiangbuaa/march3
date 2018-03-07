<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['inviteeUid'])
	$inviteeUid = $_REQUEST['inviteeUid'];
if($_REQUEST['currentInviterUid'])
	$currentInviterUid = $_REQUEST['currentInviterUid'];
if($_REQUEST['changedInviterUid'])
	$changedInviterUid = $_REQUEST['changedInviterUid'];
if ($type=='change') {
	$sql="delete from inviter where uid='$currentInviterUid' and inviteeUid='$inviteeUid';";
	$page->execute($sql, 2, true);
	$sql="delete from invitee where uid='$inviteeUid' and inviterUid='$currentInviterUid';";
	$page->execute($sql, 2, true);
	$time=time()*1000;
	$uuid=md5($inviteeUid.$changedInviterUid.$time);
	$sql="insert into inviter(uuid,uid,inviteeUid,time) values('$uuid','$changedInviterUid','$inviteeUid',$time);";
	$page->execute($sql, 2, true);
	$sql="insert into invitee(uid,inviterUid,time) values('$inviteeUid','$changedInviterUid',$time);";
	$page->execute($sql, 2, true);
	$html="更换完毕!";
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>