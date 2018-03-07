<?php
define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
define ( 'ETC_DIR', dirname ( __DIR__ ) .'/etc');
define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

if (!isset($_REQUEST['sids'])) {
	echo('invalid param: sids.');
	exit(1);
}

$tar_sids = array();
$p_sids = $_REQUEST['sids'];
$p_slave = $_REQUEST['slave'];
$t1 = explode(',', $p_sids);
foreach ($t1 as $tt) {
	$t2 = explode('-', $tt);
	if (count($t2) > 1) {
		for ($i = $t2[0]; $i <= $t2[1]; $i++) {
			$tar_sids[] = $i;
		}
	}else{
		$tar_sids[] = $t2[0];
	}
}


$db_list = get_db_list();

$result = array();
foreach ($db_list as $dbinfo) {
	if ($dbinfo['db_id']<1){
		continue;
	}
	if ($_REQUEST['sids']=='ALL' || in_array($dbinfo['db_id'], $tar_sids)) {
		if ($p_slave) {
			$result[] = "{$dbinfo['slave_ip_inner']}:{$dbinfo['port']}/{$dbinfo['dbname']}";
		}else{
			$result[] = "{$dbinfo['db_ref']}";
		}
	}
}

echo implode(' ', $result);
exit(0);
