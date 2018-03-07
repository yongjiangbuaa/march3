<?php
import('module.service.result.XAbstractResult');
/**
 * XMultiResult
 * 
 * multi result class, may contain more than one result, used as action result
 * 
 * 包含多个结果的结果类，action操作完成后返回这种类型的结果
 * 
 * @author Tianwei
 * @package service
 */
class XMultiResult extends XAbstractResult{
	protected $id = null;
	protected $code = NULL;
	protected $message = NULL;
	protected $data = array();
	
	function __construct($id = 1, $code = 200, $message = ''){
		$this->id = $id;
		$this->code = $code;
		$this->message = $message;	
	}
	
	/** 
	 * <b>id getter</b>
	 * 
	 * <b>获取id属性的方法</b>
	 * 
	 * @return int
	 */	
	public function getId(){
		return $this->id;
	}
	
	/** 
	 * <b>id setter</b>
	 * 
	 * <b>设置id属性的方法</b>
	 * 
	 * @param int $id
	 * @return XMultiResult
	 */	
	public function setId($id){
		$this->id = $id;
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
	 * @return XMultiResult
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
	 * @return XMultiResult
	 */	
	public function setMessage($message){
		$this->message = $message;
		return $this;
	}

	/**
	 * <b>add a XResult object to the results array</b>
	 * 
	 * <b>添加一个XResult对象到results数组中</b>
	 * 
	 * @param XResult $result
	 */
	public function addResult(XResult $result){
		$this->data[] = $result;
	}

	/** 
	 * <b>results getter</b>
	 * 
	 * <b>获取results属性的方法</b>
	 * 
	 * @return array
	 */	
	public function getResults(){
		return $this->data;
	}
	
	/** 
	 * <b>results setter</b>
	 * 
	 * <b>设置results属性的方法</b>
	 * 
	 * @param array $results
	 * @return XMultiResult
	 */
	public function setResults($results){
		$this->data = $results;
		return $this;
	}
	
	/**
	 * <b>convert the properties of XMultiResult object into an array</b>
	 * 
	 * <b>将XMultiResult对象的属性转化为数组形式</b>
	 * 
	 * <b>数组的格式为array('id'=>$id, 'results'=>$results)</b>
	 * 
	 * @return array
	 */
	public function asArray(){
		$results = array();
		foreach($this->data as $result){
			$results[] = $result->asArray();
		}
		return array(
			'id' => $this->id,
			'code' => $this->code,
			'message' => $this->message,
			'data' => $results,
		);
	}
}
?>