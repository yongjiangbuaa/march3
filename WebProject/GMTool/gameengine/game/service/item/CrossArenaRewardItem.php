<?php
import('persistence.dao.RActiveRecord');
class CrossArenaRewardItem extends RActiveRecord {
	protected $uid;				//玩家UID 
	protected $selfRank;		//自己的排名
  	protected $rewardId;		//在对应等级的竞技场中获得的奖励
  	protected $serverRank1;		//玩家所在服在50级竞技场中的排名
  	protected $serverReward1; 	//玩家所在服在50级竞技场中的奖励
  	protected $serverRank2;		//玩家所在服在60级竞技场中的排名
  	protected $serverReward2; 	//玩家所在服在60级竞技场中的奖励
  	protected $serverRank3;		//玩家所在服在70级竞技场中的排名
  	protected $serverReward3; 	//玩家所在服在70级竞技场中的奖励
  	protected $time = 0; 		//领奖时间 0代表未领取过
  	static protected $rankList = array ();
  	
	static function getWithUid ($uid) {
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
	
	static function setRankList () {
		import('service.item.ItemSpecManager');
		$xmlGroup = ItemSpecManager::singleton('default', 'cross.xml')->getGroup('cross_reward');
		foreach ($xmlGroup as $xml) {
			$data['rank'] = explode (',', $xml -> rank);
			$data['reward'] = $xml -> reward;
			switch ($xml -> area) {
				case 103300:
					$level = 5;
					break;
				case 103301:
					$level = 6;
					break;
				case 103302:
					$level = 7;
					break;
			}
			self::$rankList[$xml -> type][$level][] = $data;			
		}
	}
	
	static function getRewardId ($type, $level, $rank) {
		foreach (self::$rankList[$type][$level] as $key => $data) {
			if ($rank >= $data['rank'][0] && $rank <= $data['rank'][1]) 
				return $data['reward'];
		}
		return null;
	}
	
	/**
	 * 活动结束后根据积分状况存储玩家奖励记录
	 */
	static function sendReward () {
		self::setRankList();
		//全服奖励 $serverReward[服务器][竞技场等级] = 奖励
		$rank[5] = 1;
		$rank[6] = 1;
		$rank[7] = 1;
		import('service.item.CrossArenaItem');
		$serverRankData = CrossArenaItem::rankAllServer();
//		foreach ($serverRankData as $server) {
//			$level = $server['level'];
//			$rewardId = self::getRewardId(2, $level, $rank[$level]++);
//			if ($rewardId) { 
//				$serverReward[$server['server']][$level] = $rewardId;
//				$serverRank[$server['server']][$level] = $rank[$level] - 1;
//			}
//		}
		/*************************************************************************************/
		import ('service.action.CalculateUtil');
		$mergeSeverList = CalculateUtil::getServerIndex(); 
		foreach ($serverRankData as $value) {
			$serverIndex = $value['server'];
			$level = $value['level'];
			$serverIndex = $mergeSeverList[$serverIndex]['combine'];
			$newRankData[$level][$serverIndex] += $value['sum'];
		}
		if ($newRankData[5])
			arsort($newRankData[5]);
		if ($newRankData[6])
			arsort($newRankData[6]);
		if ($newRankData[7])
			arsort($newRankData[7]);
		$rank[5] = 1;
		$rank[6] = 1;
		$rank[7] = 1;
		foreach ($newRankData as $level => $info) {
			foreach ($info as $server => $sum) {
				$rewardId = self::getRewardId(2, $level, $rank[$level]++);
				if ($rewardId) { 
					$newServerReward[$server][$level] = $rewardId;
					$newServerRank[$server][$level] = $rank[$level] - 1;
				}
			}
		}
		/*************************************************************************************/
		//个人奖励
		$playerRankData = CrossArenaItem::rankAllPlayer();
		$time = 0; //用来标识玩家还没有领取过
		$sum = count($playerRankData);
		$valueNums = 0;
		foreach ($playerRankData as $player) {
			$server = $player['server'];
			$player['server'] = $mergeSeverList[$player['server']]['combine'];
			$rewardId = self::getRewardId(1, $player['level'], $player['rank']);
			$valueNums++;
			$values .= "('{$player['uid']}', '{$player['rank']}', '$rewardId', '{$newServerRank[$player['server']][5]}', '{$newServerReward[$player['server']][5]}', '{$newServerRank[$player['server']][6]}', '{$newServerReward[$player['server']][6]}', '{$newServerRank[$player['server']][7]}', '{$newServerReward[$player['server']][7]}', $time),";
			if ($valueNums % 500 == 0 || $valueNums == $sum) {
				$values = substr($values, 0, -1);
				$insertSql = "insert into crossarenareward (`uid`, `selfRank`, `rewardId`, `serverRank1`, `serverReward1`, `serverRank2`, `serverReward2`, `serverRank3`, `serverReward3`, `time`) values $values";
				import('service.action.CrossArenaClass');
				$mysql = CrossArena::getDB();
				$mysql->execute($insertSql);
				$values = NULL;
			}
		}
	}
	
	static function getSelfRank ($uid) {
		$sql = "select selfRank from crossarenareward where uid = '$uid'";
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sqlData = $mysql->execResultWithoutLimit($sql);
		return $sqlData[0]['selfRank'];
	}
	
	/**
	 * 清除已经领过奖励的玩家
	 * Enter description here ...
	 */
	static function removeData () {
		import('service.action.CrossArenaClass');
		$mysql = CrossArena::getDB();
		$sql = "delete from crossarenareward where time != 0";
		$mysql->execute($sql);
	}
}
?>  	