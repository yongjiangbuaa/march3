<?php
defined('GEO_ROOT') || define('GEO_ROOT', __DIR__);

require_once GEO_ROOT. '/vendor/autoload.php';
use GeoIp2\Database\Reader;

function geo_get_country_by_ip(&$ip=null, $default='Unknown') {
	$geo_ipdbfile = GEO_ROOT.'/GeoIP/GeoIP2-Country.mmdb';
	$geo_ok = false;
	try {
		if ($ip === null) {
			$ip = geo_detect_ip();
		}
		if (!empty($ip)) {
			$geo_reader = new Reader($geo_ipdbfile);
			$record = $geo_reader->country($ip);
			$geo_val = $record->country->isoCode;
			$geo_ok = true;
		}
	} catch (Exception $e) {
		// TODO $e->getMessage();
	}
	
	if ($geo_ok) {
		return $geo_val;
	}
	if ($default) {
		return $default;
	}else {
		return 'Unknown';
	}
}

function geo_detect_ip() {
	if (geo__valid_ip($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
		if (geo__valid_ip(trim($ip))) {
			return $ip;
		}
	}
	if (geo__valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} elseif (geo__valid_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
	} elseif (geo__valid_ip($_SERVER["HTTP_FORWARDED"])) {
		return $_SERVER["HTTP_FORWARDED"];
	} elseif (geo__valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}
function geo__valid_ip($ip) {
	if (!empty($ip) && ip2long($ip)!=-1) {
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);
		foreach ($reserved_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	} else {
		return false;
	}
}
