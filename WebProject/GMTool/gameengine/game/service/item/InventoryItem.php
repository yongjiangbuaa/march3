<?php
/**
 * 
 * 物品属性类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class InventoryItem extends RActiveRecord{
	protected $ownerId; //所属用户uid
	protected $level; //物品等级
	protected $count; //物品叠加数量
	protected $useGeneralId; //装备武将uid
	protected $pos; //装备在武将身上位置,左:1,右:2
	protected $gem = array();//
	protected $embed = 0;//宝石标明已使用个数
	protected $attr1 = null;
	protected $attrValue1 = null;
	protected $attrLevel1 = null;
	protected $attr2 = null;
	protected $attrValue2 = null;
	protected $attrLevel2 = null;
	protected $attr3 = null;
	protected $attrValue3 = null;
	protected $attrLevel3 = null;
	protected $statAttr = null;
	protected $statAttrValue = null;
	protected $statAttrLevel = null;
	protected $temp = '0';
	
	const TABLE = 'inventory';
	
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
		if($res)
			$res->unserializeProperty('gem');
		return $res;
	}
	
	/*
	 * 取得背包中物品的数量
	 */
	static function getCountByItemId($userUID, $itemId){
		import('service.item.ItemSpecManager');
		import('util.mysql.XMysql');
		$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($itemId);
		$mysql = XMysql::singleton()->connect();
		// 不可叠放
		if($xmlGoods->overlap == 1){
			return $mysql->exist(self::TABLE, array(
				'ownerId' => $userUID, 
				'itemId' => $itemId,
			));
		}else{
			$res = $mysql->get(self::TABLE, array(
				'ownerId' => $userUID, 
				'itemId' => $itemId,
			),null, 100);
			if(!$res){
				return 0;
			}else{
				return $res[0]['count'];
			}
		}
	}
	/**
	 * 取得当前用户所有物品
	 *
	 * @param Object $userProfile
	 */
	static function getItems($userUID){
		$data = $res = array();
		import('service.action.InventoryClass');
		$inventory = Inventory::singleton();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		
		//预消费卡
// 		if($mysql->exist(self::TABLE, array('ownerId'=>$userUID,'itemId'=>8340000)) == 0){
// 			$inventoryItem = new InventoryItem();
// 			$inventoryItem->ownerId = $userUID;
// 			$inventoryItem->level = 1;
// 			$inventoryItem->count = 1;
// 			$inventoryItem->itemId = 8340000;
// 			$inventoryItem->uid = getGUID();
// 			$inventoryItem->save();
// 		}
		$sql = "select * from " . self::TABLE . " where ownerId='{$userUID}'";
		$res = $mysql->execResult($sql, 500);
		$res = self::to($res, true);
		
		if(is_array($res)){
			foreach ($res as $goods){
				$goods->unserializeProperty('gem');
				$data[] = $inventory->getGoodsResArr($goods);
			}
		}
		return $data;
	}
	
	static function getGeneralEquipment($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where ownerId ='{$uid}' and useGeneralId != ''";
		$res = $mysql->execResult($sql, 100);
		$inventoryItems = self::to($res, true);
		foreach ($inventoryItems as &$inventoryItem){
			$inventoryItem->unserializeProperty('gem');
			$data[] = $inventoryItem;
		}
		return $data;
	}
	
	static function getNewItems($uid, $itemUids){
		$data = array();
		import('service.action.InventoryClass');
		$inventory = Inventory::singleton();
		foreach ($itemUids as $itemUid){
			$goods = self::getWithUID($itemUid);
			$data[] = $inventory->getGoodsResArr($goods);
		}
		return $data;
	}
	/**
	 * 获得用户物品中的道具
	 *
	 * @param Object $userProfile
	 */
	static function getInvertoryByItemId($userUID, $itemId){
		import('util.mysql.XMysql');
		$tablename = 'inventory';
		$where = array('ownerId' => $userUID, 'itemId' => $itemId);
		
		$result = XMysql::singleton()->get($tablename,$where);
		return $result;
	}
	
	/**
	 * 减少用户的物品
	 * @param Object
	 */
	static function decreaseItem($userUID,$itemId) {
		import('util.mysql.XMysql');
		$sql = "update inventory set count = count - 1 where ownerId = '" . $userUID . "' and itemId = '" . $itemId . "'";
		return XMysql::singleton()->execute($sql);
	}
	
	/**
	 * 删除用户的物品
	 * @param Object
	 */
	static function delItem($userUID,$itemId) {
		import('util.mysql.XMysql');
		$where = array('ownerId' => $userUID, 'itemId' => $itemId);
		return XMysql::singleton()->del('inventory', $where);
	}
	
	public static function checkPlayerItem($uid, $itemId, $count) {
		$goodsItem = self::getInvertoryByItemId($uid, $itemId);
		if(!$goodsItem) {
			import('service.action.ConstCode');
			return ConstCode::ERROR_INVALID;
		}
		if($goodsItem[0]['count'] < $count) {
			import('service.action.ConstCode');
			return ConstCode::ERROR_INVALID;
		}
		return $goodsItem[0];
	}
	
	public function save(){
		$this->serializeProperty('gem');
		parent::save();
		$this->unserializeProperty('gem');
	}
}
?>