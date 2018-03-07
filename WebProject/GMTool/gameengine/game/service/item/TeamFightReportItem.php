<?php
/**
 * 
 * 战报模型类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class TeamFightReportItem extends RActiveRecord{
	protected $create_at;   //创建时间
	protected $report;      //战报内容

	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param String $uid
	 * @return Object
	 */
	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	/**
	 * 根据主键取得战报
	 *
	 * @param String $uid
	 * @return Array
	 */
	static function getReportByUid($uid){
		$fight = self::getWithUID($uid);
		$fight->unserializeProperty('report');
		return $fight->report;
	}
	
	/**
	 * 记录新战报
	 *
	 * @param String $uid
	 * @param Integer $type
	 * @param Array $report
	 */
	static function createNewReport($report){
		$teamfightReportItem = new self;
		$teamfightReportItem->create_at = time();
		$teamfightReportItem->report = $report;
		$teamfightReportItem->serializeProperty('report');
		$teamfightReportItem->save();
		return $teamfightReportItem;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param String $uid
	 * @param Array $report
	 */
	static function updateReportInfo($uid, $report){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$mysql->put('teamfightreport', array('uid' => $uid), array(
			'create_at' => time(),
			'report' => json_encode($report),
		));
	}
	
	/*
	 * 删除战报
	 */
	static function removeReport($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$mysql->del('teamfightreport', array('uid' => $uid));
	}
}
?>