<?php
class BuyActionTestCase extends BaseActionTestCase{
	
	function testExecute(){
		$this->addAction('BuyAction', array('itemId'=>1));
		$this->addAction('BuyAction', array('itemId'=>2));
		$result = $this->executeAction();
		$this->assertActionResult($result);
	}
	
}
?>