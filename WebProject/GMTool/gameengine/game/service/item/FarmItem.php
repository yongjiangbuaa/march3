<?php
import('persistence.dao.RActiveRecord');
class FarmItem extends RActiveRecord
{
	protected $uid;
	protected $exp;               //经验
	protected $level;             //等级	
	protected $spy;               //已用间谍个数
	protected $spyUpdateTime;     //间谍更新时间  
	protected $plantId;           //种子Id
	protected $finishTime;        //成熟时间
	protected $reward;            //奖励Id
	protected $stealRecord;       //小偷列表
	protected $processNum;        //已用加工次数
	
	public function getItems($uid)
	{
		$res = self::getWithUID($uid);
		if($res == NULL)
		{
			$res = self::initFarmItem($uid);
		}
		if($res->spy != 0)
		{
			//更新间谍
			import('service.item.ItemSpecManager');
			$itemXml = ItemSpecManager::singleton('default', 'item.xml');
			$farmStealXML = $itemXml -> getItem('farm_steal');
			$spyRecoverTime = $farmStealXML -> k2;
			if(time() - $res -> spyUpdateTime >= $spyRecoverTime * 60)
			{
				$addSpyNum = (int)((time() - $res->spyUpdateTime) / ($spyRecoverTime * 60));
				$res -> spy = max(0, $res -> spy - $addSpyNum);
				if($res -> spy == 0)
					$res -> spyUpdateTime = 0;
				else 
					$res -> spyUpdateTime += $addSpyNum * ($spyRecoverTime * 60);			
			}
		}
		$data['uid'] = $res->uid;	
		$data['itemId'] = null;					
		$data['level'] = $res -> level;
		$data['exp'] = $res -> exp;
		$data['spy'] = $res -> spy;
		$data['spyUpdateTime'] = $res -> spyUpdateTime;
		$data['plantId'] = $res -> plantId;
		$data['finishTime'] = $res -> finishTime;
		$data['stealRecord'] = $res -> stealRecord;
		$data['processNum'] = $res -> processNum;
		
		$farmXml = ItemSpecManager::singleton('default', 'farm.xml');
		$farmPlatformXML = $farmXml -> getItem('1316000' + (int)$data['level']);
		$data['upgradeExp'] = $farmPlatformXML -> exp;
		$data['machineNum'] = $farmPlatformXML -> num;
		
		$temp = ItemSpecManager::singleton('default', 'seed.xml') -> getGroup('xmas');
		foreach($temp as $key=>$value){
			$data['plantSeed'][] = $value; 
		}

		import('service.item.ItemSpecManager');		
		$farmXml = ItemSpecManager::singleton('default', 'farm.xml');
		$farmPlatformXML = $farmXml -> getItem('1316000' + (int)($data['level']));

		//获取被偷后剩余比例
		import('service.item.ItemSpecManager');
		$itemXml = ItemSpecManager::singleton('default', 'item.xml');
		$stealRate = $itemXml->getItem('farm_steal')->k5;		
		$thiefNum = substr_count($res -> stealRecord , ',');
		$remainRatio = 1 - $stealRate * $thiefNum / 100;
		
//		import('service.action.WorldClass'); //占有中型遗迹奖励
		import('service.user.UserProfile');
//		$allianceRelicRewardTimesTemp = World::getAllianceRelicReward(UserProfile::getWithUID($uid)->league, 2);
		import('service.action.WorldFightClass');
		$worldfightTemp = WorldFight::calPROMoney($uid, 100);	
//		$ratio['money'] = (1 + $allianceRelicRewardTimesTemp / 100) * $worldfightTemp / 100; 
		$ratio['money'] = $worldfightTemp * $remainRatio / 100; 
		$data['ratio'] = $worldfightTemp / 100;	
		if($data['plantId'] != 0)
		{
			//奖励信息
			$farmPlatformReward = $farmPlatformXML -> reward;
			$rewardByPlantType = explode('|', $farmPlatformReward);
			$rewardByPlantLevel = explode(',', $rewardByPlantType[(int)($data['plantId'] % 100 / 10)]);
			$rewardId = $rewardByPlantLevel[$data['plantId'] % 10 - 1];
			
			
			import('service.action.CalculateUtil');
			$data['rewardIdInfo'] = CalculateUtil::getInfoByRewardId($rewardId, null, $ratio);
			$data['reward'] = $rewardId;
		}

		$data['space'] = $farmPlatformXML -> space;
		
		import('service.item.FarmHistoryItem');
		$data['history'] = FarmHistoryItem::getItems($uid);

		$resData = array();
		$resData[] = $data;
		return $resData;

	}

	public  static function getWithUID($uid)
	{
		return self::getOne(__CLASS__, $uid);
	}
	
	public static function refresh($uid)
	{
		$res = self::getWithUID($uid);
		if($res == NULL)
		{
			$res = self::initFarmItem($uid);
		}
		$res -> processNum = 0;
		$res -> save();
	}
	
	/**
	 * 初始化
	 * @param unknown_type $uid
	 */
	public static function initFarmItem($uid)
	{
		$farmItem = new FarmItem();
		$farmItem -> uid = $uid;
		$farmItem -> exp = 0;
		$farmItem -> level = 1;		
		$farmItem -> spy = 0;
		$farmItem -> spyUpdateTime = 0;
		$farmItem -> plantId = 0;
		$farmItem -> finishTime = 0;
		$farmItem -> reward = 0;
//		$farmItem -> steal = 0;
		$farmItem -> stealRecord = NULL;
		$farmItem -> processNum = 0;
		$farmItem -> save();
		return $farmItem;
	}

}

?>