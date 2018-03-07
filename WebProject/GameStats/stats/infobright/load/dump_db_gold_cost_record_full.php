<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();
$dump_configs = array(
		'gold_cost_record_full' => array(
				'src' => 'gold_cost_record_full',
				'export_fields' => array("date_format(from_unixtime(g.time/1000),'%Y%m%d')",'g.userId',"ifnull(g.goldType,0)",'g.type',"ifnull(g.param1,0)","ifnull(g.param2,0)",
											'g.originalGold','g.cost','g.remainGold','g.time','u.payTotal',"ifnull(u.gmFlag,0)",'u.level','u.gold',"ifnull(u.paidGold,0)",
											'u.country',"ifnull(u.allianceId,'')","ifnull(u.pf,'')","ifnull(u.lang,'')",'u.regTime',"ifnull(u.deviceId,'')","ifnull(u.serverId,0)","ifnull(u.gmail,'')"),
				'import_fields' => array('date','userId','goldType','type','param1','param2','originalGold','cost','remainGold','time','payTotal','gmFlag','level','gold',
										'paidGold','country','allianceId','pf','lang','regTime','deviceId','serverId','gmail'),
				'increment' => 'time',
				'fromTable' => COK_DB_NAME.'.gold_cost_record g INNER JOIN '.COK_DB_NAME.'.userprofile u ON g.userId=u.uid',
		),
);
$colArray=array('sid','date','userId','goldType','type','param1','param2','originalGold','cost','remainGold','time','payTotal','gmFlag','level','gold',
										'paidGold','country','allianceId','pf','lang','regTime','deviceId','serverId','gmail');
/* $expArray=array(SERVER_ID,"date_format(from_unixtime(g.time/1000),'%Y%m%d')",'g.userId',"ifnull(g.goldType,0)",'g.type',"ifnull(g.param1,0)","ifnull(g.param2,0)",
											'g.originalGold','g.cost','g.remainGold','g.time','u.payTotal',"ifnull(u.gmFlag,0)",'u.level','u.gold',"ifnull(u.paidGold,0)",
											'u.country',"ifnull(u.allianceId,'')","ifnull(u.pf,'')","ifnull(u.lang,'')",'u.regTime',"ifnull(u.deviceId,'')","ifnull(u.serverId,0)","ifnull(u.gmail,'')"); */
$ym = date('Ym');
$allTable="gold_cost_record_full_$ym";
foreach ($dump_configs as $table => $dump_config) {
	$source_table = $dump_config['src'];
	$export_fields = $dump_config['export_fields'];
	$fromTable=$dump_config['fromTable'];

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
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE %s>=%d and %s<%d %s", array(
					implode(",", $export_fields),
					$fromTable,
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

	$cmd = "sed -i '".'s/"//g'. "' $dump_file";
	$re = system($cmd, $retval);
	
	writeRunLog("start to load data for table:$table");
	$cmd = build_mysql_cmd(
			'stats_db',
			vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' ENCLOSED BY '' LINES TERMINATED BY '\\n' (%s)", array(
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
//		unlink($dump_file);
	}
	update_process_offset($table, $end_date_ts, IB_DB_NAME_SNAPSHOT);
	
	/*$cmd= "sed -i 's/^/".SERVER_ID."<>/' ".$dump_file;
	$re = system($cmd, $retval);
	
	writeRunLog("start to load data for table:snapshot_allserver.$allTable");
	$cmd = build_mysql_cmd(
			'stats_db',
			vsprintf("LOAD DATA INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' ENCLOSED BY '' LINES TERMINATED BY '\\n' (%s)", array(
					$dump_file,
					"snapshot_allserver.$allTable",
					implode(",", $colArray)
			))
	);
	$re = system($cmd, $retval);
	
	writeRunLog('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog("run script $cmd fail");
	}else{
		unlink($dump_file);
	}*/
	writeRunLog("finished load data for table:$table");

	
	
	/* $last_processed = get_process_offset($allTable, "snapshot_allserver");
	if($last_processed > $start_date_ts){
		continue;
	}
	$dump_file2 = "/home/data/log/".SERVER_MARK."/database/snapshot_allserver_".$allTable.".dat";
	if (!file_exists(dirname($dump_file2))) {
		mkdir(dirname($dump_file2), 0755, true);
	}
	if (file_exists($dump_file2)) {
		unlink($dump_file2);
	}
	touch($dump_file2);
	$dump_file2 = realpath($dump_file2);
	$dump_file2 = str_replace('\\', '/', $dump_file2);
	
	$cmd = build_mysql_cmd(
			'slave_db',
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE %s>=%d and %s<%d %s", array(
					implode(",", $expArray),
					$fromTable,
					$dump_config['increment'],
					$start_date_ts,
					$dump_config['increment'],
					$end_date_ts,
					empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
			)),
			$dump_file2
	);
	$re = system($cmd, $retval);
	
	$cmd = "sed -i '".'s/"//g'. "' $dump_file2";
	$re = system($cmd, $retval);
	
	$cmd = build_mysql_cmd(
			'stats_db',
			vsprintf("LOAD DATA INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' ENCLOSED BY '' LINES TERMINATED BY '\\n' (%s)", array(
					$dump_file2,
					"snapshot_allserver.".$allTable,
					implode(",", $colArray)
			))
	);
	$re = system($cmd, $retval);
	
	writeRunLog('execute command:'.$cmd.', result:'.$retval);
	if($re === false || $retval == 1){
		writeRunLog("run script $cmd fail");
	}else{
		unlink($dump_file2);
	}
	writeRunLog("finished load data for table:snapshot_allserver.".$allTable);
	
	update_process_offset($allTable, $end_date_ts, "snapshot_allserver"); */
	
	
}

writeRunLog(basename(__FILE__).' end dump database for date  takes '.(time() - $start).' seconds');