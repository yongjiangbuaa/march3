<?php
/**
 * 获得Mysql配置，instanceId，memcache配置的函数
 * require_once "XingcloudSDK.php";
 * 
 * mysql host: xingcloud_get('mysql_host');
 * mysql port: xingcloud_get('mysql_port');
 * mysql username: xingcloud_get('mysql_user');
 * mysql password: xingcloud_get('mysql_pass');
 * instance id: xingcloud_get('instance_id');
 * mysql dbname: xingcloud_get('mysql_db');
 * memcache host: xingcloud_get('memcache_host');
 * memcache port: xingcloud_get('memcache_port');
 */
function xingcloud_get($message) {
	$appConfig = XRuntime::singleton()->getAppConfig();
	$config = $appConfig->getConfig();
	$appInfo = $appConfig->get('app');
	if(empty($appInfo)
		|| !isset($appInfo['storage_services'])){
		return isset ($config[$message]) ? $config[$message] : null;
	}
	if(preg_match('/^sqlite_/i', $message)){
		if(!isset($appInfo['storage_services']['kv'])
			|| $appInfo['storage_services']['kv']!=1){
				throw new Exception("kv service is turned off in app.yaml", 500);
		}
	}elseif(preg_match('/^mysql_/i', $message)){
		if(!isset($appInfo['storage_services']['mysql'])
			|| $appInfo['storage_services']['mysql']!=1){
				throw new Exception("mysql service is turned off in app.yaml", 500);
		}
	}elseif(preg_match('/^memcache_/i', $message)){
		if(!isset($appInfo['storage_services']['memcache'])
			|| $appInfo['storage_services']['memcache']!=1){
				throw new Exception("memcache service is turned off in app.yaml", 500);
		}
	}
	return isset ($config[$message]) ? $config[$message] : null;
}
?>