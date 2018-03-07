<?php
/**
 * XEvent
 * 
 * event interface
 * 事件接口
 * @author Tianwei
 * @interface
 * @package event 
 */
interface XEvent{
	/** 
	 * event source object getter
	 * 
	 * 得到事件的发起者对象
	 * 
	 * @return object
	 */	
	public function getSource();
}
?>