<?php
import('module.util.logger.XLogger');
import('module.util.logger.XLoggingEvent');
import('module.util.logger.appender.XLoggerAppender');
/**
 * XingCloudLogger
 * 
 * xingcloud logger
 * 
 * 行云日志记录器，实现XLogger接口
 * 
 * @author Tianwei
 * @package util.logger
 */
class XingCloudLogger implements XLogger{
	private static $instance = null;
	protected $appenders = array();
	protected $level = ALL;
	
	private function __construct() {
	}
	
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * @static
	 * @return XingCloudLogger
	 */
	public static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLogger::level()
	 */
	public function level() {
		return $this->level;	
	}
	/**
	 * (non-PHPdoc)
	 * @see XLogger::setLevel()
	 */
	public function setLevel($level) {
		$this->level = $level;
		return $this;
	}

	/**
	 * add XLoggerAppender instance to the logger
	 * 
	 * 为日志记录器添加XLoggerAppender实例
	 * 
	 * @param XLoggerAppender $appender
	 * @return XingCloudLogger
	 */	
	public function addAppender(XLoggerAppender $appender){
		$this->appenders[$appender->getName()] = $appender;
		return $this;
	}
	/**
	 * check whether the logger has an appender
	 * 
	 * 检查日志记录器是否含有一个名字为$name的appender
	 * 
	 * @param string $name
	 */
	public function hasAppender($name){
		return isset($this->appenders[$name]);
	}
	/**
	 * (non-PHPdoc)
	 * @see XLogger::log()
	 */
	public function log(XLoggingEvent $event){
		if($this->level > $event->getLevel()){
			return false;
		}
		foreach($this->appenders as $this->appender){
			$slowRequestThreshold = intval($this->appender->getSlowRequestThreshold());
			$serviceName = $event->getServiceName();
			global $xingcloudRequestStartTime;
			$elapsedTime = $event->getTimestamp() - $xingcloudRequestStartTime;
			if($serviceName=='XingcloudSlowRequestLog' && !$this->appender->getSlowRequestThreshold()){
				continue;
			}
			if($serviceName=='XingcloudSlowRequestLog' && $elapsedTime<=$slowRequestThreshold){
				continue;
			}
			$this->appender->append($event);
		}
		return true;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLogger::close()
	 */
	public function close(){
		foreach($this->appenders as $this->appender){
			$this->appender->close();
		}
	}
}
?>