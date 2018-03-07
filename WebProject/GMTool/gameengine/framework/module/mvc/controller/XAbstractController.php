<?php
import('module.mvc.controller.XController');
/**
 * XAbstractController
 * 
 * abstract class of the controller
 * 
 * 控制器抽象类
 * 
 * @abstract
 * @author Tianwei
 * @package mvc
 */
abstract class XAbstractController implements XController{
	private $route = null;
	private $action = null;
	private $view = null;
	private $params = array();
	
	/** 
	 * action setter
	 * 
	 * 设置action属性，action表示执行controller时框架将会调用的该controller的方法名
	 * 
	 * 例如action属性设为为doIndex，则在controller执行时将会调用$controller->doIndex()方法
	 * 
	 * @param string $action
	 * @return XBasicController
	 */		
	public function setAction($action){
		$this->action = $action;
		return $this;
	}
	
	/** 
	 * action getter
	 * 
	 * 获取action属性
	 * 
	 * @return string
	 */	
	public function action(){
		return $this->action;
	}
	
	/** 
	 * view setter
	 * 
	 * 设置view属性, view为XView视图实例
	 * 
	 * @param XView $view
	 * 
	 * @return XBasicController
	 */		
	public function setView($view){
		$this->view = $view;
		$this->view->setController($this);
		return $this;
	}
	
	/** 
	 * view getter
	 * 
	 * 获取view属性的方法
	 * 
	 * @return mixed
	 */	
	public function view(){
		return $this->view;
	}
	
	/** 
	 * route setter
	 * 
	 * 设置route属性
	 * 
	 * @return XBasicController
	 */		
	public function setRoute($route){
		$this->route = $route;
		return $this;
	}
	
	/** 
	 * add params to the controller
	 * 
	 * 为controller添加参数，参数通过数组形式的$params传入
	 * 
	 * @param array $params
	 * @return XBasicController
	 */		
	public function addParams(array $params){
		foreach($params as $key => $value){
			$this->addParam($key, $value);
		}
		return $this;
	}
	
	/** 
	 * add a param to the controller
	 * 
	 * 为controller添加一个指定的参数
	 * 
	 * @param string $key
	 * @param string $value
	 * @return XBasicController
	 */		
	public function addParam($key, $value){
		$this->params[$key] = $value;
		return $this;
	}
	
	/** 
	 * param getter
	 * 
	 * 获取一个key为$key的参数值
	 * 
	 * @param string $key
	 * @return mixed
	 */		
	public function param($key){
		if(!isset($this->params[$key])){
			return null;
		}
		return $this->params[$key];
	}
	
	/** 
	 * params getter
	 * 
	 * 获取params参数
	 * 
	 * @return array
	 */	
	public function params(){
		return $this->params;
	}	
	
	/**
	 * execute the controller
	 * 
	 * 执行controller
	 * 
	 * 会在调用视图类之前执行__before()方法和$action()方法，$action为controller的action属性
	 * 会在调用视图类之后执行__after()方法
	 * 视图类的调用将会执行视图实例的view()方法
	 * 
	 * 例如，controller的action属性值为doIndex，则执行exec()方法时将会顺序调用$this->__before(), $this->doIndex(), $this->view->view(), $this->__after()方法
	 * 
	 * @throws XRouteNotFoundException
	 */
	public function exec(){		
		$action = $this->action;
		if(!method_exists($this, $action)){
			throw new XRouteNotFoundException();
		}
		//handle get/post params
		foreach($_GET as $key => $value){
			$this->addParam(urldecode($key), urldecode($value));
		}
		foreach($_POST as $key => $value){
			$this->addParam(urldecode($key), html_entity_decode($value));
		}
		$this->__before();
		$this->$action();
		if($this->view){
			$this->view->setRoute($this->route);
			$this->view->setName($action);
			$this->view->setAll(get_object_vars($this));
			$this->view->view();
		}
		$this->__after();
	}
	
	/** 
	 * XServletRequest getter
	 * 
	 * 获取XServletRequest单例
	 * 
	 * @return XServletRequest
	 */	
	public function request(){
		import('util.http.XServletRequest');
		return XServletRequest::singleton();
	}
	
	/** 
	 * XServletResponse getter
	 * 
	 * 获取XServletResponse单例
	 * 
	 * @return XServletResponse
	 */		
	public function response(){
		return XServletResponse::singleton();
	}
	
	/**
	 * redirect the client page to the specified url
	 * 
	 * 将客户端页面重定向到指定的url
	 * 
	 * @param string $url
	 */
	public function redirect($url = null){
		return $this->response()->redirect($url);
	}
	
	/**
	 * forward to the specified route
	 * 
	 * 导向到指定route
	 * 
	 * @param string $route
	 * @param array $params
	 */
	public function forward($route, array $params = array()){
		if($route == $this->route){
			return true;
		}
		return XRouter::singleton()->forward($route, $params);
	}
	
	protected function __before(){
		
	}
	
	protected function __after(){
		
	}
}
?>