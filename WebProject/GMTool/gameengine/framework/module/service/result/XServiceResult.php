<?php
import('module.service.result.XAbstractResult');
import('module.service.XServiceRequest');
/**
 * XServiceResult
 * 
 * service result class
 * 
 * service结果类
 * 
 * @author Tianwei
 * @package service
 */
class XServiceResult extends XAbstractResult{
	protected $id;
	protected $code;
	protected $message;
	protected $data;
	protected $request = NULL;
	
	/**
	 * <b>construct method</b>
	 * 
	 * <b>默认构造方法</b>
	 * 
	 * @param int $code 错误代码
	 * @param string $message 错误信息
	 * @param mixed $data 具体数据
	 * @return XServiceResult
	 */
	function __construct($code = 200, $message = '', $data = array()){
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
	
	/** 
	 * <b>id setter</b>
	 * 
	 * <b>设置id属性的方法</b>
	 * 
	 * @param string $id
	 */
	public function setId($id){
		$this->id = $id;
	}
	
	/** 
	 * <b>request setter</b>
	 * 
	 * <b>设置request属性的方法</b>
	 * 
	 * @param XServiceRequest $request
	 */	
	public function setRequest(XServiceRequest $request){
		$this->request = $request;
	}
	/**
	 * <b>create a XServiceResult object with code=200 when success</b>
	 * 
	 * <b>成功时返回一个code=200的XServiceResult实例</b>
	 * 
	 * @static
	 * @param mixed $data
	 * @return XServiceResult
	 */
	public static function success($data = array()){
		return new XServiceResult(200, '', $data);
	} 
	
	/**
	 * <b>create a XServiceResult object with code=400 when client error occurs</b>
	 * 
	 * <b>客户端错误时返回一个code=400的XServiceResult实例</b>
	 * 
	 * @static
	 * @param mixed $data
	 * @return XServiceResult
	 */
	public static function clientError($message = '', $data = array()){
		return new XServiceResult(400, $message, $data);
	}
	
	/**
	 * <b>create a XServiceResult object with code=500 when server error occurs</b>
	 * 
	 * <b>服务器端错误时返回一个code=500的XServiceResult实例</b>
	 * 
	 * @static
	 * @param mixed $data
	 * @return XServiceResult
	 */
	public static function serverError($message = '', $data = array()){
		return new XServiceResult(500, $message, $data);
	}
	
	/**
	 * <b>convert the properties of XServiceResult object into an array</b>
	 * 
	 * <b>将XServiceResult对象的属性转化为数组形式</b>
	 * 
	 * <b>数组的格式为array('id'=>$id, 'code'=>$code, 'message'=>$message, 'data'=>$data)</b>
	 * 
	 * @return array
	 */
	public function asArray(){
		if(empty($this->id) && !empty($this->request)){
			$id = $this->request->getId();
			if($id != NULL){
				$this->id = intval($id);
			}
		}
		return array(
			'id'	=> $this->id,
			'code' => $this->code,
			'message' => $this->message,
			'data' => $this->formatData($this->data),
		);
	}
	
	public function dataAsArray(){
		return  $this->formatData($this->data);
	}
	
	/**
	 * <b>format the data property as an array</b>
	 * 
	 * <b>将data属性转化为数组形式</b>
	 * 
	 * <b>将会深度遍历$data属性，如果其中包含object，并且该object有asArray()方法，将会调用asArray()方法将其转化为数组</b>
	 * 
	 * @param mixed $data
	 * @return array
	 */
	private function formatData($data, $level=1){
		if(is_object($data)
			&& method_exists($data, 'asArray')){
			return $data->asArray();
		}elseif(!is_array($data)){
			return $data;
		}
		if($level>2){
			return $data;
		}
		$result = array();
		$level++;
		foreach($data as $key => $value){
			$result[$key] = $this->formatData($value, $level);
		}
		return $result;
	}
}
?>