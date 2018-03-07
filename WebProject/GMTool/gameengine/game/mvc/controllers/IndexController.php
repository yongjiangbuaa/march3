<?php
/**
 * @Controller
 */
import('module.mvc.controller.XAbstractGMController');
import('util.http.XServletRequest');
class IndexController extends XAbstractGMController {
	public function doIndex(){
		//取得平台ID
//		$data = file_get_contents('php://input');
//		$data = json_decode($data, TRUE);
//		$data = array_merge($data,$_GET);
		$data = array_merge($_GET, $_POST);
		$publishID = $data['platform_app'];
		$sid = $data['sid'];

		//取得GM所在服务器地址
		import('module.config.XConfig');
		$config = XConfig::singleton()->get('item_lang');
		if(is_array($config) && $config[$publishID] != NULL)
		{
			$server_host = $config[$publishID]['server'];
		}
		else 
		{
			$server_host = $_SERVER['SERVER_NAME'];
		}
		$this->publishID = $publishID;
		$this->sid = $sid;
		//通过redmine验证后加入gm系统内部验证机制
		import('module.util.session.XAbstractSession');
		XAbstractSession::setSessionID($sid);
		XAbstractSession::start();
		XAbstractSession::set('publishID', $publishID);
		$this->server_host = $server_host;
	}
}
?>