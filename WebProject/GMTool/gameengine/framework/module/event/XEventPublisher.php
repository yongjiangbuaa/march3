<?php
/**
 * XEventPublisher
 * 
 * event publisher interface
 * 事件发布者接口
 * @author Tianwei
 * @interface
 * @package event 
 */
interface XEventPublisher{
	/** 
	 * publish an event
	 * 
	 * 发布一个事件
	 * 
	 * @param XEvent $event 事件对象
	 */	
	public function publish(XEvent $event);	
}
?>