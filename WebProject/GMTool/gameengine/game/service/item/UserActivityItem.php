<?php
/**
 * UserActivityItem.php
 * 
 * 用户的活动表
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class UserActivityItem extends RActiveRecord {
	
	protected $ownerId; //用户UID
	protected $activityId; //活动ID
	protected $obtainAward;
	protected $dailyCount; //每日允许的次数
	protected $buyCount; //购买的次数
	protected $flushTime; //上次刷新次数的时间
	
	static function getItem($uid, $activityId) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$userActData = $mysql->get('useractivity', array('ownerId' => $uid, 'activityId' => $activityId));
		$userActivityItem = self::toObject(__CLASS__, $userActData);
		if($userActivityItem === null) {
			$userActivityItem  = self::init($uid, $activityId);
		} 
		if($userActivityItem && date('Y-m-d',$userActivityItem->flushTime) != date('Y-m-d', $currTime)){
			$goldEggXml = ItemSpecManager::singleton('default','duanwu.xml')->getItem('5210');
			$userActivityItem->dailyCount = $goldEggXml->freeegg;
			$userActivityItem->flushTime = $currTime;
			$userActivityItem->save();
		}
		$userActivityItem->unserializeProperty('obtainAward');
		return $userActivityItem;
	}
	
	public static function init($uid, $activityId) {
		$userActivityItem = new UserActivityItem();
		$userActivityItem->ownerId = $uid;
		$userActivityItem->activityId = $activityId;
		$goldEggXml = ItemSpecManager::singleton('default','duanwu.xml')->getItem('5210');
		$userActivityItem->dailyCount = $goldEggXml->freeegg;
		$userActivityItem->buyCount = 0;
		$userActivityItem->flushTime = time();
		$userActivityItem->save();
		return $userActivityItem;
	}
	
	public function save() {
		$this->serializeProperty('obtainAward');
		parent::save();
		$this->unserializeProperty('obtainAward');
	}
}
?>