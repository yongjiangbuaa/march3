<?php
// php dump_db.php sid=1
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

function get_last_record_time($file_name, $time_col_num, $field_delimiter='<>'){
	$line = `tail -n1 $file_name`;
	$k = explode($field_delimiter, $line);
	return $k[$time_col_num - 1];
}
ini_set('memory_limit', '5120M');

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
	return;
}

$stat_cron_start = time();

//include IB_ROOT.'/stat/stat_basicDatas_allserver.php';//废弃基本数据
include IB_ROOT.'/stat/basic_operation.php'; //运营数据日
//include IB_ROOT.'/stat/basic_basicDatas.php';//基本数据

remove_pid_file(MODULE);
