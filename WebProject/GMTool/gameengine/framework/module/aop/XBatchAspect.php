<?php
/**
 * XBatchAspect
 * 
 * AOP Batch Aspect Interface
 * 
 * 批量Aspect接口，定义批量Aspect通用的方法
 * 
 * @author Tianwei
 * @interface
 * @package aop
 */
interface XBatchAspect{
	/** 
	 * batch Aspect executer
	 * 
	 * 进行批量Aspect执行的函数
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param array $batchMethods 方法名数组
	 * @param array $batchArgs 方法参数数组
	 * @return mixed
	 */	
	public function batchExecute($factory, $object, $batchMethods, $batchArgs = array());
}
?>