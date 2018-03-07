<?php
/**
 * NuclearItem
 * 
 * 核反应堆
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class NuclearItem extends RActiveRecord {
	protected $status;   		 //充能状态0：未充能
	protected $weekTimes;       //周领取次数
	protected $time; 			//充能（领取）时间
	
	
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
		return $res;
	}
	public function getItems($uid){
		
		$nuclearItem = self::getWithUID($uid);
		if(!$nuclearItem){
			$nuclearItem = new self;
			$nuclearItem->uid = $uid;
			$nuclearItem->status = 0;
			$nuclearItem->weekTimes = 0;
			$nuclearItem->time = time();
			$nuclearItem->save();
		}
		$data[] = self::retArr($nuclearItem);
		return $data;
	}
	
	public function retArr($nuclearItem){
		import('service.action.CalculateUtil');
		$data_xml = ItemSpecManager::singleton('default','item.xml')->getItem('Nuclear_power_plant1');
		return Array(
			'status'=>$nuclearItem->status,
			'weekTimes'=>$nuclearItem->weekTimes,
			'time'=>$nuclearItem->time,
			'dayReward'=>CalculateUtil::getInfoByRewardId($data_xml->k4),
			'weekReward'=>CalculateUtil::getInfoByRewardId($data_xml->k2),
		);
	}


	
	
}
?>