<?php
require_once XINGCLOUD_CLOUD_DIR.'/statistics/factory/LogFactory.php';
class Statistics{
	static public function sendLog($log){
		if(defined('XA_LOCAL_TYPE')&& XA_LOCAL_TYPE=='http'){
			$type='http';
		}
		else $type='local';
		$logger=LogFactory::getInstance($type);
		if($logger!=null) {
			$logger->sendLog($log);
			return true;
		}
		else return false;		
	}
}