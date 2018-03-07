<?php
/**
 * 
 * 开服小活动
 * @author Administrator
 *
 */
import('persistence.dao.RActiveRecord');
class OpenActivityItem extends RActiveRecord{
	protected $ownerId;     //玩家uid
	protected $type;		//活动类型 1-招募 2-军令 3-金币 
	protected $status;		//待领取状态1-领取 2-未领取
	protected $receivetime; //奖励领取时间
	
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
	
	/**
	 * 取得 玩家ownerId 中 活动类型是 typeId 的所有活动领取状况
	 * 返回数组 key 为 活动kid value 为领取状态
	 * 或null
	 */
	static function getByType ($ownerId, $typeId) {
		//查询数据库中当前用户所有军令任务
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$curentTime = strtotime(date('Y-m-d'));
		if (1 == $typeId)
			$sql = "select itemId, status from openactivity where ownerId = '{$ownerId}' and type = {$typeId}";
		else 
			$sql = "select itemId, status from openactivity where ownerId = '{$ownerId}' and type = {$typeId} and receivetime > {$curentTime}";
		$resarray = $mysql->execResult($sql,100);

		//按照格式$openItem['itemId'] = 1保存
		if ($resarray) {
			for ($i = 0; $i < count ($resarray); $i++) {
				$openItem[$resarray[$i]['itemId']] = $resarray[$i]['status'];
			}
		}
		else {
			$openItem = null;
		}
		
		return $openItem;

	}
	
	/**
	 * 取得 玩家ownerId 中 活动id是 itemId 的活动领取状况
	 * 返回对象
	 * 或null
	 */
	static function getByItemId ($ownerId, $itemId, $typeId) {
		//查看数据库判断是否领取过
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$curentTime = strtotime(date('Y-m-d'));
		if (1 == $typeId)
			$sql = "select * from openactivity where ownerId = '{$ownerId}' and itemId = '{$itemId}'";
		else 
			$sql = "select * from openactivity where ownerId = '{$ownerId}' and itemId = '{$itemId}' and receivetime > {$curentTime}";
		$res = $mysql->execResult($sql);
		$openActivityItem = self::to($res, false);
		return $openActivityItem;
	}
}
?>