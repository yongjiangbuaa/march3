<?php
return array(
	'default' => FALSE,
	'services' => array(
		'enabled' => array(
//			'UserService.doLogin' => 'UserService.doLogin',
		),
		'disabled' => array(
			'ActionService.doExecute' => 'ActionService.doExecute',
			'ChangeService.doApply' => 'ChangeService.doApply',
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