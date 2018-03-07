<?php
class UserServiceTestCase extends XJSONRPCServiceTestCase{
	
	public function testRegister(){
		$result = $this->invokeService('user.user.register', array('account'=>array('username'=>$this->platformUserId, 'password'=>$this->platformUserId)));
		$this->assertServiceResult($result);
	}
	
	public function testLogin(){
		$result = $this->invokeService('user.user.login', array('username'=>$this->platformUserId, 'password'=>$this->platformUserId));
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertArray($result['data']);
		XAssert::assertKeyExists('uid', $result['data']);
		XAssert::assertKeyExists('username', $result['data']);
		XAssert::assertEquals(true, strlen($result['data']['uid'])>0);
		XAssert::assertEquals($this->platformUserId, $result['data']['username']);
	}
	
	public function testPlatformRegister(){
		$this->setUserPlatformInfo($this->platformAppId, $this->platformAppId.$this->platformUserId);
		$result = $this->invokeService('user.user.platformRegister', array());
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertArray($result['data']);
		XAssert::assertKeyExists('uid', $result['data']);
		XAssert::assertKeyExists('platformAddress', $result['data']);
		XAssert::assertEquals(true, strlen($result['data']['uid'])>0);
		XAssert::assertEquals($this->platformUserId.'_'.$this->platformAppId, $result['data']['platformAddress']);
		$this->setGameUserId($result['data']['uid']);
	}
	
	public function testPlatformLogin(){
		$result = $this->invokeService('user.user.platformLogin', array());
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertArray($result['data']);
		XAssert::assertKeyExists('uid', $result['data']);
		XAssert::assertKeyExists('platformAddress', $result['data']);
		XAssert::assertEquals(true, strlen($result['data']['uid'])>0);
		XAssert::assertEquals($this->platformUserId.'_'.$this->platformAppId, $result['data']['platformAddress']);
	}
	
	public function testGet(){
		$result = $this->invokeService('user.user.get', array(array('gameUserId'=>$this->gameUserId)));
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertArray($result['data']);
		XAssert::assertKeyExists(0, $result['data']);
		$user = $result['data'][0];
		XAssert::assertKeyExists('uid', $user);
		XAssert::assertKeyExists('platformAddress', $user);
		XAssert::assertEquals($this->user_uid, $user['uid']);
		XAssert::assertEquals($this->platformUserId.'_'.$this->platformAppId, $user['platformAddress']);
	}
	
	public function testGetItems(){
		$result = $this->invokeService('user.user.getItems', array('property'=>'ownedItems'));
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertArray($result['data']);
	}
}


?>