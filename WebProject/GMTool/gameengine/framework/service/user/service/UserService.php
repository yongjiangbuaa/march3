<?php
/**
 * UserService
 * 
 * user service
 * 
 * 用户服务
 * 
 * @Pointcut('protocol|auth')
 * @author Tianwei
 * @package user
 */
class UserService extends XAbstractService{
	/**
	 * <b>platform user login service, return the user information</b>
	 * 
	 * <b>平台用户登录服务，返回用户信息。</b>
	 * 
	 * <b>参数要提供SNS平台的APP ID和用户在SNS平台的用户ID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doPlatformLogin(XServiceRequest $request){
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->platformLogin($request);
		return $this->_success($user);
	}
	
	/**
	 * <b>platform user register service, create user profile in Database when user first login</b>
	 * 
	 * <b>平台用户注册服务，用户首次登陆时在数据库中创建用户记录。</b>
	 * 
	 * <b>参数要提供SNS平台的ID和用户在SNS平台的用户ID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam array userinfo 用户属性初始化值
	 * @return XServiceResult
	 */
	public function doPlatformRegister(XServiceRequest $request){
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->platformRegister($request);
		return $this->_success($user);
	}
	
	/**
	 * <b>platform user register service, create user profile in Database when user first login</b>
	 *
	 * <b>测试用，注册用户并将INFO里面的名字设置成用户名</b>
	 *
	 * <b>参数要提供SNS平台的ID和用户在SNS平台的用户ID。</b>
	 *
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam array userinfo 用户属性初始化值
	 * @return XServiceResult
	 */
	public function doPlatformRegisterAndSetInfo(XServiceRequest $request){
		$info = $request->getParameter('info');
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->platformRegister($request);
		$request = new XActionRequest();
		$changes = array();
		$name = "SetPlayerInfoAction";
		$data['playername'] = $info['platformUserId'];
		$data['changes'] = array();
		$request->setParameters($data);
		$request->setActionName($name);
		$return = XActionContext::singleton()->executeAction($request,$user);
		return XServiceResult::success($return);
	}
	
	/**
	 * <b>user login service, return the user information</b>
	 * 
	 * <b>用户登录服务，返回用户信息。</b>
	 * 
	 * <b>参数要提供用户名和用户密码。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string username 用户名
	 * @ServiceParam string password 用户密码
	 * @return XServiceResult
	 */
	public function doLogin(XServiceRequest $request){
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->login($request);
		return $this->_success($user);
	}
	
	/**
	 * <b>user register service, create user profile in Database when user first login</b>
	 * 
	 * <b>用户注册服务，用户首次登陆时在数据库中创建用户记录。</b>
	 * 
	 * <b>参数要提供用户名和用户密码。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam array account 用户账户属性初始化值,必须指定username&password
	 * @return XServiceResult
	 */
	public function doRegister(XServiceRequest $request){
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->register($request);
		return $this->_success();
	}
	
