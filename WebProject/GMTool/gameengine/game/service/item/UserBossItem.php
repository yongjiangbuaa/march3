<?php
/**
 * 用户攻打怪物信息记录表
 */
import('persistence.dao.RActiveRecord');
class UserBossItem extends RActiveRecord {
	protected $uid;  //玩家ID
	protected $fightCount; //战斗次数
	protected $fightCountRecoverTime; //战斗次数恢复时间
	protected $bossInfo; //攻打怪物情况 array('itemId' => 3301, 'bossRemainForces' => 1000, '$acceptReward' => array('id' = 15, 'status' => 0/1/2),,,)
	protected $buyTimes; //购买的次数
	protected $buyTimesDaily; //每天购买的次数
	protected $ratio1; //怪物难度一打的 进度
	protected $count1; //怪物难度一打的 次数
	protected $ratio2; //怪物难度二打的 进度
	protected $count2; //怪物难度二打的 次数
	protected $ratio3; //怪物难度三打的 进度
	protected $count3; //怪物难度三打的 次数
	protected $time; 
	
	const tableName = 'userboss';
	
	public function getItems($uid){
		$userBossItem = self::getWithUID($uid);
		$dataXml = ItemSpecManager::singleton('default','item.xml')->getItem('activity_monster');
		import('service.action.ActiveMonsterClass');
		$data = ActiveMonster::flushUserFightMonsterCount($userBossItem, $dataXml->k5, $dataXml->k4);
		$data['totalFightCount'] = $dataXml->k4;
		$data['buyTimes'] = $userBossItem->buyTimes;
		$data['buyTimesDaily'] = $userBossItem->buyTimesDaily;
		$data['goldPerBuyTimes'] = $dataXml->k6;
		$data['maxBuyTimes'] = $dataXml->k7;
		import('service.item.ServerResetItem');
		$activityTime = ServerResetItem::getAttackBossActivityTime($dataXml->k1, $dataXml->k2, $dataXml->k3);
		if($activityTime && $activityTime[2]) unset($activityTime[2]);
		$data['time'] = $activityTime;
		$bossInfo = self::handleBossInfo($userBossItem->bossInfo);
		$data['bossInfo'] = $bossInfo;
		$data['itemId'] = $uid;
		$res[0] = $data;
		return $res;
	}
	
	static function handleBossInfo($bossInfo) {
		if($bossInfo) {
			import('service.item.ItemSpecManager');
			import("service.action.CalculateUtil");
			foreach($bossInfo as $key => $bossItem) {
				$data = array();
				$bossXml = ItemSpecManager::singleton('default','activeMonster.xml')->getItem($bossItem['itemId']);
				$bossItem['totalForces'] = $bossXml->army;
				$bossRewardArray = explode(';', $bossXml->reward);
				if($bossRewardArray) {
					foreach($bossRewardArray as $k => $bossRewardItem) {
						$item = explode(',', $bossRewardItem);
						$rewardDate = CalculateUtil::getInfoByRewardId($item[1]);
						$data[] = array('id' => $item[0], 'reward' => $rewardDate, 'status' => 0);
					}
					$acceptReward = $bossItem['acceptReward'];
					if($acceptReward) {
						foreach($data as $d => $dValue) {
							foreach($acceptReward as $kk => $value) {
								if($dValue['id'] == $value['id']) {
									$dValue['status'] = $value['status'];
									$data[$d] = $dValue;
								}
							}
						}	
					}
					$bossItem['acceptReward'] = $data;
					$bossInfo[$key] = $bossItem;
				}
			}
		}
		return $bossInfo;
	}
	
	static function getWithUID($uid) {
		$userBossItem = self::getOne(__CLASS__, $uid);
		if(!$userBossItem){
			$userBossItem = self::init($uid);
		}
		$userBossItem->unserializeProperty('bossInfo');
		return $userBossItem;
	}
	
	static function init($uid) {
		import('service.item.ItemSpecManager');
		$bosses = ItemSpecManager::singleton('default','activeMonster.xml')->getGroup('activeMonster');
		$bossInfo = array();
		foreach($bosses as $key => $bossItem) {
			$bossInfo[] = array('itemId' => $bossItem->id, 'bossRemainForces' => $bossItem->army, 'acceptReward' => null);
		}
		$userBossItem = new UserBossItem();
		$userBossItem->uid = $uid;
		$userBossItem->fightCount = 0;
		$dataXml = ItemSpecManager::singleton('default','item.xml')->getItem('activity_monster');
		import('service.item.ServerResetItem');
		$activityTime = ServerResetItem::getAttackBossActivityTime($dataXml->k1, $dataXml->k2, $dataXml->k3);
		$userBossItem->fightCountRecoverTime = $activityTime[2];
		$userBossItem->bossInfo = $bossInfo;
		$userBossItem->save();
		return $userBossItem;
	}
	
	public function save() {
		$this->time = time();
		$this->serializeProperty('bossInfo');
		parent::save();
		$this->unserializeProperty('bossInfo');
	}
}
?>