<?php
/**
 * userShopReordItem
 * 
 * 用户商城物品最近购买记录
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class UserShopRecordItem extends RActiveRecord {
//	protected $id;
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $goodsList = array();//购买记录 array($itemId => time());
	
	/*
	 * 更新最近购买记录
	 */
	static public function updateRecord($uid, $itemId){
		$recordItem = self::getWithUID($uid);
		if(!$recordItem){
			$recordItem = self::init($uid);
		}
		$recordItem->goodsList[$itemId] = time();
		if(count($recordItem->goodsList) > 8){
			asort($recordItem->goodsList);
			array_shift($recordItem->goodsList);
		}
		$recordItem->save();
	}
	
	static public function getRecords($uid){
		$recordItem = self::getWithUID($uid);
		if(!$recordItem){
			$recordItem = self::init($uid);
		}
		return $recordItem->goodsList;
	}
	
	static function init($uid){
		$recordItem = new self;
		$recordItem->uid = $uid;
		$recordItem->goodsList = array();
		return $recordItem;
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
			$res->unserializeProperty('goodsList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('goodsList');
		parent::save();
		$this->unserializeProperty('goodsList');
	}
}
?>