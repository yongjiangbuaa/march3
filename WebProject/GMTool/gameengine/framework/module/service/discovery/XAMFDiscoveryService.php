<?php
import('module.service.discovery.XDiscoveryService');
import('plugin.amfphp.DiscoveryService');
/**
 * XAMFDiscoveryService
 * 
 * discovery all the existing amf services
 * 
 * amf API发现服务，发现已有的amf API
 * 
 * @author Tianwei
 * @Pointcut('auth|cache')
 * @package service
 */
class XAMFDiscoveryService extends XAbstractService{
	/**
	 * <b>discovery all the amf API, return the API list</b>
	 * 
	 * <b>amf API发现服务，返回amf API列表。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Cache(policy='application')
	 * @param XServiceRequest $request 服务请求
	 * @return array
	 */	
	public function doGetServices(XRequest $request){		
		$service = new DiscoveryService();
		$results = $service->getServices();
		return $results;
	}
	
	/**
	 * <b>describe all the amf API, return the API description</b>
	 * 
	 * <b>amf API描述服务，返回amf API描述信息。</b>
	 * 
	 * @Protocol(allow='ANY')
	 * @Method(allow='ANY')
	 * @Cache(policy='application')
	 * @param XServiceRequest $request 服务请求
	 * @return array
	 */	
	public function doDescribeService(XRequest $request){			
		$service = new DiscoveryService();
		$results = $service->describeService($request->getParameters());
		return $results;
	}
}
?>