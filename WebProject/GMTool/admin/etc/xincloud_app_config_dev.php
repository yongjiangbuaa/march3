<?php
return array(
	'mysql' => array(
		'host' => '10.1.16.211',
		'port' => '3306',
		'user' => 'cok',
		'password' => '1234567',
		'database' => 'cokdb1',
		'instance_id' => 'zoo',
	),
	'logServer' => array(
		'host' => '10.1.16.211',
	),
	'memcache' => array(
		'type' => 'memcache',
		'servers' => array(
			array(
				'host' => '10.1.16.211',
				'port' => 11211,	
			),
		),
	),
);
?>
