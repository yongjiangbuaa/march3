<?php
import('persistence.dao.RActiveRecord');
class CrossArenaItem extends RActiveRecord {
	protected $level = 0;		//50级场 60级场 70 级场 分别对应 5 6 7
	protected $server = 0;		//服务器
	protected $times = 0;		//挑战次数
	protected $integration = 0; //个人积分 
	protected $wins = 0;   		//连胜次数
	protected $rank = 0;   		//排名-Index,type:unique
	protected $trend = 0;  		//排名变化趋势 0-不变,1-升,2-降
	protected $cd = 0; 
	protected $buyTimes = 0;	//购买挑战次数
	protected $initTime;		//初始化时间
	
	static function getWithUid ($uid) {
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
	static function getDB(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		return $mysql->connect();
	}
	
	/**
	 * 得到某个服务器的排名
	 * @param unknown_type $server
	 * @param unknown_type $level
	 */
	static function getServerRank ($server, $level) {
		$sql = "select server, sum(integration) as sum from crossarena where level = $level group by server order by sum DESC";
		$mysql = self::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
//		foreach ($sqlData as $key => $value) {
//			if ($value['server'] == $server) {
//				$res = $value;
//				$res['rank'] = $key + 1;
//				break;
//			}
//		}
		import ('service.action.CalculateUtil');
		$mergeSeverList = CalculateUtil::getServerIndex();
		foreach ($sqlData as $value) {
			$serverIndex = $value['server'];
			$serverIndex = $mergeSeverList[$serverIndex]['combine'];
			$newRankData[$serverIndex] += $value['sum'];
		}
		if ($newRankData)
			arsort($newRankData);
		$serverIndex = $mergeSeverList[$server]['combine'];
		$rank = 0;
		foreach ($newRankData as $key => $value) {
			$rank++;
			if ($key == $serverIndex) {
				$res['sum'] = $value;
				$res['rank'] = $rank;
				break;
			}
		}	
		return $res;
	}
	
	static function getCombineInfo () {
		import ('service.action.CalculateUtil');
		$serverIndex = CalculateUtil::getServerIndex();
		import('service.action.DataClass');
		foreach ($serverIndex as $server => $value) {
			$combineServer = $value['combine'];
			$name = $value['name'];
			$index = $value['index'];
			if ($combineServer) {
				$res[$combineServer][] = $server;
			}
		}
		foreach ($res as $key => $value) {
			foreach ($value as $combineSer) {
				if ($combineSer != $key) 
					$res[$combineSer] = $res[$key];
			}
		}
		return $res;
	}
	/**
	 * 得到某个服务器内玩家的排名 
	 * @param unknown_type $server
	 * @param unknown_type $level
	 */
	static function getPlayerRankByServer ($server, $level) {
		$combineInfo = self::getCombineInfo();
		$combineServer = $combineInfo[$server];
		foreach ($combineServer as $server) {
			$serverSql .= " or server = $server";
		}
		$serverSql = substr($serverSql, 4);
		$sql = "select uid, rank, integration from crossarena where ($serverSql) and level = $level order by integration DESC limit 20";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$playerRanks = $mysql->execResultWithoutLimit($sql);
		if($playerRanks)
		{
			import('service.action.GeneralClass');
			$general = General::singleton();
			foreach ($playerRanks as $key => $playerRank){
				$playerProfile = UserProfile::getWithUID($playerRank['uid']);
				$general->setUserUid($playerRank['uid']);
				if($playerProfile->league){
					import('service.item.AllianceItem');
					$allianceItem = AllianceItem::getWithUID($playerProfile->league);
					$allianceName = $allianceItem->name;
				}else{
					$allianceName = null;	
				}
				
				$rankList[] = array(
					'integration' => $playerRank['integration'],
					'rank' => $playerRank['rank'],
					'uid' => $playerProfile->uid,
					'name' => $playerProfile->name,
					'pic' => $playerProfile->pic,
					'level' => $playerProfile->level,
					'power' => $general->getUserFightPower(),
					'league' => $allianceName
				);
			}
		}
		return $rankList;
	}
	
	/**
	 * 根据积分对所有的服务器排序并返回 
	 */
	static function rankAllServer () {
		$sql = "select server, level, SUM(integration) as sum from crossarena group by level, server order by SUM(integration) DESC";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		return $sqlData;
	}
	
	/**
	 * 根据排名对所有的玩家排序并返回
	 */
	static function rankAllPlayer () {
		$sql = "select uid, server, rank, level from crossarena order by rank";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		return $sqlData;
	}
	/**
	 * 得到排名前五的服务器 
	 * @param unknown_type $level
	 */
	static function rankServerTopFive ($level, $limit = 5) {
		$sql = "select server, sum(integration) as sum from crossarena where level = $level group by server order by sum DESC";
		$mysql = self::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		import ('service.action.CalculateUtil');
		$mergeSeverList = CalculateUtil::getServerIndex(); 
		foreach ($sqlData as $value) {
			$serverIndex = $value['server'];
			$serverIndex = $mergeSeverList[$serverIndex]['combine'];
			if ($serverIndex)
				$newRankData[$serverIndex] += $value['sum'];
			else
				$newRankData[$value['server']] += $value['sum'];
		}
		if ($newRankData)
			arsort($newRankData);
		$limit=0;
		foreach ($newRankData as $key => $value) {
			$limit++;
			$res[] = array ('server' => $key, 'sum' => $value);
			if ($limit == 5)
				break;
		}
		return $res;
	}
	
	/**
	 * 得到排名前五的玩家
	 * @param unknown_type $level
	 */
	static function rankPesonalTopFive ($level) {
		$sql = "select uid, rank, integration from crossarena where level = $level and rank > 0 and rank < 6 order by rank limit 5";
		$mysql = self::getDB();
		$playerRanks = $mysql->execResultWithoutLimit($sql);
		if($playerRanks)
		{
			import('service.action.GeneralClass');
			$general = General::singleton();
			foreach ($playerRanks as $playerRank){
				$playerProfile = UserProfile::getWithUID($playerRank['uid']);
				$general->setUserUid($playerRank['uid']);
				if($playerProfile->league){
					import('service.item.AllianceItem');
					$allianceItem = AllianceItem::getWithUID($playerProfile->league);
					$allianceName = $allianceItem->name;
				}else{
					$allianceName = null;	
				}
				
				$rankList[] = array(
					'rank' => $playerRank['rank'],
					'integration' => $playerRank['integration'],
					'uid' => $playerProfile->uid,
					'name' => $playerProfile->name,
					'pic' => $playerProfile->pic,
					'level' => $playerProfile->level,
					'power' => $general->getUserFightPower(),
					'league' => $allianceName
				);
			}
		}
		return $rankList;
	}
	
	/**
	 * 根据多个排名批量查询竞技场的信息
	 */
	static function getArenasByRanks($ranks, $level){
		if(is_array($ranks)){
			$count = count($ranks);
			if($count<=0)
				return Array();
			$i = 1; 
			$sql = "select * from crossarena where level = $level and (";
			foreach($ranks as $rank){
				if($i == $count)
					$sql.= "rank = '{$rank}'";
				else 
					$sql.= "rank = '{$rank}' or ";
				$i++;
			}
		}
		$sql .= ") ORDER BY rank";
		$mysql = self::getDB();
		$res = $mysql->execResult($sql,20);
		return $res;
	}
	
	static function setCDTime ($uid) {
		$time = time ();
		$sql = "UPDATE crossarena SET cd = {$time} WHERE uid = '{$uid}'";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execute($sql);
		return $time;
	}
	
	static function getCDTime ($uid) {
		$sql = "SELECT cd FROM crossarena WHERE uid = '{$uid}'";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		$cd = $sqlData[0]['cd'];
		return $cd;
	}
	
	static function setBuyTimes ($uid, $times) {
		$sql = "UPDATE crossarena SET buyTimes = buyTimes + {$times} WHERE uid = '{$uid}'";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execute($sql);
	}
	
	static function getBuyTimes ($uid) {
		$sql = "SELECT buyTimes FROM crossarena WHERE uid = '{$uid}'";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		$sqlData = $sqlData[0]['buyTimes'];
		if ($sqlData)
			return $sqlData;
		else
			return 0;
	}
	
	/**
	 * 初始化跨服竞技场数据
	 * uid, level, server, rank, integration
	 */
	static function initCrossArena ($time) {
		//根据战斗力对UID排序
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select u.uid, u.level as le, u.platformAddress as pt,r.fightPower from rankinfo r right join userprofile u on u.uid = r.uid order by fightPower DESC";
		$orderData = $mysql->execResultWithoutLimit($sql);
//		import ('service.action.CrossArenaClass');
//		$mysql = CrossArena::getDB();
//		$orderData = $mysql->execResultWithoutLimit($sql);
		$sum = count ($orderData);
		$rank[5] = 1;
		$rank[6] = 1;
		$rank[7] = 1;
		$valueNums = 0;
		foreach ($orderData as $one) {
			$values .= '(';
			foreach ($one as $key => $value) {
				if ($key == 'fightPower')
					continue;
				if ($key == 'le') {
					$value = floor ($value / 10);
					if ($value > 7)
						$value = 7;
					$one[$key] = $value;
				}
				elseif ($key == 'pt') {
					$value = explode('_', $value);
					$server = $value[3];
					$playerRank = $rank[$one['le']]++;
					import ('service.action.CrossArenaClass');
					$arenaNum = CrossArena::getArenaNum($one['le']);
					$integration = CrossArena::getIntegration($arenaNum, $playerRank);
					$value ="{$server}', '{$playerRank}', '{$integration}";
				}
				$values .= "'{$value}', ";
			}
//			$values = substr($values, 0, -2);
			$values .= "'{$time}'"; 
			$values .= '),' ;
// 			$uid = $one['uid'];
// 			$level = floor($one['le']/10);
// 			if ($level > 7)$level = 7;
// 			$pt = $one['pt'];
// 			$pt = explode('_', $pt);
// 			$server = $pt[3];
// 			$playerRank = $rank[$level]++;
// 			import ('service.action.CrossArenaClass');
// 			$arenaNum = CrossArena::getArenaNum($level);
// 			$integration = CrossArena::getIntegration($arenaNum, $playerRank);
// 			$values .="('{$uid}', '{$level}', '{$server}', '{$playerRank}', '{$integration}', '{$time}'),";
			$valueNums++;
			if ($valueNums % 500 == 0 || $valueNums == $sum) {
				$values = substr($values, 0, -1);
				$insertSql = "insert into crossarena (uid, level, server, rank, integration, initTime) values $values";
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$mysql->execute($insertSql);
				$values = NULL;
//				import ('service.action.CrossArenaClass');
//				$mysql = CrossArena::getDB();
//				$mysql->execute($insertSql);
			}
		}
		//TODO 查询结果记录
		import('service.action.LoggerClass');
		$dir = date('Y-m-d').'_carena';
		foreach ($orderData as $key=>$one) {
			$level = floor($one['le']/10);
			if ($level > 7)$level = 7;
			$log .= "$key	{$one['fightPower']}	$level	{$one['le']}	{$one['uid']}	{$one['pt']}\n";
		}
		$logger = new FileLogger('initList', $log, $dir);
		$logger->log();
	}
}
?>