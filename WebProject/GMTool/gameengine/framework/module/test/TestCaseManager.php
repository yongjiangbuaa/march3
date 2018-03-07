<?php
/**
 * TestCaseManager
 * 
 * testcase manager class
 * 
 * testcase管理器
 * 
 * @author Wangqi
 * @package test 
 */
class TestCaseManager{
	private $basepath;//
	private $testType='unitTest';
	private $protocol='rest';
	private $classes;// array(array('path'=>'user.service', 'classname'=>'UserServiceTestCase'))
	protected $passed;
	protected $failed;
	protected $elapsed_time;
	
	/**
	 * construct method
	 * 
	 * 构造函数
	 */
	public function __construct(){
	}
	/**
	 * path setter
	 * 
	 * 设置path属性
	 * 
	 * @param string $path
	 */
	public function setBasepath($path){
		$this->basepath = $path;
	}
	/**
	 * testType setter
	 * 
	 * 设置testType属性
	 * 
	 * @param string $testType
	 */
	public function setTestType($testType){
		$this->testType = $testType;
	}
	/**
	 * protocol setter
	 * 
	 * 设置protocol属性
	 * 
	 * @param string $protocol
	 */
	public function setProtocol($protocol){
		$this->protocol = $protocol;
	}
	/**
	 * list all testcases in basepath
	 * 
	 * 列出basepath目录下所有的testcase
	 * 
	 * @return array
	 */
	public function findAll(){
		$this->listDir($this->basepath);
		return $this->classes;
	}
	
	/*
	 * $caseStr = "user.UserServiceTestCase,item.ItemServiceTestcase"
	 * $caseStr = "all"
	 */
	private function parseTestCaseName($caseStr){
		$caseStr = trim($caseStr, ',');
		$errMsg = '';
		if($caseStr=='all'){
			$this->listDir($this->basepath);
		}else{
			$caseStr = $this->pointToSlash($caseStr);
			$caseArr = explode(',', $caseStr);
			if(!empty($caseArr)){
				foreach ($caseArr as $v){
					$v = trim($v);
					$file = $this->pointToSlash($v);
					$file .= '.php';
					if(is_file($this->basepath.'/'.$file)){
						$file_dir = dirname($file);
						$file_dir = $this->slashToPoint($file_dir);
						$this->classes[] = array('path'=>$file_dir, 'classname'=>basename($file, '.php'));
					}else{//
						$errMsg .= "$v is not a valid parameter.\n";
					}
				}
			}
		}
		return $errMsg;
	}
	
