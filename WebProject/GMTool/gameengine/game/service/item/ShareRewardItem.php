<?php
/**
 * ShareRewardItem
 * 分享领奖的数据
 */
import('persistence.dao.RActiveRecord');
class ShareRewardItem extends RActiveRecord {
	protected $ownerId;      //所属者
	protected $shareId;		//分享ID
	protected $shareParam;			//分享类型
	protected $status;			//分享是否已领奖，即是否已发出分享，0已触发1已分享
	protected $time;			//触发时间
	
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
		return self::getOne(__CLASS__,$uid);
	}
	
	static function checkRewardAble($user,$type,$shareId){
		import('service.action.DataClass');
		import('service.item.ItemSpecManager');
		$qqshare= ItemSpecManager::singleton('default','item.xml')->getItem('qqshare');
		if (StatData::$pf== 'elex337')
			return false;
		if (StatData::$pf== 'qzone')
			$shareLimit = $qqshare->k1;
		if (StatData::$pf== 'pengyou')
			$shareLimit = $qqshare->k2;
		if($shareLimit){
			import('util.mysql.XMysql');
			$timeLimit = strtotime(date('Y-m-d'));
			$sql = "select count(1) as rewardCount from sharereward where ownerId = '{$user->uid}' and time >= $timeLimit";
			$res = XMysql::singleton()->execResult($sql);
			if($res && $res[0]['rewardCount'] >= $shareLimit){
				return false;
			}
		}
		if($type == 8){
			import('util.mysql.XMysql');
			$timeLimit = strtotime(date('Y-m-d'));
			$sql = "select count(1) as rewardCount from sharereward where ownerId = '{$user->uid}' and shareId = $shareId and time >= $timeLimit";
			$res = XMysql::singleton()->execResult($sql);
			if($res && $res[0]['rewardCount'] == 0){
				return true;	
			}
			return false;
		}
		return true;
	}
}
?>