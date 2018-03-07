<?php
/** 
 * ActiveQuestManager
 * 
 * active quest manager
 * 
 * active quest管理器
 * 
 * @author Wangqi
 * @package quest
 */
class ActiveQuestManager{
	private static $instance = null;
	
	/**
	 * <b>singleton method</b>
	 *
	 * <b>singleton方法</b>
	 *
	 * @static
	 * @return ActiveQuestManager
	 */
	static function singleton(){
		if(!self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * query user's active quests from database
	 * 
	 * 从数据库中查询用户的任务列表
	 * 
	 * @param string $user_uid
	 * @return UserActiveQuest
	 */
	public function queryActiveQuest($user_uid){
		if(empty($user_uid)){
			import('module.context.XException');
			throw new XException('user_uid is empty when querying UserActiveQuest');
		}
		import('service.quest.UserActiveQuest');
		import('persistence.orm.PersistenceSession');
		$result = PersistenceSession::singleton()->get('UserActiveQuest', 'QUEST'.$user_uid);
		if(empty($result)){
			//double check?135
			$result = new UserActiveQuest();
			$result->setUID('QUEST'.$user_uid);
			$result->save();
		}
		return $result;
	}
	
	/**
	 * accept the specified quest
	 * 
	 * 接受一个指定的任务
	 * 
	 * @param string $user_uid
	 * @param string $quest_uid
	 * @return boolean
	 * @throws XException
	 */
	public function acceptActiveQuest($user_uid, $quest_uid){
		import('service.quest.QuestSpecManager');
		$quest = QuestSpecManager::singleton()->getQuest($quest_uid);
		if(empty($quest)){
			return false;
		}
		$userActiveQuest = $this->queryActiveQuest($user_uid);
		$activeQuests = $userActiveQuest->getItems();
		if(!empty($activeQuests)){
			foreach ($activeQuests as $activeQuest){
				if($activeQuest->get('id')==$quest_uid){
					import('module.context.XException');
					throw new XException('already accepted this quest', 400);
				}
			}
		}
		$activeQuest = new ActiveQuest();
		$activeQuest->init($quest);
		$userActiveQuest->addItem($activeQuest);
		$userActiveQuest->save();
		return true;
	}
	
	/**
	 * complete an active quest if all the quest elements are completed
	 * 
	 * 如果所有子任务都已经完成，则将指定任务设为完成状态
	 * 
	 * @param string $user_uid
	 * @param string $quest_uid
	 * @return boolean
	 * @throws XException
	 */
	public function completeActiveQuest($user_uid, $quest_uid){
		$userActiveQuest = $this->queryActiveQuest($user_uid);
		$activeQuests = $userActiveQuest->getItems();
		if(empty($activeQuests)){
			import('module.context.XException');
			throw new XException('no active quests');
		}
		foreach ($activeQuests as $quest){
			if($quest->get('id')==$quest_uid){
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
					$userActiveQuest->updateItem($quest);
					return TRUE;
				}
				break;
			}
		}
		return false;
	}
}