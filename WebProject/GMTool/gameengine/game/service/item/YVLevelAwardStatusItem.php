<?php
/**
 * YVLevelAwardStatusItem
 * 
 * 腾讯黄钻用户升级礼包的领取状态
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class YVLevelAwardStatusItem extends RActiveRecord {
//	protected $id;
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;//用户uid
	protected $level;//用户等级
	protected $status; //记录领取的黄砖等级奖励：{1：{1}，5：{5}}
	
	const TABLE = 'yvlevelawardstatus';
	
	function  __construct($uid,$level,$status=0) {
		parent::__construct();
		$this->uid = $uid;
		$this->level = $level;
		$this->status = $status;
	}
	/**
     +----------------------------------------------------------
     * 获得items
     +----------------------------------------------------------
     * @method getItems
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	
	static function getAwardLowerLevel($userUID,$userLevel)
	{
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, level from " . self::TABLE . " where uid = '{$userUID}' and level = '{$userLevel}' limit 1";
		return $mysql->execResult($sql, 1);
	}
	
	//获取领奖记录
	public function getRecordItems($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, level from " . self::TABLE . " where uid = '{$uid}' order  by level asc";
		$res = $mysql->execResult($sql, 20);	
		$data = Array();
		if($res){
			$data = $res;
		}
		return $data;	
	}
	
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid,$level){
		$res = self::getOne(__CLASS__, $uid);
		if(!$res){
			$YVLevelItem = new self();
			$YVLevelItem->uid = $uid;
			$YVLevelItem->level = $level;
			$YVLevelItem->status = 0;
			$YVLevelItem->save();
			$res = self::getOne(__CLASS__, $uid);
		}
		return $res;
	}
	
	/**
	 * 创建一条记录
	 */
	static function addOneRecord($uid,$level){
		$YVLevelItem = new self($uid,$level);
		$YVLevelItem->uid = $uid;
		$YVLevelItem->level = $level;
		$YVLevelItem->status = 0;
		$YVLevelItem->save();
		return $YVLevelItem;
	}
	
	
	public function update(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update " . self::TABLE . " set status = {$this->status} where uid = '{$this->uid}' and level = {$this->level}";
		return $mysql->execute($sql);
	}
	
	public function insert() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "insert into  " . self::TABLE . "(uid, level, status) values('{$this->uid}', {$this->level}, {$this->status})";
		return $mysql->execute($sql);
	}
}
?>