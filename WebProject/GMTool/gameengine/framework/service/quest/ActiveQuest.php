<?php
import('service.quest.ActiveQuestElement');
import('service.quest.Quest');
import('persistence.dao.XAbstractEntityCollection');
/** 
 * ActiveQuest
 * 
 * active quest model
 * 
 * 用户任务模型定义，任务在数据库中存储的模型
 * 
 * @Entity
 * @author Wangqi
 * @package quest
 */
class ActiveQuest extends XAbstractEntityCollection{
	
	/**
	 * 任务ID
	 */
	protected $id = null;
	
	/**
	 * 任务的开始时间
	 */
	protected $startTime = null;
	
	/**
	 * 任务的结束时间
	 */
	protected $endTime = null;
	
	/**
	 * 任务完成状态
	 */
	protected $completed = FALSE;
	
	/**
	 * @Save(type=value)
	 */
	protected $items = array();
	
	/**
	 * <b>initialize an active quest, set id to the quest id, set startTime to the current time. initialze all the quest elements</b>
	 * 
	 * <b>初始化一个用户任务, 将id设为任务id，将开始时间startTime设为当前时间。会对每一个任务元素进行初始化</b>
	 * 
	 * @param Quest $quest
	 */
	public function init(Quest $quest){
		$this->id = $quest->get('id');
		$this->startTime = $_SERVER['REQUEST_TIME'];
		$activeElements =  array();
		foreach ($quest->getElements() as $element){
			$activeElement =  new ActiveQuestElement();
			$activeElement->init($element);
			$activeElements[] = $activeElement;
		}
		$this->addItems($activeElements);
	}
	
	/**
	 * <b>uid setter</b>
	 * 
	 * <b>设置uid属性的方法</b>
	 * 
	 * @param string $uid
	 * @return ActiveQuest
	 */
	public function setUID($uid){
		$this->uid = $uid;
	}
	
	/**
	 * <b>startTime getter</b>
	 * 
	 * <b>获取startTime属性的方法</b>
	 * 
	 * @return int
	 */
	public function getStartTime(){
		return $this->startTime;
	}
	
	/**
	 * <b>startTime setter</b>
	 * 
	 * <b>设置startTime属性的方法</b>
	 * 
	 * @param int $time
	 * @return ActiveQuest
	 */
	public function setStartTime($time){
		$this->startTime = $time;
		return $this;
	}
	
	/**
	 * <b>endTime getter</b>
	 * 
	 * <b>获取endTime属性的方法</b>
	 * 
	 * @return int
	 */
	public function getEndTime(){
		return $this->endTime;
	}
	
	/**
	 * <b>endTime setter</b>
	 * 
	 * <b>设置endTime属性的方法</b>
	 * 
	 * @param int $time
	 * @return ActiveQuest
	 */
	public function setEndTime($time){
		$this->endTime = $time;
		return $this;
	}
	
	/**
	 * <b>complete the quest, set completed to true, set endTime to the current time</b>
	 * 
	 * <b>将当前任务设置为已经完成，将完成时间endTime设置为当前时间</b>
	 * 
	 */
	public function complete(){
		$this->completed = TRUE;
		$this->endTime = $_SERVER['REQUEST_TIME'];
	}
	
	/**
	 * <b>check whether the quest is completed</b>
	 * 
	 * <b>判断当前任务是否已经完成</b>
	 * 
	 * @return boolean
	 */
	public function isCompleted(){
		return $this->completed;
	}
}



?>