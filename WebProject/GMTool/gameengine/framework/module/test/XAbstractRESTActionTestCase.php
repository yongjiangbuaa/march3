<?php
import_class('module.test.XAbstractRESTServiceTestCase');
/**
 * XAbstractRESTActionTestCase
 * 
 * abstract rest action testcase class
 * 
 * rest service testcase抽象类，继承XAbstractServiceTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test
 */
abstract class XAbstractRESTActionTestCase extends XAbstractRESTServiceTestCase{
	protected $data = NULL;
	
	protected function addAction($name, $params){
		$this->data[] = array(
			'name'=>$name,
			'params'=>$params
		);
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
		XAssert::assertKeyExists('code', $result);
		XAssert::assertKeyExists('message', $result);
		XAssert::assertKeyExists('data', $result);
		XAssert::assertEquals(200, $result['code']);
		XAssert::assertArray($result['data']);
		foreach ($result['data'] as $v){
			XAssert::assertArray($v);
			XAssert::assertKeyExists('index', $v);
			XAssert::assertKeyExists('code', $v);
			XAssert::assertEquals(200, $v['code']);
			XAssert::assertKeyExists('message', $v);
			XAssert::assertKeyExists('data', $v);
			XAssert::assertArray($v['data']);
			$this->assertServiceResult($v['data']);
		}
	}
}


?>