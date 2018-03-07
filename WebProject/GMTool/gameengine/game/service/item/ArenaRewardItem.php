<?php
/**
 * ArenaRankItem
 * 
 * 竞技场属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class ArenaRewardItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;                        	//用户UID
	protected $rank; 						//用户领奖排名
	protected $time;						//重置数据的时间
	//protected $rewardStatus;				//领奖状态
	
	//开启竞技场，初始化领奖
	static function init($uid,$rank){
		$arenaRewardItem = new self;
		$arenaRewardItem->uid = $uid;
		$arenaRewardItem->rank = $rank;
		$arenaRewardItem->time = time();
		$arenaRewardItem->save();
		return $arenaRewardItem;
	}
	
	static function currentRankList(){
	
	}
	
	/*
	 * 刷新领奖榜
	 */
	static function updateRankList(){
		import('service.action.CacheLockClass');
		import('service.item.ItemSpecManager');
		$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_time');
		$dayFlushTime = mktime($xmlDataConfig->k3,0,0);     //记录排名时间
		$time = 24*3600;
		$nextDayFlushTime = $dayFlushTime + $time;
		$currentTime = time();

		if($currentTime>$dayFlushTime){

			$lock = CacheLock::start();
			$lock->setKeyPreWords('ARENA_NEXT_FLUSH_TIME');
			$lock->setExprireTime($time);
			$res = $lock->getLock();

			if($res && $res > $currentTime){
				return false;
			}	
			
		}else{
			return false;	
		}
		
		//为防止缓存失效，从中取一条再次验证，是否更新
		if(!self::cheackTime($dayFlushTime))
			return false;
			
		import('util.cache.XCache');
		$lockCache = XCache::singleton();
		$lockCache->setKeyPrefix('IK2');
		$lockRes = $lockCache->get('ARENA_REWARD_TIME');
		
		if (!$lockRes)
		{
			$lockCache->set('ARENA_REWARD_TIME','lock',20);
			//更新数据库
			self::flush();
			$lock->setLock($nextDayFlushTime);
		
			//每天统计重楼排行
			import('service.item.UserOneThousandItem');
			$rank = UserOneThousandItem::getOneThousandOrder('totalOrder',0);
			//统计
			import('service.action.CalculateUtil');
			CalculateUtil::writeLog('world', 'OneThousand', array('rank'), array(), 'logstat', strtotime(date('Y-m-d')), $rank);
		}
		return true;
	}
	
	static function flush(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$time = time();
		$sql1 = "TRUNCATE TABLE arenareward";
		$res = $mysql->execute($sql1);
		$sql2 = "Insert into arenareward(uid,rank,time) select uid,rank,'{$time}' from arena";
		$res = $mysql->execute($sql2);
		//return $res[0]['count'];
		import('service.item.ArenaRecordItem');
		ArenaRecordItem::removeExpiredRecord(null);
	}
	static function cheackTime($dayFlushTime){
		
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$time = time();
		$sql = "select * from arenareward limit 1";
		$res = $mysql->execResult($sql, 1);
		$recordTime = $res[0]['time'];
		if($recordTime && $recordTime>$dayFlushTime){
			return false;
		}else{
			return true;
		}
	
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
	
}
?>