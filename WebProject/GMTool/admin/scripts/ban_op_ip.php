<?php
//added by qinbin
// 20170117 op_banIP.php 单独封禁IP


define('IN_ADMIN', true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding', 'UTF-8');
include ADMIN_ROOT . '/config.inc.php';
include ADMIN_ROOT . '/admins.php';
include_once ADMIN_ROOT . '/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');


//运营排除在外的ip
$extraIpArr = require ADMIN_ROOT . '/etc/agentIpArray.php';

echo '----ban_op_ip start-------'.date('Ymd H:i:s').PHP_EOL;

global $servers;

$client = new Redis();
$client->connect('10.173.2.11', 6379, 3);

$key = 'op_banIP';

$ipArray = array();
$tmpIp = $client->lPop($key);
while ($tmpIp) {
    $ipArray[] = $tmpIp;
    $tmpIp = $client->lPop($key);
}

foreach ($servers as $server => $serverInfo) {
    $redis = new Redis();
    $redis->connect($serverInfo['ip_inner'], 6379);

    foreach ($ipArray as $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }
        $ret = $redis->sAdd('ban_ip_set', $value);
    }
    $redis->close();
}
$redis = new Redis();
$redis->connect(GLOBAL_REDIS_SERVER_IP, 6379);
foreach ($ipArray as $value) {
    $value = trim($value);
    if (empty($value)) {
        continue;
    }
    $result = $redis->sAdd('ban_ip_set', $value);
}
$redis->close();

echo '----ban_op_ip end-------'.date('Ymd H:i:s').PHP_EOL;

