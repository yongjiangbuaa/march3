<?php
class SellActionTestCase extends XJSONRPCActionTestCase{
	function testExecute(){
		$this->addAction('SellAction', array('uid'=>'11111111'));
		$result = $this->invokeAction();
		$this->assertActionResult($result);
	}
}

?>