<?php
if(!defined('LOGGER_STDOUT')){	
	define('LOGGER_STDOUT', 'php://stdout');
}
if(!defined('LOGGER_STDERR')){	
	define('LOGGER_STDERR', 'php://stderr');
}
import('module.util.logger.appender.XAbstractLoggerAppender');
/**
 * XConsoleLoggerAppender
 * 
 * console logger appender
 * 
 * 控制台日志添加器，继承XAbstractLoggerAppender
 * 
 * @author Tianwei
 * @package util.logger
 */
class XConsoleLoggerAppender extends XAbstractLoggerAppender{	
	protected $handler = null;
	/**
	 * construct method
	 * 
	 * 构造函数，须指定日志添加器的名称、参数等
	 * 
	 * @param string $name
	 * @param array $params
	 */
	public function __construct($name, $params = array()){
		parent::__construct($name, $params);
		if(is_string($this->target)
			&& strtoupper($this->target) == 'STDERR'){			
			$this->target = LOGGER_STDERR;
		}else{
			$this->target = LOGGER_STDOUT;
		}
	}
	
	protected function init(){
		$this->handler = fopen($this->target, 'w');
		$this->closed = (bool)is_resource($this->handler) === false;
	}
	
	protected function output($message) {
		if(!$this->handler){
			$this->init();
		}
		if(is_resource($this->handler)) {
			fwrite($this->handler, $message);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see XAbstractLoggerAppender::close()
	 */
	public function close() {
		if($this->closed != true){
			if(is_resource($this->handler)){
				fclose($this->handler);
			}
			$this->closed = true;
		}			 
	}
}
?>