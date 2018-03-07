<?php
/**
 * XServiceAnnotation
 * 
 * Service Annotation Parser
 * 
 * service注释信息解析类
 * 
 * @author Tianwei
 * @final
 * @package service 
 */
final class XServiceAnnotation{
	private static $singletons = array();
	private $annotations = null;

	/**
	 * <b>construct method, will parse the annotation of the methods in the given class</b>
	 * 
	 * <b>构造方法, 会对给定的类的方法的注释进行解析</b>
	 * 
	 * <b>解析结果会放到XingCloudApp的contents中</b>
	 * 
	 * @param string $class 类名
	 */
	private function __construct($class) {
		$annotations = x_apc_file_fetch('serviceAnnotation_'.$class);
		if(is_array($annotations)){
			$this->annotations = $annotations;
			return;
		}
		$this->annotations = array();
		$annotation = XAnnotation::singleton($class);
		foreach($annotation->reflection()->getMethods() as $method){
			$this->_parse($annotation, $method);
		}
		if(x_is_prod()){
			x_apc_file_store('serviceAnnotation_'.$class, $this->annotations);
		}
	}
	
	/**
	 * <b>parse the annotation of a method</b>
	 * 
	 * <b>对给定方法的注释进行解析</b>
	 * 
	 * @param XAnnotation $annotation
	 * @param ReflectionMethod $method
	 */
	private function _parse($annotation, $method){
		$methodName = $method->getName();
		if(!preg_match('/^do[A-Z]/i', $methodName)){
			return;
		}
		$results = $annotation->method($methodName); 
		if(!is_array($results)){
			$results = array();
		}
		$results['description'] = '';
		$comments = $method->getDocComment();
		$lines = preg_split("/(\r?\n)/", $comments);
		foreach($lines as $line){
			if(preg_match('/^[\/\*\s]+$/i', $line)){
				continue;
			}
			if(!preg_match('/@\w+/i', $line)){
				$results['description'] .= preg_replace('/^[\/\*\s]+/i', '', $line);
				continue;
			}				
			if(preg_match('/@(\w+)\s+[^\s]+/i', $line, $matches)){
				if(!isset($results[$matches[1]])){
					$results[$matches[1]] = array();
				}
				$split = preg_split('/\s+/i', preg_replace('/^[\/\*\s]+/i', '', $line));
				unset($split[0]);
				$results[$matches[1]][] = $split;
			}
		}
		$methodName = preg_replace('/^do([A-Z])/e', "strtolower('\\1')", $method->getName());
		x_array_set($this->annotations, $methodName, $results);		
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServiceAnnotation
	 */
	public static function singleton($class) {
		if(is_object($class)){
			return self::singleton(get_class($class));
		}
		if (!isset(self::$singletons[$class])) {
			self::$singletons[$class] = new XServiceAnnotation($class);
		}
		return self::$singletons[$class];
	}

	/** 
	 * <b>get the annotation of the given method</b>
	 * 
	 * <b>获取给定方法的注释信息</b>
	 * 
	 * @return mixed
	 */
	public function get($key){
		return x_array_get($this->annotations, $key);
	}

	/** 
	 * <b>get all the annotations</b>
	 * 
	 * <b>获取所有注释信息</b>
	 * 
	 * @return array
	 */		
	public function serviceAnnotations(){
		return $this->annotations;
	}
	
	/** 
	 * <b>get the annotation of the given method</b>
	 * 
	 * <b>获取给定方法的注释信息</b>
	 * 
	 * @return mixed
	 */	
	public function service($key){
		return x_array_get($this->annotations, $key);
	}
}
?>