<?php
import('service.action.XAction');
import('module.util.lock.XLockable');
import('module.cache.XCacheable');
/**
 * XAbstractAction
 * 
 * abstract action class
 * 
 * action抽象类，实现XAction, XLockable, XCacheable接口
 * 执行Action的入口是doExecute()方法
 * 所有Action都应该是XAbstractAction的子类
 * 
 * @author Tianwei
 * @abstract
 * @package action
 */
abstract class XAbstractAction implements XAction, XLockable, XCacheable{
	protected $user = null;
	
	/** 
	 * <b>user getter</b>
	 * 
	 * <b>获取user属性的方法</b>
	 * 
	 * @return UserProfile
	 */	
	public function getUser(){
		return $this->user;
	}
	
	/** 
	 * <b>user setter</b>
	 * 
	 * <b>设置user属性的方法</b>
	 * 
	 * @param UserProfile $user
	 * @return XAction
	 */	
	public function setUser($user){
		$this->user = $user;
		return $this;
	}
	
	/**
	 * <b>execute an action, will invoke the execute() method of the action</b>
	 * 
	 * <b>执行一个action的入口，会调用该action的execute()方法</b>
	 * 调用execute()方法之前会调用预处理方法beforeExecuted($request)
	 * Action处理完成后会调用后处理方法afterExecuted($result)
	 * 
	 * @Lock(policy='retrieve')
	 * @param XActionRequest $request
	 * @return XServiceResult
	 */
	public function doExecute(XActionRequest $request){
		$this->beforeExecuted($request);
		$result = $this->execute($request);
		$this->afterExecuted($result);
		return $result;
	}
	
	/** 
	 * <b>get lockID of the action</b>
	 * 
	 * <b>获取action的lockID</b>
	 * 
	 * @return string
	 */	
	public function getLockId(){
		return 'action_lock_'.get_class($this).'_'.@$this->getUser()->get('uid');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XLockable::onLockConflicted()
	 */
	public function onLockConflicted(XLocker $locker){
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see XLockable::onLockReleased()
	 */
	public function onLockReleased(XLocker $locker){
	}
	
	/**
	 * <b>logic code that the action will execute</b>
	 * 
	 * <b>action具体执行的逻辑代码</b>
	 * 
	 * @param XActionRequest $request
	 */
	protected function execute(XActionRequest $request){
	}
	
	/**
	 * <b>before executed</b>
	 * 
	 * <b>execute()之前将会执行的代码</b>
	 * 
	 * @param XActionRequest $request
	 */
	protected function beforeExecuted(XActionRequest $request){
	}
	
	/**
	 * <b>after executed</b>
	 * 
	 * <b>execute()之后将会执行的代码</b>
	 * 
	 * @param XServiceResult $result
	 */
	protected function afterExecuted($result){
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XCacheable::getCacheKey()
	 */
	public function getCacheKey($method, $args = array(), $config = array()){
		return substr(SERVER_UID, 0, 5).'_'.get_class($this).'_'.$method.'_'.@$this->getUser()->getUID();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XCacheable::onCacheUpdated()
	 */
	public function onCacheUpdated($cacheKey, $cacheValue){
		
	}
}
?>