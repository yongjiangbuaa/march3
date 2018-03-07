<?php
/**
 * XAnnotation
 * 
 * Annotation Holder Class
 * 
 * 含有类注释信息的类
 * 
 * @author Tianwei
 * @final
 * @package util.annotation
 */
final class XAnnotation{
	private static $singletons = array();
	private static $parser = null;
	private $className = null;
	private $annotations = array();
	private $reflection = null;

	private function __construct($class){
		$this->className = $class;
		$annotations = x_apc_file_fetch('annotation_'.$class);
		if(is_array($annotations)){
			$this->annotations = $annotations;
			return;
		}
		$comment = $this->reflection()->getDocComment();
		$this->annotations['class'] = $this->_parse($comment);
		$this->annotations['method'] = array();	
		foreach($this->reflection()->getMethods() as $method){
			x_array_set($this->annotations['method'], $method->getName(), $this->_parse($method->getDocComment()));
		}
		$this->annotations['property'] = array();	
		foreach($this->reflection()->getProperties() as $property){
			$propertyName = $property->getName();
			x_array_set($this->annotations['property'], $propertyName, $this->_parse($property->getDocComment()));
			if($property->getModifiers() != 1024){
				continue;
			}
			x_array_set($this->annotations['property'], $propertyName.'.modifiers', 1024, true);
		}
		$this->annotations['propertyNames'] = array_keys($this->annotations['property']);
		if(x_is_prod()){
			x_apc_file_store('annotation_'.$class, $this->annotations);
		}		
	}
	
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回XAnnotation实例，每个类对应的实例有且仅有一个
	 * 
	 * @return XAnnotation
	 */
	public static function singleton($class) {
		if (!isset(self::$singletons[$class])) {
			self::$singletons[$class] = new XAnnotation($class);
		}
		return self::$singletons[$class];
	}

	/** 
	 * reflection getter method
	 * 
	 * 反射类getter方法
	 * 
	 * 该方法会返回$this->className对应的ReflectionClass实例
	 * 
	 * @return ReflectionClass
	 */
	public function reflection(){
		if(!$this->reflection){			
			$this->reflection = new ReflectionClass($this->className);
		}
		return $this->reflection;
	}
	
	/** 
	 * class annotation getter (by annotation name) method
	 * 
	 * 类头部注释getter方法，按传入的$name返回对应的类头部
	 * 
	 * @param string $name 类注释名
	 * @return mixed
	 */
	public function get($name){
		$annotations = $this->annotations();
		return x_array_get($annotations, $name);
	}
	
	/** 
	 * class annotation getter method
	 * 
	 * 类头部注释getter方法，返回所有类头部注释
	 * 
	 * @return array
	 */
	public function annotations(){
		return $this->annotations['class'];
	}
	
	/** 
	 * method annotation getter method
	 * 
	 * 方法注释getter方法，返回所有方法注释
	 * 
	 * @return array
	 */
	public function methodAnnotations(){
		return $this->annotations['method'];
	}
	
	/** 
	 * method annotation getter (by method name) method
	 * 
	 * 方法注释getter方法，按传入的$method返回对应的方法注释
	 * 
	 * @param string $method 方法名
	 * @return mixed
	 */
	public function method($method){
		$methodAnnotations = $this->methodAnnotations();
		return x_array_get($methodAnnotations, $method);
	}
	
	/** 
	 * property annotation getter method
	 * 
	 * 属性注释getter方法，返回所有属性注释
	 * 
	 * @return array
	 */		
	public function propertyAnnotations(){
		return $this->annotations['property'];
	}
	
	/** 
	 * return a list of all the property names 
	 * 
	 * 返回$this->className对应类的所有属性名
	 * 
	 * @return array
	 */		
	public function propertyNames(){
		return $this->annotations['propertyNames'];
	}
	
	/** 
	 * property annotation getter (by property name) method
	 * 
	 * 属性注释getter方法，按传入的$property返回对应的属性注释
	 * 
	 * @param string $property 属性名
	 * @return mixed
	 */
	public function property($property){
		$propertyAnnotations = $this->propertyAnnotations();
		return x_array_get($propertyAnnotations, $property);
	}
	
	/** 
	 * return if a property is private 
	 * 
	 * 返回$property对应属性是否为私有属性
	 * 
	 * @param string $property 属性名
	 * @return array
	 */
	public function isPropertyPrivate($property){
		$propertyAnnotations = $this->propertyAnnotations();
		return x_array_fast_get($propertyAnnotations, $property, 'modifiers') == 1024;
	}
	
	/** 
	 * return if a property named $property has annotation named $annotation
	 * 
	 * 返回$property对应属性是否含有名为$annotation的注释
	 * 
	 * @param string $property 属性名
	 * @param string $annotation 属性注释名
	 * @return array
	 */
	public function hasPropertyAnnotation($property, $annotation){
		$propertyAnnotation = $this->property($property);
		if(empty($propertyAnnotation)){
			return false;
		}
		$result = x_array_get($propertyAnnotation, $annotation);
		if(empty($result)
			|| $result === FALSE){
			return false;
		}
		return true;
	}
	
	private function _parse($comment){
		if(!self::$parser){			
			import('util.annotation.XAnnotationParser');
			self::$parser = new XAnnotationParser();
		}
		return self::$parser->parse($comment);		
	}
}
?>