<?php
/**
 * DialyResetItem
 * 
 * 每日重置的数据
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class DailyResetItem extends RActiveRecord {
	protected $collectFreeTime;    //每日征收次数
	protected $collectBuyTime;    //每日征收购买的次数
	protected $everydayBuyTimes = 0; 	//特购次数
	protected $cleanTrainTimes = 0;		//每日5倍训练位秒CD次数
	protected $bodyStatus = 10;		//演练的体力值默认为10
	protected $lowexerciseTimes;		//普通演练
	protected $highexerciseTimes;		//高级演练次数
	protected $dailyBuyDiscount = array();		//每日购买特购
	protected $nuclearStatus = 0; 			//核反应堆每日领取状态
	protected $rankStatus;					//排行榜领奖状态 按位存储
	protected $yellowVipYearReward = 0;				//年费黄钻每日奖励
	protected $yellowVipGift = 0;				//黄钻抽奖奖励
//	protected $yellowVipPay = 0;				//黄钻充值次数
//	protected $yellowVipPayReward = 0;				//黄钻充值领奖状态
	protected $bmRefresh = 0; //黑市活动每日已刷新次数
	protected $bmBuy = array(); //黑市活动每日已刷新次数
	protected $golds = 0;		//开服小活动购买过的金币
	protected $orders = 0;		//开服小活动购买过的军令
	protected $worshipFlag;    //联盟祭拜刷新
	protected $zongTimes = 0;		//端午活动中记录免费翻倍次数
	protected $giftGoodsTime = 0;	//商城赠送次数
	protected $giftedGoodsTime = 0; //商城被赠送次数
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		return self::toObject(__CLASS__, $results, $retArr);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('dailyBuyDiscount');
			$res->unserializeProperty('bmBuy');
		}
		return $res;
	}
	
	/*
	 * 
	 */
	public function getItems($uid){
		
		$dailyReset = self::getWithUID($uid);
		if(!$dailyReset){
			$data_xml = ItemSpecManager::singleton('default','item.xml')->getItem('the_war_exercise_1');
			$dailyReset = new self();
			$dailyReset->uid = $uid;
			$dailyReset->collectFreeTime = 0;
			$dailyReset->collectBuyTime = 0;
			$dailyReset->everydayBuyTimes = 0;
			$dailyReset->bodyStatus = 10;
			$dailyReset->lowexerciseTimes= 0;
			$dailyReset->highexerciseTimes = 0;
			$dailyReset->nuclearStatus = 0;
			$dailyReset->dailyBuyDiscount = array();
			$dailyReset->zongTimes = 0;
			$dailyReset->giftGoodsTime = 0;
			$dailyReset->giftedGoodsTime = 0;
			$dailyReset->save();
		}
		$data[] = self::retArr($dailyReset);
		return $data;
	}
	public function retArr($dailyReset){
		
		return Array(
			'collectFreeTime'=>$dailyReset->collectFreeTime,
			'collectBuyTime'=>$dailyReset->collectBuyTime,
			'everydayBuyTimes'=>$dailyReset->everydayBuyTimes,
			'cleanTrainTimes'=>$dailyReset->cleanTrainTimes,
			'bodyStatus'=>$dailyReset->bodyStatus,
			'lowexerciseTimes'=>$dailyReset->lowexerciseTimes,
			'highexerciseTimes'=>$dailyReset->highexerciseTimes,
			'dailyBuyDiscount'=>$dailyReset->dailyBuyDiscount,
			'nuclearStatus'=>$dailyReset->nuclearStatus,
			'yellowVipYearReward'=>$dailyReset->yellowVipYearReward,
			'yellowVipGift'=>$dailyReset->yellowVipGift,
			'bmRefresh'=>$dailyReset->bmRefresh,
			'golds' => $dailyReset -> golds,
			'orders' => $dailyReset -> orders,
			'worshipFlag' => $dailyReset->worshipFlag,
			'zongTimes' =>$dailyReset->zongTimes,
			'giftGoodsTime' => $dailyReset->giftGoodsTime,
			'giftedGoodsTime' => $dailyReset->giftedGoodsTime,
		);
	}
	
	static function DailyReset($uid){
		$dailyReset = self::getWithUID($uid);
		if($dailyReset){		
			$dailyReset->collectFreeTime = 0;
			$dailyReset->collectBuyTime = 0;
			$dailyReset->everydayBuyTimes = 0;
			$dailyReset->cleanTrainTimes = 0;
			$dailyReset->bodyStatus = 10;
			$dailyReset->lowexerciseTimes = 0;
			$dailyReset->highexerciseTimes = 0;
			$dailyReset->dailyBuyDiscount = array();
			$dailyReset->nuclearStatus = 0;
			$dailyReset->rankStatus = 0;
			$dailyReset->yellowVipYearReward = 0;
			$dailyReset->yellowVipGift = 0;
			$dailyReset->refreshTimes = 0;
			$dailyReset->bmRefresh = 0;
			$dailyReset->bmBuy = array();
			$dailyReset->golds = 0;
			$dailyReset->orders = 0;
			$dailyReset->worshipFlag = 0;
			$dailyReset->zongTimes = 0;
			$dailyReset->giftGoodsTime = 0;
			$dailyReset->giftedGoodsTime = 0;
			$dailyReset->save();
		}
	}
	/**
	 * 购买征收次数
	 * 
	 */
	static function CommendCenterBuyTimes($user,&$gold){
		$data = Array();
		$dailyResetItem = self::getWithUID($user->uid);
		$data_xml = ItemSpecManager::singleton('default','item.xml')->getItem('building_getmoney');
		if($dailyResetItem->collectBuyTime>=$data_xml->k3){
			import('service.action.ConstCode');
			return ConstCode::ERROR_INVALID;
		}
		$gold = $data_xml->k4;	
// 		if($user->user_gold + $user->system_gold<$gold){
// 			import('service.action.ConstCode');	
// 			return ConstCode::ERROR_SYSGOLD_IS_NOT_ENOUGH;				
// 		}		
// 		import('service.action.CalculateUtil');
// 		CalculateUtil::reduceGold($user,$gold,'CommendCenterBuyTimes');
		import('service.action.DataClass');
		$goldType = StatData::$goldType;
		if($goldType == 2){//消耗礼券
			if($user->system_gold<$gold){
				import('service.action.ConstCode');
				return ConstCode::ERROR_SYSGOLD_IS_NOT_ENOUGH;
			}
		}else{//消耗金币
			if($user->user_gold <$gold){
				import('service.action.ConstCode');
				return ConstCode::ERROR_USERGOLD_IS_NOT_ENOUGH;
			}
		}
		import('service.action.CalculateUtil');
		CalculateUtil::reduceGoldByType($user, $goldType, $gold, 'CommendCenterBuyTimes');
		$dailyResetItem->collectBuyTime++;
		$dailyResetItem->save();
		$user->save();
		$data['collectBuyTime'] = $dailyResetItem->collectBuyTime;		
		return $data;
		
	}
	/*
	/**
	 * 指挥中心征收
	 * 
	 */
	static function CommendCenterCollect($user){
		$uid = $user->uid;
		//征收
		import('service.item.CityItem');
		$cityItem = CityItem::getWithUID($uid);
		$level = $cityItem->level;
		$buildingId = '1301000';
		$contry_config = ItemSpecManager::singleton('default','building.xml')->getItem($level + $buildingId);
		$addMoney = $contry_config->para2;
		import('service.action.WorldClass');
		$addMoney = World::addCityPicMoneyRatio($user->uid, $addMoney);
		import('service.action.WorldFightClass');
		$addMoney = WorldFight::calPROMoney($uid, $addMoney);
		
		 //占有中型遗迹奖励
		$allianceRelicRewardTimes = World::getAllianceRelicReward($user->league, 2);
		$relicRewardMoney = floor($addMoney * $allianceRelicRewardTimes / 100);
		$addMoney += $relicRewardMoney;
		import('service.action.CalculateUtil');
		$money = CalculateUtil::changeMoney($cityItem, $addMoney, 'commendCenter');
		$cityItem->save();
		return array('remainMoney' => $money, 'relicRewardMoney' => $relicRewardMoney);
		
	}
	
	public function save(){
		$this->serializeProperty('dailyBuyDiscount');
		$this->serializeProperty('bmBuy');
		parent::save();
		$this->unserializeProperty('dailyBuyDiscount');
		$this->unserializeProperty('bmBuy');
	}

}
?>