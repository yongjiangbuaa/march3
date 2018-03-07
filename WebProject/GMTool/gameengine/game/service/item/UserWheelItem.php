<?php
/**
 * 用户转盘抽奖
 */
import('persistence.dao.RActiveRecord');
class UserWheelItem extends RActiveRecord {
	protected $rndAwardList;  //用户随机奖励表
	protected $luckyValue; //幸运值
	protected $status; //cd和free状态
	protected $firstFlag; //转盘B和C首次使用连转功能时，额外获得特殊奖励
	protected $obtainAward; //获得的奖品
	protected $moneyCount;//每日银币转盘次数，00:00重置
	protected $time;//记录重置时间
	
	public static function getWithUID($uid) {
		$userWheelItem = self::getOne(__CLASS__, $uid);
		if(!$userWheelItem){
			$userWheelItem = self::init($uid);
		}
		$userWheelItem->unserializeProperty('rndAwardList');
		$userWheelItem->unserializeProperty('obtainAward');
		$userWheelItem->unserializeProperty('firstFlag');
		$userWheelItem->unserializeProperty('status');
		if(date('y-m-d',$userWheelItem->time)!=date('y-m-d',time())){//跨天时，重置银币抽奖次数
			$userWheelItem->time = time();
			$userWheelItem->moneyCount = 0;
		}
		return $userWheelItem;
	}
	
	public static function init($uid) {
		$userWheelItem = new UserWheelItem();
		$userWheelItem->uid = $uid;
		import('service.action.WheelClass');
		$user = UserProfile::getWithUID($uid);
		$rndAwardList = Wheel::getInstance()->initAwardList('all', $user->level);
		$userWheelItem->rndAwardList = $rndAwardList;
		$luckyMaxValue = ItemSpecManager::singleton('default', 'item.xml')->getItem('activity_wheel')->k1;
		if(!$luckyMaxValue) $luckyMaxValue = 10000;
		$userWheelItem->luckyValue = mt_rand(1, $luckyMaxValue);
		$status = array();
		$currTime = time();
		foreach($rndAwardList as $wheelId => $awardList) {
			$wheelXml = ItemSpecManager::singleton('default','wheels.xml')->getItem($wheelId);
			$status[$wheelId] = array('cd' => $currTime + $wheelXml->freetime, 'freeFlag' => 1);
		}
		$userWheelItem->status = $status;
		$userWheelItem->firstFlag = null;
		$userWheelItem->save();
		return $userWheelItem;
	}
	
	public function getItems($uid){
		$res = array();
		$userWheelItem = self::getWithUID($uid);
		import('service.action.WheelClass');
		$wheelWeekAwardTime = time();
		$weekItem = Wheel::getInstance()->getWeekAwardItem($wheelWeekAwardTime);
		$user = UserProfile::getWithUID($uid);
		self::addWeekItem($userWheelItem, $weekItem->id, $user);
		$data = array();
		$data['rndAwardList'] = self::encapAwardList($userWheelItem->rndAwardList, $data);
		$data['luckyValue'] = $userWheelItem->luckyValue;
		$data['status'] = $userWheelItem->status;
		$data['firstFlag'] = $userWheelItem->firstFlag;
		$data['itemId'] = $uid;
		$data['obtainAward'] = $userWheelItem->obtainAward;
		$data['moneyCount'] = $userWheelItem->moneyCount;
		$goodsInfo = array();
		if($weekItem->type == 2) {
			import("service.action.CalculateUtil");
			$reward = CalculateUtil::getInfoByRewardId($weekItem->itemid);
		} else {
			$goodsInfo = self::getGoodsInfo($weekItem->itemid);
		}
		$data['wheel_scroll_notice'] = Wheel::getScrollCache();
		$data['weekItem'] = array('id' => $weekItem->id, 'itemId' => $weekItem->itemid, 'type' => $weekItem->type, 'reward' => $reward, 'color' => $weekItem->color, 'goodsInfo' => $goodsInfo, 'time' => $wheelWeekAwardTime);
		$res[0] = $data;
		return $res;
	}
	
	static function addWeekItem($userWheelItem, $weekId, $user) {
		$awardList = $userWheelItem->rndAwardList;
		$goldWheel = $awardList['130022'];
		if($goldWheel) {
			foreach($goldWheel as $key => $goldItem) {
				if($goldItem['id'] == $weekId) {
					return $userWheelItem;
				}
			}
			$obtainAward = $userWheelItem->obtainAward;
			$goldNoAward = $obtainAward['130022'];
			if($goldNoAward) {
				return $userWheelItem;
			}
			import('service.action.WheelClass');
			$newGoldWheelAward = Wheel::getInstance()->initAwardList('130022', $user->level);
			$awardList['130022'] = $newGoldWheelAward['130022'];
			$userWheelItem->rndAwardList = $awardList;
			$userWheelItem->save();
			return $userWheelItem;
		}
	}
	
