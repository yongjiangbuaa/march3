<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['allianceName'])
	$allianceName = $_REQUEST['allianceName'];
$dbArray = array(
		'uid' => array('name'=>'联盟UID',),
		'alliancename' => array('name'=>'联盟名称',),
		'abbr' => array('name'=>'联盟简称',),
		'curMember' => array('name'=>'当前成员数',),
		'maxMember' => array('name'=>'最大成员数',),
		'createtime' => array('name'=>'联盟创建时间',),
);

if ($type=='view') {
	$sql = "select uid,alliancename,abbr,curMember,maxMember,createtime from alliance where alliancename='$allianceName';";
	$result = $page->execute($sql, 3);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$item['alliancename'] = $item['alliancename']."
		        <input class='input-medium' id='changename' name='changename' type='text' value='COK". intval(microtime(true)*1000)
			        ."' size='50' /><input class='btn js-btn btn-primary' type='button' value='修改' id='btn_change' name='btn_change' onclick='dochangename(".
			       '"'.$item['uid'].'"'.");' />";
		$item['createtime'] = date('Y-m-d H:i:s',$item['createtime']/1000);
		$showData = true;
	}else{
		$error_msg = search($result);
	}
}

if($_REQUEST['dochangename']){
	$allianceUid = $_REQUEST['changeuid'];
	$newAllianceName = $_REQUEST['changename'];
	if (empty($newAllianceName)) {
		exit('ERROR: name is empty!');
	}
	$sql = "select count(allianceId) cnt from alliance where alliancename='$newAllianceName'";
	$result = $page->globalExecute($sql, 3);
	if ($result['ret']['data'][0]['cnt'] > 0) {
		exit("ERROR: 该用户名[".$newAllianceName."]已经存在!");
	}
	$sql="select pointid from user_world where uid in (select am.uid from alliance a inner join alliance_member am on a.uid=am.allianceId where a.uid='$allianceUid');";
	$result = $page->execute($sql, 3);
	foreach ($result['ret']['data'] as $curRow){
		if($curRow['pointid']){
			$pointid=$curRow['pointid'];
			$currserver = $page->getAppId();
			$serverinfo = $servers[$currserver];
			if ($currserver == 'test' || $currserver == 'localhost') {
				$t = explode(':', $serverinfo['webbase']);//http://IPIPIP:8080/gameservice/
				$ip = substr($t[1], 2);
				$rediskey = 'world0';
			}else{
				$ip = $serverinfo['ip_inner'];
				$rediskey = 'world'.substr($currserver, 1);
			}
			$redis = new Redis();
			$redis->connect($ip,6379);
			$rd_json = $redis->hGet($rediskey, $pointid);
			if (!empty($rd_json)) {
				$rd_arr = json_decode($rd_json, true);
				$rd_arr['afn'] = $newAllianceName;
				$rd_json = json_encode($rd_arr);
				$redis->hSet($rediskey, $pointid, $rd_json);
			}
		}
	}
	$sql= "update alliance set alliancename='$newAllianceName' where uid='$allianceUid';";
	$page->execute($sql, 2, true);
	$sql = "update alliance set alliancename='$newAllianceName' where allianceId='$allianceUid'";
	$page->globalExecute($sql, 2, true);
	exit('OK');
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>