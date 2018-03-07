<?php
/**
 * LordItem
 * 
 * 君主属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class LordItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;				//用户uid
	protected $commandExp = 0;	//统率经验
	protected $attrPoint = 0;	//可分配属性点
	protected $economy = 0;		//暂时用作存储新年祝福语Id
	protected $construct = 0;	//君主建设
	protected $military = 0;	//君主军事
	protected $defense = 0;		//君主防御
	protected $pvphonor = 0;	//君主荣誉
	protected $message = '';	//君主留言
	protected $generalrankId = '9900'; //军衔id
	protected $rankgifttime=0;  //军衔领取奖励
	protected $generalRewardRankId='0';  //军衔领取到的当前军衔
	protected $activityReward = 1; //活动奖励id
	protected $allianceContr = 0; //个人消耗的联盟贡献，用于退盟时返还。
	protected $chessEndtime=0;  //新年祝福领取结束时间
	protected $isFirstAlliance; //联盟收人奖励是否已经记录过
	protected $firstAlliance;   //收人获取奖励时的联盟
	protected $saleBuyTimes = 0;   //累计的特购次数
	protected $saleBuyRewardId;   //特购领奖轮次Id
	protected $saleBuyTurns=0;   //领奖轮次
	protected $version=0;//程序版本号
	protected $leaguecd=0;//退盟冷却时间
	protected $donatedMoneyDaily=0; //联盟建筑每天捐献的银币，因每天捐献有限制
	protected $donatedTotalMoney;
	protected $lastLeague; //上次离开的联盟UID
	protected $leaveAllianceTime; //上次离开联盟的时间
	protected $buyGenPlaces = 0; //购买可携带的将军位
	
	
	public function getItems($uid)
	{
		$lordItem = self::getWithUID($uid);
		if(!isset($lordItem))
			$lordItem = $this->init($uid);
		else if($lordItem->generalRewardRankId==0){//修复军衔领取
			$lordItem->generalRewardRankId = $lordItem->generalrankId;
			//判断今日是否已领取
			if($lordItem->rankgifttime==0){
				$lordItem->rankgifttime = time();
			}
			$lordItem->save();
		}
		$item = self::getUpgradeExp($lordItem);
		//竞技场领奖CD
		import('service.item.ItemSpecManager');
		$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_time');
		import('service.item.ArenaItem');
		$arenaItem = ArenaItem::getWithUID($uid);
//		if($arenaItem && $arenaItem->endTime < time()){
//			$playerProfile = UserProfile::getWithUID($uid);
//			import('service.action.ArenaClass');
//			$arena = Arena::singletion($playerProfile);
//			$item['arenagiftTime']= $arenaItem->endTime;
//			$item['arenaReward']= $arena->getArenaRewardInfo();
//		}
		if($arenaItem){
			$item['arenacdTime']= $arenaItem->cd;
			$item['arenagiftTime']= $arenaItem->endTime+5;
		}else{
			$item['arenacdTime']= 0;
			$item['arenagiftTime']= null;
		}
		//获得军阶配置信息
		$xmlHonor = ItemSpecManager::singleton('default', 'generalRank.xml')->getGroup('generalRank');
		foreach ($xmlHonor as $obj)
		{
			$item["generalRankConf"][] = get_object_vars($obj);
		}
		//$item["generalRankConf"] = get_object_vars($xmlHonor);
		$item['chessItemId'] = $lordItem->economy;
		$item['saleBuyTimes'] = $lordItem->saleBuyTimes;
		import('util.cache.XCache');
		XCache::singleton()->setKeyPrefix('IK2');
		$time = XCache::singleton()->get('chessEndtime'.$uid);//倒计时
		if($time){
			$item['chessEndtime'] = $time+5;	
		}else{
			$tempTime = 10;
			XCache::singleton()->set('chessEndtime'.$uid,$tempTime,24*3600);
			$item['chessEndtime'] = $tempTime+5;
		}
		$item['donatedMoneyDaily'] = $lordItem->donatedMoneyDaily;
		$data[] = $item;
		return $data;
	}
	
	/**
	 * 初始化
	 * @param unknown_type $userUid
	 */
	static function init($userUid){
		$lordItem = new self;
		$lordItem->uid = $userUid;
		import('service.item.ItemSpecManager');
		$xmlDataConfig = ItemSpecManager::singleton('default', 'item.xml')->getItem('game_version');
		$lordItem->version = $xmlDataConfig->k1;
		$lordItem->generalRewardRankId = '9900';
		$lordItem->rankgifttime = time();
		$lordItem->save();
		return $lordItem;
	}
	
	/**
     +----------------------------------------------------------
     * 升级军衔
     +----------------------------------------------------------
     * @method updateGeneralRank
     * @access public
     * @param $generalRankId 用户将要升级的军衔id
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function updateGeneralRank($ItemXml){
		$nowId = intval($ItemXml->id) - 1;
		if(intval($this->generalrankId) != $nowId){
			//越级升级
			return "err1";
		}
		if($this->pvphonor < $ItemXml->need){
			//荣誉值不够
			return "err2";
		}
		$this->generalrankId = $ItemXml->id;
		//$this->rankgifttime = 0;
		
		$this->save();
		
		return array('generalrankId' => $this->generalrankId);
	}
	/**
     +----------------------------------------------------------
     * 军衔每日gift时间更新
     +----------------------------------------------------------
     * @method updateGeneralRank
     * @access public
     * @param $generalRankId 用户将要升级的军衔id
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function updateRankGiftGetTime(){
		if($this->rankgifttime > 0 && date('Y-m-d',$this->rankgifttime)!= date('Y-m-d')){//首先判断每日的奖励是否领取
			$this->rankgifttime = time();
			$result['rankgifttime'] = $this->rankgifttime;
			if($this->generalRewardRankId<9900)
				$this->generalRewardRankId = $this->generalrankId;
			$result['generalRewardRankId'] = $this->generalRewardRankId;
			$this->save();
		}else if($this->generalRewardRankId+0<$this->generalrankId+0){//判断未领取
			if($this->generalRewardRankId<9900)
				$this->generalRewardRankId = $this->generalrankId;
			else
				$this->generalRewardRankId +=1; 
			$this->save();
			$result['generalRewardRankId'] = $this->generalRewardRankId;
			$result['rankgifttime'] = $this->rankgifttime;
		}else{
			$result = false;
		}		
		return $result;
	}
	
	
	
	static function getUpgradeExp($lordItem)
	{
		$data = $lordItem->asArray();
		$user = UserProfile::getWithUID($lordItem->uid);//UserFactory::singleton()->get($lordItem->uid);
		import('service.item.ItemSpecManager');
		//TODO ABTest
		$roleFileName = 'role.xml';
// 		if ($user->test == '4')
// 		{
// 			$roleFileName = 'role_4.xml';
// 		}
		$xml = ItemSpecManager::singleton('default',$roleFileName)->getItem($user->level + 2000);
		$data['upgradeExp'] = $xml->player_exp;
		
		$xmlRole = ItemSpecManager::singleton('default', 'generalRank.xml')->getItem($lordItem->generalrankId);
		$data['generalNum'] = $xmlRole->have_gnum; //玩家可以拥有的将军数量
		if($xml->exppool){
			$poolLimit = $xml->exppool;
			if($user->league){
				import('service.action.ScienceClass');
				$poolEffect = Science::singleton($user)->getScienceWithEffectId(111);	
				if($poolEffect){
					$poolLimit = $poolLimit*(1+$poolEffect['value']/100);
				}		
			}
			$data['poolLimit'] = $poolLimit;
			if($lordItem->attrPoint > $poolLimit){
				$lordItem->attrPoint = $poolLimit;
				$lordItem->save();
				$data['attrPoint'] = $poolLimit;
			}
		}
		//取得当前主将level的所有升级经验
		$generalExpList = Array();
		for($i=1;$i<=$user->level;$i++){
			//TODO ABTest
			$roleFileName = 'role.xml';
// 			if ($user->test == '4')
// 			{
// 				$roleFileName = 'role_4.xml';
// 			}
			$xmlItem = ItemSpecManager::singleton('default',$roleFileName)->getItem($i + 2000);
			$generalExpList[] = $xmlItem->general_exp;
		}
		$data['generalExpList'] = $generalExpList;
//		else 
//			$data['poolLimit'] =  100000000;
		$data['nextGeneralNums'] = $data['nextUserLevel'] = null;
		$xmlRoles = ItemSpecManager::singleton('default', 'generalRank.xml')->getGroup('generalRank');
		foreach ($xmlRoles as $role){
			if($role->have_gnum > $data['generalNum']){
				$data['nextGeneralNums'] = $role->have_gnum;
				$data['nextUserLevel'] = $role->id;
				break;
			}
		}
		return $data;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		$res = self::getOne(__CLASS__, $uid);
		parent::setCacheValue($cachekey, $res);
		return $res;
	}
	
	public function save(){
		parent::save();
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
	}
	/**
	 * 获得军衔
	 */
	static function getField($uid, $field) {
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select {$field} from lord where uid = '{$uid}'";
		$res = $mysql->execResult($sql);
		return $res[0][$field];
	}
}
?>