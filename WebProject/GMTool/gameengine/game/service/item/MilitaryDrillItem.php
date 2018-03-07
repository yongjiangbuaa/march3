<?php
/**
 * MilitaryDrillItem
 * 
 * 联合军演
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class MilitaryDrillItem extends RActiveRecord {
	
	protected $uid;//用户uid
	protected $scene; //当前所在scene
	protected $unlockGeneralId; //已经解锁到哪个将军
	protected $generalList; //所在scene的将军列表状态 array('general_id' => array('status' => '0/1','reset_times' => '1/0'),.....)
	
	const sceneCount = 5;
	
	public function __construct($uid=null, $scene=null,$unlockGeneralId=null,$generalList=null) {
		parent::__construct();
		$this->uid = $uid;
		$this->scene = $scene;
		$this->unlockGeneralId = $unlockGeneralId;
		$this->generalList = $generalList;
	}
	
	public function setGeneralList($generalList) {
		$this->generalList = $generalList;
	}
	
	public function getGeneralList() {
		return $this->generalList;
	}

	public function getItems($uid){
		$militaryDrillItem = $this->getUnlockScene($uid);
		if(!$militaryDrillItem) {
			$user = UserProfile::getWithUID($uid);
			$data = $this->getSpecSceneGenerals(0,$user->level,true);
			$data[0]['lastScene'] = $this->getLastScene();
			return $data;
		} else {
			//20130702返回所有已解锁的将军列表
			$lock = $this->scene;
			$lastScene = $this->getLastScene();
			for ($i = 0;$i <= $lock;$i++){
				$militaryDrillItem = $this->getSpecSceneFromDb($uid, $i);
				if(!$militaryDrillItem) {
					continue;
				}
				$temp = $this->encapData($uid);
				$data[$i] = $temp[0];
				$data[$i]['lastScene'] = $lastScene;
				if($i<$lock){
					$data[$i]['isLock'] = true;
				}
			}
			$this->checkFirstScene($uid);
			return $data;
		}
	}
	
	private function checkFirstScene($uid) {
		$firstScene = 0;
		import('util.mysql.XMysql');
		$sql = "select * from militarydrill where uid = '{$uid}' and scene = {$firstScene}";
		$zeroScene = XMysql::singleton()->execResultWithoutLimit($sql);
		if($zeroScene) {
			return;
		} else {
			$userLevel = UserProfile::getWithUID($uid)->level;
			import('service.item.ItemSpecManager');
			$xmlDrill = ItemSpecManager::singleton('default', 'drill.xml')->getGroup('drill');
			$i = 0;
			$data[$i]['itemId'] = $firstScene;
			foreach($xmlDrill as $drill) {
				if($drill->scene == $firstScene) {
					$sceneGeneral = $this->resNpcList($drill, $userLevel, false);
					$data[$i]['generalList'][] = $sceneGeneral;
					if($drill->order == self::sceneCount) {
						$data[$i]['unlockGeneralId'] = $drill->id;
					}
				}
			}
			$this->saveDefault($uid, $data, false);
		}
	}
	
	private function encapData($uid) {
		$i = 0;
		$data[$i]['itemId'] = $this->scene;
		$data[$i]['unlockGeneralId'] = $this->unlockGeneralId;
		$data[$i]['generalList'] = $this->generalList;
		
		$generalList = $this->generalList;
		import('service.item.ItemSpecManager');
		$isChange = false;
		foreach($generalList as $key => $general) {
			$generalItem = ItemSpecManager::singleton('default', 'drill.xml')->getItem($general['id']);
			$maxResetTimes = $generalItem->exdo_times;
			$xmlBattle = ItemSpecManager::singleton('default', 'battle.xml')->getItem($generalItem->battle);
			if($xmlBattle->arm_type != $general['army_type']) {
				$general['army_type'] = $xmlBattle->arm_type;
				$generalList[$key] = $general;
				$isChange = true;
			}
			if($general['max_reset_times'] != $maxResetTimes) {
				$general['max_reset_times'] = $maxResetTimes;
				$generalList[$key] = $general;
				$isChange = true;
			}
		}
		if($isChange) {
			$data[$i]['generalList'] = $generalList;
			$this->saveDefault($uid, $data, true);
		}
		import('service.item.DrillFightReportItem');
// 		foreach($generalList as $key => $general) {
// 			$generalBattleReport = DrillFightReportItem::getGeneralReport($general['id']);
// 			$generalList[$key]['battle_report'] = $generalBattleReport;
// 		}
		//批量读取战报
		$generalId = array();
		foreach($generalList as $key => $general) {
			$generalId[] = $general['id'];
		}
		$generalBattleReport = DrillFightReportItem::getGeneralReportByGeneralIds($generalId);
		foreach($generalList as $key => $general) {
			$generalList[$key]['battle_report'] = $generalBattleReport[$general['id']];
		}
		$data[$i]['generalList'] = $generalList;
		return $data;
	}
	
	public function saveDefault($uid, $data, $isSaved) {
		$this->setSaved($isSaved);
		$i = 0;
		$this->uid = $uid;
		$this->scene = $data[$i]['itemId'];
		$this->unlockGeneralId = $data[$i]['unlockGeneralId'];
		$this->generalList = $data[$i]['generalList'];
		if($isSaved) {
			$this->serializeProperty('generalList');
			import('util.mysql.XMysql');
			$sql = "update militarydrill set unlockGeneralId = '{$this->unlockGeneralId}', generalList = '{$this->generalList}'" .
				" where uid = '{$this->uid}' and scene = '{$this->scene}'";
			return XMysql::singleton()->execute($sql);
		}
		$this->save();
	}
	
	public function getNewItems($uid, $sceneID) {
		$min = $this->getMinScene();
		$max = $this->getLastScene();
		if($sceneID > $max) {
			return 'last';
		}
		if($sceneID < $min) {
			return 'first';
		}
		$militaryDrillItem = $this->getSpecSceneFromDb($uid, $sceneID);
		if(!$militaryDrillItem) {
			$user = UserProfile::getWithUID($uid);
			$data = $this->getSpecSceneGenerals($sceneID,$user->level, true);
			return $data;
		} else {
			return $this->encapData($uid);
		}
	}
	
	public function getLastScene() {
		import('service.item.ItemSpecManager');
		$xmlDrill = ItemSpecManager::singleton('default', 'drill.xml')->getGroup('drill');
		$maxScene = null;
		foreach($xmlDrill as $drill) {
			$scene = $drill->scene;
			if(is_null($maxScene)) {
				$maxScene = $scene;
				continue;
			}
			if($scene > $maxScene) {
				$maxScene = $scene;
			}
		}
		return $maxScene;
	}
	
	private function getMinScene() {
		import('service.item.ItemSpecManager');
		$xmlDrill = ItemSpecManager::singleton('default', 'drill.xml')->getGroup('drill');
		$minScene = null;
		foreach($xmlDrill as $drill) {
			$scene = $drill->scene;
			if(is_null($minScene)) {
				$minScene = $scene;
				continue;
			}
			if($minScene > $scene) {
				$minScene = $scene;
			}
		}
		return $minScene;
	}
	
	public function getSpecSceneGenerals($sceneId,$userLevel, $showReport) {
		import('service.item.ItemSpecManager');
		$xmlDrill = ItemSpecManager::singleton('default', 'drill.xml')->getGroup('drill');
		$i = 0;
		$data[$i]['itemId'] = $sceneId;
		foreach($xmlDrill as $drill) {
			if($drill->scene == $sceneId) {
				$data[$i]['generalList'][] = $this->resNpcList($drill, $userLevel, $showReport);
				if($drill->order == '1') {
					$data[$i]['unlockGeneralId'] = $drill->id;
				}
			}
		}
		return $data;
	}
	
	private function resNpcList($drill, $userLevel, $showReport){
		import('service.action.CalculateUtil');
		$xmlBattle = ItemSpecManager::singleton('default', 'battle.xml')->getItem($drill->battle);
		$armyId = CalculateUtil::getArmsOrRewardForFight($userLevel, $xmlBattle);
		import('service.action.LoadXMLUtil');
		$xmlArmy = LoadXMLUtil::loadArmy($armyId);
		//$xmlArmy = ItemSpecManager::singleton('default','army.xml')->getItem($armyId);
		import('service.action.FormationClass');
		$matrixItem = Formation::singleton()->getFormation($armyId,2);
		for ($i = 1; $i < 4; $i++)
		{
			for ($j = 1; $j < 4; $j++)
			{
				$pos = $i * 3 - 3 + $j;
				if ($matrixItem->generalList['pos'.$pos] != null)
				{
					$matrixArms[$pos]['arms'] = $matrixItem->generalList['pos'.$pos]['arms'];
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
		$result = array(
			'id' => $drill->id,
			'matrixArms'=>$matrixArms,
			'army_type' => $xmlBattle->arm_type,
			'battle_count' => 0,
			'already_reset_times' => 0,
			'max_reset_times' => $drill->exdo_times
		);
		if($showReport) {
			import('service.item.DrillFightReportItem');
			$generalBattleReport = DrillFightReportItem::getGeneralReport($drill->id);
			$result['battle_report'] = $generalBattleReport;
		}
		return $result;
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if($res)
			$res->unserializeProperty('generalList');
		return $res;
	}
	
	private function getUnlockScene($uid) {
		import('util.mysql.XMysql');
		$sql = "select * from militarydrill where uid = '{$uid}' ORDER BY scene desc limit 1";
		$maxScene = XMysql::singleton()->execResultWithoutLimit($sql);
		if($maxScene) {
			$this->scene = $maxScene[0]['scene'];
			$this->unlockGeneralId = $maxScene[0]['unlockGeneralId'];
			$this->generalList = $maxScene[0]['generalList'];
			$this->unserializeProperty('generalList');
			return true;
		} else {
			return false;
		}
	}
	
	public function getMaxScene($uid) {
		import('util.mysql.XMysql');
		$sql = "select * from militarydrill where uid = '{$uid}' ORDER BY scene desc limit 1";
		$maxScene = XMysql::singleton()->execResultWithoutLimit($sql);
		if($maxScene) {
			return $maxScene[0];
		} else {
			return false;
		}
	}
	
	public function getSpecSceneFromDb($uid, $sceneId) {
		import('util.mysql.XMysql');
		$sql = "select * from militarydrill where uid = '{$uid}' and scene = '{$sceneId}'";
		$SpecScene = XMysql::singleton()->execResultWithoutLimit($sql);
		if($SpecScene) {
			$this->scene = $SpecScene[0]['scene'];
			$this->unlockGeneralId = $SpecScene[0]['unlockGeneralId'];
			$this->generalList = $SpecScene[0]['generalList'];
			$this->unserializeProperty('generalList');
			return $this;
		} else {
			return false;
		}
	}
	
	public static function getAllSceneFromDb($uid) {
		import('util.mysql.XMysql');
		$sql = "select * from militarydrill where uid = '{$uid}'";
		return XMysql::singleton()->execResultWithoutLimit($sql);
	}
	
	public function update() {
		$this->serializeProperty('generalList');
		import('util.mysql.XMysql');
		$sql = "update militarydrill set unlockGeneralId = '{$this->unlockGeneralId}', generalList = '{$this->generalList}'" .
			" where uid = '{$this->uid}' and scene = '{$this->scene}'";
		return XMysql::singleton()->execute($sql);
	}

	public function save(){
		$this->serializeProperty('generalList');
		parent::save();
		$this->unserializeProperty('generalList');
	}
}
?>