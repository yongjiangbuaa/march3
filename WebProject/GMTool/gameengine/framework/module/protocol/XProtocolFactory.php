<?php
/**
 * XProtocolFactory
 * 
 * protocol factory
 * 
 * 协议工厂类
 * 
 * @author Tianwei
 * @final
 * @package protocol
 */
final class XProtocolFactory{
	private static $instance = null;

	/**
	 * construct method
	 * 
	 * 构造方法
	 */
	private function __construct() {
	}
		
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * @return XProtocolFactory
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}	
	
	/** 
	 * get the XProtocol instance from uri
	 * 
	 * 从请求uri中生成XProtocol实例
	 * 
	 * @return XProtocol
	 */	
	public static function get(){
		$uri = x_get_uri();
		preg_match("/^([^\?]+)/i", $uri, $match);
		if(!isset($match[1])){
			import('module.context.XException');
			throw new XException("url {$uri} not valid");
			return false;
		}
		$route = $match[1];
		$routes = array(
			'amf' => 'XAMFProtocol',
			'rest' => 'XRESTProtocol',
			'file' => 'XFileProtocol',
			'xadmin' => 'XAdminProtocol',
			'discovery' => 'XDiscoveryProtocol',
			'jsonrpc' => 'XRPCProtocol',
			'xmlrpc' => 'XRPCProtocol',
			'status' => 'XStatusProtocol',
		);
		foreach($routes as $key => $value){
			if(preg_match("/^\/+{$key}/i", $route)){
				import('module.protocol.'.$value);
				$protocol = new $value();
				$protocol->setRoute($route);
				return $protocol;
			}
		}
		return null;
	}
}
?>