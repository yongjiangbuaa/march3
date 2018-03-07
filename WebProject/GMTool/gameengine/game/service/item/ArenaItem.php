<?php
/**
 * ArenaItem
 * 
 * 竞技属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class ArenaItem extends RActiveRecord {
	protected $wins;    //连胜次数
	protected $rank;   //排名-Index,type:unique
	protected $trend;  //排名变化趋势 0-不变,1-升,2-降
	protected $endTime; //（领奖时间-timestamp
	protected $cd;     //战斗CD
	
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
	
	/*
	 * 根据排名查询
	 */
	static function getWithRank($rank){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('arena', array('rank' => $rank));
		return self::to($res);
	}
	
	/*
	 * 根据多个排名批量查询竞技场的信息
	 */
	static function getArenasByRanks($ranks){
		if(is_array($ranks)){
			$count = count($ranks);
			if($count<=0)
				return Array();
			$i = 1; 
			$sql = "select * from arena where ";
			foreach($ranks as $rank){
				if($i == $count)
					$sql.= "rank = '{$rank}'";
				else 
					$sql.= "rank = '{$rank}' or ";
				$i++;
			}
		}
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->execResult($sql,20);
		return $res;
	}
	
	/*
	 * 初始化竞技场数据
	 */
	static function InitArena($user){
		
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		
		import('service.item.ItemSpecManager');
		$cacheRank = $cache->get('Arena');
		if(!$cacheRank)
		{	
//			import('service.item.ItemSpecManager');
//			$rank = self::getArenaPlayerNums();
			$rank = self::getArenaPlayerNums();
			$cache->set('Arena',$rank+1,300);
			
		}else{
			$cache->increment('Arena');
			$rank = $cacheRank;
		}
// 		import('service.action.ArenaClass');
// 		$arena = Arena::singletion($user);
		$endTime = self::setRewardCD();
		
		//$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_time');
		$arenaItem = new self;
		$arenaItem->uid = $user->uid;
		$arenaItem->rank = $rank + 1;
		$arenaItem->wins = 1;
		$arenaItem->trend = 1;
		$arenaItem->endTime = $endTime;
		$arenaItem->save();
		return $arenaItem;
	}
	/*
	 * 设置奖励CD
	*/
	static function setRewardCD(){
		import('service.item.ItemSpecManager');
		$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_time');
		$time = mktime($xmlDataConfig->k3, 0, 0);
		//	$this->attackArenaItem->endTime = time() + 3600*24*($xmlDataConfig->k1);
		$currtime = time();
		if($currtime>=$time){
			$endtime = $time + 24*3600;
		}else{
			$endtime = $time;
		}
		return $endtime;
	}
	
	/*
	 * 取得竞技场玩家数量
	 */
	static function getArenaPlayerNums(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select count(*) as count from arena";
		$res = $mysql->execResult($sql);
		return $res[0]['count'];
	}
	
	/*
	 * 取得前100名
	 */
	static function getTop100Players($loweLimit,$upLimit){
		$loweLimit = $loweLimit - 1;
		$num = $upLimit - $loweLimit;
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select uid, rank, trend from arena where rank <= 100 order by rank limit {$loweLimit},{$upLimit}";
		$playerRanks = $mysql->execResult($sql, $num);
		
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
					'uid' => $playerProfile->uid,
					'name' => $playerProfile->name,
					'pic' => $playerProfile->pic,
					'level' => $playerProfile->level,
					'power' => $general->getUserFightPower(),
					'league' => $allianceName,
					'trend' => $playerRank['trend'],
				);
			}
		}
		$num = self::getArenaPlayerNums();
		$count = $num>100?100:$num;

		return array(
					'count' => $count,
					'RankList' => $rankList,
				);
	}
}
?>