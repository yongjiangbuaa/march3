<?php
/**
 * UserRepository
 * 
 * user repository interface
 * 
 * UserRepository接口
 * 
 * @author Wangqi
 * @interface
 * @package context.user
 */
interface UserRepository{
	/**
	 * get user instance by the given uid
	 * 
	 * <b>根据给定的uid对用户信息进行查询，并返回用户实例</b>
	 * 
	 * @return User
	 * @throws XException
	 */
	public function get($uid);
	/**
	 * put user instance to the repository
	 * 
	 * 将用户对象放入容器
	 * 
	 * @param User $user
	 */
    public function put($user);
}
?>