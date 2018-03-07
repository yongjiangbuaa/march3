<?php
/**
 * UserAware
 * 
 * user aware interface
 * 
 * UserAware接口，任何需要注入游戏用户对象的类都可以实现该接口
 * 
 * @author Wangqi
 * @interface
 * @package context.user
 */
interface UserAware{
	/** 
	 * user getter
	 * 
	 * 获得游戏用户对象
	 * 
	 * @return User
	 */	
	public function getUser();
	/**
	 * user setter
	 * 
	 * 注入游戏用户对象
	 * 
	 * @param User $user
	 */
    public function setUser($user);
}
?>