<?php
/**
 * shopActivityItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class ShopActivityNumsItem extends RActiveRecord {
	protected $nums;   //已卖数量
	protected $limit;   //限额
	protected $price;   //价格
	protected $startTime;   //已卖数量
	protected $endTime;   //已卖数量
	
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
	
	static function getCurrentDiscount($itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$currentTime = time();
		$sql = "select * from shopactivitynums where `startTime` < $currentTime and `endTime` > $currentTime and itemId = $itemId";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res){
			$res = self::to($res,true);
			return $res[0];
		}else{
			return null;
		}
	}
	
	static function getShopGoodsActivityNums(){
		//缓存
		$cacheData = parent::getCacheValue('shopItems');
		if($cacheData)
			return $cacheData;		
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$currentTime = time();
		$sql = "select * from shopactivitynums where `endTime` > $currentTime";
		$res = $mysql->execResultWithoutLimit($sql);
		parent::setCacheValue('shopItems', $res);
		return $res;
	}
}
?>