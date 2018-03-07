<?php
/**
 * XingCloudApp
 * 
 * #en framework loader
 * #zh 框架启动类
 * @author Tianwei
 */
final class XingCloudApp{
	private static $instance = null;
	private $userRepository = null;
	
	private function __construct(){
	}
	
	/** 
	 * @return XingCloudApp
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
		
	public function start($enableRoute = true){
		try{
			try {
				$this->init();
			}catch (Exception $e){
				import('module.event.context.XingCloudErrorEvent');
				XEventContext::singleton()->publish(new XingCloudErrorEvent($e));
				throw $e;
			}
			if($enableRoute !== false){
				import('module.service.XServiceContext');
				return XServiceContext::singleton()->start();
			}
			return true;
		}catch(Exception $e){
			import('module.event.XEventContext');
			import('module.util.logger.XExceptionEvent');
			XEventContext::singleton()->publish(new XExceptionEvent($e));
			$GLOBALS['XExceptionThrowed'] = TRUE;
		}
	}
	
	public function close(){
// 		import('module.event.context.XingCloudClosedEvent');
// 		XEventContext::singleton()->publish(new XingCloudClosedEvent($this));
		$xingcloudRequestFinishTime = microtime(true);
		global $xingcloudRequestStartTime;
		$elapsedTime = $xingcloudRequestFinishTime - $xingcloudRequestStartTime;

		if($elapsedTime > 10){
// 			global $xingcloudRequestStartTime;
// 			$elapsedTime = microtime(true) - $xingcloudRequestStartTime;
			$protocol = XServiceContext::singleton()->getProtocol();
			$request = null;
			$log = '';
			if($protocol){
				$request = $protocol->getRequest();
			}
			$usemem = memory_get_usage();
			$log = "request time: $elapsedTime, memory: $usemem, time: $xingcloudRequestStartTime, parameters: ".json_encode($request->getParameters());
			$log_file = 'warfare_' . date("Y-m-d");
			import('service.action.LoggerClass');
			$logger = new FileLogger($log_file, $log);
			$logger->log();
			
		}
		
// 		XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, WARN, $log, "XingcloudSlowRequestLog"));
	}
	
	public function register($className){
		if(substr($className, -8) == 'Listener'){			
			import('util.annotation.XAnnotation');	
			$annotation = XAnnotation::singleton($className)->annotations();
			if(isset($annotation['EventListener'])){	
				import('module.event.XEventContext');
				XEventContext::singleton()->addListenerByParams($className, $annotation['EventListener']);
			}
		}
	}
	
	public function getUserRepository(){
		if($this->userRepository){
			return $this->userRepository;
		}
		$userRepository = XConfig::singleton()->get('service.user.userRepository.class');
		if(!$userRepository){
			return null;
		}
		$repositoryName = preg_replace('/^[\w+\.]+\./', '', $userRepository);
		import($userRepository);
		if(method_exists($repositoryName, 'singleton')){
			eval('$this->userRepository = $repositoryName::singleton();');
		}else{
			$this->userRepository = new $repositoryName();
		}
		return $this->userRepository;
	}
	
	private function init(){
		set_time_limit(30);	
 		import('util.config.XConfig');
 		XConfig::singleton()->load();
// 		$config = XConfig::singleton()->get('global');
// 		if(is_array($config)
// 			&& isset($config['timezone'])
// 			&& ini_get('date.timezone') != $config['timezone']){
// 			ini_set('date.timezone', $config['timezone']);
// 		}
// 		import('module.event.XEventContext');
// 		import('module.event.context.XingCloudStartedEvent');
// 		$this->loadListeners();
// 		register_shutdown_function(array($this, 'close'));
// 		//publish context started event
// 		XEventContext::singleton()->publish(new XingCloudStartedEvent($this));
	}
	
	private function loadListeners(){
		$config = XConfig::singleton()->get('event');
		if(isset($config['listeners'])
			&& is_array($config['listeners'])){
			foreach($config['listeners'] as $listener){
				import($listener, true);
			}
		}
		//load sdk listeners from sdk-description.xml
		$config = XConfig::singleton()->get('modules');
		if(is_array($config) && isset($config['module'])){
			foreach($config['module'] as $moduleConfig){
				if(isset($moduleConfig['@attributes']) && isset($moduleConfig['@attributes']['choose']) && $moduleConfig['@attributes']['choose']=='true'){
				}else{
					continue;
				}
				if(isset($moduleConfig['listener']) && isset($moduleConfig['listener']['id']) && isset($moduleConfig['listener']['class']) &&!empty($moduleConfig['listener']['class'])){
					import($moduleConfig['listener']['class'], TRUE);
				}
			}
		}
		//load listeners from services configuration file
		$config = XConfig::singleton()->get('service');
		if(is_array($config)){
			foreach($config as $serviceName => $serviceConfig){
				if(!isset($serviceConfig['listeners'])){
					continue;
				}
				$listeners = explode(',', $serviceConfig['listeners']);
				foreach($listeners as $listener){
					import('service.'.$serviceName.'.'.trim($listener), true);
				}
			}
		}
	}
}
?>