<?php
/**
 * 辛西娅培养活动
 */
import('persistence.dao.RActiveRecord');
class CynthiaItem extends RActiveRecord {
	protected $startTime;	//辛西娅培养活动开始时间
	protected $status = 1;		//当前正在进行的任务
	
	const TABLE = 'cynthia';
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
}
?>