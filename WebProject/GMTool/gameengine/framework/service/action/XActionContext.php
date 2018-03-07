<?php
/**
 * XActionContext
 * 
 * action context class
 * 
 * action context类，解析XServiceRequest参数并执行action。
 * 
 * @author Tianwei
 * @package action 
 */
class XActionContext implements XContext{
	private static $instance = null;
	protected $request = null;
	
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
	 * @return XActionContext
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * <b>process the action request, execute the specified actions</b>
	 * 
	 * <b>will publish ActionStartedEvent and XLoggingEvent before executing actions</b>
	 * 
	 * <b>will publish ActionFinishedEvent and XLoggingEvent after action executed</b>
	 * 
	 * <b>处理action请求，支持批量处理多个action</b>
	 * 
	 * <b>action执行前会发布 ActionStartedEvent和XLoggingEvent</b>
	 * 
	 * <b>action执行后会发布ActionFinishedEvent和XLoggingEvent</b>
	 * 
	 * <b>action的执行结保存为XMultiResult</b>
	 * 
	 * @param XServiceRequest $request
	 * @return XMultiResult
	 */
	public function execute(XServiceRequest $request){
		import('module.service.result.XMultiResult');
		import('service.action.XActionRequest');
		import('service.action.XActionResult');
		import('service.action.event.ActionStartedEvent');
		import('service.action.event.ActionFinishedEvent');
		import('service.user.UserFactory');
		import('service.user.AbstractUserProfile');
		import('service.user.'.UserFactory::singleton()->getUserClass());
		import('module.util.logger.XLoggingEvent');
		$id = $request->getId();
		$info = $request->getInfo();
		$params = $request->getParameter('data');
		if(!is_array($params)){
			return XServiceResult::clientError('param data invalid');
		}
		$platformAppId = $request->getPlatformAppId();
		$platformUserId = $request->getPlatformUserUID();
		$gameUserId = $request->getGameUserId();
		$mutilResult = new XMultiResult();
		$mutilResult->setId($request->getId());
		$index = 0;
		$result = NULL;
		$errorFlag = false;
		foreach($params as $data){
			$actionResult = new XActionResult();
			if(isset($data['name']) && array_key_exists('index', $data)){
				try{
					//onec an error occured, skip the rest of the actions
					if($errorFlag){
						$result = new XServiceResult(500, 'action skipped due to previous error');
						$actionResult->setResult($result);
						$actionResult->setCode($result->getCode());
						$actionResult->setMessage($result->getMessage());
					}else{
						$actionRequest = new XActionRequest();
						$actionName = $data['name'];
						if(!preg_match("/Action$/i", $actionName)){
							$actionName = $actionName.'Action';
						}
						$sign = md5($info['platformUserId'].'_nnd_' .$info['platformAppId'].'c11adtmd72ed4787a6d1c46ef8841800');
						if ($data['params']['sign']!= $sign)
						{
  							throw new XException("E600028");
						}
						$currTime = time();
						if (($data['params']['time'] < $currTime - 60 || $data['params']['time'] >  $currTime + 60) && $actionName != "VIPAction" && $actionName != "SetPlayerInfoAction" && $actionName != "TutorialAction" && $actionName != "SetLogAction")
						{
  							throw new XException("E600029");
						}
						import('service.action.DataClass');
						StatData::$pf = $data['params']['pf'];
						
						$actionRequest->setActionName($actionName);
						$actionRequest->setServiceRequest($request);
						if(isset($data['params'])){
							$actionRequest->setParameters($data['params']);
						}
// 						XEventContext::singleton()->publish(new ActionStartedEvent($actionRequest));
// 						XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, INFO, "action {$actionName} user ".$gameUserId." started: ".json_encode($params), 'action.'.$actionName));
						if($actionName == "SetPlayerInfoAction")
						{
							import('service.user.UserProfile');
							$user = UserProfile::getWithUID($gameUserId);
							if(!is_object($user)){
								throw new XException("user uid $gameUserId not found");
							}
						}
						else if($actionName != "TutorialAction")
						{
							$user = $request->getContext()->getUser($gameUserId);
							if(!is_object($user)){
								throw new XException("user uid $gameUserId not found");
							}
						}
						$result = $this->executeAction($actionRequest, $user);
						if($result->getCode()!=200){
							$errorFlag = true;
						}else{
							//返回值增加分享
							import('service.action.DataClass');
							if(!$result->getMessage() && StatData::$share){
								$result->setMessage(array('share'=>StatData::$share));
							}
						}
						$actionResult->setResult($result);
						$actionResult->setCode($result->getCode());
						$actionResult->setMessage($result->getMessage());
// 						$actionFinishedEvent = new ActionFinishedEvent($actionRequest);
// 						$actionFinishedEvent->setPlatformUID($platformAppId);
// 						$actionFinishedEvent->setUserUID($gameUserId);
// 						$actionFinishedEvent->setResult($result);
// 						XEventContext::singleton()->publish($actionFinishedEvent);
// 						XEventContext::singleton()->publish(new XLoggingEvent($this, __METHOD__, __LINE__, INFO, "action {$actionName} user ".$gameUserId." finished: ".json_encode($result->asArray()), 'action.'.$actionName));
					}
				}catch(Exception $e){
					$errorFlag = true;
					$result = new XServiceResult($e->getCode(), $e->getMessage());
					$actionResult->setResult($result);
					$actionResult->setCode($result->getCode());
					$actionResult->setMessage($result->getMessage());
					import('service.action.event.ActionErrorEvent');
					XEventContext::singleton()->publish(new ActionErrorEvent($e));
					import('module.util.logger.XExceptionEvent');
					XEventContext::singleton()->publish(new XExceptionEvent($e, $data));
				}
			}else{
				continue;
			}
			if(array_key_exists('index', $data)){
				$actionResult->setIndex($data['index']);
			}
			$mutilResult->addResult($actionResult);
			$this->writeActionLog($info,$data,$result,$user);
		}
		return $mutilResult;
	}
	protected function writeActionLog($info,$param,XServiceResult $result,$user){
// 		import('util.config.XConfig');
// 		$config = XConfig::singleton()->get('statistics');
		
// 		$actionName = $param['name'];
// 		if(in_array($actionName, array_keys($config['actions']['enabled']))){
// 			$msg = array('params'=>$param['params'],'code'=>$result->getCode());
// 			$path = date('Y-m-d').'_action' . '/' . $actionName;
// 			import('service.action.LoggerClass');
// 			$logger = new FileLogger($info['gameUserId'], $msg, $path);
// 			$logger->log();
// 		}

		$actionName = $param['name'];
		$msg = array('action'=>$actionName,'params'=>$param['params'],'time'=>time(),'code'=>$result->code,'data'=>$result->data,'message'=>$result->message);
		if($user->level > 30 && !in_array($actionName, array('ChatAction','CityRefreshAction','TeamAction','TeamBattleAction')))// && in_array($info['gameUserId'], $config['users']['enabled']['u']))
		{
			$path = date('Y-m-d').'_useraction';
			import('service.action.LoggerClass');
			$logger = new FileLogger($info['gameUserId'], $msg, $path);
			$logger->log();
		}
		if($result->code === 'AMFPHP_RUNTIME_ERROR')
		{
			import('service.action.LoggerClass');
			$amf = array(
					'time' => date('Y-m-d H:i:s'),
					'user' => $info['gameUserId'],
					'data' => $msg,
			);
			$logger = new FileLogger(date('Y-m-d').'_amf', $amf, date('Y-m-d').'_amf');
			$logger->log();
		}
	}
	
