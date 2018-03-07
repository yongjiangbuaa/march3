<?php
import('module.util.invoker.XInvoker');
/**
 * XAbstractInvoker
 * 
 * abstract invoker class
 * 
 * 调用器invoker的抽象方法，实现XInvoker接口</b>
 * 
 * @author Wangqi
 * @abstract
 * @package util.invoker 
 */
abstract class XAbstractInvoker implements XInvoker{
	protected $serviceRequest = null;
	
	protected function setServiceRequest($classPath, $className, $methodName, $parameters){
		if(!$this->serviceRequest){
			import('module.service.XServiceRequest');
			import('module.service.XServiceContext');
			import('util.http.XServletRequest');
			$this->serviceRequest = XServiceRequest::singleton();
			$this->serviceRequest->setContext(XServiceContext::singleton());
			$this->serviceRequest->setServletRequest(XServletRequest::singleton());
		}
		$this->serviceRequest->setClassPath($classPath);
		$this->serviceRequest->setClassName($className);
		$this->serviceRequest->setMethodName($methodName);
		$this->serviceRequest->setParameters($parameters);
	}
}