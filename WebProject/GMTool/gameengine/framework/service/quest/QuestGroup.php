<?php
import('util.model.XObject');
import('service.quest.Quest');
/**
 * QuestGroup
 * 
 * quest group model class
 * 
 * 任务组模型定义类
 * 
 * @author Wangqi
 * @package quest
 */
class QuestGroup extends XObject{
	protected $id;
	protected $name;
	protected $description;
	protected $parent;
	protected $quests = array();
	protected $childGroup = array();
	
	/**
	 * <b>get the id of the quest group</b>
	 * 
	 * <b>获取任务组的id</b>
	 * 
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * <b>add a quest to the current quest group</b>
	 * 
	 * <b>向当前任务组添加一个任务定义</b>
	 * 
	 * @param Quest $quest
	 */
	public function addQuest(Quest $quest){
		$this->quests[$quest->getId()] = $quest;
	}
	
	/**
	 * <b>add a child group to the current quest group</b>
	 * 
	 * <b>向当前任务组添加一个子任务组</b>
	 * 
	 * @param QuestGroup $group
	 */
	public function addChildGroup(QuestGroup $group){
		$this->childGroup[$group->getId()] = $group;
	}
	
}

?>