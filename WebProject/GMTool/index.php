<?php
/* set_include_path here to avoid php.ini miss. wangxianwei@20141129*/
$xclibpath = '/data/htdocs/lib/xingcloud';
$xincloud_app_config = '/data/htdocs/ifadmin/admin/etc/xincloud_app_config.php';
putenv('xpath=' . $xincloud_app_config);// 设置行云应用的配置文件目录

$include_path = get_include_path();
if (strpos($include_path, $xclibpath) === false) {
	set_include_path($include_path . PATH_SEPARATOR . $xclibpath);
}
if(!preg_match('/(\/rest|\/status|\/amf|\/discovery|\/admin|\/rpc|\/file|\/route\.php)/i', $_SERVER["REQUEST_URI"])){
	die('Forbidden');
}
require dirname(__FILE__)."/gameengine/index.php";
?>