<?php
/**
 * 激活码领取批次历史
 */
import('persistence.dao.RActiveRecord');
class CodeHistoryItem extends RActiveRecord {
 	protected $uid = null;
 	protected $history;		//批次历史

 	/*
 	 * 根据主键取得记录对象实例
 	 *
 	 * @param String $uid
 	 * @return Object
 	 */
 	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if(!$res)
			$res = self::initCodeHistoryItem($uid);
		return $res;
 	}
 	
 	public static function initCodeHistoryItem($uid)
 	{
 		$codeHistoryItem = new CodeHistoryItem();
 		$codeHistoryItem->uid = $uid;
 		$codeHistoryItem->history = NULL;
 		$codeHistoryItem->save();
 		
 		return $codeHistoryItem;
 	}
}
?>