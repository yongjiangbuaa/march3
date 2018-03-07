<?php
class UserServiceTestCase extends BaseServiceTestCase{
	
	public function testRegister(){
		$this->setUserPlatformInfo($this->platformAppId, $this->platformAppId.$this->platformUserId);
		$this->setRequestParam('user.user.register', array('account'=>array('username'=>$this->platformUserId, 'password'=>$this->platformUserId)));
		$result = $this->executeService();
		$this->assertServiceResult($result);
	}
	
	public function testLogin(){
		$this->setRequestParam('user.user.login', array('username'=>$this->platformUserId, 'password'=>$this->platformUserId));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'uid');
		XAssert::assertPropertyExists($result->data, 'username');
		XAssert::assertEquals(TRUE, strlen($result->data->uid)>0);
		XAssert::assertEquals($this->platformUserId, $result->data->username);
	}
	
	public function testPlatformRegister(){
		$this->setUserPlatformInfo($this->platformAppId, $this->platformAppId.$this->platformUserId);
		$this->setRequestParam('user.user.platformRegister', array());
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'uid');
		XAssert::assertPropertyExists($result->data, 'platformAddress');
		XAssert::assertEquals(TRUE, strlen($result->data->uid)>0);
		XAssert::assertEquals($this->platformUserId.'_'.$this->platformAppId, $result->data->platformAddress);
		$this->setGameUserId($result->data->uid);
	}
	
	public function testPlatformLogin(){
		$this->setRequestParam('user.user.platformLogin', array());
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'uid');
		XAssert::assertPropertyExists($result->data, 'platformAddress');
		$this->user_uid = $result->data->uid;
		XAssert::assertEquals(TRUE, strlen($result->data->uid)>0);
		XAssert::assertEquals($this->platformUserId.'_'.$this->platformAppId, $result->data->platformAddress);
	}
	
	public function testGet(){
		$this->setRequestParam('user.user.get', array(array('gameUserId'=>$this->gameUserId)));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertArray($result->data);
		XAssert::assertKeyExists(0, $result->data);
		$data = $result->data[0];
		XAssert::assertPropertyExists($data, 'uid');
		XAssert::assertPropertyExists($data, 'platformAddress');
		XAssert::assertEquals(strlen($data->uid)>0, true);
		XAssert::assertEquals($data->platformAddress, $this->platformUserId.'_'.$this->platformAppId);
	}
	
	public function testGetItems(){
		$this->setRequestParam('user.user.getItems', array());
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertPropertyExists($result->data, 'className');
		XAssert::assertEquals($result->data->className, 'OwnedItemCollection');
	}
	
}

?>