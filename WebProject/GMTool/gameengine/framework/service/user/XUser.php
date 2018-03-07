<?php
/**
 * XUser
 * 
 * Xingcloud user interface
 * 
 * 行云的user接口
 * 
 * @author Tianwei
 * @interface
 * @package user
 */
import('module.context.user.User');
interface XUser extends User{
	/**
	 * <b>this mothod will be invoked when the user logged in</b>
	 * 
	 * <b>用户登陆时会调用此方法</b>
	 */
	public function onLogin();
	/**
	 * <b>this mothod will be invoked when the user registered</b>
	 * 
	 * <b>用户注册时会调用此方法</b>
	 */
	public function onRegister();
	/**
	 * <b>this mothod will be invoked when user profile is loaded</b>
	 * 
	 * <b>加载用户档案时会调用此方法</b>
	 */
	public function onLoad();
}
?>