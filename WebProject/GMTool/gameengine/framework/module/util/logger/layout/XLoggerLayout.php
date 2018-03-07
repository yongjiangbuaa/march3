<?php
import('module.util.logger.XLoggingEvent');
/**
 * XLoggerLayout
 * 
 * logger layout Interface
 * 
 * 日志格式接口
 * 
 * @author Tianwei
 * @interface
 * @package util.logger
 */
interface XLoggerLayout{
	/**
	 * format the logging event
	 * 
	 * 将日志事件以设定的格式输出
	 * 
	 * @param XLoggingEvent $event
	 * @return string
	 */
	public function format(XLoggingEvent $event);
}
?>