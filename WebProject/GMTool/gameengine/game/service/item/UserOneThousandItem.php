<?php
/**
 * UserOneThousandItem.php
 * 
 * 列岛远征useronethousand对应数据表Item
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class UserOneThousandItem extends RActiveRecord {
	
	protected $uid;//用户uid
	protected $currRecord; //当前挑战的关卡记录
	protected $hisMaxRecord; //历史挑战的最高关卡记录
	protected $hisMaxRecordTime; //最高关通过时的时间戳
	protected $challengeTimes; //今日挑战的次数
	protected $isAcceptRewardTimes; //是否领取额外赠送的次数
	protected $buyTimes; //今日购买的次数
	protected $failTimes; //今日是失败的次数
	protected $auto;
	protected $roundReward;
	protected $emailReward;
	protected $time; //更新的时间
	
	const ONETHOUSANDBUTTON = '16';
	const BASEID = 4000;
	const FIRSTNPCID = 4001;
	const DISPLAYCOUNTPERCONTEXT = 4;
	
	public function __construct($uid=null, $currRecord=null) {
		parent::__construct();
		$this->uid = $uid;
		$this->currRecord = $currRecord;
	}

	public function getItems($uid){
		$user = UserProfile::getWithUID($uid);
		$buttonIndexArray = explode(',', $user->buttonIndex);
		if(!in_array(self::ONETHOUSANDBUTTON, $buttonIndexArray)) {
			return;
		}
		$data = array();
		$userOneThousandItem = self::getWithUID($uid);
		if(!$userOneThousandItem) {
			$userOneThousandItem = new UserOneThousandItem($uid, self::BASEID);
			$userOneThousandItem->save();
		} 
		return self::encapData($user, $userOneThousandItem);
	}
	
	public function getNewItems($uid, $itemUids){
		return $this->getItems($uid);
	}
	
	public static function encapData($user,$userOneThousandItem) {
		$i = 0;
		$data[$i]['itemId'] = $user->uid;
		$data[$i]['currRecord'] = $userOneThousandItem->currRecord;
		$data[$i]['challengeTimes'] = $userOneThousandItem->challengeTimes;
		$data[$i]['buyTimes'] = $userOneThousandItem->buyTimes;
		$data[$i]['failTimes'] = $userOneThousandItem->failTimes;
		$data[$i]['hisMaxRecord'] = $userOneThousandItem->hisMaxRecord;
		$userOneThousandItem->unserializeProperty('roundReward');
		$data[$i]['totalReward'] = $userOneThousandItem->roundReward;
		$last = self::getLast();
		$data[$i]['maxCheckPoints'] = $last;
		import('service.item.ItemSpecManager');
		$thousandNumItem = ItemSpecManager::singleton('default','item.xml')->getItem('onethousand_num');
		import('service.action.CalculateUtil');
		$vipEffect = CalculateUtil::getVipEffect($user);
		$limit = $thousandNumItem->k4 - $vipEffect['eft21']['value'];
		$data[$i]['cd'] = $userOneThousandItem->time + $limit;
		if($userOneThousandItem->currRecord == self::FIRSTNPCID) {
			$from = self::FIRSTNPCID;
			$to = self::FIRSTNPCID + self::DISPLAYCOUNTPERCONTEXT - 1;
		} elseif($userOneThousandItem->currRecord > $last - 2) {
			$from = $last - self::DISPLAYCOUNTPERCONTEXT + 1;
			$to = $last;
		} elseif($userOneThousandItem->currRecord > self::FIRSTNPCID && $userOneThousandItem->currRecord <= $last - 2) {
			$before = $userOneThousandItem->currRecord - 1;
			$from = $userOneThousandItem->currRecord;
			$to = $userOneThousandItem->currRecord + self::DISPLAYCOUNTPERCONTEXT - 2;
		}
		import('service.item.ItemSpecManager');
		$npcs = array();
		if($before) {
			$thousandXmlItem = ItemSpecManager::singleton('default', 'onethousand.xml')->getItem($before);
			$npcs[] = self::resNpcList($thousandXmlItem, $user->level);
		}
		if($from && $to) {
			for($npcId=$from; $npcId<=$to; $npcId++) {
				$thousandXmlItem = ItemSpecManager::singleton('default', 'onethousand.xml')->getItem($npcId);
				$npcs[] = self::resNpcList($thousandXmlItem, $user->level);
			}
		}
		$data[$i]['npcs'] = $npcs;
		import('service.action.WorldClass');
		$allianceRelicRewardTimes = World::getAllianceRelicReward($user->league, 1);
		$data[$i]['allianceRelicRewardTimes'] = $allianceRelicRewardTimes;
		return $data;
	}
	
	public static function getLast() {
		import('service.item.ItemSpecManager');
		$thousandXmlGroup = ItemSpecManager::singleton('default', 'onethousand.xml')->getGroup('onethousand', true);
		$lastItem = array_pop($thousandXmlGroup);
		return $lastItem->id;
	}

	private static function resNpcList($xmlThousand, $userLevel){
		import('service.action.CalculateUtil');
		import('service.item.ItemSpecManager');
		$xmlBattle = ItemSpecManager::singleton('default', 'battle.xml')->getItem($xmlThousand->battle);
		$armyId = CalculateUtil::getArmsOrRewardForFight($userLevel, $xmlBattle);
		import('service.action.LoadXMLUtil');
		$xmlArmy = LoadXMLUtil::loadArmy($armyId);
		import('service.action.FormationClass');
		$matrixItem = Formation::singleton()->getFormation($armyId,2);
		for ($i = 1; $i < 4; $i++)
		{
			for ($j = 1; $j < 4; $j++)
			{
				$pos = $i * 3 - 3 + $j;
				if ($matrixItem->generalList['pos'.$pos] != null)
				{
					$matrixArms[$pos]['arms'] = $matrixItem->generalList['pos'.$pos]['arms'];
					if($matrixItem->generalList['skill'.$pos])
					{
						$matrixArms[$pos]['skill'] = $matrixItem->generalList['skill'.$pos];
					}
					else 
					{
						$armsItem = ItemSpecManager::singleton('default','arms.xml')->getItem($matrixArms[$pos]['arms']);
						$matrixArms[$pos]['skill'] = $armsItem->skill_id;
					}
				}
			}
		}
		import("service.action.CalculateUtil");
		$npcRewardDate = CalculateUtil::getInfoByRewardId($xmlThousand->reward);
		$result = array(
			'id' => $xmlThousand->id,
			'matrixArms' => $matrixArms,
			'army_type' => $xmlBattle->arm_type,
			'reward' => $npcRewardDate,
			'soldier_lose' => $xmlBattle->soldier_lose,
		);
		return $result;
	}
	
	public static function getWithUID($uid) {
		return self::getOne(__CLASS__, $uid);
	}
	
	public static function getOneThousandOrder($type, $userLevel, $user=null){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$totalOrderSQL = "select u.uid, u.name, u.level, uot.hisMaxRecord, ld.commandExp from useronethousand uot 
					left join userprofile u on uot.uid = u.uid left join lord ld on uot.uid = ld.uid 
					where uot.hisMaxRecord is not null
					order by uot.hisMaxRecord desc, uot.hisMaxRecordTime asc limit 50";
		$levelOrderSQL = "select u.uid, u.name, u.level, uot.hisMaxRecord, ld.commandExp from useronethousand uot 
					left join userprofile u on uot.uid = u.uid left join lord ld on uot.uid = ld.uid 
					where u.level = {$userLevel} and uot.hisMaxRecord is not null
					order by uot.hisMaxRecord desc, uot.hisMaxRecordTime asc limit 20";
		$data = array();
		switch($type) {
			case 'totalOrder':
				$data = $mysql->execResultWithoutLimit($totalOrderSQL);
				$data = self::handleOrder($data);
				break;
			case 'levelOrder':
				$data = $mysql->execResultWithoutLimit($levelOrderSQL);
				$data = self::handleOrder($data);
				break;
			case 'bothOrder':
				$totalOrder = $mysql->execResultWithoutLimit($totalOrderSQL);
				$data[] = self::handleOrder($totalOrder);
				$levelOrder = $mysql->execResultWithoutLimit($levelOrderSQL);
				$data[] = self::handleOrder($levelOrder);
				break;
		}
		if($user) {
			import('service.action.WorldClass');
			$data['userOneThousandRewardTimes'] = World::getAllianceRelicReward($user->league, 1);
		}
		return $data;
	}
	
	private static function handleOrder($data) {
		if($data) {
			import('service.action.GeneralClass');
			$general = General::singleton();
			foreach($data as $key => $value) {
				$value['order'] = $key + 1;
				if($value['hisMaxRecord']) {
					$value['hisMaxRecord'] -= self::BASEID;
				}
				$general->setUser(UserProfile::getWithUID($value['uid']));
				$value['fightPower'] = $general->getUserFightPower();
				$data[$key] = $value;
			}
		}
		return $data;
	}
	
	static function checkIsInOneThousand($uid) {
		$userOneThousandItem = self::getWithUID($uid);
		$thousandXml = ItemSpecManager::singleton('default','item.xml')->getItem('onethousand_num');
		$currTime = time() + 5;
		if($currTime >= $userOneThousandItem->time && $currTime < ($userOneThousandItem->time + $thousandXml->k4 + 2)) {
			return false;
		}
		return true;
	}
	
	public function save() {
		$this->serializeProperty('roundReward');
		$this->serializeProperty('emailReward');
		parent::save();
		$this->unserializeProperty('roundReward');
		$this->unserializeProperty('emailReward');
	}
}
?>