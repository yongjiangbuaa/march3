<?php
/**
 * SilverMineRecordItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class SilverMineRecordItem extends RActiveRecord {
	protected $uid;
	protected $friendUid;
	protected $time;
	
	const table = 'silverminerecord';
	
	public function __construct($uid=null,$friendUid=null) {
		$this->uid = $uid;
		$this->friendUid = $friendUid;
		$this->time = time();
	}

	public static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
	public static function insertRecord($uid, $friendUid, $timeDiff) {
		$timestamp = time() + $timeDiff;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "insert into silverminerecord(uid, friendUid, time) values('{$uid}', '{$friendUid}', $timestamp)";
		return $mysql->execute($sql);
	}
	
	public static function getLatelyRecord($uid, $friendUid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from silverminerecord where uid = '{$uid}' and friendUid = '{$friendUid}' order by time desc limit 1";
		return $mysql->execResult($sql);
	}
	
	public static function getHelperList($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select up.name,time from silverminerecord smr
				left join userprofile up on smr.uid = up.uid
				where friendUid = '{$uid}' order by time desc limit 10";
		return $mysql->execResult($sql,10);
	}
	
	public static function deleteRecord($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$selectSql = "select * from silverminerecord where uid = '{$uid}' order by time desc limit 10";
		$selectRes = $mysql->execResultWithoutLimit($selectSql);
		if($selectRes) {
			$count = count($selectRes);
			if($count == 10) {
				$deleteTime = $selectRes[$count-1]['time'];
				$deleteSql = "delete from silverminerecord where uid = '{$uid}' and time < {$deleteTime}";
				$mysql->execute($deleteSql);
			}
		}
	}
	
	public static function getFriendExplorerStatus($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select fr.playerUid as friendId, max(smr.time) as time from friend fr 
				right join silverminerecord smr on fr.playerUid = smr.friendUid 
				and fr.ownerId = smr.uid
				where ownerId = '{$uid}' group by friendId";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	public static function getLeftJoinFriendExplorerStatus($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select fr.playerUid as friendId, max(smr.time) as time from friend fr 
				left join silverminerecord smr on fr.playerUid = smr.friendUid 
				and fr.ownerId = smr.uid
				where ownerId = '{$uid}' group by friendId";
		return $mysql->execResultWithoutLimit($sql);
	}
	
}
?>