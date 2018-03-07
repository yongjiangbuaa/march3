<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);

//status=0 未处理;status=1 已处理
$serchSql="select uid,serverStr,sqlStr,status from server_push_auxiliary where status=0;";
$result=$page->globalExecute($serchSql, 3);
$todos=array();
foreach ($result['ret']['data'] as $row){
	$line=array();
	$line['uid']=$row['uid'];
	$line['serverStr']=$row['serverStr'];
	$line['sqlStr']=$row['sqlStr'];
	$todos[]=$line;
}
foreach ($todos as $todo){
	$severArr=explode(",", $todo['serverStr']);
	foreach ($severArr as $server){
		$result=$page->executeServer('s'.$server,$todo['sqlStr'],2);
		//print_r($result);
		echo date('Y-m-d H:i:s')."|"."s$server"."|".json_encode($result)."\n";
	}
	$opTime=date('Y-m-d H:i:s');
	$uid=$todo['uid'];
	$updateSql="update server_push_auxiliary set status=1,operationTime='$opTime' where uid='$uid';";
	$page->globalExecute($updateSql, 2);
}
