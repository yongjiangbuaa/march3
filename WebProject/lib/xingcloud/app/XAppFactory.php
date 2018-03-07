<?php
/**
 * 获得App类的工厂方法
 */
require_once "XApp.php"; 
class XAppFactory {
	private static $app;
	public static function getApp() {
		if (!isset ($app)) {
			$appConfig = XRuntime :: singleton()->getAppConfig();
			$config = $appConfig->getConfig();
			$appInfo = array (
				'appId' => $config['instance_id']
			);
			$app = new XApp($appInfo, $appConfig);
		}
		return $app;
	}
}
?>
