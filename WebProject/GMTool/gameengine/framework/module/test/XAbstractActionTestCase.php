<?php
import('module.test.XAbstractServiceTestCase');
/**
 * XAbstractActionTestCase
 * 
 * abstract action testcase class
 * 
 * action testcase抽象类，继承XAbstractServiceTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test 
 */
abstract class XAbstractActionTestCase extends XAbstractServiceTestCase{
	protected $data = NULL;
	
	protected function addAction($name, $params){
		$this->data[] = array(
			'name'=>$name,
			'params'=>$params
		);
	}
	/**
	 * execute an action
	 * 
	 * 执行一个action
	 * @return XActionResult
	 */
	public function executeAction(){
		$parameters = array('id'=>1, 'data'=>$this->data, 'info'=>array('platformAppId'=>$this->platformAppId, 'platformUserId'=>$this->platformUserId, 'gameUserId'=>$this->gameUserId));
		$this->setRequestParam('action.action.execute', $parameters);
		$result = XActionContext::singleton()->execute($this->request);
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
		XAssert::assertObject($result);
		XAssert::assertPropertyExists($result, 'code');
		XAssert::assertEquals(200, $result->code);
		XAssert::assertPropertyExists($result, 'message');
		XAssert::assertPropertyExists($result, 'data');
		XAssert::assertArray($result->data);
		foreach ($result->data as $r){
			XAssert::assertEquals(200, $r->code);
			XAssert::assertPropertyExists($r, 'message');
			XAssert::assertPropertyExists($r, 'data');
			$this->assertServiceResult($r->data);
		}
	}
	
}