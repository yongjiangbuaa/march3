<?php
import('module.service.XServiceContext');
import('module.request.XAbstractRequest');
import('module.security.XAuthentication');
/**
 * XServiceRequest
 * 
 * service request class, extends XAbstractRequest class and implements XAuthentication interface
 * 
 * 包装service的request类，继承XAbstractRequest抽象类并实现XAuthentication接口
 * 
 * @author Tianwei
 * @package service 
 */
class XServiceRequest extends XAbstractRequest implements XAuthentication{	
	private static $instance = null;
	protected $servletRequest = null;
	protected $classPath;
	protected $className;
	protected $methodName;
	protected $context;
	protected $id = NULL;
	protected $info = array();
	protected $paramValidated = FALSE;

	/**
	 * <b>construct method</b>
	 * 
	 * <b>构造方法</b>
	 */
	private function __construct(){
	
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServiceRequest
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * get id in request parameters
	 * 
	 * 获取请求参数中的id字段，请求参数格式为：{"id":"","info":"","data":""}
	 * 
	 * @return string
	 */
	function getId(){
		return $this->getParameter('id');
	}
	
	/**
	 * get info in request parameters
	 * 
	 * 获取请求参数中的info字段，请求参数格式为：{"id":"","info":"","data":""}
	 * 
	 * @return string
	 */
	function getInfo(){
		return $this->getParameter('info');
	}

	/**
	 * get data in request parameters
	 * 
	 * 获取请求参数中的data字段，请求参数格式为：{"id":"","info":"","data":""}
	 * 
	 * @return string
	 */
	function getData(){
		if(!$this->paramValidated){
			$this->validateParams();
		}
		return $this->getParameter('data');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRequest::getParameter()
	 */
	function getParameter($key){
		if(!is_array($this->parameters)){
			return null;
		}elseif(array_key_exists('data', $this->parameters) && is_array($this->parameters['data']) && array_key_exists($key, $this->parameters['data'])){
			return $this->parameters['data'][$key];
		}elseif(array_key_exists($key, $this->parameters)){
			return $this->parameters[$key];
		}else{
			return null;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractRequest::setParameters()
	 */
	function setParameters($parameters){
		$this->parameters = $parameters;
		return $this;
	}
	
	/**
	 * validata the format of request parameters
	 * 
	 * 验证请求参数的格式，格式应为{"id":"","info":"","data":""}
	 * 
	 * info字段中应包含{"platformAppId":"","platformUserId":""}
	 * 
	 * @throws XException
	 */
	function validateParams(){
		$inputParams = $this->parameters;
		if(!isset($inputParams['id']) || !isset($inputParams['info']) || !isset($inputParams['data'])){
			import('module.context.XException');
			throw new XException('invalid parameters. expected:{"id":"","info":"","data":""}, but get:'.json_encode($inputParams), 400);
		}
		if(!is_array($inputParams['info'])){
			import('module.context.XException');
			throw new XException('invalid param info: '.json_encode($inputParams), 400);
		}
		if(!array_key_exists('platformAppId', $inputParams['info']) || !array_key_exists('platformUserId', $inputParams['info'])){
			import('module.context.XException');
			throw new XException('need to specify platformAppId, platformUserId in info parameter: '.json_encode($inputParams), 400);
		}
	}
	
	/** 
	 * <b>locale getter</b>
	 * 
	 * <b>获取locale信息</b>
	 * @todo
	 */	
	function getLocale(){
		
	}	

	/** 
	 * <b>servletRequest getter</b>
	 * 
	 * <b>获取servletRequest属性的方法</b>
	 * 
	 * @return XServletRequest
	 */	
	function getServletRequest(){
		if(!$this->servletRequest){			
			import('util.http.XServletRequest');
			$this->servletRequest = XServletRequest::singleton();
		}
		return $this->servletRequest;
	}
	
	/** 
	 * <b>servletRequest setter</b>
	 * 
	 * <b>设置servletRequest属性的方法</b>
	 * 
	 * @param XServletRequest $servletRequest
	 * @return XServiceRequest
	 */	
	function setServletRequest(XServletRequest $servletRequest){
		$this->servletRequest = $servletRequest;
		return $this;
	}
	
	/** 
	 * <b>context getter</b>
	 * 
	 * <b>获取context属性的方法</b>
	 * 
	 * @return XServiceContext
	 */	
	function getContext(){
		return $this->context;
	}
	
	/** 
	 * <b>context setter</b>
	 * 
	 * <b>设置context属性的方法</b>
	 * 
	 * @param XServiceContext $context
	 * @return XServiceRequest
	 */	
	function setContext(XServiceContext $context){
		$this->context = $context;
		return $this;
	}
	
	/**
	 * <b>get platform_uid from parameters</b>
	 * 
	 * <b>从parameters属性中获取platform_uid</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	function getPlatformUID(){
		$info = $this->getInfo();
		if(isset($info['platformId'])){
			$platformUid = $info['platformId'];
		}elseif(isset($info['platform_uid'])){
			$platformUid = $info['platform_uid'];
		}else{
			import('module.context.XException');
			throw new XException('invalid param platform_uid or platformId', 400);
		}
		return $platformUid;
	}

	/**
	 * <b>get platform_uid from parameters</b>
	 * 
	 * <b>从parameters属性中获取platform_uid</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	function getPlatformAppId(){
		$info = $this->getInfo();
		if(isset($info['platformAppId'])){
			$platformUid = $info['platformAppId'];
		}elseif(isset($info['platform_uid'])){
			$platformUid = $info['platform_uid'];
		}else{
			import('module.context.XException');
			throw new XException('invalid param platform_uid or platformAppId', 400);
		}
		return $platformUid;
	}
	
	/**
	 * <b>get platform_user_uid from parameters</b>
	 * 
	 * <b>从parameters属性中获取platform_user_uid</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	function getPlatformUserUID(){
		$info = $this->getInfo();
		if(isset($info['platformUserId'])){
			$platformUserUid = $info['platformUserId'];
		}elseif(isset($info['platform_user_uid'])){
			$platformUserUid = $info['platform_user_uid'];
		}else{
			import('module.context.XException');
			throw new XException('invalid param platform_user_uid or platformUserId', 400);
		}
		return $platformUserUid;
	}
	
	/**
	 * <b>get platformAddress, platformAddress=platform_uid.'_'.platform_user_uid</b>
	 * 
	 * <b>获取platformAddress，platformAddress=platform_uid.'_'.platform_user_uid</b>
	 * 
	 * @return string
	 */
	function getPlatformAddress(){
		return $this->getPlatformUserUID().'_'.$this->getPlatformAppId();
	}
	
	/**
	 * <b>get user_uid from parameters</b>
	 * 
	 * <b>从parameters属性中获取user_uid</b>
	 * 
	 * @throws XException
	 * @return string
	 */
	function getUserUID(){
		return $this->getGameUserId();
	}
	
	/**
	 * <b>get gameUserId from parameters</b>
	 * 
	 * <b>从parameters属性中获取gameUserId</b>
	 * 
	 * @return string
	 */
	function getGameUserId(){
		$info = $this->getInfo();
		if(!isset($info['gameUserId'])){
			return null;
		}
		return $info['gameUserId'];
	}
	
	/** 
	 * <b>classPath setter</b>
	 * 
	 * <b>设置classPath属性的方法</b>
	 * 
	 * @param string $classPath
	 * @return XServiceRequest
	 */	
	public function setClassPath($classPath){
		$this->classPath = $classPath;
		return $this;	
	}
	
	/** 
	 * <b>classPath getter</b>
	 * 
	 * <b>获取classPath属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getClassPath(){
		return $this->classPath;	
	}
	
	/** 
	 * <b>className setter</b>
	 * 
	 * <b>设置className属性的方法</b>
	 * 
	 * @param string $className
	 * @return XServiceRequest
	 */	
	public function setClassName($className){
		$this->className = $className;
		return $this;
	}
	
	/** 
	 * <b>className getter</b>
	 * 
	 * <b>获取className属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getClassName(){
		return $this->className;	
	}	

	/** 
	 * <b>methodName setter</b>
	 * 
	 * <b>设置methodName属性的方法</b>
	 * 
	 * @param string $methodName
	 * @return XServiceRequest
	 */	
	public function setMethodName($methodName){
		$this->methodName = $methodName;
		return $this;
	}
	
	/** 
	 * <b>methodName getter</b>
	 * 
	 * <b>获取methodName属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getMethodName(){
		return $this->methodName;	
	}
	
	/** 
	 * <b>get the raw data from the request body, will invoke getDetails() method of the protocol object</b>
	 * 
	 * <b>获取请求的原始数据, 会调用相应protocol对象的getDetails()方法</b>
	 * 
	 * @return mixed
	 */	
	public function getDetails(){
		return $this->getContext()->getProtocol()->getDetails();
	}
	
	/**
	 * <b>get the XServletRequest object</b>
	 * 
	 * <b>获取XServletRequest对象</b>
	 * 
	 * @return XServletRequest
	 */
	public function getPrincipal(){
		return $this->getServletRequest();
	}
	
	/**
	 * <b>get the HTTP Request Header by given key</b>
	 * 
	 * <b>根据给定key获取HTTP请求的Header</b>
	 * 
	 * @param string $key
	 * @return string
	 */
	public function getHeader($key){
		return $this->getContext()->getProtocol()->getHeader($key);
	}
	
	/**
	 * get client ip address of this request
	 * 
	 * 获取本次请求客户端IP地址
	 * 
	 * @return string
	 */
	public function getLastLoginIp(){
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/**
	 * get client request timestamp
	 * 
	 * 获取本次请求unix时间戳
	 * 
	 * @return int
	 */
	public function getLastLoginTime(){
		return $_SERVER['REQUEST_TIME'];
	}
}
?>