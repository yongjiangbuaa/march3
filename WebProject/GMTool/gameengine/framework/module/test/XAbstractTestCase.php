<?php
import('module.test.XTestCase');
import('util.annotation.XAnnotation');
/**
 * XAbstractTestCase
 * 
 * abstract testcase class
 * 
 * testcase抽象类，实现XTestCase接口
 * 
 * @author Wangqi
 * @abstract
 * @package test 
 */
abstract class XAbstractTestCase implements XTestCase{
	protected $tests = array();
	protected $countTestCases = 0;
	protected $passedTests = array();
	protected $failedTests = array();
	
	/**
	 * construct method
	 * 
	 * 构造函数
	 * 构造函数运行时会调用XAnnotation来解析当前类自身所含有的testcase，并将结果保存到$this->tests数组中
	 */
	public function __construct(){
		$methods = XAnnotation::singleton(get_class($this))->reflection()->getMethods();
		foreach($methods as $method){
			$name = $method->getName();
			if(!preg_match("/^test[A-Z]/i", $method->getName())){
				continue;
			}
			$this->tests[$name] = $method;
		}
		$this->countTestCases = count($this->tests);
	}
	/**
	 * get the testcase count
	 * 
	 * 获取testcase数量
	 * 
	 * @return int
	 */
	public function countTestCases(){
		return $this->countTestCases;
	}
	/**
	 * (non-PHPdoc)
	 * @see XTestCase::run()
	 */
	public function run(){
		$this->setUp();
		foreach($this->tests as $key => $value){
			$this->runTest($key);
		}
		$this->tearDown();
	}
	
	protected function runTest($method){
		try{			
			$this->$method();	
			$this->passedTests[$method] = $method;		
		}catch(XAssertFailedException $e){
			$this->failedTests[$method] = $e;		
		}catch(Exception $e){
			throw $e;
		}
	}
	
	protected function setUp(){
		
	}

	protected function tearDown(){
		
	}
}
?>