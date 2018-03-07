<?php
/**
 * XActionResult
 * 
 * action result class, extends XAbstractResult class
 * 
 * 包装action处理结果的result类，继承XAbstractResult抽象类
 * 
 * @author Tianwei
 * @package action 
 */
class XActionResult extends XAbstractResult{
	protected $index;
	protected $code;
	protected $message;
	protected $result;
	
	/**
	 * <b>construct method</b>
	 * 
	 * <b>默认构造方法</b>
	 * 
	 * @param int $code 错误代码
	 * @param string $message 错误信息
	 * @param mixed $data 具体数据
	 * @return XActionResult
	 */
	function __construct($code = 200, $message = '', $data = array()){
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
		
	/** 
	 * <b>index getter</b>
	 * 
	 * <b>获取index属性的方法</b>
	 * 
	 * @return int
	 */	
	public function getIndex(){
		return $this->index;
	}
	
	/** 
	 * <b>index setter</b>
	 * 
	 * <b>设置index属性的方法</b>
	 * 
	 * @param int $index
	 * @return XActionResult
	 */	
	public function setIndex($index){
		$this->index = $index;
		return $this;
	}
	
	/** 
	 * <b>code getter</b>
	 * 
	 * <b>获取code属性的方法</b>
	 * 
	 * @return int
	 */	
	public function getCode(){
		return $this->code;
	}
	
	/** 
	 * <b>code setter</b>
	 * 
	 * <b>设置code属性的方法</b>
	 * 
	 * @param int $code
	 * @return XActionResult
	 */	
	public function setCode($code){
		$this->code = $code;
		return $this;
	}
	
	/** 
	 * <b>message getter</b>
	 * 
	 * <b>获取message属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getMessage(){
		return $this->message;
	}
	
	/** 
	 * <b>message setter</b>
	 * 
	 * <b>设置message属性的方法</b>
	 * 
	 * @param string $message
	 * @return XActionResult
	 */	
	public function setMessage($message){
		$this->message = $message;
		return $this;
	}
	
	/**
	 * <b>result setter</b>
	 * 
	 * <b>设置result属性的方法</b>
	 * 
	 * @param XResult $result
	 */
	public function setResult(XResult $result){
		$this->result = $result;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XObject::asArray()
	 */
	public function asArray(){
		return array(
			'index' => $this->index,
			'code' => $this->code,
			'message' => $this->message,
			'data' => $this->result->dataAsArray(),
		);
	}
}
?>