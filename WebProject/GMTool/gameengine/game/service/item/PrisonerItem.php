<?php
/**
 * PrisonerItem
 * 
 * 战俘属性
 * 
 * @Entity
 * @package item
 */
import('persistence.dao.RActiveRecord');
class PrisonerItem extends RActiveRecord {
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 * **/
	protected $uid;           //用户uid
	protected $start_time;  // 被俘虏开始的时间
	protected $captive_time;  // 被俘虏结束的时间
	protected $ownerId;       //俘虏人
	protected $record_time;  // 记录刷新时间
	protected $askhelp_count; // 求救剩余次数
	protected $enslave_count; //奴役剩余次数
	protected $conquer_count; //征服剩余次数
	protected $save_count;    //解救剩余次数
	protected $enslave_end_time;//被奴役结束时间	
	protected $taskid;		 //被奴役执行任务id
	protected $free_use;     //免费使用标志位 0 未使用  1使用
	protected $taskupdatetime;//任务刷新时间
	protected $task1;			//奴役任务1
	protected $task2;			//奴役任务2
	protected $task3;			//奴役任务3
	protected $task4;			//奴役任务4
	protected $task5;			//奴役任务5
	protected $exp_add_time;	//上次战俘经验更新时间
	protected $brand_uid;		//烙印者uid
	protected $brand_time;		//烙印时间
	protected $brand_content;   //自定义烙印
	protected $buy_conquer_count; //购买的征服次数的次数，从0开始
	protected $buy_askhelp_count; //购买的求救次数的次数，从0开始
	protected $buy_enslave_count; //购买的奴役次数的次数，从0开始
	protected $buy_save_count; //购买的解救次数的次数，从0开始
	protected $prison_position; //花钱开启的战俘位置，相应字节为1表示开启，为0表示未开启
	protected $isExp;		//标志是否压榨
	
