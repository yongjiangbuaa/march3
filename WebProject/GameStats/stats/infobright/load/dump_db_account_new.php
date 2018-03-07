<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();

$dump_configs = array(
	'account_new_full' => array(
		'src' => 'account_new',
		'export_fields' => array("date_format(from_unixtime(lastTime/1000),'%Y%m%d')",'gameUid','server',"ifnull(deviceId,'')","ifnull(facebookAccount,'')","ifnull(pf,'')",'lastTime'),
		'import_fields' => array('date','uid','server','deviceId','facebookAccount','pf','lastTime'),
		'increment' => 'lastTime',
	),
);

foreach ($dump_configs as $table => $dump_config) {
	$source_table = $dump_config['src'];
	$export_fields = $dump_config['export_fields'];
	
	$last_processed = get_process_offset($table, "snapshot_global");
	
	writeRunLog2(basename(__FILE__).' start dump database '.$start_date);
// 	if($last_processed > $start_date_ts){
// 		continue;
// 	}

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
	
	for($i=0;$i<=31;$i++){
		if ($i<=15){
			$cmd = build_mysql_cmd(
					'slave_db_global_cobar',
					vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s where %s=".SERVER_ID." %s", array(
							implode(",", $export_fields),
							"cokdb_useraccount_$i.".$source_table,
							"server",
							empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
					)),
					$dump_file,
					true
			);
		}else {
			$sdgc=array('main'=>array('10.142.9.46','3306','root','t9qUzJh1uICZkA','cokdb_useraccount_0'),
				'slave'=>array('10.62.108.110','3306', 'cok','7Yxc2of0pdIJg','cokdb_useraccount_0'));
			$dsn=changedbinfoformat($sdgc['slave']);
			$cmd = build_mysql_cmd(
					'slave_db_global_cobar',
					vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s where %s=".SERVER_ID." %s", array(
							implode(",", $export_fields),
							"cokdb_useraccount_$i.".$source_table,
							"server",
							empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
					)),
					$dump_file,
					true,
					$dsn
			);
		}
		$re = system($cmd, $retval);
		writeRunLog2("execute command$i:".$cmd.', result:'.$retval);
		if($re === false || $retval == 1){
			writeRunLog2("run script $cmd fail");
			continue;
		}
	}
	
	
	$cmd="/usr/local/bin/php /data/htdocs/stats/scripts/drop_table.php db_prefix=snapshot from_sid=".SERVER_ID." end_sid=".SERVER_ID." table=$table";
	$re = system($cmd, $retval);
	if($re === false || $retval == 1){
		echo "drop $table fail";
		writeRunLog("run script $cmd fail");
	}else{
		$cmd="/usr/local/bin/php /data/htdocs/stats/scripts/create_dbtbl.php db_prefix=snapshot from_sid=".SERVER_ID." end_sid=".SERVER_ID;
		$re = system($cmd, $retval);
		if($re === false || $retval == 1){
			echo "create $table fail";
			writeRunLog("run script $cmd fail");
		}else{
			writeRunLog2("start to load data for table:$table");
			$cmd = build_mysql_cmd(
					'stats_db',
					vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' LINES TERMINATED BY '\\n' (%s)", array(
							$dump_file,
							IB_DB_NAME_SNAPSHOT.".$table",
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
	}
	
	
}

writeRunLog2(basename(__FILE__).' end dump database for date  takes '.(time() - $start).' seconds');


function writeRunLog2($msg){
	$logdir = '/home/data/log/scripts/runlog';
	if (!file_exists($logdir)) {
		mkdir($logdir,0775,TRUE);
	}
	file_put_contents($logdir.'/snapshot_global_cobar.log', date("[Y-m-d H:i:s]")." $msg\n", FILE_APPEND);
}



