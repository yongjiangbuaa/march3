<?php
/**
 * XActionRequest
 * 
 * action request class, extends XAbstractRequest class
 * 
 * 包装action请求信息的request类，继承XAbstractRequest抽象类
 * 
 * @author Tianwei
 * @package action 
 */
class XActionRequest extends XAbstractRequest{	
	protected $serviceRequest = null;
	protected $actionName = null;
	protected $context;
	protected $user = null;

	/** 
	 * <b>serviceRequest getter</b>
	 * 
	 * <b>获取serviceRequest属性的方法</b>
	 * 
	 * @return XServiceRequest
	 */	
	function getServiceRequest(){
		return $this->serviceRequest;
	}
	
	/** 
	 * <b>serviceRequest setter</b>
	 * 
	 * <b>设置serviceRequest属性的方法</b>
	 * 
	 * @param XServiceRequest $serviceRequest
	 * @return XActionRequest
	 */	
	function setServiceRequest(XServiceRequest $serviceRequest){
		$this->serviceRequest = $serviceRequest;
		return $this;
	}
	
	/**
	 * <b>actionName getter</b>
	 * 
	 * <b>获取actionName属性的方法</b>
	 * 
	 * @return string
	 */
	public function getActionName(){
		return $this->actionName;
	}	
	
	/** 
	 * <b>$actionName setter</b>
	 * 
	 * <b>设置$actionName属性的方法</b>
	 * 
	 * @param string $actionName
	 * @return XActionRequest
	 */	
	public function setActionName($actionName){
		$this->actionName = $actionName;
		return $this;
	}
	
	/**
	 * <b>get gameUserId from request parameter info</b>
	 * 
	 * <b>从请求参数中info字段中获取gameUserId</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	public function getUserUID(){
		return $this->getGameUserId();
	}
	
	/**
	 * <b>get gameUserId from request parameter info</b>
	 * 
	 * <b>从请求参数中info字段中获取gameUserId</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	public function getGameUserId(){
		return $this->serviceRequest->getGameUserId();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRequest::getUser()
	 */
	public function getUser(){
		if($this->user){
			return $this->user;
		}
		$this->user = $this->serviceRequest->getUser();
		return $this->user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRequest::setUser()
	 */
	public function setUser($user){
		$this->user = $user;
		return $this;
	}
}
?>