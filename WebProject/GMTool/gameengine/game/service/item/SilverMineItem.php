<?php
/**
 * TeamItem
 * 队伍模型
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class SilverMineItem extends RActiveRecord {
	protected $uid;       //user id
	protected $level = 1;   //current mine level
	protected $curExp = 0;  //current mine exp
	protected $exploreCount = 0; //user explore mine count
	protected $buyTimes = 0;       //user buy mine times
	protected $helpRewardCount = 0;
	protected $upgradeTime;       //user obtain exp to upgrade mine level time
	protected $rewardLevel = 0; //升级奖励的领取状态
	
	const TABLE = 'silvermine';
	const MINEBUTTON = '13';
	
	public function __construct() {
		parent::__construct();
	}
	
	public static function getItems($uid) {
		$buttonIndexArray = explode(',',UserProfile::getWithUID($uid)->buttonIndex);
		if(in_array(SilverMineItem::MINEBUTTON, $buttonIndexArray)) {
			$userLevel = UserProfile::getWithUID($uid)->level;
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton();
			$res = $mysql->get(SilverMineItem::TABLE, array('uid' => $uid), 'level,curExp,exploreCount,buyTimes,helpRewardCount,upgradeTime,rewardLevel');
			if(!$res) {
				return SilverMineItem::init($uid,$userLevel);
			}
			return SilverMineItem::fillXMLData($res[0],$userLevel);
		}
	}
	
	private static function fillXMLData($silverMineItem,$userLevel) {
		$roleXML = ItemSpecManager::singleton('default','role.xml')->getItem($silverMineItem['level'] + 2000);
		$silverMineItem['mine_exp'] = $roleXML->mine_exp;
		import("service.action.CalculateUtil");
		if($silverMineItem['rewardLevel'] == 0) {
			$rewardLevel = 1;
		} else {
			$rewardLevel = $silverMineItem['rewardLevel'];
		}
		$acceptReward = ItemSpecManager::singleton('default','role.xml')->getItem($rewardLevel + 2000)->minelv_reward;
		$silverMineItem['minelv_reward'] = CalculateUtil::getInfoByRewardId($acceptReward);
		$silverMineItem['discover_exp1'] = $roleXML->discover_exp1;
		$mineReward = ItemSpecManager::singleton('default','role.xml')->getItem($userLevel + 2000)->mine_reward;
		$silverMineItem['mine_reward'] = $mineReward;
		$silverMineItem['mine_face'] = $roleXML->mine_face;
		$data[0]['itemId'] = '';
		foreach($silverMineItem as $key => $value) {
			$data[0][$key] = $value;
		}
		return $data;
	}
	
	public static function init($uid,$userLevel) {
		$silverMineItem = new SilverMineItem();
		$silverMineItem->uid = $uid;
		$silverMineItem->save();
		$data['level'] = 1;
		$data['curExp'] = 0;
		$data['exploreCount'] = 0;
		$data['buyTimes'] = 0;
		$data['helpRewardCount'] = 0;
		$data['upgradeTime'] = 0;
		$data['acceptLevelupReward'] = 0;
		$data['rewardLevel'] = 0;
		return SilverMineItem::fillXMLData($data,$userLevel);
	}
	
	public static function resetDaily($user) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "update silvermine set exploreCount=0, buyTimes=0, helpRewardCount=0 where uid = '{$user->uid}' ";
		$mysql->execute($sql);
	} 
	
	public static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		return $res;
	}
}
?>