<?php
/**
 * @Pointcut('protocol|auth|cache')
 */
class FontService extends XAbstractService{
	/**
	 * 多语言字体服务，为前台提供指定语言的字体文件fonts.swf。
	 * 默认按API参数缓存。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache
	 * @Status(file='/locale/files/language/#lang#/fonts.swf')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string lang 指定语言名
	 * @ServiceParam int version 文件版本，从xingcloud/status接口获取
	 * @return XServiceResult
	 */
	public function doGet(XServiceRequest $request){
		$lang = $request->getParameter('data_lang');
		if(!isset($lang)){
			$result = new XHeaderResult();		
			$result->setStatus(HTTP_BAD_REQUEST);
			return $result;
		}
		$file = XINGCLOUD_RESOURCE_DIR."/locale/language/{$lang}/fonts.swf";
		if(!file_exists($file)){
			$result = new XHeaderResult();		
			$result->setStatus(HTTP_NOT_FOUND);
			return $result;
		}
		import('module.service.result.XFileResult');
		import('util.io.XFile');
		$result = new XFileResult(new XFile($file));
		$result->setContentDispotion('attachment');
		$result->setContentType('application/x-shockwave-flash');
		return $result;
	}
}
?>