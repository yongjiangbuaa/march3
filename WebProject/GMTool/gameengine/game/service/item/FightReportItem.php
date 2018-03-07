<?php
/**
 * 
 * 战报模型类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class FightReportItem extends RActiveRecord{
	protected $type;        //战报类型(保留属性) 1-pve;2-竞技场;3-团战;4-世界
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
	static function getFightReportByUid($uid){
		$fight = self::getWithUID($uid);
		$fight->unserializeProperty('report');
		return $fight->report;
	}
	
	/**
	 * 根据主键取得战报
	 *
	 * @param String $uid
	 * @return Array
	 */
	static function getFightReportItemByUid($uid){
		$fight = self::getWithUID($uid);
		if($fight)
			$fight->unserializeProperty('report');
		return $fight;
	}
	
	/**
	 * 记录新战报
	 *
	 * @param String $uid
	 * @param Integer $type
	 * @param Array $report
	 */
	static function createNewReport($type, $report){
		$fightReportItem = new self;
		$fightReportItem->type = $type;
		$fightReportItem->create_at = time();
		$fightReportItem->report = $report;
		$fightReportItem->serializeProperty('report');
		$fightReportItem->save();
		return $fightReportItem;
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
		$mysql->put('fightreport', array('uid' => $uid), array(
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
		$mysql->del('fightreport', array('uid' => $uid));
	}
}
?>