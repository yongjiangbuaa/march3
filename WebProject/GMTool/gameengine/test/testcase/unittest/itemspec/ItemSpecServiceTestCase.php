<?php
class ItemSpecServiceTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	
	public function testXml(){
		$this->setRequestParam('item.itemspec.xml', array('version'=>1));
		$result = $this->executeService();
		XAssert::assertNotEmpty($result);
		XAssert::assertEquals($result->fileName, 'item.xml');
	}
}


?>