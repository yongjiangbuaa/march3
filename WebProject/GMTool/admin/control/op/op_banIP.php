<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;

global $servers;
//"禁封IP" check_submit('view')"
//"解封IP" delete_submit('delete')"
//"查询IP" search_submit('search')"
$cmd = 'ps -ef | grep ban_op_ip.php | grep -v grep | wc -l';
exec($cmd,$ret);
if($ret[0] >= 1){
	$headAlert = '封禁IP队列正在执行';
}
if ($_REQUEST['action'] && $_REQUEST['action'] != 'search') {
	$queryIps = $_REQUEST['queryIps'];
	$type = $_REQUEST['action'];

	if (empty($queryIps)){
		exit('IP不能为空!');
	}else {
		$info="";
		$queryIps=str_replace('；', ';', $queryIps);
		$queryIps=trim($queryIps);
		$queryIps=trim($queryIps,';');
		$ipArray=explode(';', $queryIps);
		//运营排除在外的ip
		$extraIpArr = require ADMIN_ROOT . '/etc/agentIpArray.php';

		$errorIpArr = array();
		$newIpArr = array();
		foreach ($ipArray as $value){
			if (strpos($value, '10.')===0){
				exit('IP不能以10.开始!');
			}
			if(in_array($value,$extraIpArr)){
				$errorIpArr[] = $value;
			}else{
				$newIpArr[] = $value;
			}
		}
		//自动过滤掉,不提示了
//		if(count($errorIpArr) >0 && $_REQUEST['action'] == 'view'){
//			exit('IP 不能封禁'.var_dump($errorIpArr));
//		}
		$ipArray = $newIpArr;
		if($type == 'view'){
			$rediskey = 'op_banIP';

			$client = new Redis();
			if (!$client->connect('10.173.2.11', 6379, 3)) {
				$headAlert = '连接redis失败';
			} else {
				// 批量插入
				$pipe = $client->multi(Redis::PIPELINE);
				foreach ($ipArray as $i) {
					$pipe->rPush($rediskey, $i);//尾部插入
				}
				$pipe->exec();

				shell_exec('/home/elex/php/bin/php /data/htdocs/ifadmin/admin/scripts/ban_op_ip.php >> /data/htdocs/ifadmin/admin/scripts/ban_op_ip.log 2>&1 &');
			}
			$client->close();
			adminLogUser ( $adminid, '', '', array (
					'ips'=>$ipArray,
				)
			);
			$headAlert .= "加入封禁IP对列成功";
			exit($headAlert);
		}
//==================================================================
		foreach ($servers as $server=>$serverInfo){
			$redis=new Redis();
			$redis->connect($serverInfo['ip_inner'],6379);
			if ($_REQUEST['action']=='view'){
				foreach ($ipArray as $value){
					$value=trim($value);
					if (empty($value)){
						continue;
					}
					$ret=$redis->sAdd('ban_ip_set',$value);
				}
			}else {
				foreach ($ipArray as $value){
					$value=trim($value);
					if (empty($value)){
						continue;
					}
					$ret=$redis->sRemove('ban_ip_set',$value);
				}
			}
			$redis->close();
		}
		$redis=new Redis();
		$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		if ($_REQUEST['action']=='view'){ //添加
			foreach ($ipArray as $value){
				$value=trim($value);
				if (empty($value)){
					continue;
				}
				$result=$redis->sAdd('ban_ip_set',$value);
			}
		}else {
			foreach ($ipArray as $value){
				$value=trim($value);
				if (empty($value)){
					continue;
				}
				$result=$redis->sRemove('ban_ip_set',$value);
			}
		}
		$redis->close();
		
		/////////////////////////////
		$redis=new Redis();
		$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		$whitelist = $redis->sMembers('whitelist_ip_set');
		$addedIps = array();
		foreach ($extraIpArr as $value){
			$value=trim($value);
			if (empty($value)){
				continue;
			}
			if(!in_array($value,$whitelist)){
				$addedIps[] = $value;
			}
		}
		foreach ($addedIps as $value){
			$result=$redis->sAdd('whitelist_ip_set',$value);
		}
		$redis->close();
		///////////////////////////////////
		/*
		//把白名单重新刷到全局redis中，给各个sfs服使用
		$redis=new Redis();
		$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		$redis->delete("whitelist_ip_set");
		// 批量插入
		$pipe = $redis->multi(Redis::MULTI);
		$pipe->delete("whitelist_ip_set");
		foreach ($extraIpArr as $value){
			$value=trim($value);
			if (empty($value)){
				continue;
			}
			$pipe->sAdd('whitelist_ip_set',$value);
		}
		$pipe->exec();
		$redis->close();
		*/
		///////////////////////////////////////////////////////////

		adminLogUser ( $adminid, '', '', array (
			'ips'=>$ipArray,
			)
		);
		
		$info="操作成功!";
		exit($info);
	}
}else{
	$type = $_REQUEST['action'];

	if($type == 'search'){
		$redis=new Redis();
		$ret = $redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		if(!$ret){
			$html .= '<p>connect error</p>';
		}else{
			$data = $redis->sMembers('ban_ip_set');
			$showData = true;
		}
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>