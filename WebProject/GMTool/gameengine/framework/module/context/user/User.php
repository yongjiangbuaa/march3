<?php
/**
 * User
 * 
 * basic user interface
 * 
 * user接口 
 * 
 * @author Wangqi
 * @interface
 * @package context.user
 */
interface User{
	/** 
	 * <b>uid getter</b>
	 * 
	 * <b>获取uid属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getUid();
	/**
	 * <b>uid setter</b>
	 * 
	 * <b>设置uid属性的方法</b>
	 * 
	 * @param string $uid
	 */
    public function setUid($uid);
	/** 
	 * <b>platformAddress getter</b>
	 * 
	 * <b>获取platformAddress属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getPlatformAddress();
	/**
	 * <b>platformAddress setter</b>
	 * 
	 * <b>设置platformAddress属性的方法</b>
	 * 
	 * @param string $platformAddress
	 */
    public function setPlatformAddress($platformAddress);
}
?>