<?php
/**
 * XAOPFactory
 * 
 * AOP Factory Class
 * 
 * AOP工厂类，提供动态代理，AOP织入和调用
 * 
 * @author Tianwei
 * @package aop
 */
class XAOPFactory{
	private $singletons = array();
	private $batchSingletons = array();
	private $aspects = array();
	private static $instance = null;
	
	private function __construct() {
	}
	
	/** 
	 * XAOPFactory singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回XAOPFactory单例
	 * 
	 * @return XAOPFactory
	 */		
	public static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/** 
	 * dynamic proxy generator
	 * 
	 * 动态代理生成方法
	 * 
	 * 该方法将根据传入的类和参数生成动态代理，接管该类的调用，并织入Aspects
	 * 
	 * 返回动态代理对象
	 * 
	 * @param string $class 被代理的类名
	 * @param array $args 被代理的类构造参数
	 * @param boolean $singleton 要生成的动态代理是否是单例
	 * @return XAOPProxy
	 */
	public function get($class, $args = array(), $singleton = false){
		if($singleton){
			return $this->getSingleton($class, $args);
		}		
		import('util.annotation.XAnnotation');
		$pointcutAnnotation = XAnnotation::singleton($class)->get('Pointcut');
		if(!$pointcutAnnotation){
			import('module.aop.XAOPProxy');
			return new XAOPProxy(new $class($args));
		}
		$pointcutAnnotation = explode('|', $pointcutAnnotation);
		if(is_array($pointcutAnnotation)){
			foreach($pointcutAnnotation as $pointcut){
				$className = 'X'.ucfirst(trim($pointcut)).'Aspect';
				import('module.aop.'.$className);
				$this->addAspect($class, new $className());				
			}
		}
		import('module.aop.XAOPProxy');
		return new XAOPProxy($this->newInstance($class, $args));
	}
	
	/** 
	 * new instance generator
	 * 
	 * 新实例的通用生成方法
	 * 
	 * 返回生成的指定$class类对象
	 * 
	 * @param string $class 类名
	 * @param array $args 构造参数
	 * @return object
	 */
	public function newInstance($class, array $args = array()){
		$reflection = XAnnotation::singleton($class)->reflection();
		$constructor = $reflection->getConstructor();
		if(!$constructor){
			return new $class();
		}
		$parameters = $constructor->getParameters();
		if(empty($parameters)){
			return new $class();
		}
		return $reflection->newInstanceArgs($args);
	}
	
	/** 
	 * dynamic proxy singleton generator
	 * 
	 * 动态代理单例生成方法
	 * 
	 * 该方法将根据传入的类和参数生成动态代理单例，接管该类的调用，并织入Aspects
	 * 
	 * 返回动态代理单例
	 * 
	 * @param string $class 被代理的类名
	 * @param array $args 被代理的类构造参数
	 * @return XAOPProxy
	 */	
	public function getSingleton($class, $args = array()){
		if(isset($this->singletons[$class])){
			return $this->singletons[$class];
		}
		import('module.aop.XAOPProxy');
		$this->singletons[$class] = $this->get($class, $args);
		return $this->singletons[$class];
	}
	
	/** 
	 * dynamic proxy singleton generator
	 * 
	 * 动态批量代理单例生成方法
	 * 
	 * 该方法将根据传入的类和参数生成动态批量代理单例，接管该类的调用，并织入Aspects
	 * 
	 * 返回动态批量代理单例
	 * 
	 * @param string $class 被代理的类名
	 * @param array $args 被代理的类构造参数
	 * @return XAOPBatchProxy
	 */
	public function getBatchSingleton($class, $args = array()){
		if(isset($this->batchSingletons[$class])){
			return $this->batchSingletons[$class];
		}
		import('util.annotation.XAnnotation');
		$pointcutAnnotation = XAnnotation::singleton($class)->get('Pointcut');
		if(!$pointcutAnnotation){
			import('module.aop.XAOPProxy');
			$this->batchSingletons[$class] = new XAOPBatchProxy($this->newInstance($class, $args));
			return $this->batchSingletons[$class];
		}
		$pointcutAnnotation = explode('|', $pointcutAnnotation);
		if(is_array($pointcutAnnotation)){
			foreach($pointcutAnnotation as $pointcut){
				$className = 'X'.ucfirst(trim($pointcut)).'Aspect';
				import('module.aop.'.$className);
				$this->addAspect($class, new $className());				
			}
		}
		import('module.aop.XAOPBatchProxy');
		$this->batchSingletons[$class] = new XAOPBatchProxy($this->newInstance($class, $args));
		return $this->batchSingletons[$class];
	}
	
	/** 
	 * dynamic proxy wrapper
	 * 
	 * 将对象包装为动态代理的方法
	 * 
	 * 该方法将根据传入的类作为被代理实例生成动态代理
	 * 
	 * 返回动态代理
	 * 
	 * @param object $object 被代理的实例
	 * @return XAOPProxy
	 */	
	public function proxy($object){
		import('module.aop.XAOPProxy');
		return new XAOPProxy($object);
	}
	
