<?php
/**
 * XRouteParser
 * 
 * route parser interface
 * 
 * 路由解析器接口
 * 
 * @author Tianwei
 * @interface
 * @package mvc
 */
interface XRouteParser{
	/**
	 * parse the route information and params
	 * 
	 * 解析路由信息及其参数
	 * 
	 * @param string $dir
	 * @param string $route
	 * @param array $params
	 */
	public function parse($dir, $route, $params = array());
}
?>