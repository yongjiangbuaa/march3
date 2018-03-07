<?php
import('persistence.dao.RActiveRecord');
import('persistence.dao.RActiveRecordDAO');
/**
 * UserProfile
 * 
 * user profile
 * 
 * 用户模型
 * 
 * @Entity
 * @package user
 */
final class UserProfile extends RActiveRecord
{	
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 */
	protected $uid = null;
	
	protected $platformAddress = null;
	/**
	 * @Index
	 */
 	protected $name;			//角色名 --不能重复
 	protected $level = 1;		//级别
 	protected $vip = 0;			//vip等级
 	protected $vip_finish_time; //vip结束时间
 	protected $accept_vipgift_status; //vip领奖状态：0,可以领取;1,不可以领取
 	protected $yellowvip_firstgift_status; //腾讯黄钻用户的新手礼包领取状态:0,不是黄钻或者未领取;1,已领取
 	protected $yellow_vip_status; //关于用户腾讯黄钻的状态:0,不是黄钻;1,普通黄钻;2,年费黄钻
 	protected $yellow_vip_level; //用户腾讯黄钻的等级；0，不是黄钻没有等级
 	protected $accept_yvipgift_status; //yellow vip腾讯黄钻用户每日领奖状态：0,当日可以领取;1,当日不可以领取
 	protected $first_pay_status; //关于首次充值奖励的状态:0,没有充值;1,已首次充值，但未领取首充奖励;2,已首次充值且领取首充奖励
 	protected $pic;				//头像图片
 	protected $picTimes;	//头像更换次数
 	protected $gender;			//性别； 1:男 2:女
    protected $user_gold ;		//元宝
    protected $system_gold ;	//点券
    protected $active_point = 0;    //活跃值
	protected $x;				//世界坐标x
	protected $y;				//世界坐标y
	protected $league;			//联盟
	protected $country = 0;		//阵营
	protected $registerTime;    //用户注册时间,timestamp
	protected $date = 931859980;			//登陆日期，用于每日零点刷新
	protected $forcibly_forces = 0;	//当天强征士兵次数
	protected $forcibly_resource = 0;	//当天强征资源次数
	protected $occupyCityTimes;	//城市占领次数
	protected $occupyResourceTimes;  //资源点占领次数
	protected $plunderTimes;	//资源掠夺次数
	protected $destroyTimes;    //城市摧毁次数
	protected $ownResource;		//占领的资源点数量
	protected $maxResource;		//玩家到达过的最大资源带
	protected $dailyFlushTimes; //日常任务免费刷新次数
	protected $dailyCompleteTimes; //日常任务每日完成次数
	protected $allianceFlushTimes; //联盟任务免费刷新次数
	protected $dailyFlushTime;  //日常任务下次刷新时间
	protected $pveTimes;		//每日pve挑战次数
	protected $extraPveTimes;	//额外pve挑战次数,包括购买的次数
	protected $pveRefreshTime = 0;	//上一次更新额外pve挑战次数
	protected $buyPveTimes = 0;		//每日购买pve挑战次数
	protected $onlineGift;		//在线礼包编号
	protected $giftEndTime;		//在线礼包领取倒计时 
	protected $buttonIndex = "1,3";     //显示的btn值,默认为背包
	protected $speakingForbid = 0; 	//用户禁言结束时间，默认为0
	protected $seize = 0;     		//用户封号结束时间，默认为0 
	protected $tabIndex = "10,20,40,71,80";
	protected $onLoadKey;			//用户登陆标示
	protected $lastLoadTime = 0;	//上一次读取时间
	protected $playerOnlineTime = 0;		//用户总在线时长
	protected $island = 1;     //副岛开启
	protected $gmFlag = 0;	//是否为GM 1为GM
	protected $goldOffered = 0;//发放金币的账号
	protected $onlineVersion = 0;
	protected $gmShow = 0;//聊天窗口显示为GM
	protected $arenaTimes;     //竞技场挑战次数
	protected $buyArenaTimes = 0;	//购买竞技场次数
	protected $leagueFlushTimes = 0;	//联盟任务刷新次数
	protected $skillPoint = 0;		//战略点数
	protected $serverMailTime = 0;	//系统邮件收件时间
	protected $moveDistanceDelta; //团战移动距离增减
	protected $fixTime = 0;	//数据修复时间
	protected $test = 0; //ABTest状态
	protected $rewardFlag = 0;//返回奖励状态记录 低三位为2013五一活动标记，第四位为qq面板添加领奖标记
	protected $inviteFlag;	//受邀请进入应用的玩家
	protected $contractId;	//活动ID
	protected $source; //玩家来源
	protected $registerBefore;	//是否在其他服注册过
	
