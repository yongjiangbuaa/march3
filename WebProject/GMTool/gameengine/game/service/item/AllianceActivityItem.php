<?php
/**
 * AllianceActivityItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class AllianceActivityItem extends RActiveRecord {
	protected $allianceId; //报名的用户ID
	protected $activityId;
	protected $darkLevel;
	protected $playerList;
	protected $npcInfo;
	protected $startTime;
	protected $battleCD;
	protected $battleResult;
	protected $battleRecord;
	protected $lastRound;
	protected $topFight;
	protected $repaireFlag;
	
	public static function getWithUid($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where uid = '{$uid}'";
		$res = $mysql->execResultWithoutLimit($sql);
		$allianceActItem = self::to($res, false);
		if(!$allianceActItem) {
			return null;
		}
		$allianceActItem->unserializeProperty('playerList');
		$allianceActItem->unserializeProperty('npcInfo');
		$allianceActItem->unserializeProperty('battleRecord');
		$allianceActItem->unserializeProperty('topFight');
		return $allianceActItem;
	}
	
	public static function checkStart($allianceId, $activityId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from allianceactivity where allianceId = '{$allianceId}' and startTime >= {$time} and activityId = '{$activityId}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectAllianceActItem($allianceId, $activityId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where allianceId = '{$allianceId}' and startTime >= {$time} and activityId = '{$activityId}'";
		$res = $mysql->execResultWithoutLimit($sql);
		if(!$res) {
			return null;
		}
		$allianceActItem = self::to($res, false);
		$allianceActItem->unserializeProperty('playerList');
		$allianceActItem->unserializeProperty('npcInfo');
		$allianceActItem->unserializeProperty('battleRecord');
		$allianceActItem->unserializeProperty('topFight');
		return $allianceActItem;
	}
	
	public static function selectAllianceAllRecords($allianceId, $activityId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where allianceId = '{$allianceId}' and activityId = '{$activityId}' and battleResult is not null order by startTime desc limit 2";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectAllianceBestRecord($allianceId, $activityId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where allianceId = '{$allianceId}' and activityId = '{$activityId}' and battleResult is not null order by darkLevel desc, lastRound desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectAllAllianceActItem($activityId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where startTime >= {$time} and activityId = '{$activityId}' and battleResult is null";
		$res = $mysql->execResultWithoutLimit($sql);
		if(!$res) {
			return null;
		}
		$allianceActItem = self::to($res, true);
		return $allianceActItem;
	}
	
	public static function selectPreItem($allianceId, $activityId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from allianceactivity where allianceId = '{$allianceId}' and activityId = '{$activityId}' and battleResult is not null order by startTime desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		if(!$res) {
			return null;
		}
		return self::to($res, false);
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
	
	public function save() {
		$this->serializeProperty('playerList');
		$this->serializeProperty('npcInfo');
		$this->serializeProperty('battleRecord');
		$this->serializeProperty('topFight');
		parent::save();
		$this->unserializeProperty('playerList');
		$this->unserializeProperty('npcInfo');
		$this->unserializeProperty('battleRecord');
		$this->unserializeProperty('topFight');
	}
}
?>