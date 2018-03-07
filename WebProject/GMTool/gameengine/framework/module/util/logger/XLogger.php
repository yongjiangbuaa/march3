<?php
import('module.util.logger.XLoggingEvent');
/**
 * XLogger
 * 
 * logger Interface
 * 
 * 日志接口
 * 
 * @author Tianwei
 * @interface
 * @package util.logger
 */
interface XLogger{
	/**
	 * add log from XLoggingEvent
	 * 
	 * 从XLoggingEvent中获取信息，并记录相应日志
	 * 
	 * @param XLoggingEvent $event
	 */
	public function log(XLoggingEvent $event);
	/**
	 * level setter
	 * 
	 * 设置日志的等级
	 * 
	 * @param int $level
	 */
	public function setLevel($level);
	/**
	 * level getter
	 * 
	 * 获取level属性
	 */
	public function level();
	/**
	 * close the logger
	 * 
	 * 关闭日志记录器
	 */
	public function close();
}
?>