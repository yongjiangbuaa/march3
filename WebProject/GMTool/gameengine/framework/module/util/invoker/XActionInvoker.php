<?php
import('module.util.invoker.XInvoker');
/**
 * XActionInvoker
 * 
 * action invoker
 * 
 * action调用器
 * 
 * @author Wangqi
 * @package util.invoker
 */
class XActionInvoker extends XAbstractInvoker{
	private static $instance = null;
	protected $id = 1;
	protected $info = array();
	/**
	 * @var XActionRequest
	 */
	private $actionRequest = null;
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XActionInvoker
	 */
	static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * invoke an Action
	 * 
	 * 调用一个Action
	 * 
	 * @param string $api Action的名称，如BuyAction
	 * @param array $parameters 执行Action所需的参数
	 */
	public function invoke($api, $parameters=array()){
		if(empty($this->info)){
			import('module.context.XException');
			throw new XException('info param is not correct');
		}
		$this->actionRequest = new XActionRequest();
		if(!preg_match('/Action$/i', $api)){
			$api = ucwords($api).'Action';
		}
		$actionName = preg_replace('/[\w\.]+\./i', '', $api);
		$actionPath = preg_replace('/'.$actionName.'$/', '', $api);
		if(!preg_match('/Action$/i', $actionName)){
			$actionName = ucwords($actionName).'Action';
		}
		$serviceParams = $this->constructServiceParam($api, $parameters);
		$this->setServiceRequest('action', 'ActionService', 'execute', $serviceParams);
		$this->actionRequest->setServiceRequest($this->serviceRequest);
		$this->actionRequest->setParameters($parameters);
		
		$this->actionRequest->setActionName($api);
		$path = GAME_SERVICE_DIR.__DS__.'action'.__DS__;
		if(!empty($actionPath)){
			$actionPath = preg_replace('/\./', __DS__, $actionPath);
			$actionPath = trim($actionPath, __DS__);
			$path .= $actionPath.__DS__;
		}
		$path .= $actionName.'.php';
		if(!is_file($path)){
			import('module.context.XException');
			throw new XException("action $actionName not found");
		}
		if(!class_exists($actionName)){
			require $path;
		}
		import('service.user.UserFactory');
		$user = UserFactory::singleton()->get($this->info['gameUserId']);
		$action = new $actionName();
		$action->setUser($user);
		$result = $action->doExecute($this->actionRequest);
		$result->setId($this->id);
		return $result;
	}
	
	/**
	 * id setter
	 * 
	 * 设置id属性的方法
	 * 
	 * @param string/int $id
	 */
	public function setID($id){
		$this->id = $id;
	}
	
	/**
	 * id setter
	 * 
	 * 设置id属性的方法
	 * 
	 * @param string/int $info
	 */
	public function setInfo($info){
		if(!is_array($info) || !isset($info['platformAppId']) ||  !isset($info['platformUserId']) || !isset($info['gameUserId'])){
			return false;
		}
		$this->info = $info;
	}
	
	protected function constructServiceParam($actionName, $parameters){
		$param = array();
		$param['info'] = $this->info;
		$param['id'] = $this->id;
		$data['name'] = $actionName;
		$data['params'] = $parameters;
		$param['data'] = array($data);
		return $param;
	}
	
}