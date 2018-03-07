<?php
import('module.util.invoker.XAbstractInvoker');
/**
 * XServiceInvoker
 * 
 * service invoker
 * 
 * service调用器
 * 
 * @author Wangqi
 * @package util.invoker
 */
class XServiceInvoker extends XAbstractInvoker{
	private static $instance = null;
	protected $id = 1;
	protected $info = array();
	
	/**
	 * @return XServiceInvoker
	 */
	static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
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
	
	/**
	 * invoke a service
	 * 
	 * 调用一个服务
	 * 
	 * @param string $api 服务的名称，如user.user.platformLogin
	 * @param array $parameters 执行服务所需的参数
	 */
	public function invoke($api, $parameters=array()){
		if(empty($this->info)){
			import('module.context.XException');
			throw new XException('info param is not correct');
		}
		$apiArr = $this->parseAPI($api);
		$serviceParams = $this->constructServiceParam($parameters);
		$this->setServiceRequest($apiArr['classPath'], $apiArr['className'], $apiArr['methodName'], $serviceParams);
		$service = new $apiArr['className']();
		if(!preg_match('/^do[A-Z]/i', $apiArr['methodName'])){
			$apiArr['methodName'] = 'do'.ucwords($apiArr['methodName']);
		}
		$result = $service->$apiArr['methodName']($this->serviceRequest);
		$result->setId($this->id);
		return $result;
	}
	
	protected function parseAPI($api){
		$apiArr = array();
		preg_match("/([\w\.]+)\.(\w+)\.(\w+)$/i", $api, $match);
		if(!isset($match[3])){
			return false;
		}
		if(preg_match('/Service$/', $match[2])){
			$apiArr['className'] = x_name_to_java($match[2]);
		}else{
			$apiArr['className'] = x_name_to_java($match[2]).'Service';
		}
		$apiArr['classPath'] = strtr($match[1], '.', __DS__);
		$apiArr['methodName']=$match[3];
		return $apiArr;
	}
	
	protected function constructServiceParam($parameters){
		$param = array();
		$param['info'] = $this->info;
		$param['id'] = $this->id;
		$param['data'] = $parameters;
		return $param;
	}
	
}