<?php
import('persistence.dao.RActiveRecord');
class OrangeEquipExchangeItem extends RActiveRecord {
	protected $currentEquid;	//当前正在兑换的装备
	protected $fullEquidTimes = 0;	//全装的兑换次数
	
	const TABLE = 'orangeequipexchange';
	
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
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
}
?>