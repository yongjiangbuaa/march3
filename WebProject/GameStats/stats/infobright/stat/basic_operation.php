<?php
//运营数据统计日 再次统计

defined('IB') || define('IB', dirname(__DIR__));
require_once IB . '/ib.inc.php';
if (isset($_REQUEST['fixdate'])) {
    $req_date_end = $_REQUEST['fixdate'];
    $span = 9;
} else {
    $req_date_end = date('Ymd', time());
    $span = 9;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day", strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
$crossId = 900000;

$s = microtime(true);
//这个函数不需要 每个字段加单引号 '',因为上边赋值时已加
if (!function_exists("buildUpdateSql")) {
    function buildUpdateSql($kv)
    {
        $all = array();
        foreach ($kv as $key => $value) {
            $all[] = "$key=$value";
        }
        return implode(',', $all);
    }
}
$client = getInfobrightConnect('basic_operation.php');
if (!$client) {
    echo 'mysql error basic_operation.php' . PHP_EOL;
    return;
}
// ************** get countrys' DAU
$alldataArr = array();
$whereSql = " where date>=$req_date_start and date<=$req_date_end ";

$sql = "select date,pf,country,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as pdau,sum(pdau_relocation) as pdau_move from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql  group by date,pf,country ORDER by date DESC ";

$result = query_infobright_new($client, $sql);

foreach ($result as $curRow) {
    $country = strtoupper($curRow['country']);
    $pf = $curRow['pf'];
    $date = $curRow['date'];
    $alldataArr[$date][$pf][$country]['dau'] = $curRow['s_dau'];
    $alldataArr[$date][$pf][$country]['reg'] = $curRow['s_reg'];
    $alldataArr[$date][$pf][$country]['pdau'] = $curRow['pdau'];
    $alldataArr[$date][$pf][$country]['pdau_move'] = $curRow['pdau_move']; //迁服
}
$sql = "select country,pf,date,sum(payTotle) as paytotal,sum(payUsers) as payusers,sum(firstPay) as firstpayusers
from stat_allserver.pay_analyze_pf_country_referrer_new $whereSql GROUP BY country,pf,date ORDER by date DESC;";

$result = query_infobright_new($client, $sql);

foreach ($result as $row) {
    $country = strtoupper($row['country']);
    $pf = $row['pf'];
    $date = $row['date'];

    $alldataArr[$date][$pf][$country]['paytotal'] = $row['paytotal'];
    $alldataArr[$date][$pf][$country]['payusers'] = $row['payusers'];
    $alldataArr[$date][$pf][$country]['firstpayusers'] = $row['firstpayusers'];

}
$sql = "select country,pf,date,sum(newTotalPay) as newTotalPay from stat_allserver.pay_ratio_analyze_pf_country_referrer_appVersion $whereSql GROUP BY country,pf,date ORDER by date DESC;";

$result = query_infobright_new($client, $sql);

foreach ($result as $row) {
    $country = strtoupper($row['country']);
    $pf = $row['pf'];
    $date = $row['date'];

    $alldataArr[$date][$pf][$country]['newTotalPay'] = $row['newTotalPay'];
}

$dayArr = array(1, 3, 7, 15,30);
foreach ($dayArr as $day) {
    $rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
}
$fields = implode(',', $rfields);

$sql = "select country,pf,date,$fields from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion $whereSql and reg_all>0  group by country,pf,date ORDER BY date desc";
//echo $sql.PHP_EOL;
$ret = query_infobright_new($client, $sql);
foreach ($ret as $row) {
    $country = strtoupper($row['country']);
    $pf = $row['pf'];
    $date = $row['date'];

    foreach ($dayArr as $day) {
        $count = $row['r' . $day] ? $row['r' . $day] : 0;
        $alldataArr[$date][$pf][$country][$day] = $count;
    }
}

$records = array();
foreach ($alldataArr as $dateKey => $pfValue) {
    foreach ($pfValue as $pfKey => $countryValue) {
        foreach ($countryValue as $countryKey => $value) {
            $one = array();
            $one['date'] = $dateKey;
            $one['pf'] = "'{$pfKey}'";
            $one['country'] = "'{$countryKey}'";

            $one['reg'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['reg']);
            $one['dau'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['dau']);
            $one['pdau'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['pdau']);
            $one['pdau_move'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['pdau_move']);
            $one['paytotal'] = $alldataArr[$dateKey][$pfKey][$countryKey]['paytotal']?$alldataArr[$dateKey][$pfKey][$countryKey]['paytotal']:0;
            $one['payusers'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['payusers']);
            $one['firstpayusers'] = intval($alldataArr[$dateKey][$pfKey][$countryKey]['firstpayusers']);
            $one['newTotalPay'] = $alldataArr[$dateKey][$pfKey][$countryKey]['newTotalPay']?$alldataArr[$dateKey][$pfKey][$countryKey]['newTotalPay']:0;

            foreach ($dayArr as $day) {
                $tmp = 'r'.$day;
                $one[$tmp] = $alldataArr[$dateKey][$pfKey][$countryKey][$day] ?$alldataArr[$dateKey][$pfKey][$countryKey][$day] : 0;
            }
            $records[] = $one;
        }
    }
}


//print_r($records);
foreach ($records as $fieldvalue) {
    $keys = array_keys($fieldvalue);
    $temp = $fieldvalue;
    $updKv = buildUpdateSql($temp);
    $f = join(',', $keys);
    $str = join(',', $fieldvalue);

    $insertSql = "INSERT into %s ($f) VALUES " . " ($str) ";
    $ondup = 'ON DUPLICATE KEY UPDATE ' . $updKv;
    $insertSql .= " $ondup;";

    $db_tbl = "$statdb_allserver.basic_operation";
    $sql = sprintf($insertSql, $db_tbl);
//	echo $sql."\n";
    query_infobright_new($client, $sql);
}
