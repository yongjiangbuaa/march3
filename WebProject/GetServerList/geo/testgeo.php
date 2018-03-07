<?php

require_once 'geo.inc.php';

$ips = array(
		'86.107.144.170',
		'88.243.153.148',
		'176.124.169.114',
);

// $times = time();
// foreach ($ips as $ip) {
// // 	$val = geo_get_country_by_ip($ip);
// 	$val = geo_get_city_by_ip($ip);
// 	echo "$ip ".$val.PHP_EOL;
// // 	break;
// }
// echo time() - $times;


// print_r(DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, "US"));
// date_default_timezone_set('America/Los_Angeles');

date_default_timezone_set('UTC');

$user_ip = '121.106.179.199';
$user_timezone = geo_get_timezone_by_ip($user_ip);
echo $user_timezone;


$city = geo_get_city_by_ip($user_ip);
echo $city;


// print($record->traits->ipAddress . "\n");
// print($record->country->isoCode . "\n"); // 'US'
// print($record->country->name . "\n"); // 'United States'
// print($record->country->names['zh-CN'] . "\n"); // '美国'
