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


$server_list = get_server_list();

$result = array();
foreach ($server_list as $server) {
	if ($server['svr_id']<1){
		continue;
	}
	if ($_REQUEST['sids']=='ALL' || in_array($server['svr_id'], $tar_sids)) {
		$db_ref = $server['db_ref'];
		$darr = explode('/', $db_ref);
		$tmp = explode(':', $darr[0]);
		$db_ip = $tmp[0];
		$db_name = $darr[1];
		$result[$server['ip_inner']] = array(
				'id' => $server['svr_id'],
				'db_ip' => $db_ip,
				'db_name' => $db_name,
				'db_passwd' => GLOBAL_DB_SERVER_PWD,
		);
	}
}

echo json_encode($result);
exit(0);
