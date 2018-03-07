<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);

require_once IB_ROOT.'/ib.inc.php';
ini_set('memory_limit', '2048M');
define('MODULE',basename(__FILE__, '.php'));
//参数只要name
if(!write_pid_file(MODULE)){
    return;
}

$stat_cron_start = time();

//writeRunLog("stat_tutorial_pf_country_appVersion");
//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion.php';

//writeRunLog("stat_tutorial_pf_country_appVersion_new");
//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion_new.php';

writeRunLog("stat_tutorial_pf_country_appVersion_referrer");
include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion_referrer.php';

writeRunLog("stat_hot_goods_cost_record2");
include IB_ROOT.'/stat/stat_hot_goods_cost_record2.php';

//writeRunLog("stat_cross_fight");
//include IB_ROOT.'/stat/stat_cross_fight.php';

remove_pid_file(MODULE);


function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
