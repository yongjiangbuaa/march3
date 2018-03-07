<?php
class BuyActionTestCase extends XRESTActionTestCase{
	
	function testExecute(){
		$this->addAction('BuyAction',  array('itemId'=>1));
		$result = $this->invokeAction();
		$this->assertActionResult($result);
	}
	
}


?>