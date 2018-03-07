<?php
class LogTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	protected $enableService=FALSE;

	public function testWritable(){
		$xfile = new XFile(GAME_LOG_DIR);
		if(!$xfile->exists()){
			XAssert::fail('log directory not exists');
		}
		if(!$xfile->isDirectory()){
			XAssert::fail(GAME_LOG_DIR.' is not a directory');
		}
		if(!$xfile->canWrite()){
			XAssert::fail('can not write sdk log');
		}
	}
}
?>