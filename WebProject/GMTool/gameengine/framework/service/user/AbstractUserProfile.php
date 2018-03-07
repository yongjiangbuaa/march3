<?php
import('service.user.XUser');
import('persistence.dao.XActiveRecord');
/**
 * AbstractUserProfile
 * 
 * abstract class of UserProfile
 * 
 * UserProfile抽象类
 * 
 * @Entity
 * @author Tianwei
 * @package user
 */
abstract class AbstractUserProfile extends XActiveRecord implements XUser{
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 */
	protected $uid = null;
	
	protected $platformAddress = null;
	
	/**
	 * @var string
	 */
	protected $username = '';
	
	protected $experience=0;
	
	protected $level=0;
 	
 	protected $coin=0;
 	
 	protected $money=0;
 	
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
	 * <b>username getter</b>
	 * 
	 * <b>获取username属性的方法</b>
	 * 
	 * @return string
	 */
	public function getUsername(){
    	return $this->get('username');
    }
    
    /**
	 * <b>username setter</b>
	 * 
	 * <b>设置username属性的方法</b>
	 * 
	 * @param string $username
	 */
    public function setUsername($username){
    	$this->set('username', $username);
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

    /**
	 * <b>experience getter</b>
	 * 
	 * <b>获取experience属性的方法</b>
	 * 
	 * @return int
	 */
	public function getExperience(){
    	return $this->get('experience');
    }
    /**
	 * <b>experience setter</b>
	 * 
	 * <b>设置experience属性的方法</b>
	 * 
	 * @param int $experience
	 */
    public function setExperience($experience){
    	$this->set('experience', $experience);
    }

    /**
	 * <b>level getter</b>
	 * 
	 * <b>获取level属性的方法</b>
	 * 
	 * @return int
	 */
    public function getLevel(){
    	return $this->get('level');
    }
    /**
	 * <b>level setter</b>
	 * 
	 * <b>设置level属性的方法</b>
	 * 
	 * @param int $level
	 */
    public function setLevel($level){
    	$this->set('level', $level);
    }
    
	/**
	 * <b>coin getter</b>
	 * 
	 * <b>获取coin属性的方法</b>
	 * 
	 * @return int
	 */
    public function getCoin(){
    	return $this->get('coin');
    }
    /**
	 * <b>coin setter</b>
	 * 
	 * <b>设置coin属性的方法</b>
	 * 
	 * @param int $coin
	 */
    public function setCoin($coin){
    	$this->set('coin', $coin);
    }

    /**
	 * <b>money getter</b>
	 * 
	 * <b>获取money属性的方法</b>
	 * 
	 * @return int
	 */
    public function getMoney(){
    	return $this->get('money');
    }
    /**
	 * <b>money setter</b>
	 * 
	 * <b>设置money属性的方法</b>
	 * 
	 * @param int $money
	 */
    public function setMoney($money){
    	$this->set('money', $money);
    }
    
	/**
	 * <b>this mothod will be invoked when the user logged in</b>
	 * 
	 * <b>用户登陆时会调用此方法</b>
	 */
	public function onLogin(){
		
	}
	
	/**
	 * <b>this mothod will be invoked when the user registered</b>
	 * 
	 * <b>用户注册时会调用此方法</b>
	 */
	public function onRegister(){
		
	}
	
	/**
	 * <b>this mothod will be invoked when user profile is loaded</b>
	 * 
	 * <b>加载用户档案时会调用此方法</b>
	 */
	public function onLoad(){
		
	}

	/**
	 * (non-PHPdoc)
	 * @see XActiveRecord::isSelf()
	 */
	public function isSelf(){
		import('module.util.session.XSession');
		$httpSession = XSession::singleton()->getInstance();
		$request = null;
		if($httpSession){
			$request = $httpSession->getRequest();
		}
		if (!$request){
			return false;
		}
		$requestUserId =$request->getGameUserId();
		if(!empty($requestUserId)){
			return $requestUserId==$this->getUid();
		}else{
			$requestPlatformAddress = $request->getPlatformAddress();
			if(!empty($requestPlatformAddress)){
				return $requestPlatformAddress==$this->getPlatformAddress();
			}
		}
		return false;
	}
}
?>