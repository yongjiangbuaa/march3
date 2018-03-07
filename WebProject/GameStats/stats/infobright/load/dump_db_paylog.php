<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();

$server_list = get_db_list();

// print_r($server_list);
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}

$dump_configs = array(
		'paylog' => array(
				'src' => 'paylog',
				'export_fields' => array("date_format(from_unixtime(time/1000),'%Y%m%d')",'uid','orderId','pf','productId',"''",'time',"ifnull(currency,0)",'spend',"ifnull(paid,0.00)","ifnull(status,0)","ifnull(payLevel,0)","ifnull(buildingLv,0)"),
				'import_fields' => array('date','uid','orderId','pf','productId','orderInfo','time','currency','spend','paid','status','payLevel','buildingLv'),
				'increment' => 'time',
		),
);

foreach ($dump_configs as $table => $dump_config) {
	$useNew = true;
	if($useNew){
		$source_table = $dump_config['src'];
		$export_fields = $dump_config['export_fields'];

		writeRunLog(basename(__FILE__).' start dump database '.$table.' '.$start);

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
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s %s", array(
				implode(",", $export_fields),
				COK_DB_NAME.'.'.$source_table,
				empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
			)),
			$dump_file
		);
		$re = system($cmd, $retval);
		writeRunLog('execute command:'.$cmd.', result:'.$retval);
		if($re === false || $retval == 1){
			writeRunLog("[IB_ERR] run script $cmd fail");
			continue;
		}

		$cmd = "sed -i '".'s/"//g'. "' $dump_file";
		$re = system($cmd, $retval);

		$cmd="/usr/local/bin/php /data/htdocs/stats/scripts/drop_table.php db_prefix=snapshot from_sid=".SERVER_ID." end_sid=".SERVER_ID." table=$table";
		$re = system($cmd, $retval);
		if($re === false || $retval == 1){
			echo "drop $table fail";
			writeRunLog("[IB_ERR] run script $cmd fail");
		}else{
			$cmd="/usr/local/bin/php /data/htdocs/stats/scripts/create_dbtbl.php db_prefix=snapshot from_sid=".SERVER_ID." end_sid=".SERVER_ID;
			$re = system($cmd, $retval);
			if($re === false || $retval == 1){
				echo "create $table fail";
				writeRunLog("[IB_ERR] run script $cmd fail");
			}else{
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
					writeRunLog("[IB_ERR] run script $cmd fail");
				}else{
					unlink($dump_file);
				}
				writeRunLog("finished load data for table:$table");

			}
		}
	}
	else
	{
		$source_table = $dump_config['src'];
		$export_fields = $dump_config['export_fields'];

		$last_processed = get_process_offset($table, IB_DB_NAME_SNAPSHOT);

		writeRunLog(basename(__FILE__) . ' start dump database ' . $start_date);
// 	if($last_processed > $start_date_ts){
// 		continue;
// 	}

		$dump_file = "/home/data/log/" . SERVER_MARK . "/database/{$source_table}_" . SERVER_ID . ".dat";
		if (!file_exists(dirname($dump_file))) {
			mkdir(dirname($dump_file), 0755, true);
		}
		if (file_exists($dump_file)) {
			unlink($dump_file);
		}
		touch($dump_file);
		$dump_file = realpath($dump_file);
		$dump_file = str_replace('\\', '/', $dump_file);
		$slave_db_info = $slave_db_list[SERVER_ID];
//	print_r($slave_db_info);
		$link = mysqli_connect($slave_db_info['slave_ip_inner'], GAME_DB_SERVER_USER, GAME_DB_SERVER_PWD, $slave_db_info['dbname'], $slave_db_info['port']);
		$sql = "select count(*) countNum from paylog;";
		$res = mysqli_query($link, $sql);
		$row = mysqli_fetch_assoc($res);
		$slaveNum = $row['countNum'];

		$sql = "select count(*) countNum from snapshot_s" . SERVER_ID . ".paylog;";
		$ret = query_infobright($sql);
		$infobrightNum = $ret[0]['countNum'];


		$cmd = build_mysql_cmd(
			'slave_db',
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s order by %s limit %d,%d %s", array(
				implode(",", $export_fields),
				COK_DB_NAME . '.' . $table,
				$dump_config['increment'],
				$infobrightNum,
				$slaveNum - $infobrightNum,
				empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
			)),
			$dump_file
		);

		/* $cmd = build_mysql_cmd(
				'slave_db',
				vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s WHERE %s>=%d and %s<=%d %s", array(
						implode(",", $export_fields),
						COK_DB_NAME.'.'.$table,
						$dump_config['increment'],
						$start_date_ts,
						$dump_config['increment'],
						$end_date_ts,
						empty($dump_config['extra_condition']) ? '' : $dump_config['extra_condition']
				)),
				$dump_file
		); */

		$re = system($cmd, $retval);
		writeRunLog('execute command:' . $cmd . ', result:' . $retval);
		if ($re === false || $retval == 1) {
			writeRunLog("[IB_ERR] run script $cmd fail");
			continue;
		}

		writeRunLog("start to load data for table:$table");
		$cmd = build_mysql_cmd(
			'stats_db',
			vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY '<>' LINES TERMINATED BY '\\n' (%s)", array(
				$dump_file,
				IB_DB_NAME_SNAPSHOT . ".$table",
				implode(",", $dump_config['import_fields'])
			))
		);
		$re = system($cmd, $retval);
		writeRunLog('execute command:' . $cmd . ', result:' . $retval);
		if ($re === false || $retval == 1) {
			writeRunLog("[IB_ERR] run script $cmd fail");
		} else {
			//copy($dump_file, '/tmp/log/'."{$source_table}_".SERVER_ID.'_'.$start_date_ts.'_'.$end_date_ts."_".$infobrightNum."_".$slaveNum.".dat");
			unlink($dump_file);
		}
		writeRunLog("finished load data for table:$table");

		update_process_offset($table, $end_date_ts, IB_DB_NAME_SNAPSHOT);
	}
}

writeRunLog(basename(__FILE__).' end dump database for date  takes '.(time() - $start).' seconds');