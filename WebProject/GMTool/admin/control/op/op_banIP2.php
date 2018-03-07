<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;

global $servers;
//"禁封IP" check_submit('view')"
//"解封IP" delete_submit('delete')"
if ($_REQUEST['action']) {
	$queryIps = $_REQUEST['queryIps'];
	$type = $_REQUEST['action'];

	if (empty($queryIps)){
		exit('IP不能为空!');
	}else {
		$info="";
		$queryIps=str_replace('；', ';', $queryIps);
		$queryIps=str_replace('：', ':', $queryIps);
		$queryIps=trim($queryIps);
		$ipArray=explode(';', $queryIps);//2.3.3.3:s4
		//运营排除在外的ip
		$extraIpArr = require ADMIN_ROOT . '/etc/agentIpArray.php';

		$errorIpArr = array();
		$newIpArr = array();
		foreach ($ipArray as $value){
			$tmp = explode(':',$value);
			if(count($tmp) != 2 || strpos($tmp[0],'.') === false){
				exit('输入有误,格式不对,ip:server');
			}
			if (strpos($tmp[0], '10.')===0){
				exit('IP不能以10.开始!');
			}
			if(!in_array($tmp[0],$extraIpArr)){
				$newIpArr[$tmp[1]][] = $tmp[0];//$newIpArr[s1]= array('ip1','ip2');
			}
		}
		$page = new BasePage();
		foreach($newIpArr as $server=>$ipArr){
			$redis=new Redis();
			$redis->connect($servers[$server]['ip_inner'],6379);

			if ($_REQUEST['action']=='view'){
				foreach ($ipArr as $value){
					$value=trim($value);
					if (empty($value)){
						continue;
					}
					$ret=$redis->sAdd('ban_ip_set',$value);

					$sql = "update userprofile  set banTime=9223372036854775807 where uid in(select DISTINCT uid from stat_reg where ip='$value') ";
					$page->executeServer($server,$sql,2,true);
				}

			}

			$redis->close();


		}

		$redis=new Redis();
		$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		if ($_REQUEST['action']=='view'){ //添加
			foreach ($newIpArr as $server=>$ipArr) {
				foreach ($ipArr as $value) {
					$value = trim($value);
					if (empty($value)) {
						continue;
					}
					$result = $redis->sAdd('ban_ip_set', $value);
				}
			}
		}
		$redis->close();
		
		adminLogUser ( $adminid, '', '', array (
			'ips'=>$ipArray,
			)
		);
		
		$info="操作成功!";
		exit($info);
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>