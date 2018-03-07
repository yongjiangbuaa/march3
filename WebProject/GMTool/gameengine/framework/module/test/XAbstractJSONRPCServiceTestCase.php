<?php
import_class('module.test.XAbstractRESTServiceTestCase');
/**
 * XAbstractJSONRPCServiceTestCase
 * 
 * abstract jsonrpc service testcase class
 * 
 * jsonrpc service testcase抽象类，继承XAbstractRESTServiceTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test
 */
abstract class XAbstractJSONRPCServiceTestCase extends XAbstractRESTServiceTestCase{
	protected $responseId;
	
	protected function getUserUid(){
		$result = $this->invokeService('user.user.platformLogin', array());
		$this->assertServiceResult($result);
		if($result['result']['data']==NULL){
			$result = $this->invokeService('user.user.platformRegister', array());
			$this->assertServiceResult($result);
		}
		XAssert::assertKeyExists('uid', $result['result']['data']);
		XAssert::assertNotEmpty($result['result']['data']['uid']);
		$this->gameUserId = $this->user_uid = $result['result']['data']['uid'];
	}
	
	protected function constructPostData($method, $parameters){
		$postData = array(
			'id'=>$this->responseId,
			'method'=>$method,
			'params'=>$parameters
		);
		return json_encode($postData);
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRESTServiceTestCase::invokeService()
	 */
	public function invokeService($api, $parameters=array(), $decode=TRUE){
		$inputParams = array();
		$inputParams['id'] = $this->id;
		$inputParams['info'] = array('platformAppId'=>$this->platformAppId, 'platformUserId'=>$this->platformUserId, 'gameUserId'=>$this->gameUserId);
		$inputParams['data'] = $parameters;
		$this->setRequestMethod('post');
		$apiArr = explode('.', $api);
		if(count($apiArr)!=3){
			throw new XException('api name error', 400);
		}
		$apiUrl = $this->constructUrl().'/'.$apiArr[0].'/'.$apiArr[1];
		$postData = $this->constructPostData($apiArr[2], $inputParams);
		if($this->enableAuth){
			$authHeader = $this->constructAuthHeader($apiUrl, $postData);
			curl_setopt($this->ch, CURLOPT_USERAGENT, "XingCloud OAuth Test");
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array($authHeader));
		}
		curl_setopt($this->ch, CURLOPT_POST, $this->requestMethod);
		switch ($this->requestMethod){
			case 0:{
				if(!empty($inputParams)){
					$apiUrl .= '?'.http_build_query($inputParams);
				}
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
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRESTServiceTestCase::assertServiceResult()
	 */
	public function assertServiceResult($result){
		XAssert::assertNotEmpty($result);
		XAssert::assertArray($result);
		XAssert::assertKeyExists('id', $result);
		XAssert::assertKeyExists('result', $result);
		XAssert::assertKeyExists('error', $result);
		XAssert::assertEquals($this->responseId, $result['id']);
		parent::assertServiceResult($result['result']);
	}
}

?>