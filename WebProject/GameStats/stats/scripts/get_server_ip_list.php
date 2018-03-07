#!/usr/local/bin/php -q
<?php
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT.'/stats.inc.php';

if(array_key_exists('h',$_REQUEST)){
	show_usage();
}

$type = 'online';
if(!empty($_REQUEST['t'])){
	$type = $_REQUEST['t'];
}
$range = null;
/**
 * @param $range_str
 * @return array
 */
function parse_range($range_str)
{
	$range_arr = explode(',', $range_str);
	$range = array();
	foreach ($range_arr as $val) {
		if (strpos($val, '-') !== false) {
			$val_arr = explode('-', $val);
			if($val_arr[1] - $val_arr[0] > 10000 || $val_arr[0] >= $val_arr[1]){
				continue;
			}
			$range = array_merge($range, range($val_arr[0], $val_arr[1]));
		} else {
			$range[] = $val;
		}
	}
	return $range;
}

function show_usage(){
	$filename = basename(__FILE__);
	$msg = <<<MSG
$filename [-t=all] [-r=<range>] [-h] [-s]
		-t     server type. valid value is: all, test
		-r     server id range. format 1,2-4
		-s     echo server id in the output
		-h     show this message.
MSG;
	echo $msg,PHP_EOL;
	exit(0);
}

if(!empty($_REQUEST['r'])){
	$range = parse_range($_REQUEST['r']);
}

$server_list = get_sfs_server_info_list($type, $range);
// print_r($server_list);
$echo_id = array_key_exists('s', $_REQUEST);

foreach ($server_list as $server) {
	if ($server['svr_id'] > 0) {
		if($echo_id){
			echo $server['svr_id'],' ';
		}
		echo $server['ip_inner'], PHP_EOL;
	}
}
