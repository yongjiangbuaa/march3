<?php
import('persistence.dao.XAbstractEntityCollection');
import('service.quest.ActiveQuest');
/** 
 * UserActiveQuest
 * 
 * user active quest class
 * 
 * 用户当前任务列表类
 * 
 * @Entity
 * @author Wangqi
 * @package quest
 */
class UserActiveQuest extends XAbstractEntityCollection{
	
	/**
	 * @Id
	 */
	protected $uid = null;
	/**
	 * @Save(type=value)
	 */
	protected $items = array();
	
	/**
	 * <b>construce method</b>
	 *
	 * <b>构造方法</b>
	 */
	public function __construct($user_uid=NULL){
		parent::__construct();
		if(!empty($user_uid)){
			$this->uid = $user_uid;
		}
	}
	
	/**
	 * <b>uid setter</b>
	 * 
	 * <b>设置uid属性的方法</b>
	 * 
	 * @param string $uid
	 * @return UserActiveQuest
	 */
	public function setUID($uid){
		$this->uid = $uid;
	}
	
	/**
	 * <b>complete the current active quest if all the quest elements are completed</b>
	 * 
	 * <b>如果所有子任务都已经完成，则将本任务设置为完成状态</b>
	 * 
	 * @param int $questId
	 * @return boolean
	 */
	public function completeQuest($questId){
		foreach ($this->items as $quest){
			if($quest->get('id')==$questId){
				if($quest->isCompleted()){
					return false;
				}
				$completed = 0;
				$elements = $quest->getItems();
				foreach ($elements as $element){
					if($element->isCompleted()){
						$completed += 1;
					}
				}
				if($completed>=count($elements)){
					$quest->complete();
					$quest->save();
					$this->items[$quest->get('uid')] = $quest;
					$this->save();
					return TRUE;
				}
				break;
			}
		}
		return FALSE;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see XAbstractEntityCollection::asArray()
	 */
	public function asArray(){
		$questArray = array();
		foreach ($this->items as $quest){
			if(!is_object($quest)){
				return FALSE;
			}
			$questStatus = array();
			$questStatus['id'] = $quest->get('id');
			$elements = $quest->getItems();
			foreach ($elements as $element){
				$questStatus['actions'][] = $element->get('count');
			}
			$questArray[] = $questStatus;
		}
		$result = array('actived'=>$questArray);
		return $result;
	}
}