<?php
class TutorialServiceTestCase extends BaseServiceTestCase{
	
	public function testStep(){
		$this->param['tutorial'] = 'unittest';
		$this->param['name'] = 'unittest';
		$this->param['index'] = 1;
		$this->setRequestParam('tutorial.tutorial.step', array('tutorial'=>$this->param['tutorial'], 'name'=>$this->param['name'], 'index'=>$this->param['index']));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'uid');
		XAssert::assertPropertyExists($result->data, 'className');
		XAssert::assertPropertyExists($result->data, 'items');
		XAssert::assertEquals(strlen($result->data->uid)>0, true);
		XAssert::assertEquals('Tutorial', $result->data->className);
		XAssert::assertKeyExists($this->param['tutorial'], $result->data->items);
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'tutorial');
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'name');
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'index');
		XAssert::assertEquals($this->param['tutorial'], $result->data->items[$this->param['tutorial']]->tutorial);
		XAssert::assertEquals($this->param['name'], $result->data->items[$this->param['tutorial']]->name);
		XAssert::assertEquals($this->param['index'], $result->data->items[$this->param['tutorial']]->index);
		
		$this->setRequestParam('tutorial.tutorial.step', array('tutorial'=>$this->param['tutorial'], 'name'=>$this->param['name'], 'index'=>$this->param['index']+1));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'uid');
		XAssert::assertPropertyExists($result->data, 'items');
		XAssert::assertKeyExists($this->param['tutorial'], $result->data->items);
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'tutorial');
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'name');
		XAssert::assertPropertyExists($result->data->items[$this->param['tutorial']], 'index');
		XAssert::assertEquals(strlen($result->data->uid)>0, true);
		XAssert::assertEquals($result->data->items[$this->param['tutorial']]->tutorial, $this->param['tutorial']);
		XAssert::assertEquals($result->data->items[$this->param['tutorial']]->name, $this->param['name']);
		XAssert::assertEquals($result->data->items[$this->param['tutorial']]->index, $this->param['index']+1);
	}
	
	public function testGet(){
		$this->setRequestParam('tutorial.tutorial.get', array('tutorial'=>$this->param['tutorial']));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertEquals(strlen($result->data->uid)>0, true);
	}
	
	public function testComplete(){
		$this->setRequestParam('tutorial.tutorial.complete', array('tutorial'=>$this->param['tutorial']));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertEquals(strlen($result->data->uid)>0, true);
	}
	
}


?>