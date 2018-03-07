<?php
import('module.service.discovery.XDiscoveryService');
/**
 * XRPCDiscoveryService
 * 
 * discovery all the existing rpc services
 * 
 * rpc API发现服务，发现已有的rpc API
 * 
 * @author Tianwei
 * @Pointcut('auth|cache')
 * @package service
 */
class XRPCDiscoveryService extends XDiscoveryService{
	/**
	 * <b>get the method names of a given class</b>
	 * 
	 * <b>获取给定类的所有方法名</b>
	 * 
	 * <b>会对方法名进行过滤和重命名，方法的作用域必须是public，名称为doSomthing（S为大写字母），重命名后为somthing（s为小写字母）</b>
	 * 
	 * @param string $path 类所在的完整路径，如果类不存在，返回空数组
	 * @param string $className 类名
	 * @return array $results 存储方法名的数组
	 */
	protected function getMethods($path, $className){
		$included = include_once($path);
		if($included === FALSE){
			return array();
		}
		$results = array();
		$class = new ReflectionClass($className);
		$methods = $class->getMethods();
		foreach($methods as $method){
			if($method->isPublic()
				&& preg_match('/^do[A-Z]\w+/', $method->getName())){
				$serviceName = preg_replace('/^do([A-Z])/ie', "strtolower('\\1')", $method->getName());
				$result = XServiceAnnotation::singleton($className)->service($serviceName);
				if(!$result){
					$result = array();
				}
				$allow = x_array_fast_get($result, 'Protocol', 'allow');
				if(!x_is_in('ANY', $allow)
					&& !x_is_in('RPC', $allow)){
					continue;
				}
				$result['serviceName'] = $serviceName;
				$results[$serviceName] = $result;
			}
		}
		ksort($results);
		return $results;
	}
}
?>