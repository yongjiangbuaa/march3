<?php
/**
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class RewardRecordItem extends RActiveRecord {
	protected $uid;      //拥有者uid
	protected $rewardList = array();    //礼包UID array($uid1, $uid2, ....)	
	
	static function init($uid){
		$questRecordItem = new self;
		$questRecordItem->uid = $uid;
		return $questRecordItem;
	}
	
	static function getRecords($uid){
		$questRecordItem = self::getWithUID($uid);
		if(!$questRecordItem){
			$questRecordItem = self::init($uid);
		}
		return $questRecordItem;
	}
	
	static function putRecord($uid, $rewardUid){
		$questRecordItem = self::getRecords($uid);
		$questRecordItem->rewardList[] = $rewardUid;
		$questRecordItem->save();
		return true;
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
			$res->unserializeProperty('rewardList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('rewardList');
		parent::save();
		$this->unserializeProperty('rewardList');
	}
	
}
?>