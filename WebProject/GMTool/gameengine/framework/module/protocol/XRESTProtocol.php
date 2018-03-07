<?php
import('module.protocol.XAbstractProtocol');
/**
 * XRESTProtocol
 * 
 * rest protocol
 * 
 * rest协议，供rest请求使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XRESTProtocol extends XAbstractProtocol{
	protected $name = 'REST';
	protected $gateway = 'rest';
	
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
		$request = parent::getRequest();
		$request->setClassName($className);
		$request->setClassPath($classPath);
		$request->setMethodName($match[3]);
		$request->setParameters($this->getStream()->getInputParams());
		return $request;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getStream()
	 * @return mixed
	 */
	public function getStream(){
		if($this->stream){
			return $this->stream;
		}
		if(isset($_GET['format']) && strtolower($_GET['format']) == 'xml'){
			import('module.stream.XXMLStream');
			$this->setStream(new XXMLStream());
		}else{
			import('module.stream.XJSONStream');
			$this->setStream(new XJSONStream());
		}
		return $this->stream;		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getDetails()
	 * @return array
	 */
	public function getDetails(){
		$rawData = $this->getStream()->getInputRawData();
		if(!empty($rawData) && is_string($rawData) && ($rawData{0}=='{' || $rawData{0}=='<')){
			return $rawData;
		}
		return null;
	}
}
?>