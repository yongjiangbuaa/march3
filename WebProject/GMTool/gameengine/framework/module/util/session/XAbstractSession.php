<?php
import('module.util.session.XHttpSession');
import('module.context.user.UserAware');
/**
 * XAbstractSession
 * 
 * abstract HTTP Session
 * 
 * HTTP Session抽象类
 * 
 * @author wangqi
 * @package util.session
 */
class XAbstractSession implements XHttpSession, UserAware{
	protected $sessionName = '';
	protected $sessionID = NULL;
	protected $session = NULL;
	protected $started = FALSE;
	protected $request = NULL;
	protected $user = NULL;
	
	/**
	 * (non-PHPdoc)
	 * @see XSession::start()
	 */
	public function start(){
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::getSessionID()
	 */
	public function getSessionID(){
		return $this->sessionID;
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::setSessionID()
	 */
	public function setSessionID($sid){
		$this->sessionID = $sid;
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::get()
	 */
	public function get($key){
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::getAll()
	 */
	public function getAll(){
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::set()
	 */
	public function set($key, $value){
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::add()
	 */
	public function add($key, $value){
		$this->set($key, $value);
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::clear()
	 */
	public function clear(){
	}
	/**
	 * (non-PHPdoc)
	 * @see XSession::destroy()
	 */
	public function destroy(){
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::storeSessionInCookie()
	 */
	public function storeSessionInCookie($expire = 2592000, $path = null, $domain = null, $secure = null, $httponly = null){
		$expire = $_SERVER['REQUEST_TIME']+$expire;
		setcookie($this->sessionName, $this->sessionID, $expire, $path, $domain, $secure, $httponly);
	}
	/**
	 * generate session id
	 * 
	 * 生成session id
	 * 
	 * @return string
	 */
	protected function generateSessionID(){
		return md5(uniqid(microtime(true)).$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
	}
	/**
	 * request getter
	 * 
	 * 获取request属性的方法
	 * 
	 * @return XRequest
	 */
	public function getRequest(){
		return $this->request;
	}
	/**
	 * request setter
	 * 
	 * 设置request属性的方法
	 * 
	 * @param XRequest $request
	 */
	public function setRequest($request){
		$this->request = $request;
	}
	/**
	 * user getter
	 * 
	 * 获取user属性的方法
	 * 
	 * @return XUser
	 */
	public function getUser(){
		return $this->user;
	}
	/**
	 * user setter
	 * 
	 * 设置user属性的方法
	 * 
	 * @param XUser $user
	 */
	public function setUser($user){
		$this->user = $user;
	}
}

?>