	/**
	 * <b>bind platform service, bind a group of platformAppId & platformUserId to a user account</b>
	 * 
	 * <b>绑定平台服务，将一组SNS平台APP ID和用户在SNS平台的ID绑定到一个用户账号上。</b>
	 * 
	 * <b>参数要提供SNS平台的APP ID和用户在SNS平台的用户ID。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='POST')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doBindPlatform(XServiceRequest $request){
		import('service.user.UserFactory');
		$result = UserFactory::singleton()->bindPlatform($request);
		return $this->_success($result);
	}
	
	/**
	 * <b>load user information by gameUserId or platformAppId&platformUserId</b>
	 * 
	 * <b>获取用户信息的服务，返回用户信息。</b>
	 * 
	 * <b>参数要提供用户在游戏内部的UID(gameUserId)或用户平台信息(platformAppId&platformUserId)。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGet(XServiceRequest $request){
		$data = $request->getData();
		$gameUserIds = array();
		$platforAddresses = array();
		if(!empty($data)){
			foreach ($data as $value){
				if(is_array($value)){
					if(array_key_exists('gameUserId', $value)){
						$gameUserIds[] = $value['gameUserId'];
					}elseif(array_key_exists('platformAppId', $value) && array_key_exists('platformUserId', $value)){
						$platforAddresses[] = $value['platformUserId'].'_'.$value['platformAppId'];
					}
				}
			}
		}
		$profileList1 = array();
		$profileList2 = array();
		if(!empty($platforAddresses)){
			import('service.user.UserFactory');
			$profileList1 = UserFactory::singleton()->getUsersByPlatformAddresses($platforAddresses);
		}
		if(!empty($gameUserIds)){
			import('service.user.UserFactory');
			$profileList2 = UserFactory::singleton()->getUsers($gameUserIds);
		}
		$profileList = array_merge($profileList1, $profileList2);
		return $this->_success(array_values($profileList));
	}
	/**
	 * <b>get user owned items by user_uid and property name</b>
	 * 
	 * <b>获取用户物品信息的服务，返回用户物品信息。</b>
	 * 
	 * <b>参数要提供用户在游戏内部的UID和以及物品属性。物品属性为不能空</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string user_uid 用户UID
	 * @ServiceParam string property 用户物品属性
	 * @return XServiceResult
	 */
	public function doGetItems(XServiceRequest $request){
		$uid = $request->getUserUID();

		$property = $request->getParameter('property');
		if(!$property){
			return XServiceResult::clientError("items {$uid} not exists");
		}
		if(strpos($property, ',') === false){			
			$class = substr($property, 0, -1);
			$class{0} = strtoupper($class{0});
			import('service.item.'.$class);	
			$obj = new $class();
			if($class == 'CityItem'){
				$items = $obj->getItems($uid, true);
			}else{
				$items = $obj->getItems($uid);
			}
			return $this->_success($items);
		}
		$property = explode(',', $property);					

		$results = array();
		foreach($property as $key){
			$class = substr($property, 0, -1);
			$class{0} = strtoupper($class{0});
			import('service.item.'.$class);	
			$obj = new $class();
			if($class == 'CityItem'){
				$items = $obj->getItems($uid, true);
			}else{
				$items = $obj->getItems($uid);
			}
			if ($items != null)
			{
				$results[$key] = $items;
			}
			$results[$key] = array();
		}
		return $this->_success($results);
	}
	
	/**
	 * <b>get user owned new items by user_uid , itemIds and property name </b>
	 * 
	 * <b>获取用户新增物品信息的服务，返回用户物品信息。</b>
	 * 
	 * <b>参数要提供用户在游戏内部的UID和新增物品uid列表以及物品属性。物品属性为不能空</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string user_uid 用户UID
	 * @ServiceParam string property 用户物品属性
	 * @return XServiceResult
	 */
	public function doGetNewItems(XServiceRequest $request){
		$userUid = $request->getUserUID();
		$property = $request->getParameter('property');
		$itemUids = explode(',', $request->getParameter('itemUids'));
		if(!$property || !$itemUids){
			return XServiceResult::clientError("items {$userUid} not exists");
		}
		
		$class = substr($property, 0, -1);
		$class{0} = strtoupper($class{0});
		import('service.item.'.$class);	
		$obj = new $class();
		foreach ($itemUids as $key => $uid){
			if(!$uid){
				unset($itemUids[$key]);
			}
		}
		$items = array();
		$items['property'] = $property;
		$items['list'] = $obj->getNewItems($userUid, $itemUids);
		return $this->_success($items);
		
	}
	
