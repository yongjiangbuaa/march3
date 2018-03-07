<?php
class XSmarty {
	private static $instance;

	static function &load() {
		if (!self::$instance) {
			require_once dirname(__FILE__).'/Smarty.class.php';
			self::$instance = new Smarty();
		}
		return self::$instance;
	}
}
?>