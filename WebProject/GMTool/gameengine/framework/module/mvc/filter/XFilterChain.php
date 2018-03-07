<?php
import('util.http.XServletRequest');
import('util.http.XServletResponse');
/**
 * XFilterChain
 * 
 * filter chain interface
 * 
 * 过滤链接口
 * 
 * @author Tianwei
 * @interface
 * @package mvc
 */
interface XFilterChain{
	/**
	 * start to filter
	 * 
	 * 开始执行过滤
	 * 
	 * @param XServletRequest $request
	 * @param XServletResponse $response
	 */
	public function doFilter(XServletRequest $request, XServletResponse $response);
}
?>