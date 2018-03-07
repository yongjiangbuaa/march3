<?php
import('util.http.XServletRequest');
import('util.http.XServletResponse');
import('module.mvc.filter.XFilterChain');
/**
 * XFilter
 * 
 * filter interface
 * 
 * 过滤器接口
 * 
 * @author Tianwei
 * @interface
 * @package mvc
 */
interface XFilter{
	/**
	 * execute the filter
	 * 
	 * 执行过滤器
	 * 
	 * @param XServletRequest $request
	 * @param XServletResponse $response
	 * @param XFilterChain $chain
	 */
	public function doFilter(XServletRequest $request, XServletResponse $response, XFilterChain $chain);
}
?>