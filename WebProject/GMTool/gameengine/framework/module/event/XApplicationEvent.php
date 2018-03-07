<?php
import('module.event.XEvent');
/**
 * XApplicationEvent
 * 
 * application event
 * 
 * 应用事件类，框架事件的基本类型
 * 
 * @author Tianwei
 * @package event 
 */
class XApplicationEvent implements XEvent{
	protected $source = null;
	
	/** 
	 * construct method
	 * 
	 * 构造函数，将事件的发起者对象注入事件
	 * 
	 * @param object $source 事件的发起者对象
	 */	
	public function __construct($source){
		$this->source = $source;
	}
	
	/** 
	 * event source object getter
	 * 
	 * 得到事件的发起者对象
	 * 
	 * @return object
	 */
	public function getSource(){
		return $this->source;
	}
}
?>