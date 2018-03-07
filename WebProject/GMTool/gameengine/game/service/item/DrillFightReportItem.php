<?php
/**
 * MilitaryDrillItem
 * 
 * 联合军演
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class DrillFightReportItem extends RActiveRecord {
	
	protected $generalId; //当前将军
	protected $report;//统计类型:array('isFirst'=>0/1,'minFight'=>name)
	
	public function __construct($generalId,$report) {
		parent::__construct();
		$this->generalId = $generalId;
		$this->report = $report;
	}
	
	static function getGeneralReport($generalId) {
		import('util.mysql.XMysql');
		$sql = "select report from drillfightreport where generalId = '{$generalId}'";
		$drillReport = XMysql::singleton()->execResult($sql);
		if($drillReport) {
			return (array)json_decode($drillReport[0]['report'], true);
		} else {
			return false;
		}
	}
	
	static function getGeneralReportByGeneralIds($generalIds) {
		import('util.mysql.XMysql');
		$generalId = implode("','", $generalIds);
		$sql = "select generalId,report from drillfightreport where generalId in ('$generalId')";
		$drillReports = XMysql::singleton()->execResultWithoutLimit($sql);
		$data = array();
		foreach ($drillReports as $drillReport){
			$data[$drillReport['generalId']] = json_decode($drillReport['report'], true);
		}
		return $data;
	} 
	
	public function save(){
		$this->serializeProperty('report');
		parent::save();
		$this->unserializeProperty('report');
	}
}
?>