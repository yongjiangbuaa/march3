<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
    return;
}
$stat_cron_start = time();
ini_set('memory_limit', '2048M');

//writeRunLog("stat_dau_daily_pf_country_v2");
//include IB_ROOT.'/stat/stat_dau_daily_pf_country_v2.php';

writeRunLog("stat_dau_daily_pf_country_new");
include IB_ROOT.'/stat/stat_dau_daily_pf_country_new.php';

writeRunLog("stat_dau_daily_pf_country_new_week");
include IB_ROOT.'/stat/stat_dau_daily_pf_country_new_week.php';

writeRunLog("stat_dau_daily_pf_country_referrer");
include IB_ROOT.'/stat/stat_dau_daily_pf_country_referrer.php';

remove_pid_file(MODULE);

function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
