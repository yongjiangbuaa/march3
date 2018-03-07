<?php
/**
 * 用户通过XAppFactory获得App
 * App中可以过得AppInfo和AppConfig
 * AppInfo目前有instanceid
 * AppConfig包含项目的配置信息
 */
class XApp {
	private $appInfo = array ();
	private $appConfig = array ();

	public function __construct($appInfo, $appConfig) {
		$this->appInfo = $appInfo;
		$this->appConfig = $appConfig;
	}

	public function getAppInfo() {
		return $this->appInfo;
	}

	public function getAppConfig() {
		return $this->appConfig;
	}
}
?>