	/**
	 * <b>execute an action through APO proxy</b>
	 * 
	 * <b>通过APO代理执行一个action</b>
	 * 
	 * @param XActionRequest $request
	 * @param mixed $user
	 * @throws XException
	 * @return XServiceResult
	 */
	public function executeAction(XActionRequest $request, $user = null){
		$actionName = $request->getActionName();
		if(!$actionName){
			throw new XException("action not defined in params");
		}
		if(!preg_match('/Action$/i', $actionName)){
			$actionName .= 'Action';
		}
		$actionClass = $actionName;
		$classPath = null;
		if(strpos($actionName, '.') !== false){
			$pos = strrpos($actionName, '.');
			$classPath = substr($actionName, 0, $pos);
			$actionClass = substr($actionName, $pos + 1);
		}
		$actionClass{0} = strtoupper($actionClass{0});
		if(!class_exists($actionClass, false)){
			$realPath = GAME_SERVICE_DIR.__DS__.'action'.__DS__;
			if($classPath){
				$realPath .= strtr($classPath, '.', __DS__).__DS__;
			}
			$realPath .= $actionClass.'.php';
			if(!is_file($realPath)){
				import('module.context.XException');
				throw new XException("action {$actionClass} not exists in path ".$classPath);
			}
			import('service.action.XAbstractAction');
			if($classPath){				
				import('domain.action.'.$classPath.'.'.$actionClass);
			}else{
				import('domain.action.'.$actionClass);
			}
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$action = $factory->get($actionClass);
		$action->setUser($user);
		return $action->doExecute($request);
	}
}
?>