	/*
	 * list all testcases in $dir, including sub-directory
	 */
	private function listDir($dir){
		$xfile = new XFile($dir);
		$files = $xfile->listFiles();
		if(!empty($files)){
			foreach ($files as $file){
				if($file->isDirectory()){
					$this->listDir($file->getPath());
				}else if($file->isFile()){
					if($this->isTestCase($file)){
						$file_dir = $file->getParent();
						$file_dir = substr($file_dir, strlen($this->basepath)+1);
						$file_dir = $this->slashToPoint($file_dir);
						$this->classes[] = array('path'=>$file_dir, 'classname'=>basename($file->getName(), '.php')); 
					}
				}else{
					continue;
				}
			}
		}
	}
	
	
	private function isTestCase($file){
		if(preg_match('/TestCase\.php$/i', $file->getName())){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * run the specified testcases
	 * 
	 * 运行指定的testcase
	 * 指定的testcase可以为完整的testcase名称，例如
	 * $caseStr = "user.UserServiceTestCase,item.ItemServiceTestcase"
	 * 也可以指定为
	 * $caseStr = "all"
	 * 此时将运行测试案例根目录下所有的testcase
	 * 
	 * @param string $caseStr
	 * @return string
	 */
	public function run($caseStr){
		$st = microtime(TRUE);
		$msg = $this->parseTestCaseName($caseStr);
		if(!empty($msg)){
			return $msg;
		}
		if(!empty($this->classes)){
			foreach ($this->classes as $class){
				//import_class('service.'.$class['path'].'.'.preg_replace('/TestCase$/i', '', $class['classname']));
				$file =  XINGCLOUD_TESTCASE_DIR.__DS__.$this->testType;
				if($this->testType=='apitest'){
					$file .= '/'.$this->protocol;
				}
				$file .= '/'.$class['path'].'/'.$class['classname'].'.php';
				if(class_exists($class['classname']) || interface_exists($class['classname'])){
				}else{
					require $file;
				}
				$classname = x_class_name($class['classname']);
				if(!class_exists($classname)){
					continue;
				}
				$instance = new $classname;
				if($this->testType=='apitest'){
					if(in_array($this->protocol, array('rest', 'jsonrpc'))){
						if(!extension_loaded('curl')){
							return 'PHP CURL extention is required for this test.';
						}
						$instance->setProtocol($this->protocol);
					}else{
						return "Parameter protocol is not correct.";
					}
				}
				try{
					$instance->run();
					$r = $instance->getResult();
				}catch (Exception $e){
					$r['countTestCases']=1;
					$r['passedTests']=array();
					$r['failedTests']=array('error occured'=>$e->getTraceAsString());
				}
				if($r['countTestCases']==count($r['passedTests'])){
					$this->passed[] = array_merge($class, $r);
				}else{
					$this->failed[] = array_merge($class, $r);
				}
			}
		}
		$et = microtime(TRUE);
		$this->elapsed_time = number_format($et-$st, 6);
		return '';
	}
	
	/*
	 * replace slash(/ or \) with point(.)
	 */
	private function slashToPoint($str){
		$str = preg_replace('#/#i', '.', $str);
		$str = preg_replace('/\\\/i', '.', $str);
		return $str;
	}
	
	/*
	 * replace point(.) with slash(/)
	 */
	private function pointToSlash($str){
		$str = preg_replace('#\.#i', '/', $str);
		return $str;
	}
	/**
	 * generate testcase execution report
	 * 
	 * 生成testcase运行结果报告
	 * 
	 * @return string
	 */
	public function report(){
		$totalPassed = 0;
		$totalFailed = 0;
		$str = '';
		
		
		$str .= "Time: {$this->elapsed_time}\n\n";
		
		$str .= "\n---------------------------\n";
		
		$str .= "Details:\n";
		if(count($this->failed)>0){
			foreach ($this->failed as $v){
				$totalFailed += count($v['failedTests']);
				$totalPassed += count($v['passedTests']);
				$str .= $this->slashToPoint($v['path']).'.'.$v['classname']."\n  Tests run: ".$v['countTestCases'].', Failures: '. count($v['failedTests'])."\n";
				$str .= '  Failed methods: ';
				$errorStr = "  Errors: \n";
				foreach ($v['failedTests'] as $method=>$error){
					$str .= $method.", ";
					$errorStr .= $error."\n";
				}
				$str = trim($str, ', ');
				$str .= "\n";
				if(count($v['passedTests'])>0){
					$str .= '  Passed methods: '.implode(", ", $v['passedTests'])."\n";
				}
				$str .= $errorStr;
				
			}
		}
		$str .= "\n";
		if(count($this->passed)>0){
			foreach ($this->passed as $v){
				$totalFailed += count($v['failedTests']);
				$totalPassed += count($v['passedTests']);
				$str .= $this->slashToPoint($v['path']).'.'.$v['classname']."\n  Tests run: ".$v['countTestCases'].', Failures: '. count($v['failedTests'])."\n";
				if(count($v['passedTests'])==0){
					continue;
				}
				$str .= '  Passed methods: '.implode(", ", $v['passedTests'])."\n";
			}
		}
		$str = 'Tests run:'.($totalFailed+$totalPassed).', Failures: '.$totalFailed."\n\n".$str;
		return $str;
	}
	
}


?>