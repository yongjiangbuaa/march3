<?php
/**
 * QuestRecordItem
 * 
 * 已完成剧情任务
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class QuestRecordItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;          //用户uid
	protected $questList = array();    //任务列表 array($questId1, $questId2, ....)

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
	
	static function putRecord($uid, $questId){
		$questRecordItem = self::getRecords($uid);
		$questRecordItem->questList[$questId]++;
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
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if (is_object($cacheVal))
			return $cacheVal;
		
		$res = self::getOne(__CLASS__, $uid);
		if($res){
			$res->unserializeProperty('questList');
			$cacheVal = $res;
			parent::setCacheValue($cachekey, $cacheVal);
		}
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('questList');
		parent::save();
		$this->unserializeProperty('questList');
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
}	
?>