<?php
import('module.context.XContext');
/**
 * XServiceContext
 * 
 * service context class, hold protocol information and start to invoke service
 * 
 * service context类，包含protocol信息并开始调用服务
 * 
 * @author Tianwei
 * @package service 
 */
class XServiceContext implements XContext{
	private static $instance = null;
	private $protocol = null;

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
	 * @return XServiceContext
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/** 
	 * <b>protocol setter</b>
	 * 
	 * <b>设置protocol属性的方法</b>
	 * 
	 * @param XProtocol $protocol
	 * @return XServiceContext
	 */	
	function setProtocol(XProtocol $protocol){
		$this->protocol = $protocol;
		return $this;
	}
	
	/** 
	 * <b>protocol getter</b>
	 * 
	 * <b>获取protocol属性的方法</b>
	 * 
	 * @return XProtocol
	 */	
	function getProtocol(){
		return $this->protocol;
	}
	
	/**
	 * <b>start to process the service request, will invoke the service and output the result</b>
	 * 
	 * <b>开始处理service请求，将会调用service并且向前台输出处理结果</b>
	 * 
	 * <b>start方法会从protocol中构造出XRequest, XResponse对象，以XRequest对象为传入参数在service中执行</b>
	 * 
	 * <b>执行结果保存在XResponse对象中，完成后调用XResponse的output方法输出结果给前台</b>
	 * 
	 * <b>调用服务前会发布ServiceStartedEvent和XLoggingEvent</b>
	 * 
	 * <b>服务调用完成后会发布ServiceFinishedEvent和XLoggingEvent</b>
	 * 
	 * <b>如果抛出异常，会发布XExceptionEvent</b>
	 * 
	 * @throws Exception
	 * @return boolean
	 */
	public function start(){	
		$response = null;
		try{
			import('util.io.XFile');
			import('module.protocol.XProtocolFactory');
			//import('module.event.XEventContext');
			import('module.service.result.XServiceResult');
			import('module.service.result.XMultiResult');
			import('module.service.result.XFileResult');
			import('module.service.result.XHeaderResult');
			$protocol = XProtocolFactory::singleton()->get();
			if(!$protocol){
				if(defined('__MVC__') && __MVC__){
					import('module.mvc.route.XRouter');
					XRouter::singleton()->start();
				}
				return false;
			}
			$this->setProtocol($protocol);
			$request = $protocol->getRequest();
			
			$methodName = $request->getMethodName();
			$methodName = ucwords($methodName);
			//doGetItems返回压缩处理
			if ($methodName =='GetItems')
			{
				$protocol->getStream()->setGzip('gzip');
			}
			$response = $protocol->getResponse();
			if(!$request){
				$response->setResult(new XServiceResult());
				$response->output();
				return false;
			}
			$request->setContext($this);
// 			$userRepository = XingCloudApp::singleton()->getUserRepository();
// 			$gameUserId = $request->getGameUserId();
 			$user = null;
// 			if($userRepository && !empty($gameUserId)){
// 				$user = $userRepository->get($gameUserId);
// 			}
			import('module.context.user.UserAware');
			if($request instanceof UserAware){
				$request->setUser($user);
			}
			import('module.util.session.XSession');
			$httpSession = XSession::singleton()->getInstance();
			if($httpSession){
				$httpSession->setRequest($request);
				if($httpSession instanceof UserAware){
					$httpSession->setUser($user);
				}
			}
			//时区设置
			$config = array(
										'cn'=>array('language'=>'cn',
												'timezone'=>'Asia/Shanghai',
												'encode'=>'utf-8',),
										'tw'=>array('language'=>'tw',
												'timezone'=>'Asia/Shanghai',
												'encode'=>'utf-8',),
										'pt'=>array('language'=>'pt',
												'timezone'=>'Brazil/East',
												'encode'=>'utf-8',),
										'de'=>array('language'=>'de',
												'timezone'=>'Europe/Berlin',
												'encode'=>'utf-8',),
										'tr'=>array('language'=>'tr',
												'timezone'=>'Turkey',
												'encode'=>'utf-8',),
										'es'=>array('language'=>'es',
												'timezone'=>'Mexico/BajaSur',
												'encode'=>'utf-8',),
										'en'=>array('language'=>'en',
												'timezone'=>'CST6CDT',
												'encode'=>'utf-8',),
								);
			$params = $request->getParameters();
			$tempArr_ = explode('_', $params['info']['platformAppId']);
			$lang= $tempArr_[1];
			import('service.action.DataClass');
			StatData::$lang = $lang;
			if(is_array($config)
					&& isset($config[$lang]['timezone'])
					&& ini_get('date.timezone') != $config[$lang]['timezone']){
				ini_set('date.timezone', $config[$lang]['timezone']);
			}
// 			import('module.service.event.ServiceStartedEvent');
// 			XEventContext::singleton()->publish(new ServiceStartedEvent($request));
// 			import('module.util.logger.XLoggingEvent');
// 			XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, INFO, "service {$request->getClassName()} method {$request->getMethodName()} started: ".@json_encode($request->getParameters()), $request->getClassName().'.do'.ucfirst($request->getMethodName())));
			$result = $this->invoke($request);
			if(is_object($result)){
				$result->setRequest($request);
			}
			$response->setResult($result);
			$response->output();
			XingCloudApp::singleton()->close();
// 			import('module.service.event.ServiceFinishedEvent');
// 			$serviceFinishedEvent = new ServiceFinishedEvent($request);
// 			$serviceFinishedEvent->setResult($result);
// 			XEventContext::singleton()->publish($serviceFinishedEvent);
// 			import('module.util.logger.XLoggingEvent');
// 			$message = "service {$request->getClassName()} method {$request->getMethodName()} finished: ";
// 			if(is_array($result)){
// 				$message .= json_encode($result);
// 			}elseif (is_object($result) && method_exists($result, 'asArray')){
// 				$message .= json_encode($result->asArray());
// 			}
// 			XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, INFO, $message, $request->getClassName().'.do'.ucfirst($request->getMethodName())));
			return true;
		}catch(Exception $e){
			if($protocol){
				if(!$response){
					import('module.service.XServiceResponse');
					$response = XServiceResponse::singleton();
					if(!$response->getStream()){
						$response = $protocol->getResponse();
					}
				}
				$request = $protocol->getRequest();
				$result = new XServiceResult($e->getCode(), $e->getMessage(), array());
				$result->setRequest($request);
				$response->setResult($result);
				$response->output();
			}
// 			import('module.service.event.ServiceErrorEvent');
// 			XEventContext::singleton()->publish(new ServiceErrorEvent($e));
// 			import('module.util.logger.XExceptionEvent');
// 			$parameters = NULL;
// 			if(!empty($request) && is_object($request)){
// 				$parameters = $request->getParameters();
// 			}
// 			XEventContext::singleton()->publish(new XExceptionEvent($e, $parameters));
			$GLOBALS['XExceptionThrowed'] = TRUE;
		}
	}
	
