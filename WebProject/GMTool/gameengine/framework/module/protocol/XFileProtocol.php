<?php
import('module.protocol.XRESTProtocol');
/**
 * XFileProtocol
 * 
 * file protocol
 * 
 * 文件协议，供文件获取接口使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XFileProtocol extends XRESTProtocol{
	protected $name = 'FILE';
	protected $gateway = 'file';
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getRequest()
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$uri = preg_replace("/^\/*{$this->gateway}\/+/i", '', $this->route);
		$uri = preg_replace("/\/+/i", '.', $uri);
		preg_match("/([\w\.]+)\.(\w+)\.(\w+)$/i", $uri, $match);
		if(!isset($match[3])){
			return false;
		}
		$className = x_name_to_java($match[2]).'Service';
		$classPath = strtr($match[1], '.', __DS__);
		import('module.service.XServiceRequest');
		$request = XServiceRequest::singleton();
		$request->setClassName($className);
		$request->setClassPath($classPath);
		$request->setMethodName($match[3]);
		$inputParams = $this->getStream()->getInputParams();
		if(!isset($inputParams['id']) || !isset($inputParams['info']) || !isset($inputParams['data'])){
			$request->setParameters($inputParams);
			if(isset($inputParams['id'])){
				$request->setId($inputParams['id']);
			}
		}else{
			$request->setParameters($inputParams);
		}
		return $request;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XRESTProtocol::getStream()
	 * @return XFileStream
	 */
	public function getStream(){
		if($this->stream){
			return $this->stream;
		}
		import('module.stream.XFileStream');
		$this->setStream(new XFileStream());
		return $this->stream;		
	}
}
?>