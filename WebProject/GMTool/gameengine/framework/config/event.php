<?php
return array(
	'listeners' => array(
		'XLoggerListener' => 'module.util.logger.XLoggerListener',
		'XStatisticsListener' => 'module.util.statistics.XStatisticsListener',
		'XContextCloseListener' => 'module.event.context.XContextCloseListener',
	),
);
?>