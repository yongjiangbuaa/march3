<?php
/**
 * 用户征讨纪录表 
 */
import('persistence.dao.RActiveRecord');
class WorldFightItem extends RActiveRecord {
	protected $ownerId; //征讨方
	protected $type; //类型：1,征讨玩家; 2,增援玩家; 3,征讨遗迹; 4,增援自己联盟遗迹; 5;征讨联盟BOSS 
	protected $takeForces; //携带兵力
	protected $remainForces; //战斗损失后的剩余返还的兵力
	protected $targetUid;  //征讨目标UID
	protected $targetX; //征讨目标X
	protected $targetY; //征讨目标Y
	protected $startTime; //征讨或者增援的开始时间
	protected $waitTime;    //外出行军时间
	protected $endTime; //返回行军时间
	protected $status; //征战或增援的状态,0,还没战斗;1,回城中;2,胜利;3,失败
	protected $reinforceEndTime; //驻扎结束时间
	protected $allianceHostilityFlag; //是否是联盟天降,0否;1是
	protected $reportId;
	protected $timestamp;
	
	const TD = 5;
	
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
	
	public static function getWithUID($uid) {
		return self::getOne(__CLASS__, $uid);
	}
	
	public function init($type, $attacker, $target, $waitTime, $endTime, $reinforceEndTime, $isAllianceEnemy=0) {
		$currTime = time();
		$this->ownerId = $attacker['uid'];
		$this->type = $type;
		$this->takeForces = $attacker['takeForces'];
		$this->targetUid = $target['uid'];
		$this->targetX = $target['x'];
		$this->targetY = $target['y'];
		$this->startTime = $currTime;
		$this->waitTime = $waitTime;
		$this->endTime = $endTime;
		$this->reinforceEndTime = $reinforceEndTime;
		$this->status = 0;
		$this->timestamp = $currTime;
		$this->allianceHostilityFlag = $isAllianceEnemy;
		$this->save();
	}
	
	/**
	 * 取得可以征讨的记录 
	 */
	static function getAvailableMarchRecord($ownerId, $targetUid) {
		$time = time();
		$td = self::TD;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where ownerId = '{$ownerId}' and targetUid = '{$targetUid}' and status = 0 and {$time} > waitTime - {$td}";
		$res = $mysql->execResult($sql);
		return self::to($res, false);
	}
	
