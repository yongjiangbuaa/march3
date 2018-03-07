<?php
class QuestServiceTestCase extends BaseServiceTestCase{
	private $questId = null;
	
	public function testAccept(){
		//clear the quest list
		import('service.quest.UserActiveQuest');
		$userActiveQuest = new UserActiveQuest();
		$userActiveQuest->setUID('QUEST'.$this->gameUserId);
		$userActiveQuest->save();
		import('persistence.orm.PersistenceSession');
		PersistenceSession::singleton()->flush();
		
		//accept a quest
		$this->setQuestID();
		$this->setRequestParam('quest.quest.accept', array('quest_uid'=>$this->questId));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertEquals($this->questId, $result->data);
	}
	

	public function testGet(){
		//accept a quest
		$this->setRequestParam('quest.quest.get', array());
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertNotEmpty($result->data);
		$userActiveQuest = $result->data;
		XAssert::assertNotEmpty($userActiveQuest);
		XAssert::assertObject($userActiveQuest);
		$activeQuests = $userActiveQuest->getItems();
		XAssert::assertNotEmpty($activeQuests);
		XAssert::assertArray($activeQuests);
		foreach ($activeQuests as $activeQuest){
			XAssert::assertNotEmpty($activeQuest);
			XAssert::assertObject($activeQuest);
			XAssert::assertEquals($this->questId, $activeQuest->get('id'));
			break;
		}
	}
	
	public function testSubmit(){
		//get the quest
		import('service.quest.ActiveQuestManager');
		$userActiveQuest = ActiveQuestManager::singleton()->queryActiveQuest($this->gameUserId);
		XAssert::assertNotEmpty($userActiveQuest);
		XAssert::assertObject($userActiveQuest);
		$activeQuests = $userActiveQuest->getItems();
		XAssert::assertNotEmpty($activeQuests);
		XAssert::assertArray($activeQuests);
		//set the status to finish
		foreach ($activeQuests as $activeQuest){
			XAssert::assertNotEmpty($activeQuest);
			XAssert::assertObject($activeQuest);
			if($activeQuest->get('id')==$this->questId){
				foreach ($activeQuest->getItems() as $activeQuestElement){
					$activeQuestElement->complete();
				}
				$activeQuest->updateItem($activeQuestElement);
				$userActiveQuest->updateItem($activeQuest);
				break;
			}
		}
		$userActiveQuest->save();
		import('persistence.orm.PersistenceSession');
		PersistenceSession::singleton()->flush();
		$this->setRequestParam('quest.quest.submit', array('quest_uid'=>$this->questId));
		$result = $this->executeService();
		$this->assertServiceResult($result);
		XAssert::assertEquals($this->questId, $result->data);
		
	}
	
	private function setQuestID(){
		import('service.quest.QuestSpecManager');
		$quests = QuestSpecManager::singleton()->getAllQuests();
		XAssert::assertNotEmpty($quests);
		XAssert::assertArray($quests);
		$index = rand(0, count($quests)-1);
		$i = 0;
		foreach ($quests as $quest){
			if($i==$index){
				$this->questId = $quest->get('id');
				break;
			}
			$i++;
		}
	}
}
?>