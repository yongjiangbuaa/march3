<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';


$stat_cron_start = time();
include_once IB_ROOT.'/load/dump_db_account_new_daily.php';
