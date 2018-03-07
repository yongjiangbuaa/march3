<?php
/**
 * @Pointcut('protocol|auth|cache')
 */
class TextService extends XAbstractService{
	/**
	 * 多语言文本服务，为前台提供指定语言的文本。
	 * 默认返回XML文件，指定format参数为json时返回JSON格式。
	 * 默认按API参数缓存。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @Cache
	 * @Status(file='/locale/files/language/#lang#/lang.xml')
	 * @param XServiceRequest $request 服务请求
	 * @ServiceParam string lang 语言名
	 * @ServiceParam string format 返回格式(json/xml)
	 * @ServiceParam int version 文件版本，从xingcloud/status接口获取
	 * @return XServiceResult
	 */
	public function doGetAll(XServiceRequest $request){
		$lang = $request->getParameter('data_lang');
		if(!isset($lang)){
			$lang = 'cn';//TODO 临时修改
			//return $this->_error(400, 'param lang is required');
		}
		$file = XINGCLOUD_RESOURCE_DIR."/locale/language/{$lang}/lang.xml";
		if(!file_exists($file)){
			return $this->_error(400, 'file not exists');
		}
		$format = $request->getParameter('data_format');
		if(!isset($format)
			|| strtolower($format) == 'xml')
			{
			import('module.service.result.XFileResult');
			import('module.util.io.XFile');		
			$result = new XFileResult(new XFile($file));
			$result->setContentType('text/xml');
			return $result;
		}
		$xpath = '//texts';
    	$dom = simplexml_load_file($file);
		mb_internal_encoding("UTF-8");
		$texts = $dom->xpath($xpath);	
		$results = array();
		$results['texts'] = array();
		foreach($texts as $text){		
			foreach($text->children() as $node){
				$attributes = $node->attributes();
				$results['texts'][] = array(
					'key' => $node->getName(),
					'value' => (string) $attributes['text'],
				);
			}
		}
		return $this->_success($results);		
	}
}
?>