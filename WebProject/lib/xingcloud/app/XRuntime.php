<?php
define("__ISCLOUD__", false);
/**
 * runtime类，获得环境以及配置信息
 */
require_once "XAppConfig.php"; 
final class XRuntime {
	private static $instance = null;
	private static $appconfig = null;

	private function __construct() {
	}

	/** 
	 * 
	 * 取得Runtime类单例的方法
	 * 
	 * @return XRuntime
	 */
	public static function singleton() {
		if (!self :: $instance) {
			self :: $instance = new self;
		}
		return self :: $instance;
	}

	public function isCloud() {
		return __ISCLOUD__; 
	}

	public function getAppConfig() {
		if (!isset ($appconfig)) {
			XAppConfig::singleton()->_loadFile();
			XAppConfig::singleton()->_loadYAML();
			$appconfig = XAppConfig::singleton();
		}
		return $appconfig;
	}
}
?>