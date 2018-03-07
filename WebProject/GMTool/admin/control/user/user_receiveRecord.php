<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "输入玩家名字或者UID，可以查询这个玩家收到的赠送邮件";
$headAlert = "";
$lang = loadLanguage();
$clintXml = loadXml('goods','goods');
$title=array(
		'receiveTime'=>'收取时间',
		'user'=>'赠送人',
		'contents'=>'邮件内容',
		'goodsName'=>'赠送物品',
		'isRewarded'=>'是否领取',
		'isThanks'=>'是否感谢'
);
$length=count($title);
if ($type=='view') {
	if(!$username && (!$useruid)){
		$headAlert='请输入玩家姓名或者UID';
	}else{
		$whereSql='';
// 		if($username){
// 			$whereSql=" toName='$username' ";
// 		}else {
// 			$whereSql=" toUid='$useruid' ";
// 		}
		
		if($username){
			$account_list = cobar_getValidAccountList('name', $username);
			$uid = $account_list[0]['gameUid'];
			$whereSql=" toUid='$uid' ";
		}else{
			$whereSql=" toUid='$useruid' ";
		}
		
		$sql="select receiveTime,fromUid,fromName,contents,rewards,isOpened,isRewarded,isThanks from mail_gift_log where $whereSql;";
		$result = $page->globalExecute($sql, 3);
		$data=array();
		$i=1;
		foreach ($result['ret']['data'] as $curRow){
			$data[$i]['receiveTime']=$curRow['receiveTime']?date("Y-m-d H:i:s",$curRow['receiveTime']/1000):0;
			$data[$i]['user']=$curRow['fromName'].'('.$curRow['fromUid'].')';
			$data[$i]['contents']=$curRow['contents'];
			$temp=explode(",", $curRow['rewards']);
			$data[$i]['goodsName']=$lang[(int)$clintXml[$temp[1]]['name']].'*'.$temp[2];
			$data[$i]['isRewarded']=$curRow['isRewarded']?'是':'否';
			$data[$i]['isThanks']=$curRow['isThanks']?'是':'否';
			$i++;
		}
	}
	if($data){
		$showData = true;
	}else{
		$headAlert='数据查询失败';
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>