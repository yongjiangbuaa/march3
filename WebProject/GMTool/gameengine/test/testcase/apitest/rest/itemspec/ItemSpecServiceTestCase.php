<?php
class ItemSpecServiceTestCase extends XRESTServiceTestCase{
	protected $initUserUid = FALSE;
	
	public function testXml(){
		$this->setRequestMethod('get');
		$this->setProtocol('file');
		$result = $this->invokeService('item.itemspec.xml', array('version'=>1), FALSE);
		XAssert::assertNotEmpty($result);
	}
}


?>