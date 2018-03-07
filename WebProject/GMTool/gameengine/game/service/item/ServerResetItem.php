<?php
/**
 * ServerResetItem
 * 
 * 控制全服发奖等逻辑
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class ServerResetItem extends RActiveRecord {
	protected $allianceReward;    //全服联盟排行发奖时间
	protected $releaseTime;       //开服时间
	protected $wheelWeekAward;    //轮盘抽奖系统每周的最佳奖励
	protected $wheelWeekAwardTime;
	
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
	static function getWithUID($uid = null){
		$uid = 'server';
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		$res = self::getOne(__CLASS__, $uid);
		if(!$res){
			$currTime = time();
			$res = new self();
			$res->uid = $uid;
			$res->allianceReward = $currTime;
			$res->save();
		}
		parent::setCacheValue($cachekey, $res);
		return $res;
	}
	
	/**
	 * 获取开服的天数
	 */
	static function getOpenDay () {
		$releaseTime = strtotime(date('Y-m-d', self::getWithUID() -> releaseTime));
		$day = ceil((time() - $releaseTime) / 86400);	
		return $day;
	}
	
	public function save(){
		parent::save();
		$uid = 'server';
		$cachekey = __CLASS__.$uid;
		parent::delCacheValue($cachekey);
	}
	
	static function getAttackBossActivityTime($k1, $k2, $k3) {
		$serverInfoItem = self::getWithUID();
		$releaseTime = $serverInfoItem->releaseTime;
		$attackTime = $releaseTime + $k2 * 24 * 60 * 60;
		$activityFinishTime = $attackTime + $k3 * 24 * 60 * 60;
		$data[] = $attackTime;
		$data[] = $activityFinishTime;
		$data[] = $releaseTime;
		return $data;
	}
}
?>