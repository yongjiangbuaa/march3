<?php
/**
 * FriendItem
 * 
 * 建筑属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class FriendItem extends RActiveRecord {
	private static $instance = null;
	protected $ownerId;//所属用户
	protected $type;//类型1好友2仇人3世界中的收藏4屏蔽的玩家
	protected $playerUid;//对方uid
	protected $addTime;//添加时间
	protected $playerInfo;//玩家信息
	protected $onLine;//在线状态true false
	protected $bqqFriend;
	
 	/**
 	 * 获得指定uid的所有建筑
 	 * @param String $uid
 	 */
 	public function getItems($uid)
	{
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();

//		$sql = "select friend.*,u.vip,u.yellow_vip_status,u.yellow_vip_level,u.name,u.level,u.country,u.league,u.pic,u.x,u.y,f.exp as fexp,f.level as flevel,f.plantId,f.finishTime,f.reward,f.stealRecord from friend 
//				left join userprofile u on friend.playerUid = u.uid left join farm f on f.uid = u.uid where !ISNULL(u.name) and friend.ownerId = '$uid'";

		$sql = "select friend.*,al.name allianceName,u.vip,u.yellow_vip_status,u.yellow_vip_level,u.name,u.level,u.country,u.league,u.pic,u.x,u.y,f.exp as fexp,f.level as flevel,f.plantId,f.finishTime,f.reward,f.stealRecord from friend 
				left join userprofile u on friend.playerUid = u.uid 
				left join alliance al on al.uid = u.league 
				left join farm f on f.uid = friend.playerUid
				where !ISNULL(u.name) and friend.ownerId = '$uid'";

		$friends = $mysql->execResult($sql,400);
		$friends = self::toObject('FriendItem',$friends,true);
		import('service.item.ItemSpecManager');
		$data = Array();
		if(count($friends) > 0)
		{
			foreach ($friends as &$friend)
			{
				$this->getPlayerInfo($friend,false);
				$this->getPlayerOnlineState($friend);
				$this->getFarmInfo($friend);
			}
			
			
		}


		return $friends;
	}
	
	/**
	 * 世界中收藏
	 */
	static function collectPlayerInWorld($uid, $playerName) {
		import("service.item.FriendItem");
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$player = UserProfile::getWithName($playerName);
		$playerUid = $player->uid;
		if(!$player || !$playerUid) {
			import('service.action.ConstCode');
			return XServiceResult::clientError(ConstCode::ERROR_PLAYER_NOT_FOUND);
		}
		if($playerUid == $uid) {
			import('service.action.ConstCode');
			return XServiceResult::clientError(ConstCode::ERROR_CANNOT_ADD_SELF);
		}
		$friend = $mysql->get('friend',array('ownerId'=>$uid,'playerUid'=>$playerUid, 'type'=>3));
		if($friend) {
			import('service.action.ConstCode');
			return XServiceResult::clientError(ConstCode::ERROR_IN_FRIEND_LIST);
		}
		$sql = "select count(uid) as count from friend where ownerId = '{$uid}' and type = 3";
		$friendCount = $mysql->execResultWithoutLimit($sql);
		import('service.item.ItemSpecManager');
		$maxCount = 10;//ItemSpecManager::singleton('default', 'item.xml')->getItem('player_maxnum2')->k1;
		if($friendCount[0]['count'] > $maxCount) {
			import('service.action.ConstCode');
			return XServiceResult::clientError(ConstCode::ERROR_INVALID);
		}
		$friend = new FriendItem();
		$friend->ownerId = $uid;
		$friend->type = 3;
		$friend->playerUid = $playerUid;
		$friend->addTime = time();
		$friend->uid = getGUID();
		$friend->save();
		$friend->getPlayerInfo($friend);
		$friend->getPlayerOnlineState($friend);
		$data['friend'] = $friend;
		return $data;
	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param String $uid
	 * @return Object
	 */
	public function getWithUID($uid){
		$friendItem = self::getOne(__CLASS__, $uid);
		return $friendItem;
	}
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
	 * 获得建筑升级信息
	 * @param BuildingItem $buildingItem
	 * @param int $level
	 */
	public function getPlayerInfo(FriendItem $friendItem,$getUser = true)
	{
		if($getUser){
			$userProfile = UserProfile::getWithUID($friendItem->playerUid);
			if($userProfile->league){
				import('service.item.AllianceItem');
				$allianceItem = AllianceItem::getWithUID($userProfile->league);
			}
			$friendItem->x = $userProfile->x;
			$friendItem->y = $userProfile->y;
			$friendItem->playerInfo = array(
					'vip'=>$userProfile->vip,
					'yellow_vip_status'=>$userProfile->yellow_vip_status,
					'yellow_vip_level'=>$userProfile->yellow_vip_level,
					'name'=>$userProfile->name,
					'level'=>$userProfile->level,
					'country'=>$userProfile->country,
					'legion'=>$userProfile->league,
					'legionName'=>$allianceItem->name,
					'pic'=>$userProfile->pic,
					'fightPower'=>0,);
			//农场
			import('service.item.FarmItem');
			$res = FarmItem::getWithUID($friendItem->playerUid);
			$friendItem->flevel = $res->level;
			$friendItem->fexp = $res->exp;
			$friendItem->plantId = $res->plantId;
			$friendItem->finishTime = $res->finishTime;
			$friendItem->reward = $res->reward;
			$friendItem->stealRecord = $res->stealRecord;
			
			return;
		}
// 		if($friendItem->league){
// 			import('service.item.AllianceItem');
// 			$allianceItem = AllianceItem::getWithUID($friendItem->league);
// 		}
		$friendItem->playerInfo = array(
				'vip'=>$friendItem->vip,
				'yellow_vip_status'=>$friendItem->yellow_vip_status,
				'yellow_vip_level'=>$friendItem->yellow_vip_level,
				'name'=>$friendItem->name,
				'level'=>$friendItem->level,
				'country'=>$friendItem->country,
				'legion'=>$friendItem->league,
				'legionName'=>$friendItem->allianceName,
				'pic'=>$friendItem->pic,
				'fightPower'=>0,);
		//农场
		if($friendItem->reward != 0)
		{			
			import('service.action.CalculateUtil');
			$thiefNum = substr_count($friendItem -> stealRecord , ',');
			import('service.item.ItemSpecManager');
			$itemXml = ItemSpecManager::singleton('default', 'item.xml');
			$farmStealXML = $itemXml -> getItem('farm_steal');	
			$stealRate = $farmStealXML -> k5;
			$ratio['money'] = (1 - $stealRate * $thiefNum / 100);
			$friendItem->rewardIdInfo = CalculateUtil::getInfoByRewardId($friendItem->reward, null, $ratio);			
		}
		if($friendItem->flevel !== NULL)
		{
			$farmXml = ItemSpecManager::singleton('default', 'farm.xml');
			$farmPlatformXML = $farmXml -> getitem('1316000' + (int)$friendItem->flevel);
			$friendItem->farmUpgradeExp = $farmPlatformXML -> exp;
			$friendItem->farmMachineNum = $farmPlatformXML -> num;	
		}
	}
	/**
	 * 获得玩家在线状态
	 * @param FriendItem $friendItem
	 */
	public function getPlayerOnlineState(FriendItem $friendItem)
	{
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$key = 'ONLINE_USER_' . $friendItem->playerUid;
		if($cache->get($key) !== false)
			$friendItem->onLine = true;
		else
			$friendItem->onLine = false;
		return array('uid'=>$friendItem->uid,'onLine'=>$friendItem->onLine);
	}
	

	public function getFarmInfo(FriendItem $friendItem)
	{
		import('service.item.ItemSpecManager');		
		$farmXml = ItemSpecManager::singleton('default', 'farm.xml');
		$farmPlatformXML = $farmXml -> getItem('1316000' + (int)($friendItem->flevel));	
		//势力冲突
		import('service.action.WorldFightClass');
		$worldfightTemp = WorldFight::calPROMoney($friendItem->playerUid, 100);	
		$friendItem->ratio = $worldfightTemp / 100; 		 
			
		if($friendItem->plantId != 0)
		{
			//奖励信息
			$farmPlatformReward = $farmPlatformXML -> reward;
			$rewardByPlantType = explode('|', $farmPlatformReward);
			$rewardByPlantLevel = explode(',', $rewardByPlantType[(int)($friendItem->plantId % 100 / 10)]);
			$rewardId = $rewardByPlantLevel[$friendItem->plantId % 10 - 1];
			import('service.action.CalculateUtil');
			$thiefNum = substr_count($friendItem -> stealRecord , ',');
			import('service.item.ItemSpecManager');
			$itemXml = ItemSpecManager::singleton('default', 'item.xml');
			$farmStealXML = $itemXml -> getItem('farm_steal');	
			$stealRate = $farmStealXML -> k5;
			$stolenLimit = $farmStealXML -> k4;
			$ratio['money'] = 1 - $stealRate * $thiefNum / 100;
			$friendItem->rewardIdInfo = CalculateUtil::getInfoByRewardId($rewardId, null, $ratio);
			//偷取下限状态
			if($thiefNum >= (100 - $stolenLimit) / $stealRate)
				$friendItem->fLimitStatus = 1;
			else 
				$friendItem->fLimitStatus = 0;
			//是否已偷过	
			$friendItem->stealStatus = (strpos($friendItem->stealRecord, $friendItem->ownerId) === false) ? 0 : 1;
		}				
		$friendItem->space = $farmPlatformXML->space;
		if(!$friendItem)
		{
			$farmXml = ItemSpecManager::singleton('default', 'farm.xml');
			$farmPlatformXML = $farmXml -> getitem('1316000' + (int)$res->level);
			$friendItem->farmUpgradeExp = $farmPlatformXML -> exp;
			$friendItem->farmMachineNum = $farmPlatformXML -> num;	
		}
	}

}
?>