<?php
import('module.security.XHttpAuthenticationProvider');
/**
 * XAdminAuthenticationProvider
 * 
 * authentication provider for GM system
 * 
 * GM系统验证提供者
 * 
 * @author Tianwei
 * @package security 
 */
class XAdminAuthenticationProvider extends XHttpAuthenticationProvider{
	protected $config = array();
	
	/**
	 * construct method
	 * 
	 * 构造方法，从配置文件读取GM验证的consumer_key和secret_key
	 * 
	 * @throws XAuthenticationException
	 */
	function __construct(){
		import('util.config.XConfig');
		$this->config = XConfig::singleton()->get('auth.admin');
		$authkey = XConfig::singleton()->get('authkey.admin');
		$this->config = array_merge($this->config, $authkey);
		if(!is_array($this->config)
			|| !isset($this->config['consumer_key'])
			|| !isset($this->config['secret_key'])){
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('oauth config is missing');
		}
	}
}
?>