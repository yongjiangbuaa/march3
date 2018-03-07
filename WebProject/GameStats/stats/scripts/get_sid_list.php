<?php
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT.'/stats.inc.php';

$server_list = get_sfs_server_info_list();
// print_r($server_list);

$sids = array();
foreach ($server_list as $server) {
	if ($server['svr_id'] > 0) {
		if (file_exists('/data/ib_svr2')) {
			if ($server['svr_id']>500 && $server['svr_id']<=1000 ||($server['svr_id']>=900051 && $server['svr_id']<=900100)){
				$sids[] = $server['svr_id'];
			}
		}else {
			if (($server['svr_id']>=1 && $server['svr_id']<=500) ||($server['svr_id']>=900001 && $server['svr_id']<=900050)){
				$sids[] = $server['svr_id'];
			}
		}
	}
}

echo implode(' ', $sids);
exit(0);