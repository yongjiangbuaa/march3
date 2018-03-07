<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "玩家荣誉";
$headAlert = "";
$dbArray = array(
	'uid' => array('name'=>'uid'),
	'glory' => array('name'=>'荣誉',),
	'todayGloryAdd' => array('name'=>'今天增加荣誉',),
	'update_time' => array('name'=>'刷新时间'),
);

if($type == 'view'){
	if($useruid) {
		$sql = "select * from user_glory where uid = '{$useruid}'";
	}else if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from user_glory where uid = '{$uid}'";
	}
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$item['update_time'] = date('Y-m-d H:i:s',$item['update_time']/1000);
		$showData = true;
	}else{
		$error_msg = search($result);
	}
}else if($type == 'rank'){
	$sql = "select uid,glory from user_glory order by glory desc limit 50";
	$result = $page->execute($sql,3);

	if($result['ret']['data']) {
		$showData = true;
		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>NO.</th><th>uid</th><th>荣誉值</th></tr></thead>";
	$i = 1;
		foreach ($result['ret']['data'] as $currow) {
			$html .= "<tr><td>$i</td><td>{$currow['uid']}</td><td>{$currow['glory']}</td></tr>";
			++$i;
		}

		$html .= '</table></div>';
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>