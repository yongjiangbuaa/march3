<?php
import('module.stream.XStream');
/**
 * XAbstractStream
 * 
 * abstract stream class
 * 
 * <b>数据流抽象类，实现XStream接口</b>
 * 
 * @author Tianwei
 * @abstract
 * @package stream 
 */
abstract class XAbstractStream implements XStream{
	protected $contentType = 'text/html';
	protected $charset = 'utf-8';
	protected $clientCache = FALSE;
	protected $gzip = false;
	
	public function __construct(){
		import('util.http.XServletRequest');
		$gzip = XServletRequest::singleton()->header('Content-Encoding');
		if(!empty($gzip)){
			$this->gzip = true;
		}
	}
	
	/**
	 * contentType setter
	 * 
	 * 设置contentType属性，值会在输出时赋给HTTP头信息中的Content-Type属性
	 * 
	 * @param string $contentType
	 */
	public function setContentType($contentType){
		$this->contentType = $contentType;
		return $this;
	}
	
	/**
	 * charset setter
	 * 
	 * 设置charset属性，值会在输出时赋给HTTP头信息中的charset属性
	 * 
	 * @param string $charset
	 */
	public function setCharset($charset){
		$this->charset = $charset;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XStream::getInputRawData()
	 */
	public function getInputRawData(){
		$rawInput = file_get_contents('php://input');
		if($this->gzip){
			$rawInput = gzuncompress($rawInput);
		}
		return $rawInput;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XStream::getInput()
	 * @return array
	 */
	public function getInput(){
		return array();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XStream::getInputParams()
	 * @return array
	 */
	public function getInputParams(){
		return $this->getInput();
	}
	
	/**
	 * output the result data
	 * 
	 * 输出结果给前台，会设置HTTP头中的Content-type，charset以及缓存信息等
	 * 
	 * 默认Content-type是text/html，默认charset是utf-8
	 * 
	 * @param string $output
	 */
	public function output($output){
		if($this->clientCache){
			header ("Last-Modified: Tue, 01 Mar 2011 00:00:00 +0000"); 
			header ("Expires: Tue, 01 Mar 2050 00:00:00 +0000");
			header ("Cache-Control: max-age=315360000");
		}else{
			header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
			header("Pragma: no-cache");
			header("Cache-Control: no-cache, must-revalidate");
		}
		header("Content-type: {$this->contentType}; charset={$this->charset}");
		return $this->outputRawData($output);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XStream::outputRawData()
	 */
	public function outputRawData($rawData){
		if($this->gzip){
// 			$rawData = gzcompress($rawData);
		}
		//file_put_contents('/data/htdocs/ifadmin/admin/lastResultYao.log', print_r($rawData,true)."\n",FILE_APPEND);
		return file_put_contents('php://output', $rawData);
	}
	
	/**
	 * gzip getter
	 * 
	 * 获取gzip属性的方法
	 * 
	 * @return boolean
	 */
	public function getGzip(){
		return $this->gzip;
	}
	
	/**
	 * gzip setter
	 *
	 * 设置gzip属性的方法
	 *
	 * @return boolean
	 */
	public function setGzip($val){
		return $this->gzip = $val;
	}
}
?>