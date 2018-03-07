<?php
/**
 * 宣战记录
 */
import('persistence.dao.RActiveRecord');
class ProclaimWarItem extends RActiveRecord {
	protected $ownerId;  //宣战发起方
	protected $targetId;    //被宣战方
	protected $type;      //1,宣战;
	protected $waitTime;  //宣战后进入宣战状态，宣战状态<<持续一段时间>>后，俩个玩家进入敌对状态
	protected $endTime;   //敌对持续时间，自成功宣战起计
	protected $timeStamp; //时间戳
	
	const TD = 30;
	
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
	 * 取得玩家间宣战记录
	 */
	static function getProclaimRecord($ownerId, $targetId, $type=1){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from proclaimwar where ((ownerId='{$ownerId}' and targetId='{$targetId}') or (ownerId='{$targetId}' and targetId='{$ownerId}')) and type = {$type} and {$time} < endTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 取得自己的天降记录
	 */
	static function getOwnerAllianceProclaimRecord($ownerId, $type=1) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from proclaimwar where ownerId='{$ownerId}' and type = {$type} and {$time} < endTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 根据时间取得宣战记录
	 */
	static function getProclaimOwnerRecordByTime($ownerId, $type=1) {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from proclaimwar where (ownerId='{$ownerId}' or targetId='{$ownerId}') and type = {$type} and {$time} > waitTime and {$time} < endTime";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 检测是否某玩家处于敌对状态
	 */
	static function checkHostility($ownerId, $targetId, $type=1){
		$time = time();
		$td = self::TD;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from proclaimwar where ((ownerId='{$ownerId}' and targetId='{$targetId}') or (ownerId='{$targetId}' and targetId='{$ownerId}')) and type = {$type} and {$time} > waitTime - {$td} and {$time} < endTime - {$td}";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 清除敌对
	 */
	static function delHostility($ownerId, $targetId){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from proclaimwar where (ownerId='{$ownerId}' and targetId='{$targetId}') or (ownerId='{$targetId}' and targetId='{$ownerId}')";
		return $mysql->execute($sql);
	}
	
	/**
	 * 取得收藏玩家 
	 *
	 */
	static function getCollectPlayer($ownerId, $targetId){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from proclaimwar where ownerId='{$ownerId}' and targetId='{$targetId}' and type = 2";
		$res = $mysql->execResultWithoutLimit($sql);
		return self::to($res);
	}
	
	/**
	 * 已宣战人数
	 */
	static function getProclaimCount($userUid){
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(uid) as count from proclaimwar where (ownerId='{$userUid}' or targetId='{$userUid}') and type = 1 and {$time} < endTime";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res[0]['count'];
	}
	
	/**
	 * 增加一个敌对关系和宣战关系解除条件：
	 * 玩家如果被打流放，则与之相关的宣战和敌对关系解除
	 */
	static function deletePlayerProclaimRecord($userUid) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from proclaimwar where ownerId='{$userUid}' or targetId='{$userUid}' ";
		return $mysql->execute($sql);
	}
	
	/**
	 * 清除过期的征讨记录
	 */
	static function deleteOutDateRecord() {
		$time = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from proclaimwar where {$time} > endTime";
		return $mysql->execute($sql);
	}
	
	/**
	 * 联盟宣战记录:target
	 */
	static function selectAllianceOwnerProclaims($allianceId) {
		import('util.mysql.XMysql');
		$time = time();
		$data = array();
		$mysql = XMysql::singleton();
		$sql = "select p.uid, p.ownerId, p.targetId, a.name as targetAllianceName, a.country as targetAllianceCountry, 
				a.level as targetAllianceLevel, a.memberNum targetAllianceNums, a.memLimitNum as targetAllianceMaxNums,
				sum(am.power) as targetPower from proclaimwar p 
				left join alliance a on p.targetId = a.uid
				left join alliancemem am on p.targetId = am.AllianceId
				where p.type = 3 and p.ownerId = '{$allianceId}' and am.status = 1 and p.endTime > {$time} 
				group by p.targetId";
		$data = $mysql->execResultWithoutLimit($sql);
		return $data;
	}
	
	/**
	 * 联盟宣战记录:onwer
	 */
	static function selectAllianceTargetProclaims($allianceId) {
		import('util.mysql.XMysql');
		$time = time();
		$data = array();
		$mysql = XMysql::singleton();
		$sql = "select p.uid, p.ownerId, p.targetId, a.name as targetAllianceName, a.country as targetAllianceCountry, 
				a.level as targetAllianceLevel, a.memberNum targetAllianceNums, a.memLimitNum as targetAllianceMaxNums,
				sum(am.power) as targetPower from proclaimwar p 
				left join alliance a on p.ownerId = a.uid
				left join alliancemem am on p.ownerId = am.AllianceId
				where p.type = 3 and p.targetId = '{$allianceId}' and am.status = 1 and p.endTime > {$time}
				group by p.ownerId order by targetPower desc";
		$data = $mysql->execResultWithoutLimit($sql);
		return $data;
	}
	
	/**
	 * 获取目标：敌对
	 */
	static function getHostilityInfo($uid) {
		import('util.mysql.XMysql');
		$time = time();
		$data = array();
		$mysql = XMysql::singleton();
		$sql = "select p.targetId as uid, p.type, p.waitTime, p.endTime, 
				u.name, u.vip, u.level, u.x, u.y from proclaimwar p 
				left join userprofile u on p.targetId = u.uid
				where p.ownerId = '{$uid}' and p.type = 1 and {$time} <= p.endTime";
		$ownerData = $mysql->execResultWithoutLimit($sql);
		$sql = "select p.ownerId as uid, p.type, p.waitTime, p.endTime, 
				u.name, u.vip, u.level, u.x, u.y from proclaimwar p 
				left join userprofile u on p.ownerId = u.uid
				where p.targetId = '{$uid}' and p.type = 1 and {$time} <= p.endTime";
		$targetData = $mysql->execResultWithoutLimit($sql);
		if($ownerData) {
			if($targetData) {
				$data = array_merge($ownerData, $targetData);
			} else {
				$data = $ownerData;
			}
		} else {
			$data = $targetData;
		}
		return $data;
	}
	
	/**
	 * 获取联盟天降目标
	 */
	static function getAllianceProclaimTargets($allianceId) {
		import('util.mysql.XMysql');
		$time = time();
		$data = array();
		$mysql = XMysql::singleton();
		$sql = "select p.type, p.waitTime, p.endTime, a.name as allianceName, u.uid, u.name, u.vip, u.level, u.x, u.y,am.type as leagueRole from proclaimwar p 
				left join alliance a on p.targetId = a.uid
				left join alliancemem am on p.targetId = am.AllianceId
				left join userprofile u on am.MemberId = u.uid
				left join userworld uw on am.MemberId = uw.uid
				where am.status = 1 and p.ownerId = '{$allianceId}' and {$time} <= p.endTime and p.type = 3 and uw.uid is not null";
		$ownerData = $mysql->execResultWithoutLimit($sql);
		$sql = "select p.type, p.waitTime, p.endTime, a.name as allianceName, u.uid, u.name, u.vip, u.level, u.x, u.y,am.type as leagueRole from proclaimwar p 
				left join alliance a on p.ownerId = a.uid
				left join alliancemem am on p.ownerId = am.AllianceId
				left join userprofile u on am.MemberId = u.uid
				left join userworld uw on am.MemberId = uw.uid
				where am.status = 1 and p.targetId = '{$allianceId}' and {$time} <= p.endTime and p.type = 3 and uw.uid is not null";
		$targetData = $mysql->execResultWithoutLimit($sql);
		if($ownerData) {
			if($targetData) {
				$data = array_merge($ownerData, $targetData);
			} else {
				$data = $ownerData;
			}
		} else {
			$data = $targetData;
		}
		return $data;
	}
	
	/**
	 * 获取目标：同盟成员正在遭受征讨的
	 */
	static function getSameLeagueInfo($uid, $league) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select am.MemberId as uid, u.name, u.vip, u.level, u.x, u.y, wf.waitTime from alliancemem am 
				left join userprofile u on am.MemberId = u.uid
				left join worldfight wf on am.MemberId = wf.targetUid
				where am.AllianceId = '{$league}' and am.status = 1 
				and wf.type = 1 and wf.status = 0 and am.MemberId != '{$uid}' group by am.MemberId";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 获取目标：所有遗迹
	 */
	static function getAllRelic() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select w.x,w.y,w.type as worldType, w.occupant, a.name as allianceName, w.relicId from 
				world w left join alliance a on w.occupant = a.uid where w.type = 2";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 获取特定遗迹的占领部队数量
	 */
	static function getRelicNumbers($relicId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(uid) as count from 
				worldfight where targetUid = '{$relicId}' and (type = 3 or type = 4) and endTime = -1";
		$data = $mysql->execResultWithoutLimit($sql);
		if($data) {
			return $data[0]['count'];
		}
		return 0;
	}
	
	/**
	 * 获取特定遗迹的占领部队数量
	 */
	static function getRelicDefenders($relicId) {
		$data = array();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select wf.ownerId, u.name, wf.takeForces from 
				worldfight wf left join userprofile u on wf.ownerId = u.uid 
				where wf.targetUid = '{$relicId}' and (wf.type = 3 or wf.type = 4) and wf.endTime = -1 order by wf.waitTime asc";
		$data = $mysql->execResultWithoutLimit($sql);
		return $data;
	}
	
	/**
	 * 获取目标：占领部队数量
	 */
	static function getMarchRelicNumbers() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select targetUid, count(uid) as count from 
				worldfight where (type = 3 or type = 4) and endTime = -1 group by targetUid";
		return $mysql->execResultWithoutLimit($sql);
	}
	
	/**
	 * 清除同联盟的敌对关系 
	 */
	static function clearSameLeagueHostility($allianceUid) {
		$currTime = time();
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select pw.uid from proclaimwar pw
				left join userprofile u1 on pw.ownerId = u1.uid
				left join userprofile u2 on pw.targetId = u2.uid
				where u1.league='{$allianceUid}' and u2.league='{$allianceUid}'
				and pw.type = 1 and {$currTime} < pw.endTime";
		$data = $mysql->execResultWithoutLimit($sql);
		if($data) {
			$uids = '';
			$count = count($data);
			foreach($data as $key => $item) {
				if($key == $count - 1) {
					$uids = $uids . "'" . $item['uid'] . "'";
				} else {
					$uids = $uids . "'" . $item['uid']."',";
				}
			}
			$sql = "delete from proclaimwar where uid in ( " . $uids .")";
			$mysql->execute($sql);
		}
	}
}
?>