	/**
	 * 取得已经到达目标的征讨记录
	 */
	static function getAlreadyArriveTargetMarchRecord($targetUid, $fightType) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		if($fightType == 1) {
			$type = '(type = 1 or type = 2)';
		} else {
			$type = '(type = 3 or type = 4 or type = 5)';
		}
		$sql = "select * from worldfight where targetUid = '{$targetUid}' and status = 0 and {$time} > waitTime + 10 and {$type} and endTime != -1 order by waitTime asc";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	/**
	 * 取得可以召回部队的记录 
	 */
	static function getAvailableRecallRecord($ownerId) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where ownerId = '{$ownerId}' and ((status = 0 and {$time} < waitTime)
				or endTime = -1)";
		$res = $mysql->execResult($sql);
		return self::to($res, false);
	}
	
	/**
	 * 取得可以返回城市部队的记录 
	 */
	static function getAvailableReturnRecord($ownerId) {
		$time = time();
		$td = self::TD;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where ownerId = '{$ownerId}' and status > 0 and status < 4 and {$time} > endTime - {$td}";
		$res = $mysql->execResult($sql);
		return self::to($res, false);
	}
	
	/**
	 * 取得征讨或增援记录
	 */
	static function getWorldFightRecord($ownerId) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where ownerId = '{$ownerId}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	/**
	 * 取得征讨或增援的所有记录 
	 */
	static function getMarchAndReinForcesRecord($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, targetUid, targetX, targetY, waitTime, endTime, remainForces, reinforceEndTime, type, status 
				from worldfight where ownerId = '{$uid}'";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 取得到达目标的增援者 
	 */
	static function getArriveTargetHelpers($uid) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where targetUid = '{$uid}' and type = 2 and {$time} < reinforceEndTime
				and ((status = 0 and {$time} > waitTime) or endTime = -1) order by waitTime asc";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	/**
	 * 取得驻扎者 
	 */
	static function getHelpers($uid) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select u.uid, u.name, wf.takeForces, wf.reinforceEndTime from worldfight wf left join userprofile u on wf.ownerId = u.uid 
				where wf.targetUid = '{$uid}' and wf.type = 2 and {$time} < wf.reinforceEndTime and wf.endTime = -1 order by wf.waitTime asc";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 取得驻扎者 
	 * 对象形式
	 */
	static function getObjectHelpers($uid) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where targetUid = '{$uid}' and type = 2 and {$time} < reinforceEndTime and endTime = -1 ";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	/**
	 * 取得请离的对象，即未处于返回城市的记录 
	 */
	static function getClearReinForceRecord($uid, $targetUid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where ownerId = '{$uid}' and targetUid = '{$targetUid}' and type = 2";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, false);
	}
	
	/**
	 * 取得增援uid的人数
	 */
	static function getReinForceCount($uid) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(uid) as count from worldfight where targetUid = '{$uid}' and type = 2 
				and ({$time} < endTime or (endTime = -1 and {$time} < reinforceEndTime))";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res[0]['count'];
	}
	
	/**
	 * 玩家返回城市后删除此记录 
	 */
	static function delete($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from worldfight where uid = '{$uid}'";
		return $mysql->execute($sql);
	}
	
	/**
	 * 显示当前与自己相关的所有征讨信息和增援信息
	 */
	static function getMarchRecord($uid, $x = 0, $y = 0) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select wf.uid, wf.ownerId, u.name as ownerName, u.level as ownerLevel, u.vip as ownerVip, wf.targetUid, ". 
				"u1.name as targetName, u1.level as targetLevel, u1.vip as targetVip, wf.type, wf.status, wf.waitTime, ".
				"wf.endTime, wf.reportId, wf.reinforceEndTime, a.name as allianceName, wf.targetX, wf.targetY from worldfight wf ".
				"left join userprofile u on wf.ownerId = u.uid ".
				"left join userprofile u1 on wf.targetUid = u1.uid ".
				"left join alliance a on u.league = a.uid ".
				"where wf.ownerId = '{$uid}' or wf.targetUid = '{$uid}'";
		$data = $mysql->execResultWithoutLimit($sql);
		if($data) {
			import('service.action.WorldFightClass');
			foreach($data as $key => $value) {
				if($value['type'] == 2 && $currTime > $value['reinforceEndTime'] || ($value['type'] == 2 && $value['status'] == 1 && $currTime > $value['endTime'])) {
					unset($data[$key]);
				}
				if($value['type'] == 1 && $value['status'] != 0 && $currTime > $value['endTime']) {
					unset($data[$key]);
				}
				if($value['type'] == 1 && $value['status'] == 0 && $currTime > ($value['waitTime'] + 5) && $value['ownerId'] != $uid && $value['targetUid'] == $uid) {
					$worldFightLock = WorldFight::getWorldFightLock($value['uid']);
					if($worldFightLock->getLock()) continue;
					$ownerProfile = UserProfile::getWithUID($value['ownerId']);
					if($ownerProfile) {
						$worldFightService = WorldFight::singleton($ownerProfile);
						$worldFightService->setUser($ownerProfile);
						$fightData = $worldFightService->fight($uid);
						if(is_string($fightData)) continue;
						$value['status'] = $fightData['status'];
						$value['endTime'] = $fightData['endTime'];
						$value['reportId'] = $fightData['reportId'];
						$data[$key] = $value;
					}
				}
				if (5 == $value['type']) {
					$targetUid = explode ('_', $value['targetUid']);
					$value['targetUid'] = $targetUid[1];
					$value['allianceId'] = $targetUid[0];
//					import ('service.action.WorldFightClass');
//					$marchTime = WorldFight::calMarchTime($x, $y, $value['targetX'], $value['targetY']);
//					$value['bossGetTime'] = $marchTime;
					$data[$key] = $value;
				}
			}
			sort($data);
		}
		return $data;
	}
	
	/**
	 * 显示当前所有和联盟玩家相关的征讨信息
	 * 且只显示前往状态的信息。如果征讨部队召回，则不在该军情页显示
	 */
	static function getAllianceMemberFightItem($uid,$league) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select wf.ownerId, u.name as ownerName ,u.level as ownerLevel, u.vip as ownerVip, wf.targetUid,
				u1.name as targetName, u1.level as targetLevel, u1.vip as targetVip,
				wf.status, wf.waitTime, wf.endtime, a.name, u1.x, u1.y 
				from alliancemem am left join worldfight wf on wf.targetUid  = am.MemberId
				left join userprofile u on wf.ownerId = u.uid 
				left join userprofile u1 on wf.targetUid = u1.uid 
				left join alliance a on u.league = a.uid 
				where am.AllianceId = '{$league}' and am.status = 1 
				and wf.status = 0 and wf.type = 1 and wf.targetUid != '{$uid}' and {$currTime} < wf.waitTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 显示当前所有和联盟玩家相关的征讨信息
	 * 且只显示前往状态的信息。如果征讨部队召回，则不在该军情页显示
	 */
	static function getAllianceMemberFightOwnerItem($uid,$league) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select wf.ownerId, u.name as ownerName ,u.level as ownerLevel, u.vip as ownerVip, wf.targetUid,
				u1.name as targetName, u1.level as targetLevel, u1.vip as targetVip,
				wf.status, wf.waitTime, wf.endtime, a.name, u1.x, u1.y 
				from alliancemem am left join worldfight wf on wf.ownerId  = am.MemberId
				left join userprofile u on wf.ownerId = u.uid 
				left join userprofile u1 on wf.targetUid = u1.uid 
				left join alliance a on u.league = a.uid 
				where am.AllianceId = '{$league}' and am.status = 1 
				and wf.status = 0 and (wf.type = 1 or wf.type = 2) and wf.ownerId != '{$uid}' and {$currTime} < wf.waitTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 自己联盟的遗迹被攻击行为
	 */
	static function getSelfRelicMilitaryStatus($userLeague) {
		if(!$userLeague) {
			return array();
		}
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " select wf.ownerId, ou.name as ownerName, ou.level as ownerLevel, ou.vip as ownerVip, a.name as allianceName, 
				wf.targetUid, wf.targetX, wf.targetY, wf.status, wf.waitTime
				from worldfight wf left join world w on wf.targetUid = w.relicId
				left join userprofile ou on wf.ownerId = ou.uid
				left join alliance a on ou.league = a.uid
				where w.occupant = '{$userLeague}' and ou.league != '{$userLeague}' and wf.status = 0 and wf.type = 3 and {$currTime} < wf.waitTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 自己同联盟成员的遗迹行为
	 */
	static function getSameLeagueRelicMilitaryStatus($uid, $userLeague) {
		if(!$userLeague) {
			return array();
		}
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select wf.ownerId, u.name as ownerName ,u.level as ownerLevel, u.vip as ownerVip, wf.targetUid,
				wf.type, wf.status, wf.waitTime, a.name as allianceName, wf.targetX, wf.targetY
				from alliancemem am left join worldfight wf on wf.ownerId  = am.MemberId
				left join userprofile u on wf.ownerId = u.uid 
				left join alliance a on u.league = a.uid 
				where am.AllianceId = '{$userLeague}' and am.status = 1 
				and wf.status = 0 and wf.type = 3 and wf.ownerId != '{$uid}' and {$currTime} < wf.waitTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 取得驻扎在遗迹中的部队
	 */
	static function getArmyInRelic($relicId,$uid) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where (endTime = -1 or ({$currTime} > waitTime - 2 and status = 0)) 
				and (type = 3 or type = 4) and targetUid = '{$relicId}' and ownerId != '{$uid}' order by waitTime asc";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res, true);
	}
	
	/**
	 * 取得相同战斗时间的记录
	 */
	static function getSameFightTimeRecords($targetUid, $waitTime) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where waitTime = {$waitTime} and status = 0 and targetUid = '{$targetUid}'";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}
	
	/**
	 * 检测是否驻扎
	 */
	static function checkResidence($uid, $relicId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where endTime = -1 and (type = 3 or type = 4) and targetUid = '{$relicId}' and ownerId = '{$uid}' ";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 检测是否驻扎
	 */
	static function getResidenceRelicId($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select targetUid from worldfight where endTime = -1 and (type = 3 or type = 4) and ownerId = '{$uid}' ";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res) {
			return $res[0]['targetUid'];
		} else {
			return null;
		}
	}
	
	/**
	 * 获取征讨或驻扎遗迹的记录
	 */
	static function getSelfMarchRelicRecord($uid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from worldfight where (type = 3 or type = 4) and ownerId = '{$uid}' ";
		return $mysql->execResult($sql);
	}
	
	/**
	 * 取得特定联盟成员的征讨遗迹或者增援遗迹的记录
	 */
	static function selectAllianceMarch($allianceId) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " select wf.* from worldfight wf left join userprofile u on wf.ownerId = u.uid 
				where u.league = '{$allianceId}' and (wf.type = 3  or wf.type = 4) and (wf.status = 0 or wf.endTime = -1)";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 获得同一联盟玩家正在出征或返回的联盟boss
	 */
	static function getSameAllianceMarchBoss ($userLeague, $uid) {
		$currTime = time();
		import ('util.mysql.XMysql');
		$mysql = XMysql::singleton();	
		$sql = "select a.name, wf.ownerId, u.name as ownerName, u.level as ownerLevel, u.vip as ownerVip, wf.targetUid, "
		."wf.type, wf.targetX, wf.targetY, wf.startTime, wf.waitTime, wf.status "
		."from alliancemem am left join worldfight wf on wf.ownerId  = am.MemberId "
		."left join userprofile u on wf.ownerId = u.uid "
		."left join alliance a on a.uid = am.AllianceId "
		."where am.AllianceId = '{$userLeague}' and am.status = 1 and wf.type = 5 and wf.status = 0 and wf.ownerId != '{$uid}'";
		$data = $mysql->execResultWithoutLimit($sql);
		if($data) {
			foreach ($data as $key => $value) {
				if (time() - $value['waitTime'] >= 0) {//不显示已经到达的玩家
					unset($data[$key]);
					continue;	
				}	
				$targetUid = explode ('_', $value['targetUid']);
				$value['targetUid'] = $targetUid[1];
				$value['allianceId'] = $targetUid[0];
				$data[$key] = $value;
			}
		}
		return $data;
	}
	
	/**
	 * 到达联盟BOSSD的时间
	 */
	static function getAllianceBossTime ($userX, $userY, $targetX, $targetY) {
		
	}
	
	static function updateStatus($uid, $status, $endTime, $takeForces) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = " update worldfight set status = {$status}, endTime = {$endTime}, remainForces = {$takeForces} where uid = '{$uid}' ";
		return $mysql->execute($sql);
	}
}
?>