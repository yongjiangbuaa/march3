<?php
/**
 * @Pointcut('protocol|auth|cache')
 */
class LanguageService extends XAbstractService{
	/**
	 * 多语言列表服务，为前台提供目前支持的多语言列表。
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Auth(type='http')
	 * @param XServiceRequest $request 服务请求
	 * @return XServiceResult
	 */	
	public function doGetAll(XServiceRequest $request){
		import('util.io.XFile');
		$dir = new XFile(XINGCLOUD_RESOURCE_DIR."/locale/language");
		if(!$dir->isDirectory()){
			return $this->_error(400, "language not inited");
		}
		$files = $dir->listFiles();
		$results = array();
		foreach($files as $file){
			if($file->isDirectory()){
				$results[] = $file->getName();
			}
		}
		return $this->_success($results);		
	}
}
?>