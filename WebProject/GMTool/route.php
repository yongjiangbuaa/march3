<?php
/* set_include_path here to avoid php.ini miss. wangxianwei@20141129*/
$xclibpath = '/data/htdocs/lib/xingcloud';

$include_path = get_include_path();
if (strpos($include_path, $xclibpath) === false) {
	set_include_path($include_path . PATH_SEPARATOR . $xclibpath);
}
if(php_sapi_name() == 'cli-server'){
	if(preg_match('/^(\/rest|\/status|\/amf|\/discovery|\/admin|\/rpc|\/file|\/route\.php)/i', $_SERVER["REQUEST_URI"])){
		require "gameengine/index.php";
		return true;
	}
	return false;
}
if(!preg_match('/(\/rest|\/status|\/amf|\/discovery|\/admin|\/rpc|\/file|\/route\.php)/i', $_SERVER["REQUEST_URI"])){
	die('Forbidden');
}
require "gameengine/index.php";
?>