<?php
/**
 * AllianceBattleApplyItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class AllianceBattleApplyItem extends RActiveRecord {
	protected $uid; //报名的用户ID
	protected $alliance; //用户的联盟ID
	protected $type; //1,联盟资源站报名;2,天降奇兵报名
	protected $time; //时间戳
	
	public function __construct($uid=null, $alliance=null, $type=1) {
		$this->uid = $uid;
		$this->alliance = $alliance;
		$this->type = $type;
		$this->time = time();
	}
	
	const TABLE = 'alliancebattleapply';
	
	public static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	public static function getApplyWithTime($allianceId, $time, $type=1){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from " . self::TABLE . " where alliance = '{$allianceId}' and time >= {$time} and type={$type}";
		$res = $mysql->execResult($sql, 1);
		return $res;
	}
	
	public static function getAllApply($time, $type=1){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$endTime = $time + 3600 * 24;
		$sql = "select u.uid from alliancebattleapply aba left join alliancemem am on aba.alliance = am.AllianceId 
				left join userprofile u on am.MemberId = u.uid where u.date > {$time} and aba.time > {$time} and aba.time < {$endTime} and aba.type={$type} limit 10000";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function getAllianceOrder($time, $type=1){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select a.uid, a.name from " . self::TABLE . " aba
				left join alliance a on aba.alliance = a.uid
				where aba.time >= {$time} and aba.type={$type}
				ORDER BY a.points desc, a.level DESC, a.exp desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public function insert() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "insert into " . self::TABLE . " values('{$this->uid}', '{$this->alliance}', $this->type, $this->time)";
		$res = $mysql->execute($sql);
		return $res;
	}
}
?>