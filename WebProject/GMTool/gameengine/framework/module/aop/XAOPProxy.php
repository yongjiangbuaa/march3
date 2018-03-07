<?php
/**
 * XAOPProxy
 * 
 * AOP Proxy Class
 * 
 * AOP代理，将实际访问对象封装，拦截实际访问对象的方法调用
 * 
 * @author Tianwei
 * @package aop
 */
import('util.model.XObject');
class XAOPProxy extends XObject{
	protected $delegate = null;
	
	public function __construct($delegate){
		$this->delegate = $delegate;
	}
	
	/** 
	 * delegate getter method
	 * 
	 * 被代理对象的getter
	 * 
	 * 该方法会返回被代理对象
	 * 
	 * @return object
	 */		
	public function getDelegate(){
		return $this->delegate;
	}

	/** 
	 * delegate method caller
	 * 
	 * 被代理对象方法的拦截和执行函数
	 * 
	 * 该方法会拦截被代理对象的方法调用，并交给XAOPFactory进行执行
	 * 
	 * @param string $method 被代理对象的方法
	 * @param array $args 被代理对象的方法参数
	 * @return mixed
	 */		
	public function __call($method, $args = array()){
		import('module.aop.XAOPFactory');
		return XAOPFactory::singleton()->execute($this->delegate, $method, $args);
  	}
  	
	/** 
	 * delegate __get wrapper
	 * 
	 * 被代理对象动态__get方法的包装方法
	 * 
	 * 该方法会调用和返回被代理对象动态__get的值
	 * 
	 * @param string $key 属性名
	 * @return mixed
	 */		
	public function __get($key){
		return $this->delegate->__get($key);
	}
	
	/** 
	 * delegate __set wrapper
	 * 
	 * 被代理对象动态__set方法的包装方法
	 * 
	 * 该方法会调用被代理对象动态__set，返回XAOPProxy
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XAOPProxy
	 */
	public function __set($key, $value){
		$this->delegate->__set($key, $value);
		return $this;
	}

	/** 
	 * delegate to Array method
	 * 
	 * 将被代理对象以数组形式输出的方法
	 * 
	 * @return mixed
	 */	
	public function asArray(){
		return get_object_vars($this->delegate);
	}
}
?>