<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();

$dump_configs = array(
	'sign_in_feed' => array(
		'src' => 'sign_in_feed',
		'export_fields' => array("date_format(from_unixtime(time/1000),'%Y%m%d')",'uid','type','feed','ifnull(time,0)'),
		'import_fields' => array('date','uid','type','feed','time'),
		'increment' => 'time',
	),
);
if($start_date_ts<10000){
	$start_date_ts=(time()-86400)*1000;
} 
foreach ($dump_configs as $table => $dump_config) {
	$source_table = $dump_config['src'];
	$export_fields = $dump_config['export_fields'];
	
	$last_processed = get_process_offset($table, IB_DB_NAME_SNAPSHOT);
	
	writeRunLog2(basename(__FILE__).' start dump database '.$start_date);
	
	$dump_file = "/home/data/log/cokdb_global/database/{$source_table}_cokdb_global.dat";
	if (!file_exists(dirname($dump_file))) {
		mkdir(dirname($dump_file), 0755, true);
	}
	if (file_exists($dump_file)) {
		unlink($dump_file);
	}
	touch($dump_file);
	$dump_file = realpath($dump_file);
	$dump_file = str_replace('\\', '/', $dump_file);
	
	$cmd = build_mysql_cmd(
			'slave_db_global',
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE type=1 and %s>=%d and %s<%d %s", array(
					implode(",", $export_fields),
					'cokdb_global.'.$table,
					$dump_config['increment'],
					$start_date_ts,
					$dump_config['increment'],
					$end_date_ts,
					empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
			)),
			$dump_file
	);
	$re = system($cmd, $retval);
	writeRunLog2('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog2("run script $cmd fail");
		continue;
	}
	
	writeRunLog2("start to load data for table:$table");
	$cmd = build_mysql_cmd(
		'stats_db', 
		vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' LINES TERMINATED BY '\\n' (%s)", array(
			$dump_file,
			"snapshot_global.$table",
			implode(",", $dump_config['import_fields'])
		))
	);
	$re = system($cmd, $retval);
	writeRunLog2('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog2("run script $cmd fail");
	}else{
		unlink($dump_file);
	}
	writeRunLog2("finished load data for table:$table");
	
	update_process_offset($table, $end_date_ts, "snapshot_global");
}

writeRunLog2(basename(__FILE__).' end dump database for date  takes '.(time() - $start).' seconds');

function writeRunLog2($msg){
	$logdir = '/home/data/log/scripts/runlog';
	if (!file_exists($logdir)) {
		mkdir($logdir,0775,TRUE);
	}
	file_put_contents($logdir.'/snapshot_global.log', date("[Y-m-d H:i:s]")." $msg\n", FILE_APPEND);
}



