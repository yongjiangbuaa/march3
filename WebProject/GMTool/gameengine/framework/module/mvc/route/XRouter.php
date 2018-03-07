<?php
/**
 * XRouter
 * 
 * router class
 * 
 * 路由器类
 * 
 * @final
 * @author Tianwei
 * @package mvc
 */
final class XRouter{
	private static $instance = null;
	private $dir = '';
	private $extensions = array();
	private $hasExtensions = false;
	private $view = null;
	
	private function __construct() {
	
	}
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XRouter
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * start the router
	 * 
	 * 路由器开始工作，解析路由信息和参数，并执行相关操作
	 * 
	 * @throws XRouteNotFoundException
	 */
	public function start(){	
		$this->_init();
		$uri = x_get_uri();
		preg_match("/^([^\?]+)/i", $uri, $match);
		if(!isset($match[1])){
			import('module.mvc.route.XRouteNotFoundException');
			throw new XRouteNotFoundException("url {$uri} not valid");
			return;
		}
		$route = $match[1];
		$params = $this->_params($uri);	
		$this->forward($route, $params);	
	}
	
	private function _init(){
		$this->dir = GAME_MVC_DIR.__DS__.'controllers';
		$this->view = 'XSmartyView';
	}

	private function _params($uri){
		$params = array();
		preg_match("/\?([^\?]+)$/i", $uri, $match);
		if(!isset($match[1])){		
			return $params;
		}
		$array = preg_split("/&+/i", $match[1]);
		foreach($array as $value){
			$pair = preg_split("/=+/i", $value);
			if(isset($pair[0])){
				$params[urldecode($pair[0])] = isset($pair[1]) ? urldecode($pair[1]) : null;
			}
		}
		return $params;
	}
	
	/**
	 * load controller and execute the controller
	 * 
	 * 加载控制器并执行，会调用控制器的exec()方法
	 * 
	 * @param string $route
	 * @param string $file
	 * @param string $className
	 * @param string $methodName
	 * @param array $params
	 */
	public function loadController($route, $file, $className, $methodName, $params = array()){
		$newFile = preg_replace("/[\\|\/]+/i", DIRECTORY_SEPARATOR, $file);
		$newRoute = preg_replace("/[\\|\/]+/i", '/', $route);
		if(!is_file($newFile)){
			return false;
		}
		import('module.mvc.controller.XAbstractController');
		import('module.mvc.view.XAbstractView');
		$runned = false;
		if(!class_exists($className, false)){
			x_require($newFile);
		}
		if(class_exists($className, false)
			&& method_exists($className, $methodName)){
			$view = $this->view;
			$controller = new $className();
			$controller->setRoute($newRoute);
			$controller->setAction($methodName);		
			$controller->addParams($params);
			import('module.mvc.view.'.$view);
			$controller->setView(new $view());
			$controller->exec();
			$runned = true;
		}
		return $runned;
	}
	
	/**
	 * forward to the specified route
	 * 
	 * 导向到指定的路由，会调用XRESTRouteParser的parse()方法解析路由信息和参数
	 * 
	 * @param string $route
	 * @param array $params
	 * @throws XRouteNotFoundException
	 * @return boolean
	 */
	public function forward($route, $params = array()){	
		import('module.mvc.route.XRESTRouteParser');
		$parser = new XRESTRouteParser();
		if($parser->parse($this->dir, $route, $params)){
			return true;
		}
		import('module.mvc.route.XRouteNotFoundException');
		throw new XRouteNotFoundException("route not found in {$route}");	
	}
}
?>