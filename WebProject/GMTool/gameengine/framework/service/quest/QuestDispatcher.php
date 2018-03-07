<?php
import('service.quest.ActiveQuest');
import('service.quest.QuestSpecManager');
import('service.quest.UserActiveQuest');
/**
 * QuestDispatcher
 * 
 * quest dispatcher
 * 
 * 任务分发器
 * 
 * @author Wangqi
 * @package quest
 */
class QuestDispatcher{
	private static $instance = null;
	
	/**
	 * <b>singleton method</b>
	 *
	 * <b>singleton方法</b>
	 *
	 * @static
	 * @return QuestDispatcher
	 */
	static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * <b>add quests to the given user's active quest list</b>
	 *
	 * <b>向给定用户的当前任务列表中添加任务</b>
	 * 
	 * @param string $user_uid
	 * @return array
	 */
	public function addActiveQuests($user_uid){
		if(empty($user_uid)){
			return null;
		}
		$quests = QuestSpecManager::singleton()->getAllQuests();
		$userActiveQuest = new UserActiveQuest();
		$userActiveQuest->setUID('QUEST'.$user_uid);
		$activeQuests = array();
		foreach ($quests as $quest){
			$activeQuest = new ActiveQuest();
			$activeQuest->init($quest);
			$activeQuests[] = $activeQuest;
		}
		$userActiveQuest->addItems($activeQuests);
		return $userActiveQuest->getItems();
	}
	
	/**
	 * <b>add a quest to the given user's active quest list</b>
	 *
	 * <b>向给定用户的当前任务列表中添加指定的一个任务</b>
	 * 
	 * @param string $user_uid
	 * @param int $quest_uid
	 * @return array
	 */
	public function addActiveQuest($user_uid, $quest_uid){
		$quest = QuestSpecManager::singleton()->getQuest($quest_uid);
		if(empty($quest)){
			return FALSE;
		}
		import('persistence.orm.PersistenceSession');
		$userActiveQuest = PersistenceSession::singleton()->get('UserActiveQuest', 'QUEST'.$user_uid);
		if(empty($userActiveQuest)){
			$userActiveQuest = new UserActiveQuest();
			$userActiveQuest->setUID('QUEST'.$user_uid);
		}
		$activeQuest = new ActiveQuest();
		$activeQuest->init($quest);
		$userActiveQuest->addItem($activeQuest);
		$userActiveQuest->save();
		return $userActiveQuest->getItems();
	}
	
}