<?php
/**
 * 
 * 科技属性类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class ScienceItem extends RActiveRecord{
	protected $ownerId; //拥有者uid
	protected $type; //科技类型: 1:陆战;2:海战;3:空战;4:民生;5:经济;6:防御
	protected $level; //当前等级
	protected $status; //状态 0:正常;1:升级中
	protected $upgradeTime; //升级结束时间
	protected $money; //已经捐献的银币 
	protected $userId; //联盟建筑ownerId为联盟ID，缓存键为ownerId
	
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
		return self::getOne(__CLASS__, $uid);
	}
	
	
	static function init($ownerId, $itemId, $type, $level=0){
		$scienceItem = new ScienceItem();
		$scienceItem->itemId = $itemId;
		$scienceItem->ownerId = $ownerId;
		$scienceItem->type = $type;
		$scienceItem->level = $level;
		$scienceItem->status = 0;
		$scienceItem->save();
		return $scienceItem;
	}
	
	//初始化科技的等级以及其他状态
	static function resetScience($ownerId,$type=7){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton(); 
		$sql = "update science set `level`=0,`status`=0,`upgradeTime`=0 where `ownerId`='{$ownerId}' and `type`='{$type}'";
		$resdata = $mysql->execute($sql);
		
	}
	
	//联盟解散后清除数据 type为8
	static function clearAllianceBigScience($allianceId){
		self::clearAllianceBigScienceCache($allianceId);
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton(); 
		$sql = "delete from science where ownerId='{$allianceId}' and type=8 ";
		return $mysql->execute($sql);
	}
	
	static function clearAllianceBigScienceCache($allianceId) {
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$cachekey = __CLASS__.$allianceId;
		$cache->delete($cachekey);
	}
	
	//根据科技类型查询科技
	static function getWithType($ownerId, $type, $status = null){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$conArr = array(
			'ownerId' => $ownerId,
			'type' => $type,
		);
		if(is_int($status)){
			$conArr['status'] = $status;
		}
		$res = $mysql->get('science', $conArr, null, 100);
		return self::to($res, true);
	}
	//取得所有科技
	static function getAllScience($uid){
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal == 'NaN')
			return null;
		elseif ($cacheVal)
			return $cacheVal;
		//查询玩家的所有科技
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		//group中type可以不加
		$sql = "select * from science where ownerId='{$uid}' GROUP BY ownerid,itemid order by level desc";
		$res = $mysql->execResult($sql, 100);
		$cacheVal = $res;
		if(!$res)
		{
			$cacheVal = 'NaN';
		}
		parent::setCacheValue($cachekey, $cacheVal);
		return $res;
	}
	
	//取得所有联盟大科技
	static function getAllianceBigScience($userLeague){
		$cachekey = __CLASS__.$userLeague;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal == 'NaN')
			return null;
		elseif ($cacheVal)
			return $cacheVal;
		//查询玩家的所有科技
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		//group中type可以不加
		$sql = "select * from science where ownerId='{$userLeague}' and type = 8";
		$res = $mysql->execResult($sql, 100);
		$cacheVal = $res;
		if(!$res)
		{
			$cacheVal = 'NaN';
		}
		parent::setCacheValue($cachekey, $cacheVal);
		return $res;
	}
	
	public function save(){
		parent::save();
		$cachekey = __CLASS__.$this->ownerId;
		parent::delCacheValue($cachekey);
	}
	
	/**
	 * 初始化用户科技
	 *
	 * @param unknown_type $userUid
	 */
