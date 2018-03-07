<?php
define('E_ACCOUNT_ALREADY_EXIST', 501);
define('E_ACCOUNT_NOT_EXIST', 502);
define('E_INCCORECT_PASSWORD', 503);
define('E_PLATFORM_USER_ALREADY_EXIST', 504);
import('module.context.user.UserRepository');
/**
 * UserFactory
 * 
 * user factory class
 * 
 * 用户工厂类
 * 
 * @author Tianwei
 * @package user
 */
class UserFactory implements UserRepository{
	private static $instance = null;
	private $userClass = null;
	private $platformUserClass = null;
	private $userAccountClass = null;

	/**
	 * <b>construct method</b>
 	 * 
 	 * <b>构造方法</b>
	 */
	private function __construct() {
	}
	
	/** 
	 * <b>singleton method</b>
	 *
	 * <b>singleton方法</b>
	 * 
	 * @return UserFactory
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * <b>get user information by the given uid</b>
	 * 
	 * <b>根据给定的uid对用户信息进行查询，并返回结果</b>
	 * 
	 * @return User
	 * @throws XException
	 */
	public function get($uid){
		if(empty($uid)){
			throw new XException("user uid is null");
		}
		$userClass = $this->getUserClass();
		import('service.user.'.$userClass);
		import('persistence.orm.PersistenceSession');
		$user = $userClass::getWithUid($uid);
		if(!empty($user)){
			$user->onLoad();
			import('service.user.event.UserLoadEvent');
			XEventContext::singleton()->publish(new UserLoadEvent($user));
		}
		return $user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see UserRepository::put()
	 */
	public function put($user){
		
	}
	
	/**
	 * <b>get user information by the given uids</b>
	 * 
	 * <b>给定一组uid对用户信息进行查询，返回结果</b>
	 * 
	 * @param array $uids
	 * @return Array
	 */
	public function getUsers($uids){
		if(empty($uids)){
			return array();
		}
		$userClass = $this->getUserClass();
		import('service.user.'.$userClass);
		import('persistence.orm.PersistenceSession');
		foreach ($uids as $uid)
			$users[] = $userClass::getWithUID($uid);
		if(!empty($users)){
			foreach ($users as $user){
				if(!empty($user)){
					$user->onLoad();
					import('service.user.event.UserLoadEvent');
					XEventContext::singleton()->publish(new UserLoadEvent($user));
				}
			}
		}
		return $users;
	}
	
	/**
	 * <b>query user information by the given platformAddresses</b>
	 * 
	 * <b>根据给定的platformAddress对用户信息进行查询，并返回结果</b>
	 * 
	 * @param string
	 * @return Array
	 */
	public function getUserByPlatformAddress($platformAddress){
		if(empty($platformAddress)){
			return null;
		}
		$platformUserClass = $this->getPlatformUserClass();
		$userClass = $this->getUserClass();
		import('service.user.'.$platformUserClass);
		import('persistence.orm.PersistenceSession');
		$platformUser = $platformUserClass::getWithUID($platformAddress);
		$gameUserId = null;
		if(!empty($platformUser)){
			$gameUserId = $platformUser->getUserUID();
		}else{
			return null;
		}
		import('service.user.'.$userClass);
		$user = $userClass::getWithUID($gameUserId);
		if(!empty($user)){
			if(!empty($user)){
// 				$user->onLoad();
				import('service.user.event.UserLoadEvent');
				XEventContext::singleton()->publish(new UserLoadEvent($user));
			}
		}
		return $user;
	}
	
	/**
	 * <b>query user information by the given platformAddresses</b>
	 * 
	 * <b>根据给定的platformAddress对用户信息进行查询，并返回结果</b>
	 * 
	 * @param Array
	 * @return Array
	 */
	public function getUsersByPlatformAddresses($platformAddresses){
		if(empty($platformAddresses)){
			return null;
		}
		$platformUserClass = $this->getPlatformUserClass();
		$userClass = $this->getUserClass();
		import('service.user.'.$platformUserClass);
		import('persistence.orm.PersistenceSession');
		$platformUsers = $platformUserClass::getWithUID($platformAddresses);
		$gameUserIds = array();
		if(!empty($platformUsers)){
			foreach ($platformUsers as $platformUser){
				if(!empty($platformUser)){
					$gameUserIds[] = $platformUser->getUserUID();
				}else{
					$gameUserIds[] = null;
				}
			}
		}
		import('service.user.'.$userClass);
		foreach ($gameUserIds as $uid)
			$users[] = $userClass::getWithUID($uid);
		if(!empty($users)){
			foreach ($users as $user){
				if(!empty($user)){
					$user->onLoad();
					import('service.user.event.UserLoadEvent');
					XEventContext::singleton()->publish(new UserLoadEvent($user));
				}
			}
		}
		return $users;
	}
	
	/**
	 * <b>login a user by the given platform_uid and platform_user_uid, if the specified user doesn't exist, null returned</b>
	 * 
	 * <b>根据给定的平台ID和用户在平台上的ID对用户进行登录操作，如果用户不存在，返回null</b>
	 * 
	 * @return User
	 */
	public function platformLogin(XServiceRequest $request){
		$platformAddress = $request->getPlatformAddress();
		if($request->getParameter('count') > 1){
			import('util.mysql.XMysql');
			$mysql = XMysql::singleton();
			$mysql->add('platformlogin', array('platformAddress'=>$platformAddress,'timeStamp'=>time(),'count'=>$request->getParameter('count')));
		}
		$platformUser = $this->getPlatformUser($platformAddress);
		if(empty($platformUser)){
			return null;
		}
		import('service.user.'.$this->getUserClass());
		$userClass = $this->getUserClass();
		$user = $userClass::getWithUID($platformUser->getUserUID());
		//由于没有请求用户初始化导致用户无法进入游戏的处理
		if($user && $user->name === null && $user->level == 1){
			$user->remove();
			$user = null;
			$platformUser->remove();
		}
		if(!empty($user)){
			$user->onLogin();
			import('service.user.event.UserLoginEvent');
			XEventContext::singleton()->publish(new UserLoginEvent($user));
		}
		return $user;
	}
	
	/** 
	 * <b>register a user by the given platform_uid and platform_user_uid</b>
	 * 
	 * <b>根据给定的平台ID和用户在平台上的ID对用户进行注册，保存用户信息到数据库</b>
	 * 
	 * @return User
	 */
	public function platformRegister(XServiceRequest $request){
		$platformUserClass = $this->getPlatformUserClass();
		$platformAddress = $request->getPlatformAddress();
		$platformUser = $this->getPlatformUser($platformAddress);
		if(!empty($platformUser)){
			import('module.context.XException');
			throw new XException('platform user already exists', E_PLATFORM_USER_ALREADY_EXIST);
		}
		try{
			import('util.mysql.XMysql');
			$regTime = time();
			XMysql::singleton()->add('register', array('platformAddress'=>$platformAddress,'timeStamp'=>$regTime));
			$platformUser = new $platformUserClass();
			$platformUser->set('platformAddress', $platformAddress);
			$userClass = $this->getUserClass();
			import('service.user.'.$userClass);
			$user = new $userClass();
			$userInfo = $request->getParameter('userInfo');
			if(!empty($userInfo) && is_array($userInfo)){
				$user->setAttrs($userInfo);
			}
			//$user->uid = $request->getPlatformUserUID();
			$user->set('platformAddress', $request->getPlatformAddress());
			$user->save();
			$platformUser->set('userUID', $user->getUID());
			$platformUser->save();
			$user->onRegister();
			XMysql::singleton()->put('register', array('platformAddress'=>$platformAddress,'timeStamp'=>$regTime),array('user'=>$user->uid));
			import('service.user.event.UserRegisterEvent');
			XEventContext::singleton()->publish(new UserRegisterEvent($user));
		}
		catch(Exception $e){
			throw $e;
		}
		return $user;
	}
	
	/**
	 * <b>login a user by the given username and password, if the specified user doesn't exist, null returned</b>
	 * 
	 * <b>根据给定的用户名和用户密码对用户进行登录操作，如果用户不存在，返回null</b>
	 * 
	 * @return User
	 */
	public function login(XServiceRequest $request){
		$username = $request->getParameter('username');
		$password = $request->getParameter('password');
		if(is_null($username) || is_null($password)){
			import('module.context.XException');
			throw new XException('please specify username and password');
		}
		import('persistence.dao.XActiveRecord');
		import('persistence.orm.PersistenceSession');
		$userAccountClass = $this->getUserAccountClass();
		import($userAccountClass);
		$userAccountClassName = $this->getClassName($userAccountClass);
		$userAccount = PersistenceSession::singleton()->get($userAccountClassName, $username);
		if(empty($userAccount)){
			import('module.context.XException');
			throw new XException('user account not exists', E_ACCOUNT_NOT_EXIST);
		}
		$salt = $userAccount->getSalt();
		$dbPassword = $userAccount->getPassword();
		if($this->genDBPassword($password, $salt) != $dbPassword){
			import('module.context.XException');
			throw new XException('password not correct', E_INCCORECT_PASSWORD);
		}
		$gameUserId = $userAccount->getUserUid();
		$userClass = $this->getUserClass();
		import('service.user.'.$userClass);
		$user = PersistenceSession::singleton()->get($userClass, $gameUserId);
		if(empty($user)){
			import('module.context.XException');
			throw new XException('user not exists');
		}
		$user->onLogin();
		import('service.user.event.UserLoginEvent');
		XEventContext::singleton()->publish(new UserLoginEvent($user));
		return $user;
	}
	
	/** 
	 * <b>register a user by the given username and password</b>
	 * 
	 * <b>根据给定的用户名和用户密码对用户进行注册，保存用户信息到数据库</b>
	 * 
	 * @return User
	 */
	public function register(XServiceRequest $request){
		$account = $request->getParameter('account');
		if(empty($account) || !is_array($account)){
			import('module.context.XException');
			throw new XException('please specify account');
		}
		if(!isset($account['username']) || !isset($account['password'])){
			import('module.context.XException');
			throw new XException('please specify username and password');
		}
		$username = $account['username'];
		$password = $account['password'];
		if(preg_match('/[^\d\w\x{0080}-\x{ffff}]+/u', $username)){
			import('module.context.XException');
			throw new XException('user name not valid');
		}
		$salt = mt_rand(1, 10000) * mt_rand(1, 10000);
		$salt = md5(json_encode($account).$request->getLastLoginTime().$salt);
		$dbPassword = $this->genDBPassword($password, $salt);
		$account['salt'] = $salt;
		$account['password'] = $dbPassword;
		$userAccountClass = $this->getUserAccountClass();
		import($userAccountClass);
		$userAccountClassName = $this->getClassName($userAccountClass);
		import('persistence.dao.XActiveRecord');
		import('persistence.orm.PersistenceSession');
		$userAccount = PersistenceSession::singleton()->get($userAccountClassName, $username);
		if(!empty($userAccount)){
			import('module.context.XException');
			throw new XException('user account already exists', E_ACCOUNT_ALREADY_EXIST);
		}
		
		try {
			import($userAccountClass);
			$userAccountClassName = $this->getClassName($userAccountClass);
			$userAccount = new $userAccountClassName();
			$userAccount->setAttrs($account);
			$userClass = $this->getUserClass();
			import('service.user.'.$userClass);
			$user = new $userClass();
			$user->setUsername($username);
			$user->save();
			$userAccount->setUserUid($user->getUID());
			$userAccount->save();
			$user->onRegister();
			import('service.user.event.UserRegisterEvent');
			XEventContext::singleton()->publish(new UserRegisterEvent($user));
		}
		catch(Exception $e){
			throw $e;
		}
		return $user;
	}
	
	/**
	 * <b>bind a group of platformAppId & platformUserId to a user account</b>
	 * 
	 * <b>绑定平台服务，将一组SNS平台APP ID和用户在SNS平台的ID绑定到一个用户账号上。</b>
	 * 
	 * @return Boolean
	 */
	public function bindPlatform(XServiceRequest $request){
		$platforms = $request->getData();
		if(empty($platforms) || !is_array($platforms)){
			import('module.context.XException');
			throw new XException('please specify platform infomation');
		}
		$gameUserId  = $request->getGameUserId();
		if(empty($gameUserId)){
			import('module.context.XException');
			throw new XException('please specify gameUserId');
		}
		$platformUserClass = $this->getPlatformUserClass();
		import('service.user.PlatformUser');
		import('persistence.dao.XActiveRecord');
		import('service.user.'.$platformUserClass);
		foreach ($platforms as $platform){
			if(isset($platform['platformAppId']) && isset($platform['platformUserId'])){
				$platformUser = new $platformUserClass();
				$platformUser->set('platformAddress', $platform['platformUserId'].'_'.$platform['platformAppId']);
				$platformUser->set('userUID', $gameUserId);
				$platformUser->save();
			}
		}
		return true;
	}
	
	/**
	 * <b>query a user by the given platform_uid and platform_user_uid</b>
	 * 
	 * <b>根据给定的平台APP ID和用户在平台上的ID对用户进行查询</b>
	 * 
	 * @return User
	 */
	public function query(XServiceRequest $request){
		$platformAddress = $request->getPlatformAddress();
		$platformUser = $this->getPlatformUser($platformAddress);
		if(empty($platformUser)){
			return null;
		}
		import('service.user.AbstractUserProfile');
		import('service.user.'.$this->getUserClass());
		$user = PersistenceSession::singleton()->get($this->getUserClass(), $platformUser->getUserUID());
		return $user;
	}
	
	/**
	 * get platform user instance by platform address
	 * 
	 * 根据platform address获取平台用户实例
	 * 
	 * @param string $platformAddress
	 * @return PlatformUser
	 */
	public function getPlatformUser($platformAddress){
		$platformUserClass = $this->getPlatformUserClass();
		import('service.user.'.$platformUserClass);
		$platformUser = $platformUserClass::getWithUID($platformAddress);
		return $platformUser;
	}
	
	/**
	 * <b>get the user class name from config file</b>
	 * 
	 * <b>从配置文件中读取用户模型类的名称</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	public function getUserClass(){
		if($this->userClass){
			return $this->userClass;
		}
		import('module.config.XConfig');
		$userClass = XConfig::singleton()->get('service.user.user.class');
		if(!$userClass){
			import('module.context.XException');
			throw new XException('user class name must defined in service config');
		}
		$this->userClass = $userClass;
		return $this->userClass;
	}
	
	/**
	 * <b>get the PlatformUser class name from config file</b>
	 * 
	 * <b>从配置文件中读取平台用户模型类的名称</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	public function getPlatformUserClass(){
		if($this->platformUserClass){
			return $this->platformUserClass;
		}
		import('module.config.XConfig');
		$platformUserClass = XConfig::singleton()->get('service.user.platformUser.class');
		if(!$platformUserClass){
			import('module.context.XException');
			throw new XException('platform user class name must defined in service config');
		}
		$this->platformUserClass = $platformUserClass;
		return $this->platformUserClass;
	}
	
	/**
	 * <b>get the UserAccount class name from config file</b>
	 * 
	 * <b>从配置文件中读取用户账户类的名称</b>
	 * 
	 * @return string
	 */
	public function getUserAccountClass(){
		if($this->userAccountClass){
			return $this->userAccountClass;
		}
		$userAccountClass = XConfig::singleton()->get('service.user.userAccount.class');
		if(empty($userAccountClass)){
			import('module.context.XException');
			throw new XException('user account class name must defined in config file');
		}
		$this->userAccountClass = $userAccountClass;
		return $userAccountClass;
	}
	
	private function getClassName($fullName){
		$className = preg_replace('/^[\w+\.]+\./', '', $fullName);
		return $className;
	}
	
	/**
	 * generate password stored in database by submitted password and salt
	 * 
	 * 根据前台提交的密码和salt生成最终密码，用于数据库存储
	 * 
	 * @param string $password
	 * @param string $salt
	 */
	public function genDBPassword($password, $salt){
		$dbPassword = base64_encode(hash_hmac('sha1', $password, $salt));
		return $dbPassword;
	}
}
?>