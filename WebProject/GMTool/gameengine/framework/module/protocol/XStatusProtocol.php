<?php
import('module.protocol.XAbstractProtocol');
/**
 * XStatusProtocol
 * 
 * status protocol
 * 
 * status协议，供状态请求接口使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XStatusProtocol extends XAbstractProtocol{
	protected $name = 'STATUS';
	protected $gateway = 'status';
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getRequest()
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$request = parent::getRequest();
		if(preg_match("/^\/*status\/+admin/i", $this->route)){
			import('module.service.status.XAdminStatusService');
			$request->setClassName('XAdminStatusService');
		}else{
			import('module.service.status.XStatusService');
			$request->setClassName('XStatusService');
		}
		$request->setMethodName('doStatus');
		$inputParams = $this->getStream()->getInputParams();
		if(!isset($inputParams['id']) || !isset($inputParams['info']) || !isset($inputParams['data'])){
			$request->setParameters($this->getStream()->getInputParams());
			if(isset($inputParams['id'])){
				$request->setId($inputParams['id']);
			}
		}else{
			$request->setParameters($this->getStream()->getInputParams());
		}
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