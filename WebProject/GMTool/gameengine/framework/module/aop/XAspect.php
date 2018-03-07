<?php
/**
 * XAspect
 * 
 * AOP Aspect Interface
 * 
 * Aspect接口，定义Aspect通用的方法
 * 
 * @author Tianwei
 * @interface
 * @package aop
 */
interface XAspect{
	/** 
	 * aspect executer
	 * 
	 * Aspect执行函数
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 */		
	public function execute($factory, $object, $method, $args = array());
}
?>