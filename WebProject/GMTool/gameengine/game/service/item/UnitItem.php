<?php
/**
 * Arms
 * 
 * 兵种属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class UnitItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $endTime; //研究结束时间
	protected $studyArms; //当前研究兵种
	protected $armsList; //兵种列表 array(arms_id => array('id' => $arms_id, ...), ..)
	protected $armyFront;	//兵种等级
	protected $armyMiddle;	//兵种等级
	protected $armyBack;	//兵种等级
	protected $navyFront;	//兵种等级
	protected $navyMiddle;	//兵种等级
	protected $navyBack;	//兵种等级
	protected $airFront;	//兵种等级
	protected $airMiddle;	//兵种等级
	protected $airBack;		//兵种等级
	protected $armySwapTime;	//兵种互换次数
	
	private $army_para;//陆军
	private $navy_para;//海军
	private $air_para;//空军
	
	private $army_country;//陆军国家效果值
	private $navy_country;//海军国家效果值
	private $air_country;//空间国家效果值
	
	static function getUnit($uid){
		return self::getWithUID($uid);
	}
	
	/*
	 * 初始化兵种
	 */
	static function initUnit($uid, $itemId){
		$unitItem = self::getUnit($uid);
		if(!$unitItem){
			$unitItem = new UnitItem();
			$unitItem->uid = $uid;
			$unitItem->armsList = array();
		}
		$armsList = array();
		$armsList[$itemId] = array('id' => $itemId);
		$unitItem->armsList = $armsList;
		$unitItem->save();
	}
	/*
	 * 获得兵种的下一级别配置信息
	 */
	public function getArminfo($arrmy_id,$uid){
		import('service.action.BuildingClass');
		import('service.action.CalculateUtil');
		import('service.action.ConstCode');
		$building = new BuildingClass($uid);
		$arms = ItemSpecManager::singleton('default', 'arms.xml')->getItem($arrmy_id);
		if(empty($arms)){
			return null;
		}
		switch (substr($arrmy_id,1,1)){
			case 1: //陆军
				$BuildingItems = $building->getBuildings(1, 1308000);
				$landBuilding = $BuildingItems[0];
				$this->army_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1308000 + $landBuilding['level'])->para1;
				$effectArmy = CalculateUtil::getGoodsStatus($uid,array(ConstCode::EFFECT_ARMY));
				$effect_value = $effectArmy[ConstCode::EFFECT_ARMY]['value'];
				break;
			case 2: //海军
				$BuildingItems = $building->getBuildings(1, 1309000);
				$landBuilding = $BuildingItems[0];
				$this->navy_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1309000 + $landBuilding['level'])->para1;
				$effectNavy = CalculateUtil::getGoodsStatus($uid,array(ConstCode::EFFECT_NAVY));
				$effect_value = $effectNavy[ConstCode::EFFECT_NAVY]['value'];
				break;
			case 3: //空军
				$BuildingItems = $building->getBuildings(1, 1310000);
				$landBuilding = $BuildingItems[0];
				$this->air_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1310000 + $landBuilding['level'])->para1;
				$effectAir = CalculateUtil::getGoodsStatus($uid,array(ConstCode::EFFECT_AIRFORCE));
				$effect_value = $effectAir[ConstCode::EFFECT_AIRFORCE]['value'];
				break;
		}
		import('service.item.ItemSpecManager');
		$unitItem = self::getUnit($uid);
		return self::resArr($unitItem, $arms, $effect_value);
	}
	
	public function getItems($uid){
		import('service.action.BuildingClass');
		import('service.action.CalculateUtil');
		import('service.action.ConstCode');
		$building = new BuildingClass($uid);
		$landLevel = $skyLevel = $seaLevel = 1;
		$BuildingItems = $building->getBuildings(1, array(1308000,1309000,1310000));
		foreach($BuildingItems as $temp)
		{
			switch($temp['itemId'])
			{
				case '1308000':
					$landBuilding = $temp;
					break;
				case '1309000':
					$seaBuilding = $temp;
					break;
				case '1310000':
					$skyBuilding = $temp;
					break;
			}
		}
		//国家特效值
		import('service.action.CalculateUtil');
		$effect = CalculateUtil::getGoodsStatus($uid,array(ConstCode::EFFECT_ARMY,ConstCode::EFFECT_AIRFORCE,ConstCode::EFFECT_NAVY));
		//陆军研究院等级
		if($landBuilding){
			$this->army_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1308000 + $landBuilding['level'])->para1;
			$this->army_country = $effect[ConstCode::EFFECT_ARMY]['value'];
		}
		//空军研究院等级
		if($skyBuilding){
			$this->air_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1310000 + $skyBuilding['level'])->para1;
			$this->air_country = $effect[ConstCode::EFFECT_AIRFORCE]['value'];
		}
		//海军研究院等级
		if($seaBuilding){
			$this->navy_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1309000 + $seaBuilding['level'])->para1;
			$this->navy_country = $effect[ConstCode::EFFECT_NAVY]['value'];
		}
				
		import('service.item.ItemSpecManager');
		$armsGroup = ItemSpecManager::singleton('default', 'arms.xml')->getGroup('arms_role');
		$unitItem = self::getUnit($uid);
		
		foreach (array_keys($unitItem->armsList) as $arrmy_id){
			switch (substr($arrmy_id,1,1)){
				case 1 :
					$effect_value = $this->army_country;
					break;
				case 2 :
					$effect_value = $this->navy_country;
					break;
				case 3 :
					$effect_value = $this->air_country;
					break;
			}
			$arms = $armsGroup->$arrmy_id;
			if (!isset($arms)) continue;
			$level = $arrmy_id%1000;
			if($level == 150)
				$next_id = intval($arrmy_id) + 351;
			else
				$next_id = intval($arrmy_id) + 1;
			$nextarms = $armsGroup->$next_id;
			if($nextarms){
				//取得下一级别兵种配置信息
				$data[] = self::resArr($unitItem, $arms, $effect_value);
				$data[] = self::resArr($unitItem, $nextarms, $effect_value);
			}else{
				$pre_id = $arrmy_id;
				$prearms = $armsGroup->$pre_id;
				$data[] = self::resArr($unitItem, $prearms, $effect_value);
				$data[] = self::resArr($unitItem, $arms, $effect_value, true);
			}
		}
		return $data;
	}
	/**
     +----------------------------------------------------------
     * 初始化兵种后返回相应的信息
     +----------------------------------------------------------
     * @method updateGeneralRank
     * @access public
     * @param $generalRankId 用户将要升级的军衔id
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function getUnitConf($array_unitids){
		import('service.action.BuildingClass');
		import('service.action.CalculateUtil');
		import('service.action.ConstCode');
		$building = new BuildingClass($this->uid);
		$landLevel = $skyLevel = $seaLevel = 1;
		$BuildingItems = $building->getBuildings(1, array(1308000,1309000,1310000));
		foreach($BuildingItems as $temp)
		{
			switch($temp['itemId'])
			{
				case '1308000':
					$landBuilding = $temp;
					break;
				case '1309000':
					$seaBuilding = $temp;
					break;
				case '1310000':
					$skyBuilding = $temp;
					break;
			}
		}
		//国家特效值
		import('service.action.CalculateUtil');
		//陆军研究院等级
		if($landBuilding){
			$landLevel = $landBuilding['level'];
			$this->army_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1308000 + $landBuilding[0]['level'])->para1;
			$effectArmy = CalculateUtil::getGoodsStatus($this->uid,array(ConstCode::EFFECT_ARMY));
			$this->army_country = $effectArmy[ConstCode::EFFECT_ARMY]['value'];
		}
		//空军研究院等级
		if($skyBuilding){
			$skyLevel = $skyBuilding['level'];
			$this->air_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1310000 + $skyBuilding[0]['level'])->para1;
			$effectAir = CalculateUtil::getGoodsStatus($this->uid,array(ConstCode::EFFECT_AIRFORCE));
			$this->air_country = $effectAir[ConstCode::EFFECT_AIRFORCE]['value'];
		}
		//海军研究院等级
		if($seaBuilding){
			$seaLevel = $seaBuilding['level'];
			$this->navy_para = ItemSpecManager::singleton('default', 'building.xml')->getItem(1309000 + $seaBuilding[0]['level'])->para1;
			$effectNavy = CalculateUtil::getGoodsStatus($this->uid,array(ConstCode::EFFECT_NAVY));
			$this->navy_country = $effectNavy[ConstCode::EFFECT_NAVY]['value'];
		}
				
		import('service.item.ItemSpecManager');
		$unitItem = self::getUnit($this->uid);
		
		foreach ($array_unitids as $arrmy_id){
			switch ($arrmy_id{1}){
				case 1 :
					$effect_value = $this->army_country;
					break;
				case 2 :
					$effect_value = $this->navy_country;
					break;
				case 3 :
					$effect_value = $this->air_country;
					break;
			}
			
			$arms = ItemSpecManager::singleton('default', 'arms.xml')->getItem($arrmy_id);
			$data[] = self::resArr($unitItem, $arms, $effect_value);
			//取得下一级别兵种配置信息
			$next_id = intval($arrmy_id) + 1;
			$nextarms = ItemSpecManager::singleton('default', 'arms.xml')->getItem($next_id);
			if($nextarms){
				$data[] = self::resArr($unitItem, $nextarms, $effect_value);
			}
			
		}
		return $data;
	}
	
	
	
	private function resArr($unitItem, $arms,$effect_value=0,$maxLevel = false){
		return array(
			'itemId' => $arms->id,
			'status' => self::isStudying($unitItem, $arms->id),//0未研究,1研究中,2已研究
			'endTime' => $unitItem->endTime,
			'att1' => $arms->att1,
			'att2' => $arms->att2,
			'att3' => $arms->att3,
			'att4' => $arms->att4,
			'att1_para' => $arms->att1_para,
			'att2_para' => $arms->att2_para,
			'att3_para' => $arms->att3_para,
			'att4_para' => $arms->att4_para,
			'att5_para' => $arms->att5_para,
			'att1_para2' => $arms->att1_para2,
			'att2_para2' => $arms->att2_para2,
			'att3_para2' => $arms->att3_para2,
			'att4_para2' => $arms->att4_para2,
			'att5_para2' => $arms->att5_para2,
			'building' => CalculateUtil::getItemIdFromXmlId($arms->building),
			'b_lv' => CalculateUtil::getConditionLvFromXmlId($arms->building),
			'arms' => $arms->arms,
			'player_lv' => $arms->player_lv,
			'gold' => $arms->gold,
			'mineral' => $arms->mineral,
			'oil' => $arms->oil,
			'food' => $arms->food,
			'effect1' => $arms->effect1,
			'effect2' => $arms->effect2,
			'effect3' => $arms->effect3,
			'value1' => $arms->value1,
			'value2' => $arms->value2,
			'value3' => $arms->value3,
			'time' => self::StudyTime($arms, $effect_value),
			'maxLevel' => $maxLevel,
			'upgradeLevel' => self::upgradeLevel($unitItem, $arms->id),
			'remind_lv' => $arms->remind_lv,
		);
	}
	private function upgradeLevel($unitItem,$unitId){
		//通过unit获得对应的level
		$unitType = substr($unitId, 0, 2);
		import('service.action.ConstCode');
		$unitLink = ConstCode::$unitLink;
		$dbName = $unitLink[$unitType];
		if($dbName && $unitItem->$dbName){
			return $unitItem->$dbName;
		}else{
			return 0;
		}
	}
	
	/*
	 * 判定兵种状态
	 */
	static function isStudying($unitItem, $unitId){
		if($unitItem->studyArms == $unitId && $unitItem->endTime > time()){
			return 1;
		}
		if($unitItem->armsList[$unitId]){
			return 2;
		}
		return 0;
	}
	
	/*
	 * 取得兵种类型
	 * 
	 * 1:陆,2:海,3:空
	 */
	static function getArmsTypeFromId($itemId){
		return substr($itemId, 1, 1);
	}
	
	/*
	 * 研究费时
	*/
	static function getStudyTime($building, $xmlArms,$effectValue=0 ){
		switch ($xmlArms->id{1}){
			case 1: //陆军
				$buildId = 1308000;
				break;
			case 2: //海军
				$buildId = 1309000;
				break;
			case 3: //空军
				$buildId = 1310000;
				break;
		}
		$res = $building->getBuildings(1, CalculateUtil::getItemIdFromXmlId($buildId));
		$xmlBuilding = ItemSpecManager::singleton('default', 'building.xml')->getItem($buildId + $res[0]['level']);
		return intval($xmlArms->time / (1 + $xmlBuilding->para1) * (1 - $effectValue / 100));
	}
	
	/*
	 * 研究费时
	 */
	private function StudyTime( $xmlArms, $effectValue=0){
		switch ($xmlArms->id{1}){
			case 1: //陆军
				$para1 = $this->army_para;
				break;
			case 2: //海军
				$para1 = $this->navy_para;
				break;
			case 3: //空军
				$para1 = $this->air_para;
				break;
		}

		return intval($xmlArms->time / (1 + $para1) * (1 - $effectValue / 100));
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
		if($res)
			$res->unserializeProperty('armsList');
		parent::setCacheValue($cachekey, $res);
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('armsList');
		parent::save();
		$this->unserializeProperty('armsList');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
	
	static $unitCache;
	static function getCacheUnit($uid){
		if(!self::$unitCache[$uid] && $uid){
			self::$unitCache[$uid] = self::getWithUID($uid); 
		}
		return self::$unitCache[$uid];
	}
}
?>