<?php
import('util.model.XObject');
import('service.quest.QuestElement');
/**
 * Quest
 * 
 * quest model class
 * 
 * 任务模型定义类
 * 
 * @author Wangqi
 * @package quest
 */
class Quest extends XObject{
	protected $id;
	protected $name;
	protected $description;
	protected $parent;
	protected $elements = array();
	
	/**
	 * <b>get all the quest elements</b>
	 * 
	 * <b>获取所有任务元素</b>
	 * 
	 * @return array
	 */
	public function getElements(){
		return $this->elements;
	}
	
	/**
	 * <b>add a quest element to the current quest</b>
	 * 
	 * <b>为当前任务添加一个任务元素</b>
	 * 
	 * @param QuestElement $element
	 */
	public function addElement(QuestElement $element){
		$this->elements[] = $element;
	}
	
}

?>