	//blob
	protected $activeReward;    //领取活跃奖励记录array(0 => array('reward' => itemId, 'value' => 所需活跃值, 'status' => 1))
	protected $teamTimes= array();//团长挑战次数 key 活动ID val 活动次数
	protected $buyTeamTimes = array();//每日购买团长次数
	protected $resetPveTimes = array();//重置经典ＰＶＥ次数
	/**
	 * <b>uid getter</b>
	 * 
	 * <b>获取uid属性的方法</b>
	 * 
	 * @return string
	 */
	public function getUid(){
    	return $this->get('uid');
    }
    
    /**
	 * <b>uid setter</b>
	 * 
	 * <b>设置uid属性的方法</b>
	 * 
	 * @param string $uid
	 */
    public function setUid($uid){
    	$this->set('uid', $uid);
    }
    
    /**
	 * <b>platformAddress getter</b>
	 * 
	 * <b>获取platformAddress属性的方法</b>
	 * 
	 * @return string
	 */
    public function getPlatformAddress(){
    	return $this->get('platformAddress');
    }
    
    public function getPlatformId(){
    	$platformAddress = $this->get('platformAddress');
    	$pos = strpos($platformAddress,'_');
    	$platformId = substr($platformAddress,$pos+1);
    	return $platformId; 
    }
    public function getLang()
    {
    	$platformId = $this->getPlatformId();
    	$pos = strpos($platformId,'_');
    	$lang = substr($platformId,$pos+1,2);
    	return $lang;
    }
    /**
	 * <b>platformAddress setter</b>
	 * 
	 * <b>设置platformAddress属性的方法</b>
	 * 
	 * @param string $platformAddress
	 */
    public function setPlatformAddress($platformAddress){
    	$this->set('platformAddress', $platformAddress);
    }
    
	public function changeOnlineVersion(){
// 		import('service.item.ItemSpecManager');
// 		$xmlDataConfig = ItemSpecManager::singleton('default', 'item.xml')->getItem('game_version');
// 		$this->onlineVersion = $xmlDataConfig->k2;
		$this->onlineVersion = 1001;//1大版本号必须刷新001小版本号可以不刷新
	}
	
	public function getCurrentVersion(){
// 		import('service.item.ItemSpecManager');
// 		$xmlDataConfig = ItemSpecManager::singleton('default', 'item.xml')->getItem('game_version');
// 		return $xmlDataConfig->k2;
		return 1001;
	}
	
	public function addTabIndex($newTab){
		$tabIndex = explode(',', $this->tabIndex);
		$open = explode(',', $newTab);
		$tabIndex = array_merge($tabIndex,$open);
		$tabIndex = array_unique($tabIndex);
		sort($tabIndex);
		$tabIndex = implode(',', $tabIndex);
		//防止多一个逗号的情况
		if(substr($tabIndex, 0, 1) == ','){
			$tabIndex = substr($tabIndex, 1);
		}
		if($this->tabIndex != $tabIndex){
			$this->tabIndex = $tabIndex;
			$this->save();
		}
	}
	
