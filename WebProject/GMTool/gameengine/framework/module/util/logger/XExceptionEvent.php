<?php
import('module.util.logger.XLoggingEvent');
/**
 * XExceptionEvent
 * 
 * exception event
 * 
 * 抛出异常时发布的事件，继承XLoggingEvent
 * 
 * @author Tianwei
 * @package event 
 */
class XExceptionEvent extends XLoggingEvent{
	protected $exception;
	protected $data = null;
	/**
	 * construct method
	 * 
	 * 构造函数
	 * 
	 * @param Exception $exception
	 * @param string $data
	 */
	public function __construct($exception, $data = NULL){		
		$traces = $exception->getTrace();
		$trace = array_shift($traces);
		$class = isset($trace['class']) ? $trace['class'] : $trace['file'];
		parent::__construct($class, isset($trace['function']) ? $trace['function'] : '', isset($trace['line']) ? $trace['line'] : '', ERROR, $exception->getMessage());
		$this->exception = $exception;
		$this->data = $data;
	}

	/**
	 * exception getter
	 * 
	 * 获取exception属性
	 * 
	 * @return Exception
	 */
	public function getException(){
		return $this->exception;
	}
	
	/**
	 * data getter
	 * 
	 * 获取data属性
	 * 
	 * @return string
	 */
	public function getData(){
		return $this->data;
	}
}
?>