<?php
/**
 * AlliancePointsRankItem
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class AlliancePointsRankItem extends RActiveRecord {
	protected $uid;
	protected $lastRank;
	protected $currRank;
	protected $timestamp;
	
	const table = 'alliancepointsrank';
	
	public static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
	public static function addAlliancePointsRankItem($allianceUid,$rank) {
		$allianceItem = new self;
		$allianceItem->uid = $allianceUid;
		$allianceItem->lastRank = $rank;
		$allianceItem->currRank= $rank;
		$allianceItem->timestamp= time();
		$allianceItem->save();	
	}
	
	public static function getAllianceOrder() {
		$allianceRank = self::selectPointsRankItem();
		if(!$allianceRank) {
			$allianceOrder = self::selectAllianceOrderFromAlliance();
			$allianceOrder = self::insertAlliancePointsBatch($allianceOrder);
			return $allianceOrder;
		} else {
			return self::selectAllianceRank();
		}
	}
	
	public static function updateAlliancePointsRank() {
		$newAllianceOrder = self::selectAllianceOrderFromAlliance();
		if(!$newAllianceOrder) {
			return array();
		}
		$alliancePointsRankItem = self::selectPointsRankItem();
		if($alliancePointsRankItem) {
			self::deleteAlliancePointsRank();
		} else {
			$newAllianceOrder = self::insertAlliancePointsBatch($newAllianceOrder);
			return $newAllianceOrder;
		}
		foreach($newAllianceOrder as $order => $allianceOrderItem) {
			foreach($alliancePointsRankItem as $rankItem) {
				if($allianceOrderItem['uid'] == $rankItem['uid']) {
					$allianceOrderItem['lastRank'] = $rankItem['currRank'];
					break;
				}
			}
			$newAllianceOrder[$order] = $allianceOrderItem;
		}
		return self::insertAlliancePointsBatch($newAllianceOrder, false);
	}
	
	private static function insertAlliancePointsBatch($allianceOrder, $first=true) {
		if(!$allianceOrder) {
			return $allianceOrder;
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$batchSql = "insert into alliancepointsrank(uid, lastRank, currRank, timestamp) values";
		$time = time();
		$count = count($allianceOrder);
		foreach($allianceOrder as $order => $allianceItem) {
			if($first) {
				$lastRank = $order + 1;
			} else {
				$lastRank = $allianceItem['lastRank'];
			}
			$currRank = $order + 1;
			if($count - 1 == $order) {
				$batchSql .= "('{$allianceItem['uid']}', {$lastRank}, {$currRank}, {$time});";
			} else {
				$batchSql .= "('{$allianceItem['uid']}', {$lastRank}, {$currRank}, {$time}),";
			}
			$allianceItem['rankTrend'] = $currRank - $lastRank;
			$allianceOrder[$order] = $allianceItem;
		}
		$mysql->execute($batchSql);
		return $allianceOrder;
	}
	
	
	public static function selectAllianceOrderFromAlliance(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select a.uid, a.name as allianceName, a.level as allianceLevel, a.country as allianceCountry, a.points, 
				u.uid as leaderUid, u.name as leaderName, u.level as userLevel, u.pic 
				from alliance a
				left join alliancemem am on a.uid = am.AllianceId and am.type = 2
				left join userprofile u on am.MemberId = u.uid 
				where a.points != 0
				order by a.points desc, a.level DESC, a.exp desc";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}	
	
	public static function selectAllianceRank(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select a.uid, a.name as allianceName, a.level as allianceLevel, a.country as allianceCountry, a.points, 
				u.uid as leaderUid, u.name as leaderName, u.level as userLevel, u.pic, (ar.lastRank-ar.currRank) as rankTrend
				from alliancepointsrank ar
				left join alliance a on a.uid = ar.uid 
				left join alliancemem am on a.uid = am.AllianceId and am.type = 2
				left join userprofile u on am.MemberId = u.uid 
				where a.points != 0
				order by ar.currRank";
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}	
	
	public static function selectRPRank($allianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select currRank from " . self::table . " where uid = '{$allianceId}' ";
		$res = $mysql->execResultWithoutLimit($sql);
		if($res) {
			return $res[0]['currRank'];
		}
		return 0;
	}
	
	public static function selectPointsRankItem(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from " . self::table;
		$res = $mysql->execResultWithoutLimit($sql);
		return $res;
	}	
	
	public static function deleteAlliance($allianceId) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::table . " where uid = '{$allianceId}'";
		$res = $mysql->execute($sql);
	}
	
	private static function deleteAlliancePointsRank() {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "delete from " . self::table;
		$res = $mysql->execute($sql);
	}
}
?>