<?php
/**
 * UserAllianceWelfareItem
 * 联盟福利属性
 */
import('persistence.dao.RActiveRecord');
class UserAllianceWelfareItem extends RActiveRecord {
	protected $uid;
	protected $welfareCount; //联盟福利今日领取的次数
	protected $welfareTime; //联盟福利领取cd
	protected $isAcceptTodayReward; //今日联盟福利是否已经领取
	
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
		return self::getOne(__CLASS__, $uid);
	}
	
	static function init($uid) {
		$userWel = new UserAllianceWelfareItem();
		$userWel->uid = $uid;
		$userWel->welfareCount = 0;
		$userWel->welfareTime = 0;
		$userWel->save();
		return $userWel;
	}
}
?>