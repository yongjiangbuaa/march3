<?php
import('persistence.dao.RActiveRecord');
class TeamBattleItem extends RActiveRecord {
	protected $ownerId;    //用户UID
	protected $teamUid;    //队伍ID
	protected $x;          //坐标x
	protected $y;          //坐标y
	protected $generalList; //默认阵法上武将信息
	protected $role;       //1-攻防，2-守方
	protected $npc;       //1-npc; 0-player
	protected $armsId;    //npc armsId
	protected $join_in;   //进入时间,timestamp
	protected $name;
	protected $level;
	protected $face;
	protected $fightPower;
	protected $dead;       //1-挂了; 0-未挂
	protected $moveDistance; //移动距离
	protected $maxForces; //最大兵力
	protected $forces; //上一轮剩余兵力
	protected $lossForces; //损失兵力
	protected $scene; //用于战报的保存，不存数据库
	protected $attrGrow; //武将的颜色
	protected $forcesRatio; //武将的兵力比例
	protected $takeForcesNums; //携带兵力
	protected $useGoods; //使用道具
	protected $cmd; //使用命令
	protected $fixedPosition; //玩家指定位置
	protected $league;
	protected $killCount;
	protected $killForces;
	protected $getPoints;
	protected $effectList;
	protected $continuousKill;
	protected $leagueRole;
	protected $lastPath;
	
	const TABLE = 'teambattle';
	
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
	static function getWithUID($uid, $teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where ownerId='{$uid}' and teamUid = '{$teamUid}' order by x desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	/**
	 * 根据活动时间查找teambattle
	 */
	static function selectItemByTime($uid, $startTime){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from teambattle where ownerId='{$uid}' and join_in > {$startTime} order by join_in desc limit 1";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	static function getItems($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid='{$teamUid}' and dead = 0 order by join_in desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function getItemsByLevelOrder($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid='{$teamUid}' and dead = 0 order by level desc,join_in asc";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function getBattleOrder($teamUid, $killCountRatio, $killForcesRatio, $getPointsRatio){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select ownerId, name, role, killCount, killForces, getPoints from " . self::TABLE . 
				" where teamUid='{$teamUid}' order by killCount*" . $killCountRatio . " + killForces*" . $killForcesRatio . 
				" + getPoints*" . $getPointsRatio . " desc, killForces desc, getPoints desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	static function getItemsByRole($teamUid, $role){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid='{$teamUid}' and role = {$role} order by join_in asc limit 25";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function getAllItemsByRole($teamUid, $role){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid='{$teamUid}' and role = {$role}";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function getAllItems($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::TABLE . " where teamUid='{$teamUid}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	static function deleteNpcByTeamUid($teamUid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::TABLE . " where teamUid='{$teamUid}' and npc = 1";
		$res = $mysql->execute($sql);
	}
	
	static function deleteOutDatePlayer($teamItem) {
		$currTime = time();
		$xmlActivity = ItemSpecManager::singleton('default', 'activity.xml')->getItem($teamItem->itemId);
		$startTimes = explode(',',$xmlActivity->start_time);
		$endTimes = explode(',',$xmlActivity->end_time);
		for($i=0,$count=count($startTimes);$i<$count;$i++) {
			if($currTime > strtotime($startTimes[$i]) && $currTime < strtotime($endTimes[$i])){
				$startTime = strtotime($startTimes[$i]);
				$endTime = strtotime($endTimes[$i]);
			}
		}
		if(!$startTime || !$endTime) {
			return;
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::TABLE . " where teamUid = '{$teamItem->uid}' and npc = 0 and (join_in < {$startTime} or join_in > {$endTime})";
		$res = $mysql->execute($sql);
	}
	
	static function deleteOutDateAllianceBattleData($allianceBattleTeamCreateTime, $allianceBattleEndTime) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from teambattle where teamUid in (select uid from team where type = 2 and create_at >= {$allianceBattleTeamCreateTime}) 
				and join_in < {$allianceBattleEndTime}";
		$res = $mysql->execute($sql);
	}
	
	static function init($userProfile, $teamUid, $role, $npc){
		$teamBattleItem = new self;
		$teamBattleItem->ownerId = $userProfile->uid;
		$teamBattleItem->teamUid = $teamUid;
		$teamBattleItem->role = $role;
		$teamBattleItem->npc = $npc;
		$teamBattleItem->name = $userProfile->name;
		$teamBattleItem->level = $userProfile->level;
		$teamBattleItem->face = $userProfile->face;
		import('service.action.CalculateUtil');
		$generalGrow = CalculateUtil::getUserAttrGrow($userProfile->uid);
		$teamBattleItem->attrGrow = $generalGrow;
		$teamBattleItem->dead = 0;
		return $teamBattleItem;
	}
	
	static function clear($teamUid){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::TABLE . " where teamUid='{$teamUid}'";
		$res = $mysql->execute($sql);
		return $res;
	}
	
	public function save() {
		$this->serializeProperty('generalList');
		$this->serializeProperty('forcesRatio');
		$this->serializeProperty('useGoods');
		$this->serializeProperty('effectList');
		$this->serializeProperty('lastPath');
		parent::save();
		
		$this->unserializeProperty('generalList');
		$this->unserializeProperty('forcesRatio');
		$this->unserializeProperty('useGoods');
		$this->unserializeProperty('effectList');
		$this->unserializeProperty('lastPath');
	}
}
?>