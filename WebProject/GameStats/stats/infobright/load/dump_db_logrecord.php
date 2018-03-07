<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();

$dump_configs = array(
	'logrecord' => array(
		'src' => 'logrecord',
		'export_fields' => array("date_format(from_unixtime(timeStamp/1000),'%Y%m%d')",'uid','user','timeStamp','category','ifnull(type,0)','ifnull(param1,0)','ifnull(param2,0)','ifnull(param3,0)','ifnull(param4,0)',"ifnull(data1,'')","ifnull(data2,'')","ifnull(data3,'')","ifnull(data4,'')"),
		'import_fields' => array('date','uid','user','timeStamp','category','type','param1','param2','param3','param4','data1','data2','data3','data4'),
		'increment' => 'timeStamp',
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
	
	
	$dump_file = "/home/data/log/".SERVER_MARK."/database/{$table}_".SERVER_ID.".dat";
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
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE %s=%d and %s>=%d and %s<%d %s", array(
					implode(",", $export_fields),
					COK_DB_NAME.'.'.$source_table,
					'category',
					14,
					$dump_config['increment'],
					$start_date_ts,
					$dump_config['increment'],
					$end_date_ts,
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





