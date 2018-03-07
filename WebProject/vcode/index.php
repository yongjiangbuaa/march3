<?php
date_default_timezone_set('UTC');

$logdir = '/data/log/vcode';
if (!file_exists($logdir)) {
	@mkdir($logdir, 0777, true);
}

header("Cache-Control:   post-check=0,   pre-check=0",   false);    
header("Pragma:   no-cache");

file_put_contents($logdir.'/'.date("Y-m-d").'.log', date("Y-m-d H:i:s")."\t".$_REQUEST['uid']."\t".json_encode($_REQUEST)."\n", FILE_APPEND);

$ac = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : '';
$time = isset($_REQUEST['time']) ? $_REQUEST['time'] : '';
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
$val = isset($_REQUEST['val']) ? strtolower($_REQUEST['val']) : '';
if(empty($uid)){
	echo 'error';
	exit;
}

//验证
$key = '!NbTR$#we0Zp';
$t = md5($key.md5(md5($uid).$time));
if($t != $token){
	echo 'error';
	file_put_contents($logdir.'/'.date("Y-m-d").'.log', date("Y-m-d H:i:s")."\t".$_REQUEST['uid']."\t check out\n", FILE_APPEND);
	exit;
}

$key = "PLAY_${uid}_VCODE";
$client_redis = new Redis();
$list = array('localhost');
$server = $list[array_rand($list)];
$r=$client_redis->connect($server,6379);
if($r === false){
	file_put_contents($logdir.'/'.date("Y-m-d").'.log', date("Y-m-d H:i:s")."\t".$_REQUEST['uid']."\t redis error\n", FILE_APPEND);
	echo 0;
	exit;
}

if($ac == 'get'){
	require_once('./vcode.php');
        $img = new Vcode();
        $check = $img->show();
        $check = strtolower($check);
        $img->outputImg();
        $client_redis->set($key,$check);
        $client_redis->EXPIRE($key,300);
} else if ($ac == 'check'){
	$result = $client_redis->exists($key);
	if(!$result){
		echo 0;
		exit();
	}
	
	$code = $client_redis->get($key);
	if($code != $val){
		echo 0;
		exit();
	} else {
		$client_redis->delete($key);
		echo 1;
		exit();
	}
} else {
	echo 0;
	exit();
}
exit();
?>