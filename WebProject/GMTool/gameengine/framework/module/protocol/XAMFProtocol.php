<?php
import('module.protocol.XAbstractProtocol');
/**
 * XAMFProtocol
 * 
 * amf protocol
 * 
 * amf协议，供amf请求使用
 * 
 * @author Tianwei
 * @Protocol
 * @package protocol
 */
class XAMFProtocol extends XAbstractProtocol{
	protected $name = 'AMF';
	protected $gateway = 'amf';
	protected $exec;
	protected $filters;
	protected $actions;
	protected $amfGateway = null;
	
	/**
	 * construct method
	 * 
	 * 构造方法
	 */
	public function __construct(){
		define("PRODUCTION_SERVER", false);
		list($usec, $sec) = explode(" ", microtime());
		$amfphp['startTime'] = ((float)$usec + (float)$sec);
		include XINGCLOUD_PLUGIN_DIR."/amfphp/amf/app/Gateway.php";
		$this->amfGateway = new Gateway();
		$this->amfGateway->setClassPath(XINGCLOUD_PLUGIN_DIR);
		$this->amfGateway->setServicePath(XINGCLOUD_SERVICE_DIR);
		$this->amfGateway->setClassMappingsPath(XINGCLOUD_SERVICE_DIR);
		$this->amfGateway->setCharsetHandler("utf8_decode", "ISO-8859-1", "ISO-8859-1");
		$this->amfGateway->setErrorHandling(E_ALL ^ E_NOTICE);
		if(PRODUCTION_SERVER)
		{
			$this->amfGateway->disableDebug();
			$this->amfGateway->disableStandalonePlayer();
		}
		$this->amfGateway->enableGzipCompression(25*1024);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getRequest()
	 * @return XServiceRequest
	 */
	public function getRequest(){
		$amfObject = $this->getStream()->getInput();	
		if(!$amfObject){
			//TODO throw exceptions
			return null;
		}		
		$numBody = $amfObject->numBody();
		if($numBody == 0){
			//TODO throw exceptions
			return null;
		}
		global $amfphp;
		$amfphp['callTime'] = microtime_float();
		$amfBody = &$amfObject->getBodyAt(0);
		$specialHandling = $amfBody->getSpecialHandling();
		switch($specialHandling){
			case 'describeService':
				$request = parent::getRequest();
				import('module.service.discovery.XDiscoveryService');
				$request->setClassName('XDiscoveryService');
				$request->setMethodName('describeAMFService');
				$request->setParameters($this->getStream()->getInputParams());
				return $request;
			default:
				if($amfBody->className == 'DiscoveryService'){
					$request = parent::getRequest();					
					import('module.service.discovery.XAMFDiscoveryService');
					$request->setClassName('XAMFDiscoveryService');
					$request->setMethodName($amfBody->methodName);
					$request->setParameters($this->getStream()->getInputParams());
					return $request;				
				}
				$classPath = $amfBody->uriClassPath;
				if(!$classPath){				
					if(Headers::getHeader('serviceBrowser') != true){
						$request = parent::getRequest();
						import('module.service.discovery.XAMFDiscoveryService');
						$request->setClassName('XAMFDiscoveryService');
						$request->setMethodName('getServices');
						$request->setParameters($this->getStream()->getInputParams());
						return $request;
					}
					return null;
				}
				$classPath = preg_replace('/'.$amfBody->className.'\.php$/i', '', trim($classPath, '\\/'));												
				$classPath = preg_replace('/[\\/]+/i', __DS__, $classPath);
				$request = parent::getRequest();
				$request->setClassPath($classPath);
				$request->setClassName(x_name_to_java($amfBody->className).'Service');
				$request->setMethodName($amfBody->methodName);	
				$request->setParameters($this->getStream()->getInputParams());			
				return $request;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getStream()
	 * @return XAMFStream
	 */
	public function getStream(){
		if($this->stream){
			return $this->stream;
		}
		import('module.stream.XAMFStream');
		$this->stream = new XAMFStream();
		$this->stream->setAMFGateway($this->amfGateway);
		return $this->stream;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getDetails()
	 * @return string
	 */
	public function getDetails(){
		$params = $this->getStream()->getInputParams();
		if(empty($params)){
			return null;
		}
		x_array_sort_recursive($params, 'ksort');
		import('util.json.XJSONUtil');
		$json = XJSONUtil::singleton()->encode($params);
		return $json;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractProtocol::getHeader()
	 */
	public function getHeader($key){
		return Headers::getHeader($key);
	}	
}
?>