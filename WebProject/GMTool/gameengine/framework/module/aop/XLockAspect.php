<?php
import('module.aop.XAspect');
/**
 * XLockAspect
 * 
 * Lock Aspect Class
 * 
 * 对方法调用进行加锁的Aspect类，支持对函数调用加锁
 * 
 * @author Tianwei
 * @package aop
 */
class XLockAspect implements XAspect{
	/** 
	 * Lock Aspect executer
	 * 
	 * 进行方法级加锁的函数
	 * 
	 * 该方法会加载锁配置，按照$object的$method方法中的注释信息，初始化XLocker，并进行加锁
	 * 
	 * 如果已经有锁，会调用$object的onLockConflicted方法，并抛出锁冲突的错误XException
	 * 
	 * 如果加锁出错，会尝试进行解锁，并抛出错误XException
	 * 
	 * 如果加锁成功，会执行对象调用，最后进行解锁，返回对象调用结果
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 */
	public function execute($factory, $object, $method, $args = array()){
		import('util.config.XConfig');
		if(!XConfig::singleton()->has('lock')){
			return $factory->execute($object, $method, $args);
		}
		$className = get_class($object);
		$annotation = XAnnotation::singleton($className);
		$lockAnnotation = $annotation->get('Lock');
		$policy = x_array_fast_get($lockAnnotation, 'policy');
		if(!$policy || strtolower($policy) == 'none'){
			return $factory->execute($object, $method, $args);
		}
		$lockAnnotation = $annotation->method($method.'.Lock');
		$policy = x_array_fast_get($lockAnnotation, 'policy');
		if(!$policy || strtolower($policy) == 'none'){
			return $factory->execute($object, $method, $args);
		}
		import('module.util.lock.XLock');
		$locker = XLock::singleton();
		if(!$locker){
			return $factory->execute($object, $method, $args);
		}
		try{
			if($locker->isLocked($object)){
				$object->onLockConflicted($locker);
				import('module.context.XException');
				throw new XException('E600004');
				//throw new XException('Class '.$className.' method '.$method.' is locked');
			}
			//MYSQL初始化
// 			import('util.mysql.XMysql');
// 			if(!XMysql::singleton()->connect()){
// 				throw new XException('E600004');
// 			}
			$defaultTime = x_array_get($lockAnnotation, 'defaultTime');
			if($defaultTime){
				$locker->lock($object, $defaultTime);
			}else{
				$locker->lock($object);
			}
			$result = $factory->execute($object, $method, $args);
			$locker->release($object);
			return $result;
		}catch(Exception $e){
			if($locker && ($e->getCode() == 6001 || rand(1,3) == 1)){
				$locker->release($object);
			}
			throw $e;
		}
		return $factory->execute($object, $method, $args);
	}
}
?>