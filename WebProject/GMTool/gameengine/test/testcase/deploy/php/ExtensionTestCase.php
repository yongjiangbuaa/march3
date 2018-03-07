<?php
class ExtensionTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	protected $enableService=FALSE;

	public function testRequired(){
		if(!extension_loaded('apc')){
			XAssert::fail('apc is not enabled');
		}
		if(!extension_loaded('json')){
			XAssert::fail('json extension is not enabled');
		}
		if(!extension_loaded('SimpleXML')){
			XAssert::fail('SimpleXML extension is not enabled');
		}
	}
}
?>