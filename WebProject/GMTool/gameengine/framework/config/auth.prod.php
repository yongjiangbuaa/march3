<?php
return array(
	'oauth' => array(
		'expires' => 86400,
		'default' => true,
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
		'expires' => 86400,
		'default' => true,
	),
);
?>