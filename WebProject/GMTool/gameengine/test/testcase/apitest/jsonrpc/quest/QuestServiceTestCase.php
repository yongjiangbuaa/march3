<?php
class QuestServiceTestCase extends XJSONRPCServiceTestCase{
	private $questId = null;
	
	public function testAccept(){
		import('service.quest.UserActiveQuest');
		$userActiveQuest = new UserActiveQuest();
		$userActiveQuest->setUID('QUEST'.$this->gameUserId);
		$userActiveQuest->save();
		import('persistence.orm.PersistenceSession');
		PersistenceSession::singleton()->flush();
		$this->setQuestID();
		$parameters = array(
				'quest_uid'=>$this->questId
		);
		$result = $this->invokeService('quest.quest.accept', $parameters);
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertEquals($this->questId, $result['data']);
	}
	
	public function testGet(){
		$result = $this->invokeService('quest.quest.get', array());
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertKeyExists('actived', $result['data']);
		XAssert::assertArray($result['data']['actived']);
		foreach ($result['data']['actived'] as $quest){
			XAssert::assertArray($quest);
			XAssert::assertKeyExists('id', $quest);
			XAssert::assertEquals($this->questId, $quest['id']);
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
		$parameters = array(
				'quest_uid'=>$this->questId
		);
		$result = $this->invokeService('quest.quest.submit', $parameters);
		$this->assertServiceResult($result);
		$result = $result['result'];
		XAssert::assertEquals($this->questId, $result['data']);
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