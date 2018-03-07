<?php
return array(
	'rootLogger'=>array(
		'appenders'=>array('default'),
	),
	'appenders'=>array(
		'default'=>array(
			'class'	=> 'LoggerAppenderRollingFile',
			'params' => array(
				'file' => './gamelog.log',
				'maxFileSize' => '200MB',
				'maxBackupIndex' => 1,
			),
			'layout'=>array(
				'class'=>'LoggerLayoutPattern',
				'params'=> array(
					'conversionPattern'=>'%m%n',
				)
			)
		)
	)
);