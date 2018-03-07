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

echo implode(' ', $tar_sids);
exit(0);
