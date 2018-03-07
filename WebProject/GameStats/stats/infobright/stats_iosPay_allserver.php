<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
    return;
}

$stat_cron_start = time();

writeRunLog("stats_iosPay_allserver");
include IB_ROOT.'/stat/stat_iosPay_allServer.php';

writeRunLog("stat_rotaryTable_out");
include IB_ROOT.'/stat/stat_rotaryTable_out.php';

writeRunLog("stat_rotaryTable_in");
include IB_ROOT.'/stat/stat_rotaryTable_in.php';

writeRunLog("stat_achievement");
include IB_ROOT.'/stat/stat_achievement.php';

writeRunLog("stat_alliance_territory");
include IB_ROOT.'/stat/stat_alliance_territory.php';

remove_pid_file(MODULE);

function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
