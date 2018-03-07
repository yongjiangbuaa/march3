<?php
/**
 * 
 * 战报模型类
 * @author yueyueniao
 *
 */
import('persistence.dao.RActiveRecord');
class OriginalReportItem extends RActiveRecord{
	protected $type;        //战报类型(保留属性) 1-pve;2-竞技场;3-团战
	protected $create_at;   //创建时间
	protected $backClass;	//最终结果调用的类名
	protected $report;      //战报内容
	protected $matrix;		//布阵信息
	protected $queue;		//攻击顺序
	protected $params;		//战报参数

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
		$fight->unserializeProperty('matrix');
		$fight->unserializeProperty('report');
		$fight->unserializeProperty('queue');
		$fight->unserializeProperty('params');
		return $fight;
	}
	
	/**
	 * 记录新战报
	 *
	 * @param String $uid
	 * @param Integer $type
	 * @param Array $report
	 */
	static function createNewReport($type, &$report, $backClass, $params){
		$fightReportItem = new self;
		$fightReportItem->type = $type;
		$fightReportItem->backClass = $backClass;
		$fightReportItem->create_at = time();
		$fightReportItem->matrix = $report['matrix'];
		$fightReportItem->serializeProperty('matrix');
		unset($report['matrix']);
		$fightReportItem->queue = $report['queue'];
		$fightReportItem->serializeProperty('queue');
		unset($report['queue']);
		$fightReportItem->report = $report;
		$fightReportItem->serializeProperty('report');
		$fightReportItem->params = $params;
		$fightReportItem->serializeProperty('params');
		$fightReportItem->save();
		return $fightReportItem->uid;
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
		$mysql->put('originalreport', array('uid' => $uid), array(
			'report' => json_encode($report),
		));
	}
	
	/*
	 * 删除战报
	 */
	static function removeReport($uid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$mysql->del('originalreport', array('uid' => $uid));
	}
}
?>