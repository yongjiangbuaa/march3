<?php
import('module.response.XResponse');
import('util.http.XServletResponse');
/**
 * XServiceResponse
 * 
 * service response class, implements XResponse interface
 * 
 * 包装service的response类，实现XResponse接口
 * 
 * @author Tianwei
 * @package service 
 */
class XServiceResponse implements XResponse{	
	private static $instance = null;
	private $result = null;
	protected $servletResponse = null;
	protected $stream = null;
	
	/**
	 * <b>construct method</b>
	 * 
	 * <b>构造方法</b>
	 */
	private function __construct(){
	
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServiceResponse
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/** 
	 * <b>result setter</b>
	 * 
	 * <b>设置result属性的方法</b>
	 * 
	 * @param XResult $result
	 * @return XServiceResponse
	 */	
	public function setResult($result){
		$this->result = $result;
		return $this;
	}
	
	/** 
	 * <b>result getter</b>
	 * 
	 * <b>获取result属性的方法</b>
	 * 
	 * @return XResult
	 */	
	public function getResult(){
		return $this->result;
	}
	
	/** 
	 * <b>stream setter</b>
	 * 
	 * <b>设置stream属性的方法</b>
	 * 
	 * @param XStream $stream
	 * @return XServiceResponse
	 */	
	public function setStream(XStream $stream){
		$this->stream = $stream;
		return $this;
	}
	
	/** 
	 * <b>stream getter</b>
	 * 
	 * <b>获取stream属性的方法</b>
	 * 
	 * @return XStream
	 */	
	public function getStream(){
		return $this->stream;
	}	
	
	/** 
	 * <b>servletResponse getter</b>
	 * 
	 * <b>获取servletResponse属性的方法</b>
	 * 
	 * @return XServletResponse
	 */	
	function getServletResponse(){
		return $this->servletResponse;
	}
	
	/** 
	 * <b>servletResponse setter</b>
	 * 
	 * <b>设置servletResponse属性的方法</b>
	 * 
	 * @param XServletResponse $servletResponse
	 * @return XServiceResponse
	 */	
	function setServletResponse(XServletResponse $servletResponse){
		$this->servletResponse = $servletResponse;
		return $this;
	}
	
	/**
	 * <b>output the result, will invoke the output() method of XStream object</b>
	 * 
	 * <b>输出结果给前台，会调用XStream对象的output()方法</b>
	 * 
	 * <b>输出格式支持json, xml, jsonrpc, xmlrpc等，由XStream对象的类型决定</b>
	 * 
	 * @see XResponse::output()
	 */
	public function output(){
		import('module.service.result.XServiceResult');
		import('module.service.result.XMultiResult');
		import('module.service.result.XFileResult');
		import('module.service.result.XHeaderResult');
		$result = $this->getResult();
		//file_put_contents('/data/htdocs/ifadmin/admin/outputYao.log', print_r($result,true)."\n",FILE_APPEND);
		if(!is_object($result) && !is_array($result)){
			return false;
		}
		if($result instanceof XHeaderResult){
			$status = $result->getStatus();
			if(isset(XHeaderResult::$HEADERS[$status])){
				header(XHeaderResult::$HEADERS[$status]);
			}
			return true;
		}
		if($result instanceof XFileResult){
			import('module.stream.XFileStream');
			$stream = new XFileStream();
			$stream->output($result);
			return true;
		}
		if(!$this->stream){		
			return false;	
		}
		if(!is_object($result)){
			return $this->stream->output($result);
		}
		if(method_exists($result, 'asArray')){
			return $this->stream->output($result->asArray());
		}
		return $this->stream->output($result);			
	}
}
?>