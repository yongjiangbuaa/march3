<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

ini_set('memory_limit', '3072M');
function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}


$stat_cron_start = time();

//writeRunLog("stat_log_rbi_dailyGoodsCost");
include IB_ROOT.'/stat/paylevel_stat_equip.php';
//include IB_ROOT.'/stat/stat_log_rbi_dailyActive.php';
//include IB_ROOT.'/stat/stat_log_rbi_dailyGoodsCost.php';

//include IB_ROOT.'/stat/pay_analyze_pf_country_referrer.php';
//include IB_ROOT.'/stat/pay_analyze_pf_country_week.php';
//
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer.php';
//
//include IB_ROOT.'/stat/stat_dau_daily_pf_country_referrer.php';

//include IB_ROOT.'/stat/stat_dau_daily_pf_country_v3.php';//没有

//include IB_ROOT.'/stat/stat_hot_goods_cost_record2.php';//没用

//include IB_ROOT.'/stat/stat_retention_ios.php';//没用

//include IB_ROOT.'/stat/stat_retention_allPhone.php';//no use

//include IB_ROOT.'/stat/stat_equipmentForgingTimes.php';//no use

//include IB_ROOT.'/stat/stat_equipmentForgingTimes_v2.php'; //meiyong

//include IB_ROOT.'/stat/stat_achievement.php'; 没用

//include IB_ROOT.'/stat/stat_noticeUsersAndTimes.php';//no use

//include IB_ROOT.'/stat/stat_usedSkillUsersAndTimes.php';//no use

//include IB_ROOT.'/stat/stat_exploreUsersAndTimes.php';//no use

//include IB_ROOT.'/stat/stat_pushInfo.php'; //no use

//include IB_ROOT.'/stat/stat_fbRoi_retention.php';// no use

//include IB_ROOT.'/stat/stat_fbRoi_pay.php';///no use

//include IB_ROOT.'/stat/stat_iosPay_allServer.php';// no use

//include IB_ROOT.'/stat/stat_dau_daily_pf_country_v2.php';// no use

//include IB_ROOT.'/stat/stat_dau_daily_pf_country_new.php';

//include IB_ROOT.'/stat/stat_basicDatas_allserver.php';//------------基本数据

//include IB_ROOT.'/stat/stat_sign_allServer.php';//no use

//include IB_ROOT.'/stat/stat_exchange_pf_country.php';//no use

//include IB_ROOT.'/stat/stat_exchange_pf_country_send.php'; //no use

//include IB_ROOT.'/stat/stat_retention_daily_pf_country.php';//no use

//writeRunLog("stat_retention_daily_pf_country_new");
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_new.php';
//writeRunLog("stat_retention_daily_pf_country_referrer_appVersion");
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer_appVersion.php';
//writeRunLog("stat_retention_daily_pf_country_version");
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_version.php';

//include IB_ROOT.'/stat/stat_retention_daily.php';//no use

//include IB_ROOT.'/stat/pay_payTotle_pf_country.php';

//include IB_ROOT.'/stat/pay_payAnalyze_7day.php';

//include IB_ROOT.'/stat/pay_analyze_pf_country.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily.php';

//include IB_ROOT.'/stat/stat_roi_pf_country_v2.php';

//include IB_ROOT.'/stat/stat_recharge_cumulative.php';

//include IB_ROOT.'/stat/stat_roi_pf_country_v3.php';//no use

//include IB_ROOT.'/stat/stat_rotaryTable_out.php';//no use

//include IB_ROOT.'/stat/stat_rotaryTable_in.php';//no sue

//include IB_ROOT.'/stat/stat_vip_record.php';//no sue

//include IB_ROOT.'/stat/stat_dau_daily_pf_country.php';

//include IB_ROOT.'/stat/exportGoldCostFull.php';// no use

//include IB_ROOT.'/stat/pay_goldStatistics_daily.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily_groupByType.php';

//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion_referrer.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily_groupByType.php';

//include IB_ROOT.'/stat/pay_goldStatistics_daily_groupByGoodsAndResource.php';

//include IB_ROOT.'/stat/stat_dressUp.php';

//===重跑24号支付数据,少了
//writeRunLog("stat_lost_payUsers");
//include IB_ROOT.'/stat/stat_lost_payUsers.php';
//
//writeRunLog("stat_recharge_cumulative");
//include IB_ROOT.'/stat/stat_recharge_cumulative.php';

//writeRunLog("pay_payAnalyze_7day");
//include IB_ROOT.'/stat/pay_payAnalyze_7day.php';
//
//writeRunLog("pay_analyze_pf_country");
//include IB_ROOT.'/stat/pay_analyze_pf_country.php';

//writeRunLog("pay_analyze_pf_country_week");
//include IB_ROOT.'/stat/pay_analyze_pf_country_week.php';
//
//writeRunLog("pay_analyze_pf_country_referrer");
//include IB_ROOT.'/stat/pay_analyze_pf_country_referrer.php';

//exit();
//=======带referrer的数据,防止以后重用
//writeRunLog("pay_analyze_pf_country_referrer");
//include IB_ROOT.'/stat/pay_analyze_pf_country_referrer.php';
//writeRunLog("pay_analyze_pf_country_referrer_new");
//include IB_ROOT.'/stat/pay_analyze_pf_country_referrer_new.php';
//
//writeRunLog("stat_retention_daily_pf_country_referrer");
//include IB_ROOT.'/stat/stat_dau_daily_pf_country_referrer.php';
//
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer.php';
//writeRunLog("stat_retention_daily_pf_country_referrer_appVersion");
//include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer_appVersion.php';
//writeRunLog("stat_tutorial_pf_country_appVersion_referrer");
//include IB_ROOT.'/stat/stat_tutorial_pf_country_appVersion_referrer.php';
