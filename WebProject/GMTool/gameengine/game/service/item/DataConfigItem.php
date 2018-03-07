<?php
class DataConfigItem {
	static function getItems($uid){
		$data[0] = array();
		import('service.item.ItemSpecManager');
		$xmlDataConfig = ItemSpecManager::singleton('default', 'item.xml')->getGroup('data_config');
		$userProfile = UserProfile::getWithUID($uid);
		foreach ($xmlDataConfig as $dataConfig){
			//默认值
			$data[0][$dataConfig->id] = $dataConfig;
			
			if($dataConfig->id == '120012'){
				$data[0]['hire_price'] = $dataConfig->k2;
			}
			if($dataConfig->id == 'player_cdnum'){
				$data[0]['buildingOriginal'] = $dataConfig->k1;
			}
			if($dataConfig->id == 'player_maxnum2'){
				$data[0]['player_maxnum2'] = $dataConfig->k1;
			}
			
			//黄钻活动
			if($dataConfig->id == 'activity_yd'){
				$data[0]['activity_yd'] = array();
				$data[0]['activity_yd']['k1'] = strtotime($dataConfig->k1);
				$data[0]['activity_yd']['k2'] = strtotime($dataConfig->k2);
			}
			//国家补偿
			if($dataConfig->id == 'country'){
				$data[0]['country_compensate'] = array(
					'k1' => $dataConfig->k1,
					'k2' => $dataConfig->k2,
					'k3' => $dataConfig->k3
				);
			}
			if($dataConfig->id == 'worldmap') {
				list($worldLength, $worldWidth) = explode(',', $dataConfig->k1);
				$data[0]['worldmap'] = array(
					'x' => $worldLength,
					'y' => $worldWidth,
				);
			}
			if($dataConfig->id == 'activity_open')
			{
				import('service.item.ServerResetItem');
				$serverResetItem = ServerResetItem::getWithUID();
				$releaseTime = strtotime(date('Y-m-d',$serverResetItem->releaseTime));
				//老服活动不出现2013-05-16
				if($releaseTime < 1368633600 || $userProfile->getLang() == 'en'){
					$releaseTime = 1367337600;//2013-05-01
				}
				$data[0]['activity_open'] = array(
					'k1' => strtotime($dataConfig->k1),
					'k2' => strtotime($dataConfig->k2),
					'k3' => strtotime($dataConfig->k3),
					'start' => $releaseTime,
					'k6' => $dataConfig -> k6,
					'k7' => $dataConfig -> k7,
					'k8' => $dataConfig -> k8,
				);
			}
			if($dataConfig->id == 'activity_duanwu'){
				$data[0]['activity_duanwu'] = array(
						'k1' => strtotime($dataConfig->k1),
						'k2' => strtotime($dataConfig->k2),
						'k3' => $dataConfig->k3,
						'k4' => $dataConfig->k4,
						'k5' => $dataConfig->k5,
						'k6' => $dataConfig->k6,
				);
			}
			if($dataConfig->id == 'buy_coin1'){//将奖励物品的effect传给前台
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($dataConfig->k7);
				$data[0]['buy_coin1']->effect1 = $xmlGoods->effect1;
				$data[0]['buy_coin1']->value1 = $xmlGoods->value1;
			}
			if($dataConfig->id == 'buy_coin2'){//将奖励物品的effect传给前台
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($dataConfig->k7);
				$data[0]['buy_coin2']->effect1 = $xmlGoods->effect1;
				$data[0]['buy_coin2']->value1 = $xmlGoods->value1;
			}
			if($dataConfig->id == 'buy_coin3'){//将奖励物品的effect传给前台
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($dataConfig->k7);
				$data[0]['buy_coin3']->effect1 = $xmlGoods->effect1;
				$data[0]['buy_coin3']->value1 = $xmlGoods->value1;
			}
			if($dataConfig->id == 'buy_coin4'){//将奖励物品的effect传给前台
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($dataConfig->k7);
				$data[0]['buy_coin4']->effect1 = $xmlGoods->effect1;
				$data[0]['buy_coin4']->value1 = $xmlGoods->value1;
			}
			if($dataConfig->id == 'activity_market')
			{
				import('service.action.BlackShopClass');
				$startTime = BlackShop::getInstance(null)->getStartTime();
				$openDay = ceil((time()-strtotime(date('Y-m-d',$startTime)))/86400);
				$data[0]['activity_market'] = array(
						'k1' => $dataConfig->k1,
						'k2' => $dataConfig->k2,
				);
				$marketDayXml = ItemSpecManager::singleton('default','item.xml')->getItem('activity_markettime');
				$marketCounts = -1;
				foreach ($marketDayXml as $values) {
					$marketCounts++;
					$onePeriod = explode (',', $marketDayXml -> {'k'.$marketCounts});
					if ($onePeriod[2]){
						if ($openDay >= $onePeriod[0] && $openDay < $onePeriod[0] + $onePeriod[1]) {
							$data[0]['activity_market']['k3'] = $startTime + ($onePeriod[0] - 1) * 86400;
							$data[0]['activity_market']['k4'] = $startTime + ($onePeriod[0] + $onePeriod[1] - 1) * 86400 - 1;
							$data[0]['activity_market']['k5'] = $marketCounts;
							break;
						}
					}
				}
			}
			if($dataConfig->id == 'activity_goldegg'){
				$data[0]['activity_goldegg'] = array(
					'k1' => strtotime($dataConfig->k1),
					'k2' => strtotime($dataConfig->k2),
					'k3' => $dataConfig->k3
				);
			}
			if($dataConfig->id == 'activity_xinxiya'){
				import('service.item.CynthiaItem');
				$cynthiaItem = CynthiaItem::getWithUID($uid);
				if (2 == $userProfile -> first_pay_status && !$cynthiaItem) {
					$cynthiaItem = new CynthiaItem();
					$cynthiaItem -> uid = $uid;
					$cynthiaItem -> startTime = strtotime(date('Y-m-d',time()));
					$cynthiaItem -> save ();
				}
				$data[0]['activity_xinxiya'] = array(
					'k1' => $cynthiaItem -> startTime,
					'k2' => $cynthiaItem -> startTime + $dataConfig->k1 * 86400,
					'k3' => $cynthiaItem -> status);
			}
			if($dataConfig->id == 'activity_shoot'){
				$data[0]['activity_shoot'] = array(
					'k1' => strtotime($dataConfig->k1),
					'k2' => strtotime($dataConfig->k2),
					'k3' => $dataConfig->k3,
					'k4' => $dataConfig->k4,
					'k5' => $dataConfig->k5,			
				);
			}
			if($dataConfig->id == 'shop_gift'){
				$data[0]['shop_gift'] = array(
					'k1' => $dataConfig->k1,		
				);
			}
			if($dataConfig->id == 'activity_jinjuan'){
				$data[0]['activity_jinjuan'] = array(
						'k1' => strtotime($dataConfig->k1),
						'k2' => strtotime($dataConfig->k2),
				);
			}
			if($dataConfig->id == 'activity_mystery'){
				$data[0]['activity_mystery'] = array(
						'k1' => strtotime($dataConfig->k1),
						'k2' => strtotime($dataConfig->k2),
				);
			}
			if($dataConfig->id == 'activity_mystery'){
				$data[0]['activity_mystery'] = array(
						'k1' => strtotime($dataConfig->k1),
						'k2' => strtotime($dataConfig->k2),
				);
			}
			if($dataConfig->id == 'depreciate'){
				$data[0]['depreciate'] = array(
						'k1' => $dataConfig->k1,
						'k2' => $dataConfig->k2,
						'k3' => $dataConfig->k3,
						'k4' => strtotime($dataConfig->k4),
						'k5' => strtotime($dataConfig->k5),
						'k6' => $dataConfig->k6,
						'k7' => $dataConfig->k7,
						'k8' => $dataConfig->k8,
						'k9' => $dataConfig->k9,
				);
			}
		}
		
		if ($userProfile->getLang() == 'en')
		{
				$data[0]['buy_coin1']->k1 /= 5;
				$data[0]['buy_coin2']->k1 /= 5;
				$data[0]['buy_coin3']->k1 /= 5;
				$data[0]['buy_coin4']->k1 /= 5;
		}
// 		if($userProfile->gmFlag == 1)
// 		{
// 			$data[0]['player_warnum']->k1 = 999;
// 			$data[0]['player_warnum']->k2 = 999;
// 		}
		//背包空间
		import('service.action.InventoryClass');
		$data[0]['spaceLimit'] = Inventory::getSpaceLimit($uid);
		$data[0]['tempSpaceLimit'] = Inventory::getTempSpaceLimit();
		//建筑
		import('service.action.BuildingClass');
		$buildingLimt = BuildingClass::getConstructOrder($uid);
		$data[0]['buildingLimit'] = $buildingLimt['count'];
		$data[0]['buildingLimitEndTime'] = $buildingLimt['endTime'];
		//当前时间
		$data[0]['currentTime'] = microtime(true);
// 		$userProfile = UserProfile::getWithUID($uid);
		import('service.action.CalculateUtil');
		$data[0]['rewards'] = CalculateUtil::getInfoByGiftId($userProfile);
		$data[0]['vip'] = $vipEffect = CalculateUtil::getVipEffect($userProfile,array(),array(),true);
		$data[0]['yvip'] = $yvipEffect = CalculateUtil::getYVipEffect($userProfile);
		if($vipEffect){
			$data[0]['player_maxnum']->k4 += $vipEffect['eft11']['value'];//强化等级上限
			$data[0]['player_warnum4']->k4 += $vipEffect['eft13']['value'];//购买军令次数
// 			$data[0]['player_warnum']->k2 += $vipEffect['eft15']['value'];//竞技场次数
			$data[0]['player_warnum5']->k3 += $vipEffect['eft16']['value'];//竞技场购买上限
		}
		//合服gm双倍竞技场次数
		import('service.item.ServiceConfigItem');
		$ServiceConfigItem = ServiceConfigItem::getWithUID('config');
		if($ServiceConfigItem && $ServiceConfigItem->isDouble("arena"))
		{
			import('service.item.ItemSpecManager');
			$arenaAddNum = ItemSpecManager::singleton('default', 'item.xml')->getItem('server_combine')->k6;
			$data[0]['player_warnum']->k2 += $arenaAddNum;
		}	
		//取得玩家VIP的奖励信息
		import('service.action.VIPClass');
		$VIPService = VIPClass::getInstance($userProfile);
		$vipRewardId = $VIPService->getRewardIdByVIPRank();
		if($vipRewardId){
			$data[0]['vipRewards'] = CalculateUtil::getInfoByRewardId($vipRewardId);
		}
		//玩家的阵法中最多可以上阵的将军数量
		import("service.item.LordItem");
		$lordItem = LordItem::getWithUID($uid);
		$xmlRole = ItemSpecManager::singleton('default', 'generalRank.xml')->getItem($lordItem->generalrankId);
		$data[0]['battle_gnum'] = $xmlRole->battle_gnum;
		//招募配置信息
		$general_employ2XML = ItemSpecManager::singleton('default', 'item.xml')->getItem('general_employ2');
		$general_employ4XML = ItemSpecManager::singleton('default', 'item.xml')->getItem('general_employ4');
		$data[0]["general_employ2"] = $general_employ2XML;
		$data[0]["general_employ4"] = $general_employ4XML;
// 		import('util.mysql.XMysql');
// 		$mysql = XMysql::singleton();
// 		$res = $mysql->execResult("select sum(`price`*`num`) as total from qpay where ownerid='$uid' and  sendtime < 1351180800");
// 		$data[0]['usedGold'] = (int)$res[0]['total'];
		//跳转url
		if ($userProfile->getLang() == 'en')
		{
			$data[0]['turnUrl'] = "https://apps.facebook.com/warfare_en/";
		}
		else
		{
			$data[0]['turnUrl'] = "http://apps.pengyou.com/100650014";
		}
		//圣诞活动开关
		$data[0]['xmas'] = 1;
		//兵种强化配置信息
		import('service.action.UnitClass');
		$unit = new Unit($userProfile);
		$unitConfs = $unit->getUpgradeXml();
		$unitArray = Array();
		foreach($unitConfs as $unitConf){
			$unitArray[] = $unitConf;
		}
		$data[0]['unitStrengthConfig'] = $unitArray;
		//每日谁购买了什么
		import('util.cache.XCache');
		$cache = new XCache();
		$cachePrefix = 'IK2';
		$cache->setKeyPrefix($cachePrefix);
		$cacheKeys = array('buyDailyDiscount','noLimitDiscount','wuYiOpenGiftInfo','bmarketBuy'.date('Y-m-d'),'orangeGeneral','goldGeneral', 'saleBuy');
		$cacheObjs = array('buyDailyDiscount','noLimitDiscount','wuYiOpenGiftInfo','bmarketBuy','orangeGeneral','goldGeneral', 'saleBuy');
		$cacheData = $cache->get($cacheKeys);
		foreach ($cacheKeys as $key=>$cacheKey){
			$data[0][$cacheObjs[$key]] = $cacheData[$cachePrefix.$cacheKey] ? $cacheData[$cachePrefix.$cacheKey] : array();
		}
// 		$buyDailyDiscount = $cache->get('buyDailyDiscount');
// 		$data[0]['buyDailyDiscount'] = $buyDailyDiscount ? $buyDailyDiscount : array();
// 		$noLimitDiscount = $cache->get('noLimitDiscount');
// 		$data[0]['noLimitDiscount'] = $noLimitDiscount ? $noLimitDiscount : array();
// 		$wuYiOpenGiftInfo = $cache->get('wuYiOpenGiftInfo');
// 		$data[0]['wuYiOpenGiftInfo'] = $wuYiOpenGiftInfo ? $wuYiOpenGiftInfo : array();
// 		$bmarketBuy = $cache->get('bmarketBuy'.date('Y-m-d'));
// 		$data[0]['bmarketBuy'] = $bmarketBuy ? $bmarketBuy : array();
// 		$famousGeneral = $cache -> get ('orangeGeneral');
// 		$data[0]['orangeGeneral'] = $famousGeneral ? $famousGeneral : array ();
// 		$famousGeneral = $cache -> get ('goldGeneral');
// 		$data[0]['goldGeneral'] = $famousGeneral ? $famousGeneral : array ();
		$data[0]['timeFix'] = strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'));
		return $data;
	}
}
?>