<?php
import('module.protocol.XRESTProtocol');
/**
 * XAdminProtocol
 * 
 * admin protocol
 * 
 * admin协议，供GM接口使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XAdminProtocol extends XRESTProtocol{
	protected $name = 'ADMIN';
	protected $gateway = 'admin';
	
	/** 
	 * get XServiceRequest instance from request uri
	 * 
	 * 从请求的uri中获取XServiceRequest实例
	 * 
	 * 例如，请求为http://xingcloud/admin/user/query?platform_uid=facebook&platform_user_uid=1111
	 * 
	 * 经过解析后，XServiceRequest中各个属性的值为
	 * 
	 * 'classPath'=>'\\admin', 'className'=>'UserAdminService', 'methodName'=>'query',
	 * 'parameters'=>array('platform_uid'=>'facebook', 'platform_user_uid'=>'1111')
	 * 
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$uri = preg_replace("/\/+/i", '.', $this->route);
		preg_match("/([\w\.]+)\.(\w+)\.(\w+)$/i", $uri, $match);
		if(!isset($match[3])){
			return false;
		}
		$className = x_name_to_java($match[2]).'AdminService';
		$classPath = strtr($match[1], '.', __DS__);
		import('module.service.XServiceRequest');
		$request = XServiceRequest::singleton();
		$request->setClassName($className);
		$request->setClassPath($classPath);
		$request->setMethodName($match[3]);
		$request->setParameters($this->getStream()->getInputParams());
		return $request;
	}
}
?>