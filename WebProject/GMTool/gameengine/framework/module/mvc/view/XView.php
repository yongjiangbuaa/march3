<?php
/**
 * XView
 * 
 * view interface
 * 
 * 视图接口
 * 
 * @author Tianwei
 * @interface
 * @package mvc
 */
interface XView{
	/**
	 * controller setter
	 * 
	 * 设置controller属性
	 * 
	 * @param XController $controller
	 */
	function setController(XController $controller);
	/**
	* route setter
	*
	* 设置route属性
	*
	* @param string $route
	*/
	function setRoute($route);
	/**
	* name setter
	*
	* 设置name属性
	*
	* @param string $name
	*/
	function setName($name);
	/**
	 * set a parameter of the view by key and value
	 * 
	 * 根据key和value设置视图的参数值
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	function set($key, $value);
	/**
	 * set parameters to the specified array
	 * 
	 * 将参数值设置为给定的数组
	 * 
	 * @param array $array
	 */
	function setAll(array $array);
	/**
	 * display the view
	 * 
	 * 执行视图的显示方法
	 */
	function view();
}
?>