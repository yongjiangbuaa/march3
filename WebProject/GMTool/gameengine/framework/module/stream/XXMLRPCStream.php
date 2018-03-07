<?php
import('module.stream.XAbstractStream');
import('util.xml.XXMLRPCUtil');
/**
 * XXMLRPCStream
 * 
 * XML RPC stream
 * 
 * <b>XML RPC格式数据流</b>
 * 
 * @author Tianwei
 * @package stream 
 */
class XXMLRPCStream extends XAbstractStream{
	protected $input = array();
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInput()
	 */
	public function getInput(){
		if(!empty($this->input)){
			return $this->input;
		}
		import('util.http.XServletRequest');
		$request = XServletRequest::singleton();
		$this->input = XXMLRPCUtil::singleton()->xmlRPCRequestToArray($this->getInputRawData());
		return $this->input;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInputParams()
	 */
	public function getInputParams(){
		$input = $this->getInput();
		if(!isset($input['params'])){
			return array();
		}
		return $input['params'];
	}
	
	/**
	 * output the result data
	 * 
	 * 输出结果给前台
	 * 
	 * 使用utf-8编码，Content-type为text/xml
	 * 
	 * @param string $output
	 */
	public function output($output){
		$this->setContentType('text/xml');
		$this->setCharset('utf-8');
		$output = XXMLRPCUtil::singleton()->arrayToXMLRPCResponse($output);
		return parent::output($output);
	}
}
?>