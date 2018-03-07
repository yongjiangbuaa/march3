<?php
/**
 * PowerItem
 * 
 * 城市属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class PowerItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $sel_power; //当前所在power
	protected $powerList; //打过的势力 array('power_id' => array(npc_id => array('id' => $id, ....),...),.....)
	protected $powerSetList; //存储已打过的PVE，用于24:00重置或使用道具时，清除该List，结构同$powerList
	protected $powerRecordList;
	protected $powerRewardFlag;
	protected $hidePowerList;	//隐藏势力array('power_id' => array(npc_id => array('id' => $id, 'time' => $time, 'cd' => $cd...),...),...)
	
	static function getPower($uid){
		$powerItem = self::getWithUID($uid);
		return $powerItem;
	}

	public function getItems($uid){
		$userProfile = UserProfile::getWithUID($uid);
		import('service.item.CityItem');
		$cityItem = CityItem::getWithUID($uid);
		if(!$cityItem){
			CityItem::initCityItem($uid);
			$cityItem = CityItem::getWithUID($uid);
		}
		import('service.item.ItemSpecManager');
		import('service.action.PowerClass');
		import('service.action.CalculateUtil');
		$xmlPower = ItemSpecManager::singleton('default', 'power.xml')->getGroup('power');
		$xmlNpc = ItemSpecManager::singleton('default', 'npc.xml')->getGroup('npc');
		
		$tempPowerList  = array();
		$powerList = array();
		$powerSetList = array();
		$powerRewardFlag = NULL;
		$powerItem = self::getPower($uid);
	//	$powerList = $powerItem->powerList;
	//	$powerSetList = $powerItem->powerSetList;

		//初始化
		if(!$powerItem){
			//$powerList = array();
			//$powerSetList = array();
		}else{
			$powerList = $powerItem->powerList;
			$powerSetList = $powerItem->powerSetList;
			$powerRecordList = $powerItem->powerRecordList;
			$powerRewardFlag = $powerItem->powerRewardFlag;
			$hidePowerList = $powerItem->hidePowerList;
		}
		if(!empty($powerSetList)&&is_array($powerSetList)){
			foreach ($powerSetList as $key=>$powerSet){
				$powerList[$key] = 	$powerSet;
				
			}
		}
		$tempPowerList = $powerList;
		$data = array();
		
		$i = 0;
		foreach($xmlPower as $power){

			//type为3的势力通过后不再返回
			if($power->power_type == 3 && Power::isNpcComplete($power->id, $tempPowerList[$power->id])){
				continue;
			}
			$data[$i]['itemId'] = $power->id;
			$data[$i]['power_add'] = $power->power_add;
			$data[$i]['power_type'] = $power->power_type;
			$data[$i]['player_lv'] = $power->player_lv;
			$data[$i]['city_lv'] = $power->city_lv;
			$data[$i]['task_id'] = $power->task_id;
			$data[$i]['power_id'] = $power->power_id;
			import('service.action.CalculateUtil');
			$rewardIdInfo = CalculateUtil::getInfoByRewardId($power->reward_id);			
			$data[$i]['rewardInfo'] = $rewardIdInfo;
			$data[$i]['powerRewardFlag'] = $powerRewardFlag;
// 			$data[$i]['hidePowerList'] = $hidePowerList;		
			$data[$i]['npcList'] = array();
			$data[$i]['selfPower'] = $power->id == $powerItem->sel_power ? 1 : 0;
			

			if($power->power_type != 3){
				if(!Power::gotoCheck($userProfile, $power, $tempPowerList, $userProfile->level, $cityItem->level)){
					$i++;
					continue;
				}
			}
			foreach ($xmlNpc as $npc){
				if($npc->power_id == $power->id){
					$xmlBattle = ItemSpecManager::singleton('default', 'battle.xml')->getItem($npc->battle_id);
					$data[$i]['npcList'][] = self::resNpcList($npc, $xmlBattle, $tempPowerList, $powerRecordList, $hidePowerList, $power,$userProfile);
				}
			}
			$i++;
		}
		return $data;
	}
	
	static function resNpcList($npc, $xmlBattle, $powerList, $powerRecordList, $hidePowerList, $powerXml,$user){
		$powerId = $powerXml->id;
		import('service.action.CalculateUtil');
		import('service.action.FormationClass');
		import('service.action.GeneralClass');
		$armyId = CalculateUtil::getArmsOrRewardForFight($user->level, $xmlBattle);
// 		//ABTest
// 		$xmlFile = 'army.xml';
// 		if ($user->test == 1 && $user->level < 5)
// 		{
// 			$xmlFile = 'army_'.$user->test.'.xml';
// 		}
		import('service.action.LoadXMLUtil');
		$xmlArmy = LoadXMLUtil::loadArmy($armyId);
		//$xmlArmy = ItemSpecManager::singleton('default',$xmlFile)->getItem($armyId);
		$matrixItem = Formation::singleton()->getFormation($armyId,2);
// 		foreach ($matrixItem->generalList as $key => $val)
// 		{
// 			if ($val != null && is_array($val))
// 			{
// 				$matrixArms[$key]['arms'] = $val['arms'];
// 			}
// 		}
		for ($i = 1; $i < 4; $i++)
		{
			for ($j = 1; $j < 4; $j++)
			{
				$pos = $i * 3 - 3 + $j;
				if ($matrixItem->generalList['pos'.$pos] != null)
				{
					$matrixArms[$pos]['arms'] = $matrixItem->generalList['pos'.$pos]['arms'];
					$matrixArms[$pos]['itemId'] = $matrixItem->generalList['pos'.$pos]['uid'];
					if($matrixItem->generalList['skill'.$pos])
					{
						$matrixArms[$pos]['skill'] = $matrixItem->generalList['skill'.$pos];
					}
					else 
					{
						$armsItem = ItemSpecManager::singleton('default','arms.xml')->getItem($matrixArms[$pos]['arms']);
						$matrixArms[$pos]['skill'] = $armsItem->skill_id;
					}
				}
			}
		}
		$isCompleteFlag = 0;
		$hisComplete = 0;
		if($powerXml->power_type == 4){
			if($powerRecordList[$powerId][$npc->id]){
				$hisComplete = 1;
			}
		}else{
			if($powerList[$powerId + 1]){
				$isCompleteFlag = 1;
			}
			if($powerList[$powerId][$npc->id]){
				$hisComplete = 1;
			}
		}
		if($powerList[$powerId][$npc->id])
			$isCompleteFlag = 1;
// 		$total = General::singleton($user)->addUpPropertyValue($matrixItem->generalList,true);
// 		$fightPower = CalculateUtil::calculateFightPower($total);
		return array(
			'id' => $npc->id,
			'power_id' => $npc->power_id,
			'battle_id' => $npc->battle_id,
			'army_type' => $xmlBattle->arm_type,
			'battle_cost' => $xmlBattle->battle_cost,
			'fb_type' => $npc->fb_type,
			'player_lv' => $npc->player_lv,
			'city_lv' => $npc->city_lv,
			'task_id' => $npc->task_id,
			'fightPower'=> 0,//$fightPower,
			'fb_id' => $npc->fb_id,
			'fb_cd' => $hidePowerList[$npc->power_id][$npc->id]['cd'],
			'battle_ready' => $xmlArmy->battle_ready,
			'tip_skill'=>$xmlArmy->tip_skill,
			'isComplete' => $isCompleteFlag,
			'hisComplete' => $hisComplete,
			'rewards' => CalculateUtil::getInfoByRewardId($xmlBattle->reward),
			'matrixArms'=>$matrixArms,
			'boss'=>$xmlArmy->boss,
		);
	}
	
	/**
	 * 用于每日凌晨重置powerSetList
	 */
	
	static function clearPowerSetList($uid){
		
		$powerItem = self::getPower($uid);
		if($powerItem){
			$powerItem->powerSetList = Array();
			$powerItem->save();
		}
	}
	/**
	 * 用于重置powerSetList
	 */
	
	static function clearPowerSetListByPowerId($uid,$powerId){
		
		$powerItem = self::getPower($uid);
		$tempList = Array();
		if($powerItem){
			foreach($powerItem->powerSetList as $key=>$powerSet){
				if($key !=$powerId)
					$tempList[$key] = $powerSet;
			}
			$powerItem->powerSetList = $tempList;
			$powerItem->save();
		}
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
		if ($cacheVal == 'NaN')
			return null;
		elseif ($cacheVal)
			return $cacheVal;
		
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('powerList');
			$res->unserializeProperty('powerSetList');
			$res->unserializeProperty('powerRecordList');
			$res->unserializeProperty('hidePowerList');
		}
		$cacheVal = $res;
		if(!$res)
		{
			$cacheVal = 'NaN';
		}
		parent::setCacheValue($cachekey, $cacheVal);
		return $res;
	}

	public function save(){
		$this->serializeProperty('powerList');
		$this->serializeProperty('powerSetList');
		$this->serializeProperty('powerRecordList');
		$this->serializeProperty('hidePowerList');
		parent::save();
		$this->unserializeProperty('powerList');
		$this->unserializeProperty('powerSetList');
		$this->unserializeProperty('powerRecordList');
		$this->unserializeProperty('hidePowerList');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
}
?>