	public function addButtonIndex($newButton){
		$buttonIndex = explode(',', $this->buttonIndex);
		$open = explode(',', $newButton);
		$buttonIndex = array_merge($buttonIndex,$open);
		$buttonIndex = array_unique($buttonIndex);
		sort($buttonIndex);
		$buttonIndex = implode(',', $buttonIndex);
		//防止多一个逗号的情况
		if(substr($buttonIndex, 0, 1) == ','){
			$buttonIndex = substr($buttonIndex, 1);
		}
		if($this->buttonIndex != $buttonIndex){
			$this->buttonIndex = $buttonIndex;
			$this->save();
		}
	}
	
	
	/**
	 * <b>this mothod will be invoked when the user logged in</b>
	 * 
	 * <b>用户登陆时会调用此方法</b>
	 */
	public function onLogin(){
		//TODO
		$this->_fixData();
		//发送全服邮件
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$sql = "select * from servermail order by `startTime` asc";
		$result = $mysql->execute($sql);
		if ($result) {
			$current_time = time();
			while ($mail = mysql_fetch_assoc($result))
			{
				if($this->serverMailTime < $mail['startTime'] && $current_time >= $mail['startTime'] && $current_time <= $mail['endTime'] )
				{
					$this->serverMailTime = $mail['startTime'];
					$this->save();
					//加入联盟判断
					$addFlag = false;
					if($mail['league']){
						if($mail['league'] == $this->league){
							$addFlag = true;
						}
					}
					elseif($mail['registerTime'])
					{
						if($this->registerTime <= $mail['registerTime'])
							$addFlag = true;
					}
					elseif($this->level >= $mail['levelMin'] && $this->level <= $mail['levelMax']){
						$addFlag = true;
					}
					
					if($addFlag){
						import('service.item.MailItem');
						MailItem::addMail(null , $this, 1, $mail['title'], $mail['contents'], 0, $mail['startTime'], $mail['rewardId']);
						$mysql->add('servermaillog', array('uid'=>getGUID(),'user'=>$this->uid,'sendTime'=>$current_time,'level'=>$this->level,'mail'=>json_encode($mail)));
					}
				}
			}
		}
		if(!$this->activeReward){
			import('service.action.CalculateUtil');
			$this->activeReward = CalculateUtil::getActiveRewardArr();
		}
// 		//清楚物资上限//银币兵力上限不清除
// 		import('service.item.CityItem');
// 		import('service.item.ItemSpecManager');
// 		CityItem::deleteOverRescource($this->uid);
		//修改登陆标示
		$this->onLoadKey = md5($this->uid . microtime(true));
		$this->changeOnlineVersion();
		//日期改变
		if(date('Y-m-d',$this->date)!= date('Y-m-d')){
			$this->flush();
		}
		else 
		{
			$this->save();
		}
		//更新任务状态
		import('service.action.QuestClass');
		Quest::singleton($this)->updateQuestNums(null, 32, 1);
		import('util.cache.XCache');
		XCache::singleton()->setKeyPrefix('IK2');
		$this->giftEndTime = XCache::singleton()->get('giftEndTime'.$this->uid);//在线礼包领取倒计时
		$mysql->add('logindata', array('uid'=>getGUID(),'timeStamp'=>time(),'user'=>$this->uid,'ip'=>getIP()));
		
		//更新vip状态
		if($this->vip > 0 && $this->vip_finish_time < time()) {
			$this->vip = 0;
			$this->save();
		}
		if($this->vip > 0)
			Quest::singleton($this)->updateQuestNums(null, 49, 1);
		if($this->yellow_vip_status > 0)
			Quest::singleton($this)->updateQuestNums(null, 83, 1);

		$datePoint = strtotime(date('Y-m-d'));	
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();		
		$sql = "select count(1) as count from logindata where timeStamp >= '{$datePoint}' and user='{$this->uid}'";
		$data = $mysql -> execResultWithoutLimit($sql);
		if($data[0][count] == 1)
			$this->firstLogin = true;
		else 
			$this->firstLogin = false;
	}
	private function getZoneid(){
		$temp = explode('_', $this->platformAddress);
		return $temp[3];
	}
	private function getFixedTime(){
		$zoneid = $this->getZoneid();
		switch ($zoneid){
			case 1:	
			case 2:
				$fixedTime = 1377864000;//2013-05-30 10:30
				break ;
			default:
				$fixedTime = 1378198800;//2013-09-03 17:00:00
				break;
		}

		return $fixedTime;
	}
	public function _fixData(){
		//修复无法达到5级10级的bug
		if($this->level < 5)
		{
			import('service.item.QuestRecordItem');
			$questRecordItem = QuestRecordItem::getRecords($this->uid);
			$questList = $questRecordItem->questList;
			if($questList[4000080]){
				$this->level = 5;
				$this->save();
			}
		}elseif($this->level < 10){
			import('service.item.QuestRecordItem');
			$questRecordItem = QuestRecordItem::getRecords($this->uid);
			$questList = $questRecordItem->questList;
			if($questList[4000300]){
				$this->level = 10;
				$this->save();
			}
		}
		//登陆修复0点重置时还在征战导致军令为负数的问题
		$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_warnum');
		if($this->pveTimes > $this->extraPveTimes + $xmlDataConfig->k1 + 10){
			$this->pveTimes = 0;
			$this->save();
		}
		
		//修复通过等级获得的tabIndex和buttonIndex
		import('service.item.ItemSpecManager');
		$tutorialGroup = ItemSpecManager::singleton('default', 'tutorial2.xml')->getGroup('tutorial2');
		$open_button = '1,3';
		$open_tab = '10,20,40,71,80';
		for ($i = 1;$i<=$this->level;$i++){
			$tutorialXml = $tutorialGroup->{1200+$i};
			if(!$tutorialXml)
				continue;
			if($tutorialXml->open_button)
				$open_button .= ','.$tutorialXml->open_button;
			if($tutorialXml->open_tab)
				$open_tab .= ','.$tutorialXml->open_tab;
		}
		$this->addButtonIndex($open_button);
		$this->addTabIndex($open_tab);
		
		//首充奖励修复
		$fixedTime = 1373972880;
		if ($this->fixTime < $fixedTime) {
			$this -> fixTime = $fixedTime;
			$this -> save();
			if (2 == $this -> first_pay_status) {
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from general where ownerId = '{$this -> getUid()}' and itemId = 1290003";
				$cynthiaArray = $mysql->execResult($sql,1);
				if (!$cynthiaArray) {
					import('service.item.ItemSpecManager');
					$cynthiaXml = ItemSpecManager::singleton('default', 'general.xml')->getItem(1290003);
					import('service.action.GeneralClass');
					$generalClass = General::singleton($this);
					$cynthiaGeneralArray = $generalClass -> createOneGeneral(1290003, $cynthiaXml);
					$cynthiaGeneral = $generalClass->addGeneral($cynthiaGeneralArray, TRUE);
				}
			}
		}
		//替补将军任务修复
		$fixedTime = 1376236800;//2013-08-12 00:00:00
		if ($this->fixTime < $fixedTime) {
			$this->fixTime = $fixedTime;
			$this->save();
			if($this->level > 60){
				import('service.item.ItemSpecManager');
				$tutorialXml = ItemSpecManager::singleton('default', 'tutorial2.xml')->getItem(1260);
				if($tutorialXml->quest){
					import('service.action.LoadXMLUtil');
					import('service.action.QuestClass');
					$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$this)->getItem($tutorialXml->quest);
					Quest::singleton($this)->createQuest($xmlQuest,1);
				}
			}
		}
		
