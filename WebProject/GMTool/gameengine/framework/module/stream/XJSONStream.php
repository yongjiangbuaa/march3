<?php
import('module.stream.XAbstractStream');
/**
 * XJSONStream
 * 
 * json stream
 * 
 * <b>json格式数据流</b>
 * 
 * @author Tianwei
 * @package stream 
 */
class XJSONStream extends XAbstractStream{
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInput()
	 */
	public function getInput(){
		import('util.http.XServletRequest');
		$request = XServletRequest::singleton();
		$params = $request->gets();
		if(!$request->isPost()){			
			return $params;
		}
		$rawData = $this->getInputRawData();
		if(!$rawData){
			return $params;
		}
		$data = array();
		if($rawData{0} == '{'){
			$data = @json_decode($rawData, true);			
		}
		if(empty($data)){
			$data = $request->posts();
		}
		if(is_array($data) || is_object($data)){
			$data = (array) $data;				
		    foreach($data as $key => $value){
		    	$params[$key] = $value;
		    }
		}
		return $params;	
	}
	
	/**
	 * output the result data in json format
	 * 
	 * 输出json格式的结果给前台
	 * 
	 * 使用utf-8编码，Content-type为text/javascript
	 * 
	 * @param string $output
	 */
	public function output($output){
		$this->setContentType('text/javascript');
		$this->setCharset('utf-8');
		return parent::output(json_encode($output));
	}
}
?>