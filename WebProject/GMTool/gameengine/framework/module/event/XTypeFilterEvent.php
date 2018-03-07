<?php
import('module.event.XApplicationEvent');
/**
 * XTypeFilterEvent
 * 
 * type filter event
 * 
 * 类型过滤事件，提供复杂事件逻辑
 * 
 * <b>可以用类型属性匹配多种事件，只要这些事件都是XTypeFilterEvent的实例或子类实例，并拥有同样的类型值</b>
 * 
 * @author Tianwei
 * @package event
 */
class XTypeFilterEvent extends XApplicationEvent{
	protected $type = null;
	
	/** 
	 * construct method
	 * 
	 * 构造函数，将事件的发起者对象和事件类型注入事件
	 * 
	 * @param object $source 事件的发起者对象
	 * @param string $type 事件的类型值
	 */		
	public function __construct($source, $type){
		parent::__construct($source);
		$this->type = $type;
	}
	
	/** 
	 * event type getter
	 * 
	 * 得到事件的类型
	 * 
	 * @return string
	 */	
	public function getType(){
		return $this->type;
	}
}
?>