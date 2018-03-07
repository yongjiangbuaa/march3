<?php
class GMServerAction extends XAbstractAction {
	protected $params;
	protected $user;
	protected $mysql;
	public function execute(XAbstractRequest $request){
		
		$this->params = $request->getParameters();
		//rest接口
		if($this->params['type'] == null)
			$this->params = $this->params['data']['params'];
		$data = array();
		switch ($this->params['type']){
			case 1: // 查看玩家列表
				$limit = 100;
				$className = 'userprofile';
				$where = '';
				if($this->params['levelMin'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' level > '.$this->params['levelMin'];
				}
				if($this->params['levelMax'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' level < '.$this->params['levelMax'];
				}
				if($this->params['regMin'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' registerTime > '.strtotime($this->params['regMin']);
				}
				if($this->params['regMax'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' registerTime < '.strtotime($this->params['regMax']);
				}
				if($this->params['loadMin'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' lastLoadTime < '.(time() - $this->params['loadMin'] * 3600);
				}
				if($this->params['loadMax'] !== null){
					if($where)
						$where .= ' and';
					else
						$where = 'where';
					$where .= ' lastLoadTime > '.(time() - $this->params['loadMax'] * 3600);
				}
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$count = $mysql->execResult("select count(1) DataCount from {$className} {$where}");
				$count = $count[0]['DataCount'];
				//实现分页
				$pager = self::page($count, $this->params['page'], $limit);
				$index = $pager['offset'];
				$order = '';
				if($this->params['byvip'])
					$order .= "order by vip desc";
				if($this->params['bylevel'])
				{
					if($order)
						$order .= ",level desc";
					else
						$order .= "order by level desc";
				}
				import('service.item.AllianceItem');
				import('service.item.CityItem');
				$xmlDataConfig = ItemSpecManager::singleton()->getItem('player_warnum');
				$sql = "select * from {$className} {$where} {$order} limit {$index},{$limit}";
				$result = $mysql->execute($sql);
				$i = 0;
				import('service.action.GeneralClass');
				$data = array();
				if ($result) {
					while ($curRow = mysql_fetch_assoc($result) )
					{
						//league
						$league = null;
						if($curRow['league']){
							$league = AllianceItem::getWithUID($curRow['league']);
						}
						$city = CityItem::getWithUID($curRow['uid']);
						$data['data'][] = array(
							'num' => $i++,
							'name' => $curRow['name'],
							'uid' => $curRow['uid'],
							'platform' => $curRow['platformAddress'],
							'gender' => $curRow['gender'],
							'vip' => $curRow['vip'],
							'level' => $curRow['level'],
							'gold' => $curRow['user_gold'],
// 							'fightPower' => General::singleton()->setUserUid($curRow['uid'])->getUserFightPower(),
							'silver' => $city->money,
							'pve' => $curRow['pveTimes'],
							'buyPve' => $curRow['buyPveTimes'],
							'canPve' => $curRow['extraPveTimes'] + $xmlDataConfig->k1 - $curRow['pveTimes'],
							'league' => $league->name,
							'active_point' => $curRow['active_point'],
							'country' => $curRow['country'],
							'lastOnline' => $curRow['lastLoadTime'],
							'register' => $curRow['registerTime'],
							'onlineTime' => $curRow['playerOnlineTime'],
							'chat' => $curRow['speakingForbid'],
							'forbid' => $curRow['seize'],
							'gm' => $curRow['gmFlag'],
						);
					}
				}
				$data['pager'] = $pager['pager'];
				$data['total'] = $count;
				break;
			case 2://发送多种物品
				import("service.item.CityItem");
				$userlist = $this->params['params']['userList'];
				$itemlist = $this->params['params']['itemList'];
				$reward = $this->params['params'];
				$items = explode(',',$itemlist);
				$successList = array();
				$failList = array();
				foreach ($items as $key=>&$item)
				{
					$item = explode('_',trim($item));
					if($item[0] == null)
						unset($items[$key]);
				}
				$users = explode(',',$userlist);
				foreach ($users as $user)
				{
					if($user == null)
						continue;
					$currentUser = null;
					if($this->params['params']['userType'] == 'name')
					{
						$currentUser = UserProfile::getWithName($user);
					}
					if($this->params['params']['userType'] == 'id')
					{
						$currentUser = UserProfile::getWithUID($user);
					}
					if($this->params['params']['userType'] == 'plat')
					{
						import("service.user.PlatformProfile");
						$platformProfile = PlatformProfile::getWithUID($user);
						$currentUser = UserProfile::getWithUID($platformProfile->userUID);
					}
					if(!isset($currentUser))
					{
						$failList[] = $user;
						continue;
					}
					//数据更新重新登录
					$currentUser->onLoadKey = md5($user->uid . microtime(true));
					$currentUser->save();
					$successList[] = $user;
					//添加物品
					import('service.item.InventoryItem');
					import('service.action.InventoryClass');
					import('service.item.ItemSpecManager');
					$inventory = new Inventory($currentUser);
					for($i = 0;$i < count($items);$i++)
					{
						$item = $items[$i];
						$inventoryItem = null;
						$xmlGoods = null;
						$xmlGoods = ItemSpecManager::singleton('default', 'goods.xml')->getItem($item[0]);
						$inventory->addGoods($item[0],$item[1],$item[2]);
// 						if($xmlGoods)
// 						{
// 							//不可叠加
// 							$inventoryItem = $inventory->getGoodsByItemID($item[0],false);
// 							if($xmlGoods->overlap != 1){
// 								if($inventoryItem){
// 									$inventoryItem->increase('count', $item[2]);
// 									$inventoryItem->save();
// 								}else{
// 									$inventoryItem = new InventoryItem();
// 									$inventoryItem->ownerId = $currentUser->uid;
// 									$inventoryItem->level = $item[1];
// 									$inventoryItem->count = $item[2];
// 									$inventoryItem->itemId = $item[0];
// 									$inventoryItem->uid = getGUID();
// 									$inventoryItem->save();
// 								}
// 							}
// 							else{
// 								for ($j = 0;$j<$item[2];$j++){
// 									$inventoryItem = new InventoryItem();
// 									$inventoryItem->ownerId = $currentUser->uid;
// 									$inventoryItem->level = $item[1];
// 	// 								$inventoryItem->count = $item[2];
// 									$inventoryItem->count = 1;
// 									$inventoryItem->itemId = $item[0];
// 									$inventoryItem->uid = getGUID();
// 									$inventoryItem->save();
// 								}
// 							}
// 						}
					}
					//其他数据
					if($reward['gold'] > 0)
					{
						$currentUser->user_gold += $reward['gold'];
						$currentUser->save();
					}
					if($reward['money'] > 0 || $reward['mineral'] > 0 || $reward['oil'] > 0 || $reward['food'] > 0 || $reward['soldiers'])
					{
						$currentUserCity = CityItem::getWithUID($currentUser->uid);
						$reward['money'] > 0 ? $currentUserCity->money += $reward['money']:0;
						$reward['mineral'] > 0 ? $currentUserCity->mineral += $reward['mineral']:0;
						$reward['oil'] > 0 ? $currentUserCity->oil += $reward['oil']:0;
						$reward['food'] > 0 ? $currentUserCity->food += $reward['food']:0;
						$reward['soldiers'] > 0 ? $currentUserCity->soldiers += $reward['soldiers']:0;
						$currentUserCity->save();
					}
				}
				$data = array('exec'=>true,'success'=>$successList,'fail'=>$failList,'reward'=>$reward,'item'=>$items);
				break;
			case 3: //删除用户所有数据
				if($this->params['code'] != 'ik2Gm')
					return XServiceResult::clientError('code error');
				$uid = $this->params['uid'];
				if($this->params['plat'])
				{
					import("service.user.PlatformProfile");
					$platformProfile = PlatformProfile::getWithUID($this->params['plat']);
					if(!$platformProfile)
						break;
					$uid = $platformProfile->userUID;
				}
				import('persistence.dao.RActiveRecord');
// 				self::removeItem('EffectItem',$uid);
// 				self::removeItem('CityItem', $uid);
// 				self::removeItem('GeneralInitItem',$uid);
// 				self::removeItem('GeneralRankItem',$uid);
// 				import('service.item.GeneralItem');
// 				$generalItems = GeneralItem::getItems($uid);
// 				foreach ($generalItems as $generalItem)
// 				{
// 					self::removeItem('GeneralSkillItem',$generalItem['uid']);
// 				}
// 				self::removeItem('LordItem',$uid);
// 				self::removeItem('PowerItem',$uid);
// 				self::removeItem('QuestRecordItem',$uid);
// 				self::removeItem('StarGeneralItem',$uid);
// 				self::removeItem('TrainGridItem',$uid);
// 				self::removeItem('UserShopRecordItem',$uid);
// 				self::removeItem('UnitItem',$uid);
// 				import('util.mysql.XMysql');
// 				$this->mysql = XMysql::singleton()->connect();
// 				self::removeMysqlData('arena','uid',$uid);
// 				self::removeMysqlData('arenarecord','attacker',$uid);
// 				self::removeMysqlData('arenarecord','defender',$uid);
// 				self::removeMysqlData('building','ownerId',$uid);
// // 				self::removeMysqlData('fightbehavior','fromUser',$uid);
// // 				self::removeMysqlData('fightbehavior','toUser',$uid);
// 				self::removeMysqlData('formation','ownerId',$uid);
// 				self::removeMysqlData('friend','ownerId',$uid);
// 				self::removeMysqlData('friend','playerUid',$uid);
// 				self::removeMysqlData('general','ownerId',$uid);
// 				self::removeMysqlData('inventory','ownerId',$uid);
// 				self::removeMysqlData('mail','fromUser',$uid);
// 				self::removeMysqlData('mail','toUser',$uid);
// // 				self::removeMysqlData('messageball','fromUser',$uid);
// // 				self::removeMysqlData('messageball','toUser',$uid);
// // 				self::removeMysqlData('proclaimwar','fromUser',$uid);
// // 				self::removeMysqlData('proclaimwar','toUser',$uid);
// 				self::removeMysqlData('pverecord','ownerId',$uid);
// 				self::removeMysqlData('quest','ownerId',$uid);
// 				self::removeMysqlData('science','ownerId',$uid);
				
// 				import("service.user.PlatformProfile");
// 				import('service.tutorial.Tutorial');
// 				import("service.user.UserProfile");
// 				try {
					$user = UserProfile::getWithUID($uid);
					if($user)
					{
						$platformProfile = PlatformProfile::getWithUID($user->platformAddress);
						if($platformProfile)
							$platformProfile->remove();
						$user->name = 'removed_'.time();
						$user->save();
					}
// 						$tutorial = Tutorial::getWithUID($uid);
// 						if($tutorial)
// 							$tutorial->remove();
// 						import('util.cache.XCache');
// 						$cache = XCache::singleton();
// 						$cache->setKeyPrefix('IK2');
// 						$cache->delete('USERPROFILE_'.$user->uid);
// 						$user->remove();
// // 						$this->mysql->execute("delete from `UserProfile_name` where `key_`='$uid'");
// 					}
// 				} catch (Exception $e) {
// // 					$client=KVStorageService::getService();
// // 					$persistenceSession = PersistenceSession::singleton();
// // 					import('persistence.orm.StreamHelper');
// // 					$streamHelper = new StreamHelper($persistenceSession);
// // 					$propertyListString = $client->get('UserProfile', ((string)$uid));
// // 					if($propertyListString)
// // 					{
// // 						$propertyListString = str_ireplace("\r", '#&01', $propertyListString);
// // 						$propertyListString = str_ireplace("\t", '#&02', $propertyListString);
// // 						$user = $streamHelper->Json2Object('UserProfile',$propertyListString);
	
// // 						$platformAddress = $user->platformAddress;
// // 						$platformAddress = str_ireplace('#&01', "\r", $platformAddress);
// // 						$platformAddress = str_ireplace('#&02', "\t", $platformAddress);
// // 						$propertyListString = $client->get('PlatformProfile', ((string)$platformAddress));
// // 						if($propertyListString)
// // 						{
// // 							$propertyListString = str_ireplace("\r", '#&01', $propertyListString);
// // 							$propertyListString = str_ireplace("\t", '#&02', $propertyListString);
// // 							$platformProfile = $streamHelper->Json2Object('PlatformProfile',$propertyListString);
// // 							$platformProfile->platformAddress = $platformAddress;
// // 							$platformProfile->remove();
// // 						}
// // 						$user->remove();
// // 						$this->mysql->execute("delete from `UserProfile_name` where `key_`='$uid'");
// // 					}
// 				}
				$data = $user;
				break;
			case 4: //发布即时公告
				import('service.action.ChatClass');
				$data['contents']=$this->params['data'];
				$data['mode'] = 7;
				$data['modeValue'] = '0';
// 				$data['fromName']='';
				Chat::message()->setContents($data)->sendOneMessage();
				$data['mode'] = 6;
				$data['param'] = 'all';
				$data['contents'] = $this->params['data'];
				Chat::message()->setContents($data)->sendOneMessage();
				break;
			case 5://同步数据库公告到缓存
				import('util.cache.XCache');
				$cache = XCache::singleton();
				$cache->setKeyPrefix('IK2');
				$announceKeyList = 'ANNOUNCE_LIST';
				$announceMemKey = 'ANNOUNCE_';
				
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from serverannounce order by startTime";
				$maxEnd = $currentTime = time();
				$announceKeys = array();
				$result = $mysql->execute($sql);
				if ($result) {
					while ($curRow = mysql_fetch_assoc($result) ) {
						if($curRow['endTime'] < $currentTime)
							continue;
						$announceKeys[] = $curRow['uid'];
						$cache->set($announceMemKey.$curRow['uid'],$curRow,$curRow['endTime'] - $currentTime);
						if($curRow['endTime'] > $maxEnd)
							$maxEnd = $curRow['endTime'];
					}
					$cache->set($announceKeyList,$announceKeys,$maxEnd - $currentTime);
				}
				break;
			case 6://查询mysql中的公告和缓存中的公告
				$data['result'] = true;
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$sql = "select * from serverannounce order by startTime";
				$rowResults = array();
				$result = $mysql->execute($sql);
				if ($result) {
					while ($curRow = mysql_fetch_assoc($result) ) {
						$rowResults[] = $curRow;
					}
				}
				$data['db'] = $rowResults;
 				$data['memcache'] = array();
				import('util.cache.XCache');
				$announceKeyList = 'ANNOUNCE_LIST';
				$announceMemKey = 'ANNOUNCE_';
				$cache = XCache::singleton();
				$cache->setKeyPrefix('IK2');
				$announceKeys = $cache->get($announceKeyList);
				if(!$announceKeys)
					break;
				foreach ($announceKeys as $announceKey)
				{
					$temp = $cache->get($announceMemKey.$announceKey);
					if($temp)
						$data['memcache'][] = $temp;
				}
				break;
			case 7://从memcache中删除选定公告
				import('util.cache.XCache');
				$cache = XCache::singleton();
				$cache->setKeyPrefix('IK2');
				$announceMemKey = 'ANNOUNCE_';
				$uid = $this->params['data']['uid'];
				$cache->delete($announceMemKey.$uid);
				break;
			case 8: //加入激活码
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$param = $this->params['data'];
				$tableIndex = array('delivery','userLimit','startTime','endTime','goods','playerName','playerUid','receiveTime');
				foreach ($param as $key=>$value)
				{
					if(!in_array($key, $tableIndex))
						unset($param[$key]);
				}
				for($i=0;$i<$this->params['data']['counts'];$i++)
				{
					$param['uid']=getGUID();
					$mysql->add('code', $param);
				}
				$data = true;
				break;
			case 9: //查询激活码
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				if($this->params['data']['getdata'] == 'search')
				{
					$count = $mysql->execResult("select count(1) DataCount from code where delivery='{$this->params['data']['batch']}' ORDER BY delivery");
					$count = $count[0]['DataCount'];
					$data['page'] = self::page($count, $this->params['data']['page'], 100);
					$offset = ($this->params['data']['page'] - 1) * 100;
					$result = mysql_query("SELECT * FROM code where delivery='{$this->params['data']['batch']}' ORDER BY delivery limit $offset,100");
				}elseif($this->params['data']['action'] == 'output')
				{
					$result = mysql_query("SELECT * FROM code where delivery='{$this->params['data']['event']}' ORDER BY delivery");
				}
				$i=0;
				while($row = mysql_fetch_array($result))
				{
					$data['code'][$i]=$row;
					$i++;
				}
				break;
			case 10: //删除激活码
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				
				$uid=$this->params[data][uid];
				mysql_query("DELETE FROM code WHERE uid = '$uid'");
				break;
			case 11: //删除过期激活码
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton()->connect();
				$result = mysql_query("SELECT * FROM code ORDER BY delivery");
				$i=0;
				$curr_time=time();
				while($row = mysql_fetch_array($result))
				{
					$end_time = $row['endTime'];
					if($curr_time<$end_time)
						continue;			//未过期
					$data[$i]=$row;
					mysql_query("DELETE FROM code WHERE uid = '$row[uid]'");
					$i++;
				}		
				break;
			case 12://设置双倍活动
				import('service.item.ServiceConfigItem');
				$data = $temp = $this->params['data'];
				$temp['endTime'] = strtotime($temp['endTime']);
				$temp['startTime'] = strtotime($temp['startTime']);
				ServiceConfigItem::unsetDouble('config');//先取消上一次的双倍活动;
				ServiceConfigItem::setDouble($temp, 'config');
			case 13://查询双倍活动;
				import('service.item.ServiceConfigItem');
				$data=ServiceConfigItem::viewDouble();
				break;
			case 14://取消双倍活动
				import('service.item.ServiceConfigItem');
				$data=ServiceConfigItem::unsetDouble('config');
				$data=ServiceConfigItem::viewDouble();
				break;
			case 15://聊天监控
				$user = $this->params['data']['user'];
				$data['result'] = true;
				$data['chat'] = array();
				import('util.cache.XCache');
				$cache = XCache::singleton();
				$cache->setKeyPrefix('IK2');
				$userCountFlag = $cache->get('CHAT_USER_'.$user);
				if(!$userCountFlag){
					$userCountFlag = 0;
				}
				$messageTotalCount = $cache->get('CHAT_MESSAGE_COUNT');
				if(!$messageTotalCount || $userCountFlag == $messageTotalCount){
					break;
				}
				if($userCountFlag <= 0 || $userCountFlag > $messageTotalCount){
					$userCountFlag = max(0, $messageTotalCount - 50);
				}
				for ($i = $userCountFlag; $i < $messageTotalCount; $i++){
					$message = $cache->get('CHAT_MESSAGE_LIST_' . $i);
					if($message && !in_array($message['mode'], array(11,12))){
						if($message['mode'] == 3){//联盟
							import('service.item.AllianceItem');
							$message['modeValue'] = AllianceItem::getWithUID($message['modeValue'])->name;
							if(is_array($message['contents'])){
								switch($message['contents']['type']){
									case 1:
										$message['contents'] = "盟主".$message['contents']['para1']."将盟主之位传给".$message['contents']['para2'];
										break;
									case 2:
										$message['contents'] = $message['contents']['para1']."加入了本盟，联盟的实力又增强了";
										break;
									case 3:
										$message['contents'] = "<font color='#ff0000'>" . $message['contents']['para1'] . "退出了联盟</font>";
										break;
									case 4:
										$message['contents'] = $message['contents']['para1']."战功显赫，被任命为副盟主";
										break;
									case 5:
										$message['contents'] = "<font color='#ff0000'>" . $message['contents']['para1'] . "将" . $message['contents']['para2'] . "开除出联盟</font>";
										break;
									case 6:
										$message['contents'] = "<font color='#ff0000'>" . $message['contents']['para1'] . "被降职为成员</font>";
										break;
									case 7:
										$message['contents'] = "<font color='#ff0000'>" . $message['contents']['para1'] . "成功弹劾了".$message['contents']['para2']."，成为新一任联盟盟主</font>";
										break;
									case 8:
// 										import('service.item.ItemSpecManager');
// 										$xml = ItemSpecManager::singleton('cn','item.xml')->getItem($message['contents']['para1']);
// 										$message['contents'] = "<font color='#ff0000'>联盟成功占领" . $xml->name . "（". $xml->position ."）</font>";
										$message['contents'] = "<font color='#ff0000'>联盟成功占领" . $message['contents']['para1'] . "（". $message['contents']['para1'] ."）</font>";
										break;
									case 9:
// 										import('service.item.ItemSpecManager');
// 										$xml = ItemSpecManager::singleton('cn','item.xml')->getItem($message['contents']['para1']);
// 										$xml2 = ItemSpecManager::singleton('cn','item.xml')->getItem($message['contents']['para2']);
// 										$message['contents'] = "<font color='#ff0000'>联盟占领的".$xml->name."（".$xml->position."）被".$xml2->name."联盟" . $message['contents']['para3'] . "攻占了</font>";
										$message['contents'] = "<font color='#ff0000'>联盟占领的".$message['contents']['para1']."（".$message['contents']['para1']."）被".$message['contents']['para2']."联盟" . $message['contents']['para3'] . "攻占了</font>";
										break;
								}
							}
						}else if($message['mode'] == 4){//私聊
							$message['modeValue'] = UserProfile::getWithUID($message['modeValue'])->name;
						}
						$data['chat'][] = $message;
					}
				}
				$cache->set('CHAT_USER_'.$user, $messageTotalCount, 30);
				break;
			case 16://发送单人邮件
				$params = $this->params['data'];
				switch ($params['userType']){
					case 'plat'://平台ID
						$platformProfile = PlatformProfile::getWithUID($params['user']);
						$user = UserProfile::getWithUID($platformProfile->userUID);
						break;
					case 'name'://角色名
						$user = UserProfile::getWithName($params['user']);
						break;
					case 'uid'://UID
						$user = UserProfile::getWithUID($params['user']);
						break;						
				}
				if(!$user)
					return XServiceResult::clientError('user not found');
				$temp = array();
				foreach ($params as $key=>$value){
					if(substr($key,0,6) != 'reward' || $value == null)
						continue;
					$realKey = substr($key,7,strlen($key));
					$temp[$realKey] = $value;
				}
				$reward = '';
				$checkArr = array('item'=>'number','item1'=>'number1','item2'=>'number2','item3'=>'number3','item4'=>'number4','item5'=>'number5','item6'=>'number6','item7'=>'number7');
				$appendArr = array('item'=>'rate','item1'=>'rate1','item2'=>'rate2','item3'=>'rate3','item4'=>'rate4','item5'=>'rate5','item6'=>'rate6','item7'=>'rate7');
				if($temp['general'] && !ItemSpecManager::singleton('default','general.xml')->getItem($temp['general'])){
					unset($temp['general']);
				}
				foreach ($checkArr as $a=>$b){
					if($temp[$a] && !$temp[$b])
						unset($temp[$a]);
					elseif($temp[$b] && !$temp[$a])
						unset($temp[$b]);
					elseif(!ItemSpecManager::singleton('default', 'goods.xml')->getItem($temp[$a])){
						unset($temp[$a]);
						unset($temp[$b]);
					}
				}
				foreach ($temp as $key=>$value){
					if($reward)
						$reward .= '|';
					$reward .= $key.','.$value;
					//TODO由于rewardclass改造这里可以用新的方式
					if(in_array($key, array_keys($appendArr)))
						$reward .= '|'.$appendArr[$key].',100000';
				}
				import('service.item.MailItem');
				MailItem::addMail(null, $user, 1, $params['title'], $params['contents'], 0, null, $reward);
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$addOne = array('uid'=>getGUID(),'sendBy'=>$params['sendBy'],'sendTime'=>time(),'toUser'=>$user->uid,'title'=>$params['title'],'contents'=>$params['contents'],'rewardId'=>$reward);
				$mysql->add('serverusermail', $addOne);
				$data = array('userUid'=>$user->uid,'userName'=>$user->name,'mail'=>$addOne['uid'],'title'=>$addOne['title'],'contents'=>$addOne['contents'],'sendTime'=>time(),'reward'=>$temp);
				break;
			case 17://查询联盟成员
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$result = $mysql->execute($this->params['sql']);
				if ($result) {
					while ($member = mysql_fetch_assoc($result) )
					{
						$user = UserProfile::getWithUID($member['MemberId']);
						$data['member'][] = array('uid'=>$member['uid']
								,'type'=>$member['type']
								,'status'=>$member['status']
								,'name'=>$user->name
								,'level'=>$user->level
								,'vip'=>$user->vip
								,'userUid'=>$user->uid
								,'contribution'=>$member['contribution']
								,'power'=>$member['power']);
					}
				}
				break;
			case 18://时间段注册用户统计
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$dayStart = $this->params['start']?strtotime($this->params['start']):0;
				$dayEnd = $this->params['end']?strtotime($this->params['end']):0;
				$loginDay = $this->params['relogin']?strtotime($this->params['relogin']):0;
// 				$from = "(select user from registerdata where `timeStamp`>={$dayStart} and `timeStamp`<{$dayEnd})";
// 				$from = "(select distinct(a.user) from registerdata as a left join logindata as b on a.`user` = b.`user` where a.`timeStamp`>={$dayStart} and a.`timeStamp`<{$dayEnd} and b.`timeStamp` >= {$loginDay})";
				$from = "as ori inner join registerdata as a on ori.uid = a.`user` inner join logindata as b on ori.uid = b.`user` where a.`timeStamp`>={$dayStart} and a.`timeStamp`<{$dayEnd} and b.`timeStamp` >= {$loginDay}";
				$from .= " group by ori.uid";
				$levelArr = array();
				$countryArr = array('0'=>0,'8300'=>0,'8301'=>0,'8302'=>0,'8303'=>0);
				$sql = "select level,country,test,count(1) as total from (select level,country,test from userprofile $from) as a group by country,test,level order by test asc,level asc";
				$result = $mysql->execute($sql);
				if ($result) {
					while ($row = mysql_fetch_assoc($result) )
					{
						$levelArr[$row['test']][$row['level']] += $row['total'];
						$countryArr[$row['country']] += $row['total'];
					}
				}
				ksort($countryArr);
				ksort($levelArr);
				$data['level'] = $levelArr;
				$data['country'] = $countryArr;
				
				$powerArr = array();
				$sql = "select powerList from power $from";
				$result = $mysql->execute($sql);
				if ($result) {
					while ($row = mysql_fetch_assoc($result) )
					{
						$power = json_decode($row['powerList'],true);
						foreach ($power as $key=>$powerGroup){
							foreach ($powerGroup as $powerId=>$value){
								if($powerId > 0)
									$powerArr[$powerId]++;
							}
						}
					}
				}
				ksort($powerArr);
				$sort = $tutorial = array();
				foreach ($powerArr as $powerId=>$count){
					$sort[$powerId] = $powerId;
					$tutorial[$powerId] = $powerId >= 129801 ? 1 : 0;
				}
				array_multisort($powerArr,SORT_DESC,$tutorial,SORT_ASC,$sort);
				$data['power'] = array_combine($sort, $powerArr);
				
				$questrecordArr = array();
				$questrecordCountArr = array();
				$sql = "select questList from questrecord $from";
				$result = $mysql->execute($sql);
				if ($result) {
					while ($row = mysql_fetch_assoc($result) )
					{
						$quest = json_decode($row['questList'],true);
						foreach ($quest as $questId=>$count){
							$questrecordCountArr[$questId]+= $count;
							$questrecordArr[$questId]++;
						}
					}
				}
				ksort($questrecordArr);
				foreach ($questrecordArr as $questId=>$count){
					$questXml = ItemSpecManager::singleton('default','quest.xml')->getItem($questId);
					switch ($questXml->type1){
						case 0:
							$missionType = substr($questId,1,1);
							if (0 == $missionType || 4 == $missionType)
								$questType = 'main';
							else //1 2 3 支线
								$questType = substr($questId,0,4);
							break;
						case 2://日常
							$questType = 'daily';
							break;
						case 3://联盟
							$questType = 'league';
							break;
						default:
							$questType = 'undefined';
							break;
					}
					$data['quest'][$questType][$questId] = $count;
					if($questType=='daily')
						$data['quest']['count_'.$questType][$questId] = $questrecordCountArr[$questId];
				}
				break;
			case 19://发送全服邮件
				$params = $this->params['data'];
				$temp = array();
				foreach ($params as $key=>$value){
					if(substr($key,0,6) != 'reward' || $value == null)
						continue;
					$realKey = substr($key,7,strlen($key));
					$temp[$realKey] = $value;
				}
				$reward = '';
				$checkArr = array('item'=>'number','item1'=>'number1','item2'=>'number2','item3'=>'number3','item4'=>'number4','item5'=>'number5','item6'=>'number6','item7'=>'number7');
				$appendArr = array('item'=>'rate','item1'=>'rate1','item2'=>'rate2','item3'=>'rate3','item4'=>'rate4','item5'=>'rate5','item6'=>'rate6','item7'=>'rate7');
				if($temp['general'] && !ItemSpecManager::singleton('default','general.xml')->getItem($temp['general'])){
					unset($temp['general']);
				}
				foreach ($checkArr as $a=>$b){
					if($temp[$a] && !$temp[$b])
						unset($temp[$a]);
					elseif($temp[$b] && !$temp[$a])
						unset($temp[$b]);
					elseif(!ItemSpecManager::singleton('default', 'goods.xml')->getItem($temp[$a])){
						unset($temp[$a]);
						unset($temp[$b]);
					}
				}
				foreach ($temp as $key=>$value){
					if($reward)
						$reward .= '|';
					$reward .= $key.','.$value;
					if(in_array($key, array_keys($appendArr)))
						$reward .= '|'.$appendArr[$key].',100000';
				}
				$startTime = strtotime($params['start_time']);
				$endTime = strtotime($params['end_time']);
				$registerTime = strtotime($params['register_time']);
				$mailTitle = $params['title'];
				$mailContents = $params['contents'];
				$levelMin = $params['levelMin'];
				$levelMax = $params['levelMax'];
				$league = $params['league'];
				$uid = md5($startTime.$endTime.$registerTime.$levelMin.$levelMax.$league.$mailTitle.$mailContents.$reward.time());
				$sql = "INSERT INTO `servermail` (`uid`, `startTime`, `endTime`, `registerTime`, `levelMin`, `levelMax`, `league`, `title`, `contents`, `rewardId`) VALUES ('$uid', '$startTime', '$endTime', '$registerTime', '$levelMin', '$levelMax', '$league', '$mailTitle', '$mailContents', '$reward')";
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$mysql->execute($sql);
				$data = true;
				break;
			case 20: //复制用户所有数据
				$tables = array(
						'building'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//建筑
						,'cdalignment'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//CD
						,'exploit'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//成就
						,'formation'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array('generalList'))//阵法
						,'general'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//将领
						,'inventory'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array('gem','useGeneralId','embed'))//物品	去掉gem usegeneral
						,'quest'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//任务
						,'science'=>array('generate'=>array('uid'),'user'=>'ownerId','clear'=>array())//科技
						,'city'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//城市
						
						,'effect'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//物品效果
						,'generaleffect'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//将军特效
						,'generalrank'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//
						,'generalskill'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//将军技能
						,'militarydrill'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//联合军演
						,'questrecord'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//任务完成列表
						,'recruit'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//招募武将
						,'power'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//PVE进度
						,'lord'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//君主
						,'medal'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//勋章
						,'sign'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//签到
						,'silvermine'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//银矿
						,'traingrid'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//训练武将
						,'unit'=>array('generate'=>array(),'user'=>'uid','clear'=>array())//兵种
						);
				$oldUid = $this->params['uid'];
				$user = UserProfile::getWithUID($oldUid);
				if($user){
					$oldAddress = $user->platformAddress;
					$thisPlatform = explode('ik2', $oldAddress);
					$thisPlatform = end($thisPlatform);
					$newAddress = $this->params['code'].'_ik2'.$thisPlatform;
					import('persistence.dao.RActiveRecord');
					import("service.user.PlatformProfile");
					$platformProfile = PlatformProfile::getWithUID($newAddress);
					if($platformProfile){
						return XServiceResult::clientError('PlatformProfile exists');
					}
					$newUid = getGUID();
					$platformProfile = new PlatformProfile();
					$platformProfile->platformAddress = $newAddress;
					$platformProfile->userUID = $newUid;
					$platformProfile->save();
					$temp = $user->asArray();
					$userProfile = new UserProfile();
					$userProfile->setAttrs($temp);
					$userProfile->setSaved(false);
					$userProfile->league = null;
					$userProfile->platformAddress = $newAddress;
					$userProfile->uid = $newUid;
					
					import('util.mysql.XMysql');
					$mysql = XMysql::singleton()->connect();
					$tempName = $userProfile->name;
					$i = 1;
					while ($mysql->exist('userprofile', array('name'=>$tempName)) != 0)
					{
						$tempName = $userProfile->name.'_'.$i++;
					}
					$userProfile->name = $tempName;
					$userProfile->save();
					$generalModify = array();
					foreach ($tables as $table=>$tableAlter){
						if($table == 'generalskill'){
							foreach ($generalModify as $oldGeneral=>$newGeneral){
								$sqlDatas = $mysql->get($table,array('uid'=>$oldGeneral));
								$temp = $sqlDatas[0];
								if($temp){
									$temp['uid'] = $newGeneral;
									$mysql->add($table,$temp);
								}
							}
							continue;
						}
						$sqlDatas = $mysql->get($table,array($tableAlter['user']=>$oldUid),null,100);
						if($sqlDatas){
							foreach ($sqlDatas as $sqlData){
								$temp = $sqlData;
								foreach ($tableAlter['clear'] as $clearColumn){
									unset($temp[$clearColumn]);
								}
								foreach ($tableAlter['generate'] as $generateColumn){
									$temp[$generateColumn] = getGUID();
								}
								$temp[$tableAlter['user']] = $newUid;
								if($table == 'general'){
									if($temp['type2'] == 1)
										$temp['name'] = $tempName;
									$generalModify[$sqlData['uid']] = $temp['uid'];
								}
								$mysql->add($table,$temp);
							}
						}
					}
					$data = $newUid;
				}else{
					return XServiceResult::clientError('user not exists');
				}
				break;
			case 21://发送邮件的记录
				$addOne = $this->params['data'];
				import('util.mysql.XMysql');
				$mysql = XMysql::singleton();
				$mysql->add('serverusermaillog', $addOne);
				$data = true;
				break;
			case 22:
				import('service.action.WorldClass');
				$data = World::singletion()->mergeWorldData(30);
				if ($data == false) {
					return XServiceResult::clientError('merge world data error');
				}
				break;
			case 23://合服竞技场初始化
				import('service.action.ArenaClass');
				$data = Arena::combineArenaInit();
				if ($data == false) {
					return XServiceResult::clientError('arena init error');
				}
				break;				
		}
		return XServiceResult::success($data);
	}
	
	private function removeItem($itemName,$uid){
		try {
			$className = "service.item.".$itemName;
			import($className);
			$item = $itemName::getWithUID($uid);
			if($item)
				$item->remove();
		} catch (Exception $e) {
		}
	}
	
	private function removeMysqlData($tableName,$userColumn,$user){
		$this->mysql->execute("delete from `$tableName` where `$userColumn`='$user'");
	}
	
	/**
	 * 分页
	 *
	 * @param Number $total
	 * @param Number $curr_page
	 * @param Number $page_limit
	 */
	static function page($total, $curr_page, $page_limit){
		$page = empty($curr_page) ? 1 : $curr_page;
		$last_page = ceil($total/$page_limit); //最后页，也是总页数
		$page = min($last_page, $page);
		$prepg = $page - 1; //上一页
		$nextpg = ($page == $last_page ? 0 : $page + 1); //下一页
		$offset = intval(max($page - 1, 0) * $page_limit);
		
		//开始分页导航条代码：
		$pagenav="显示第 <B>".($total ? ($offset + 1):0)."</B>-<B>".min($offset + $page_limit ,$total)."</B> 条记录，共 $total 条记录";
		//如果只有一页则跳出函数：
		if($last_page<=1) return array('offset' => $offset, 'pager' => NULL);
		$pagenav.=" <a href='#' onclick='getData(1)'>首页</a> ";
		if($prepg) $pagenav.=" <a href='#' onclick='getData({$prepg})'>前页</a> "; else $pagenav.=" 前页 ";
		if($nextpg) $pagenav.=" <a href='#' onclick='getData({$nextpg})'>后页</a> "; else $pagenav.=" 后页 ";
		$pagenav.=" <a href='#' onclick='getData({$last_page})'>尾页</a> ";
		$pagenav.=" 第{$page}页  共 {$last_page} 页";
		$pagenav .= " 跳转 <input size=3 id='turn' onKeyUp='check(this)' value=''> <input type='button' value='go' onclick='turnPage()'";
		return array('offset' => $offset, 'pager' => $pagenav);
	}
}
?>