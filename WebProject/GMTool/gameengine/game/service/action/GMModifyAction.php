<?php
class GMModifyAction extends XAbstractAction {
	protected $params;
	protected $user;
	protected $mysql;
	public function execute(XAbstractRequest $request){
		
		$this->params = $request->getParameters();
		//rest接口
		if($this->params['type'] == null)
			$this->params = $this->params['data']['params'];
		$info = $request->getParameter('info');
		if(isset($info['platformUserId']) && isset($info['platformAppId']))
		{
			$platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
			$this->user = UserProfile::getWithUID($platformProfile->userUID);
		}
		elseif (isset($info['gameUserId']))
			$this->user = UserProfile::getWithUID($info['gameUserId']);
		elseif (isset($info['gameUserName']))
		{
			$this->user = UserProfile::getWithName($info['gameUserName']);
		}
		if(!in_array($this->params['type'], array(19,21,23,24,26,27,28)))
		{
			if(!$this->user)
				return XServiceResult::clientError('user not found');
		}
		$data = array();
		switch ($this->params['type']){
			case 1: //查看玩家所有物品
				import('service.item.InventoryItem');
				$inventoryItems = InventoryItem::getItems($this->user->uid);
				$goodsGroup = ItemSpecManager::singleton('default','goods.xml')->getGroup('goods');
				$temp = array();
				foreach ($inventoryItems as &$goods){
					$itemXml = $goodsGroup->$goods['itemId'];
					$langXml = ItemSpecManager::singleton('cn')->getItem($goods['itemId']);
					$gem = "";
					if($goods['gem'])
						foreach ($goods['gem'] as $gemUid)
							$gem .= $gemUid."<br />";
					$temp[] = array(
						'uid' => $goods['uid'],
						'itemId' => $goods['itemId'],
						'ownerId' => $goods['ownerId'],
						'level' => $goods['level'],
						'count' => $goods['count'],
						'overlap' => $itemXml->overlap,
						'useGeneralId' => $goods['useGeneralId'],
						'name' =>  $langXml->name,
						'color' => $itemXml->color,
						'value1' => $goods['value1'],
						'value2' => $goods['value2'],
						'value3' => $goods['value3'],
						'effect1' => $goods['effect1'],
						'effect2' => $goods['effect2'],
						'effect3' => $goods['effect3'],
						'gem' => $gem,
						'embed' => $goods['embed'],
					);
					$sort[] = $goods['itemId'];
				}
				if(count($temp)>1){
					array_multisort($sort,SORT_ASC,$temp);
				}
				$data['inventory'] = $temp;
				$data['result'] = true;
				break;
			case 2: //修改物品属性
				import('service.item.InventoryItem');
				$inventoryItem = InventoryItem::getWithUID($this->params['goods_id']);
// 				if($this->params['modifyType'] == 'level' && $inventoryItem->useGeneralId != null)
// 				{
// 					import('service.item.GeneralItem');
// 					$general = GeneralItem::getWithUID($inventoryItem->useGeneralId);
					//扣除新装备属性加成
// 					import('service.action.CalculateUtil');
// 					CalculateUtil::getGoodsEffect($this->user, $inventoryItem, 1, $general, false);
// 					//增加新装备属性加成
// 					$inventoryItem->{$this->params['modifyType']} = $this->params['num'];
// 					CalculateUtil::getGoodsEffect($this->user, $inventoryItem, 1, $general);
// 					$general->save();
// 				}
				$inventoryItem->{$this->params['modifyType']} = $this->params['num'];
				$inventoryItem->save();
				$data = $inventoryItem;
				break;
			case 3: //添加一个物品
				import('service.item.InventoryItem');
				import('service.action.InventoryClass');
				$inventory = Inventory::singleton($this->user);
				import('service.item.ItemSpecManager');
				$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($this->params['itemId']);
				if($xmlGoods)
				{
					$inventory->addGoods($this->params['itemId'],$this->params['num'],$this->params['level'],'GM');
// 					//不可叠加
// 					$inventoryItem = $inventory->getGoodsByItemID($this->params['itemId'],false);
// 					if($xmlGoods->overlap != 1){
// 						if($inventoryItem){
// 							$inventoryItem->increase('count', $this->params['num']);
// 							$inventoryItem->save();
// 						}else{
// 							$inventoryItem = new InventoryItem();
// 							$inventoryItem->ownerId = $this->user->uid;
// 							$inventoryItem->level = $this->params['level'];
// 							$inventoryItem->count = $this->params['num'];
// 							$inventoryItem->itemId = $this->params['itemId'];
// 							$inventoryItem->uid = getGUID();
// 							$inventoryItem->save();
// 						}
// 					}
// 					else{
// 						for ($j = 0;$j<$this->params['num'];$j++){
// 							$inventoryItem = new InventoryItem();
// 							$inventoryItem->ownerId = $this->user->uid;
// 							$inventoryItem->level = $this->params['level'];
// // 							$inventoryItem->count = $this->params['num'];
// 							$inventoryItem->count = 1;
// 							$inventoryItem->itemId = $this->params['itemId'];
// 							$inventoryItem->uid = getGUID();
// 							$inventoryItem->save();
// 						}
// 					}
				}
				$inventoryItems = InventoryItem::getItems($this->user->uid);
				$temp = array();
				foreach ($inventoryItems as $goods){
					$itemXml = ItemSpecManager::singleton('default','goods.xml')->getItem($goods['itemId']);
					$langXml = ItemSpecManager::singleton('cn')->getItem($goods['itemId']);
					$temp[] = array(
						'uid' => $goods['uid'],
						'itemId' => $goods['itemId'],
						'ownerId' => $goods['ownerId'],
						'level' => $goods['level'],
						'count' => $goods['count'],
						'overlap' => $itemXml->overlap,
						'useGeneralId' => $goods['useGeneralId'],
						'name' =>  $langXml->name,
						'color' => $itemXml->color,
						'value1' => $goods['value1'],
						'value2' => $goods['value2'],
						'value3' => $goods['value3'],
						'effect1' => $goods['effect1'],
						'effect2' => $goods['effect2'],
						'effect3' => $goods['effect3'],
					);
					$sort[] = $goods['itemId'];
				}
				if(count($temp)>1){
					array_multisort($sort,SORT_ASC,$temp);
				}
				$data['inventory'] = $temp;
				$data['result'] = true;
				break;
			case 4: //删除物品
				import('service.item.InventoryItem');
				$inventoryItem = InventoryItem::getWithUID($this->params['goods_id']);
// 				if($inventoryItem->useGeneralId != null)
// 				{
// 					import('service.item.GeneralItem');
// 					$general = GeneralItem::getWithUID($inventoryItem->useGeneralId);
// 					//扣除新装备属性加成
// 					import('service.action.CalculateUtil');
// 					CalculateUtil::getGoodsEffect($this->user, $inventoryItem, 1, $general, false);
// 					$general->save();
// 				}
				$inventoryItem->remove();
				$data = $inventoryItem;
				break;
			case 5://获得PVE进度
				import("service.item.PowerItem");
				$data['result'] = true;
				$data['power'] = PowerItem::getWithUID($this->user->uid);
				break;
			case 6://修改PVE进度
				import("service.item.PowerItem");
				$powerItem = PowerItem::getWithUID($this->user->uid);
				$newList = array();
				$paramList = explode(';',$this->params['newData']['newList']);
				foreach ($paramList as $key=>$value)
				{
					$power = explode(':', $value);
					if($power[0] < 1)continue;
					$armys = explode(',', $power[1]);
					foreach ($armys as $army)
						$newList[$power[0]][$army] = array('id'=>$army);
				}
				if(!$powerItem)
				{
					$powerItem = new PowerItem();
					$powerItem->uid = $this->user->uid;
				}
				$powerItem->powerList = $newList;
				$powerItem->save();
				$data['power'] = $powerItem;
				break;
			case 7://踢下线
				$data = true;
				break;
			case 8://获得阵法信息
				import('service.item.FormationItem');
				$data['result'] = true;
				$data['formation'] = FormationItem::getAllFormation($this->user->uid);
				break;
			case 9://添加删除阵法
				import('service.action.FormationClass');
				import('service.item.FormationItem');
				$newFormationList = explode(';',$this->params['newData']['newList']);
				foreach ($newFormationList as $newFormation)
				{
					$formation = explode(':', $newFormation);
					$formationItem = Formation::singleton($this->user)->getFormationByItemId($formation[0]);
					if($formation[1]=='false' && $formationItem)
					{
						$formationItem->remove();
					}
					elseif ($formation[1]=='true' && !$formationItem)
					{
						FormationItem::InitFormation($this->user->uid, $formation[0]);
					}
				}
				$default = $this->params['newData']['default'];
				if($default > 0)
				{
					if(Formation::singleton($this->user)->getFormationByItemId($default))
						Formation::singleton($this->user)->setDefaultFormation($default);
					else
					{
						FormationItem::InitFormation($this->user->uid, $default);
						Formation::singleton($this->user)->setDefaultFormation($default);
					}
				}
				else if(!FormationItem::getDefaultFormation($this->user->uid))
				{
					$res = FormationItem::getAllFormation($this->user->uid);
					$formationList = FormationItem::to($res, true);
					foreach ($formationList as $formationListItem)
					{
						$formationListItem->isDefault = 1;
						$formationListItem->save();
						break;
					}
				}
				$data['result'] = true;
				$data['formation'] = FormationItem::getAllFormation($this->user->uid);
				break;
			case 10: //查询科技
				break;
			case 11: //修改科技等级
				break;
			case 12: //添加科技
				break;
			case 13: //删除科技
				break;
			case 14://查看用户city
				import("service.item.CityItem");
				$data = CityItem::getWithUID($this->user->uid);
				break;
			case 15://修改cityItem
				import("service.item.CityItem");
				$city = CityItem::getWithUID($this->user->uid);
				$newData = $this->params['data'];
				$modify = array();
				foreach ($newData as $key => $param){
					if(substr($key,0,5) != 'value' || $param == null)
						continue;
					$realKey = substr($key, 6 , strlen($key));
					if(substr($realKey, 0,-2) == 'groundIndex' && $city->groundIndex[substr($realKey, -1)] != $param){
						$modify[$realKey] = array('old'=>$city->groundIndex[substr($realKey, -1)],'new'=>$param);
						$temp = $city->groundIndex; 
						$temp[substr($realKey, -1)] = trim($param);
						$city->groundIndex = $temp;
					}
					else if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param)){
						//如果值有变化，写入log
						if($city->{$realKey} != $param){
							$modify[$realKey] = array('old'=>$city->{$realKey},'new'=>$param);
							$city->set($realKey, trim($param));
						}
					}
				}
				$city->save();
				$city = CityItem::getWithUID($this->user->uid);
				$data = array('city'=>$city,'modify'=>$modify);
				break;
			case 16://查看用户君主item
				import("service.item.LordItem");
				$data = LordItem::getWithUID($this->user->uid);
				break;
			case 17://修改君主Item
				import("service.item.LordItem");
				$lord = LordItem::getWithUID($this->user->uid);
				$newData = $this->params['data'];
				$modify = array();
				foreach ($newData as $key => $param){
					if(substr($key,0,5) != 'value' || $param == null)
						continue;
					$realKey = substr($key, 6 , strlen($key));
					if($realKey == 'message' || preg_match("/^\d*$|^\d+(\.\d+)?$/",$param)){
						//如果值有变化，写入log
						if($lord->{$realKey} != $param){
							$modify[$realKey] = array('old'=>$lord->{$realKey},'new'=>$param);
							$lord->set($realKey, trim($param));
						}
					}
				}
				$lord->save();
				$lord = LordItem::getWithUID($this->user->uid);
				$data = array('lord'=>$lord,'modify'=>$modify);
				break;
			case 18://用户当前任务
				import('service.item.QuestItem');
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$sql = $mysql->execResult("select * from quest where ownerId = '{$this->user->uid}' order by itemId asc",100);
				$data['user'] = $this->user;
				$data['quest'] = QuestItem::to($sql, true);
// 				$goods_xml = ItemSpecManager::singleton('cn')->getGroup('quest');
// 				foreach ($questDatas as $questData)
// 				{
// 					$questData->description1 = $goods_xml[$questData->itemId]->description1;
// 					$questData->description2 = $goods_xml[$questData->itemId]->description2;
// 					$data['quest'][] = $questData;
// 				}
				break;
			case 19://修改任务状态
				import('service.item.QuestItem');
				$questItem = QuestItem::getWithUID($this->params['quest_id']);
				$questItem->{$this->params['modifyType']} = $this->params['num'];
				$questItem->save();
				$data = $questItem;
				break;
			case 20://添加新任务
				import('service.item.QuestItem');
				import('service.action.LoadXMLUtil');
				$xmlQuest = LoadXMLUtil::loadXmlFile('quest.xml',$this->user)->getItem(trim($this->params['itemId']));
				if(!isset($this->params['itemId']) || !isset($this->user) || $xmlQuest == null)
					break;
				import('service.item.ItemSpecManager');
				$questItem = new QuestItem();
				$questItem->nums = $this->params['nums'];
				$questItem->status = $this->params['status'];
				$questItem->itemId = $xmlQuest->id;
				$questItem->type = $xmlQuest->type1;
				$questItem->ownerId = $this->user->uid;
				$questItem->target = $xmlQuest->type3;
				$questItem->uid = getGUID();
				$questItem->save();
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$sql = $mysql->execResult("select * from quest where ownerId = '{$this->user->uid}' order by itemId asc",100);
				$data['quest'] = QuestItem::to($sql, true);
				break;
			case 21: //删除任务
				import('service.item.QuestItem');
				$questItemItem = QuestItem::getWithUID($this->params['quest_id']);
				$questItemItem->remove();
				$data = $questItemItem;
				break;
			case 22: //查询玩家所有武将
				import('service.item.GeneralItem');
				import('service.item.GeneralSkillItem');
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from general where ownerId='{$this->user->uid}'";
				$generalItems = $mysql->execResultWithoutLimit($sql);
				foreach($generalItems as $key => &$general){
					$general['skill'] = GeneralSkillItem::getSkills($general['itemId'],$general['uid']);
				}
				$data=$generalItems;
				if(!$generalItems)
					$data = true;
				break;
			case 23://修改将军属性
				import('service.item.GeneralItem');
				$generalItem = GeneralItem::getWithUID($this->params['general_id']);
				$generalItem->{$this->params['modify_type']} = trim($this->params['num']);
				if($this->params['modify_type']=='name')
					$generalItem->nameFlag=1;
				$generalItem->save();
				$data = $generalItem;
				break;
			case 24://删除将军
				import('service.item.GeneralItem');
				$generalItem = GeneralItem::getWithUID($this->params['general_id']);
				$generalItem->remove();
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$res = $mysql->execute("update inventory set useGeneralId = '' where ownerId = '{$this->user->uid}' and useGeneralId = '{$generalItem->uid}'");
				import('service.action.FormationClass');
				Formation::singleton($this->user)->putGeneralOffFromAllFormation($generalItem->uid);
				$data = $generalItem;
				break;
			case 25://添加武将
				if(ItemSpecManager::singleton('default', 'general.xml')->getItem($this->params['generalId'])){
					import('util.mysql.XMysql');
					$mysql = XMysql::singleton();
					if($mysql->exist('general', array('ownerId' => $this->user->uid, 'itemId' => $this->params['generalId'])))
						break;
					import('service.action.GeneralClass');
					$initGeneral = General::singleton($this->user)->createOneGeneral($this->params['generalId']);
					$data = General::singleton($this->user)->addGeneral($initGeneral);
				}
				break;
			case 26://修改武将技能
				import('service.item.GeneralSkillItem');
				$generalSkillItem = GeneralSkillItem::getWithUID($this->params['general_id']);
				$skillList = $generalSkillItem->skillList;
				foreach ($skillList as &$generalSkill)
				{
					if($generalSkill['id'] == $this->params['skillid'] && in_array($this->params['modify'], array('id','level')))
					{
						$generalSkill[$this->params['modify']] = trim($this->params['num']);
						break;
					}
				}
				$generalSkillItem->skillList = $skillList;
				$generalSkillItem->save();
				$data = $skillList;
				break;
			case 27://添加武将技能
				import('service.item.GeneralSkillItem');
				$generalSkillItem = GeneralSkillItem::getWithUID($this->params['general_id']);
				if(!$generalSkillItem){
					$generalSkillItem = new GeneralSkillItem();
					$generalSkillItem->uid = $this->params['general_id'];
					$generalSkillItem->skillList = array();
				}				
				$skillList = $generalSkillItem->skillList;
				$skillList[] = array('id'=>5100000,'level'=>1);
				$generalSkillItem->skillList = $skillList;
				$generalSkillItem->save();
				$data = $skillList;
				break;
			case 28://删除武将技能
				import('service.item.GeneralSkillItem');
				$generalSkillItem = GeneralSkillItem::getWithUID($this->params['general_id']);
				$skillList = $generalSkillItem->skillList;
				if(count($skillList) <= 1)
					return XServiceResult::clientError('cannot delete');
				foreach ($skillList as $key=>$generalSkill)
				{
					if($generalSkill['id'] == $this->params['skillid'])
					{
						unset($skillList[$key]);
						array_merge($skillList);
						break;
					}
				}
				$generalSkillItem->skillList = $skillList;
				$generalSkillItem->save();
				$data = $skillList;
				break;
			case 29: //封号禁言给用户发消息
				import('service.action.ChatClass');
				$contents['mode'] = 11;
				$contents['modeValue'] = $this->params[data][value_modeValue];
				$contents['contents'] = $this->params[data][value_tip];
				Chat::message($this->user)->setContents($contents)->sendOneMessage();
				$data = true;
				break;
			case 30://查询其他数据
				import('service.item.MedalItem');
				$medalItem = MedalItem::getWithUID($this->user->uid);
				$data['medal'] = $medalItem;
				import('service.item.RecruitItem');
				$recruit = RecruitItem::getWithUID($this->user->uid);
				$data['recruit'] = $recruit;
				import('service.item.TreasureItem');
				$treasure = TreasureItem::getWithUID($this->user->uid);
				$data['treasure'] = $treasure;
				import('service.item.YellowDiamondItem');
				$item = YellowDiamondItem::getWithUID($this->user->uid);
				$data['yellowDiamond'] = $item;
				break;
			case 31://修改其他数据
				foreach ($this->params['data'] as $key => $param){
					$temp = explode('_', $key);
					if($temp[0] != 'value' || $param == null)
						continue;
					$table = $temp[1];
					$name = $temp[2];
					switch ($table){
						case 'medal':
							import('service.item.MedalItem');
							$item = MedalItem::getWithUID($this->user->uid);
							if($item->$name != $param){
								$item->$name = $param;
								$item->save();
							}
							break;
						case 'recruit':
							import('service.item.RecruitItem');
							$item = RecruitItem::getWithUID($this->user->uid);
							if($item->$name != $param){
								$item->$name = $param;
								$item->save();
							}
							break;
						case 'treasure':
							import('service.item.TreasureItem');
							$item = TreasureItem::getWithUID($this->user->uid);
							if($item->$name != $param){
								$item->$name = $param;
								$item->save();
							}
							break;
						case 'yellowDiamond':
							import('service.item.YellowDiamondItem');
							$item = YellowDiamondItem::getWithUID($this->user->uid);
							if($item->$name != $param){
								$item->$name = $param;
								$item->save();
							}
							break;
					}
				}
				import('service.item.MedalItem');
				$medalItem = MedalItem::getWithUID($this->user->uid);
				$data['medal'] = $medalItem;
				import('service.item.RecruitItem');
				$recruit = RecruitItem::getWithUID($this->user->uid);
				$data['recruit'] = $recruit;
				import('service.item.TreasureItem');
				$treasure = TreasureItem::getWithUID($this->user->uid);
				$data['treasure'] = $treasure;
				import('service.item.YellowDiamondItem');
				$item = YellowDiamondItem::getWithUID($this->user->uid);
				$data['yellowDiamond'] = $item;
				break;
			case 32://prisoner
				import('service.item.PrisonerItem');
				$data['item'] = PrisonerItem::getWithUID($this->user->uid);
				break;
			case 33:
				import('service.item.PrisonerItem');
				$prisonerItem = PrisonerItem::getWithUID($this->user->uid);
				$newData = $this->params['data'];
				$modify = array();
				foreach ($newData as $key => $param){
					if(substr($key,0,5) != 'value' || $param == null)
						continue;
					$realKey = substr($key, 6 , strlen($key));
					if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param) || $realKey == 'brand_content'){
						//如果值有变化，写入log
						if($prisonerItem->{$realKey} != $param){
							$modify[$realKey] = array('old'=>$prisonerItem->{$realKey},'new'=>$param);
							$prisonerItem->set($realKey, trim($param));
						}
					}
				}
				$prisonerItem->save();
				$data['item'] = PrisonerItem::getWithUID($this->user->uid);
				$data['modify'] = $modify;
				break;
			case 34: //查看玩家所有建筑
				import('service.item.BuildingItem');
				$buildingItems = BuildingItem::getBuildingsByCityType($this->user->uid);
				$temp = array();
				foreach ($buildingItems as &$buildingItem){
					$temp[] = $buildingItem;
					$sort[] = $buildingItem['itemId'];
				}
				if(count($temp)>1){
					array_multisort($sort,SORT_ASC,$temp);
				}
				$data['result'] = true;
				$data['building'] = $temp;
				break;
			case 35: //修改建筑属性
				import('service.item.BuildingItem');
				$buildingItem = BuildingItem::getWithUID($this->params['uid']);
				if($this->params['modify_type'] == 'pos')
				{
					if(!in_array($buildingItem->itemId, array(1308000,1309000,1310000)))
					{
						import('util.mysql.XMysql');
						$mysql = XMysql::singleton();
						if(!$mysql->exist('building', array('ownerId' => $buildingItem->ownerId,'cityType' => $buildingItem->cityType,'pos' => trim($this->params['num']))))
						{
							$buildingItem->{$this->params['modify_type']} = trim($this->params['num']);
							$buildingItem->save();
						}
					}
				}else{
					$buildingItem->{$this->params['modify_type']} = trim($this->params['num']);
					$buildingItem->save();
				}
				$data = true;
				break;
			case 36: //添加一个建筑
				import('service.item.ItemSpecManager');
				import("service.item.BuildingItem");
				if(ItemSpecManager::singleton('default', 'building.xml')->getItem($this->params['itemId']+$this->params['level'])){
					switch ($this->params['itemId']){
						case 1308000:
							$pos = 2;
							break;
						case 1309000:
							$pos = 11;
							break;
						case 1310000:
							$pos = 7;
							break;
						default:
							$pos = $this->params['pos'];
							break;
					}
					import('util.mysql.XMysql');
					$mysql = XMysql::singleton();
					if($mysql->exist('building', array('ownerId' => $this->user->uid,'cityType' => $this->params['cityType'],'pos' => $pos)))
					{
						$data['error'] = 'position conflict'; 
					}
					elseif($mysql->exist('building', array('ownerId' => $this->user->uid,'cityType' => $this->params['cityType'],'itemId' => $this->params['itemId'])))
					{
						$data['error'] = 'id conflict';
					}
					else
					{
						$buildingItem = new BuildingItem();
						$buildingItem->setAttrs(array('ownerId'=>$this->user->uid,'cityType'=>$this->params['cityType'],'pos'=>$pos,'itemId'=>$this->params['itemId'],'level'=>$this->params['level'],'trend'=>0,'finishTime'=>0));
						$buildingItem->uid = getGUID();
						$buildingItem->save();
						//初始化兵种
						if(in_array($this->params['itemId'], array(1308000,1309000,1310000)))
						{
							import('service.action.BuildingClass');
							$buildingClass = new BuildingClass($this->user->uid,$this->user);
							$buildingClass->initUnit(NULL,$this->params['itemId'],1);
						}
						//初始化世界
						if(in_array($this->params['itemId'], array(1315000)))
						{
							import('service.action.WorldClass');
							World::singletion($this->user)->createCity();
						}
					}
				}else{
					$data['error'] = 'id invalid';
				}
				$buildingItems = BuildingItem::getBuildingsByCityType($this->user->uid);
				$temp = array();
				foreach ($buildingItems as &$buildingItem){
					$temp[] = $buildingItem;
					$sort[] = $buildingItem['itemId'];
				}
				if(count($temp)>1){
					array_multisort($sort,SORT_ASC,$temp);
				}
				$data['building'] = $temp;
				break;
			case 37: //删除建筑	
				import('service.item.BuildingItem');
				$buildingItem = BuildingItem::getWithUID($this->params['uid']);
				$buildingItem->remove();
				$data = $buildingItem;
				break;
			case 38://查看用户银矿
				import("service.item.SilverMineItem");
				$data = SilverMineItem::getWithUID($this->user->uid);
				break;
			case 39://修改君主Item
				import("service.item.SilverMineItem");
				$item = SilverMineItem::getWithUID($this->user->uid);
				$newData = $this->params['data'];
				$modify = array();
				foreach ($newData as $key => $param){
					if(substr($key,0,5) != 'value' || $param == null)
						continue;
					$realKey = substr($key, 6 , strlen($key));
					if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param)){
						//如果值有变化，写入log
						if($item->{$realKey} != $param){
							$modify[$realKey] = array('old'=>$item->{$realKey},'new'=>$param);
							$item->set($realKey, trim($param));
						}
					}
				}
				$item->save();
				$item = SilverMineItem::getWithUID($this->user->uid);
				$data = array('item'=>$item,'modify'=>$modify);
				break;
			case 40://用户科技
				import('service.item.ScienceItem');
				$data['user'] = $this->user;
				//查询玩家的所有科技
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from science where ownerId='{$this->user->uid}' order by itemId";
				$items = $mysql->execResultWithoutLimit($sql);
				$data['items'] = $items;
				break;
			case 41://修改科技
				import('service.item.ScienceItem');
				$item = ScienceItem::getWithUID($this->params['itemUid']);
				$item->{$this->params['modifyType']} = $this->params['num'];
				$item->save();
				$data = $item;
				break;
			case 42://添加新科技
				import('service.item.ScienceItem');
				$level = trim($this->params['itemId'])%100;
				$itemId = trim($this->params['itemId']) - $level;
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				import('service.action.LoadXMLUtil');
				$xml = LoadXMLUtil::loadXmlFile('science.xml',$this->user)->getItem($itemId + $level);
				if(!isset($this->params['itemId']) || !isset($this->user) || $xml == null 
						|| $mysql->exist('science', array('ownerId' => $this->user->uid,'itemId' => $itemId)))
					break;
				$item = new ScienceItem();
				$item->itemId = $itemId;
				$item->ownerId = $this->user->uid;
				$item->type = $xml->tab;
				$item->level = $level;
				$item->status = 0;
				$item->save();
				//查询玩家的所有科技
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from science where ownerId='{$this->user->uid}' order by itemId";
				$items = $mysql->execResultWithoutLimit($sql);
				$data['items'] = $items;
				break;
			case 43: //删除科技
				import('service.item.ScienceItem');
				$item = ScienceItem::getWithUID($this->params['itemUid']);
				$item->remove();
				$data = $item;
				break;
			case 44://查询兵种
				import('service.item.UnitItem');
				$item = UnitItem::getWithUID($this->user->uid);
				$data['item'] = $item;
				break;
			case 45://查询兵种
				$unitLink = array(
						'armyFront_Level'=>'11',
						'armyMiddle_Level'=>'21',
						'armyBack_Level'=>'41',
						'navyFront_Level'=>'12',
						'navyMiddle_Level'=>'22',
						'navyBack_Level'=>'42',
						'airFront_Level'=>'13',
						'airMiddle_Level'=>'23',
						'airBack_Level'=>'43',
				);
				import('service.item.UnitItem');
				$item = UnitItem::getWithUID($this->user->uid);
				$newData = $this->params['data'];
				$newArmsList = $armsList = $modify = array();
				foreach ($item->armsList as $id=>$value){
					$unitType = substr($id, 0, 2);
					if(!in_array($unitType,$unitLink))
						continue;
// 					$unitLevel = $id%100;
					$unitLevel = substr($id,-3);
					$unitLevel = $unitLevel > 400 ? $unitLevel - 450:substr($id,-2);
					$armsList[$unitType] = $unitLevel;
				}
				foreach ($newData as $key => $param){
					if(substr($key,0,5) != 'value' || $param == null)
						continue;
					$realKey = substr($key, 6 , strlen($key));
					if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param)){
						//如果值有变化，写入log
						if($unitLink[$realKey]){
							$armsList[$unitLink[$realKey]] = trim($param);
						}
						elseif($item->{$realKey} != $param){
							$modify[$realKey] = array('old'=>$item->{$realKey},'new'=>$param);
							$item->set($realKey, trim($param));
						}
					}
				}
				foreach ($armsList as $unitType=>$level){
					$armsId = $unitType * 1000 + 100 + $level;
					if($level > 50){
						$armsId += 350;
					}
					$newArmsList[$armsId] = array('id'=>$armsId);
				}
				$item->armsList = $newArmsList;
				$item->save();
				//同步将军兵种
				import('service.item.GeneralItem');
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$sqlData = $mysql->execResultWithoutLimit("select * from general where ownerId='{$this->user->uid}'");
				if($sqlData){
					$generalItems = GeneralItem::to($sqlData, true);
					import('service.item.ItemSpecManager');
					foreach ($generalItems as $generalItem){
						$data['general'][] = $generalItem->uid;
						$generalXml = ItemSpecManager::singleton('default','general.xml')->getItem($generalItem->itemId);
						for ($i=1;$i<4;$i++){
							$unitType = substr($generalXml->{'gen_army'.$i}, 0, 2);
							if($armsList[$unitType]){
								$armsId = $unitType * 1000 + 100 + $armsList[$unitType];
								if($armsList[$unitType] > 50){
									$armsId += 350;
								}
								$generalItem->{'gen_army'.$i} = $armsId;
							}
						}
						$generalItem->save();
					}
				}
				$data['item'] = $item;
				break;
			case 46://账号的勋章修复
				import('service.item.MedalItem');
				$medalItem = MedalItem::getWithUID($this->user->uid);
				$medallist = $medalItem->medallist;
				if($medalItem->medal > 10000){
					while($medalItem->medal > 10000){
						$medalItem->medal -= 100000;
					}
					import('service.item.ItemSpecManager');
					$xmlGroup = ItemSpecManager::singleton('default','honor.xml')->getGroup('honor');
					for($i = 30;$i > 0;$i--){
						if($medalItem->medal > 0)
							break;
						$id = 110000 + $i;
						if($medallist[$id]){
							unset($medallist[$id]);
							$medalItem->medal += $xmlGroup->{$id}->need_honor;
						}
					}
					$medalItem->medallist = $medallist;
					$medalItem->save();
				}
				$data = true;
				break;
			case 47://查看任务完成状态
				import('service.item.QuestRecordItem');
				$questRecordItem = QuestRecordItem::getRecords($this->user->uid);
				$data['item'] = $questRecordItem;
				break;
			case 48://账号的完成任务更新
				import('service.item.QuestRecordItem');
				$questRecordItem = QuestRecordItem::getRecords($this->user->uid);
				$questList = $questRecordItem->questList;
				$questIds = explode(',', $this->params['data']['questId']);
				$saveFlag = false;
				if($this->params['data']['action'] == 'reset'){
					$questRecordItem->questList = array();
					$questRecordItem->save();
				}else{
					foreach ($questIds as $questId){
						if($questId){
							if($this->params['data']['action'] == 'add'){
								$questList[$questId] = 1;
							}else{
								unset($questList[$questId]);
							}
							$saveFlag = true;
						}
					}
					if($saveFlag){
						$questRecordItem->questList = $questList;
						$questRecordItem->save();
					}
				}
				$data['item'] = $questRecordItem;
				break;
		}
		//数据更新重新登录
// 		if(!in_array($this->params['type'], array(1,5,8,10,14,16,18,22,29,30,32,34,38)))
		if(in_array($this->params['type'], array(7)))
		{
			$this->user->onLoadKey = md5($this->user->uid . microtime(true));
			$this->user->save();
		}
		return XServiceResult::success($data);
	}
}
?>