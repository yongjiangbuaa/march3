<?php
import('module.stream.XAbstractStream');
/**
 * XAMFStream
 * 
 * amf stream
 * 
 * <b>amf数据流</b>
 * 
 * @author Tianwei
 * @package stream 
 */
class XAMFStream extends XAbstractStream{
	protected $amfObject = null;
	protected $isInputProcessed = false;
	protected $amfGateway = null;
	
	/**
	 * construce method
	 * 
	 * 构造方法
	 */
	public function __construct(){
		//Start NetDebug
		NetDebug::initialize();			
		error_reporting($GLOBALS['amfphp']['errorLevel']);
		$inputRawData = file_get_contents('php://input');
		if(!empty($inputRawData)){			
			$this->amfObject = new AMFObject($inputRawData);
		}
	}
	
	/**
	 * amfGateway setter
	 * 
	 * 设置amfGateway属性的方法
	 * 
	 * @param string $amfGateway
	 * @return XAMFStream
	 */
	public function setAMFGateway($amfGateway){
		$this->amfGateway = $amfGateway;
		return $this;
	}
	
	/**
	 * amfObject getter
	 * 
	 * 获取amfObject属性的方法
	 * 
	 * @return AMFObject
	 */
	public function getAMFObject(){
		return $this->amfObject;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInput()
	 */
	public function getInput(){
		if($this->isInputProcessed){
			return $this->amfObject;
		}
		if(empty($this->amfObject)){
			return null;
		}
		deserializationFilter($this->amfObject);
		$GLOBALS['amfphp']['actions'] = array(
			'adapter' => 'adapterAction',
		);
		batchProcessFilter($this->amfObject);
		$this->isInputProcessed = true;
		return $this->amfObject;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInputParams()
	 */
	public function getInputParams(){
		$this->getInput();
		if(!$this->amfObject){
			return array();
		}	
		$numBody = $this->amfObject->numBody();
		if($numBody == 0){
			return array();
		}
		$amfBody = $this->amfObject->getBodyAt(0);
		$params = $amfBody->getValue();
		if(empty($params)){
			return array();
		}
		$result = x_array_head($params);
		if(is_object($result)){
			return get_object_vars($result);
		}
		return $result;
	}
	
	/**
	 * output result data through amfphp plugin
	 * 
	 * 通过amfphp插件输出amf格式的数据给前台
	 * 
	 * @param string $output
	 */
	public function output($output){
		$this->getInput();
		if(!$this->amfObject){
			return false;
		}	
		$numBody = $this->amfObject->numBody();
		if($numBody == 0){
			return false;
		}	
		global $amfphp;
		if(isset($amfphp['callTime'])){					
			$amfphp['callTime'] += microtime_float() - $amfphp['callTime'];
		}
		$amfBody = &$this->amfObject->getBodyAt(0);
		$specialHandling = $amfBody->getSpecialHandling();
		switch($specialHandling){
			case 'RemotingMessage':
				$wrapper = new AcknowledgeMessage($amfBody->getMetadata("messageId"), 
												  $amfBody->getMetadata("clientId"));
				$wrapper->body = $output;
				$amfBody->setResults($wrapper);
				break;
			case 'Ping':
				$wrapper = new AcknowledgeMessage($amfBody->getMetadata("messageId"), 
												  $amfBody->getMetadata("clientId"));
				$amfBody->setResults($wrapper);
				$amfBody->responseURI = $amfBody->responseIndex . "/onResult";
				break;
			case 'pageRelease':
				$amfBody->setResults(true);
				$amfBody->setMetaData('type', 'boolean');
				$amfBody->responseURI = $amfBody->responseIndex . "/onResult";
				break;				
			default:
				$amfBody->setResults($output);
				break;
		}
		$amfBody->responseURI = $amfBody->responseIndex . "/onResult";
		debugFilter($this->amfObject);
		serializationFilter($this->amfObject);
		
		//Send content length header
		//Thanks to Alec Horley for pointing out the necessity
		//of this for FlashComm support
		header(AMFPHP_CONTENT_TYPE); // define the proper header
		
		$outputStream = $this->amfObject->outputStream;
		if(Headers::getHeader('serviceBrowser') == true)
		{
			//Add the total time header
			$toAddPos = strpos($outputStream, "\301\260\0\0\1\0\0\0");
			$time = (int) ((microtime_float() - $GLOBALS['amfphp']['startTime'])*1000);
			$b = pack("d", $time); // pack the bytes
			if (AMFPHP_BIG_ENDIAN) { // if we are a big-endian processor
				$r = strrev($b);
			} else { // add the bytes to the output
				$r = $b;
			}
			$outputStream = substr($outputStream, 0, $toAddPos) . $r . substr($outputStream, $toAddPos + 8);
		}
					
		//Send expire header, apparently helps for SSL
		//Thanks to Gary Rogers for that
		//And also to Lucas Filippi from openAMF list
		//And to Robert Reinhardt who appears to be the first who 
		//documented the bug
		//Finally to Gary who appears to have find a solution which works even more reliably
		$dateStr = date("D, j M Y ") . date("H:i:s", strtotime("-2 days"));
		header("Expires: $dateStr GMT");
		header("Pragma: no-store");
		header("Cache-Control: no-store");
			
		//else don't send any special headers at all

		if($this->amfGateway->outgoingMessagesFolder != NULL)
		{
			$mt = microtime();
			$pieces = explode(' ', $mt);
			file_put_contents($this->amfGateway->outgoingMessagesFolder . 
				'out.' . $pieces[1] . '.' . substr($pieces[0], 2) . ".amf", $outputStream);
		}
		
		$doCompress = false;
		$outputCompression = @ini_get("zlib.output_compression");
		if(!$outputCompression)
		{
			if(strlen($outputStream) > $this->amfGateway->_gzipCompressionThreshold  && 
			   extension_loaded("zlib") && 
			   strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE && 
			   $this->amfGateway->_enableGzipCompression)
			{
				$doCompress = true;
				ob_start();
				ob_start('ob_gzhandler');
			}
			else
			{
				header("Content-length: " . strlen($outputStream));
			}
		}
		
		print($outputStream); // flush the binary data
		
		if($doCompress)
		{
			ob_end_flush();
			header("Content-length: " . ob_get_length());
			ob_end_flush();
		}		
	}
}
?>