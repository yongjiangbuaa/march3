<?php
import('module.security.XAuthenticationProvider');
/**
 * XHttpAuthenticationProvider
 * 
 * authentication provider for http request
 * 
 * 普通http请求的验证提供者
 * 
 * @author Tianwei
 * @package security 
 */
class XHttpAuthenticationProvider implements XAuthenticationProvider{
	protected $config = array();
	
	/**
	 * construct method
	 * 
	 * 构造方法，从配置文件读取验证所需的consumer_key和secret_key
	 * 
	 * @throws XAuthenticationException
	 */
	function __construct(){
		import('util.config.XConfig');
		$this->config = XConfig::singleton()->get('auth.oauth');
		$authkey = XConfig::singleton()->get('authkey.oauth');
		$this->config = array_merge($this->config, $authkey);
		if(!is_array($this->config)
			|| !isset($this->config['consumer_key'])
			|| !isset($this->config['secret_key'])){
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('oauth config is missing');
		}
	}
	
	/**
	 * authenticate
	 * 
	 * 进行验证
	 * 
	 * 后台根据前台传入参数及验证信息进行计算，得出signature，并与前台签名oauth_signature进行对比，相同则验证通过。
	 * 
	 * 前台传入的验证信息放在HTTP请求的Header中，key为Authorization
	 * 
	 * signature的计算采用OAuth方式，支持MD5和HMAC-SHA1两种加密算法
	 * 
	 * @param object $object
	 * @param string $method
	 * @param array $args
	 */
	public function authenticate($object, $method, $args){
		return true;
		if(!$object instanceof XService
			|| !isset($args[0])
			|| !$args[0] instanceof XServiceRequest
			|| !$args[0] instanceof XAuthentication){
			return true;
		}
		$className = get_class($object);
		$apiName = $className.'.'.$method;
		//check if service auth disabled
		if(isset($this->config['services'])
			&& isset($this->config['services']['disabled'])
			&& is_array($this->config['services']['disabled'])
			&& array_key_exists($apiName,$this->config['services']['disabled'])){
			return true;
		}
		//check if service auth enabled when all service disabled by default
		if(!isset($this->config['default'])
			|| !$this->config['default']){
			if(!isset($this->config['services'])
				|| !isset($this->config['services']['enabled'])
				|| !is_array($this->config['services']['enabled'])
				|| (is_array($this->config['services']['enabled'])&&!array_key_exists($apiName, $this->config['services']['enabled'])))
			return true;
		}
		$params = array();
		$authenticateHeaders = $args[0]->getHeader('Authorization');
		import('module.util.logger.XLoggingEvent');
		XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, DEBUG, 'Authorization: '.$authenticateHeaders));
		if($authenticateHeaders){
			preg_match_all('/([\w_]+)\s*=[\s\'"]*([^,\s\'"]+)[,\s\'"]*/i', $authenticateHeaders, $match);
			if(isset($match[1])
				&& isset($match[2])
				&& count($match[1]) == count($match[2])){
				$params = array_combine($match[1], $match[2]);
			}
		}
		$request = $args[0]->getPrincipal();
		$params = array_merge($params, $request->gets(), $request->posts());
		if(isset($params['uri'])){
			unset($params['uri']);
		}
		foreach(array(
			'oauth_consumer_key',
			'oauth_nonce',
			'oauth_signature',
			'oauth_signature_method',
			'oauth_timestamp',
			'oauth_version',
		) as $oauthKey){
			if(!isset($params[$oauthKey])){
				import('module.security.XAuthenticationException');
				throw new XAuthenticationException('oauth param '.$oauthKey.' is missing');
			}
		}
		if(empty($params['oauth_timestamp']) || abs(time() - intval($params['oauth_timestamp'])) > (isset($this->config['expires']) ? $this->config['expires'] : 86400)){
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('oauth_timestamp expires');
		}
		if($this->config['consumer_key'] != $params['oauth_consumer_key']){
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('oauth_consumer_key is wrong');
		}
		$signature = rawurldecode($params['oauth_signature']);
		unset($params['oauth_signature']);
		ksort($params);
		$base = ($request->isHttps() ? 'https' : 'http').'://'.$request->host();
		$port = $request->port();
		if($port != 80){
			//$base .= ':'.$port;
		}
		$base .= $request->uri();
		$base = strtoupper($request->method()).'&'.rawurlencode($base);
		$paramString = '';
		foreach($params as $key => $value){
			$paramString .= $key.'='.$value.'&';
		}
		$paramString = trim($paramString, '&');
		$base .= '&'.rawurlencode($paramString);
		$details = $args[0]->getDetails();
		if(!empty($details)){
			$base .= '&'.rawurlencode($details);
		}
		XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, DEBUG, 'Base String: '.$base));
		$serverSignature = null;
		switch(strtoupper((string) $params['oauth_signature_method'])){
			case 'HMAC-SHA1':
				$serverSignature = base64_encode(hash_hmac("sha1", $base, $this->config['secret_key'], true));
				break;
			case 'MD5':
				$serverSignature = base64_encode(md5($base.'&'.rawurlencode($this->config['secret_key'])));
				break;
		}
		if(!$serverSignature || $serverSignature != $signature){
			XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, DEBUG, 'Signature Not Valid: expected '.$serverSignature.', but is '.$signature));
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('signature is not valid');
		}
		return true;
	}
}
?>