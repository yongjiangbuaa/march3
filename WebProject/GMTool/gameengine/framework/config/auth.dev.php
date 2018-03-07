<?php
//安全认证配置文件
return array(
	'oauth' => array(
		//oauth认证过期时间，单位为秒
		'expires' => 86400,
		//default=false表示默认不开启安全认证，但是会对services中enabled数组中定义的service进行安全认证
		//default=true表示默认开启安全认证，但是不会对services中disabled数组中定义的service进行安全认证
		'default' => false,
		'services' => array(
			'enabled' => array(
//				'UserService.doLogin' => 'UserService.doLogin',
			),
			'disabled' => array(
//				'UserService.doLogin' => 'UserService.doLogin',			
			),
		),
	),
	'admin' => array(
		//oauth认证过期时间，单位为秒
		'expires' => 86400,
		//default=false表示关闭GM模块安全认证，true表示打开
		'default' => false,
	),
);
?>