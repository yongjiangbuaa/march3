<?php
/**
 * AllianceBattleUserApplyItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class AllianceBattleUserApplyItem extends RActiveRecord {
	protected $uid; //报名的用户ID
	protected $time; //时间戳
	
	public function __construct($uid=null) {
		parent::__construct();
		$this->uid = $uid;
		$this->time = time();
	}
	
	const TABLE = 'alliancebattleuserapply';
	
	public static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	public static function checkApply($uid, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from " . self::TABLE . " where uid = '{$uid}' and time >= {$time}";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function getItemWithTime($time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from " . self::TABLE . " where time >= {$time}";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function getItemWithTimeAndLeague($time, $leagues){
		$leagueStr = self::appendStrFromArrForBatch($leagues);
		if(!$leagueStr) {
			return;
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select abua.uid from alliancebattleuserapply abua left join userprofile u on abua.uid = u.uid where abua.time >= {$time} and u.league in {$leagueStr}";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function appendStrFromArrForBatch($params) {
		$uidsStr = '(';
		if($params) {
			$count = count($params);
			foreach($params as $key => $par) {
				if($key == $count - 1) {
					$uidsStr .= "'" . $par . "'";
				} else {
					$uidsStr .= "'" . $par . "',";
				}
			}
			$uidsStr .= ')';
		}
		if($uidsStr == '(') {
			return false;
		}
		return $uidsStr;
	}
}
?>