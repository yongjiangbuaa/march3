<?php
/**
 * CityItem
 * 
 * 城市属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class CityItem extends RActiveRecord {
//	protected $id;
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $itemId;//xml表里的id
	protected $level = 1;//城市等级
	protected $boom;//当前繁荣
	protected $boomLoss;//繁荣损失
	protected $money;//金钱
	protected $mineral;//矿物
	protected $oil;//石油
	protected $food;//粮食
	protected $soldiers;//新兵数
	protected $forces = 0;//已招募的兵数
	protected $lastUpdateCity = 0;//上一次更新city属性
	protected $generalPlace;	//已开启替补将军位置，查数据用
	protected $groundIndex;
	protected $forceMarketList; //佣兵市场玩家的随机选项
	protected $forceMarketFlushTime; //刷新时间
	
	public function getItems($uid, $loadMethod = false)
	{
		$cityItem = self::getWithUID($uid);
		if($cityItem == null)
		{
			self::initCityItem($uid);
			$cityItem = self::getWithUID($uid);
		}
		
		import('service.action.BuildingClass');
		$buildingClass = new BuildingClass($uid);
		$cityData = array();
		$baseLimitOwn = ItemSpecManager::singleton()->getItem(120013);
		$baseLimitRecruit = ItemSpecManager::singleton()->getItem(120010);
		$buildingXml = ItemSpecManager::singleton('default','building.xml');
		//获得仓库
		$storage = $buildingClass->getBuildings(1,1311000);
		$cityData['storage'] = $baseLimitOwn->k1;//没有仓库时仓库初始值
		if($storage)
		{
			$storageLevel = $storage[0]['level'];
			$storageXML = $buildingXml->getitem($storageLevel+1311000);
			$cityData['storage'] += $storageXML->para1;//仓库上限para1(storage)(building)
		}
		//获得兵营
		$cityData['soldiersLimit'] = $baseLimitRecruit->k3;//可招兵上限
		$cityData['forcesLimit'] = $baseLimitOwn->k3;//城市士兵初始上限
		$cityData['add_soldier'] = 0;
		$barrack = $buildingClass->getBuildings(1,1313000);
		$cityData['moneyLimit'] = $buildingXml->getitem($cityItem->level+1301000)->para3;//金钱上限
		
		if($barrack && $barrack[0]['level'] > 0)
		{
			$cityData['soldiersLimit'] += $buildingXml->getitem($barrack[0]['level']+1313000)->para1;
			$cityData['forcesLimit'] += $buildingXml->getitem($barrack[0]['level']+1313000)->para3;
			$addSoldiersPM = $buildingXml->getitem($barrack[0]['level']+1313000)->para2;
			import('service.action.ConstCode');
			$soldiers = self::addCompensate($uid, ConstCode::EFFECT_SOLDIER, $addSoldiersPM);
			import('service.item.ServiceConfigItem');
			$ServiceConfigItem = ServiceConfigItem::getWithUID('config');
			if($ServiceConfigItem && $ServiceConfigItem->isDouble('soldiersDouble')){
				$soldiers *= 2;
			}
			$cityData['add_soldier'] += $soldiers;
		}
		

		
		//获得所有军事中心
		/*$militaryCenter = $buildingClass->getBuildings(1,1314000);
		$militaryCenterLevel = 0;
		for ($i = 0;$i<count($militaryCenter);$i++)
		{
			if($militaryCenter[$i]['level'] > 0)
			{
				$cityData['forcesLimit'] += $buildingXml->getitem($militaryCenter[$i]['level']+1314000)->para1;
				$cityData['add_soldier'] += $buildingXml->getitem($militaryCenter[$i]['level']+1314000)->para2;
			}
		}*/
		//新兵上限para1(barracks)(building)＋para1(MilitaryCenter)(building)* MilitaryCenter数量
		
		$cityData['boomUpgrade'] = ItemSpecManager::singleton('default','role.xml')->getItem($cityItem->level + 2000)->city_exp;//升级所需繁荣
		if($cityItem->level > 1)
			$cityData['boomLastLevel'] = ItemSpecManager::singleton('default','role.xml')->getItem($cityItem->level - 1 + 2000)->city_exp;//达到此等级所需的繁荣度
		else 
			$cityData['boomLastLevel'] = 0;
			
		if($loadMethod){
// 			self::resourceMaxDeal($cityItem, $cityData);
		}
			
		$cityData['uid'] = $cityItem->uid;
		$cityData['itemId'] = $cityItem->itemId;
		$cityData['level'] = $cityItem->level;
		$cityData['boom'] = $cityItem->boom;
		$cityData['boomLoss'] = $cityItem->boomLoss;
		$cityData['money'] = $cityItem->money;
		$cityData['mineral'] = $cityItem->mineral;
		$cityData['oil'] = $cityItem->oil;
		$cityData['food'] = $cityItem->food;
		$cityData['soldiers'] = $cityItem->soldiers;
		$cityData['forces'] = $cityItem->forces;
		$cityData['lastUpdateCity'] = $cityItem->lastUpdateCity;
		$cityData['levelLimit'] = ItemSpecManager::singleton()->getItem('building_maxlv')->k3;
		//需要开的地块
		$cityData['groundIndex'] = $cityItem->groundIndex;
		
		$data[] = $cityData;
		return $data;
	}
	
	static function addCompensate($uid, $effectID, $value, &$detail = FALSE) {

		$user = UserProfile::getWithUID ($uid);
		import('service.action.ScienceClass');
		import('service.action.CalculateUtil');
		$science = Science::singleton($user);
		//道具和科技影响
		$effectScience = $science->getScienceWithEffectId($effectID);
		$effectGoods = CalculateUtil::getGoodsStatus($uid, array($effectID));
		$effectValue = $effectScience['value'] + $effectGoods[$effectID]['value'];
		$total = floor($value * max(1 + $effectValue / 100, 0.1));
		
		if($user->country != 0) {
			$countryCompensate = CalculateUtil::getCountryEffect($user,array($effectID));
		}
		if($user->vip > 0) {
			$vipCompensate = CalculateUtil::getVipEffect($user,array($effectID));
		}
		if(is_array($detail))
		{
			$detail['number']['base'] = $value;
			$detail['number']['increment'] = $total - $value;
			$detail['percent']['science'] = $effectScience['value'];
			$detail['percent']['goods'] = $effectGoods[$effectID]['value'];
			$detail['percent']['country'] = $countryCompensate[$effectID]['value'];
			$detail['percent']['vip'] = $vipCompensate[$effectID]['value'];
		}
		return $total;
	}
	
	public function deleteOverRescource($uid)
	{
		$cityItem = self::getWithUID($uid);
		self::getItems($uid, true);
		return $cityItem;
	}
	
	/**
	 * 超上限数据
	 * @param XObject $cityItem
	 * @param unknown_type $cityData
	 */
	static function resourceMaxDeal(XObject $cityItem, $cityData){
		$moneyLimitEff = 1;
		$user = UserProfile::getWithUID($cityItem->uid);
		if($user->league){
			import('service.action.ScienceClass');
// 			$effect = Science::singleton($user)->getScienceWithEffectId(115);	
// 			if($effect){//根据作用号115，增加士兵的上限
// 				$forcesLimitEff= (1+$effect['value']/100);
// 			}	

			$effect1 = Science::singleton($user)->getScienceWithEffectId(116);	
			if($effect1){//根据作用号116，增加银币的上限
				$moneyLimitEff= (1+$effect1['value']/100);
			}
		}
		
		if(($cityItem->soldiers > $cityData['soldiersLimit'])
			||($cityItem->money > $cityData['moneyLimit']*$moneyLimitEff)
// 			||($cityItem->mineral > $cityData['storage'])
// 			||($cityItem->oil > $cityData['storage'])
// 			||($cityItem->food > $cityData['storage'])
			)
		{
				$cityItem->soldiers = min($cityItem->soldiers,$cityData['soldiersLimit']);
				$cityItem->money = min($cityItem->money,$cityData['moneyLimit']*$moneyLimitEff);
// 				$cityItem->mineral = min($cityItem->mineral,$cityData['storage']);
// 				$cityItem->oil = min($cityItem->oil,$cityData['storage']);
// 				$cityItem->food = min($cityItem->food,$cityData['storage']);
				$cityItem->save();
		}
	}
	
	public function checkMoneyLimit(){
		$moneyLimitEff = 1;
		import('service.item.ItemSpecManager');
		$moneyLimit = ItemSpecManager::singleton('default','building.xml')->getitem($this->level+1301000)->para3;//金钱上限
		$user = UserProfile::getWithUID($this->uid);
		if($user->league){
			import('service.action.ScienceClass');
			$effect1 = Science::singleton($user)->getScienceWithEffectId(116);
			if($effect1){//根据作用号116，增加银币的上限
				$moneyLimit *= (1+$effect1['value']/100);
			}
		}
		return $moneyLimit;
	}
	
	public function checkForcesLimit(){
		import('service.item.ItemSpecManager');
		$forcesLimit = ItemSpecManager::singleton('default', 'item.xml')->getItem(120013)->k3;//初始上限
		import('service.action.BuildingClass');
		$buildingClass = new BuildingClass($this->uid);
		$barrack = $buildingClass->getBuildings(1,1313000);
		if($barrack && $barrack[0]['level'] > 0)
		{
			$xmlBuild = ItemSpecManager::singleton('default', 'building.xml')->getItem($barrack[0]['level']+1313000);
			$forcesLimit += $xmlBuild->para3;
		}
		$user = UserProfile::getWithUID($this->uid);
		if($user->league){
			import('service.action.ScienceClass');
			$effect = Science::singleton($user)->getScienceWithEffectId(115);
			if($effect){//根据作用号115，增加士兵的上限
				$forcesLimit*= (1+$effect['value']/100);
			}
		}
		return $forcesLimit;
	}
	
	static public function initCityItem($uid)
	{
		import('service.action.ConstCode');
		$cityInitXML = ItemSpecManager::singleton()->getItem(120010);
		$cityItem = new CityItem();
		$cityItem->uid = $uid;
		$cityItem->level = ConstCode::CITY_INIT_LEVEL;//城市等级
		$cityItem->boom = 0;//当前繁荣
		$cityItem->boomLoss = 0;//繁荣损失
		$cityItem->money = $cityInitXML->k2;//银币
//		$cityItem->mineral = $cityInitXML->k1;//矿物
//		$cityItem->oil = $cityInitXML->k1;//石油
//		$cityItem->food = $cityInitXML->k1;//粮食
		$cityItem->soldiers = 100;//新兵数
		$cityItem->forces = $cityInitXML->k3;//已招募的兵数
		$cityItem->groundIndex = array(0=>'1,2,3,4,5,6,7,11,8,9,10');//需要判断开启的地块的记录
		$cityItem->save();
		
		
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('groundIndex');
		}
		parent::setCacheValue($cachekey, $res);
		return $res;
	}
	
	/**
	 * 更新城市兵力
	 */
	static function updateForces($uid, $delta, $reason='', $cityItem=null) {
		if(!$cityItem) { 
			import('service.item.CityItem');
			$cityItem = self::getWithUID($uid);
		}
		import('service.action.CalculateUtil');
		$remainForces = CalculateUtil::changeForces($cityItem, $delta, $reason);
		$cityItem->save();
		return $remainForces; 
	}
	
	public function save(){
		$this->serializeProperty('groundIndex');
		$this->serializeProperty('forceMarketList');
		parent::save();
		$this->unserializeProperty('groundIndex');
		$this->unserializeProperty('forceMarketList');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
}
?>