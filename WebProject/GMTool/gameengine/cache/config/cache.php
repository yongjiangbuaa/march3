<?php
/**
 * 缓存信息配置文件
 * @package cache
 */
return array(
//	'apc' => array(
//		'type' => 'apc',
//	),
	'memcache' => array(
		'type' => 'memcache',
		'servers' => array(
			array(
				'host' => 'IPIPIP',
				'port' => 11211,	
			),
		),
	),
);
?>