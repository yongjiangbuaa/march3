<?php
/**
 * GeneralRankItem
 * 
 * 军阶列表
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class GeneralRankItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;				//用户uid
	protected $rankList = array();//军阶列表 1=>generalUid 3=generalUid 4=>generalUid
	public function getItems($uid)
	{
		$generalRankItem = self::getWithUID($uid);
		if(!isset($generalRankItem))
			$generalRankItem = $this->init($uid);
		import('service.item.ItemSpecManager');
		$groupInfo = ItemSpecManager::singleton('default','generalRank.xml')->getGroup('generalRank');

		$rankList = $generalRankItem->rankList;
		$data = array();
		foreach ($groupInfo as $id=>$value)
		{
			$data[$id] = array('rankList'=>$rankList[$id],'rankInfo'=>$groupInfo->$id,'itemId'=>$id);
		}
		return $data;
	}
	
	/**
	 * 初始化
	 * @param unknown_type $userUid
	 */
	static function init($userUid){
		$generalRankItem = new self;
		$generalRankItem->uid = $userUid;
		$generalRankItem->save();
		return $generalRankItem;
	}
	
	/*
	 * 取得用户军阶武将数目
	 */
	static function getGeneralRankNums($uid){
		$generalRankItem = self::getWithUID($uid);
		return count($generalRankItem->rankList);
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
			$res->unserializeProperty('rankList');
		return $res;
	}

	public function save(){
		$this->serializeProperty('rankList');
		parent::save();
		$this->unserializeProperty('rankList');
	}
}
?>