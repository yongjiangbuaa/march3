<?php
// databases : 
// stats_s*|template|global
// detail_s*|template
// snapshot_s*|template

/*

mysql -uroot -pt9qUzJh1uICZkA -P5029 -h10.81.92.112

*/

defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
require_once STATS_ROOT.'/stats.inc.php';

$tmpfile = STATS_ROOT.'/tmp.dat';

// $db_prefix = 'snapshot';
// $db_prefix = 'stat';
$db_prefix = $_REQUEST['db_prefix'];
$from_sid = $_REQUEST['from_sid'];
$end_sid = $_REQUEST['end_sid'];

if (!in_array($db_prefix, array('snapshot', 'stat', 'coklog'))) {
	die('invalid db_prefix'.PHP_EOL);;
}

$ib_db_list = array();
// $ib_db_list[] = 'stat_global';
// $ib_db_list[] = 'stat_allserver';
// $server_list = get_server_list();
// foreach ($server_list as $server_info) {
// 	if ($server_info['svr_id'] < $from_sid) {
// 		continue;
// 	}
// 	$ib_db_list[] = $db_prefix.'_s'.$server_info['svr_id'];
// }
for ($i = $from_sid; $i <= $end_sid; $i++) {
	$ib_db_list[] = $db_prefix.'_s'.$i;
}
if (empty($ib_db_list)) {
	die('no need to create dbtbl'.PHP_EOL);
}
if ($db_prefix == 'stat') {
	$ib_db_list[] = 'stat_allserver';
}

$query_create_tables = file_get_contents(STATS_ROOT.'/sql/'.$db_prefix.'_tables.sql');
//echo $query_create_tables,"\n";
foreach ($ib_db_list as $ibdb) {
	$mysqli = new mysqli($stats_db['host'], $stats_db['user'], $stats_db['password'], 'information_schema', $stats_db['port']);
	$sql = "create database IF NOT EXISTS $ibdb;";
	$mysqli->query($sql);
	$mysqli->select_db($ibdb);
	$mysqli->multi_query($query_create_tables);
}
