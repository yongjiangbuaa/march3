<?php
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

global $servers;
foreach ($servers as $server => $serverInfo) {
    if (substr($server, 1) < 8000) {
        $selectId[] = intval(substr($server, 1));
    }
}

$startime = time();

$startDate = date("Ymd", strtotime("-2 day", $startime));
$endDate = date("Ymd", strtotime("-1 day", $startime));

$sids = implode(',', $selectId);
$sql = "select date as regDate,pf,country,sum(reg) as reg from stat_dau_daily_pf_country_new where sid in($sids) and date between $startDate and $endDate group by regDate,pf,country having reg>0;";
$result = query_infobright_statAllServer($sql);
if (is_bool($result)) {
    echo "exec sql $sql fail\n";
    return 1;
}
foreach ($result as $row) {
    $data[$row['regDate']][$row['pf']][$row['country']] += $row['reg'];
}
$ret = array();
foreach ($data as $dKey => $temp1) {
    if (empty($dKey)) {
        continue;
    }
    $one = array();
    foreach ($temp1 as $pKey => $temp2) {
        foreach ($temp2 as $cKey => $val) {
            $one["$pKey,$cKey"] = $val;
        }
    }
    $ret[$dKey] = json_encode($one);
}

$client = new Redis ();
$client->connect('localhost');
$client->hMset("regDaily", $ret);
$client->close();
$costTime = time() - $startime;
echo "date from $startDate to $endDate, costTime: $costTime\n";

function query_infobright_statAllServer($sql)
{
    $stats_db = array('host' => STATS_DB_SERVER_IP,
        'user' => STATS_DB_SERVER_USER,
        'password' => STATS_DB_SERVER_PWD,
        'db' => 'stat_allserver',
        'port' => 5029);
    return query_from_db($stats_db, $sql);
}




