<?php
/**
 * XHttpSession
 * 
 * HTTP Session Inteface
 * 
 * HTTP Session接口
 * 
 * @author wangqi
 * @interface
 * @package util.session
 */
interface XHttpSession{
	/**
	 * start the http session
	 * 
	 * 启动http session
	 * 
	 */
	public function start();
	/**
	 * get session id
	 * 
	 * 获取session id
	 * 
	 * @return string
	 */
	public function getSessionID();
	/**
	 * set session id
	 * 
	 * 设置session id
	 * 
	 * @param string $sid
	 */
	public function setSessionID($sid);
	/**
	 * get a value from session by the specified key
	 * 
	 * 从session中获取指定键值的值
	 * 
	 * @return mixed
	 */
	public function get($key);
	/**
	 * get all data from the session
	 * 
	 * 获取session中的所有数据
	 * 
	 * @return array()
	 */
	public function getAll();
	/**
	 * set a $value in the session
	 * 
	 * 在session中存储一个值
	 * 
	 * @param $string $key
	 * @param mixed $value
	 */
	public function set($key, $value);
	/**
	 * set a $value in the session
	 * 
	 * 在session中存储一个值
	 * 
	 * @param $string $key
	 * @param mixed $value
	 */
	public function add($key, $value);
	/**
	 * clear all data in the session
	 * 
	 * 清除session中所有数据
	 */
	public function clear();
	/**
	 * destory the session
	 * 
	 * 销毁session
	 */
	public function destroy();
}
?>