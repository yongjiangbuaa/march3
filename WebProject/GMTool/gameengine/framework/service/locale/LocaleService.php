<?php
/**
 * @Pointcut('protocol|auth|cache')
 */
class LocaleService extends XAbstractService{
	/**
	 * 区域配置服务，为前台提供指定区域的配置属性。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache
	 * @Status(file='/locale/files/locale/#lang#/config.properties')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string name 区域名
	 * @ServiceParam int version 文件版本，从xingcloud/status接口获取
	 * @return XServiceResult
	 */	
	public function doGet(XServiceRequest $request){
		$name = $request->getParameter('name');
		if(!isset($name)){
			return $this->_error(400, 'param name is required');
		}
		$file = XINGCLOUD_RESOURCE_DIR."/locale/locale/{$name}/config.properties";
		if(!file_exists($file)){
			return $this->_error(400, "locale {$name} not exists");
		}
		$config = @parse_ini_file($file);
		if(!is_array($config)){
			return $this->_success(array());
		}
		return $this->_success($config);
	}
}
?>