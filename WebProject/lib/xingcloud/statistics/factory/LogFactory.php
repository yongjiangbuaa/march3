<?php
interface LogPersist{
	function sendLog($log);
}

class LogFactory{
	
	/**
	 * get logger instance
	 * 
	 */
	static public function getInstance($type){
		if($type=='http') {
			require_once XINGCLOUD_CLOUD_DIR.'/statistics/factory/HttpPersist.php';
			return HttpPersist::getInstance();
		}	
		if($type=='local') {
			require_once XINGCLOUD_CLOUD_DIR.'/statistics/factory/LocalPersist.php';
			return LocalPersist::getInstance();
		}
		return null;
	}
	
}