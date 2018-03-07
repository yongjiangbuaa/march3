<?php
/**
 * BuildingItem
 * 
 * 建筑属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class BuildingItem extends RActiveRecord {
	private static $instance = null;
	protected $ownerId;//所属用户
	protected $cityType;//建筑所属城市
 	protected $pos;//建筑位置
 	protected $itemId;//建筑种类
 	protected $level;//建筑等级
 	protected $trend;//建造趋势
 	protected $finishTime;//建造完成时间
 	protected $lastUpdateTime;//上次刷新时间
 	protected $totalResource;//一共产生的资源数
 	
 	protected $upgradeInfo;//升级所需信息
 	protected $reachMaxLevel = false;//达到最高等级
 	protected $maxLevel;//主城最高级别
 	protected $para1 = 0;
 	protected $para2 = 0;
 	protected $para3 = 0;
 	
 	static function singleton() {
 		if (!self::$instance) {
 			self::$instance = new self;
 		}
 		return self::$instance;
 	}
 	
 	/**
 	 * 获得指定区域的建筑列表
 	 */
 	static function getBuildingsByCityType($uid, $cityType = null){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		if($cityType){
			$sql = "select * from building where ownerId = '{$uid}' and cityType='{$cityType}'";
		}else{
			$sql = "select * from building where ownerId = '{$uid}'";
		}
		return $mysql->execResult($sql, 100);
 	}
 	
 	/**
 	 * 
 	 * 根据itemId获得用户建筑
 	 */
 	static function getBuildingByItemId($uid, $itemId){
 		$mysql = XMysql::singleton();
		$sql = "select * from building where ownerId = '{$uid}' and itemId='{$itemId}'";
		return $mysql->execResult($sql, 100);
 		
 		
 	}
 	
 	
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
 	 * 获得用户主岛的所有建筑
 	 * @param String $uid
 	 */
 	public function getItems($uid)
	{
		return $this->getItemByBuildingType($uid);
	}
	
	static function getNewItems($uid, $itemUids){
		return self::singleton()->getItemByBuildingType($uid, $itemUids[0]);
	}
	
	public function getItemByBuildingType($uid, $buildingType='all') {
		if($buildingType === 'all') {
			$buildings = self::getBuildingsByCityType($uid);
		} else {
			if(!$buildingType) $buildingType = '1301000';
			$buildings = self::getBuildingByItemId($uid, $buildingType);
		}
		if($buildings == null){
			$buildings = $this->initBuilding($uid);
			$buildings = self::toObject('BuildingItem',$buildings,true);
		} else {
// 			import('service.action.BuildingClass');
// 			$buildingClass = new BuildingClass($uid);
// 			$temp = self::toObject('BuildingItem',$buildings,true);
// 			$afterRefresh = $buildingClass->refreshBuildings($temp);
// 			$buildings = $afterRefresh['building'];
			$buildings = self::toObject('BuildingItem',$buildings,true);
		}
		import('service.item.ItemSpecManager');
		import('service.action.CalculateUtil');
		import('service.action.ConstCode');
		import('service.action.WorldClass');
		$effectGoods = CalculateUtil::getGoodsStatus($uid, array(ConstCode::EFFECT_BUILDING));
		$data = array();
		if(count($buildings) > 0)
		{
			import('service.item.UserWorldItem'); //指挥中心着火修复
			$userWorldItem = UserWorldItem::getWithUID($uid);
			if($userWorldItem) $userWorldItem->unserializeProperty('ccRecoverTime');
			foreach ($buildings as &$building){
				if($building->itemId == '1301000' && $userWorldItem && !$userWorldItem->ccRecoverTime && $userWorldItem->hisMaxCCLevel > $building->level) {
					$building->level = $userWorldItem->hisMaxCCLevel;
					$building->save();
				}
				$building->getXmlUpgradeInfo($building->level + 1,$effectGoods);

				$data[] = self::resArr($building);
			}
		}
		$index = 0;
		$effects = array();
		foreach($data as $key => $value) 
		{
			$detail = $this->getEffects($value, $uid);
			if($detail != false)
			{
				$data[$key]['effects'] = $detail;
			}
			else 
			{
				$data[$key]['effects'] = array();
			}
		}
		
		foreach($data as &$build) {
			if($build['itemId'] == '1301000') {
				if($userWorldItem && $userWorldItem->ccRecoverTime) {
					$build['hisMaxCCLevel'] = $userWorldItem->hisMaxCCLevel;
					if(is_string($userWorldItem->ccRecoverTime)) {
						$ccRecoverTime = $userWorldItem->unserializeProperty('ccRecoverTime');
					} else {
						$ccRecoverTime = $userWorldItem->ccRecoverTime;
					}
					$build['ccRecoverTime'] = $ccRecoverTime;
				}
			}
		}
		return $data;
	}
	
	public function getEffects($buildingItem, $uid)
	{
		import('service.action.ConstCode');
		import('service.item.CityItem');

		$buildingId = $buildingItem['itemId'];
		$originalValue = $buildingItem['para1'];
		
		$detail = array();
		$type = 0;
		switch ($buildingId)
		{
			case 1301000: //指挥中心
				$type = ConstCode::EFFECT_MONEY;
				break;
			case 1302000: //科技中心
				$type = ConstCode::EFFECT_SCIENCE;
				break;
			case 1308000: //陆军研究中心
				$type = ConstCode::EFFECT_ARMY; //陆军兵种研究时间
				break;
			case 1309000: //海军研究中心
				$type = ConstCode::EFFECT_NAVY; //海军兵种研究时间
				break;
			case 1310000: //空军研究中心
				$type = ConstCode::EFFECT_AIRFORCE; //空军兵种研究时间
				break;
			case 1313000: //兵营
				$type = ConstCode::EFFECT_SOLDIER; //新兵产出
				$originalValue = $buildingItem['para2'];
				break;
			case 1331000: //集中营
			case 1306000: //军事学院
			case 1307000: //军备中心
			default:
				//$type = ConstCode::EFFECT_BUILDING; //建筑升级时间
				return false;
		}

		CityItem::addCompensate($uid, $type, $originalValue, $detail);

		return $detail;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param String $uid
	 * @return Object
	 */
	public function getWithUID($uid){
		$buildingItem = self::getOne(__CLASS__, $uid);
		if(!isset($buildingItem))
			return;
		$buildingItem->getXmlUpgradeInfo($buildingItem->level + 1);
		return $buildingItem;
	}
	
	/**
	 * 同步指挥中心与城市等级 
	 *@author roc
	 */
	static function syncLevelWithCity($uid, $level){
		if($level < 1){
			$level = intval($level);
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update building set level={$level} where ownerId='{$uid}' and itemId=1301000";
		$mysql->execute($sql);
	}
	
	/**
	 * 获得建筑升级信息
	 * @param BuildingItem $buildingItem
	 * @param int $level
	 */
	public function getXmlUpgradeInfo($level,$effectGoods = null)
	{
		import('service.item.ItemSpecManager');
		if(!$effectGoods){
			import('service.action.CalculateUtil');
			import('service.action.ConstCode');
			$effectGoods = CalculateUtil::getGoodsStatus($this->ownerId, array(ConstCode::EFFECT_BUILDING));
		}
		//建筑最高等级
		$building_maxlv1 = ItemSpecManager::singleton()->getItem('building_maxlv')->k1;
		$building_maxlv2 = ItemSpecManager::singleton()->getItem('building_maxlv')->k2;
		$buildingXml = ItemSpecManager::singleton('default','building.xml');
		if($this->itemId == 1301000){
			import("service.item.CityItem");
			$cityItem = CityItem::getWithUID($this->ownerId);
			$level = $level - $this->level + $cityItem->level;
			$upgradeXml = $buildingXml->getItem($this->itemId+$level);
			$building_maxlv = $building_maxlv1;
			$staticId = $this->itemId + $cityItem->level;
		}
		else {
			$upgradeXml = $buildingXml->getItem($this->itemId+$level);
			$building_maxlv = $building_maxlv2;
			$staticId = $this->itemId + $this->level;
		}
		$this->maxLevel = $building_maxlv;
		if($this->level >= $building_maxlv)
			$this->reachMaxLevel = true;
		else
			$this->reachMaxLevel = false;
		$info = array('level'=>$upgradeXml->level
					,'plevel'=>$upgradeXml->plevel
					,'gold'=>$upgradeXml->gold
					,'mineral'=>$upgradeXml->mineral
					,'oil'=>$upgradeXml->oil
					,'food'=>$upgradeXml->food
					,'addcexp'=>$upgradeXml->addcexp
					,'addpexp'=>$upgradeXml->addpexp
					,'time'=>$upgradeXml->time * max(1 - ($effectGoods[ConstCode::EFFECT_BUILDING]['value']) / 100, 0.001)
					,'para1'=>$upgradeXml->para1
					,'para2'=>$upgradeXml->para2
					,'para3'=>$upgradeXml->para3
					,'reward'=>$upgradeXml->reward
					);
		if($upgradeXml->building1)
		{
			$info['blv1'] = $upgradeXml->building1%1000;
			$info['building1'] = $upgradeXml->building1 - $info['blv1'];
		}
		if($upgradeXml->building2)
		{
			$info['blv2'] = $upgradeXml->building2%1000;
			$info['building2'] = $upgradeXml->building2 - $info['blv2'];
		}
		$this->upgradeInfo = $info;

		//获得当前等级参数
// 		$this->fillXMLProperty('building.xml', $this->itemId + $this->level);
		$staticXml = $buildingXml->getItem($staticId);
		if($staticXml->para1)
			$this->para1 = $staticXml->para1;
		if($staticXml->para2)
			$this->para2 = $staticXml->para2;
		if($staticXml->para3)
			$this->para3 = $staticXml->para3;
			
		if($this->itemId == '1301000'){
			import('service.action.WorldClass');
			$this->para2 = World::addCityPicMoneyRatio($this->ownerId, $this->para2);
			if($this->upgradeInfo['para2']) {
				$baseValue = $this->upgradeInfo['para2'];
				$this->upgradeInfo['para2'] = World::addCityPicMoneyRatio($this->ownerId, $baseValue);
			}
		}
		return $this->upgradeInfo;
	}
	public function initBuilding($userUid){
		import('service.action.ConstCode');
		$data = array();
		//主城
		//cityType主城副城pos建筑位置type建筑类型level建筑等级upgradeTime升级结束时间
		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>1,'itemId'=>1301000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>2,'itemId'=>1308000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>11,'itemId'=>1309000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>7,'itemId'=>1310000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
// 		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>16,'itemId'=>1304000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
// 		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>21,'itemId'=>1305000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
// 		$data[] = array('uid'=>getGUID(),'ownerId'=>$userUid,'cityType'=>1,'pos'=>20,'itemId'=>1308000,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$mysql->addBatch('building',$data);
		return $data;
	}
	
	/*
	 * 根据区域初始化建筑
	 */
	static function intiBuildingByCityType($userUid, $islandId){
		import('service.item.ItemSpecManager');
		$xmlIsland = ItemSpecManager::singleton('default', 'building.xml')->getItem($islandId);
		$building = explode('|', $xmlIsland->floor0);
		import('service.action.ConstCode');
		$buildingItem = new self;
		$buildingItem->ownerId = $userUid;
		$buildingItem->cityType = intval($islandId%1000) + 2;
		$buildingItem->pos = $building[1];
		$buildingItem->itemId = $building[0];
		$buildingItem->level = ConstCode::CITY_INIT_LEVEL;
		$buildingItem->trend = 0;
		$buildingItem->finishTime = 0;
		$buildingItem->lastUpdateTime = time();
		$buildingItem->save();
		
		$buildingItem->getXmlUpgradeInfo($BuildingItem->level + 1);
		return self::resArr($buildingItem);
	}
	
	static function resArr(XObject $building){
		return array(
			'uid' => $building->uid,
			'itemId' => $building->itemId,
			'ownerId' => $building->ownerId,
			'cityType' => $building->cityType,
			'pos' => $building->pos,
			'level' => $building->level,
			'trend' => $building->trend,
			'finishTime' => $building->finishTime,
			'lastUpdateTime' => $building->lastUpdateTime,
			'totalResource' => $building->totalResource,
			'upgradeInfo' => $building->upgradeInfo,
			'reachMaxLevel' => $building->reachMaxLevel,
			'maxLevel' => $building->maxLevel,
			'para1' => $building->para1,
			'para2' => $building->para2,
			'para3' => $building->para3,
		);
	}
}
?>