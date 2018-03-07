<?php

$start = time();

$dump_configs = array(
	'userprofile_full' => array(
		'src' => 'userprofile',
		'extra_condition' =>'u',
		'export_fields' => array("date_format(from_unixtime(regTime/1000),'%Y%m%d')",'u.uid','ifnull(u.level,0)','ifnull(ub.level,0)','ifnull(u.exp,0)','ifnull(u.gold,0)','ifnull(u.paidGold,0)','ifnull(u.payTotal,0)',"ifnull(u.pic,'')",'ifnull(u.picVer,0)',"ifnull(u.allianceId,'')",'ifnull(u.worldPoint,0)',"ifnull(u.deviceId,'')","ifnull(u.gaid,'')","ifnull(u.lang,'')","ifnull(u.appVersion,'')","ifnull(u.gmFlag,0)",'regTime',"ifnull(u.serverId,0)",'u.banTime','0','lastOnlineTime'),
		'import_fields' => array('date','uid','level','buildingLv','exp','gold','paidGold','payTotal','pic','picVer','allinceId','worldPoint','deviceId','gaid','lang','appVersion','gmFlag','regTime','serverId','banTime','rewardForGoldDecr','lastOnlineTime'),
		'increment' => 'regTime',
	),
);
foreach ($dump_configs as $table => $dump_config) {
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
			vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s %s LEFT JOIN ".COK_DB_NAME.'.'."user_building ub on u.uid=ub.uid where ub.itemId='400000'", array(
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
		echo "[IB_ERR] drop $table fail";
		writeRunLog("[IB_ERR] run script $cmd fail");
	}else{
		$cmd="/usr/local/bin/php /data/htdocs/stats/scripts/create_dbtbl.php db_prefix=snapshot from_sid=".SERVER_ID." end_sid=".SERVER_ID;
		$re = system($cmd, $retval);
		if($re === false || $retval == 1){
			echo "[IB_ERR] create $table fail";
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

writeRunLog(basename(__FILE__).' end dump database '.$table.' for date  takes '.(time() - $start).' seconds');





