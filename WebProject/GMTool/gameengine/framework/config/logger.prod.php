<?php
return array(
	'appender' => array(
		'prod' => array(
			'type' => 'console',
			'threshold' => 'ERROR',
			'target' => 'STDERR',
 		),
 		'xingcloud' => array(
			'type' => 'dailyFile',
			'threshold' => 'INFO',
 		),
	),
	'default' => false,
	'services' => array(
		'enabled' => array(
			'UserService.doLogin' => 'UserService.doLogin',
		),
		'disabled' => array(
			'ActionService.doExecute' => 'ActionService.doExecute',
//			'UserService.doLogin' => 'UserService.doLogin',
		),
	),
	'actions' => array(
		'enabled' => array(
//			'BuyAction' => 'BuyAction',
		),
		'disabled' => array(
//			'BuyAction' => 'BuyAction',			
		),
	),
	'changes' => array(
		'enabled' => array(
//			'BuyAuditChange' => 'BuyAuditChange',
		),
		'disabled' => array(
//			'BuyAuditChange' => 'BuyAuditChange',			
		),
	),
);
?>