<?php
class FrameworkTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	protected $enableService=FALSE;

	public function testConfig(){
		if(!is_file(XINGCLOUD_GAMEENGINE_DIR.'/index.php')){
			XAssert::fail('index.php not exists');
		}
		$contents = file(XINGCLOUD_GAMEENGINE_DIR.'/index.php');
		$isDev = true;
		$usePhar = true;
		foreach ($contents as $line){
			if(strpos($line, '__DEV__')){
				if(preg_match('/define\s*\(\s*"__DEV__"\s*,\s*false\s*\);/i', $line)){
					$isDev = false;
				}
			}
			if(strpos($line, '__PHAR__')){
				if(preg_match('/define\s*\(\s*"__PHAR__"\s*,\s*false\s*\);/i', $line)){
					$usePhar = false;
				}
			}
		}
		if($isDev){
			XAssert::fail('__DEV__ is not false');
		}
		if($usePhar){
			XAssert::fail('__PHAR__ is not false');
		}
		$persistenceConfig = XConfig::singleton()->get('persistence');
		if(array_key_exists('mysql', $persistenceConfig)){
			XAssert::fail('mysql config found in game/config/persistence.php');
		}
		if(array_key_exists('data_proxy', $persistenceConfig)){
			if(!isset($persistenceConfig['data_proxy']['instance_id']) || !isset($persistenceConfig['data_proxy']['location'])){
				XAssert::fail('incorrect data_proxy configuration');
			}
		}
	}
}
?>