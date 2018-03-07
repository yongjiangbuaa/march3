<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/1/21
 * Time: 23:44
 *
 */

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
date_default_timezone_set('UTC');

$ip = get_ip();

$cmd = trim($_REQUEST['cmd']);
$type = trim($_REQUEST['type']);
if(empty($cmd)){
    echo "fail";
    exit();
}

$log_dir= '/data/log/report';
if(!file_exists($log_dir)){
    mkdir($log_dir,0777,true);
}
$msg = date('[Y-m-d H:i:s]');
$msg .= sprintf("[ip=%s][cmd=%s][type=%s]",$ip,$cmd,$type);
$log_file = $log_dir . '/' . date('Ymd') . '.log';

file_put_contents($log_file, $msg . PHP_EOL, FILE_APPEND);

function get_ip() {
    if (_valid_ip($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
        if (_valid_ip(trim($ip))) {
            return $ip;
        }
    }
    if (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED"])) {
        return $_SERVER["HTTP_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}
function _valid_ip($ip) {
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