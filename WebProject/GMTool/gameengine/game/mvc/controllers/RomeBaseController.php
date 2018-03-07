<?php
import('module.mvc.controller.XAbstractGMController');
import('util.http.XServletRequest');
class RomeBaseController extends XAbstractGMController {

	protected function __before(){
		try {
			$this->__permissionCheck();
		}catch (XException $e){
			header(XHeaderResult::$HEADERS[401]);
			echo $e->getMessage();
			exit();
		}
		//取得GM所在服务器地址
		import('module.config.XConfig');
		$publishID = $this->publishID;
		$config = XConfig::singleton()->get('item_lang');
		if(is_array($config) && $config[$publishID] != NULL)
		{
			$server_host = $config[$publishID]['server'];
		}
		else
		{
			$server_host = $_SERVER['SERVER_NAME'];
		}
//		$this->publishID = $publishID;
		$this->server_host = $server_host;
		$this->lang = $config[$publishID]['lang'];
		//GM图片服务器地址
		$this->img = 'p.xingcloud.com';
	}
	
	
	private function __permissionCheck(){
		//GM系统内部验证
		$this->sid = $_GET['sid'];
		import('module.util.session.XAbstractSession');
		XAbstractSession::setSessionID($this->sid);
		XAbstractSession::start();
		$publishID = XAbstractSession::get('publishID');
		if(strlen($publishID) < 2){
			import('module.security.XAuthenticationException');
// 			throw new XAuthenticationException("publishID is ".$publishID . "and sid is" . $this->sid);
		}
		//生成GM Service验证码
		$this->sign = md5($publishID . 'c11adfaf72ed4787a6d1c46ef8841800');
		$this->publishID = $publishID;
		return TRUE;
	}
	
	
}
?>