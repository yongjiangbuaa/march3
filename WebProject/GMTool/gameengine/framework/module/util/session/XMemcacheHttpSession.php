<?php
import('module.util.session.XAbstractSession');
/**
 * XMemcacheHttpSession
 * 
 * Memcached HTTP Session
 * 
 * 基于memcache的分布式HTTP Session
 * 
 * @author wangqi
 * @package util.session
 */
class XMemcacheHttpSession extends XAbstractSession{
	protected $sessionName = 'XINGCLOUDSESSION';
	protected $cacheAccess = null;
	protected $ttl = 3600;
	protected $saveImmediately = true;
	protected $saved = false;
	
	/**
	 * (non-PHPdoc)
	 * @see XSession::start()
	 */
	public function start(){
		if($this->started){
			return TRUE;
		}
		$this->cacheAccess = CacheServiceFactory::getCacheAccess();
		if(empty($this->cacheAccess)){
			return false;
		}
		//get sessionID from cookie
		if(empty($this->sessionID) && isset($_COOKIE[$this->sessionName])){
			$this->sessionID = $_COOKIE[$this->sessionName];
		}
		//no session id? generate session id
		if(empty($this->sessionID)){
			$this->sessionID = $this->generateSessionID();
		}else{
			//get session data from memcache
			$this->session = $this->cacheAccess->get($this->storageKey());
		}
		if(empty($this->session)){
			$this->session = array();
			$this->storeSessionInCookie();
		}
		$this->started = TRUE;
		return true;
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::get()
	 */
	public function get($key){
		if(empty($this->session)){
			return NULL;
		}
		if(array_key_exists($key, $this->session)){
			return $this->session[$key];
		}
		return null;
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::getAll()
	 */
	public function getAll(){
		if(empty($this->session)){
			return NULL;
		}
		return $this->session;
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::set()
	 */
	public function set($key, $value){
		if(!$this->started){
			return false;
		}
		$this->session[$key] = $value;
		if($this->saveImmediately){
			$this->save();
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::clear()
	 */
	public function clear(){
		$this->session = array();
		if($this->saveImmediately){
			$this->save();
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractSession::destroy()
	 */
	public function destroy(){
		if(!$this->started){
			return false;
		}
		$this->sessionID = NULL;
		$this->cacheAccess->remove($this->storageKey());
		$this->session = array();
		$this->started = false;
		$this->saved = false;
		if(isset($_COOKIE[$this->sessionName])){
			unset($_COOKIE[$this->sessionName]);
		}
		//delete cookies
		setcookie($this->sessionName, '', 1305000000);
	}
	/**
	 * set session expire time
	 * 
	 * 设置session数据过期时间
	 * 
	 * @param int $ttl
	 */
	public function setTtl($ttl){
		$this->ttl = $ttl;
	}
	/**
	 * set session expire time
	 * 
	 * 设置session数据过期时间
	 * 
	 * @param int $ttl
	 */
	public function setSaveImmediately($saveImmediately){
		$this->saveImmediatly = $saveImmediately;
	}
	/**
	 * save session data to memcache
	 * 
	 * 将session数据存入到memcache中
	 */
	public function save(){
		if($this->started){
			$this->cacheAccess->put($this->storageKey(), $this->session, $this->ttl);
			$this->saved = true;
		}
	}
	private function storageKey(){
		return $this->sessionName.'_'.$this->sessionID;
	}
}