<?php
import('module.aop.XAspect');
/**
 * XAuthAspect
 * 
 * Authentication Aspect Class
 * 
 * 安全认证的Aspect，对方法调用进行安全认证
 * 
 * @author Tianwei
 * @interface
 * @package aop
 */
class XAuthAspect implements XAspect{
	/** 
	 * Authentication Aspect executer
	 * 
	 * 进行安全认证的函数
	 * 
	 * 该方法会加载安全认证的配置，按照$object的$method方法中的注释信息初始化相应的XAuthenticationProvider，进行安全认证
	 * 
	 * 如果安全认证失败，会抛出XAuthenticationException
	 * 
	 * 如果安全认证通过，会执行对象调用
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 */
	public function execute($factory, $object, $method, $args = array()){	
		import('util.config.XConfig');
		$hasConfig = XConfig::singleton()->has('auth');		
		if(!$hasConfig){
			return $factory->execute($object, $method, $args);
		}
		import('util.annotation.XAnnotation');
		$className = get_class($object);
		$authAnnotation = x_array_fast_get(XAnnotation::singleton($className)->methodAnnotations(), $method, 'Auth');
		if(!$authAnnotation
			|| !isset($authAnnotation['type'])){
			return $factory->execute($object, $method, $args);
		}		
		import('module.security.XAuthenticationException');
		try{
			$type = ucfirst($authAnnotation['type']);
			$className = 'X'.$type.'AuthenticationProvider';
			import('module.security.'.$className);
			$provider = new $className();
			$provider->authenticate($object, $method, $args);
		}catch(XAuthenticationException $e){
			import('module.util.logger.XExceptionEvent');
			XEventContext::singleton()->publish(new XExceptionEvent($e));
			throw $e;
		}catch(Exception $e){
			throw $e;
		}
		return $factory->execute($object, $method, $args);
	}
}
?>