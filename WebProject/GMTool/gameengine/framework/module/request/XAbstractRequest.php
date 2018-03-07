<?php
import('module.request.XRequest');
/**
 * XAbstractRequest
 * 
 * abstract request class
 * 
 * <b>请求抽象类，实现XRequest接口</b>
 * 
 * 前台提交的请求都以Request形式进行封装
 * 
 * @author Tianwei
 * @abstract
 * @package request 
 */
abstract class XAbstractRequest implements XRequest{
	protected $parameters = array();
	protected $user = null;
	
	/**
	 * (non-PHPdoc)
	 * @see XRequest::getParameter()
	 */
	function getParameter($key){
		if(!isset($this->parameters[$key])){
			return null;
		}
		return $this->parameters[$key];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XRequest::getParameters()
	 */
	function getParameters(){
		return $this->parameters;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XRequest::setParameters()
	 * @return XRequest
	 */
	function setParameters($parameters){
		$this->parameters = $parameters;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XRequest::getUser()
	 */
	function getUser(){
		return $this->user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XRequest::setUser()
	 */
	function setUser($user){
		$this->user = $user;
		return $this;
	}
	
	/** 
	 * #en dynamic call method
	 * #zh 动态调用参数的方法
	 * #zh 比如有参数item_id,可以使用getItemId()方法取得该值，同时无需真正实现getItemId()方法
	 * #zh __call方法在程序调用getItemId()时候会自动调用，并取得item_id值
	 * @param string $method 方法名
	 * @param mixed $args 参数
	 * @return mixed
	 */
	public function __call($method, $args){
		if(preg_match('/^get[A-Z]/i', $method)){
			$param = x_name_to_c(preg_replace('/^get/i', '', $method));
			if(!isset($this->parameters[$param])){
				import('module.context.XException');
				throw new XException('invalid param '.$param, 400);
			}
			return $this->parameters[$param];			
		}
	}	
}
?>