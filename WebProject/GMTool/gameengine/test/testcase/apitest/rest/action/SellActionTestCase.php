<?php
class SellActionTestCase extends XRESTActionTestCase{
	function testExecute(){
		$this->addAction('sell', array('uid'=>'11111111'));
		$result = $this->invokeAction();
		$this->assertActionResult($result);
	}
	
}

?>