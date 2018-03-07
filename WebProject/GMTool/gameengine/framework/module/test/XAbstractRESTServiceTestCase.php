<?php
import_class('module.test.XAbstractServiceTestCase');
/**
 * XAbstractRESTServiceTestCase
 * 
 * abstract rest service testcase class
 * 
 * rest service testcase抽象类，继承XAbstractServiceTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test
 */
abstract class XAbstractRESTServiceTestCase extends XAbstractServiceTestCase{
	protected $domain='localhost';
	protected $protocol=NULL;
	protected $appendProtocol;
	protected $requestMethod;//0 or 1
	protected $ch=NULL;
	protected $useDefaultDomain = TRUE;
	protected $authConfig = 'oauth';
	protected $signature_method = 'MD5';
	protected $enableAuth = TRUE;
	
	protected function setUp(){
		$this->ch = curl_init();
		if($this->useDefaultDomain){
			$this->setDomain();
		}
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		if($this->initUserUid){
			$this->getUserUid();
		}
	}

	protected function tearDown(){
		curl_close($this->ch);
	}
	/**
	 * protocol setter
	 * 
	 * 设置protocol属性
	 * 
	 * @param string $protocol
	 */
	public function setProtocol($protocol){
		$this->protocol = $protocol;
	}
	/**
	 * protocol getter
	 * 
	 * 获取protocol属性
	 * 
	 * @return string
	 */
	public function getProtocol(){
		return $this->protocol;
	}
	
	protected function setRequestMethod($requestMethod){
		switch ($requestMethod){
			case 'post': case 'POST':{
				$this->requestMethod = 1;
				break;
			}
			case 'get': case 'GET':{
				$this->requestMethod = 0;
				break;
			}
		}
	}
	
	protected function getUserUid(){
		$appendProtocolChanged = FALSE;
		if(!$this->appendProtocol){
			$this->appendProtocol = TRUE;
			$appendProtocolChanged = TRUE;
		}
		$authConfig = $this->authConfig;
		$this->authConfig = 'oauth';
		$result = $this->invokeService('user.user.platformLogin', array());
		$this->assertServiceResult($result);
		if($result['data']==NULL){
			$result = $this->invokeService('user.user.platformRegister', array());
			$this->assertServiceResult($result);
		}
		XAssert::assertKeyExists('uid', $result['data']);
		XAssert::assertNotEmpty($result['data']['uid']);
		$this->gameUserId = $this->user_uid = $result['data']['uid'];
		if($appendProtocolChanged){
			$this->appendProtocol = FALSE;
		}
		$this->authConfig = $authConfig;
	}
	/**
	 * invoke a xingcloud service
	 * 
	 * 调用一个行云服务
	 * 以调用平台用户登录服务为例，调用方式为
	 * <code>
	 * $result = $this->invokeService('user.user.platformRegister');
	 * </code>
	 * 
	 * @param string $api 行云服务的API名称，例如user.user.platformLogin
	 * @param array $parameters 运行行云服务所需的参数
	 * @param boolean $decode 是否需要对服务调用结果进行json解码
	 * @return mixed
	 * @throws Exception
	 */
	public function invokeService($api, $parameters=array(), $decode=TRUE){
		$inputParams = array();
		$inputParams['id'] = $this->id;
		$inputParams['info'] = array('platformAppId'=>$this->platformAppId, 'platformUserId'=>$this->platformUserId, 'gameUserId'=>$this->gameUserId);
		$inputParams['data'] = $parameters;
		$postData = json_encode($inputParams);
		$apiUrl = $this->constructUrl().'/'.str_replace('.', '/', $api);
		if($this->enableAuth){
			$authHeader = $this->constructAuthHeader($apiUrl, $postData);
			curl_setopt($this->ch, CURLOPT_USERAGENT, "XingCloud OAuth Test");
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array($authHeader));
		}
		curl_setopt($this->ch, CURLOPT_POST, $this->requestMethod);
		switch ($this->requestMethod){
			case 0:{
				$apiUrl .= '?'.http_build_query($inputParams);
				break;
			}
			case 1:{
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);
				break;
			}
			default:{
				throw new Exception('unsupported http request method', 400);
				break;
			}
		}
		curl_setopt($this->ch, CURLOPT_URL, $apiUrl);
		$result = curl_exec($this->ch);
		if($result && $decode){
			return json_decode($result, TRUE);
		}else {
			return $result;
		}
	}
	
	protected function constructUrl(){
		$apiUrl = $this->domain;
		if($this->appendProtocol){
			$apiUrl .= '/'.$this->protocol;
		}
		return $apiUrl;
	}
	
	protected function constructAuthHeader($apiUrl, $postData, $authConfig=NULL){
		$authHeader = '';
		if(!$authConfig){
			$authConfig = $this->authConfig;
		}
		$config = XConfig::singleton()->get('auth.'.$authConfig);
		$authkey = XConfig::singleton()->get('authkey.'.$authConfig);
		if(is_array($authkey)){
			$config = array_merge($config, $authkey);
		}
		$oauth = array();
		$oauth['oauth_consumer_key'] = $config['consumer_key'];
		$oauth['oauth_timestamp'] = time();
		$oauth['oauth_nonce'] = md5(time().rand(0, 1000));
		$oauth['oauth_version'] = "1.0";
		$oauth['oauth_signature_method'] = $this->signature_method;
		if(!$this->requestMethod){
			$oauth = array_merge($oauth, json_decode($postData, TRUE));
		}
		ksort($oauth);
		$paramString = '';
		foreach ($oauth as $key=>$value){
			$paramString .= $key.'='.$value.'&';
		}
		$paramString = trim($paramString, '&');
		$requestMethod = $this->requestMethod?'POST':'GET';
		$base = strtoupper($requestMethod).'&'.rawurlencode($apiUrl).'&'.rawurlencode($paramString);
		if($this->requestMethod){
			$base .= '&'.rawurlencode($postData);
		}
		switch ($this->signature_method){
			case 'MD5':{
				$signature = base64_encode(md5($base.'&'.rawurlencode($config['secret_key'])));
				break;
			}
			case 'HMAC-SHA1':{
				$signature = base64_encode(hash_hmac("sha1", $base, $config['secret_key'], true));
				break;
			}
			default:{
				break;
			}
		}
		$oauth['oauth_signature'] = $signature;
		ksort($oauth);
		$authHeader = array();
		foreach($oauth as $key => $value){
			$authHeader[] = $key.'="'.$value.'"';
		}
		$authHeader = 'Authorization: OAuth '.implode(',', $authHeader);
		return $authHeader;
	}
	
	protected function setDomain(){
		$this->domain = 'http://'.$_SERVER['HTTP_HOST'];
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractServiceTestCase::assertServiceResult()
	 */
	public function assertServiceResult($result){
		XAssert::assertNotEmpty($result);
		XAssert::assertArray($result);
		XAssert::assertKeyExists('id', $result);
		XAssert::assertKeyExists('code', $result);
		XAssert::assertKeyExists('message', $result);
		XAssert::assertKeyExists('data', $result);
		XAssert::assertEquals(200, $result['code']);
		XAssert::assertEquals($this->id, $result['id']);
	}
}

?>