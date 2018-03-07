<?php
import('module.util.logger.appender.XLoggerAppender');
import('module.util.logger.XLoggingEvent');
/**
 * XAbstractLoggerAppender
 * 
 * abstract logger appender
 * 
 * 日志添加器抽象类，实现XLoggerAppender接口
 * 
 * @author Tianwei
 * @abstract
 * @package util.logger
 */
abstract class XAbstractLoggerAppender implements XLoggerAppender{
	protected $name = null;
	protected $threshold = INFO;
	protected $layout = null;
	protected $target = null;
	protected $closed = false;
	protected $maxfilesize = null;
	protected $slowRequestThreshold = null;
	/**
	 * construct method
	 * 
	 * 构造函数，须指定日志添加器的名称、参数等
	 * 
	 * @param string $name
	 * @param array $params
	 */
	public function __construct($name, $params = array()){
		$this->name = $name;
		if(isset($params['threshold'])){
			$this->threshold = constant(strtoupper($params['threshold']));
		}
		if(isset($params['target'])){
			$this->target = $params['target'];
		}
	}
	/**
	 * maxfilesize getter
	 * 
	 * 获取maxfilesize属性
	 * 
	 * @return int
	 */
	public function getMaxfilesize(){
		return $this->maxfilesize;
	}
	/**
	 * maxfilesize setter
	 * 
	 * 设置maxfilesize是属性
	 * 
	 * @param int $maxfilesize
	 */
	public function setMaxfilesize($maxfilesize){
		$this->maxfilesize = $maxfilesize;
	}
	/**
	 * slowRequestThreshold getter
	 * 
	 * 获取slowRequestThreshold属性，slowRequestThreshold为超时请求的时间阈值
	 * 
	 * @return int
	 */
	public function getSlowRequestThreshold(){
		return $this->slowRequestThreshold;
	}
	/**
	 * slowRequestThreshold setter
	 * 
	 * 设置slowRequestThreshold是属性
	 * 
	 * @param int $slowRequestThreshold
	 */
	public function setSlowRequestThreshold($slowRequestThreshold){
		$this->slowRequestThreshold = $slowRequestThreshold;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLoggerAppender::getName()
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLoggerAppender::setLayout()
	 */
	public function setLayout(XLoggerLayout $layout){
		$this->layout = $layout;
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $target
	 */
	public function setTarget($target){
		$this->target = $target;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLoggerAppender::append()
	 */
	public function append(XLoggingEvent $event){
		if($this->threshold > $event->getLevel()){
			return false;
		}
		return $this->output($this->layout->format($event));
	}
	/**
	 * close any resource handler if exists
	 * 
	 * 关闭appender启用的resource handler
	 * 
	 * @return boolean
	 */
	public function close(){
		return true;
	}
	
	protected function output($message){
		if($this->target){
			return file_put_contents($this->target, $message);
		}
		return false;
	}
}
?>