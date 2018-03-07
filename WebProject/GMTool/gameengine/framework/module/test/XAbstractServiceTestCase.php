<?php
import('module.test.XAbstractTestCase');
/**
 * XAbstractServiceTestCase
 * 
 * abstract service testcase class
 * 
 * service testcase抽象类，继承XAbstractTestCase
 * 
 * @author Wangqi
 * @abstract
 * @package test 
 */
abstract class XAbstractServiceTestCase extends XAbstractTestCase{
	protected $request=NULL;
	protected $user_service=NULL;
	protected $service=NULL;
	protected $user_uid;
	protected $enableService;
	protected $enableUserService;
	protected $initUserUid;
	protected $platform_uid='XingCloudTestPlatform';
	protected $platform_user_uid=1;
	protected $id = 1;
	protected $platformAppId='XingCloudTestPlatform';
	protected $platformUserId=1;
	protected $gameUserId;
	/**
	 * construct method
	 * 
	 * 构造函数
	 */
	public function __construct(){
		parent::__construct();
		$this->platformUserId = $this->platform_user_uid = strval(microtime(true)*10000);
	}
	protected function setUp(){
		$this->request = XServiceRequest::singleton();
		$this->request->setContext(XServiceContext::singleton());
		$this->request->setServletRequest(XServletRequest::singleton());
		if($this->enableService){
			$this->createService();
		}
		if($this->initUserUid){
			$this->user_service = new UserService();
			$this->getUserUid();
		}
	}
	/**
	 * create a new service from the testcase class name
	 * 
	 * 根据testcase类名创建相应的service
	 * 例如，在UserServiceTestCase中调用本方法，将会创建一个UserService实例
	 * 
	 */
	public function createService(){
		$testcase = get_class($this);
		$classname = preg_replace('/TestCase$/i', '', $testcase);
		$this->service = new $classname();
	}
	/**
	 * create a UserService instance
	 * 
	 * 创建一个UserService实例
	 */
	public function createUserService(){
		$this->user_service = new UserService();
	}
	
	protected function setRequestParam($api, $parameters=array()){
		$api = explode('.', $api);
		if(!empty($api) && count($api)==3){
			$classPath = $api[0];
			$className = ucfirst($api[1]);
			$methodName = $api[2];
			if($classPath=='admin'){
				$className = $className.'AdminService';
			}else{
				$className = $className.'Service';
			}
			$inputParams = array();
			$inputParams['id'] = $this->id;
			$inputParams['info'] = array('platformAppId'=>$this->platformAppId, 'platformUserId'=>$this->platformUserId, 'gameUserId'=>$this->gameUserId);
			$inputParams['data'] = $parameters;
			$this->request->setClassName($className);
			$this->request->setClassPath($classPath);
			$this->request->setMethodName($methodName);
			$this->request->setParameters($inputParams);
		}else{
			throw new Exception("api name not correct: $api", 400);
		}
	}
	
	protected function setUserPlatformInfo($platformAppId, $platformUserId){
		$this->platformAppId = $this->platform_uid = $platformAppId;
		$this->platformUserId = $this->platform_user_uid = $platformUserId;
	}
	
	protected function setGameUserId($gameUserId){
		$this->gameUserId = $this->user_uid = $gameUserId;
	}
	
	protected function getUserUid(){
		if(empty($this->user_service)){
			$this->createUserService();
		}
		$this->setRequestParam('user.user.login', array());
		$r = $this->user_service->doPlatformLogin($this->request);
		$loginSuccess = false;
		if(is_object($r) && property_exists($r, 'data')){
			$data = $r->data;
			if(is_object($data) && property_exists($data, 'uid')){
				$this->gameUserId = $this->user_uid = $data->uid;
				$loginSuccess = true;
			}
		}
		if(!$loginSuccess){
			$this->setRequestParam('user.user.register', array());
			$r = $this->user_service->doPlatformRegister($this->request);
			if(is_object($r) && property_exists($r, 'data')){
				$data = $r->data;
				if(is_object($data) && property_exists($data, 'uid')){
					$this->gameUserId = $this->user_uid = $data->uid;
					$loginSuccess = true;
				}
			}
		}
		if(!$loginSuccess){
			throw new Exception('failed to login or register', 500);
		}
		return $this->gameUserId;
	}
	/**
	 * execute a method of the created service, the method name is specified in $this->request
	 * 
	 * 执行所创建的service中的一个方法，方法名通过$this->request指定
	 * @return XResult
	 */
	public function executeService(){
		if(empty($this->service)){
			$this->createService();
		}
		$methodName = $this->request->getMethodName();
		$methodName = 'do'.ucfirst($methodName);
		$result = $this->service->$methodName($this->request);
		return $result;
	}
	/**
	 * get testcase execution result, including testcase count, failed testcase information and passed testcase information
	 * 
	 * 获取testcase执行结果，包括testcase数量，成果及失败的testcase的信息
	 * 
	 * @return array
	 */
	public function getResult(){
		$r['countTestCases'] = $this->countTestCases(); 
		$r['passedTests'] = $this->passedTests;
		$r['failedTests'] = $this->failedTests;
		return $r;
	}
	/**
	 * assert service execution result
	 * 
	 * 验证service执行结果是否符合格式要求
	 * 
	 * @param XResult $result
	 * @throws XAssertFailedException
	 */
	public function assertServiceResult($result){
		XAssert::assertNotEmpty($result);
		XAssert::assertObject($result);
		XAssert::assertPropertyExists($result, 'id');
		XAssert::assertPropertyExists($result, 'code');
		XAssert::assertEquals(200, $result->code);
		XAssert::assertPropertyExists($result, 'message');
		XAssert::assertPropertyExists($result, 'data');
	}
}




?>