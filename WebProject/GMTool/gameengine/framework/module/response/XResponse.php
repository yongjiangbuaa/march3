<?php
import('util.http.XServletResponse');
/**
 * XResponse
 * 
 * #en response interface
 * #zh response接口，封装后台返回前台的结果
 * @author Tianwei
 * @interface
 * @package response
 */
interface XResponse{
	/** 
	 * #en set result
	 * #zh 注入结果
	 * @param mixed $result 结果
	 * @return XResponse
	 */
	public function setResult($result);
	
	/** 
	 * #en get result
	 * #zh 获取结果
	 * @return XResult
	 */
	public function getResult();
	
	/** 
	 * #en get XServletResponse
	 * #zh 获得XServletResponse
	 * @return XServletResponse
	 */
	function getServletResponse();
	
	/** 
	 * #en set XServletResponse
	 * #zh 注入XServletResponse
	 * @param XServletResponse $servletResponse
	 * @return XResponse
	 */	
	function setServletResponse(XServletResponse $servletResponse);
	
	/** 
	 * #en output result
	 * #zh 向前台输出结果
	 * @return boolean
	 */	
	function output();
}
?>