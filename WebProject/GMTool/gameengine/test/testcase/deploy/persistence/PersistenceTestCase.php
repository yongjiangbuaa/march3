<?php
class PersistenceTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	protected $enableService=FALSE;
	protected $testObj = NULL;
	protected $testObjUid = '';

	public function testWrite(){
		$testObj = new DeployTestObject();
		$testObj->setOwnerId($this->platformUserId);
		$testObj->save();
		$this->testObj = $testObj;
		$this->testObjUid = $testObj->getUid();
		try {
			PersistenceSession::singleton()->flush();
		}catch (Exception $e){
			XAssert::fail('db write exception. message: '.$e->getMessage());
		}
	}
	
	public function testRead(){
		try{
			$testObj = PersistenceSession::singleton()->get('DeployTestObject', $this->testObjUid);
		}catch (Exception $e){
			XAssert::fail('db read exception. message: '.$e->getMessage());
		}
		if(!($testObj instanceof DeployTestObject) || $testObj->getUid()!=$this->testObjUid){
			XAssert::fail('read test object failed.');
		}
	}
	
	public function testRemove(){
		try{
			PersistenceSession::singleton()->remove($this->testObj);
		}catch (Exception $e){
			XAssert::fail('db remove exception. message: '.$e->getMessage());
		}
	}
}

class DeployTestObject extends OwnedItem{
	
}
?>