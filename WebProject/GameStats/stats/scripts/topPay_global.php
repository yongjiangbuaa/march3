<?php
error_reporting(E_ALL);
//ini_set('display_errors', true);//设置开启错误提示
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT . '/stats.inc.php';

require_once STATS_ROOT.'/infobright/ib.inc.php';
$server_list = get_db_list();

$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}

$data=array();
foreach ($slave_db_list as $sid=>$DBvalue){
	if ($sid>900000){
		continue;
	}
	$link = mysqli_connect($slave_db_list[$sid]['slave_ip_inner'],GAME_DB_SERVER_USER, GAME_DB_SERVER_PWD,$slave_db_list[$sid]['dbname'],$slave_db_list[$sid]['port']);
	$sql ="select uid,sum(spend) sumPay from paylog where status!=4 group by uid;";
	$res = mysqli_query($link,$sql);
	while ($row = mysqli_fetch_assoc($res)){
		$data[$row['uid']]+=$row['sumPay'];
	}
	mysqli_close($link);
}
echo date("Y-m-d H:i").",数据量: ".count($data)."\n";
foreach ($data as $uid=>$payVal){
	file_put_contents("/data/log/uidTopPay.log","$uid,$payVal\n",FILE_APPEND);
}
$client = new Redis ();
$ret=$client->connect ('10.121.248.63', 6380);
$key = "topPay";
$start=time();
echo "开始插入redis\n";
$handle = @fopen("/data/log/uidTopPay.log", "r");
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		$buffer = trim($buffer);
		if (empty($buffer)) continue;
		$buffer=str_replace('，', ',', $buffer);
		$temp=explode(',', $buffer);
		$uid=$temp[0];
		$pay=$temp[1];
		$client->zAdd($key,$pay,$uid);
		$client->zScore($key, $uid);
		file_put_contents("/data/log/uidTopPay.log1","$uid,$pay\n",FILE_APPEND);
	}
}
echo "插入redis结束，耗费时间: ".(time()-$start)."秒\n";
$client->close();

@unlink("/data/log/uidTopPay.log");
// if (file_exists("/data/log/uidTopPay.log")) {
// 	unlink("/data/log/uidTopPay.log");
// }

// $client = new Redis ();
// $ret=$client->connect ('10.81.103.90');
// var_dump($ret);
// $key = "topPay";
// echo "ok1\n";
// //$chunks = array_chunk($data, 2000, true);
// $chunks = array_chunk_list($data,2000,true);
// echo "ok2\n";
// echo count($chunks);
// echo '时间'.date('Y-m-d H:i:s').",总数:".count($data).",分".count($chunks)."次插入redis中\n";
// foreach ($chunks as $datachunk) {
// 	$args = array();
// 	$args[] = $key;
// 	foreach ($datachunk as $uidKey=>$value){
// 		$args[] =intval($value);
// 		$args[] =$uidKey;
// 	}
// 	call_user_func_array(array($client,'zAdd'),$args);
// }
// $client->close();

//$array 数组
//$size  每个数组的个数
//每个数组元素是否默认键值
function array_chunk_list($array, $size, $preserve_keys = false)
{
	$i = 0;
	foreach ($array as $key => $value) {
		// 是否存在这个值
		if (! isset($newarray[$i])) {
			$newarray[$i] = array();
		}
		if (count($newarray[$i]) < $size) { // 先判断的问题
			if ($preserve_keys == false) {
				$newarray[$i][] = $value;
			} else {
				$newarray[$i][$key] = $value;
			}
		} else {
			$i++;
			if ($preserve_keys == false) {
				$newarray[$i][] = $value;
			} else {
				$newarray[$i][$key] = $value;
			}
		}
	}
	return $newarray;
}