//	static function initScience($userUid){
//		$scienceItem = new self;
//		$scienceItem->itemId = 502000;
//		$scienceItem->ownerId = $userUid;
//		$scienceItem->type = 5;
//		$scienceItem->level = 1;
//		$scienceItem->status = 0;
//		$scienceItem->isDefault = 1;
//		if($scienceItem->type == 5){
//			$generalList = array();
//			for($i = 1; $i < 10; $i++){
//				$generalList['pos' . $i] = null;
//			}
//			$scienceItem->generalList = $generalList;
//			$scienceItem->serializeProperty('generalList');
//		}
//		$scienceItem->save();
//		return $scienceItem;
//	}
	
	/**
	 * 取得玩家所有科技
	 *
	 * @param String $user
	 * @return Array
	 */
	static function getItems($uid){
		$result = self::getAllScience($uid);	
		$ownSciences = $scienceList = array();
		if(is_array($result)){
			foreach ($result as $value){
				if($value['itemId']){
					$ownSciences[$value['itemId']] = $value;
				}
			}
		}
		import('service.item.ItemSpecManager');
		import('service.action.CalculateUtil');
		import('service.action.BuildingClass');
		$building = new BuildingClass($uid);
		$res = $building->getBuildings(1, 1302000);
		//没有科技建筑
		if(!$res){
			$level = 1;
		}else{
			$level = $res[0]['level'];
		}
		
		//取得联盟等级
		$playerProfile = UserProfile::getWithUID($uid);
		if(!$playerProfile->league)
			$allianceLv = 0;
		else {
			import('service.item.AllianceItem');
			$allianceItem = AllianceItem::getWithUID($playerProfile->league);
			$allianceLv = $allianceItem->level;	
		}
			
		//读取xml中所有科技
		$xmlAllScience = ItemSpecManager::singleton('default', 'science.xml')->getGroup('science_list');
		$currentTime = time();
		$currentScienceId = null;
		//读取科技建筑信息
		$buildingXml = ItemSpecManager::singleton('default', 'building.xml')->getItem($level + 1302000);
		//研究院提速系数
		$para1 = $buildingXml->para1;
		//国家特效值
		import('service.action.CalculateUtil');
		$effectCountry = CalculateUtil::getGoodsStatus($uid,array(107));
		$effectValue = $effectCountry[107]['value'];
		foreach ($xmlAllScience as $science){
			$upgradeTime = 0;
			$status = 0;
			$money = 0;
			$scienceId = CalculateUtil::getItemIdFromXmlId($science->id);
			if($scienceId == $currentScienceId || $science->tab == 8){
				continue;
			}
			$currentScienceId = $scienceId;
			if(!isset($ownSciences[$scienceId]) || !$ownSciences[$scienceId]){
				$scienceLevel = 1;
			}else{
				//正在升级中的科技
				if($ownSciences[$scienceId]['status'] == 1 && $ownSciences[$scienceId]['upgradeTime'] >= $currentTime){
					$scienceLevel = $ownSciences[$scienceId]['level'];
					$status = 1;
					$upgradeTime = $ownSciences[$scienceId]['upgradeTime'];
				} else {
					$scienceLevel = $ownSciences[$scienceId]['level'] + 1;
				}
			}
			$xmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel);
			$currXmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel -1);
//			if()
			if(!$xmlScience){
							$scienceList[] = array(
				'itemId' => $scienceId,
				'level' => $scienceLevel - 1,
				'tab' => $currXmlScience->tab,
				'show_level' => $currXmlScience->show_level,
				'isLimitLv' =>1,			
				'effect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
				'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
				'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
				'effect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
				'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
				'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,

			);
				continue;	
			}
