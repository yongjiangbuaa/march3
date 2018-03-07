<?php
import('module.aop.XAspect');
/**
 * XMethodAspect
 * 
 * Protocol Aspect Class
 * 
 * 对前后台通信协议进行访问控制的Aspect类，支持对Service进行访问控制
 * 
 * @author Tianwei
 * @package aop
 */
class XProtocolAspect implements XAspect{
	/** 
	 * Protocol Aspect executer
	 * 
	 * 对Protocol进行访问控制的函数
	 * 
	 * 该方法会读取$object的$method方法中的Protocol注释信息，进行访问控制
	 * 
	 * 如果注释信息为@Protocol(deny='ALL')，阻止访问，并抛出错误XException
	 * 
	 * 如果注释信息为@Protocol(allow='ALL')，放行访问，执行对象调用，返回对象调用结果
	 * 
	 * 同时，以REST协议为例
	 * 
	 * 如果注释信息为@Protocol(deny='REST')，而当前协议为REST，阻止访问，并抛出错误XException
	 * 
	 * 如果注释信息为@Protocol(allow='REST')，而当前协议不为REST，阻止访问，并抛出错误XException
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
		$protocolAnnotation = XAnnotation::singleton($className)->method($method.'.Protocol');		
		if(!is_array($protocolAnnotation)
			|| !isset($args[0])
			|| !$args[0] instanceof XServiceRequest){
			return $factory->execute($object, $method, $args);
		}
		//deny first
		if(isset($protocolAnnotation['deny'])){
			$protocolName = strtoupper($args[0]->getContext()->getProtocol()->getName());
			if($protocolAnnotation['deny'] == 'ALL'
				|| x_is_in($protocolName, $protocolAnnotation['deny'])){
				import('module.context.XException');
				throw new XException("service class {$className} method {$method} called by protocol {$protocolName} is not allowed!");
			}
		}
		//then allow
		if(isset($protocolAnnotation['allow'])){
			$protocolName = strtoupper($args[0]->getContext()->getProtocol()->getName());
			if($protocolAnnotation['allow'] != 'ANY'
				&& !x_is_in($protocolName, $protocolAnnotation['allow'])){
				import('module.context.XException');
				throw new XException("service class {$className} method {$method} called by protocol {$protocolName} is not allowed!");
			}
		}
		return $factory->execute($object, $method, $args);
	}
}
?>