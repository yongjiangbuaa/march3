<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = true;

global $servers;
$file = '/data/htdocs/ifadmin/admin/scripts/banIpArr.php';
if(file_exists($file)){
	$data = include_once $file;
}else{
	$data = array('无数据');
}
$cmd = 'ps -ef | grep ban_ip.php | grep -v grep | wc -l';
exec($cmd,$ret);
if($ret[0] >= 1){
//	print_r($ret);
	$headAlert = '正在扫描中...稍后查看';
}
//"查询IP" check_submit('view')"
if ($_REQUEST['action']) {
	$queryIps = $_REQUEST['queryIps'];
	$type = $_REQUEST['action'];

	if($type == 'del'){
		unlink($file);
		$data = array();
	}else {
		if (empty($queryIps)) {
			exit('IP不能为空!');
		} else {
			$info = "";
			$queryIps = str_replace('；', ';', $queryIps);
//		$queryIps=str_replace('：', ':', $queryIps);
			$queryIps = trim($queryIps);
			$queryIps = trim($queryIps,';');
			$ipArray = explode(';', $queryIps);//2.3.3.3


			$client = new Redis();
			if (!$client->connect('127.0.0.1', 6379, 3)) {
				$headAlert = '连接redis失败';
			} else {
				$key = 'op_banIP3';
				// 批量插入
				$pipe = $client->multi(Redis::PIPELINE);
				foreach ($ipArray as $i) {
					$pipe->rPush($key, $i);//尾部插入
				}
				$pipe->exec();

				shell_exec('/home/elex/php/bin/php /data/htdocs/ifadmin/admin/scripts/ban_ip.php >> /data/htdocs/ifadmin/admin/scripts/ban_ip.log 2>&1 &');
			}
			$headAlert .= "正在全服扫描查询,请稍后查看";
		}
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>