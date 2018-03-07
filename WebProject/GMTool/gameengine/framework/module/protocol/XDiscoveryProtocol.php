<?php
import('module.protocol.XAbstractProtocol');
/**
 * XDiscoveryProtocol
 * 
 * discovery protocol
 * 
 * discovery协议，供API发现服务使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XDiscoveryProtocol extends XAbstractProtocol{
	protected $name = 'DISCOVERY';
	protected $gateway = 'discovery';
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getRequest()
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$request = parent::getRequest();
		if(preg_match("/^\/*discovery\/+admin/i", $this->route)){
			import('module.service.discovery.XAdminDiscoveryService');
			$request->setClassName('XAdminDiscoveryService');
		}elseif(preg_match("/^\/*discovery\/+jsonrpc/i", $this->route)){
			import('module.service.discovery.XRPCDiscoveryService');
			$request->setClassName('XRPCDiscoveryService');
		}elseif(preg_match("/^\/*discovery\/+json/i", $this->route)){
			import('module.service.discovery.XDiscoveryService');
			$request->setClassName('XDiscoveryService');
		}elseif(preg_match("/^\/*discovery\/+action/i", $this->route)){
			import('module.service.discovery.XActionDiscoveryService');
			$request->setClassName('XActionDiscoveryService');
		}
		$request->setMethodName('doGetServices');
		$request->setParameters($this->getStream()->getInputParams());
		return $request;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getStream()
	 * @return XJSONStream
	 */
	public function getStream(){
		if($this->stream){
			return $this->stream;
		}
		import('module.stream.XJSONStream');
		$this->setStream(new XJSONStream());
		return $this->stream;
	}
}
?>