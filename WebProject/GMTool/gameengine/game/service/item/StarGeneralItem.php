<?php
/**
 * StarGeneralItem
 * 
 * 名将兑换属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class StarGeneralItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;
	protected $exchList = array();

 	
	/*
	 * 读取Items
	 */
 	
  	function getItems($uid){
 		
 		$GeneralList = array();
		import('service.item.ItemSpecManager');
		import('service.action.GeneralClass');
		$xmlStarConfig = ItemSpecManager::singleton('default', 'item.xml')->getGroup('starGeneral');
 		$starGeneralItem = self::getWithUID($uid);
		if(!$starGeneralItem){
			$starGeneralItem = self::init($uid);
		}
		
		foreach($xmlStarConfig as $starGeneral){
	
			if(in_array($starGeneral->id, $starGeneralItem->exchList))
				$starGeneral->exch = 1;		
			else 
				$starGeneral->exch = 0;	
			//根据将军Id生成将军，返回前台
			
			$generals = General::singleton();
//			p($starGeneral->gen_id);
			$generalHire = $generals->createOneGeneral($starGeneral->gen_id);
			$starGeneral->general = $generalHire;
			
			$GeneralList[] = $starGeneral;
		}		
		
 		return $GeneralList;
 	}
 	
 	//初始化数据库
	function init($uid){
		$starGeneralItem = new self;
		$starGeneralItem->uid = $uid;
//		$arenaRankItem->flush();
		$starGeneralItem->save();
		return $starGeneralItem;
	}
	
	//返回名将兑换列表
	static function getUserExchList($uid){
		
		$starGeneralItem = self::getWithUID($uid);
//		if(!$starGeneralItem){
//			$starGeneralItem = self::init();
//		}

		return $starGeneralItem->exchList;
	}
 	
	/*
	 * 存储已兑换将军
	 */
 	
 	static function addExchGeneral($exchid,$uid){
 		$starGeneralItem = self::getWithUID($uid);
 		$starGeneralItem->exchList[] = $exchid;
 		$starGeneralItem->save();
 			
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
			$res->unserializeProperty('exchList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('exchList');
		parent::save();
		$this->unserializeProperty('exchList');
	}
 
	
}

?>