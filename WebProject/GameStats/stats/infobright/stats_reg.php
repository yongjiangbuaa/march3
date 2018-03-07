<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';


function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}


$stat_cron_start = time();


//include IB_ROOT.'/stat/stat_retention_daily_pf_country.php';

//include IB_ROOT.'/stat/stat_retention_daily_pf_country_new.php';

//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer.php';

//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer_appVersion.php';

//include IB_ROOT.'/stat/stat_vip_record.php';

//include IB_ROOT.'/stat/stat_roi_pf_country.php';

//include IB_ROOT.'/stat/stat_hot_goods_cost_record2.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily.php';
 
//include IB_ROOT.'/stat/stat_alliance_territory.php';

//include IB_ROOT.'/stat/stat_roi_pf_country_v3.php';

//include IB_ROOT.'/stat/stat_roi_pf_country_reg.php';

//include IB_ROOT.'/stat/stat_fbRoi_retention.php';

//include IB_ROOT.'/stat/stat_fbRoi_pay.php';

//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion.php';

//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion_referrer.php';

//include IB_ROOT.'/stat/pay_analyze_pf_country.php';

//include IB_ROOT.'/stat/stat_server_info.php';

//include IB_ROOT.'/stat/stat_cross_fight.php';

//include IB_ROOT.'/stat/stat_dau_daily_pf_country_referrer.php';

