<?php
/**
 * XLoggerAppender
 * 
 * logger appender Interface
 * 
 * 日志添加器接口
 * 
 * @author Tianwei
 * @interface
 * @package util.logger
 */
interface XLoggerAppender{
	/**
	 * name getter
	 * 
	 * 获取name属性
	 * 
	 * @return string
	 */
	public function getName();
	/**
	 * append log from XLoggingEvent
	 * 
	 * 从XLoggingEvent中获取信息，添加日志
	 * 
	 * @param XLoggingEvent $event
	 */
	public function append(XLoggingEvent $event);
	/**
	 * set logger layout for the appender
	 * 
	 * 为日志添加器设置日志格式
	 * 
	 * @param XLoggerLayout $layout
	 */
	public function setLayout(XLoggerLayout $layout);
}
?>