<?php
class ItemSpecManagerTestCase extends BaseServiceTestCase{
	protected $initUserUid=FALSE;
	protected $enableService=FALSE;
	private $groupIds = array();
	private $itemIds = array();
	
	public function testGetItems(){
		$this->setIds();
		$result = ItemSpecManager::singleton()->getItems($this->itemIds);
		XAssert::assertNotEmpty($result);
	}
	
	public function testGetGroups(){
		$result = ItemSpecManager::singleton()->getGroups($this->groupIds);
		XAssert::assertNotEmpty($result);
	}
	
	public function testGetItem(){
		$index = rand(0, count($this->itemIds)-1);
		$id = $this->itemIds[$index];
		$result = ItemSpecManager::singleton()->getItem($id);
		XAssert::assertNotEmpty($result);
	}
	
	public function testGetGroup(){
		$index = rand(0, count($this->groupIds)-1);
		$id = $this->groupIds[$index];
		$result = ItemSpecManager::singleton()->getGroup($id);
		XAssert::assertNotEmpty($result);
	}
	
	protected function setIds(){
		$nodes = ItemSpecManager::singleton()->dom()->xpath('/tns:database//Group');
		if(empty($nodes)){
			XAssert::fail('no item group was found in item.xml. please check the xml file.');
		}
		foreach ($nodes as $node){
			$this->groupIds[] = strval($node['id']);
			foreach ($node as $key => $itemNode){
				if(preg_match('/ItemSpec/', $key)){
					$this->itemIds[] = strval($itemNode['id']);
				}
			}
		}
		if(empty($this->groupIds) || empty($this->itemIds)){
			XAssert::fail('get item id/group id failed');
		}
	}
}


?>