<?php
global $start_date_ts, $end_date_ts, $start_date;

$start = time();

$dump_configs = array(
	'stat_login_full' => array(
		'src' => 'stat_login',
		'export_fields' => array("date_format(from_unixtime(time/1000),'%Y%m%d')","date_format(from_unixtime(regTime/1000),'%Y%m%d')",'uid','time','ifnull(disconnect,0)',"ifnull(ip,'')",'ifnull(level,0)','ifnull(castlelevel,0)','payTotal',"ifnull(deviceId,'')",'regTime',"ifnull(pf,'')","ifnull(country,'')"),
		'import_fields' => array('date','regDate','uid','time','disconnect','ip','level','castlelevel','payTotal','deviceId','regTime','pf','country'),
		'increment' => 'time',
	),
);

$yearMonth=array();
/*if(date('Ym',$start_date_ts/1000)==date('Ym',$end_date_ts/1000)){
	$yearMonth[]=date('Y',$start_date_ts/1000).'_'.(date('m',$start_date_ts/1000)-1);
}else {
	$yearMonth[]=date('Y',$start_date_ts/1000).'_'.(date('m',$start_date_ts/1000)-1);
	$yearMonth[]=date('Y',$end_date_ts/1000).'_'.(date('m',$end_date_ts/1000)-1);
}*/

$yearMonth[]='2015_6';

/* $yearMonth[]='2015_3';
$start_date_ts=1; */

if (SERVER_ID!=550){
	foreach ($yearMonth as $ym){
		foreach ($dump_configs as $table => $dump_config) {
			$source_table = $dump_config['src'].'_'.$ym;
			$export_fields = $dump_config['export_fields'];
			
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
					vsprintf("SELECT CONCAT_WS('<>',%s) FROM %s", array(
							implode(",", $export_fields),
							COK_DB_NAME.'.'.$source_table,
					)),
					$dump_file
			);
			$re = system($cmd, $retval);
			writeRunLog('execute command:'.$cmd.', result:'.$retval);
			if($re === false || $retval == 1){
				writeRunLog("run script $cmd fail");
				continue;
			}
			
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
			
		}
	}
}