	/**
	 * <b>get the user object by the given uid</b>
	 * 
	 * <b>根据给定的uid查询数据库，获取user对象</b>
	 * 
	 * @param string $uid
	 * @return UserProfile
	 */
	public function getUser($uid){
		import('service.user.UserFactory');
		return UserFactory::singleton()->get($uid);
	}
	
	/**
	 * <b>invoke a service through APO proxy</b>
	 * 
	 * <b>通过APO代理调用一个service</b>
	 * 
	 * @param XServiceRequest $request
	 * @throws XException
	 * @return mixed
	 */
	public function invoke(XServiceRequest $request){
		$className = x_name_to_java($request->getClassName());
		if(!preg_match('/Service$/', $className)){
			$className .= 'Service';
		}
		$methodName = $request->getMethodName();
		if(!preg_match('/^do[A-Z]/i', $methodName)){
			$methodName = 'do'.ucwords($methodName);
		}

		if(!class_exists($className, false)){
			$servicePath = trim($request->getClassPath(), '\\/.');
			$classPath = preg_replace('/[\\/]+/i', __DS__, trim($request->getClassPath(), '/\\\\'));
			$realPath = $this->getServicePath($classPath, $className);
			if(!is_file($realPath)){
				import('module.context.XException');			
				throw new XException("service not exists in uri ".$_SERVER["PHP_SELF"]);
			}
			import('module.service.XAbstractService');
			require $realPath;
		}
		if(!class_exists($className, false)
			|| !method_exists($className, $methodName)){
			import('module.context.XException');
			throw new XException("service class {$className} or method {$methodName} not exists");
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$service = $factory->get($className);
		return $service->$methodName($request);
	}
	
	/**
	 * <b>get the real path of a service</b>
	 * 
	 * <b>获取一个service的完整路径，会在XINGCLOUD_SERVICE_DIR和GAME_SERVICE_DIR中进行检索</b>
	 * 
	 * @param string $classPath
	 * @param string $className
	 * @return string
	 */
	private function getServicePath($classPath, $className){
		foreach(array(
			XINGCLOUD_SERVICE_DIR,
			GAME_SERVICE_DIR,
		) as $basePath){
			foreach(array(
				__DS__.'service'.__DS__,
				__DS__,
			) as $servicePrefix){
				$realPath = $basePath.__DS__.$classPath.$servicePrefix.$className.'.php';					
				if(is_file($realPath)){
					return $realPath;
				}
			}
		}
		return null;
	}
}
?>