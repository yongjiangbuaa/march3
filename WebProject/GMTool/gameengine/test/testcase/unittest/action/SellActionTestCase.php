<?php
class SellActionTestCase extends BaseActionTestCase{
	
	function testExecute(){
		$this->addAction('SellAction', array('uid'=>'11111111'));
		$result = $this->executeAction();
		$this->assertActionResult($result);
	}
	
}
?>