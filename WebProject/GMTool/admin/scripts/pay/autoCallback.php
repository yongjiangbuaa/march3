<?php
	define('IN_ADMIN',true);
	define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
	include ADMIN_ROOT.'/config.inc.php';
	include ADMIN_ROOT.'/servers.php';
	include ADMIN_ROOT.'/admins.php';
	ini_set('mbstring.internal_encoding','UTF-8');
	includeModel("BasePage");
	global $servers;
	set_time_limit(0);
	
	include dirname(__FILE__).'/../../include/pay/payment.php';
	
	try {
		$payment = payment::singleton();
		$payment->autoRedoCallback();
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
?>