<?php
//added by qinbin
// 20170117 op_banIP3.php 根据注册ip查询登陆ip


define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');


//运营排除在外的ip
$extraIpArr = require ADMIN_ROOT . '/etc/agentIpArray.php';

echo '----ban_ip start-------'.date('Ymd H:i:s').PHP_EOL;

global $servers;

$client = new Redis();
$client->connect('10.173.2.11',6379,3);

$key = 'op_banIP3';
$file = __DIR__;
$file = $file.'/banIpArr.php';

$ipArray = $client->lRange($key,0,-1);
$newIpArr = array();
foreach ($ipArray as $value){
	if (strpos($value, '10.')===0){
		continue;
	}
	if(!in_array($value,$extraIpArr)){
		$newIpArr[$value] = $value;
	}
}
$ipArray = $newIpArr;
$arrIPs = array_chunk($ipArray,25);

$month = date('n',time()) -1;
$tablename = 'stat_login_'.date('Y',time()).'_'.$month;
$data = array();
foreach($servers as $server=>$item){
	$host_info = $page->getMySQLInfo(false,$server);
	$mysql  = get_mysqli_connection($host_info);

	foreach($arrIPs as $key=>$ips) {
		$where =  '(\'' . implode('\',\'',$ips) .'\')';
		$sql = "select DISTINCT ip from $tablename where uid in(select uid from stat_reg where ip in $where)" ;
//				echo $sql.PHP_EOL;
		$result = query_from_db_new($mysql,$sql);
		foreach($result as $row){
			if(!in_array($row['ip'],$extraIpArr)) {
				$data[$row['ip']] = $row['ip'];
			}
		}
	}
}

$client->del($key);

if(file_exists($file)) {
	unlink($file);
}
$strarr = var_export($data, true);
file_put_contents($file, "<?php\n \$banIpArr= " . $strarr . ";\n return \$banIpArr;\n?>");

echo '----ban_ip end-------'.date('Ymd H:i:s').PHP_EOL;

