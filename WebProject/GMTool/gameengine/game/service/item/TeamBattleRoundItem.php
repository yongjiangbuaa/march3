<?php
/**
 * TeamBattleRoundItem
 * 
 * 团战回合记录
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class TeamBattleRoundItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;          //队伍UID
	protected $round;        //回合数
	protected $roundList;    //回合列表    array($reportUid, , ......)
	protected $clearFlag;

	static function init($teamUid){
		$roundItem = new self;
		$roundItem->uid = $teamUid;
		$roundItem->round = 0;
		$roundItem->roundList = array();
		$roundItem->clearFlag = 'N';
		return $roundItem;
	}
	
	static function getItem($teamUid){
		$roundItem = self::getWithUID($teamUid);
		if(!$roundItem){
			$roundItem = self::init($teamUid);
		}
		return $roundItem;
	}
	
	/*
	 * 增加回合数
	 */
	public function addRoundNums(){
		$this->round += 1;
		return $this;
	}
	
	/**
	 * 记录回合战报
	 *
	 * @param String $reportUid  战报UID
	 * @param Integer $time      战报播放时间,timestamp
	 */
	public function putReportIntoList($reportUid){
		$this->roundList[] = $reportUid;
	}
	
	public function getPlayRoundReport(){
		return end($this->roundList);
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
			$res->unserializeProperty('roundList');
		return $res;
	}
	
	static function deleteByTeamUid($teamUid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from teambattleround where uid = '{$teamUid}'";
		$mysql->execute($sql);
	}
	
	public function save(){
		$this->serializeProperty('roundList');
		parent::save();
		$this->unserializeProperty('roundList');
	}
}
?>