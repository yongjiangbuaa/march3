<?php
import('module.mvc.view.XView');
/**
 * XAbstractView
 * 
 * abstract class of the view
 * 
 * 视图抽象类
 * 
 * @abstract
 * @author Tianwei
 * @package mvc
 */
abstract class XAbstractView implements XView{
	protected $controller = null;
	protected $contents = array();
	protected $route = null;
	protected $name = null;
	protected $extesion = null;
		
	/**
	 * controller setter
	 * 
	 * 设置controller属性
	 * 
	 * @param XController $controller
	 * @return XAbstractView
	 */	
	public function setController(XController $controller){
		$this->controller = $controller;
		return $this;
	}

	/**
	 * route setter
	 *
	 * 设置route属性
	 *
	 * @param string $route
	 * @return XAbstractView
	 */	
	public function setRoute($route){
		$this->route = $route;
		return $this;
	}
	
	/**
	 * name setter
	 *
	 * 设置name属性
	 *
	 * @param string $name
	 * @return XAbstractView
	 */	
	public function setName($name){
		$this->name = $name;
		return $this;
	}
	
	/**
	 * set a parameter of the view by key and value
	 * 
	 * 根据key和value设置视图的参数值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return XAbstractView
	 */	
	public function set($key, $value){
		$this->contents[$key] = $value;
		return $this;
	}
	
	/**
	 * set parameters to the specified array
	 * 
	 * 将参数值设置为给定的数组
	 * 
	 * @param array $array
	 * @return XAbstractView
	 */	
	public function setAll(array $array){
		foreach($array as $key => $value){
			$this->set($key, $value);
		}
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XView::view()
	 */
	public function view(){
		
	}
}
?>