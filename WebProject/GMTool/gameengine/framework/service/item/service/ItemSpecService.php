<?php
/**
 * ItemSpecService
 * 
 * ItemSpec service
 * 
 * ItemSpec服务
 * 
 * @Pointcut('protocol|auth|cache')
 * @author Tianwei
 * @package item
 */
class ItemSpecService extends XAbstractService{
	/**
	 * <b>get ItemSpec xml</b>
	 * 
	 * <b>用户物品定义服务，为前台和GM系统提供用户物品基本属性的XML定义。</b>
	 * 
	 * <b>默认为按API名缓存。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache
	 * @Status(file='/locale/language/#lang#/item.xml')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam int version 文件版本，从xingcloud/status接口获取
	 * @ServiceParam string lang 文件语言
	 * @return XServiceResult
	 */
	public function doXml(XServiceRequest $request){
		$lang = $request->getParameter('data_lang');
		$default_file = '/item.xml';
		if (empty($lang)) $lang = 'cn';//TODO 调查中临时修改
		$file = ((empty($lang)?'':('/'.$lang))).'/item.xml';
		$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language'.$file;
		if(!is_file($fullPath)){
			$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language'.$default_file;
			if(!is_file($fullPath)){
				$result = new XHeaderResult();
				$result->setStatus(HTTP_NOT_FOUND);
				return $result;
			}
		}
		import('module.service.result.XFileResult');
		import('util.io.XFile');
		$result = new XFileResult(new XFile($fullPath));
		$result->setContentType('text/xml');
		return $result;
	}
}
?>