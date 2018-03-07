<?php
import('module.mvc.controller.XAbstractController');
/**
* XAbstractGMController
*
* abstract class of the GM controller
*
* GM控制器抽象类
*
* @abstract
* @author Wangqi
* @package mvc
*/
abstract class XAbstractGMController extends XAbstractController{
	protected $config = array();
	protected function __before(){
		try {
// 			$this->authenticate();
		}
		catch (XAuthenticationException $e){
			echo $e->getMessage();
			exit();
		}
		catch (Exception $e){
			header(XHeaderResult::$HEADERS[401]);
			exit();
		}
	}
	
	protected function authenticate(){
		$this->config = XConfig::singleton()->get('auth.admin');
		$authkey = XConfig::singleton()->get('authkey.admin');
		$this->config = array_merge($this->config, $authkey);
		$params = array();
		$authenticateHeaders = $this->request()->header('Authorization');
		if($authenticateHeaders){
			preg_match_all('/([\w_]+)\s*=[\s\'"]*([^,\s\'"]+)[,\s\'"]*/i', $authenticateHeaders, $match);
			if(isset($match[1])
				&& isset($match[2])
				&& count($match[1]) == count($match[2])){
				$params = array_combine($match[1], $match[2]);
			}
		}
		$params = array_merge($params, $this->request()->gets(), $this->request()->posts());
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
		$base = ($this->request()->isHttps() ? 'https' : 'http').'://'.$this->request()->host();
		$port = $this->request()->port();
		if($port != 80){
			$base .= ':'.$port;
		}
		$base .= $this->request()->uri();
		$base = strtoupper($this->request()->method()).'&'.rawurlencode($base);
		$paramString = '';
		foreach($params as $key => $value){
			$paramString .= $key.'='.$value.'&';
		}
		$paramString = trim($paramString, '&');
		$base .= '&'.rawurlencode($paramString);
		$details = file_get_contents('php://input');
		if(!empty($details)){
			$base .= '&'.rawurlencode($details);
		}
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
			import('module.security.XAuthenticationException');
			throw new XAuthenticationException('signature is not valid');
		}
		return TRUE;
	}
}