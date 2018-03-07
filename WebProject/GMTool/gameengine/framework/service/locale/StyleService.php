<?php
/**
 * @Pointcut('protocol|auth|cache')
 */
class StyleService extends XAbstractService{
	/**
	 * 多语言样式服务，为前台提供指定语言的CSS样式。
	 * 默认按API参数缓存。
	 * @Protocol(allow='REST')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache
	 * @Status(file='/locale/files/language/#lang#/style.css')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string lang 语言名
	 * @ServiceParam int version 文件版本，从xingcloud/status接口获取
	 * @return XServiceResult
	 */
	public function doGet(XServiceRequest $request){
		$lang = $request->getParameter('lang');
		if(!isset($lang)){
			$result = new XHeaderResult();		
			$result->setStatus(HTTP_BAD_REQUEST);
			return $result;
		}
		$file = XINGCLOUD_RESOURCE_DIR."/locale/language/{$lang}/style.css";
		if(!file_exists($file)){
			$result = new XHeaderResult();		
			$result->setStatus(HTTP_NOT_FOUND);
			return $result;
		}
		import('module.service.result.XFileResult');
		import('util.io.XFile');
		$result = new XFileResult(new XFile($file));
		$result->setContentType('text/css');
		return $result;
	}
}
?>