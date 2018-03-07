<?php
//SDK日志配置
return array(
	/**
	 * 在appender数组中添加日志记录器，格式为：日志记录器名称=>日志级别、类型等内容的设定
	 * 支持添加多个日志记录器，实现不同的日志需求
	 * 例如：
	   'consoleLogger' => array(
			'type' => 'console',
			'threshold' => 'ERROR',
			'target' => 'STDERR',
 		),
 		'fileLogger' => array(
			'type' => 'dailyFile',
			'threshold' => 'INFO',
 		),
	 * 几个参数的作用：
	 * type: console|dailyFile 日志输出类型，console表示将日志写到标准输出php://stdout或php://stderr；dailyFile表示将日志以文件形式按天写到game/logs目录下
	 * threshold: ALL|DEBUG|INFO|WARN|ERROR|FATAL|CRITICAL|OFF 日志级别
	 * target: STDOUT|STDERR 对type=console的日志有用，选择日志输出目标
	 */
	'appender' => array(
		'xingcloud' => array(
			'type' => 'dailyFile',
			'threshold' => 'DEBUG',
 		),
	),
	//default=false表示默认不记录日志，但是会对services中enabled数组中定义的service进行安全认证
	//default=true表示默认记录日志，但是不会对services中disabled数组中定义的service进行安全认证
	'default' => TRUE,
	'services' => array(
		'enabled' => array(
			'UserService.doLogin' => 'UserService.doLogin',
		),
		'disabled' => array(
			'ActionService.doExecute' => 'ActionService.doExecute',
//			'UserService.doLogin' => 'UserService.doLogin',
		),
	),
	//选择哪些Action需要记录日志
	'actions' => array(
		'enabled' => array(
			'BuyAction' => 'BuyAction',
		),
		'disabled' => array(
//			'BuyAction' => 'BuyAction',			
		),
	),
	//选择哪些Audit Change需要记录日志
	'changes' => array(
		'enabled' => array(
			'BuyAuditChange' => 'BuyAuditChange',
		),
		'disabled' => array(
//			'BuyAuditChange' => 'BuyAuditChange',			
		),
	),
);
?>