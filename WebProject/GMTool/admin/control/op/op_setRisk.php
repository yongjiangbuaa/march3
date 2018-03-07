<?php
!defined('IN_ADMIN') && exit('Access Denied');
$title='设定充值预警的风险值';
$showData = false;
$headAlert='';

$dbIndex=array(
	'k1',
	'k2',
	'k3'
);
if($_REQUEST['analyze']=='user'){
	$sql="select * from risk_setting where id='pay';";
	$ret=$page->globalExecute($sql, 3);
	$data=array();
	foreach ($ret['ret']['data'] as $row){
		foreach ($dbIndex as $dbIndexVal){
			$data[$dbIndexVal]=$row[$dbIndexVal]?$row[$dbIndexVal]:0;
		}
	}
	
	if ($data){
		$showData = true;
	}else {
		$headAlert='没有查到相关数据';
	}
}

//更新风险值
if($_REQUEST['type'] == 'modify')
{
	$riskLevel = $_REQUEST['riskLevel'];
	$riskValue = $_REQUEST['riskValue'];
	$sql = "update risk_setting set $riskLevel=$riskValue where id='pay';";
	$result = $page->globalExecute($sql, 2);
	if(!$result['error'] && $result['ret']['result']==1){
		$redis = new Redis();
		$host = gethostbyname(gethostname());
		if ($host == 'IPIPIP' || $host == 'IPIPIP') {
			$redis->connect('URLIP',6379);//72的global库
		}elseif ($host == 'IPIPIP'){
			$redis->connect('10.142.9.80',6379);
		}else {
			$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		}
		$ret=$redis->del('RISK_SETTING');
		if ($ret>0){
			exit('数据更新成功!');
		}else {
			exit('redis没有更新成功!');
		}
	}
	exit('数据更新失败!');
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>