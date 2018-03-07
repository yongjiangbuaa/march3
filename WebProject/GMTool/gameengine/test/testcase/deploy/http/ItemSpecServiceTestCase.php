<?php
class ItemSpecServiceTestCase extends XRESTServiceTestCase{
	protected $initUserUid = FALSE;
	protected $requestMethod = 0;
	
	public function testGetAll(){
		$this->setProtocol('file');
		$result = $this->invokeService('item.itemspec.xml', array('lang'=>'cn', 'version'=>1), FALSE);
		XAssert::assertNotEmpty($result);
	}
}
?>