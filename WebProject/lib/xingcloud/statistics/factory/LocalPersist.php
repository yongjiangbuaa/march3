<?php
class LocalPersist implements LogPersist{
	
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回LocalPersist实例，每个类对应的实例有且仅有一个
	 * 
	 * @return LocalPersist
	 */
	private static $instance = null;
	static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * save log into local file
	 */
	public function sendLog($log){
		require_once XINGCLOUD_CLOUD_DIR.'/statistics/log4php/Logger.php';
		Logger::configure(XINGCLOUD_CLOUD_DIR.'/statistics/log4phpConfig.php');
		$logger = Logger::getLogger("default");
		$logger->info($log);
	}
	
	
}