	/** 
	 * dynamic proxy wrapper
	 * 
	 * 将对象包装为动态批量代理的方法
	 * 
	 * 该方法将根据传入的类作为被代理实例生成动态批量代理
	 * 
	 * 返回动态批量代理
	 * 
	 * @param object $object 被代理的实例
	 * @return XAOPBatchProxy
	 */
	public function batchProxy($object){
		import('module.aop.XAOPBatchProxy');
		return new XAOPBatchProxy($object);
	}
	
	/** 
	 * object method invoker
	 * 
	 * 调用被代理对象方法的函数
	 * 
	 * 该方法将根据传入的类实例和方法名，以及参数，进行函数调用
	 * 
	 * 返回调用结果
	 * 
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 参数数组
	 * @return XAOPBatchProxy
	 */
	public function invoke($object, $method, $args = array()){
		switch(count($args)){
			case 0:				
				return $object->$method();
			case 1:
				return $object->$method($args[0]);
			case 2:
				return $object->$method($args[0], $args[1]);
			case 3:
				return $object->$method($args[0], $args[1], $args[2]);
		}
		import('util.annotation.XAnnotation');
		$function = XAnnotation::singleton($object)->reflection()->getMethod($method);
		return $function->invokeArgs($object, $args);
	}
	
	/** 
	 * adding aspect executer to delegate class
	 * 
	 * 为被代理的类织入Aspect
	 * 
	 * 该方法将根据传入的类名，以及Aspect实例，织入Aspect
	 * 
	 * 返回XAOPFactory
	 * 
	 * @param string $class 类名
	 * @param object $aspect Aspect实例
	 * @return XAOPFactory
	 */	
	public function addAspect($class, $aspect){
		if(!isset($this->aspects[$class])){
			$this->aspects[$class] = array();
		}
		$this->aspects[$class][get_class($aspect)] = $aspect;
		return $this;
	}
	
	/** 
	 * executing aspects
	 * 
	 * 执行已经织入的aspects
	 * 
	 * 该方法将根据传入的类实例和方法名，找出该类对应的aspects，并按先后顺序执行
	 * 
	 * 如果没有aspects或者aspects执行完毕，调用invoke方法执行被代理对象的方法
	 * 
	 * 返回调用结果
	 * 
	 * @param object $object 类实例
	 * @param string $method 方法名
	 * @param array $args 参数数组
	 * @return mixed
	 */	
	public function execute($object, $method, $args = array()){
		$class = get_class($object);
		if(!isset($this->aspects[$class])){
			return $this->invoke($object, $method, $args);
		}
		$aspect = current($this->aspects[$class]);
		next($this->aspects[$class]);
		if(!$aspect){
			return $this->invoke($object, $method, $args);
		}
		$result = $aspect->execute($this, $object, $method, $args);
		reset($this->aspects[$class]);
		return $result;
	}
	
	/** 
	 * object method batch invoker
	 * 
	 * 批量调用被代理对象方法的函数
	 * 
	 * 该方法将根据传入的类实例和方法名数组，以及参数数组，批量进行函数调用
	 * 
	 * 返回调用结果数组，结果顺序与传入的方法名和参数的顺序一致
	 * 
	 * @param object $object 类实例
	 * @param string $batchMethods 方法名
	 * @param array $batchArgs 参数数组
	 * @return array
	 */	
	public function batchInvoke($object, $batchMethods, $batchArgs = array()){
		$results = array();
		$i = 0;	
		foreach($batchMethods as $batchMethod){
			$results[] = $this->invoke($object, $batchMethod, isset($batchArgs[$i]) ? $batchArgs[$i] : array());
			$i++;
		}
		return $results;		
	}	
	
	/** 
	 * batch executing aspects
	 * 
	 * 批量执行已经织入的aspects
	 * 
	 * 该方法将根据传入的类实例和方法名数组，分别找出该类对应的aspects，并按先后顺序批量执行
	 * 
	 * 每一个方法，如果没有aspects或者aspects执行完毕，调用invoke方法执行被代理对象的方法
	 * 
	 * 返回调用结果数组
	 * 
	 * @param object $object 类实例
	 * @param string $batchMethods 方法名
	 * @param array $batchArgs 参数数组
	 * @return mixed
	 */
	public function batchExecute($object, $batchMethods, $batchArgs = array()){
		$class = get_class($object);
		$results = array();
		if(!isset($this->aspects[$class])){	
			return $this->batchInvoke($object, $batchMethods, $batchArgs);
		}
		$aspect = current($this->aspects[$class]);
		next($this->aspects[$class]);
		if(!$aspect
			|| !method_exists($aspect, 'batchExecute')){
			return $this->batchInvoke($object, $batchMethods, $batchArgs);
		}
		$result = $aspect->batchExecute($this, $object, $batchMethods, $batchArgs);
		reset($this->aspects[$class]);
		return $result;
	}
}
?>