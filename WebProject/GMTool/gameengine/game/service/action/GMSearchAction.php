<?php
class GMSearchAction extends XAbstractAction {
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
		if(!$this->user)
			return XServiceResult::clientError('user not found');
		$data = array();
		switch ($this->params['type']){
			case 1://查询用户信息user_all页面
				import("service.item.CityItem");
				$cityItem = CityItem::getWithUID($this->user->uid);
				import("service.item.LordItem");
				$lordItem = LordItem::getWithUID($this->user->uid);
				import('util.cache.XCache');
				$cache = XCache::singleton();
				$cache->setKeyPrefix('IK2');
				$key = 'ONLINE_USER_' . $this->user->uid;
				if($cache->get($key))
					$onLine = true;
				else
					$onLine = false;
				import('service.action.GeneralClass');
				$data = array('user'=>$this->user,'city'=>$cityItem,'lord'=>$lordItem,'online'=>$onLine,'fightPower'=>General::singleton($this->user)->getUserFightPower(true));
				break;
			case 2: //查看玩家所有物品
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
			case 3://获得PVE进度
				import("service.item.PowerItem");
				$data['result'] = true;
				$data['power'] = PowerItem::getWithUID($this->user->uid);
				break;
			case 4: //修改科技等级
				break;
			case 5: //查询已完成任务
				import("service.item.QuestRecordItem");
				$data = QuestRecordItem::getWithUID($this->user->uid);
				break;
			case 6://用户当前任务
				import('service.item.QuestItem');
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$sql = $mysql->execResult("select * from quest where ownerId = '{$this->user->uid}' order by itemId asc",100);
				$data['user'] = $this->user;
				$data['quest'] = QuestItem::to($sql, true);
				break;
			case 7: //查询玩家所有武将
				import('service.item.GeneralItem');
// 				import('util.mysql.XMysql');
// 				$mysql = XMysql::singleton()->connect();
// 				$sql = "select * from general where ownerId='{$this->user->uid}'";
// 				$generalItems = $mysql->execResultWithoutLimit($sql);
// 				$data=$generalItems;
				$data = $generalItems = GeneralItem::getItems($this->user->uid);
				if(!$generalItems)
					$data = true;
				break;
		}
		return XServiceResult::success($data);
	}
}
?>