	/**
	 * 修改用户信息的服务，返回修改后的用户信息。
	 * 参数要提供用户在游戏内部的UID。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam array 包括用户UID等相关信息
	 * @return XServiceResult
	 */
	public function doModify(XServiceRequest $request){
		$info = $request->getParameter('info');
		if($info['gameUserId'])
			$user = UserProfile::getWithUID($info['gameUserId']);
		elseif($info['gameUserName'])
		{
			$user = UserProfile::getWithName($info['gameUserName']);
		}
		else 
		{
			$platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
			$user = UserProfile::getWithUID($platformProfile->userUID);
		}
		if(!$user){
			return XServiceResult::clientError("user not exists");
		}
		//修改相关数值
		$params = $request->getParameter('data');
		$modify = array();
		foreach ($params as $key => $param){
			if(substr($key,0,5) != 'value' || $param == null)
				continue;
			$realKey = substr($key, 6 , strlen($key));
			if(preg_match("/^\d*$|^\d+(\.\d+)?$/",$param) || in_array($realKey, array('buttonIndex','tabIndex','name','platformAddress'))){
				//如果值有变化，写入log
				if($user->{$realKey} != $param){
					if($realKey == 'platformAddress')
					{
						import("service.user.PlatformProfile");
						$platformProfile = PlatformProfile::getWithUID($user->platformAddress);
						if($platformProfile){
							$platformProfile->platformAddress = $param;
							$platformProfile->save();
							$modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
							$user->set($realKey, $param);
						}
						else{
							$newPlatformProfile = new PlatformProfile();
							$newPlatformProfile->userUID = $platformProfile->userUID;
							$newPlatformProfile->platformAddress = $param;
							$newPlatformProfile->save();
							$modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
							$user->set($realKey, $param);
						}
					}
					else
					{
						$modify[$realKey] = array('old'=>$user->{$realKey},'new'=>$param);
						$user->set($realKey, $param);
					}
					if($realKey != 'speakingForbid' && $realKey != 'seize'){
						$user->onLoadKey = md5($user->uid . microtime(true));
					}
				}
			}
		}
		$user->save();
		//获得更新后的数据
		if($info['gameUserId'])
			$user = UserProfile::getWithUID($info['gameUserId']);
		elseif($info['gameUserName'])
		{
			$user = UserProfile::getWithName($info['gameUserName']);
		}
		else 
		{
			$platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
			$user = UserProfile::getWithUID($platformProfile->userUID);
		}
		return XServiceResult::success(array('user'=>$user,'modify'=>$modify));
	}
	/**
	 * <b>load user information by gameUserId or platformAppId&platformUserId</b>
	 *
	 * <b>GM获取用户信息的服务，返回用户信息。</b>
	 *
	 * <b>参数要提供用户在游戏内部的UID(gameUserId)或用户平台信息(platformAppId&platformUserId)。</b>
	 *
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGMGet(XServiceRequest $request){
		$info = $request->getParameter('data');
		if($info['gameUserId'])
			$user = UserProfile::getWithUID($info['gameUserId']);
		elseif($info['gameUserName'])
		{
			$user = UserProfile::getWithName($info['gameUserName']);
		}
		else
		{
			$platformProfile = PlatformProfile::getWithUID($info['platformUserId'].'_'.$info['platformAppId']);
			$user = UserProfile::getWithUID($platformProfile->userUID);
		}
		$platformAddress = $user->platformAddress;
		$address = explode('@', $platformAddress);//11_424_ik2@elex337_tw_1=>11_424_ik2,elex337_tw_1
		$platformUserId = '';
		$app_id = explode('_', $address[0]);//11,424,ik2
		$sig_api_key = $sig_app_id = end($app_id).'@'.$address[1];//ik2@elex337_tw_1
		for ($i=0;$i<count($app_id)-1;$i++)
		{
			if($platformUserId != '')
				$platformUserId .= '_';
			$platformUserId .= $app_id[$i];//11_424
		}
		$sig_auth_key = md5($platformUserId . $sig_app_id . $sig_api_key . 'c5944690dfad012f5a17782bcb1b6cfd');
		return XServiceResult::success(array('user'=>$user,'sig_auth_key'=>$sig_auth_key));
	}
	/**
	 * <b>load user information by gameUserId or platformAppId&platformUserId</b>
	 *
	 * <b>GM获取用户信息的服务，返回用户信息。</b>
	 *
	 * <b>参数要提供用户在游戏内部的UID(gameUserId)或用户平台信息(platformAppId&platformUserId)。</b>
	 *
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */
	public function doGMTestLogin(XServiceRequest $request){
		$info = $request->getParameter('data');
		$platformUserId = $info['user'];
		$platformAddress = $info['plat'];
		$sig_api_key = $sig_app_id = $platformAddress;
		$sig_auth_key = md5($platformUserId . $sig_app_id . $sig_api_key . 'c5944690dfad012f5a17782bcb1b6cfd');
		return XServiceResult::success(array('sig_auth_key'=>$sig_auth_key,'platformAddress'=>$platformAddress));
	}
}
?>
