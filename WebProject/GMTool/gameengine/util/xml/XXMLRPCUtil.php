<?php
/**
 * XXMLRPCUtil
 * 
 * xml-rpc util
 * 
 * xml-rpc工具类
 * 
 * @author Wangqi
 * @package util.json
 */
class XXMLRPCUtil{
	private static $instance = null;
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XXMLRPCUtil
	 */	
	public static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * convert xml-rpc request to php array
	 * 
	 * 将xml-rpc request格式转换为PHP数组
	 * 
	 * @param string $xml
	 * @return array
	 */
	public function xmlRPCRequestToArray($xml){
		$request = array();
		$dom = simplexml_load_string($xml);
		$methodNode = $dom->xpath('/methodCall/methodName');
		if(empty($methodNode) || count($methodNode)==0){
			return array();
		}
		$request['method'] = strval($methodNode[0]);
		$params = $dom->xpath('/methodCall/params');
		if(empty($params) || count($params)==0 || empty($params[0]) || key($params[0])!='param'){
			return array();
		}
		foreach ($params[0] as $key => $paramNode){
			if(count($params[0])>1){
				$request['params'][] = $this->rpcParamNodeToArray($paramNode);
			}else{
				$request['params'] = $this->rpcParamNodeToArray($paramNode);
				break;
			}
		}
		return $request;
	}
	
	/**
	 * convert php array to xml-rpc response
	 * 
	 * 将PHP数组转换为xml-rpc response格式
	 * 
	 * @param array $array
	 * @param int $errorCode
	 * @return string
	 */
	public function arrayToXMLRPCResponse($array, $errorCode=200){
		if(empty($array) || count($array)==0 || !isset($array['code']) || !isset($array['message'])){
			return '<?xml version="1.0" encoding="utf-8"?>';
		}
		if($array['code']>$errorCode){
			return $this->xmlRPCFault($array['code'], $array['message']);
		}
		$xml = '<?xml version="1.0" encoding="utf-8"?><methodResponse><params><param><value>';
		$xml .= $this->constructRPCStruct($array);
		$xml .= '</value></param></params></methodResponse>';
		return $xml;
	}
	
	/**
	 * return a xml-rpc fault by specifying error code and message
	 * 
	 * 根据指定的错误代码和错误消息返回xml-rpc fault格式字符串
	 * 
	 * @param int $code
	 * @param string $message
	 * @return string
	 */
	public function xmlRPCFault($code, $message){
		return 
'<?xml version="1.0" encoding="utf-8"?>
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>'.$code.'</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>'.$message.'</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>';
	}
	
	private function rpcParamNodeToArray($paramNode){
		$array = array();
		$valueNode = $paramNode->xpath('value');
		if(!$this->validNode($valueNode)){
			return array();
		}
		$valueNode = $valueNode[0];
		if($this->isStructValue($valueNode)){
			$array = $this->rpcStructNodeToArray($valueNode);
		}elseif($this->isArrayValue($valueNode)){
			$array = $this->rpcArrayNodeToArray($valueNode);
		}else{
			$array[] = $this->getScalarValue($valueNode);
		}
		return $array;
	}
	
	private function rpcStructNodeToArray($structNode){
		$array = array();
		$members = $structNode->xpath('struct/member');
		if(!$this->validNode($members)){
			return array();
		}
		foreach ($members as $memberNode){
			$nameNode = $memberNode->xpath('name');
			if(!$this->validNode($nameNode)){
				return array();
			}
			$name = strval($nameNode[0]);
			$valueNode = $memberNode->xpath('value');
			if(!$this->validNode($valueNode)){
				return array();
			}
			$valueNode = $valueNode[0];
			if($this->isStructValue($valueNode)){
				$value = $this->rpcStructNodeToArray($valueNode);
			}elseif ($this->isArrayValue($valueNode)){
				$value = $this->rpcArrayNodeToArray($valueNode);
			}else{
				$value = $this->getScalarValue($valueNode);
			}
			$array[$name] = $value;
		}
		return $array;
	}
	
	private function rpcArrayNodeToArray($arrayNode){
		$array = array();
		$values = $arrayNode->xpath('array/data/value');
		if(!$this->validNode($values)){
			return array();
		}
		foreach ($values as $valueNode){
			if($this->isStructValue($valueNode)){
				$array[] = $this->rpcStructNodeToArray($valueNode);
			}elseif ($this->isArrayValue($valueNode)){
				$array[] = $this->rpcArrayNodeToArray($valueNode);
			}else{
				$array[] = $this->getScalarValue($valueNode);
			}
		}
		return $array;
	}
	
	private function isStructValue($node){
		$node = $node->xpath('struct');
		return !empty($node);
	}
	
	private function isArrayValue($node){
		$node = $node->xpath('array');
		return !empty($node);
	}
	
	private function validNode($node){
		return !empty($node)&&count($node)!=0&&!empty($node[0]);
	}
	
	private function getScalarValue($node){
		$value = '';
		foreach ($node as $type => $v){
			if($this->validRPCType($type)){
				$value = strval($v);
			}
			break;
		}
		return $value;
	}
	
	private function constructRPCStruct($array){
		$struct = '';
		$struct = '<struct>';
		foreach ($array as $key=>$value){
			$struct .= $this->constructRPCMember($key, $value);
		}
		$struct .= '</struct>';
		return $struct;
	}
	
	private function constructRPCMember($key, $value){
		$member = '';
		$member = '<member>';
		$member .= '<name>'.$key.'</name>';
		$member .= '<value>';
		if(is_array($value)){
			$member .= $this->constructRPCStruct($value);
		}else{
			$type = $this->toRPCType($value);
			$member .= "<$type>$value</$type>";
		}
		$member .= '</value>';
		$member .= '</member>';
		return $member;
	}
	
	private function toRPCType($value){
		$type = gettype($value);
		switch ($type) {
			case 'integer':{
				$rpcType = 'i4';
				break;
			}
			case 'NULL':{
				$rpcType = 'nil';
				break;
			}
			default:{
				$rpcType = $type;
				break;
			}
		}
		return $rpcType;
	}
	
	private function validRPCType($type){
		$supportedTypes = array(
			'i4', 'int', 'boolean', 'string', 'double', 'dateTime.iso8601', 'base64', 'nil'
		);
		return in_array($type, $supportedTypes);
	}
}