<?php
/**
 * XEventListener
 * 
 * event listener interface
 * 事件监听者接口
 * @author Tianwei
 * @interface
 * @package event 
 */
interface XEventListener{
	/** 
	 * handle an event
	 * 
	 * 处理一个事件
	 * 
	 * @param XEvent $event 事件对象
	 */		
	public function onEvent(XEvent $event);	
}
?>