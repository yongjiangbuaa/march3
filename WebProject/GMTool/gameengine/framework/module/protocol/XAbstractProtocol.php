<?php
import('module.protocol.XProtocol');
import('module.security.XAuthentication');
/**
 * XAbstractProtocol
 * 
 * abstract protocol class
 * 
 * <b>协议抽象类，实现XProtocol和XAuthentication接口</b>
 * 
 * @author Tianwei
 * @abstract
 * @package protocol 
 */
abstract class XAbstractProtocol implements XProtocol, XAuthentication{
	protected $stream;
	protected $name;
	protected $gateway;
	protected $route;
	
	/**
	 * construct method
	 * 
	 * 构造方法
	 */
	public function __construct(){
		
	}	
	
	/** 
	 * stream setter
	 * 
	 * 设置数据流实例
	 * 
	 * @return XProtocol
	 */
	public function setStream(XStream $stream){
		$this->stream = $stream;
		return $this;
	}
	
	/**
	 * stream getter
	 * 
	 * 获取数据流实例
	 * 
	 * @return XStream
	 */
	public function getStream(){
		return $this->stream;
	}
	
	/**
	 * name getter
	 * 
	 * 获取name属性
	 * 
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}	
	/**
	 * gateway getter
	 * 
	 * 获取gateway属性
	 * 
	 * @return string
	 */	
	function getGateway(){
		return $this->gateway;
	}
	
	/** 
	 * route setter
	 * 
	 * 设置route属性
	 * 
	 * @return XProtocol
	 */
	public function setRoute($route){
		$this->route = $route;
		return $this;
	}

	/**
	 * request getter
	 * 
	 * 获取XServiceRequest实例
	 * 
	 * @return XServiceRequest
	 */
	function getRequest(){
		import('module.service.XServiceRequest');
		return XServiceRequest::singleton();
	}
	
	/**
	 * response getter
	 * 
	 * 获取XServiceResponse实例
	 * 
	 * @return XServiceResponse
	 */
	function getResponse(){
		import('module.service.XServiceResponse');
		$response = XServiceResponse::singleton();
		$response->setStream($this->getStream());
		return $response;
	}
	
	/** 
	 * get authentication details
	 * 
	 * 获取验证详细资料
	 * 
	 * @return array
	 */	
	public function getDetails(){
		return null;
	}
	
	/**
	 * get authentication principal
	 * 
	 * 获得验证主体
	 * 
	 * @return XServiceRequest
	 */
	public function getPrincipal(){
		return $this->getRequest();
	}
	
	/**
	 * get authentication header
	 * 
	 * 获取验证头信息
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getHeader($key){
		return XServletRequest::singleton()->header($key);
	}
}
?>