<?php
import('service.quest.QuestElement');
import('persistence.dao.XActiveRecord');
/** 
 * ActiveQuestElement
 * 
 * active quest element model
 * 
 * 用户任务元素模型定义，任务元素在数据库中存储的模型
 * 
 * @Entity
 * @author Wangqi
 * @package quest
 */
class ActiveQuestElement extends XActiveRecord {
	/**
	 * 任务元素的ID
	 * @Id
	 */
	protected $uid;
	
	protected $count;
	
	/**
	 * 任务元素的开始时间
	 */
	protected $startTime = null;
	
	/**
	 * 任务元素的结束时间
	 */
	protected $endTime = null;
	
	/**
	 * 任务元素完成状态
	 */
	protected $completed = FALSE;
	
	/**
	 * <b>initialize an active quest element, set count to zero, set startTime to the current time</b>
	 * 
	 * <b>初始化一个用户任务元素, 将完成数count设为0，将开始时间startTime设为当前时间</b>
	 * 
	 * @param QuestElement $element
	 */
	public function init(QuestElement $element){
		$this->count = 0;
		$this->startTime = $_SERVER['REQUEST_TIME'];
	}
	
	/**
	 * <b>increase the count parameter</b>
	 * 
	 * <b>增加完成数count</b>
	 * 
	 * @param int $count
	 */
	public function addCount($count){
		$this->count += $count;
	}
	
	/**
	 * <b>check whether the quest element is completed</b>
	 * 
	 * <b>判断当前任务元素是否已经完成</b>
	 * 
	 * @return boolean
	 */
	public function isCompleted(){
		return $this->completed;
	}
	
	/**
	 * <b>completed setter</b>
	 * 
	 * <b>设置completed属性的方法</b>
	 * 
	 * @param boolean $completed
	 * @return ActiveQuestElement
	 */
	public function setCompleted($completed){
		$this->completed = $completed;
		return $this;
	}
	
	/**
	 * <b>complete the quest element, set completed to true, set endTime to the current time</b>
	 * 
	 * <b>将当前任务元素设置为已经完成，将完成时间endTime设置为当前时间</b>
	 * 
	 */
	public function complete(){
		$this->completed = TRUE;
		$this->endTime = $_SERVER['REQUEST_TIME'];
	}
}

?>