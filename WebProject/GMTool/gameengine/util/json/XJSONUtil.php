<?php
/**
 * XJSONUtil
 * 
 * xingcloud json util
 * 
 * json工具类
 * 
 * @author Wangqi
 * @package util.json
 */
class XJSONUtil{
	private static $instance = null;
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XJSONUtil
	 */	
	public static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * encode an array with non UTF-8 characters to JSON string
	 * 
	 * 将含有非UTF-8字符的数组编码成JSON字符串
	 * 
	 * @param array $array
	 */
	public function encode(Array $array){
		$json = '';
		return rawurldecode(json_encode(self::urlencodeArrayRecursive($array)));
	}
	
	private function urlencodeArrayRecursive(Array $array){
		$encodedArray = array();
		foreach ($array as $key=>$value){
			if(is_array($value)){
				$encodedArray[rawurlencode($key)] = self::urlencodeArrayRecursive($value);
			}else{
				if(is_null($value) || is_int($value)){
					$encodedArray[rawurlencode($key)] = $value;
				}else{
					$encodedArray[rawurlencode($key)] = rawurlencode($value);
				}
			}
		}
		return $encodedArray;
	}
	
}