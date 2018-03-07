<?php
/**
 * XController
 * 
 * controller interface
 * 
 * 控制器接口
 * 
 * @author Tianwei
 * @interface
 * @package mvc
 */
interface XController{
	/**
	 * route setter
	 * 
	 * 设置route属性
	 * 
	 * @param string $route
	 */
	function setRoute($route);
	/**
	* action setter
	*
	* 设置action属性
	*
	* @param string $action
	*/
	function setAction($action);
	/**
	* view setter
	*
	* 设置view属性
	*
	* @param string $view
	*/
	function setView($view);
	/**
	 * add params to the controller
	 * 
	 * 为controller添加参数，参数通过数组形式的$params传入
	 * 
	 * @param array $params
	 */
	function addParams(array $params);
	/**
	 * execute the controller
	 * 
	 * 执行controller
	 */
	function exec();
}
?>