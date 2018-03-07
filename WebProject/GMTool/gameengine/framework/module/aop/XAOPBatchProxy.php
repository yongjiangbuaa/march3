<?php
import('module.aop.XAOPProxy');
/**
 * XAOPBatchProxy
 * 
 * Batch AOP Class
 * 
 * 对一个对象的多个函数调用进行织入的动态代理类 
 * 
 * @author Tianwei
 * @package aop
 */
class XAOPBatchProxy extends XAOPProxy{
	protected $delegate = null;
	protected $batchMethods = array();
	protected $batchArgs = array();

	/** 
	 * dynamic proxy caller method
	 * 
	 * 动态代理调用方法，当代理对象执行方法调用时，会自动调用该函数
	 * 
	 * <b>该方法不会立即返回方法调用结果，而是先将方法名和调用参数分别存在数组中</b>
	 * 
	 * @param string $method 代理对象要调用的方法名
	 * @param array $args 代理对象要调用的参数
	 */	
	public function __call($method, $args = array()){
		$this->batchMethods[] = $method;
		$this->batchArgs[] = $args;
  	}
  	
  	/** 
	 * batch execute method
	 * 
	 * 动态代理批量处理方法
	 * 
	 * <b>该方法会批量执行之前代理对象调用的方法，并进行织入，最后返回批量结果，顺序与调用顺序一致</b>
	 * @return array
	 */
  	public function batch(){
		import('module.aop.XAOPFactory');
		$results = XAOPFactory::singleton()->batchExecute($this->delegate, $this->batchMethods, $this->batchArgs);  		
  		$this->batchMethods = array();
  		$this->batchArgs = array();
  		return $results;
  	}
}
?>