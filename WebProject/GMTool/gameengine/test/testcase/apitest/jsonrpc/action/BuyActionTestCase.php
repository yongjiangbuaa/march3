<?php
class BuyActionTestCase extends XJSONRPCActionTestCase{
	function testExecute(){
		$this->addAction('BuyAction',  array('itemId'=>1));
		$result = $this->invokeAction();
		$this->assertActionResult($result);
	}
	
}


?>