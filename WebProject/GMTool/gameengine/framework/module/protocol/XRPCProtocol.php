<?php
import('module.protocol.XAbstractProtocol');
/**
 * XRPCProtocol
 * 
 * rpc protocol
 * 
 * rpc协议，供rpc请求使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XRPCProtocol extends XAbstractProtocol{
	protected $name = 'RPC';
	protected $gateway = '\w+rpc';
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getRequest()
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$uri = preg_replace("/^\/*{$this->gateway}\/+/i", '', $this->route);
		$uri = preg_replace("/\/+/i", '.', $uri);
		preg_match("/([\w\.]+)\.(\w+)$/i", $uri, $match);
		if(!isset($match[2])){
			return false;
		}
		$className = x_name_to_java($match[2]).'Service';		
		$classPath = strtr($match[1], '.', __DS__);
		$input = $this->getStream()->getInput();
		if(!isset($input['method'])){
			import('module.context.XException');
			throw new XException('unknown method or incorrect parameters', 400);
		}
		$request = parent::getRequest();
		$request->setClassName($className);
		$request->setClassPath($classPath);
		$request->setMethodName($input['method']);
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
		import('module.stream.XRPCStreamWrapper');
		if(preg_match("/^\/*jsonrpc\/+/i", $this->route)){
			import('module.stream.XJSONStream');
			$this->setStream(new XRPCStreamWrapper(new XJSONStream()));
		}else{
			import('module.stream.XXMLRPCStream');
			$this->setStream(new XXMLRPCStream());
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
		if(!empty($rawData) && is_string($rawData) && ($rawData{0} == '{' || $rawData{0} == '<')){
			return $rawData;
		}
		return null;
	}
}
?>