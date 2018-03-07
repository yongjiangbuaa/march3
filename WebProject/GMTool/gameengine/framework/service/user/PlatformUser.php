<?php
/**
 * PlatformUser
 * 
 * PlatformUser interface
 * 
 * PlatformUser接口
 * 
 * @author Tianwei
 * @interface
 * @package user
 */
interface PlatformUser{
	/** 
	 * <b>userUID getter</b>
	 * 
	 * <b>获取userUID属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getUserUID();
	/** 
	 * <b>platformAddress getter</b>
	 * 
	 * <b>获取platformAddress属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getPlatformAddress();
}
?>