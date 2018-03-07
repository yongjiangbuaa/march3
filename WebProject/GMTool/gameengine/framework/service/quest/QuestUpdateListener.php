<?php
import('module.event.XEventListener');
/**
 * QuestUpdateListener
 * 
 * quest update listener, listen to the ActionFinishedEvent/AuditChangeFinishedEvent
 * 
 * 任务更新监听器，监听ActionFinishedEvent/AuditChangeFinishedEvent事件
 * 
 * @EventListener(onEvent="ActionFinishedEvent|AuditChangeFinishedEvent")
 * @author Wangqi
 * @package quest
 */
class QuestUpdateListener implements XEventListener{
	/**
	 * (non-PHPdoc)
	 * @see XEventListener::onEvent()
	 */
	public function onEvent(XEvent $event){
		if($event instanceof ActionFinishedEvent){
			$user_uid = $event->getUserUID();
			$actionName = ucfirst($event->getSource()->getActionName());
			$parameters = $event->getSource()->getParameters();
			if(empty($parameters)){
				return FALSE;
			}
			foreach ($parameters as $parameter){
				if(is_array($parameter) && isset($parameter['itemId'])){
					break;
				}
			}
			import('service.quest.UserActiveQuest');
			import('persistence.orm.PersistenceSession');
			$userActiveQuest = PersistenceSession::singleton()->get('UserActiveQuest', 'QUEST'.$user_uid);
			if(empty($userActiveQuest)){
				return FALSE;
			}
			$activeQuests = $userActiveQuest->getItems();
			if(empty($activeQuests)){
				return FALSE;
			}
			foreach ($activeQuests as $id => $activeQuest){
				import('service.quest.QuestSpecManager');
				if(!is_object($activeQuest)){
					break;
				}
				$elements = $activeQuest->getItems();
				$manager = QuestSpecManager::singleton();
				$questId = $activeQuest->get('id');
				$quest = $manager->getQuest($questId);
				$rawElements = $quest->getElements();
				$i = 0;
				foreach ($elements as $element){
					$rawElement = $rawElements[$i];
					$itemId = $rawElement->get('itemId');
					$count = $rawElement->get('count');
					$changed = FALSE;
					if(!empty($itemId)){
						if(isset($parameters['itemId']) && isset($parameters['count']) && $rawElement->get('type')==$actionName && $itemId==$parameters['itemId']){
							$element->addCount($parameters['count']);
							$changed = TRUE;
						}
					}else{
						if($rawElement->get('type')==$actionName){
							$element->addCount(1);
							$changed = TRUE;
						}
					}
					
					//whether the element completed
					if($rawElement->get('count')<=$element->get('count')){
						$element->complete();
					}
					if($changed){
						$activeQuest->updateItem($element);
						$userActiveQuest->updateitem($activeQuest);
						$userActiveQuest->save();
					}
					$i++;
				}

			}
		}
		elseif($event instanceof AuditChangeFinishedEvent){
			$user_uid = $event->getUserUID();
			$data = $event->getSource();
			if(!isset($data['name'])){
				return FALSE;
			}
			$changeName = $data['name'];
			import('service.quest.UserActiveQuest');
			import('persistence.orm.PersistenceSession');
			$userActiveQuest = PersistenceSession::singleton()->get('UserActiveQuest', 'QUEST'.$user_uid);
			if(empty($userActiveQuest)){
				return FALSE;
			}
			$activeQuests = $userActiveQuest->getItems();
			if(empty($activeQuests)){
				return FALSE;
			}
			foreach ($activeQuests as $id => $activeQuest){
				import('service.quest.QuestSpecManager');
				if(!is_object($activeQuest)){
					break;
				}
				$elements = $activeQuest->getItems();
				$manager = QuestSpecManager::singleton();
				$questId = $activeQuest->get('id');
				$quest = $manager->getQuest($questId);
				$rawElements = $quest->getElements();
				$i = 0;
				foreach ($elements as $element){
					$rawElement = $rawElements[$i];
					$itemId = $rawElement->get('itemId');
					$count = $rawElement->get('count');
					$changed = FALSE;
					if($rawElement->get('type')==$changeName){
						$element->addCount(1);
						$changed = TRUE;
					}
					
					//whether the element completed
					if($rawElement->get('count')<=$element->get('count')){
						$element->complete();
					}
					if($changed){
						$activeQuest->updateItem($element);
						$userActiveQuest->updateitem($activeQuest);
						$userActiveQuest->save();
					}
					$i++;
				}
			}
		}
	}
}
?>