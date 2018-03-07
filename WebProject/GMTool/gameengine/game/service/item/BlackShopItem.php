<?php
/**
 * 
 * 黑市物品属性类
 *
 */
import('persistence.dao.RActiveRecord');
class BlackShopItem extends RActiveRecord{
	protected $itemId1; //第一个道具所在行的ID		通过bmarket.xml取得对应物品信息
	protected $bought1;//第一个道具是否买过
	protected $itemId2; //第二个道具所在行的ID
	protected $bought2;//第二个道具是否买过
	protected $itemId3; //第三个道具所在行的ID
	protected $bought3;//第三个道具是否买过
	
	const TABLE = 'blackshop';
	
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