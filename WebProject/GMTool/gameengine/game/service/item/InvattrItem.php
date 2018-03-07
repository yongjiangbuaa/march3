<?php
/**
 * InvattrItem
 * 
 * 精炼属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class InvattrItem extends RActiveRecord {
	protected $uid;//
	protected $ownerId;//userUid
	protected $inventoryId;//所属物品
	protected $attr1;//属性类型$
	protected $attrValue1;//属性值
	protected $attrLevel1;//属性评级
	protected $attr2;//
	protected $attrValue2;//
	protected $attrLevel2;//
	protected $attr3;//
	protected $attrValue3;//
	protected $attrLevel3;//
	
	/**
 	 * 
 	 * @param String $uid
 	 */
 	public function getItems($uid)
	{
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from invattr where ownerId = '$uid'";
		$sqlData = $mysql->execResultWithoutLimit($sql);
		$sqlData = self::to($sqlData,true);
		return $sqlData;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param String $uid
	 * @return Object
	 */
	public function getWithUID($uid){
		$friendItem = self::getOne(__CLASS__, $uid);
		return $friendItem;
	}
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
}
?>