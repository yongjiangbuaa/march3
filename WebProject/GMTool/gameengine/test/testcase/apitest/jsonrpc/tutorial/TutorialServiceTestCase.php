<?php
class TutorialServiceTestCase extends XJSONRPCServiceTestCase{
	
	public function testStep(){
		$parameters = array(
				'tutorial'=>'unittest',
				'name'=>'unittest',
				'index'=>1
		);
		$result = $this->invokeService('tutorial.tutorial.step', $parameters);
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertKeyExists($parameters['tutorial'], $result['data']);
		XAssert::assertKeyExists('tutorial', $result['data'][$parameters['tutorial']]);
		XAssert::assertKeyExists('name', $result['data'][$parameters['tutorial']]);
		XAssert::assertKeyExists('index', $result['data'][$parameters['tutorial']]);
		XAssert::assertEquals($parameters['tutorial'], $result['data'][$parameters['tutorial']]['tutorial']);
		XAssert::assertEquals($parameters['name'], $result['data'][$parameters['tutorial']]['name']);
		XAssert::assertEquals($parameters['index'], $result['data'][$parameters['tutorial']]['index']);
		
		$parameters['index'] += 1;
		$result = $this->invokeService('tutorial.tutorial.step', $parameters);
		$result = $result['result'];
		XAssert::assertKeyExists($parameters['tutorial'], $result['data']);
		XAssert::assertKeyExists('tutorial', $result['data'][$parameters['tutorial']]);
		XAssert::assertKeyExists('name', $result['data'][$parameters['tutorial']]);
		XAssert::assertKeyExists('index', $result['data'][$parameters['tutorial']]);
		XAssert::assertEquals($parameters['tutorial'], $result['data'][$parameters['tutorial']]['tutorial']);
		XAssert::assertEquals($parameters['name'], $result['data'][$parameters['tutorial']]['name']);
		XAssert::assertEquals($parameters['index'], $result['data'][$parameters['tutorial']]['index']);
	}
	
	public function testGet(){
		$parameters = array(
				'tutorial'=>'unittest'
		);
		$result = $this->invokeService('tutorial.tutorial.get', $parameters);
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertKeyExists('uid', $result['data']);
		XAssert::assertEquals($parameters['tutorial'], $result['data']['uid']);
	}
	
	public function testComplete(){
		$parameters = array(
				'tutorial'=>'unittest'
		);
		$result = $this->invokeService('tutorial.tutorial.complete', $parameters);
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertKeyExists($parameters['tutorial'], $result['data']);
		XAssert::assertKeyExists('uid', $result['data'][$parameters['tutorial']]);
		XAssert::assertKeyExists('completed', $result['data'][$parameters['tutorial']]);
		XAssert::assertEquals($parameters['tutorial'], $result['data'][$parameters['tutorial']]['uid']);
		XAssert::assertTrue($result['data'][$parameters['tutorial']]['completed']);
	}
}
?>