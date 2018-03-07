<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
if (array_key_exists ( 'type', $_REQUEST )) {
	$type = $_REQUEST ['type'];
}
if (array_key_exists ( 'page', $_REQUEST )) {
	$showpage = $_REQUEST ['page'];
}
if ($type == 'add') {
	$params = $_POST;
	$sendBy = $page->getAdmin ();
	switch ($params ['userType']) {
		case 'name' : // 角色名
			$sql = "select uid from userprofile where name = '{$_REQUEST['user']}'";
			break;
		case 'uid' : // UID
			$sql = "select uid from userprofile where uid = '{$_REQUEST['user']}'";
			break;
	}
	echo $sql;
	$tmp = $page->execute ( $sql, 3 );
	$uid = $tmp ['ret'] ['data'] [0] ['uid'];
	if (! $uid) {
		$error_msg = "user not found";
	} else {
		$toUser = $uid;
		$sendTime = microtime ( true ) * 1000;
		$title = addslashes ( $params ['title'] ); //
		$contents = addslashes ( $params ['contents'] ); //
		$uid = md5 ( $toUser . $sendBy . $sendTime . $title . $contents . time () );
		$rewardStatus = 1;
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`,`rewardStatus`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents',$rewardStatus)";
		$page->execute ( $sql, 2 );
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', $sendTime, 0, 1,107)";
		$page->execute ( $sql, 2 );
		sendReward( $uid );
		
		adminLogUser ( $adminid, $uid, $currentServer, array (
			'fileMail'=>'add',
			'sendTime' => $sendTime
			)
		);
	}
}
function sendReward($mailUid) {
	$page = new BasePage ();
	$page->webRequest ( 'sendmail', array (
			'uid' => $mailUid 
	) );
}
if($showpage > 0)
{
	$page_limit = 15;
	$sql = "select count(1) DataCount from server_usermail where rewardStatus=1 and reward is null;";
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['DataCount'];

	$pager = page($count, $showpage, $page_limit);
	$index = $pager['offset'];

	$sql = "select s.*,u.name from server_usermail s left join userprofile u on s.toUser = u.uid where rewardStatus=1 and reward is null order by `sendTime` desc limit $index,$page_limit";
	$result = $page->execute($sql,3);
	if($result['error'] == 'no data')
		echo '没有邮件';
	else{
		foreach ($result['ret']['data'] as $mailItem)
		{
			$mailItem['sendTime'] = date('Y-m-d H:i:s',$mailItem['sendTime']/1000);
			$rewardStatus = $mailItem['rewardStatus'];
			$mailItem['rewardFail'] = $rewardStatus;
			$mailData[] = $mailItem;
		}
		$titleData = array('toUser'=>'玩家UID','sendBy'=>'发送人','name'=>'玩家名字','sendTime'=>'发送时间','title'=>'标题','contents'=>'内容');
		$titleWidth = array('contents'=>'40%');
	}
	$html = "<div style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'>
	<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th>编号</th>";
	foreach ($titleData as $key=>$value){
		if($titleWidth[$key])
			$html .= "<th width='{$titleWidth[$key]}'>" . $value . "</th>";
		else
			$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";
	$i = 1;
	foreach ($mailData as $sort=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		foreach ($titleData as $key=>$value){
			$td = $sqlData[$key];
			$html .= "<td>" . $td . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit;
}
include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>