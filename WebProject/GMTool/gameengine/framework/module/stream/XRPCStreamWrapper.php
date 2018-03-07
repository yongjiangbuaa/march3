<?php
import('module.stream.XAbstractStream');
/**
 * XRPCStreamWrapper
 * 
 * RPC stream wrapper
 * 
 * RPC数据流包装器
 * 
 * @author Tianwei
 * @package stream 
 */
class XRPCStreamWrapper extends XAbstractStream{
	protected $stream;
	/**
	 * construct method, set stream parameter
	 * 
	 * 构造方法，向stream属性注入XStream数据流
	 * 
	 * @param XStream $stream
	 */
	public function __construct(XStream $stream){
		$this->stream = $stream;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInput()
	 */
	public function getInput(){
		return $this->stream->getInput();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractStream::getInputParams()
	 */
	public function getInputParams(){
		$input = $this->stream->getInput();
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
	 * 输出格式统一为array('id' => 'xxx', 'result' => 'xxx', 'error' => 'xxx')
	 * 
	 * @param string $output
	 */
	public function output($output){
		$input = $this->stream->getInput();
		if(!empty($input['id'])){
			return $this->stream->output(array(
				'id' => $input['id'],
				'result' => $output,
				'error' => NULL,
			));
		}
		return false;
	}
}
?>