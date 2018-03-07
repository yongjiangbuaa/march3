<?php
import('module.aop.XAspect');
/**
 * XMethodAspect
 * 
 * Http Method Aspect Class
 * 
 * 对Http Method进行访问控制的Aspect类，支持对Service进行访问控制
 * 
 * @author Tianwei
 * @package aop
 */
class XMethodAspect implements XAspect{
	/** 
	 * Http Method Aspect executer
	 * 
	 * 对Http Method进行访问控制的函数
	 * 
	 * 该方法会读取$object的$method方法中的Method注释信息，进行访问控制
	 * 
	 * 如果注释信息为@Method(deny='ALL')，阻止访问，并抛出错误XException
	 * 
	 * 如果注释信息为@Method(allow='ALL')，放行访问，执行对象调用，返回对象调用结果
	 * 
	 * 同时，以POST为例
	 * 
	 * 如果注释信息为@Method(deny='POST')，而当前请求为POST，阻止访问，并抛出错误XException
	 * 
	 * 如果注释信息为@Method(allow='POST')，而当前请求不为POST，阻止访问，并抛出错误XException
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 */
	public function execute($factory, $object, $method, $args = array()){
		import('util.annotation.XAnnotation');
		import('module.service.XServiceRequest');
		$className = get_class($object);
		$httpMethodAnnotation = XAnnotation::singleton($className)->method($method.'.Method');
		if(!is_array($httpMethodAnnotation)
			|| !isset($args[0])
			|| !$args[0] instanceof XServiceRequest){
			return $factory->execute($object, $method, $args);
		}
		//deny first
		if(isset($httpMethodAnnotation['deny'])){
			$httpMethod = strtoupper($args[0]->getServletRequest()->method());
			if($httpMethodAnnotation['deny'] == 'ALL'
				|| x_is_in($httpMethod, $httpMethodAnnotation['deny'])){
				import('module.context.XException');
				throw new XException("service class {$className} method {$method} called by http method {$httpMethod} is not allowed!");
			}
		}
		//then allow
		if(isset($httpMethodAnnotation['allow'])){
			$httpMethod = strtoupper($args[0]->getServletRequest()->method());
			if($httpMethodAnnotation['allow'] != 'ANY'
				&& !x_is_in($httpMethod, $httpMethodAnnotation['allow'])){
				import('module.context.XException');
				throw new XException("service class {$className} method {$method} called by http method {$httpMethod} is not allowed!");
			}
		}
		return $factory->execute($object, $method, $args);
	}
}
?>