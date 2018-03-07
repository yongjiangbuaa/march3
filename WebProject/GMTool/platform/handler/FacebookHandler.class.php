<?php
/**
 * 这个类是private的，不要直接使用，
 * 要通过PlatformManger::getPlatformHandler('facebook', $platform_params)来获取操作句柄
 */

require dirname(__FILE__). DIRECTORY_SEPARATOR . "../facebook/facebook.php";

class FacebookHandler extends PlatformManager {
	private $facebookObj;
	
	public function __construct($appId, $appKey, $appSecret) {
		parent::__construct($appId, $appKey, $appSecret);
		
		try {
			$this->facebookObj = new Facebook(array(
			  'appId'  => $appId,
			  'secret' => $appSecret,
			));
			$this->_loginUid = $this->facebookObj->getUser();
			if (!$this->_loginUid) {
				$loginUrl = $this->facebookObj->getLoginUrl();
				//header('Location: ' . $loginUrl);
				exit('<script>parent.location.href="'.$loginUrl.'";</script>');
				//exit('<a href="'.$loginUrl.'" target="_top">Login with Facebook</a>');
			}
		} catch (Exception $e) {
			throw new PlatformException('init connection error!!!', PlatformException::ERROR_INIT_CONNECTION);
		}
	}
	
	public function getFacebookObj(){
		return $this->facebookObj;
	}
	
	public function getLoginUserInfo() {
		$uid = $this->getLoginUserId();
		return $this->getUserInfo($uid);
	}
	
	public function getLoginUserId() {
		return $this->_loginUid;
	}
	
	public function getUserInfo($uid) {
		$platformUserInfo = $this->facebookObj->api('/me');
		
		return array(
			'uid' => $platformUserInfo['id'],
			'name' => $platformUserInfo['name'],
			'gender' => $platformUserInfo['gender'],
		);
	}
	
	public function getPlatformParams() {
		return array();
	}
}
?>
