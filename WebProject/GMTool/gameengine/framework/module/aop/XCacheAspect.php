<?php
import('module.aop.XAspect');
import('module.aop.XBatchAspect');
/**
 * XCacheAspect
 * 
 * Cache Aspect Class
 * 
 * 方法级缓存的Aspect类，支持对多个方法进行批量缓存处理
 * 
 * @author Tianwei
 * @package aop
 */
class XCacheAspect implements XAspect, XBatchAspect{
	/** 
	 * Cache Aspect executer
	 * 
	 * 进行方法级缓存的函数
	 * 
	 * 该方法会加载缓存配置，按照$object的$method方法中的注释信息初始化相应的XCacheAccess，进行缓存处理
	 * 
	 * 如果有缓存，会直接返回缓存结果
	 * 
	 * 如果没有缓存，会执行对象调用
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 */
	public function execute($factory, $object, $method, $args = array()){
		import('util.config.XConfig');
		$hasCache = XConfig::singleton()->has('cache');		
		if(!$hasCache){
			return $factory->execute($object, $method, $args);
		}
		import('util.annotation.XAnnotation');
		$className = get_class($object);
		$cacheAnnotation = x_array_fast_get(XAnnotation::singleton($className)->methodAnnotations(), $method, 'Cache');
		if(!$cacheAnnotation){
			return $factory->execute($object, $method, $args);
		}
		import('cache.CacheServiceFactory');
		$region = x_array_get($cacheAnnotation, 'region');
		$cache = CacheServiceFactory::getCacheAccess($region);
		if(!$cache){
			return $factory->execute($object, $method, $args);
		}
		$key = null;
		if(method_exists($object, 'getCacheKey')){
			$key = $object->getCacheKey($method, $args, $cacheAnnotation);
		}else{
			$key = substr(SERVER_UID, 0, 5).'_'.$className.'_'.$method.'_'.json_encode($args);
		}
		$key = md5($key);
		$result = $cache->get($key);
		if($result){
			return $result;
		}
		$result = $factory->execute($object, $method, $args);
		$cache->put($key, $result);
		if(method_exists($object, 'onCacheUpdated')){
			$object->onCacheUpdated($key, $result);
		}
		return $result;
	}
	
	/** 
	 * Batch Cache Aspect executer
	 * 
	 * 方法级批量缓存的函数
	 * 
	 * 该方法会加载缓存配置，按照$object的$method方法中的注释信息初始化相应的XCacheAccess，进行批量缓存处理
	 * 
	 * 对于每一个方法调用，如果有缓存，会直接返回缓存结果；
	 * 
	 * 如果没有缓存，会执行对象调用
	 * 
	 * @param XAOPFactory $factory AOP工厂
	 * @param object $object 类实例
	 * @param array $batchMethods 方法名数组
	 * @param array $batchArgs 方法参数数组
	 * @return mixed
	 */	
	public function batchExecute($factory, $object, $batchMethods, $batchArgs = array()){
		import('util.config.XConfig');
		$hasCache = XConfig::singleton()->has('cache');		
		if(!$hasCache){
			return $factory->batchExecute($object, $batchMethods, $batchArgs);
		}
		import('util.annotation.XAnnotation');
		import('cache.CacheServiceFactory');
		$className = get_class($object);
		$count = count($batchMethods);
		$keys = array();
		$results = array();
		for($i = 0; $i < $count; $i++){		
			$args = isset($batchArgs[$i]) ? $batchArgs[$i] : array();	
			$method = $batchMethods[$i];	
			$cacheAnnotation = x_array_fast_get(XAnnotation::singleton($className)->methodAnnotations(), $method, 'Cache');
			if(!$cacheAnnotation){
				$results[$i] = $factory->execute($object, $method, $args);
				continue;
			}
			$region = x_array_get($cacheAnnotation, 'region');
			$cache = CacheServiceFactory::getCacheAccess($region);
			if(!$cache){
				$results[$i] = $factory->execute($object, $method, $args);
				continue;
			}
			if(method_exists($object, 'getCacheKey')){
				$keys[$i] = md5($object->getCacheKey($method, $args, $cacheAnnotation));
			}else{
				$keys[$i] = md5(substr(SERVER_UID, 0, 5).'_'.$className.'_'.$method.'_'.json_encode($args));			
			}
		}
		if(empty($keys)){
			return $results;
		}
		$cacheResults = $cache->get(array_values($keys));
		$puts = array();
		if(empty($cacheResults)){
			foreach($keys as $i => $key){
				$args = isset($batchArgs[$i]) ? $batchArgs[$i] : array();
				$results[$i] = $factory->execute($object, $batchMethods[$i], $args);
				$puts[$key] = $results[$i];
			}
		}else{
			foreach($keys as $i => $key){
				if(isset($cacheResults[$key]) && !empty($cacheResults[$key])){
					$results[$i] = $cacheResults[$key];
				}else{
					$results[$i] = $factory->execute($object, $batchMethods[$i], $args);
					$puts[$key] = $results[$i];
				}
			}
		}
		if(!empty($puts)){
			$keys = array_keys($puts);
			$values = array_values($puts);
			$cache->put($keys, $values);
			if(method_exists($object, 'onCacheUpdated')){
				$object->onCacheUpdated($keys, $values);
			}
		}
		return $results;
	}
}
?>