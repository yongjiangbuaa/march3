<?php
import_class('module.test.XAbstractJSONRPCServiceTestCase');
/**
 * XAbstractJSONRPCActionTestCase
 * 
 * abstract jsonrpc action testcase class
 * 
 * jsonrpc action testcase抽象类，继承XAbstractJSONRPCServiceTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test
 */
abstract class XAbstractJSONRPCActionTestCase extends XAbstractJSONRPCServiceTestCase{
	protected $data = array();
	
	protected function addAction($name, $params){
		$this->data[] = array(
			'name'=>$name,
			'params'=>$params,
		);
	}
	
	protected function setActionRequestParam($id, $data, $info){
		$this->requestArr = array('id'=>$id, 'data'=>$data, 'info'=>$info);
	}
	/**
	 * invoke an action
	 * 
	 * 调用一个action
	 * 
	 * @param boolean $decode
	 * @return XResult
	 */
	public function invokeAction($decode=TRUE){
		$parameters = array('id'=>1, 'data'=>$this->data, 'info'=>array('platformAppId'=>$this->platformAppId, 'platformUserId'=>$this->platformUserId, 'gameUserId'=>$this->gameUserId));
		$result = $this->invokeService('action.action.execute', $parameters, $decode);
		return $result;
	}
	/**
	 * assert action execution result
	 * 
	 * 验证action执行结果是否符合格式要求
	 * 
	 * @param XResult $result
	 * @throws XAssertFailedException
	 */
	public function assertActionResult($result){
		XAssert::assertNotEmpty($result);
		XAssert::assertArray($result);
		XAssert::assertKeyExists('id', $result);
		XAssert::assertKeyExists('result', $result);
		XAssert::assertKeyExists('error', $result);
		XAssert::assertEquals($this->responseId, $result['id']);
		$result = $result['result'];
		XAssert::assertNotEmpty($result);
		XAssert::assertArray($result);
		XAssert::assertKeyExists('id', $result);
		XAssert::assertKeyExists('code', $result);
		XAssert::assertKeyExists('message', $result);
		XAssert::assertKeyExists('data', $result);
		XAssert::assertEquals(200, $result['code']);
		XAssert::assertArray($result['data']);
	}
}


?>