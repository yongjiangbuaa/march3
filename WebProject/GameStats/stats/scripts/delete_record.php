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

// $db_prefix = 'stat';
$db_prefix = $_REQUEST['db_prefix'];
$from_sid = $_REQUEST['from_sid'];
$end_sid = $_REQUEST['end_sid'];

if (!in_array($db_prefix, array('stat'))) {
	die('invalid db_prefix'.PHP_EOL);;
}

$ib_db_list = array();
for ($i = $from_sid; $i <= $end_sid; $i++) {
	$ib_db_list[] = $db_prefix.'_s'.$i;
}
if ($db_prefix == 'stat') {
	$ib_db_list[] = 'stat_allserver';
}

if (empty($ib_db_list)) {
	die('no need to create dbtbl'.PHP_EOL);
}
$sql="delete from stat_dau_daily_pf_country_v2 where date =20150412;";
foreach ($ib_db_list as $ibdb) {
	$mysqli = new mysqli($stats_db['host'], $stats_db['user'], $stats_db['password'], 'information_schema', $stats_db['port']);
	$mysqli->select_db($ibdb);
 	$mysqli->query($sql);
}

