<?php
/**
 * XAuthentication
 * 
 * authentication interface
 * 验证接口
 * @author Tianwei
 * @interface
 * @package security 
 */
interface XAuthentication{
	/** 
	 * get authentication details
	 * 
	 * 获取验证详细资料
	 * 
	 * @return array
	 */	
	public function getDetails();
	
	/**
	 * get authentication principal
	 * 
	 * 获得验证主体
	 * 
	 * @return XServiceRequest
	 */
	public function getPrincipal();
}
?>