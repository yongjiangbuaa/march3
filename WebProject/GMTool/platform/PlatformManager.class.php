<?php
/**
 * sns平台工厂类 ...
 * @author laoyang
 *
 */
abstract class PlatformManager {
	protected $_appId; //平台 app id
	protected $_appKey; //平台 app key
	protected $_appSecret; //平台 app secret
	protected $_loginUid = 0; //登陆用户id
	protected $_snsType = 'facebook'; //SNS平台，如：facebook, qq
	
	protected static $platformObj;
	
	public function __construct($appId, $appKey, $appSecret) {
		$this->_appId = $appId;
		$this->_appKey = $appKey;
		$this->_appSecret = $appSecret;
	}
	
	public static function getPlatformInstance($snsType, $appId, $appKey, $appSecret) {
		$handlerClass = $snsType . "Handler";
		
		require_once dirname(__FILE__) . '/handler/' .ucfirst($snsType).'Handler.class.php';
		if (is_null(self::$platformObj) || !isset(self::$platformObj)) {
			self::$platformObj = new $handlerClass($appId, $appKey, $appSecret);
		}

		return self::$platformObj;
		
	}
	
	abstract function getLoginUserInfo();
	
	abstract function getLoginUserId();
	
	abstract function getUserInfo($uid);
	
	abstract function getPlatformParams();
}

/**
 * sns平台异常类 ...
 * @author laoyang
 *
 */
class PlatformException extends Exception {
	const ERROR_GET_USER = 10000;
	const ERROR_INIT_CONNECTION = 10001;
}
?>