		$fixedTime = $this->getFixedTime();
		if($this->fixTime < $fixedTime)
		{
			$this->fixTime = $fixedTime;
			import('service.action.LoadXMLUtil');
			import('service.item.ItemSpecManager');
			$user_level = $this->level;
			$update_quest = true;
			// 		if($user_level > 31)
			// 		{
			// 			$user_level = 31;
			// 			$update_quest = false;
			// 		}
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton()->connect();
			$restoreXML = ItemSpecManager::singleton('default','restore.xml')->getItem($user_level);
			if (isset($restoreXML))
			{
				if ($update_quest)
				{
					//删除剧情任务
					$sql = "delete from quest where ownerId = '{$this->uid}' and type = 0";
					$mysql->execute($sql);
		
					import('service.action.QuestClass');
					//添加主线任务
					if(isset($restoreXML->mainQuest))
					{
						$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$this)->getItem($restoreXML->mainQuest);
						Quest::singleton($this)->createQuest($xmlQuest,1);
					}
					//添加支线任务
					if (isset($restoreXML->quest))
					{
						$questArr = explode(',', $restoreXML->quest);
						foreach ($questArr as $item)
						{
							$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$this)->getItem($item);
							Quest::singleton($this)->createQuest($xmlQuest,1);
						}
					}
				}
				//修复buttonindex
				if (isset($restoreXML->button))
				{
					$this->buttonIndex = $restoreXML->button;
					$this->save();
				}
				//修复buttonindex
				if (isset($restoreXML->tab))
				{
					$this->tabIndex = $restoreXML->tab;
					$this->save();
				}
				//建筑物修复
				if (isset($restoreXML->building))
				{
					import('service.action.ConstCode');
					$buildingArr = explode(',', $restoreXML->building);
					foreach($buildingArr as $key => $value)
					{
						$tmp = explode('_', $value);
						$buildingPos[$tmp[0]]['pos'] = $tmp[1];
						$buildingPos[$tmp[0]]['flag'] = 0;
					}					
					import('service.item.BuildingItem');
					$buildingResult = BuildingItem::getBuildingsByCityType($this->uid);
					$buildingItems = BuildingItem::to($buildingResult);
					foreach($buildingItems as $key => $buildingItem)
					{
						$buildingItem->pos = $buildingPos[$buildingItem->itemId]['pos'];
						$buildingPos[$buildingItem->itemId]['flag'] = 1;
						$buildingItem->save();
						if($buildingItem->pos == 0 && $buildingItem->itemId != 1301000)
							$buildingItem->remove();
					}
					
					foreach($buildingPos as $key => $value)
					{
						if($value['flag'])
							continue;
						$data[] = array('uid'=>getGUID(),'ownerId'=>$this->uid,'cityType'=>1,'pos'=>$value['pos'],'itemId'=>$key,'level'=>ConstCode::CITY_INIT_LEVEL,'trend'=>0,'finishTime'=>0,'lastUpdateTime'=>time());
					}
					if(count($data) > 0)
						$mysql->addBatch('building', $data);
				}
					
