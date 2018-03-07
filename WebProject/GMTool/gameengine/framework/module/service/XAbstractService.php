<?php
import('module.service.XService');
import('module.service.XServiceRequest');
import('module.service.result.XServiceResult');
import('util.model.XObject');
import('cache.module.XCacheable');
/**
 * XAbstractService
 * 
 * abstract service class
 * 
 * service的抽象方法，实现XService, XCacheable接口，继承XObject类
 * 
 * @author Tianwei
 * @abstract
 * @package service 
 */
abstract class XAbstractService extends XObject implements XService, XCacheable{
	/**
	 * <b>create a XServiceResult object with code=200 when success</b>
	 * 
	 * <b>成功时返回一个code=200的XServiceResult实例</b>
	 * 
	 * @param mixed $data
	 * @return XServiceResult
	 */
	protected function _success($data = array()){
		return $this->_data(200, '', $data);
	}
	
	/**
	 * <b>create a XServiceResult object when error occurs</b>
	 * 
	 * <b>错误时返回一个XServiceResult实例</b>
	 * 
	 * @param int $code
	 * @param string $message
	 * @param mixed $data
	 */
	protected function _error($code = 400, $message = '', $data = array()){
		return $this->_data($code, $message, $data);
	}
	
	/**
	 * <b>create a XServiceResult object with code=400 when client error occurs</b>
	 * 
	 * <b>客户端错误时返回一个code=400的XServiceResult实例</b>
	 * 
	 * @param string $message
	 * @param mixed $data
	 */
	protected function _clientError($message = '', $data = array()){
		return $this->_data(400, $message, $data);
	}
	
	/**
	 * <b>create a XServiceResult object with code=500 when server error occurs</b>
	 * 
	 * <b>服务器端错误时返回一个code=500的XServiceResult实例</b>
	 * 
	 * @param string $message
	 * @param mixed $data
	 */
	protected function _serverError($message = '', $data = array()){
		return $this->_data(500, $message, $data);
	}
	
	/**
	 * <b>create a XServiceResult object with given code, message and data</b>
	 * 
	 * <b>根据给定的code, message和data生成一个XServiceResult对象</b>
	 * 
	 * @param int $code
	 * @param string $message
	 * @param mixed $data
	 */
	protected function _data($code, $message, $data){
		return new XServiceResult($code, $message, $data);
	}
	
	/** 
	 * <b>method cache key getter</b>
	 * 
	 * <b>获取缓存主键</b>
	 * 
	 * @param string $method 方法名
	 * @param array $args 参数
	 * @param array $config 缓存配置
	 * @return string
	 */
	public function getCacheKey($method, $args = array(), $config = array()){
		$className = get_class($this);
		$key = substr(SERVER_UID, 0, 5).'_'.$className.'_'.$method;
		if(isset($config['scope'])
			&& $config['scope'] == 'application'){
			return $key;
		}
		return $key.'_'.json_encode($args[0]->getParameters());		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XCacheable::onCacheUpdated()
	 */
	public function onCacheUpdated($cacheKey, $cacheValue){
		
	}	
}
?>