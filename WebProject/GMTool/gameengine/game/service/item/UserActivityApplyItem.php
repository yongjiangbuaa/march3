<?php
/**
 * UserActivityApplyItem
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class UserActivityApplyItem extends RActiveRecord {
	protected $ownerId; //报名的用户ID
	protected $activityId;
	protected $applyTime;
	protected $fightPower;
	protected $maxForces;
	protected $winCount;
	protected $recoverCount;
	protected $reward;
	protected $acceptRewardFlag;
	protected $addForces;
	protected $returnForcesFlag;
	protected $healthDegree;
	protected $allianceActId;
	
	public static function getWithUid($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from useractivityapply where uid = '{$uid}'";
		$res = $mysql->execResultWithoutLimit($sql);
		$userApplyItem = self::to($res, false);
		if(!$userApplyItem) {
			return null;
		}
		$userApplyItem->unserializeProperty('reward');
		return $userApplyItem;
	}
	
	public static function checkApply($uid, $activityId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid from useractivityapply where ownerId = '{$uid}' and applyTime >= {$time} and activityId = '{$activityId}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectUserApplyItem($uid, $activityId, $time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from useractivityapply where ownerId = '{$uid}' and applyTime >= {$time} and activityId = '{$activityId}' order by applyTime desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		$userApplyItem = self::to($res, false);
		if($userApplyItem) {
			$userApplyItem->unserializeProperty('reward');
		}
		return $userApplyItem;
	}
	
	public static function selectApplyList($allianceId, $activityId, $time) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select u.uid, u.name, uaa.fightPower, uaa.winCount, uaa.reward, uaa.applyTime 
				from alliancemem am left join useractivityapply uaa on am.MemberId = uaa.ownerId
				left join userprofile u on am.MemberId = u.uid 
				where (select count(1) from useractivityapply where ownerId = uaa.ownerId and applyTime > uaa.applyTime and uaa.activityId = '{$activityId}') <= 1 
				and am.AllianceId = '{$allianceId}' and uaa.ownerId is not null order by uaa.fightPower desc, uaa.applyTime desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectApplyListOrderByFight($allianceId, $activityId, $time) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uaa.ownerId as uid, uaa.maxForces as forces, uaa.fightPower as power from alliancemem am 
				left join useractivityapply uaa on am.MemberId = uaa.ownerId
				where am.allianceId = '{$allianceId}' and uaa.activityId = '{$activityId}' 
				and uaa.applyTime >= {$time} and uaa.ownerId is not null order by uaa.fightPower asc, uaa.applyTime desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	public static function selectApplyListNow($allianceId, $activityId, $time) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select u.uid, u.name, uaa.fightPower, uaa.winCount, uaa.reward from useractivityapply uaa 
				left join userprofile u on uaa.ownerId = u.uid 
				where u.league = '{$allianceId}' and uaa.activityId = '{$activityId}' 
				and uaa.applyTime >= {$time} order by uaa.fightPower asc";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	public static function selectAllianceApplyItems($allianceId, $activityId, $time) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from alliancemem am 
				left join useractivityapply uaa on am.MemberId = uaa.ownerId
				where am.allianceId = '{$allianceId}' and uaa.activityId = '{$activityId}' 
				and uaa.applyTime >= {$time} and uaa.ownerId is not null ";
		$res = $mysql->execResultWithoutLimit($sql);
		$applyItems = self::to($res, true);
		return $applyItems;
	}
	
	public static function selectNotAcceptRewardItems($uid, $activityId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from useractivityapply where ownerId = '{$uid}' and activityId = '{$activityId}' order by applyTime desc limit 2";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
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
	
	public function save() {
		$this->serializeProperty('reward');
		parent::save();
		$this->unserializeProperty('reward');
	}
}
?>