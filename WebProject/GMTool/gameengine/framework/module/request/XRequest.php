<?php
import('module.context.user.UserAware');
/**
 * XRequest
 * 
 * #en request interface
 * #zh request接口，封装前台对后台的请求
 * @author Tianwei
 * @interface
 * @package request
 */
interface XRequest extends UserAware{
	/** 
	 * #en get parameter by key
	 * #zh 用参数名获得参数
	 * @param string $key 参数名
	 * @return mixed
	 */
	function getParameter($key);
	
	/** 
	 * #en get parameters array
	 * #zh 获得参数数组
	 * @return array
	 */	
	function getParameters();

	/** 
	 * #en set parameters array
	 * #zh 注入参数数组
	 * @param array $parameters 参数数组
	 * @return mixed
	 */	
	function setParameters($parameters);
}
?>