<?php

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
date_default_timezone_set('UTC');

define('PATH_ROOT',dirname(__FILE__));

$input = $_POST['data'];
if(empty($input)){
    $params = array();
}else{
    $params = json_decode($input, true);
}


try{
    $rediskey = 'server.restart.time';
    $client = new Redis();
//    $client->connect ('10.1.16.211');
    $client->connect ('127.0.0.1');
    $_time = $client->get($rediskey);
}catch (Exception $e){
    $re = array('error' => $e->getMessage(), 'errno' => $e->getCode(), 'status' => 0);
}

$now = time();
if($now>$_time){
    $cd_time = 0;
}else{
    $cd_time = $_time - $now;
}
$resp = array('server_restart_time' => $cd_time);
echo json_encode($resp);