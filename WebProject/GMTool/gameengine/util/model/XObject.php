<?php
/**
 * XObject
 * 
 * object base class
 * 
 * 对象基类，提供基本方法
 * 
 * @author Tianwei
 * @package util.model
 */
class XObject{
	/** 
	 * construct method
	 * 
	 * 构造函数
	 */
	function __construct(){
	}
	
	/** 
	 * dynamic getter method
	 * 
	 * 动态getter方法，为Java命名风格。
	 * 
	 * 用来动态获取对象中属性，在使用$object->property失败时调用
	 * 
	 * 以property为name为例，会依次寻找getName,name,get方法，如果有则调用方法，如果都没有，则直接返回属性
	 * @param string $key 属性名
	 * @return mixed
	 */
	public function __get($key){
		$method = 'get'.ucfirst($key);
		if(method_exists($this, $method)){
			return $this->$method();
		}
		if(method_exists($this, $key)){
			return $this->$key();
		}
		if(method_exists($this, 'get')){
			return $this->get($key);
		}
		return $this->$key;
	}
	
	/** 
	 * dynamic setter method
	 * 
	 * 动态setter方法，为Java命名风格。
	 * 
	 * 用来动态设置对象中属性，在使用$object->property = $value失败时调用
	 * 
	 * 以property为name为例，会依次寻找setName,set方法，如果有则调用方法，如果都没有，则直接修改属性
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XObject
	 */
	public function __set($key, $value){
		$method = 'set'.ucfirst($key);
		if(method_exists($this, $method)){
			return $this->$method($value);
		}
		if(method_exists($this, 'set')){
			return $this->set($key, $value);
		}
		$this->$key = $value;
		return $this;
	}
	
	/** 
	 * dynamic call method
	 * 
	 * 动态调用getter和setter的方法，为Java命名风格。
	 * 
	 * getter：
	 * 
	 * 如果调用了一个不存在的getter方法，会自动返回这个getter所需要的属性值
	 * 
	 * 以property为name为例，即使没有声明getName方法，仍可以使用$object->getName()取得$object->name值
	 * 
	 * setter:
	 * 
	 * 同样，如果调用了一个不存在的setter方法，会自动返回这个setter所需要的属性值
	 * 
	 * 以property为name为例，即使没有声明setName方法，仍可以使用$object->setName($name)设置$object->name值为$name
	 * 
	 * @param string $method 方法名
	 * @param mixed $args 参数
	 * @return mixed
	 */	
	public function __call($method, $args){
		if(strlen($method) <= 3){
			import('module.context.XException');
			throw new XException('called undefined method '.$method.' of class '.get_class($this));
		}
		if(substr($method, 0, 3) == 'get'){
			$property = substr($method, 3);
			$property{0} = strtolower($property);
			return $this->__get($property);
		}
		if(substr($method, 0, 3) == 'set'
			&& count($args) > 0){
			$property = substr($method, 3);
			$property{0} = strtolower($property);
			return $this->__set($property, $args[0]);
		}
		import('module.context.XException');
		throw new XException('called undefined method '.$method.' of class '.get_class($this));
  	}
  	
	/** 
	 * batch setter method
	 * 
	 * 批量setter方法，将一个数组中的所有值赋给对象中相对应的属性
	 * 
	 * @param array $attrs 属性数组
	 * @return XObject
	 */
	public function setAttrs(array $attrs = array()){
		foreach($attrs as $key => $value){			
			$this->set($key, $value);
		}
		return $this;
	}

	/** 
	 * returns an array representation of the object
	 *
	 * 以数组形式返回对象属性信息，用于做动态加载
	 * 
	 * 典型的应用场景是持久化
	 * @return array
	 */
	public function attrs(){
		return get_object_vars($this);
	}
	
	/** 
	 * returns an array representation of the object
	 * 
	 * 以数组形式返回对象信息，用于做类似于序列化的对象数组转换
	 * 
	 * 典型的应用场景是向前台输出结果
	 * 
	 * 默认asArray()返回结果与attrs()一致
	 * 
	 * 如果需要自定义的输出结果，请实现asArray()方法
	 * @return array
	 */
	public function asArray(){
		return $this->attrs();
	}
	
	/**
	 * general setter
	 * 
	 * 通用setter方法，将$object->$key赋值为$value
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XObject
	 */
	public function set($key, $value){
		$this->$key = $value;
		return $this;
	}

	/**
	 * general getter
	 * 
	 * 通用getter方法，将返回$object->$key
	 * 
	 * @param string $key 属性名
	 * @return mixed
	 */
	public function get($key){
		return $this->$key;
	}
}
?>