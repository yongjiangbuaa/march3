<?php
/**
 * 这个类是private的，不要直接使用，
 * 要通过PlatformManger::getPlatformHandler('facebook', $platform_params)来获取操作句柄
 */

require dirname(__FILE__). DIRECTORY_SEPARATOR . "../qq/OpenApiV3.php";

class QqHandler extends PlatformManager {
	private $_qqObj;
	
	public function __construct($appId, $appKey, $appSecret) {
		parent::__construct($appId, $appKey, $appSecret);
		
		try {
			$this->_qqObj = new OpenApiV3($appId, $appSecret);
			$this->_loginUid = $this->facebookObj->getUser();
			if (!$this->_loginUid) {
				$loginUrl = $this->facebookObj->getLoginUrl();
				header('Location: '.$loginUrl);
				//exit('<a href="'.$loginUrl.'">Login with Facebook</a>');
			}
		} catch (Exception $e) {
			throw new PlatformException('init connection error!!!', PlatformException::ERROR_INIT_CONNECTION);
		}
	}
	
	public function getLoginUserId() {
		return $this->_loginUid;
	}
	
	public function getUserInfo($uid) {
		try {
			$params = array();
			$params['openid'] = empty($_REQUEST['openid']) ? '' : $_REQUEST['openid'];
			$params['openkey'] = empty($_REQUEST['openkey']) ? '' : $_REQUEST['openkey'];
			$params['pf'] = empty($_REQUEST['pf']) ? '' : $_REQUEST['pf'];;
			$platformUserInfo = $this->_qqObj->api('/v3/user/get_info', $params,'post');
		} catch (Exception $e) {
			throw new PlatformException('get platform user info error!!!', PlatformException::ERROR_GET_USER);
		}
		
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