<?php
/**
 * TeamMemberItem
 * 队伍成员模型
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class TeamMemberItem extends RActiveRecord {
	protected $ownerId;     //用户UID
	protected $teamUid;    //队伍UID
	protected $leader;    //是否队长
	protected $join_in;     //加入时间,timestamp
	protected $name;        //昵称
	protected $level;       //等级
	protected $league;
	protected $pic;
	protected $fightPower;  //战斗力.
	protected $takeForcesNums; //携带兵力数
	protected $maxForces; //最大兵力
	protected $roundReward; //万里长征每一轮的奖励物品
	protected $generalList;
	protected $scene;
	
	const TABLE = 'teammember';
	
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
	static function getWithUID($uid, $activityId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select tm.* from teammember tm left join team t on tm.teamUid = t.uid where tm.ownerId = '{$uid}' and t.itemId = '{$activityId}' order by tm.join_in desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	static function getWithTeamUid($uid, $teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from teammember where ownerId = '{$uid}' and teamUid = '{$teamUid}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	/**
	 * 查询队伍成员
	 *
	 * @param String $teamUid
	 */
	static function getMembersForTeam($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid = '{$teamUid}' order by join_in limit 100";
		$res = $mysql->execResult($sql, 100);
		return self::to($res, true);
	}
	
	/**
	 * 加入队伍
	 *
	 */
	static function joinInTeam($userProfile, $teamItem, $fightPower, $leader = 0, $takeForcesNums, $maxForces){
		$memberItem = new self;
		$memberItem->ownerId = $userProfile->uid;
		$memberItem->teamUid = $teamItem->uid;
		$memberItem->leader = $leader;
		$memberItem->join_in = time();
		$memberItem->name = $userProfile->name;
		$memberItem->level = $userProfile->level;
		$memberItem->league = $userProfile->league;
		$memberItem->fightPower = $fightPower;
		$memberItem->pic = $userProfile->pic;
		$memberItem->takeForcesNums = $takeForcesNums;
		$memberItem->maxForces = $maxForces;
		$memberItem = self::setGeneralList($memberItem, $userProfile);
		$memberItem->save();
		return $memberItem;
	}
	
	static function setGeneralList($memberItem, $user) {
		import('service.action.FormationClass');
		$formation = Formation::singleton();
		$formation->setUser($user);
		$defaultFormation = $formation->getFormation($user->uid, 5);
		$formation->getForcesInFormation($defaultFormation);
		$memberItem->generalList = $defaultFormation->generalList;
		$memberItem->scene = $defaultFormation->scene;
		return $memberItem;
	}
	
	static function getNewCaptain($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid = '{$teamUid}' order by join_in asc limit 1";
		$res = $mysql->execResult($sql);
		return self::to($res);
	}
	
	static function getAllCaptain() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where leader = 1 order by join_in asc limit 20";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	/**
	 * 清除队伍成员
	 *
	 * @param unknown_type $teamUid
	 */
	static function removeTeamMembers($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::TABLE . " where teamUid = '{$teamUid}'";
		$res = $mysql->execute($sql);
		return $res;
	}
	
	static function clear($time){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from teammember where teamUid in (select uid from team where create_at < " . $time . " and status = 0)";
		$mysql->execute($sql);
		return true;
	}
	
	static function getTeamIdByUID($userId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select teamUid from " . self::TABLE . " where uid = '{$userId}'";
		$res = $mysql->execResult($sql);
		if($res) {
			return $res[0]['teamUid'];
		} else {
			return false;
		}
	}
	
	public function save() {
		$this->serializeProperty('roundReward');
		$this->serializeProperty('generalList');
		
		parent::save();
		
		$this->unserializeProperty('roundReward');
		$this->unserializeProperty('generalList');
	}
}
?>