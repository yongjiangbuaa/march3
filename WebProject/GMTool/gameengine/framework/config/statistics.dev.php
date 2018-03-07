<?php
//配置统计分析条目
return array(
	//default=false表示默认不进行统计分析，但是会对services/actions/changes中enabled数组中定义的service进行统计分析
	//default=true表示默进行统计分析，但是不会对services/actions/changes中disabled数组中定义的service进行统计分析
	'default' => false,
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
	//选择哪些Action需要记录统计分析
	'actions' => array(
		'enabled' => array(
//			'BuyAction' => 'BuyAction',
		),
		'disabled' => array(
//			'BuyAction' => 'BuyAction',			
		),	
	),
	//选择哪些Audit Change需要进行统计分析
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