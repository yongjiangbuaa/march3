<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
    return;
}

$stat_cron_start = time();

writeRunLog("stat_retention_ios");
include IB_ROOT.'/stat/stat_retention_ios.php';

writeRunLog("stat_retention_allPhone");
include IB_ROOT.'/stat/stat_retention_allPhone.php';

writeRunLog("stat_vip_record");
include IB_ROOT.'/stat/stat_vip_record.php';

//writeRunLog("stat_cross_fight");
//include IB_ROOT.'/stat/stat_cross_fight.php';

//writeRunLog("stat_noticeUsersAndTimes");
//include IB_ROOT.'/stat/stat_noticeUsersAndTimes.php';

//writeRunLog("stat_pushInfo");
//include IB_ROOT.'/stat/stat_pushInfo.php';

remove_pid_file(MODULE);

function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}