				//完成任务修复
				$questList = array();
				if (isset($restoreXML->completeQuest))
				{
					$completeList = explode(',', $restoreXML->completeQuest);
					foreach ($completeList as $item)
					{
						$questList[$item] = 1;
					}
				}
				import('service.item.QuestRecordItem');
				$questRecordItem = QuestRecordItem::getRecords($this->uid);
				$questRecordItem->questList = $questList;
				$questRecordItem->save();
				
				if(isset($restoreXML->army)){
					import('service.action.FormationClass');
					import('service.item.FormationItem');
					$generalList = FormationItem::getDefaultFormation($this->uid)->generalList;
					$formationList = explode(',', $restoreXML->army);
					Formation::singleton($this)->setUser($this);
					foreach ($formationList as $formationId)
					{
						$formationItem = Formation::singleton($this)->createNewFormation($formationId);
						if($formationItem)
						{
							$formationItem->generalList = $generalList;
							$formationItem->save();
						}
					}
				}
				//修复地格
				import('service.item.CityItem');
				$cityItem = CityItem::getWithUID($this->uid);
				$cityItem->groundIndex = array(0=>'1,2,3,4,5,6,7,11,8,9,10');
				$cityItem->save();
			}
			import('service.item.RecruitItem');
			$recruitItem = RecruitItem::getWithUID($this->uid);
			if($recruitItem){
				import('service.item.MailItem');
				$xmlEmail = ItemSpecManager::singleton('cn','item.xml')->getItem('7948');
				$title = $xmlEmail->description;
				$content = $xmlEmail->description1;
				$cardNum = ceil($recruitItem->point2 * 0.00045 + $recruitItem->point3 * 0.001 + $recruitItem->point4 * 0.0015 + $recruitItem->point5 * 0.1);
				$cardNum+=5;
				$rewardId = "item,8220102|rate,100000|number,200|item1,8200199|rate1,100000|number1,$cardNum";
				MailItem::addMail(null, $this, 1, $title,$content,0,null,$rewardId);
				$recruitItem->point2 = 0;// 蓝 军魂
				$recruitItem->point3 = 0;// 紫 军魂
				$recruitItem->point4 = 0;// 橙 军魂
				$recruitItem->point5 = 0;// 金 军魂
				$recruitItem->save();
			}
		}
	}
	/**
	 * <b>this mothod will be invoked when the user registered</b>
	 * 
	 * <b>用户注册时会调用此方法</b>
	 */
	public function onRegister(){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$mysql->add('registerdata', array('uid'=>getGUID(),'timeStamp'=>time(),'user'=>$this->uid));
	}
	
	/**
	 * <b>this mothod will be invoked when user profile is loaded</b>
	 * 
	 * <b>加载用户档案时会调用此方法</b>
	 */
	public function onLoad(){
		//日期改变
		
		if(date('Y-m-d',$this->date)!= date('Y-m-d')){
			if(rand(1, 3) != 1)
				return;
			$data = $this->flush();
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'UserProfile';
			$contents['contents'] = $data;
			Chat::message($this)->setContents($contents)->sendOneMessage();
		}
	}
	/**
	 * 每次在线超过5分钟记录一次游戏时间
	 */
	public function refreshOnlineTime(){
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$key = 'ONLINE_USER_' . $this->uid;
		if($cache->get($key))
		{
			if($this->lastLoadTime == 0)
				$this->lastLoadTime = time();
			if(time() - $this->lastLoadTime >= 590)
			{
				$this->playerOnlineTime += time() - $this->lastLoadTime;
				$this->lastLoadTime = time();
				$this->save();
			}
		}
		else
		{
			$this->lastLoadTime = time();
			$this->save();
		}
	}
	
	public static function checkUserOnline($uid) {
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$key = 'ONLINE_USER_' . $uid;
		if($cache->get($key)) {
			return true;
		}
		return false;
	}
	
	/**
	 * 每日凌晨刷新用户数据
	 *
	 */
	public function flush(){
		import('service.item.QuestItem');
		QuestItem::removeActiveQuest($this->uid);
//		import('util.mysql.XMysql');
//		$mysql = XMysql::singleton();
//		$mysql->add('logindata', array('uid'=>getGUID(),'timeStamp'=>time(),'user'=>$this->uid));
		/** 此处填入的刷新属性,需要与末尾的返回数组键名对应 **/
		
//		$this->date = date('Y-m-d'); //登陆日期
		$this->date = time();
		$this->forcibly_forces = 0;	//当天强征士兵次数
		$this->forcibly_resource = 0; //当天强征资源次数
		$this->occupyCityTimes = 0;	//城市占领次数
		$this->occupyResourceTimes = 0;  //资源点占领次数
		$this->plunderTimes = 0;	//资源掠夺次数
		$this->destroyTimes = 0;    //城市摧毁次数
		$this->dailyFlushTimes = 1; //日常任务免费刷新次数
		$this->allianceFlushTimes = 0; //联盟任务免费刷新次数
		$this->leagueFlushTimes = 0; //联盟任务刷新次数
		$this->dailyCompleteTimes = 0; //日常任务每日完成次数
		$this->onlineGift = 150008;	   //在线礼包编号
		$this->extraPveTimes -= $this->pveTimes;
		$this->pveTimes = 0;
		$this->buyPveTimes = 0;
		$this->arenaTimes = 0;
		$this->resetPveTimes = array();
		$this->buyArenaTimes = 0;
		$this->teamTimes = array();
		$this->buyTeamTimes = array();
		$xmlGift = ItemSpecManager::singleton('default', 'goods.xml')->getItem(150008);
		import('util.cache.XCache');
		XCache::singleton()->setKeyPrefix('IK2');
		XCache::singleton()->set('giftEndTime'.$this->uid, $xmlGift->time,86400);//在线礼包领取倒计时
		//$this->giftEndTime = $xmlGift->time;		
		$this->active_point = 0;
		import('service.action.CalculateUtil');
		$this->activeReward = CalculateUtil::getActiveRewardArr();   //活跃值奖励
// 		if($this->vip > 0) {
			$this->accept_vipgift_status = 0; //vip每日领奖状态每天凌晨重置
// 		}
// 		if($this->yellowvip_firstgift_status == 1) {
			$this->accept_yvipgift_status = 0; //yellow vip每日领奖状态每天凌晨重置
// 		}
		$this->save();
		
		
		//联合军演每天凌晨自然重置
		import('service.action.MilitaryDrillClass');
		$militaryDrillService = MilitaryDrill::getInstance($this);
		$militaryDrillService->resetGeneralDaily();
		
		//帝国银矿用户每日重置
		import('service.item.SilverMineItem');
		SilverMineItem::resetDaily($this);
		
		//团战每日重置
		import('service.action.TeamClass');
		Team::resetDaily($this);
		
		//协助勘探记录清除
		import('service.item.SilverMineRecordItem');
		SilverMineRecordItem::deleteRecord($this->uid);
		
		//列岛远征用户每日重置
		import('service.action.OneThousandClass');
		OneThousand::resetDaily($this);
		
		//联盟大建筑每日捐献银币数量重置
		//每日祭拜重置
		import('service.item.LordItem');
		$lordItem = LordItem::getWithUID($this->uid);
		if($lordItem) {
			$lordItem->donatedMoneyDaily = 0;
			$lordItem->save();
		}
		
		//打怪每日购买次数重置 
		import('service.action.ActiveMonsterClass');
		ActiveMonster::resetBuyFightMonsterCount($this->uid);
		
		//联盟福利用户每日重置 
		import('service.action.AllianceClass');
		Alliance::resetAllianceWelfare($this);
		//删除过期邮件
		if ($this->level > 1)
		{
			import('service.item.MailItem');
			MailItem::removeExpiredMail($this->uid);
		}

// 		//世界征战次数每日重置
// 		import('service.item.UserWorldItem');
// 		$userWorldItem = UserWorldItem::getWithUID($this->uid);
// 		if($userWorldItem){ 
// 			$userWorldItem->fightCount = 0;
// 			$userWorldItem->save();
// 		}
		
		//重置经典PVE
		import('service.item.PowerItem');
		PowerItem::clearPowerSetList($this->uid);
		//重置dailyResetItem
		import('service.item.DailyResetItem');
		$dailyResetItem = DailyResetItem::DailyReset($this->uid);

		//刷新军工厂加工次数
		import('service.item.FarmItem');
		FarmItem::refresh($this->uid);
				
		//刷新联盟列表中用户的登陆时间
		if($this->league){
			import('service.item.AllianceItem');
			$allianceItem = AllianceItem::getWithUID($this->league);
			$allianceItem->time2 = $this->date ;
			//更新联盟副盟主人数
			$allianceMems = AllianceItem::getMemberCount($this->league);
			foreach ($allianceMems as $memData){
				if($memData['type'] == 1 && $memData['count'] != $allianceItem->vpNum){
					$allianceItem->vpNum = $memData['count'];
				}
			}
			$allianceItem->save();
			//判断联盟人数不足三人的最后几天，发解散提醒邮件
			if($allianceItem->time1>0){
				if(date('Y-m-d',$this->date)== date('Y-m-d',$allianceItem->time1+5*3600*24)){
					$xmlEmail = ItemSpecManager::singleton('cn','item.xml')->getItem('7932');
					$title = $xmlEmail->description;
//					$content = sprintf($xmlEmail->description1,2);
					$content = xml_replace($xmlEmail->description1, array(2));
					import('service.item.MailItem');
					MailItem::addMail(null, $this, 1, $title,$content);
				}else if(date('Y-m-d',$this->date)== date('Y-m-d',$allianceItem->time1+6*3600*24)){
					$xmlEmail = ItemSpecManager::singleton('cn','item.xml')->getItem('7932');
					$title = $xmlEmail->description;
//					$content = sprintf($xmlEmail->description1,1);
					$content = xml_replace($xmlEmail->description1, array(1));
					import('service.item.MailItem');
					MailItem::addMail(null, $this, 1, $title,$content);
				}
			}
			
			import('service.action.QuestClass');
			Quest::singleton($this)->initAllianceQuest();
			//更新联盟任务
			//数据存入聊天
			import('service.item.QuestItem');
			import('service.action.ChatClass');
			$leagueQuest = QuestItem::getWithType($this->uid,3);
	
			$questUids = '';
			foreach($leagueQuest as $questItem){
				$questUids = $questUids.$questItem->uid.',';	
			}
		
			$questUids = substr($questUids,0,strlen($questUids)-1);
			$contents['mode'] = 11;
			$contents['modeValue'] = 'questItems';
			$contents['contents'] = $questUids;
			Chat::message($this)->setContents($contents)->sendOneMessage();				
		}

		

		
		$this->save();	

		return array(
			'date' => $this->date,
			'forcibly_forces' => $this->vforcibly_forces,
			'forcibly_resource' => $this->forcibly_resource,
			'occupyCityTimes' => $this->occupyCityTimes,
			'occupyResourceTimes' => $this->occupyResourceTimes,
			'plunderTimes' => $this->plunderTimes,
			'destroyTimes' => $this->destroyTimes,
			'dailyFlushTimes' => $this->dailyFlushTimes,
			'dailyCompleteTimes' => $this->dailyCompleteTimes,
			'onlineGift' => $this->onlineGift,
			'pveTimes' => $this->pveTimes,
			'extraPveTimes' => $this->extraPveTimes,
			'buyPveTimes' => $this->buyPveTimes,
			'arenaTimes' => $this->arenaTimes,
			'buyArenaTimes' => $this->buyArenaTimes,
			'giftEndTime' => $xmlGift->time,
			'active_point' => $this->active_point,
			'activeReward' => $this->activeReward,
			'accept_vipgift_status' => $this->accept_vipgift_status,
			'accept_yvipgift_status' => $this->accept_yvipgift_status,
		);
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
// 		import('util.cache.XCache');
// 		$cache = XCache::singleton();
// 		$cache->setKeyPrefix('IK2');
// 		$cacheData = $cache->get('USERPROFILE_'.$uid);
// 		if($cacheData != null)
// 		{
// 			return self::toObject(__CLASS__,array(get_object_vars($cacheData)));
// 		}
		$cachekey = __CLASS__.$uid;
		$cacheVal = parent::getCacheValue($cachekey);
		if ($cacheVal)
			return $cacheVal;
		
		$res = self::getOne(__CLASS__, $uid);
		if($res)
		{
			$res->unserializeProperty('activeReward');
			$res->unserializeProperty('teamTimes');
			$res->unserializeProperty('buyTeamTimes');
			$res->unserializeProperty('resetPveTimes');
// 			$cacheData = $cache->set('USERPROFILE_'.$res->uid,$res,900);
// 			$cacheData = $cache->set('USERPROFILENAME_'.md5($res->name),$res->uid,900);
// 			$res->cacheTime = microtime(true);
			parent::setCacheValue($cachekey, $res);
		}
		return $res;
	}
	
	public function save(){
// 		import('util.cache.XCache');
// 		$cache = XCache::singleton();
// 		$cache->setKeyPrefix('IK2');
// 		$cache->delete('USERPROFILE_'.$this->uid);
// 		$cacheData = $cache->set('USERPROFILE_'.$this->uid,$this,900);
// 		$cacheData = $cache->set('USERPROFILENAME_'.md5($this->name),$this->uid,900);
		//先存缓存再存数据库
		$this->serializeProperty('activeReward');
		$this->serializeProperty('teamTimes');
		$this->serializeProperty('buyTeamTimes');
		$this->serializeProperty('resetPveTimes');
		parent::save();
		$this->unserializeProperty('activeReward');
		$this->unserializeProperty('teamTimes');
		$this->unserializeProperty('buyTeamTimes');
		$this->unserializeProperty('resetPveTimes');
		
		$cachekey = __CLASS__.$this->uid;
		parent::delCacheValue($cachekey);
// 		//检查缓存是否是最新的
// 		$cachekey = __CLASS__.$this->uid;
// 		$cacheVal = parent::getCacheValue($cachekey);
// 		if ($cacheVal){
// 			if($this->cacheTime != $cacheVal->cacheTime){
// 				parent::delCacheValue($cachekey);
// 			}else{
// 				$this->cacheTime = microtime(true);
// 				parent::setCacheValue($cachekey, $this);
// 			}
// 		}
	}
	static function getWithName($name){
// 		import('util.cache.XCache');
// 		$cache = XCache::singleton();
// 		$cache->setKeyPrefix('IK2');
// 		$cacheData = $cache->get('USERPROFILENAME_'.md5($name));
// 		if($cacheData != null)
// 		{
// 			return self::getWithUID($cacheData);
// 		}
		
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$res = $mysql->get('userprofile',array('name'=>$name));
		$res = self::toObject(__CLASS__,$res);
		if($res)
		{
			$res->unserializeProperty('activeReward');
			$res->unserializeProperty('teamTimes');
			$res->unserializeProperty('buyTeamTimes');
			$res->unserializeProperty('resetPveTimes');
		}
		return $res; 
	}
	
	public function isFlyInWorld() {
		return $this->x == -1 || $this->x == -2;
	}
}
?>