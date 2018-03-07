<?php
/**
 * AllianceBattlePicItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class AllianceBattlePicItem extends RActiveRecord {
	protected $battlepic; //对阵图
	protected $time; //时间戳
	protected $generatetime;
	
//	public function __construct($battlepic=null, $generatetime=null) {
//		$this->battlepic = $battlepic;
//		$this->time = time();
//		$this->generatetime = $generatetime;
//	}
	
	const TABLE = 'alliancebattlepic';
	
	public static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	public static function updateBattlePic($uid, $battlePic){
		if(is_array($battlePic)) {
			$battlePic = json_encode($battlePic);
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update " . self::TABLE . " set battlepic = '{$battlePic}' where uid = '{$uid}'";
		$res = $mysql->execute($sql);
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('battlepic');
		parent::save();
		$this->unserializeProperty('battlepic');
	}
	
	public static function getBattlePicByTime($generateBattlePicTime){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, battlepic from " . self::TABLE . " where generatetime = {$generateBattlePicTime} order by time asc, uid asc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res[0];
	}
}
?>