<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
    return;
}

$stat_cron_start = time();

//writeRunLog("stat_dau_daily_pf_country_v3");
//include IB_ROOT.'/stat/stat_dau_daily_pf_country_v3.php';

writeRunLog("pay_goldStatistics_daily");
include IB_ROOT.'/stat/pay_goldStatistics_daily.php';

writeRunLog("pay_goldStatistics_daily_groupByType");
include IB_ROOT.'/stat/pay_goldStatistics_daily_groupByType.php';

writeRunLog("pay_goldStatistics_daily_groupByGoodsAndResource");
include IB_ROOT.'/stat/pay_goldStatistics_daily_groupByGoodsAndResource.php';

remove_pid_file(MODULE);

function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
