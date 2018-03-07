<?php
/**
 * 活动单人数据
 */
import('persistence.dao.RActiveRecord');
class FestivalItem extends RActiveRecord{
	
	protected $dumplingId;		//当前开启粽子的ID
	protected $dumplingFixed;		//每个粽子是否可以翻倍
	protected $dumplingTimes;		//每个粽子奖励的倍率
	protected $dumplingFreeTimes;		//每个粽子免费翻倍次数
	
	/**
	 * 数组转化为对象实例
	 *
	 * @param Array $results
	 * @param Boolean $retArr 如果只有一条记录，false返回对象，true返回数组
	 * @return Object Or Array
	 */
	static function to($results, $retArr = false){
		return self::toObject(__CLASS__, $results, $retArr);
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if(!$res) {
			$item = new self();
			$item->uid = $uid;
			$item->save();
			return $item;
		}
		return $res;
	}
}
?>