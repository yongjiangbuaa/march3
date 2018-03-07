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
class ArenaRankItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;
	protected $rankList = array(); /* array(
										0 => array(
											'rank => 排名,
											'uid' => 玩家UID,
											'name' => 昵称,
											'pic' => 头像,
											'level' => 等级, 
											'power' => 战斗力, 
											'league' => 联盟, 
											'trend' = 排名趋势,
										  ),
									      ...
									   );*/
	
	static function init(){
		$arenaRankItem = new self;
		$arenaRankItem->uid = 'arena_rank_list';
		$arenaRankItem->flush();
		$arenaRankItem->save();
		return $arenaRankItem;
	}
	
	static function getRank(){
		$rankItem = self::getWithUID('arena_rank_list');
		if(!$rankItem){
			$rankItem = self::init();
		}
		return $rankItem;
	}
	
	static function currentRankList(){
		$rankItem = self::getRank();
		return $rankItem->rankList;
	}
	
	/*
	 * 刷新排行榜
	 */
	static function updateRankList($user){
		import('service.action.CacheLockClass');
		$time = 3600;
		$currentTime = time();
		$lock = CacheLock::start();
		$lock->setKeyPreWords('ARENA_NEXT_FLUSH_TIME');
		$lock->setExprireTime($time);
		$res = $lock->getLock();
		if($res && $res > $currentTime){
			return false;
		}
		$rankItem = self::getWithUID('arena_rank_list');
		if(!$rankItem){
			self::init();
		}else{
			$rankItem->flush();
		}
		$lock->setLock($time + $currentTime);
		//数据存入聊天
		import('service.action.ChatClass');
		$contents['mode'] = 12;
		$contents['modeValue'] = 'arenaFlush';
	//	Chat::message($user)->setContents($contents)->sendOneMessage();
		return true;
	}
	
	protected function flush(){
		import('service.item.ArenaItem');
		import('service.action.GeneralClass');
		$general = General::singleton();
		$playerRanks = ArenaItem::getTop100Players(1,2);
		$rankList = array();
		if($playerRanks)
		{
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
			$this->rankList = $rankList;
			$this->save();
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
		if($res)
			$res->unserializeProperty('rankList');
		return $res;
	}
	
	public function save(){
		$this->serializeProperty('rankList');
		parent::save();
		$this->unserializeProperty('rankList');
	}
}
?>