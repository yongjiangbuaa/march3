<?php
/**
 * XInvoker
 * 
 * invoker interface
 * 
 * invoker接口
 * 
 * @package util.invoker
 * @interface
 * @author wangqi
 */
interface XInvoker{
	/**
	 * invoke a service/action
	 * 
	 * 调用服务或Action
	 * 
	 * @param string $api
	 * @param array $parameters
	 */
	public function invoke($api, $parameters);
}