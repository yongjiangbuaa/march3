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
class QFInviteItem extends RActiveRecord {
	protected $uid;//所属用户
	protected $lastUpdate;//上一次刷新时间
	protected $dailyInvite = 0;//每天邀请数量
	protected $totalInvite = 0;//一共邀请数量
	protected $dailyRewardList;//每天已领取奖励ID  4601002,4601003,4601004,
	protected $rewardList;//已领取奖励ID  4601002,4601003,4601004,
	
	
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		$currentTime = time();
		if(!$res){
			$res = new self();
			$res->uid = $uid;
			$res->lastUpdate = $currentTime;
			$res->save();
		}else{
			//每日重置次数
			if(date('Y-m-d',$res->lastUpdate) != date('Y-m-d',$currentTime)){
				$res->dailyInvite = 0;
				$res->dailyRewardList = '';
				$res->lastUpdate = $currentTime;
				$res->save();
			}
		}
		return $res;
	}
 	/**
 	 * 获得指定uid的所有建筑
 	 * @param String $uid
 	 */
 	public function getItems($uid)
	{
		$QFInviteItem = self::getWithUID($uid);
		$rewardList = array();
		$rewardList = explode(',', $QFInviteItem->rewardList);
		$dailyRewardList = explode(',', $QFInviteItem->dailyRewardList);
		$quest = array();
		import('service.item.ItemSpecManager');
		import('service.action.CalculateUtil');
		$xmlGroup = ItemSpecManager::singleton('default','friendquest.xml')->getGroup('friendquest');
		foreach ($xmlGroup as $xml){
			if(in_array($xml->id, $rewardList) || in_array($xml->id, $dailyRewardList)){
				$xml->rewarded = true;
			}
			$xml->reward = CalculateUtil::getInfoByRewardId($xml->reward);
			$quest[] = $xml;
		}
		$QFInviteItem->quest = $quest;
		$data[0] = $QFInviteItem;
		return $data;
	}
}
?>