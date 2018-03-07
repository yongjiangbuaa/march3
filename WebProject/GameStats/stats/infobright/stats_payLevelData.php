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

writeRunLog("paylevel_stat_dau_pf_country.php");
include IB_ROOT.'/stat/paylevel_stat_dau_pf_country.php';

remove_pid_file(MODULE);

function buildUpdateSql($kv){
    $all = array();
    foreach ($kv as $key => $value) {
        $all[] = "$key=$value";
    }
    return implode(',', $all);
}