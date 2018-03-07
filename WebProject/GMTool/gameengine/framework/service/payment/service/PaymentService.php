<?php
/**
 * @Pointcut('protocol|auth')
 */
class PaymentService extends XAbstractService{
	
/**
	 * 337 en版s1服 支付回调处理。
	 * 参数要提供用户在平台的UID。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string user_id 用户UID
	 * @ServiceParam string amount amount
	 * @ServiceParam string trans_id trans_id
	 * @ServiceParam string timestamp timestamp
	 * @ServiceParam string sig sig
	 * @return XServiceResult
	 */
	public function dowarfare337Callback(XServiceRequest $request){
		$secret_key = '367fdef0dc7c012ec87c782bcb1b6cfd';
		$zoneID = $request->getParameter('zoneID');
		$platform_id = 'warfare@elex337_en_'.$zoneID;
		$result = $this->paymentHandler($request,$secret_key,$platform_id,'en');
		echo($result);
		exit();
		return $this->_success();
	}
	
	/**
	 * 337 es版fb 1服 支付回调处理。
	 * 参数要提供用户在平台的UID。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string user_id 用户UID
	 * @ServiceParam string amount amount
	 * @ServiceParam string trans_id trans_id
	 * @ServiceParam string timestamp timestamp
	 * @ServiceParam string sig sig
	 * @return XServiceResult
	 */
	public function dowarfareEnCallback(XServiceRequest $request){
		$secret_key = '367fdef0dc7c012ec87c782bcb1b6cfd';
		$zoneID = $request->getParameter('zoneID');
		$platform_id = 'warfare@facebook_en_'.$zoneID;
		$result = $this->paymentHandler($request,$secret_key,$platform_id,'en');
		echo($result);
		exit();
		return $this->_success();
	}
	/**
	 * 获得服务器列表
	 * Enter description here ...
	 */
	public function getServerList(){
		import('service.action.DataClass');
		if(StatData::$lang  == 'en') { //语言判断
			return array('1' => '10.41.163.10');
		}
		return array(
			'1'=>'10.204.154.77',
			'2'=>'10.204.154.77',
			'3'=>'10.190.162.197',
			'4'=>'10.190.162.197',
			'5'=>'10.182.47.57',
			'6'=>'10.182.47.57',
			'7'=>'10.190.163.197',
			'8'=>'10.190.163.197',
			'9'=>'10.190.162.179',
			'10'=>'10.190.162.179',
			'11'=>'10.204.189.118',
			'12'=>'10.204.189.118',
			'13'=>'10.190.166.101',
			'14'=>'10.190.166.101',
			'15'=>'10.182.60.97',
			'16'=>'10.182.60.97',
			'17'=>'10.207.255.166',
			'18'=>'10.207.255.166',
			'19'=>'10.207.255.67',
			'20'=>'10.207.255.67',
			'21'=>'10.204.184.58',
			'22'=>'10.204.184.58',
			'23'=>'10.182.39.21',
			'24'=>'10.182.39.21',
			'25'=>'10.190.165.197',
			'26'=>'10.190.165.197',
			'27'=>'10.190.168.24',
			'28'=>'10.190.168.24',
			'29'=>'10.190.168.29',
            '30'=>'10.190.168.29',
            '31'=>'10.204.200.92',
            '32'=>'10.204.200.92',
		    '33'=>'10.204.186.54',
            '34'=>'10.204.186.54',
            '35'=>'10.190.164.110',
            '36'=>'10.190.164.110',
			'37'=>'10.182.12.66',
			'38'=>'10.182.12.66',
			'39'=>'10.190.164.101',
			'40'=>'10.190.164.101',
			'41'=>'10.182.49.33',
			'42'=>'10.182.49.33',
			'43'=>'10.190.164.86',
			'44'=>'10.190.164.86',
			'45'=>'10.142.8.96',
			'46'=>'10.142.8.96',
			'47'=>'10.142.8.96',
			'48'=>'10.142.8.96',
			'49'=>'10.142.53.16',
			'50'=>'10.142.53.16',
			'51'=>'10.207.142.188',
			'52'=>'10.207.142.188',
			'53'=>'10.207.142.98',
			'54'=>'10.207.142.98',
			'55'=>'10.207.140.88',
			'56'=>'10.207.140.88',
			'57'=>'10.207.142.77',
			'58'=>'10.207.142.77',
			'59'=>'10.207.141.203',
			'60'=>'10.207.141.203',
			'61'=>'10.207.141.190',
			'62'=>'10.207.141.194',
			'63'=>'10.207.139.253',
			'64'=>'10.207.139.253',
			'65'=>'10.207.141.208',
			'66'=>'10.207.141.208',
			'67'=>'10.142.52.106',
			'68'=>'10.204.146.115',
			'69'=>'10.190.169.226',
			'70'=>'10.190.169.226',
			'71'=>'10.207.141.172',
			'72'=>'10.207.142.88',
			'73'=>'10.207.142.88',
			'74'=>'10.207.142.90',
			'75'=>'10.207.142.89',
			'76'=>'10.207.142.89',
			'77'=>'10.142.14.117',
			'78'=>'10.142.14.117',
			'79'=>'10.182.39.66',
			'80'=>'10.182.39.66',
			'81'=>'10.190.171.165',
			'82'=>'10.190.171.165',
			'83'=>'10.207.255.74',
			'84'=>'10.207.255.74',
			'85'=>'10.207.141.239',
			'86'=>'10.204.186.63',
			'87'=>'10.204.178.56',
			'88'=>'10.207.142.82',
			'89'=>'10.207.142.82',
			'90'=>'10.207.142.82',
			'91'=>'10.207.142.82',
			'92'=>'10.190.164.74',
			'93'=>'10.142.15.106',
			'94'=>'10.190.165.194',
			'95'=>'10.204.193.51',
			'96'=>'10.204.193.51',
			'97'=>'10.204.181.73',
			'98'=>'10.204.187.83',
			'99'=>'10.182.40.26',
			'100'=>'10.204.158.95',
			'101'=>'10.142.25.19',
			'102'=>'10.207.142.84',
			'103'=>'10.207.142.183',
			'104'=>'10.207.142.96',
			'105'=>'10.204.185.69',
			'106'=>'10.182.2.84',
			'107'=>'10.207.141.205',
			'108'=>'10.204.204.65',
			'109'=>'10.204.180.108',
			'110'=>'10.207.142.79',
			'111'=>'10.182.13.61',
			'112'=>'10.207.255.54',
			'113'=>'10.207.142.86',
			'114'=>'10.207.142.81',
			'115'=>'10.190.171.161',
			'116'=>'10.190.171.166',
			'117'=>'10.190.160.157',
			'118'=>'10.204.192.72',
			'119'=>'10.207.142.87',
			'120'=>'10.204.153.109',
			'121'=>'10.182.13.104',
		);
	}
	/**
	 * 集市回调，分配发货服务器
	 * @param XServiceRequest $request
	 */
	public function doProvideAward(XServiceRequest $request){
		$params = $request->getParameters();
		$zoneid = $params['zoneid'] + 1;
		$params['zoneid'] = $zoneid;
		//根据zoneid分配发货服
		$serverList = $this->getServerList();
		$timeout = 30;
		$server = $serverList[$zoneid];
		if(!$server)
		{
			return array("ret" => 4, "msg" => "no server set");
		}
		$url = "http://$server:9001/warfare/rest/payment/payment/MarketSendGoods";
		if (!isset($params['payitem']))
		{
			$url = "http://$server:9001/warfare/rest/payment/payment/CheckCompletion";
		}
		$headers = array("Content-Type: application/x-www-form-urlencoded");
		$curlopt_header = false;
		$ch = curl_init();
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		if (is_array($params) && count($params) > 0) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
		}
		if ($curlopt_header) curl_setopt($ch, CURLOPT_HEADER, true);
		if (is_array($headers) && count($headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$result = curl_exec($ch);
		header("Content-Type:text/html; charset=utf-8");
		echo $result;
	}
	
	public function doSendAnnounce (XServiceRequest $request) {
		$params = $request->getParameters();
		$lang = $params['lang'];
		import('service.action.DataClass');
		StatData::$lang = $lang;
		$itemId = $params['itemId'];
		$contentParam = $params['data'];
		import('service.item.ItemSpecManager');
		$content = ItemSpecManager::singleton('cn', 'item.xml')->getItem($itemId)->description;
		$content = xml_replace($content, $contentParam);
		import('service.action.ChatClass');
		$data['contents']=$content;
		$data['mode'] = 7;
		$data['modeValue'] = '0';
		Chat::message()->setContents($data)->sendOneMessage();
		$data['mode'] = 6;
		$data['param'] = 'all';
		$data['contents'] = $content;
		Chat::message()->setContents($data)->sendOneMessage();
	}
	
	/**
	 * 向指定的服发送公告
	 * @param string $server
	 * @param string $itemId 公告的item
	 * @param array $contentsParams 公告中需要替换的参数
	 */
	public function doAnnouncement ($server, $itemId, $contentsParams, $language) {
		$sendParam = array('itemId' => $itemId, 'data' => $contentsParams, 'lang' => $language);
		$timeout = 30;
		$url = "http://".$server.":9001/warfare/rest/payment/payment/SendAnnounce";
		$headers = array("Content-Type: application/x-www-form-urlencoded");
		$curlopt_header = false;
		$ch = curl_init();
	    if (strpos($url, 'https') === 0) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        if (is_array($sendParam) && count($sendParam) > 0) {
           curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendParam));
        } else {
           curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($sendParam));
        }
        if ($curlopt_header) curl_setopt($ch, CURLOPT_HEADER, true);
        if (is_array($headers) && count($headers) > 0) {
           curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
	    $result = curl_exec($ch);
	    if ($result === false) {
	    	$realRet['error'] = curl_error($ch);
	    	curl_close($ch);
	    	return $realRet;
	    }
	    curl_close($ch);
	}
	
	public function doEnterCrossArena (XServiceRequest $request) {
		$params = $request->getParameters();
		$userUid = $params['playerUid'];
		$user = UserProfile::getWithUID($userUid);
		$language = $params['language'];
		import('service.action.CalculateUtil');
		$timeZoneConfig = CalculateUtil::getTimeZoneInfo();
		$timeZone = $timeZoneConfig[$language]['timezone'];
		ini_set('date.timezone', $timeZone);
		import('service.action.CrossArenaClass');
		$params['time'] = time();
		switch ($params['type']){
			case 1:
				$data = CrossArena::getLockCache('REWARD');
				break;
			//进入对应的竞技场
			case 2:
				$data = CrossArena::singletion($user) -> selectArenaList($params['time']);
				break;
			case 3:
				$data = CrossArena::singletion($user) -> enterArena($params['time']);
				break;
			//挑战玩家
			case 4:
				$playerUid = $params['uid'];
				$playerRank = $params['rank'];
				import('service.action.CacheLockClass');
				$cacheLock = CacheLock::start();
				$cacheLock->setKeyPreWords('USER_CROSSARENA_');
				if($cacheLock->getLock($playerUid) || $cacheLock->getLock($user->uid)){
					import('service.action.ConstCode');
					return array(ConstCode::ERROR_REQUEST_LATER);
				}
				$cacheLock->setLock(1, $playerUid);
				$cacheLock->setLock(1, $user->uid);
				$data = CrossArena::singletion($user) -> fightWithPlayer($playerUid, $playerRank, $params['time']);
				$cacheLock->releaseLock($playerUid);
				$cacheLock->releaseLock($user->uid);
				break;
			case 6:
				$data = CrossArena::singletion($user) -> getPlayerRankByServer ($params['server']);
				break;
			//查看君主信息
			case 9:
				$data = CrossArena::getPlayerInfo($params['uid']);
				break;
			case 10:
				$data = CrossArena::singletion($user) ->contrastWithPlayer($params['uid']);
		}
		//服务器压力监控
		import('util.cache.XCache');
		$cache = new XCache();
		$currentTime = time();
		$min = date("i",$currentTime);
		$min = $min - $min%5;
		$fiveMin = strtotime(date("Y-m-d H:",$currentTime).$min);
		$expire = 300 + $fiveMin - $currentTime;
		$key = 'CrossArena';
		$keyCheck = 'CrossArenaSave';
		$cacheData = $cache->get($key);
		if(!$cacheData)
			$cacheData = array();
		$cacheData[$params['type']]++;
		ksort($cacheData);
		$cache->set($key,$cacheData,$expire);
		if($expire<10&&!$cache->get($keyCheck)){
			$cache->set($keyCheck,1,30);
			file_put_contents(GAME_LOG_DIR.'/CrossArena.log',$currentTime.': '.json_encode($cacheData) . "\n",FILE_APPEND);
		}
		return array ($data);
	}
	
	/**
	 * 跨服竞技场 
	 * @param array $actionParams
	 */
	public function doCrossArenaAction($actionParams){
		$params = $actionParams;
		$timeout = 30;
		$url = "http://10.142.53.108:9001/warfare/rest/payment/payment/EnterCrossArena";
		$headers = array("Content-Type: application/x-www-form-urlencoded");
		$curlopt_header = false;
		$ch = curl_init();
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		if (is_array($params) && count($params) > 0) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
		}
		if ($curlopt_header) curl_setopt($ch, CURLOPT_HEADER, true);
		if (is_array($headers) && count($headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$res = curl_exec($ch);
		$data = json_decode($res, TRUE); 
		$data = $data[0];
		header("Content-Type:text/html; charset=utf-8");
		return $data;
	}
	/**
	 * 集市回调发货
	 * @param XServiceRequest $request
	 */
	public function doMarketSendGoods(XServiceRequest $request){
		ini_set('date.timezone', 'Asia/Shanghai');
		$params = $request->getParameters();
		$openid = $params['openid'];
		$payitem = $params['payitem'];
		$zoneid = $params['zoneid'];
		$user = UserFactory::singleton()->getUserByPlatformAddress($openid . '_ik2@qq_cn_' . $zoneid);
		if(empty($user)){
			return array("ret" => 4, "msg" => "userProfile is empty", "zoneid" => $zoneid - 1);
		}
		switch ($params['contractid'])
		{
			case '100650014T2201305210001'://加入国家争霸世界
				import('service.item.QFInviteItem');
				$QFInviteItem = QFInviteItem::getWithUID($user->uid);
				if ($QFInviteItem->totalInvite > 0 && strpos($user->buttonIndex, ',20')){
					//添加物品
					$goodsInfo = explode('*', $payitem);
					import('service.action.InventoryClass');
					$inventory = Inventory::singleton($user);
					$data = $inventory->addGoods($goodsInfo[0], 1, 1, 'market');
					return  array("ret" => 0, "msg" => "OK", "zoneid" => $zoneid - 1);
				}
				break;
		}
		return array("ret" => 4, "msg" => "task not Completion", "zoneid" => $zoneid - 1);
	}
	/**
	 * 集市任务状态check接口
	 * @param XServiceRequest $request
	 */
	public function doCheckCompletion(XServiceRequest $request){
		ini_set('date.timezone', 'Asia/Shanghai');
		$params = $request->getParameters();
		$openid = $params['openid'];
// 		$zoneid = $params['zoneid'];//check的时候无此参数
		import('service.action.DataClass');
		StatData::$pf = 'qzone';
		import("service.action.QFriendInviteClass");
		$mysql = QFriendInvite::singleton(null)->getDB();
		$sql =  "select * from qprovideaward where opendid='$openid' and questid = '{$params['contractid']}'";
		$user = $mysql->execResult($sql);
		
// 		$return_data = array("ret" => 0, "msg" => "OK", "zoneid" => $zoneid - 1);
		if(!is_array($user)){
			return array("ret" => 4, "msg" => "userProfile is empty", "zoneid" => $user[0]['zoneid'] - 1);
		}
		switch ($params['contractid'])
		{
			case '100650014T2201305210001'://加入国家争霸世界
				$sql =  "select * from qfilink where invite='$openid'";
				$inviteData = $mysql->execResult($sql);
				if ($inviteData){
					return  array("ret" => 0, "msg" => "OK", "zoneid" => $user[0]['zoneid'] - 1);
				}
// 				if ($user->country != '0' && strpos($user->buttonIndex, ',20')){
// 				}
				break;
		}
		return array("ret" => 4, "msg" => "task not Completion", "zoneid" => $user[0]['zoneid']  - 1);
	}
	/**
	 * 充值黄钻回调，分配发货服务器
	 * Enter description here ...
	 * @param XServiceRequest $request
	 */
	public function doQVipGiftCallback(XServiceRequest $request){
		$params = $request->getParameters();
		$zoneid = $params['zoneid'] == 999 ? 1: $params['zoneid'] + 1;
		$params['zoneid'] = $zoneid;
		//根据zoneid分配发货服
		$serverList = $this->getServerList();
		$timeout = 30;
		$server = $serverList[$zoneid];
		if(!$server)
		{
			return array("ret" => 4, "msg" => "no server set");
		}
		$url = "http://$server:9001/warfare/rest/payment/payment/QVipGiftSendGoods";
		$headers = array("Content-Type: application/x-www-form-urlencoded");
		$curlopt_header = false;
		$ch = curl_init();
	    if (strpos($url, 'https') === 0) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    }
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        if (is_array($params) && count($params) > 0) {
           curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
           curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
        }
        if ($curlopt_header) curl_setopt($ch, CURLOPT_HEADER, true);
        if (is_array($headers) && count($headers) > 0) {
           curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
	    $result = curl_exec($ch);
	    header("Content-Type:text/html; charset=utf-8");
		echo $result;
	}
	/**
	 * 充值黄钻回调发货
	 * Enter description here ...
	 * @param XServiceRequest $request
	 */
	public function doQVipGiftSendGoods(XServiceRequest $request){
		ini_set('date.timezone', 'Asia/Shanghai');
		$params = $request->getParameters();
		$openid = $params['openid'];
		$appid = $params['appid'];
		$ts = $params['ts'];
		$payitem = $params['payitem'];
		$token = $params['token'];
		$billno = $params['billno'];
		$discountid = $params['discountid'];
		$zoneid = $params['zoneid'];
		$sig = $params['sig'];
		$user = UserFactory::singleton()->getUserByPlatformAddress($openid . '_ik2@qq_cn_' . $zoneid);
		if(empty($user)){
			$return_data = array("ret" => 4, "msg" => "params err:{$sig}");
		}else{
			$return_data = array("ret" => 0, "msg" => "OK");
			import('service.item.YellowDiamondItem');
			$yellowDiamondItem = YellowDiamondItem::getWithUID($user->uid);
			$yellowDiamondItem->yellowVipPay += 1;
			$yellowDiamondItem->save();
			import('service.action.CalculateUtil');
			CalculateUtil::writeLog($user->uid, 'QVIP', array($ts,$billno,$discountid,$zoneid), array(), 'log');
		}
		return $return_data;
	}
	
	/**
	 * 联盟黑暗入侵回调借口
	 */
	public function doQAllianceDarkIncursionCallback(XServiceRequest $request) {
		$param = $request->getParameters();
		$activityId = $request->getParameter('activityId');
		$serverId = $request->getParameter('serverId');
		$language = $request->getParameter('language');
		import('service.action.CalculateUtil');
		$timeZoneConfig = CalculateUtil::getTimeZoneInfo();
		$timeZone = $timeZoneConfig[$language]['timezone'];
		ini_set('date.timezone', $timeZone);
		import('service.action.AllianceActivityClass');
		//file_put_contents(GAME_LOG_DIR.'/AllianceDarkIncursion.log','ScheduleTimer----' . json_encode($param) . '   language: ' . $timeZone .  '----Time:' . date('H:i:s', time()) . "\n",FILE_APPEND);
		AllianceActivity::getInstance(null, $activityId)->fight($serverId);
	}
	
	/**
	 * 清除世界地图上 冷却时间已过的联盟BOSS
	 * @param XServiceRequest $request
	 */
	public function doClearAllianceBossPlace (XServiceRequest $request) {
		$language = $request->getParameter('language');
		import('service.action.CalculateUtil');
		$timeZoneConfig = CalculateUtil::getTimeZoneInfo();
		$timeZone = $timeZoneConfig[$language]['timezone'];
		ini_set('date.timezone', $timeZone);
		import('service.item.WorldItem');
		WorldItem::removeAllianceBoss();
	}
	
	/**
	 * 国战定时刷新 
	 */
	public function doFlushCountryBattleCallback(XServiceRequest $request) {
		$param = $request->getParameters();
		$activityId = $request->getParameter('activityId');
		$language = $request->getParameter('language');
		import('service.action.CalculateUtil');
		$timeZoneConfig = CalculateUtil::getTimeZoneInfo();
		$timeZone = $timeZoneConfig[$language]['timezone'];
		ini_set('date.timezone', $timeZone);
		import('service.action.DataClass');
		StatData::$lang = $language;
		import('service.action.CountryBattleClass');
		CountryBattle::getInstance($this->user, $activityId)->scheduleFlush();
	}
	
	/**
     +----------------------------------------------------------
     * 腾讯支付回调发货地址
     +----------------------------------------------------------
     * @method doQpaymentCallback
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function doQpaymentCallback(XServiceRequest $request){
		$platform_id = 'ik2@qq_cn';
		$result = $this->QpaymentHandler($request, $platform_id);
		header("Content-Type:text/html; charset=utf-8");
        echo(json_encode($result));
		
	}
	/**
     +----------------------------------------------------------
     * 腾讯支付回调处理
     +----------------------------------------------------------
     * @method getPrisonInfo
     * @access public
     * @param 
     +----------------------------------------------------------
     * @return 返回相应信息
     +----------------------------------------------------------
     */
	public function QpaymentHandler(XServiceRequest $request, $platform_id){
		ini_set('date.timezone', 'Asia/Shanghai');
		$params = $request->getParameters();
		$openid = $params['openid'];
		$appid = $params['appid'];
		$ts = $params['ts'];
		$payitem = $params['payitem'];
		$token = $params['token'];
		$billno = $params['billno'];
		$zoneid = $params['zoneid'] + 1;
		$amt = $params['amt'];
		$payamt_coins = $params['payamt_coins'];
		$pubacct_payamt_coins = $params['pubacct_payamt_coins'];
		$sig = $params['sig'];
		//进行token验证
//		$openid = 'qq99';
//		$appid = '1234';
//		$ts = $params['ts'];
//		$payitem = 'g00123*12*1';
//		$token = $params['token'];
//		$billno = '12345dddfdfsd';
//		$zoneid = '1';
//		$amt = 12;
//		$payamt_coins = $params['payamt_coins'];
//		$pubacct_payamt_coins = $params['pubacct_payamt_coins'];
//		$sig = $params['sig'];
//		
		//check end
		$user = UserFactory::singleton()->getUserByPlatformAddress($openid . '_' . $platform_id . '_' . $zoneid);
		if(empty($user)){
			$return_data = array("ret" => 4, "msg" => "params err:{$sig}");
			return $return_data;
		}
		//腾讯平台默认语言和默认平台
		import('service.action.DataClass');
		StatData::$lang = 'cn';
		StatData::$pf = 'qzone';
		//进行用户信息处理 发货
		import('service.action.QpayClass');
		$qpay = Qpay::singletion($user);
		$send_good_result = $qpay->sendGoods($payitem);
		if($send_good_result != 'ok'){
			$return_data = array("ret" => 4, "msg" => $send_good_result);
			return $return_data;
		}
		//进行记录信息
		import('service.item.QpayItem');
		$payItem = QpayItem::singleton();
		$payItem->uid = $openid . $billno;
		$payItem->openid = $openid;
		$payItem->ownerid = $user->uid;
		$payItem->level = $user->level;
		$payItem->billno = $billno;
		$payItem->sendtime = time();
		$arr_good = explode('*',$payitem);
		$payItem->goodsid = $arr_good[0];
		$payItem->price = $arr_good[1];
		$payItem->num = $arr_good[2];
		$payItem->amt = $amt;
		$payItem->payamt_coins = $payamt_coins;
		$payItem->pubacct_payamt_coins = $pubacct_payamt_coins;
		$payItem->zoneid = $zoneid;
		$payItem->save();
		
		//首次充值奖励
		if($user->first_pay_status == 0 && strpos($arr_good[0], 'ValueGold') !== false) {
			$user->first_pay_status = 1;
			$user->save();
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'first_pay';
			$contents['contents'] = '1';
			Chat::message($user)->setContents($contents)->sendOneMessage();
// 			$this->insertPlatformFirstPay('cn', $payItem);
		}
		//消费数量通知
		import('service.action.ChatClass');
		$contents['mode'] = 11;
		$contents['modeValue'] = 'qpay_count';
		$contents['contents'] = $pubacct_payamt_coins;
		Chat::message($user)->setContents($contents)->sendOneMessage();
		$return_data = array("ret" => 0, "msg" => "OK");
		return $return_data;
	}
	
	
	/**
	 * 337支付回调处理函数
	 * @param XServiceRequest $request
	 * @param string $secret_key
	 * @param string $platform_id
	 * @param string $lang
	 */
	public function paymentHandler(XServiceRequest $request,$secret_key,$platform_id,$lang){
		ini_set('date.timezone', 'CST6CDT');
		$params = $request->getParameters();
		$user_id = $params ["user_id"];
		$amount = $params ["amount"];
		$transaction_id = $params ["trans_id"];
		$sig = $params ["sig"];
		$gross = $params ["gross"];
		$currency = $params ["currency"];
		$channel = $params ["channel"];
		    
		switch ($lang)
		{
			case 'en':
				$scale = array(5500=>100,1050=>20,300=>6,50=>1);
				break;
			default:
				$scale = array(5500=>100,1050=>20,300=>6,50=>1);
				break;
		}

		$res = $this->check_payelex_transaction($transaction_id, $user_id, $amount, $gross, $currency, $channel);
		if ($res) {
			$platform_address = $user_id.'_'.$platform_id;
			$user = UserFactory::singleton()->getUserByPlatformAddress($platform_address);
			if(empty($user)){
				return "3,94a0acb127ef8ee8c925e3944941ce5e";
			}
			$amount = intval($amount);
			
			//此交易之前已经完成
			import('service.item.QpayItem');
			$payItem = QpayItem::getWithUID($transaction_id);
			if (isset($payItem))
			{
				return "3,$user_id";
			}
			//作弊交易
			if($amount < 0)
			{
				$amount = abs($amount);
				//扣钱操作
				if($user->user_gold+$user->system_gold < $amount){
					//钱不够扣，封禁用户
					$amount = $user->user_gold+$user->system_gold;
				}
				$user->user_gold = 0;
				$user->system_gold = 0;
				$user->save();
				return "3,$user_id";
			}
			//进行记录信息
			$payItem = QpayItem::singleton();
			$payItem->uid = $transaction_id;
			$payItem->openid = $user_id;
			$payItem->ownerid = $user->uid;
			$payItem->level = $user->level;
 			$payItem->billno = $sig;
			$payItem->sendtime = time();
 			$payItem->goodsid = $scale[$amount];
			$payItem->price = $amount;
			$payItem->num = 1;
			$payItem->amt = $scale[$amount]*10;
			$payItem->save();
			
			//发货处理
			$payType = 4;
			foreach ($scale as $k => $v)
			{
				if ($amount == $k) break;
				$payType--;
			}
			import('service.action.CalculateUtil');
			$data = CalculateUtil::valueGold($user, $payType);
			$checkKey = 'ValueGold_'.$user->uid;
			//发货结果保存
			if (is_array($data))
			{
				$this->setMemCache($checkKey, $data);
			}
			//首次充值奖励
			if($user->first_pay_status == 0) {
				$user->first_pay_status = 1;
				$user->save();
				import('service.action.ChatClass');
				$contents['mode'] = 11;
				$contents['modeValue'] = 'first_pay';
				$contents['contents'] = '1';
				Chat::message($user)->setContents($contents)->sendOneMessage();
// 				$this->insertPlatformFirstPay('en', $payItem);
			}
			//消费数量通知
			import('service.action.ChatClass');
			$contents['mode'] = 11;
			$contents['modeValue'] = 'qpay_count';
			$contents['contents'] = $amount;
			Chat::message($user)->setContents($contents)->sendOneMessage();
			return "3,$user_id";

		}
		return "3,94a0acb127ef8ee8c925e3944941ce5e";
	}
	
	/**
	 * 暂时不用，需要查询的时候将各个服的数据导入再查询
	 * @param unknown $lang
	 * @param unknown $payItem
	 */
	private function insertPlatformFirstPay($lang,$payItem){
		//查看这个用户是否在这个语言平台支付过
		if(xingcloud_get('mysql_host') == '10.1.5.59'){
			$dbPf = 'local';
		}else{
			if($lang == 'cn')
				$dbPf = 'qzone';
			elseif($lang == 'en')
			$dbPf = 'facebook';
		}
		import('util.mysql.XMysql');
		import('service.action.CalculateUtil');
		$dataMysql = XMysql::singleton(CalculateUtil::getDBParam($dbPf));
		//如果没有记录则添加
		if(!$dataMysql->get('plaftormfirstpay', array('openid'=>$payItem->openid))){
			$dbLink = array('uid','openid','ownerid','level','billno','sendtime','goodsid','price','num','amt','payamt_coins','pubacct_payamt_coins','zoneid');
			$insertData = array();
			foreach ($dbLink as $dbLinkTab){
				$insertData[$dbLinkTab] = $payItem->$dbLinkTab;
			}
			$dataMysql->add('plaftormfirstpay', $insertData);
		}
	}
	
	/**
	 * 设置数值
	 * @param string $itemId
	 * @param array $value
	 * @param number $expire
	 */
	private function setMemCache($itemId, $value, $expire=60) {
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$value = json_encode($value);
		$cache->set($itemId, $value, $expire);
	}
	
	/**
	 * 337平台认证
	 * @param unknown_type $trans_id
	 * @param unknown_type $user_id
	 * @param unknown_type $amount
	 * @param unknown_type $gross
	 * @param unknown_type $currency
	 * @param unknown_type $channel
	 * @return boolean
	 */
	function check_payelex_transaction($trans_id, $user_id, $amount, $gross, $currency, $channel) {
	        $ch = curl_init();
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	    //verisign_ca.crt is the public certificate from VeriSign(It is the biggest Certificate Authority which issue ELEX client certificate)
	    //verisign_ca.crt must be located at the same directory as this PHP code are.
	    curl_setopt($ch, CURLOPT_CAINFO, XINGCLOUD_SERVICE_DIR.'/payment/service/verisign_ca.crt'); 
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	    curl_setopt($ch, CURLOPT_URL, 'https://pay.337.com/payelex/api/callback/verify.php');
	    curl_setopt($ch, CURLOPT_POST, true);  
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        $params = array(
	                'trans_id'=>$trans_id,
	                'user_id'=>$user_id,
	                'amount'=>$amount,
	                'gross'=>$gross,
	                'currency'=>$currency,
	                'channel'=>$channel
	        );  
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));  
	    $result = curl_exec($ch);
	    curl_close($ch);
	    $result = trim($result);
	    if ($result === 'OK') return true;
	    return false;
	}
}
?>