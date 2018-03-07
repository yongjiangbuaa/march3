<?php
/**
 * 
 * 编队属性类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class FormationItem extends RActiveRecord{
	protected $ownerId; //拥有者uid
	protected $type; //编队类型: 1:陆战;2:海战;3:空战;4:自由
	protected $generalList; //上阵武将位置列表,类型为数组，序列化后存入 array('pos1' => array('uid' => $generalUid, 'arms' => $armsId),, ...)
	protected $isDefault; //1:当前默认阵法
	
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
	
	//取得所有编队
	static function getAllFormation($uid){
		//查询玩家的所有科技
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$sql = "select * from formation where ownerId='{$uid}'";
		return $mysql->execResult($sql, 100);
	}
	
	/*
	 * 根据itemId查找编队
	 */
	static function getFormationByItemId($uid, $itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('formation', array(
			'ownerId' => $uid, 
			'itemId' => $itemId,
		),null, 1);
		return self::to($res);
	}
	
	/*
	 * 根据type查找编队
	 */
	static function getFormationByType($uid, $type){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('formation', array(
			'ownerId' => $uid, 
			'type' => $type,
		),null, 1);
		return self::to($res);
	}
	
	//设置阵法位置
	public function setPos($pos, $general = null, $arms = null, $arms_flag = true){
		if(!$general || (!$arms && $arms_flag)){
			$this->generalList['pos' . $pos] = '';
		}else{
			$this->generalList['pos' . $pos] = array('uid' => $general);
		}
	}
	
	//取得阵法位置
	public function getPos($pos){
		return $this->generalList['pos' . $pos];
	}
	//初始化三个编队
	static function InitThreeFormation($uid){
		self::InitFormation($uid, '130000',1);
		self::InitFormation($uid, '130001');
		self::InitFormation($uid, '130002');
		
	}
	//初始化编队
	static function InitFormation($uid, $itemId, $isDefault = 0, $generalList = null){
		$xmlFormation = ItemSpecManager::singleton('default','formation.xml')->getItem($itemId);
		$formationItem = new self;
		$formationItem->itemId = $itemId;
		$formationItem->isDefault = $isDefault;
		$formationItem->type = $xmlFormation->type;
		$formationItem->ownerId = $uid;
		if(!$generalList){
			$generalList = array();
			for($i = 1; $i < 10; $i++){
				$generalList['pos' . $i] = null;
				if($i == 5)
				{
					import('service.item.GeneralItem');
					$generalItem = GeneralItem::getGeneral($uid);
					$generalList['pos' . $i] = array('uid'=>$generalItem->uid);
				}
			}
		}
		$formationItem->generalList = $generalList;
		$formationItem->serializeProperty('generalList');
		$formationItem->save();
		return $formationItem;
	}
	
	/*
	 * 取得默认编队
	 */
	static function getDefaultFormation($uid){
		//uid为user的uid
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('formation', array(
			'ownerId' => $uid,
// 			'isDefault' => 1,
		),null, 1, 'order by isDefault desc,itemId asc');//防止没有默认阵法，战斗出错
		$result = self::to($res);
		parent::setCacheValue($cachekey, $result);
		return $result;
	}
	
	public function save(){
		parent::save();
		//默认阵法
		if ($this->isDefault == 1)
		{
			$cachekey = __CLASS__.$this->ownerId;
			parent::delCacheValue($cachekey);
		}
	}
	/**
	 * 取得玩家所有队
	 *
	 * @param String $user
	 * @return Array
	 */
	static function getItems($uid){
		$result = self::getAllFormation($uid);
		if(!$result){
			$scienceItem = self::InitFormation($uid, 130000, 1);
			$result[] = array(
				'itemId' => $scienceItem->itemId,
				'ownerId' => $scienceItem->ownerId,
				'type' => $scienceItem->type,
				'generalList' => $scienceItem->generalList,
				'isDefault' => $scienceItem->isDefault,
			);
		}
		$ownFormation = array();
		if(is_array($result) && !empty($result)){
			foreach ($result as $value){
				$ownFormation[$value['itemId']] = $value;
			}
		}
		$xmlFormation = ItemSpecManager::singleton('default', 'formation.xml')->getGroup('formation_list');
		$data = array();
		foreach ($xmlFormation as $formation){
			$generalList = array();
			$status = 0;
			$isDefault = 0;
			$uid = null;
			if($ownFormation[$formation->id]['generalList'] != null){
				$generalList = json_decode($ownFormation[$formation->id]['generalList'],true);
			}else{
				$generalList = array();
				for($i = 1; $i < 10; $i++){
					$generalList['pos' . $i] = null;
				}
			}
			if($ownFormation[$formation->id]){
				$status = 1;
				$isDefault = $ownFormation[$formation->id]['isDefault'];
				$uid = $ownFormation[$formation->id]['uid'];
			}
			$data[] = self::resArr($formation,$uid, $formation->id, $status, $generalList, $isDefault);
		}
		return $data;
	}
	
	/*
	 * 取得新增编队信息
	 */
	static function getNewItems($uid, $itemUids){
		$data = array();
		foreach ($itemUids as $itemUid){
			$formationItem = self::getWithUID($itemUid);
			$formationItem->fillXMLProperty('formation.xml');
			$formationItem->unserializeProperty('generalList');
			$data[] = self::resArr($formationItem,$formationItem->uid, $formationItem->itemId, 1, $formationItem->generalList, $formationItem->isDefault);
		}
		return $data;
	}
	static function getRewardGeneralList($uid) {
		$formation = self::getDefaultFormation($uid);
		$generalListItem = $formation->unserializeProperty('generalList');
		$generalList = array();
		if(!$generalListItem) {
			return $generalList;
		}
		foreach($generalListItem as $general) {
			if($general) {
				$generalList[] = $general['uid'];
			}
		}
		return $generalList;
	}
	
	/*
	 * 返回数据结构
	 */
	static function resArr($formation, $uid, $itemId, $status, $generalList, $isDefault){
		return array(
			'uid' => $uid,
			'itemId' => $itemId,
			'status' => $status,
			'generalList' => $generalList,
			'isDefault' => $isDefault,
			'type' => $formation->type,
			'player_lv' => $formation->player_lv,
			'task_id' => $formation->task_id,
		);
	}
}
?>