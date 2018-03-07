<?php
/**
 * Passport
 * 
 * user passport interface
 * 
 * user账户接口
 * 
 * @author WangQi
 * @interface
 * @package user
 */
interface Passport{
	/**
	 * username getter
	 * 
	 * 获取username属性
	 * 
	 * @return string
	 */
	public function getUsername();
	/**
	 * username setter
	 * 
	 * 设置username属性
	 * 
	 * @param string $username
	 */
	public function setUsername($username);
	/**
	 * password getter
	 * 
	 * 获取password属性
	 * 
	 * @return string
	 */
	public function getPassword();
	/**
	 * password setter
	 * 
	 * 设置password属性
	 * 
	 * @param string $password
	 */
	public function setPassword($password);
	/**
	 * nickname getter
	 * 
	 * 获取nickname属性
	 * 
	 * @return string
	 */
	public function getNickname();
	/**
	 * nickname setter
	 * 
	 * 设置nickname属性
	 * 
	 * @param string $nickname
	 */
	public function setNickname($nickname);
	/**
	 * email getter
	 * 
	 * 获取email属性
	 * 
	 * @return string
	 */
	public function getEmail();
	/**
	 * email setter
	 * 
	 * 设置email属性
	 * 
	 * @param string $email
	 */
	public function setEmail($email);
	/**
	 * userUid getter
	 * 
	 * 获取userUid属性
	 * 
	 * @return string
	 */
	public function getUserUid();
	/**
	 * userUid setter
	 * 
	 * 设置userUid属性
	 * 
	 * @param string $userUid
	 */
	public function setUserUid($userUid);
}