	/**
	 * 获得用户的数据
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	static public function getPriInfo($uid){
		$priInfo = self::getWithUID($uid);
		$flag = true;
		if($priInfo == null){
			$priInfo = self::initPrisonerItem($uid);
			$flag = false;
		}
		if($flag){
			$priInfo->fresh_left_count();
		}
		if($priInfo->brand_uid){
			$user = UserProfile::getWithUID($priInfo->uid);
			$brandOwner = UserProfile::getWithUID($priInfo->brand_uid);
			if($user->level > $brandOwner->level + 10){
				$priInfo->brandClear($priInfo->brand_uid);
			}
		}
		return $priInfo;
	}
	/**
     +----------------------------------------------------------
     * 获得战俘的倒计时时间
     +----------------------------------------------------------
     * @method get_prison_num
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getCapSpanTime($uid){
		$prisoner = self::getPriInfo($uid);
		$now = time();
		if($prisoner->captive_time > $now){
			return $prisoner->captive_time - $now;
			
		}else{
			return 0;
		}	
	} 
	
	
	/**
     +----------------------------------------------------------
     * 获得战俘空位数量 跟建筑有关
     +----------------------------------------------------------
     * @method get_prison_num
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getPrisonNum($uid){
		//计算花钱开启的俘虏位
		$prison = self::getWithUID($uid);
		$prison_position = $prison->prison_position;
		$count = 0;
		for($i = 5; $i < strlen($prison_position); $i ++)
		{
			if($prison_position[$i] == '1')
			{
				$count ++;
			}
		}
		
		//计算自然开启的俘虏位
		import('service.item.BuildingItem');
		$buildingId = '1331000';
		$building = BuildingItem::getBuildingByItemId($uid, $buildingId);
		if(isset($building))
		{
			$buildingItem = $building[0];
			$level = $buildingItem['level'];
			$contry_config = ItemSpecManager::singleton('default','building.xml')->getItem($level + $buildingId);
			$count += $contry_config->para3;
		}
		
		//将开启的战俘位置1
		for($i = 0; $i < $contry_config->para3; $i ++)
		{
			if($prison_position[$i] != '1')
			{
				$prison->prison_position[$i] = '1';
				$prison->save();
			}
		}
		return $count;
	}
	
	/**
     +----------------------------------------------------------
     * 获得tab页面各种信息
     +----------------------------------------------------------
     * @method getItems
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getItems($uid){
		import('service.item.BuildingItem');
		$buildingitem = BuildingItem::getBuildingByItemId($uid, '1331000');
		if (!isset($buildingitem)) return array();
		$prisoner = self::getPriInfo($uid);
		//每次加载 更新个人任务
		$prisoner->fresh_task();
		//更新我所有战俘的信息
		$prisoner->freshMyprisonersInfo();
		//dofresh the priinfo
		$data = array();
		$user_data['uid'] = $prisoner->uid;
		$user_data['ownerId'] = $prisoner->ownerId;		//烙印者uid
		$now = time();
		$user_data['itemId'] = $prisoner->uid;
		//$user_data['role'] = $prisoner->captive_time > $now ? 1 : 0; // 1 战俘
		$user_data['start_time'] = null;
		$user_data['captive_time'] = $prisoner->captive_time;
		$user_data['askhelp_count'] = $prisoner->askhelp_count;
		$user_data['enslave_count'] = $prisoner->enslave_count;
		$user_data['conquer_count'] = $prisoner->conquer_count;
		$user_data['save_count'] = $prisoner->save_count;
		$user_data['prison_num'] = self::getPrisonNum($uid); //监狱数量
		$user_data['prison_conf'] = self::getPrisonConf(); //监狱的配置信息
		$user_data['prison_position'] = self::getWithUID($uid)->prison_position;
		$user_data['buy_conquer_count'] = $prisoner->buy_conquer_count;
		$user_data['buy_enslave_count'] = $prisoner->buy_enslave_count;
		$user_data['buy_askhelp_count'] = $prisoner->buy_askhelp_count;
		$user_data['buy_save_count'] = $prisoner->buy_save_count;
		$user_data['brand_uid'] = $prisoner->brand_uid;		//烙印者uid
		$user_data['brand_time'] = $prisoner->brand_time;		//烙印者uid
		$user_data['brand_content'] = $prisoner->brand_content;		//烙印者uid
		$user_data['isExp'] = $prisoner->isExp;	
		$user_data['exp_add_time'] = $prisoner->exp_add_time;	
		if(!empty($user_data['ownerId']))
		{
			$ownerUser = UserProfile::getWithUID($user_data['ownerId']);
			$user_data['owner_pic'] = $ownerUser->pic;
			$user_data['owner_name'] = $ownerUser->name;
		}
		elseif (!empty($user_data['brand_uid']))
		{
			$ownerUser = UserProfile::getWithUID($user_data['brand_uid']);
			$user_data['owner_name'] = $ownerUser->name;
		}
		
		$data[] = $user_data;
		//dofresh the priinfo
		//搞到俘虏
		$sql = "select pra.*, uf.name, uf.pic, uf.country, uf.league, uf.level, al.name as league_name ";
		$sql .= "from (select pr.uid, pr.start_time, pr.captive_time, pr.taskid, pr.enslave_end_time,pr.brand_content from prisoner pr where pr.ownerId='{$uid}' AND pr.captive_time>{$now}) pra ";
		$sql .= "left join userprofile uf on pra.uid=uf.uid ";
		$sql .= "left join alliance al on uf.league=al.uid";
		$result = XMysql::singleton()->execResult($sql,10);
		
		//搞到战斗力
		import('service.action.PrisonerClass');
		$user = UserProfile::getWithUID($uid);
		$result = Prisoner::singletion($user)->getPowerForList($result);
		if(!empty($result)){
			foreach($result as $value){
				$otherprisoner = self::getPriInfo($value['uid']);
				
				$pri_data = $value;
				$roleXml = ItemSpecManager::singleton('default','role.xml')->getItem($pri_data['level']  + 2000);
				$pri_data['itemId'] = $value['uid'];
				$pri_data['squeeze'] = $roleXml->squeeze;
				$pri_data['isExp'] = $otherprisoner->isExp;
				$pri_data['askhelp_count'] = null;
				$pri_data['enslave_count'] = null;
				$pri_data['conquer_count'] = null;
				$pri_data['save_count'] = null;
				$pri_data['prison_num'] = null;
				$pri_data['train_exp'] = Prisoner::singletion($user)->getAllyExp($pri_data['level'],600);
				$pri_data['extralTrain_exp'] = self::getExtralExpPercent($user->league,$pri_data['league'])*$pri_data['train_exp'];
				$data[] = $pri_data;
			}
		}
		return $data;
	}
	
	//根据联盟敌对取得战俘的额外经验，用于显示
	public  function getExtralExpPercent($ownerAllianceId,$prionserAllianceId){
		$xishu = 0;
		if($ownerAllianceId && $prionserAllianceId){
			import('service.item.AllianceEnemyItem');
			$allianceEnemyItem = AllianceEnemyItem::getOneAllianceEnemy($ownerAllianceId,$prionserAllianceId);
			if($allianceEnemyItem){
				import('service.item.ItemSpecManager');
				$data_config = ItemSpecManager::singleton()->getItem("prison_exp");
				import('service.item.AllianceItem');
				$alliance1 = AllianceItem::getWithUID($ownerAllianceId);
				$alliance2 = AllianceItem::getWithUID($prionserAllianceId);
				if($alliance1->level-$alliance2->level<=0){
					$xishu = $data_config->k4;
				}else if ($alliance1->level-$alliance2->level<=1){
					$xishu = $data_config->k4/2;	
				}
			}					
		}		
		return $xishu;
		
	}
	/**
     +----------------------------------------------------------
     * 更新我的战俘的任务
     +----------------------------------------------------------
     * @method enslavePrisoner
     * @access public
     * @param $peopleList 用户列表	  
     +----------------------------------------------------------
     * @return 返回相应人员列表信息
     +----------------------------------------------------------
     */
	public function freshMyprisonersInfo(){
		$now = time();
		$sql = "select uid from prisoner where ownerId='{$this->uid}' and (captive_time<{$now} or (enslave_end_time<{$now} and enslave_end_time>0))";
		$result = XMysql::singleton()->execResult($sql,10);
		if(!empty($result)){
			foreach ($result as $item){
				//更新每个俘虏的信息
				$uid = $item['uid'];
				$prisoner = self::getWithUID($uid);
				$prisoner->fresh_task();
			}
		}
	}
	
	
	/**
     +----------------------------------------------------------
     * 是否在奴役阶段
     +----------------------------------------------------------
     * @method isSlaveTime
     * @access 
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function isSlaveTime(){
		return $this->enslave_end_time > time();
	}
	
	
	/**
     +----------------------------------------------------------
     * 获得监狱解锁等级
     +----------------------------------------------------------
     * @method getPrisonInfo
     * @access 
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getPrisonConf(){
		$buildingId = '1331000';
		$data = array();
		$ItemSpa = ItemSpecManager::singleton('default','building.xml');
		$num = 0;
		for($i=1; $i<=20; $i++){
			$config = $ItemSpa->getItem($i + $buildingId);
			if($config->para3 > $num){
				$data[$config->para3]['level'] =  $i;
				$data[$config->para3]['cost'] = 0;
				$num = $config->para3;
			}
		}
		
		$xmlItem = ItemSpecManager::singleton('default', 'item.xml')->getItem("prison_cost3");
		if(empty($xmlItem))
		{
			return $data;
		}
		//从第六个到第十个位置，需要花钱开启
		for($i = 6; $i <= 10; $i ++)
		{
			$data[$i]['level'] =  1;
			$data[$i]['cost'] = $xmlItem->{k . ($i - 5)};
		}
		
		return $data;
	}
	
	/**
     +----------------------------------------------------------
     * 获得指定用户的战俘信息
     +----------------------------------------------------------
     * @method getItems
     * @access 
     * @param $itemUids单个用户的信息
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	static function getNewItems($uid){
		$data = array();
		$sql = "select pra.*, uf.name, uf.pic, uf.country,uf.league, uf.level, al.name as league_name ";
		$sql .= "from (select pr.uid,pr.ownerId , pr.start_time, pr.captive_time, pr.taskid, pr.enslave_end_time from prisoner pr where pr.uid='{$uid}') pra ";
		$sql .= "left join userprofile uf on pra.uid=uf.uid ";
		$sql .= "left join alliance al on uf.league=al.uid"; 
		$result = XMysql::singleton()->execResult($sql,10);
		if($result){
			//搞到战斗力
			import('service.action.PrisonerClass');
			$user = UserProfile::getWithUID($result[0]['ownerId']);
			$result = Prisoner::singletion($user)->getPowerForList($result);
			foreach($result as $value){
				$pri_data = $value;
				$pri_data['itemId'] = $value['uid'];
				$roleXml = ItemSpecManager::singleton('default','role.xml')->getItem($pri_data['level']  + 2000);
				$pri_data['squeeze'] = $roleXml->squeeze;
				$pri_data['train_exp'] = Prisoner::singletion($user)->getAllyExp($pri_data['level'],600);
				$pri_data['extralTrain_exp'] = self::getExtralExpPercent($user->league,$pri_data['league'])*$pri_data['train_exp'];
				$data[] = $pri_data;
			}	
		}
		return $data;
	}
	
	
	
	/*
	 * 创建对象之后及时刷新
	 * 
	 */
//	public function afterCreated(){
//		$this->fresh_left_count();
//	}
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		return self::getOne(__CLASS__, $uid);
	}
	
	/**
	 * 判断是否需要刷新剩余次数
	 */
	public function fresh_left_count(){
		if(date('Y-m-d',$this->record_time)!= date('Y-m-d')){
			$prisonerInitXML = ItemSpecManager::singleton()->getItem('player_pvp1');
			$this->record_time = time();
			$this->askhelp_count = $prisonerInitXML->k1; //求救剩余次数
			$this->enslave_count = $prisonerInitXML->k2; //奴役剩余次数
			$this->conquer_count = $this->conquer_count > $prisonerInitXML->k3 ? $this->conquer_count : $prisonerInitXML->k3; //征服剩余次数
			$this->save_count = $prisonerInitXML->k4;    //解救剩余次数
			$this->free_use = 0;    //是否使用免费刷新功能
			$this->buy_conquer_count = 0;
			$this->buy_askhelp_count = 0;
			$this->buy_enslave_count = 0;
			$this->buy_save_count = 0;
			$this->save();
			
		}
	}
	
	/**
	 * 增加征战次数
	 */
	public function add_conquer_count($type){
		if($type==1){//征服次数
			$this->conquer_count = $this->conquer_count + 1;
			$data = array("conquer_count" => $this->conquer_count);
		}else if($type==2){//解救次数
			$this->save_count = $this->save_count + 1;
			$data = array("save_count" => $this->save_count);
		}elseif ($type==3){//求救次数
			$this->askhelp_count = $this->askhelp_count + 1;
			$data = array("askhelp_count" => $this->askhelp_count);
		}elseif ($type==4){//奴役次数
			$this->enslave_count = $this->enslave_count + 1;
			$data = array("enslave_count" => $this->enslave_count);
		}
		$this->save();
		return $data;
	}
	
	/**
     +----------------------------------------------------------
     * 刷新俘虏 奴役等信息
     +----------------------------------------------------------
     * @method fresh_task
     * @access static public
     * @param $taskid 任务id
     * 	      $endTime 奴役结束时间
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	public function fresh_task(){
		$now = time();
		$fresh_flag = 0;
		$data['uid'] = $this->uid;
		if($this->enslave_end_time < $now && $this->enslave_end_time > 0 && $this->ownerId){
			//将任务做完
			$result = self::doTask($this->taskid, $this->uid, $this->ownerId);
			$user = UserProfile::getWithUID($this->uid);
			$owner = UserProfile::getWithUID($this->ownerId);
			//记log 作为奴隶失去
			$email_data = array("id" => 7503);
			$email_data['params'][] = $owner->name;
			$resource_name = ItemSpecManager::singleton('cn', 'item.xml')->getItem($result['effect'])->name;
			$email_data['params'][] = $resource_name;
			$email_data['params'][] = $result['number'];
			PrisonerItem::sendEmailToUser($user, $email_data,$this->enslave_end_time);
			/** 前后台数据对应：减去资源  各种资源数 **/
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'cityRefresh';
			$contents['contents'] = array($result["resource_name"] => -1 * $result["value_num"]);
			Chat::message($user)->setContents($contents)->sendOneMessage();
			
			/** 前后台数据对应：加上资源  各种资源数 第五种不加资源 **/
			if($result['effect'] != "8010005"){
				$own_contents['mode'] = 11;
				$own_contents['modeValue'] = 'cityRefresh';
				$own_contents['contents'] = array($result["resource_name"] => $result["value_num"]);
				Chat::message($owner)->setContents($own_contents)->sendOneMessage();	
			}
			if($result['effect'] == "8010005"){
				//如果是任务5 背包里面+物品 $result['rewordId']
				import('service.action.RewardClass');
				$rewardClass = RewardClass::singleton();
				$res = $rewardClass->setUser($owner)->reward($result['rewordId'],'Prisoner');
				if(!$res['goods']){
// 					file_put_contents(GAME_LOG_DIR.'/prisonerErr.log',json_encode($result['rewordId']) . "\n",FILE_APPEND);
// 					file_put_contents(GAME_LOG_DIR.'/prisonerErr.log',json_encode($res) . "\n",FILE_APPEND);
					//背包内容已满		
				}else{
					//背包正常ok
					//记log 作为战俘首领获得
					$get_email_data = array("id" => 7506);
					$get_email_data['params'][] = $user->name;
					//增加代码 资源效果 
					$itemId = $res['goods'][0]['get'][0]['itemId'];
					$itemName = ItemSpecManager::singleton('cn', 'item.xml')->getItem($itemId)->name;
					$get_email_data['params'][] = $itemName;
					$get_email_data['params'][] = $res['goods'][0]['get'][0]['count'];
// 					if(!$itemName){
// 						file_put_contents(GAME_LOG_DIR.'/prisonerErr.log','2'.json_encode($result['rewordId']) . "\n",FILE_APPEND);
// 						file_put_contents(GAME_LOG_DIR.'/prisonerErr.log','2'.json_encode($res) . "\n",FILE_APPEND);
// 					}
					PrisonerItem::sendEmailToUser($owner, $get_email_data,$this->enslave_end_time);	
					//通知前台更新物品
					$own_contents['mode'] = 11;
					$own_contents['modeValue'] = 'inventoryRefresh';
					$own_contents['contents'] = $res;
					Chat::message($owner)->setContents($own_contents)->sendOneMessage();
					
				}
			}else{
				//记log 作为战俘首领获得
				$get_email_data = array("id" => 7515);
				$get_email_data['params'][] = $user->name;
				//增加资源效果
				$get_email_data['params'][] = $resource_name;
				$get_email_data['params'][] = $result['value_num'];
				PrisonerItem::sendEmailToUser($owner, $get_email_data, $this->enslave_end_time);	
			}
			//
			$data['finish_task'] = true;
			$data['finish_task_time'] = $this->enslave_end_time;	
			
			$this->enslave_end_time = 0 ;
			$this->taskid = 0;
			$fresh_flag = 1;
			
		}
		if($this->captive_time < $now && $this->captive_time != '0'){
			if(!isset($user)){
				$user = UserProfile::getWithUID($this->uid);
			}
			if(!isset($owner)) {
				$owner = UserProfile::getWithUID($this->ownerId);
			}
			$this->expAddToOwner($user);
			//通知战俘主失去战俘
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'prisonerRefresh';
			$contents['contents'] = array("type" => 1,"playerId" => $this->uid);
			Chat::message($owner)->setContents($contents)->sendOneMessage();
			//记log 俘虏被释放
			$pemail_data = array("id" => 7505);
			$pemail_data['params'][] = $owner->name;
			PrisonerItem::sendEmailToUser($user, $pemail_data, $this->captive_time);	
			$data['finish_prisoner'] = true;
			$data['finish_prisoner_time'] = $this->captive_time;	
			//时间到了失效
		 	$this->captive_time = '0';
		 	$this->ownerId = '';
		 	//记下log test
		 	$fresh_flag = 1; 
		}
		if($fresh_flag == 1){
			$this->save();
		}
		return $data;
	}
	
	/**
	 * 新建立记录
	 * 
	 */
	static public function initPrisonerItem($uid){
		$prisonerInitXML = ItemSpecManager::singleton()->getItem('player_pvp1');
		$prisonerItem = new self();
		$prisonerItem->uid = $uid;
		$prisonerItem->start_time = 0;    //被俘虏开始时间
		$prisonerItem->captive_time = 0;    //被俘虏结束时间
		$prisonerItem->ownerId = '';		//如果被俘虏 则记录俘虏人
		$prisonerItem->record_time = time();   //记录刷新时间		
		$prisonerItem->askhelp_count = $prisonerInitXML->k1; //求救剩余次数
		$prisonerItem->enslave_count = $prisonerInitXML->k2; //奴役剩余次数
		$prisonerItem->conquer_count = $prisonerInitXML->k3; //征服剩余次数
		$prisonerItem->save_count = $prisonerInitXML->k4;    //解救剩余次数
		$prisonerItem->enslave_end_time = 0;
		$prisonerItem->taskid = 0;
		$prisonerItem->free_use = 0;
		$prisonerItem->taskupdatetime = 0;
		$prisonerItem->task1 = 0;
		$prisonerItem->task2 = 0;
		$prisonerItem->task3 = 0;
		$prisonerItem->task4 = 0;
		$prisonerItem->task5 = 0;
		$prisonerItem->exp_add_time = time(); //上次获得战俘经验更新时间
		$prisonerItem->save();
		return $prisonerItem;
	
	}
	
	/**
	 * 新建立记录
	 * 
	 */
	public function clear(){
		$prisonerInitXML = ItemSpecManager::singleton()->getItem('player_pvp1');
		$this->start_time = 0;    //被俘虏开始时间
		$this->captive_time = 0;    //被俘虏结束时间
		$this->ownerId = '';		//如果被俘虏 则记录俘虏人
		$this->record_time = time();   //记录刷新时间		
		$this->askhelp_count = $prisonerInitXML->k1; //求救剩余次数
		$this->enslave_count = $prisonerInitXML->k2; //奴役剩余次数
		$this->conquer_count = $prisonerInitXML->k3; //征服剩余次数
		$this->save_count = $prisonerInitXML->k4;    //解救剩余次数
		$this->enslave_end_time = 0;
		$this->taskid = 0;
		$this->free_use = 0;
		$this->taskupdatetime = 0;
		$this->task1 = 0;
		$this->task2 = 0;
		$this->task3 = 0;
		$this->task4 = 0;
		$this->task5 = 0;
		$this->exp_add_time = 0; //上次获得战俘经验更新时间
		$this->save();
		return ;
	
	}
	
	/**
     +----------------------------------------------------------
     * 作为战俘的主人随即任务list更新
     * 
     +----------------------------------------------------------
     * @method refreshTaskList
     * @access private
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	public function refreshTaskList($isForce = false){
		$now = time();
		$mustRefresh = false;
		if($isForce){
			$mustRefresh = true;
		}else{
			if($this->taskupdatetime < $now){
				$mustRefresh = true;
			}
		}
		if($mustRefresh){
			//必须更新任务内容 
			$xmlRandom = ItemSpecManager::singleton('default', 'squeeze.xml')->getGroup('initgeneral_plan');
			for ($i = 1; $i <= 5; $i++){
				$taskId = $this->getRandom($xmlRandom);
				$param = "task" . $i;
				$this->{$param} = $taskId;	
			}
			$this->taskupdatetime = time() + 3600; //下次刷新时间
			$this->save();
		}
	}
	
	/**
     +----------------------------------------------------------
     * 作为战俘的主人随即任务list免费更新
     * 
     +----------------------------------------------------------
     * @method freeRefreshTaskList()
     * @access public
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	public function FreeRefreshTaskList(){
		$this->free_use = 1;
		//必须更新任务内容 
		$xmlRandom = ItemSpecManager::singleton('default', 'squeeze.xml')->getGroup('initgeneral_plan');
		for ($i = 1; $i <= 5; $i++){
			$taskId = $this->getRandom($xmlRandom);
			$param = "task" . $i;
			$this->{$param} = $taskId;	
		}
		$this->taskupdatetime = time() + 3600; //下次刷新时间
		$this->save();
		
	}
	
	
	
	/**
     +----------------------------------------------------------
     * 获得随即任务id
     +----------------------------------------------------------
     * @method getRandom
     * @access private
     * @param $xmlRandom 奴役随即任务配置信息表
     +----------------------------------------------------------
     * @return 返回task id
     +----------------------------------------------------------
     */		
	private function getRandom($xmlRandom){
		$total = 0;
		$rand = mt_rand(0,100000);
		$randomList = $xmlRandom;
		foreach ($randomList as $value){
			$total += $value->rate;
			if($rand <= $total){
				return $value->id;
			}
		}
	}
	
	
	
	/**
     +----------------------------------------------------------
     * 刷新随即任务  去做任务
     +----------------------------------------------------------
     * @method doTask
     * @access public
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */	
	static public function doTask($taskId, $uid, $fid){
		$taskXml = ItemSpecManager::singleton('default', 'squeeze.xml')->getItem($taskId);
		//任务
		$effect = $taskXml->effect;
		$value = $taskXml->value;
		import('service.item.CityItem');
		import('service.action.CalculateUtil');
		$data = array();
		$data["effect"] = $effect;
		$data["number"] = $value;
		$cityItem = CityItem::getWithUID($uid);
		switch ($effect){
			case "8010001": //掠夺粮食
				$value_num = $cityItem->food * ($value / 100) ;
				//减少粮食
				CalculateUtil::changeFood($cityItem, -1 * $value_num);
				$cityItem->save();
				//增加粮食
				$ownerItem = CityItem::getWithUID($fid);
				CalculateUtil::changeFood($ownerItem, $value_num);
				$ownerItem->save();
				$data["resource_name"] = "food";
				break;
			case "8010002": //掠夺石油
				$value_num = $cityItem->oil * ($value / 100) ;
				//减少石油
				CalculateUtil::changeOil($cityItem, -1 * $value_num);
				$cityItem->save();
				//增加石油
				$ownerItem = CityItem::getWithUID($fid);
				CalculateUtil::changeOil($ownerItem, $value_num);
				$ownerItem->save();
				$data["resource_name"] = "oil";
				break;
			case "8010003": //掠夺矿物
				$value_num = $cityItem->mineral * ($value / 100) ;
				//减少矿物
				CalculateUtil::changeMineral($cityItem, -1 * $value_num);
				$cityItem->save();
				//增加矿物
				$ownerItem = CityItem::getWithUID($fid);
				CalculateUtil::changeMineral($ownerItem, $value_num);
				$ownerItem->save();
				$data["resource_name"] = "mineral";
				break;
			case "8010004": //掠夺银币
				$value_num = round($cityItem->money * ($value / 100)) ;
				//减少银币
				CalculateUtil::changeMoney($cityItem, -1 * $value_num,'PrisonerTask');
				$cityItem->save();
				//增加银币
				$ownerItem = CityItem::getWithUID($fid);
				CalculateUtil::changeMoney($ownerItem, $value_num,'PrisonerTaskAdd');
				$ownerItem->save();
				$data["resource_name"] = "money";
				break;
			case "8010005": //野外寻宝 -兵力
				$value_num = round($cityItem->forces * ($value / 100)) ;
				//减少兵力
				CalculateUtil::changeForces($cityItem, -1 * $value_num, 'prisoner');
				$cityItem->save();
				$rewordId = $taskXml->reward;
				$data["rewordId"] = $taskXml->reward;
				$data["resource_name"] = "forces";
				break;
		}
		$data["value_num"] = $value_num;
		return $data;
		
	}
	
	/**
     +----------------------------------------------------------
     * @method fighting 战斗结果 设置成战俘
     * @access public
     * @param $objuid 用户目标用户uid
     * 		  $type 1.征服战斗 
     * 		  $resultflag 战斗结果 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function becomePrisoner($ownerId){
		$now = time();
		$this->ownerId = $ownerId;
		$this->start_time = $now;
		$this->captive_time = $now + 3600 * 24;
		$this->taskid = 0;
		$this->enslave_end_time = 0;
		$this->brand_content = '';
		$this->brand_uid = $ownerId;
		$this->brand_time = $now;
		$this->isExp = 0;
		$this->save();
		import('service.action.ChatClass');
		$contents['mode'] = 11;
		$contents['modeValue'] = 'prisonerBrand';
		$contents['contents'] = array('brand_content'=>$this->brand_content,'brand_uid'=>$this->brand_uid,'brand_time'=>$this->brand_time);
		$user = UserProfile::getWithUID($this->uid);
		Chat::message($user)->setContents($contents)->sendOneMessage();
	}
	/**
     +----------------------------------------------------------
     * @method brandChange 改变烙印内容
     * @access public
     * @param $brandContent 烙印内容
     +----------------------------------------------------------
     * @return null
     +----------------------------------------------------------
     */
	public function brandChange($ownerId, $brandContent){
		if($this->brand_uid != $ownerId){
			return 'err1';
		}
		$this->brand_content = $brandContent;
		$this->save();
		import('service.action.ChatClass');
		$contents['mode'] = 11;
		$contents['modeValue'] = 'prisonerBrand';
		$contents['contents'] = array('brand_content'=>$this->brand_content);
		$user = UserProfile::getWithUID($this->uid);
		Chat::message($user)->setContents($contents)->sendOneMessage();
		return array("uid"=>$this->uid,"brand_content" =>$this->brand_content);  
	}
	/**
     +----------------------------------------------------------
     * @method brandClear 清除烙印
     * @access public
     * @param $brandContent 烙印内容
     +----------------------------------------------------------
     * @return null
     +----------------------------------------------------------
     */
	public function brandClear($uid, $isFource = FALSE){
		if($isFource){
			$this->brand_content = '';
			$this->brand_uid = '';
			$this->brand_time = 0;
			$this->save();
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'prisonerBrand';
			$contents['contents'] = array('brand_content'=>$this->brand_content,'brand_uid'=>$this->brand_uid,'brand_time'=>$this->brand_time);
			$user = UserProfile::getWithUID($this->uid);
			Chat::message($user)->setContents($contents)->sendOneMessage();
		}else{
			if($this->brand_uid == $uid){
				$this->brand_content = '';
				$this->brand_uid = '';
				$this->brand_time = 0;
				$this->save();
				import('service.action.ChatClass');
				$contents['mode'] = 11;
				$contents['modeValue'] = 'prisonerBrand';
				$contents['contents'] = array('brand_content'=>$this->brand_content,'brand_uid'=>$this->brand_uid,'brand_time'=>$this->brand_time);
				$user = UserProfile::getWithUID($this->uid);
				Chat::message($user)->setContents($contents)->sendOneMessage();
			}
		}	 
	}
	/**
     +----------------------------------------------------------
     * @method isPrisoner 判断用户是否为战俘
     * @access public 
     * @param  
     +----------------------------------------------------------
     * @return $result 是的话 返回奴隶主uid 不是返回false
     +----------------------------------------------------------
     */
	public function isPrisoner(){
		$now = time();
		if($this->captive_time > $now){
			return $this->ownerId;
		}
		return false;	 
	}
	/**
     +----------------------------------------------------------
     * @method isPrisoner 脱离战俘队列变成自由人
     * @access public 
     * @param  
     +----------------------------------------------------------
     * @return $result 是的话 返回奴隶主uid 不是返回false
     +----------------------------------------------------------
     */
	public function becomeFreePerson(){
		$late_captive_time = $this->captive_time;
		$this->captive_time = 0;
		$this->ownerId = '';
		$this->enslave_end_time = 0;
		$this->taskid = '';
		$this->save();
		return $late_captive_time;
	}
	/**
     +----------------------------------------------------------
     * @method reduceFightingCount 减少征战次数
     * @access public
     * @param $type 种类  1求救  2奴役  3征服   4解救
     * 		  $type 1.征服战斗 
     * 		  $resultflag 战斗结果 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function reduceFightingCount($type){
		$result = false;
		switch ($type){
			case 1:
				if($this->askhelp_count > 0){
					$this->askhelp_count = $this->askhelp_count - 1;
					$this->save();
					$result = true;
				}
				break;
			case 2:
				if($this->enslave_count > 0){
					$this->enslave_count = $this->enslave_count - 1;
					$this->save();
					$result = true;
				}
				break;
			case 3:
				if($this->conquer_count > 0){
					$this->conquer_count = $this->conquer_count - 1;
					$this->save();
					$result = true;
				}
				break;
			case 4:
				if($this->save_count > 0){
					$this->save_count = $this->save_count - 1;
					$this->save();
					$result = true;
				}
				break;
		}
		return $result;
	}
	/**
     +----------------------------------------------------------
     * 作为战俘的主人随即任务list免费更新
     * 
     +----------------------------------------------------------
     * @method getEnslaveTask
     * @access public
     * @param $taskid 任务id
     * 	      $endTime 奴役结束时间
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	public function getEnslaveTask($taskid, $endTime){
		$this->taskid = $taskid;
		$this->enslave_end_time = $endTime;
		$this->save();
	}
	
	/**
     +----------------------------------------------------------
     * 给相应的战俘主人加上相应的经验 在战俘阶段
     +----------------------------------------------------------
     * @method expAddToOwner
     * @access static public
     * @param $taskid 任务id
     * 	      $endTime 奴役结束时间
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function expAddToOwner($user){
		if(!empty($this->ownerId)){
			$ownerPrison = self::getWithUID($this->ownerId);
			$ownerProfile = UserProfile::getWithUID($this->ownerId);
			$last_expadd_time = $ownerPrison->exp_add_time;
			if($last_expadd_time >= $this->captive_time) return;
			$begin_time = $last_expadd_time > $this->start_time ? $last_expadd_time: $this->start_time;
			$now = time();
			//获得建筑物的para1
			import('service.item.BuildingItem');
			$buildingId = '1331000';
			$building = BuildingItem::getBuildingByItemId($this->ownerId, $buildingId);
			if(isset($building)){
				$buildingItem = $building[0];
				$level = $buildingItem['level'];
				$contry_config = ItemSpecManager::singleton('default','building.xml')->getItem($level + $buildingId);
				$para1 = $contry_config->para1;
			}
			$end_time = $now < $this->captive_time ? $now : $this->captive_time ;
// 			$exp = intval($user->level / 5 * (1 + $para1)) * ($end_time - $begin_time) ;
			//获得初始化参数
			$prisonerInitXML = ItemSpecManager::singleton()->getItem('prison_exp');
			$k1 = $prisonerInitXML->k1;
			$k2 = $prisonerInitXML->k2;
			$k3 = $prisonerInitXML->k3;
			$roleId = 2000 + $user->level; //角色id为等级加2000
			$roleXml = ItemSpecManager::singleton('default', 'role.xml')->getItem($roleId);
			$general_train_exp = $roleXml->general_train_exp;
			$exp = round($general_train_exp * $k1 * max(1 - ($ownerProfile->level - $user->level) * $k2, $k3) * $para1 * ($end_time - $begin_time)/600);
			if($exp > 0){
				import('service.action.CalculateUtil');
				$owner = UserProfile::getWithUID($this->ownerId);
				$add_exp = CalculateUtil::increaseLordExpPool($owner, $exp);
				if($add_exp > 0){
					$data['exp'] = $add_exp;
					/** 前后台数据对应：刷新后士兵数 **/
					import('service.action.ChatClass');
					$contents['mode'] = 11;
					$contents['modeValue'] = 'cityRefresh';
					$contents['contents'] = $data;
					Chat::message($owner)->setContents($contents)->sendOneMessage();
				}	
			}
		}
	}
	/**
     +----------------------------------------------------------
     * 发送邮件
     +----------------------------------------------------------
     * @method sendEmail
     * @access static public
     * @param $taskid 任务id
     * 	      $endTime 奴役结束时间
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	static public function sendEmail($toUserId,$data,$act_time=null){
		import('service.item.MailItem');
		$fromUser = "system";
		$toUser = UserProfile::getWithUID($toUserId);
		$type = 3;
		$id = $data["id"];
		$xmlEmail = ItemSpecManager::singleton('cn','item.xml')->getItem($data["id"]);
		$title = $xmlEmail->description;
		$params = $data["params"];
//		$content = sprintf($xmlEmail->description1, $params[0],$params[1],$params[2],$params[3],$params[4]);
		$content = xml_replace($xmlEmail->description1, array($params[0],$params[1],$params[2],$params[3],$params[4]));
		MailItem::addMail($fromUser, $toUser, $type, $title, $content,0,$act_time);
	}
	
	/**
     +----------------------------------------------------------
     * 发送邮件 直接发送给用户
     +----------------------------------------------------------
     * @method sendEmail
     * @access static public
     * @param $taskid 任务id
     * 	      $endTime 奴役结束时间
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */		
	static public function sendEmailToUser($user,$data,$act_time=null){
		import('service.item.MailItem');
		$fromUser = "system";
		$type = 3;
		$id = $data["id"];
		$xmlEmail = ItemSpecManager::singleton('cn','item.xml')->getItem($data["id"]);
		$title = $xmlEmail->description;
		$params = $data["params"];
//		$content = sprintf($xmlEmail->description1, $params[0],$params[1],$params[2],$params[3],$params[4]);
		$content = xml_replace($xmlEmail->description1, array($params[0],$params[1],$params[2],$params[3],$params[4]));
		MailItem::addMail($fromUser, $user, $type, $title, $content,0,$act_time);
	}


	
}
?>