	static function encapAwardList($rndAwardList, &$data) {
		import('service.item.ItemSpecManager');
		import("service.action.CalculateUtil");
		$wheelData = array();
		foreach($rndAwardList as $wheelId => $awardList) {
			$wheelXml = ItemSpecManager::singleton('default','wheels.xml')->getItem($wheelId);
			$wheelData[] = array('wheelId' => $wheelId, 'costtype' => $wheelXml->costtype, 
								'costgold' => $wheelXml->costgold, 'cost' => $wheelXml->cost, 
								'morecost' => $wheelXml->morecost, 'refresh' => $wheelXml->refresh,
								'cutcost' => $wheelXml->cutcost, 'cutmax' => $wheelXml->cutmax, 'firstgift' => $wheelXml->firstgift);
			foreach($awardList as $key => $awardItem) {
				$awardItemXml = ItemSpecManager::singleton('default','wheels.xml')->getItem($awardItem['id']);
				$awardItem['itemId'] = $awardItemXml->itemid;
				$awardItem['type'] = $awardItemXml->type;
				if($awardItemXml->type == 2) {
					$awardItem['reward'] = CalculateUtil::getInfoByRewardId($awardItemXml->itemid);
				} else {
					$awardItem['goodsInfo'] = self::getGoodsInfo($awardItemXml->itemid);
				}
				$awardItem['color'] = $awardItemXml->color;
				$awardItem['num'] = $awardItemXml->num;
				$awardItem['roll'] = $awardItemXml->roll;
				$awardList[$key] = $awardItem;
			}
			$rndAwardList[$wheelId] = $awardList;
		}
		$data['wheelConfig'] = $wheelData;
		return $rndAwardList;
	}
	
	static function getGoodsInfo($goodsId) {
		$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($goodsId);
		for($e = 1; $e < 5; $e++){
			if (isset($xmlGoods->{effect.$e}))
			{
				$effectArr['effect'.$e] = $xmlGoods->{effect.$e};
				$effectArr['value'.$e] = $xmlGoods->{value.$e};
			}
		}
		if (isset($effectArr)) {
			$tempArr = array_merge(array('itemId' => $goodsId, 'color' => $xmlGoods->color, 'require_level'=>$xmlGoods->require_level, 'type' => $xmlGoods->type),$effectArr);
		}
		else {
			$tempArr = array('itemId' => $goodsId, 'color' => $xmlGoods->color, 'require_level'=>$xmlGoods->require_level, 'type' => $xmlGoods->type);
		}
		return $tempArr;
	}
	
	static function encapWheelAwardList($wheelAwardList, $isChangeRoll=false) {
		import('service.item.ItemSpecManager');
		import("service.action.CalculateUtil");
		foreach($wheelAwardList as $key => $awardItem) {
			$awardItemXml = ItemSpecManager::singleton('default','wheels.xml')->getItem($awardItem['id']);
			$awardItem['type'] = $awardItemXml->type;
			if($awardItemXml->type == 2) {
				$awardItem['reward'] = CalculateUtil::getInfoByRewardId($awardItemXml->itemid);
			} else {
				$awardItem['goodsInfo'] = self::getGoodsInfo($awardItemXml->itemid);
			}
			$awardItem['itemId'] = $awardItemXml->itemid;
			$awardItem['color'] = $awardItemXml->color;
			$awardItem['num'] = $awardItemXml->num;
			if($isChangeRoll) {
				$awardItem['roll'] = $awardItemXml->roll1;
			} else {
				$awardItem['roll'] = $awardItemXml->roll;
			}
			$awardItem['notice'] = $awardItemXml->notice;
			$wheelAwardList[$key] = $awardItem;
		}
		return $wheelAwardList;
	}
	
	public function save() {
		$this->serializeProperty('rndAwardList');
		$this->serializeProperty('obtainAward');
		$this->serializeProperty('firstFlag');
		$this->serializeProperty('status');
		parent::save();
		$this->unserializeProperty('rndAwardList');
		$this->unserializeProperty('obtainAward');
		$this->unserializeProperty('firstFlag');
		$this->unserializeProperty('status');
	}
}
?>