/*			if( $xmlScience->tab == 7){
				if($xmlScience->alliance > $allianceLv){
					continue;
				}
			}*/else if($xmlScience->tab != 7 && $xmlScience->show_level > $level){
				continue;
			}
			
			$scienceList[] = array(
				'itemId' => $scienceId,
				'level' => $scienceLevel - 1,
				'tab' => $xmlScience->tab,
				'gold' => $xmlScience->gold,
				'mineral' => $xmlScience->mineral,
				'oil' => $xmlScience->oil,
				'food' => $xmlScience->food,
				'upgradeTime' => $upgradeTime,
				'status' => $status,
				'effect1' => $xmlScience->effect1,
				'value1' => $xmlScience->value1,
				'effect2' => $xmlScience->effect2,
				'value2' => $xmlScience->value2,
				'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
				'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
				'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
				'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,
				'arms' => $xmlScience->arms,
				'building1' => CalculateUtil::getItemIdFromXmlId($xmlScience->building1),
				'b1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building1),
				'building2' => CalculateUtil::getItemIdFromXmlId($xmlScience->building2),
				'b2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building2),
				'science1' => CalculateUtil::getItemIdFromXmlId($xmlScience->science1),
				's1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science1),
				'science2' => CalculateUtil::getItemIdFromXmlId($xmlScience->science2),
				's2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science2),
				'time' => intval($xmlScience->time / ( 1 + $para1) * (1 - $effectValue / 100)),
				'contribution' => $xmlScience->contribution,
				'allianceLv' => $xmlScience->alliance,
				'show_level' => $xmlScience->show_level,
				'remind_lv' => $xmlScience->remind_lv,
			);
		}
		$user = UserProfile::getWithUID($uid);
		$userAllianceBigScience = self::getAllianceBigScienceItems($user->league);
		if($userAllianceBigScience) {
			$scienceList = array_merge($scienceList, $userAllianceBigScience);
		}
		return $scienceList;
	}
	
	static function getAllianceBigScienceItems($userLeague){
		if(!$userLeague) {
			return array();
		}
		$result = self::getAllianceBigScience($userLeague);
		$ownSciences = $scienceList = array();
		if(is_array($result)){
			foreach ($result as $value){
				if($value['itemId']){
					$ownSciences[$value['itemId']] = $value;
				}
			}
		}
		import('service.item.ItemSpecManager');
		import('service.action.CalculateUtil');
		$xmlAllScience = ItemSpecManager::singleton('default', 'science.xml')->getGroup('science_list'); //读取xml中所有科技
		$currentTime = time();
		$currentScienceId = null;
		foreach ($xmlAllScience as $science){
			$upgradeTime = 0;
			$status = 0;
			$scienceId = CalculateUtil::getItemIdFromXmlId($science->id);
			if($scienceId == $currentScienceId || $science->tab != 8){
				continue;
			}
			$currentScienceId = $scienceId;
			if(!isset($ownSciences[$scienceId]) || !$ownSciences[$scienceId]){
				$scienceLevel = 0;
			}else{
				$scienceLevel = $ownSciences[$scienceId]['level'];
				$upgradeTime = $ownSciences[$scienceId]['upgradeTime'];
			}
			$money = $ownSciences[$scienceId]['money'];
			$xmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel + 1);
			$currXmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel);
			if(!$xmlScience){
				$scienceList[] = array(
					'itemId' => $scienceId,
					'level' => $scienceLevel,
					'tab' => $currXmlScience->tab,
					'show_level' => $currXmlScience->show_level,
					'isLimitLv' =>1,			
					'effect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
					'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
					'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
					'effect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
					'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
					'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,
				);
				continue;	
			}
			$scienceList[] = array(
				'itemId' => $scienceId,
				'level' => $scienceLevel,
				'tab' => $xmlScience->tab,
				'gold' => $xmlScience->gold,
				'mineral' => $xmlScience->mineral,
				'oil' => $xmlScience->oil,
				'food' => $xmlScience->food,
				'upgradeTime' => $upgradeTime,
				'status' => $status,
				'effect1' => $xmlScience->effect1,
				'value1' => $xmlScience->value1,
				'effect2' => $xmlScience->effect2,
				'value2' => $xmlScience->value2,
				'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
				'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
				'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
				'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,
				'arms' => $xmlScience->arms,
				'building1' => CalculateUtil::getItemIdFromXmlId($xmlScience->building1),
				'b1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building1),
				'building2' => CalculateUtil::getItemIdFromXmlId($xmlScience->building2),
				'b2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building2),
				'science1' => CalculateUtil::getItemIdFromXmlId($xmlScience->science1),
				's1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science1),
				'science2' => CalculateUtil::getItemIdFromXmlId($xmlScience->science2),
				's2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science2),
				'time' => intval($xmlScience->time),
				'contribution' => $xmlScience->contribution,
				'allianceLv' => $xmlScience->alliance,
				'show_level' => $xmlScience->show_level,
				'remind_lv' => $xmlScience->remind_lv,
				'money' => $money,
			);
		}
		return $scienceList;
	}
	
	static function getNewItems($uid, $itemUids){
		import('service.item.ItemSpecManager');
		import('service.action.CalculateUtil');
		import('service.action.BuildingClass');
		$building = new BuildingClass($uid);
		$res = $building->getBuildings(1, 1302000);
		//没有科技建筑
		if(!$res){
			$level = 1;
		}else{
			$level = $res[0]['level'];
		}
		$buildingXml = ItemSpecManager::singleton('default', 'building.xml')->getItem($level + 1302000);
		//研究院提速系数
		$para1 = $buildingXml->para1;
		//国家特效值
		import('service.action.CalculateUtil');
		$effectCountry = CalculateUtil::getGoodsStatus($uid,array(107));
		$effectValue = $effectCountry[107]['value'];
		$data = array();
		foreach($itemUids as $itemUid){
			$scienceItem = self::getWithUID($itemUid);
			$scienceId = $scienceItem->itemId;
			$scienceLevel = $scienceItem->level + 1;
			$xmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel);
			$currXmlScience = ItemSpecManager::singleton('default', 'science.xml')->getItem($scienceId + $scienceLevel -1);
			if(!$xmlScience){
					$scienceList[] = array(
					'itemId' => $scienceId,
					'level' => $scienceLevel - 1,
					'tab' => $currXmlScience->tab,
					'show_level' => $currXmlScience->show_level,
					'isLimitLv' =>1,			
					'effect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
					'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
					'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
					'effect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
					'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
					'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,
	
				);
					continue;	
			}else if($xmlScience->tab != 7 && $xmlScience->show_level > $level){
				continue;
			}
			
			$scienceList[] = array(
				'itemId' => $scienceId,
				'level' => $scienceLevel - 1,
				'tab' => $xmlScience->tab,
				'gold' => $xmlScience->gold,
				'mineral' => $xmlScience->mineral,
				'oil' => $xmlScience->oil,
				'food' => $xmlScience->food,
				'upgradeTime' => $scienceItem->upgradeTime,
				'status' => $scienceItem->status,
				'effect1' => $xmlScience->effect1,
				'value1' => $xmlScience->value1,
				'effect2' => $xmlScience->effect2,
				'value2' => $xmlScience->value2,
				'currEffect1' => isset($currXmlScience) ? $currXmlScience->effect1 : 0,
				'currValue1' => isset($currXmlScience) ? $currXmlScience->value1 : 0,
				'currEffect2' => isset($currXmlScience) ? $currXmlScience->effect2 : 0,
				'currValue2' => isset($currXmlScience) ? $currXmlScience->value2 : 0,
				'arms' => $xmlScience->arms,
				'building1' => CalculateUtil::getItemIdFromXmlId($xmlScience->building1),
				'b1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building1),
				'building2' => CalculateUtil::getItemIdFromXmlId($xmlScience->building2),
				'b2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->building2),
				'science1' => CalculateUtil::getItemIdFromXmlId($xmlScience->science1),
				's1_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science1),
				'science2' => CalculateUtil::getItemIdFromXmlId($xmlScience->science2),
				's2_lv' => CalculateUtil::getConditionLvFromXmlId($xmlScience->science2),
				'time' => intval($xmlScience->time / ( 1 + $para1) * (1 - $effectValue / 100)),
				'contribution' => $xmlScience->contribution,
				'allianceLv' => $xmlScience->alliance,
				'show_level' => $xmlScience->show_level,
				'remind_lv' => $xmlScience->remind_lv,
			);
		}
		return $scienceList;
			
	}
	
}

?>