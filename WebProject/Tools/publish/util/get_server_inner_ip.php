<?php
define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

if (!file_exists(PUBLISH_DIR.'/logs')) {
	mkdir(PUBLISH_DIR.'/logs', 0777, true);
}
$cached_file = PUBLISH_DIR.'/logs/server_list.cache.txt';

if (isset($_REQUEST['svr_id'])) {
	$svr_id = $_REQUEST['svr_id'];
}else{
	$svr_id = 1;
}

if (file_exists($cached_file)) {
	$cached = file_get_contents($cached_file);
	$lines = explode("\n", $cached);
	foreach ($lines as $line) {
		$ip_id = explode(' ', $line);
		if ($ip_id[0] == $svr_id) {
			echo $ip_id[1];
			exit(0);
		}
	}
}

$info = get_server_info($svr_id);
file_put_contents($cached_file, "$svr_id {$info['ip_inner']}\n", FILE_APPEND);
echo $info['ip_inner'];
exit(0);