<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();
$dump_configs = array(
		'goods_cost_record' => array(
				'src' => 'goods_cost_record',
				'export_fields' => array('time','userId','itemId','type','param1','param2','original','cost','remain'),
				'import_fields' => array('time','userId','itemId','type','param1','param2','original','cost','remain'),
				'increment' => 'time',
		),
);

foreach ($dump_configs as $table => $dump_config) {
	$source_table = $dump_config['src'];
	$export_fields = $dump_config['export_fields'];

	$last_processed = get_process_offset($table, IB_DB_NAME_SNAPSHOT);

	writeRunLog(basename(__FILE__).' start dump database '.$start_date);
// 	if($last_processed > $start_date_ts){
// 		continue;
// 	}


	$dump_file = "/home/data/log/".SERVER_MARK."/database/{$source_table}_".SERVER_ID.".dat";
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
			'slave_db',
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE %s>=%d and %s<%d and %s=%d %s", array(
					implode(",", $export_fields),
					COK_DB_NAME.'.'.$table,
					$dump_config['increment'],
					1439251200000,
					$dump_config['increment'],
					1439942400000,
					'itemId',
					200682,
					empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
			)),
			$dump_file
	);
	
	$re = system($cmd, $retval);
	writeRunLog('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog("run script $cmd fail");
		continue;
	}

	writeRunLog("start to load data for table:$table");
	$cmd = build_mysql_cmd(
			'stats_db',
			vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' LINES TERMINATED BY '\\n' (%s)", array(
					$dump_file,
					IB_DB_NAME_SNAPSHOT.".$table",
					implode(",", $dump_config['import_fields'])
			))
	);
	$re = system($cmd, $retval);
	writeRunLog('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog("run script $cmd fail");
	}else{
		unlink($dump_file);
	}
	writeRunLog("finished load data for table:$table");

	update_process_offset($table, $end_date_ts, IB_DB_NAME_SNAPSHOT);
}

writeRunLog(basename(__FILE__).' end dump database for date  takes '.(time() - $start).' seconds');