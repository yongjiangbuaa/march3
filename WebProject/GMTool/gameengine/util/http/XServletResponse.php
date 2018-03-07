<?php
/**
 * XServletResponse
 *
 * servlet response tool
 *
 * servlet respons工具
 *
 * @author Tianwei
 * @package util.http
 */
class XServletResponse{
	private static $instance = null;
	
	private function __construct(){
		
	}
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServletResponse
	 */		
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * redirect the client page to the specified url
	 * 
	 * 将客户端页面重定向到指定的url
	 * 
	 * @param string $url
	 */
	public function redirect($url = null){
		$location = preg_replace("/^[\\|\/]+/i", '/', $url);
		if(empty($url) || $url == '/'){
			$location = '/';
		}
		if(!headers_sent()){		
			header('location:'.$location);
			exit();
		}
	}
}
?>