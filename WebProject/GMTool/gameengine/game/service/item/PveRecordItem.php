<?php
/**
 * 
 * 副本记录模型类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class PveRecordItem extends RActiveRecord{
	protected $ownerId;     //玩家uid
	protected $name;		//玩家昵称
	protected $level;		//玩家等级
	protected $create_at;	//创建时间
	protected $reportUid;   //战报uid
	
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
	 * 根据itemId查询副本记录
	 *
	 * @param String $itemId
	 */
	static function getRecordsByItemId($itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('pverecord', array( 
			'itemId' => $itemId,
		),null, 10);
		if(is_array($res) && $res){
			return $res;
		}else{
			return array();
		}
	}
	
	static function createNewRecord($user, $itemId, $reportUid){
		$recordItem = new self;
		$recordItem->itemId = $itemId;
		$recordItem->ownerId = $user->uid;
		$recordItem->name = $user->name;
		$recordItem->level = $user->level;
		$recordItem->create_at = time();
		$recordItem->reportUid = $reportUid;
		$recordItem->save();
		return $recordItem;
	}
	
	/**
	 * 根据itemId取得最旧记录
	 *
	 * @param String $itemId
	 */
	static function getOldestRecordByItemId($itemId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from pverecord where itemId='{$itemId}' order by create_at limit 1";
		$res = $mysql->execResult($sql);
		return $res;
	}
}
?>