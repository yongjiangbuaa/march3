<?php
import('service.quest.QuestGroup');
import('service.quest.Quest');
import('service.quest.QuestElement');
/**
 * QuestSpecManager
 * 
 * quest data manager, load quest and quest group information from quest.xml
 * 
 * 任务定义管理器，从任务定义xml文件中加载任务或任务组的数据
 * 
 * @author Wangqi
 * @package quest
 */
class QuestSpecManager{
	private static $instance = null;
	protected $groups = array();
	protected $quests = array();
	protected $dom = null;
	
	/**
	 * <b>singleton method</b>
	 *
	 * <b>singleton方法</b>
	 *
	 * @static
	 * @return QuestSpecManager
	 */
	public static function singleton(){
		if(!self::$instance){
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * <b>load the quest.xml file</b>
	 *
	 * <b>加载quest.xml文件</b>
	 *
	 * @return SimpleXML
	 */
	public function dom(){
		if($this->dom){
			return $this->dom;
		}
		$file = XINGCLOUD_RESOURCE_DIR.'/quest/quest.xml';
		if(!is_file($file)){
			$file = XINGCLOUD_INNER_RESOURCE_DIR.'/quest/quest.xml';
			if(!is_file($file)){
				import('module.context.XException');
				throw new XException('can not find quest.xml');
			}
		}
		$this->dom = simplexml_load_file($file);
		return $this->dom;
	}
	
	/**
	 * <b>load a group by the given group id</b>
	 *
	 * <b>根据任务组id加载任务组信息</b>
	 * 
	 * @Cache
	 * @param int $id
	 * @return QuestGroup
	 */
	public function loadGroup($id){
		$xpath = '/tns:quests//Group[@id=\''.$id.'\']';
		mb_internal_encoding("UTF-8");
		$groups = $this->dom()->xpath($xpath);
		if(empty($groups)){
			return null;
		}
		$group = $groups[0];
		$questGroup = $this->parseGroup($group);
		$this->groups[$questGroup->getId()] = $questGroup;
		return $questGroup;
	}
	
	/**
	 * <b>load a quest by the given quest id</b>
	 *
	 * <b>根据任务id加载任务信息</b>
	 * 
	 * @Cache
	 * @param int $id
	 * @return Quest
	 */
	public function loadQuest($id){
		$xpath = '/tns:quests//Quest[@id=\''.$id.'\']';
		mb_internal_encoding("UTF-8");
		$quests = $this->dom()->xpath($xpath);
		if(empty($quests)){
			return null;
		}
		$questNode = $quests[0];
		$quest = $this->parseQuest($questNode);
		$this->quests[$quest->get('id')] = $quest;
		return $quest;
	}
	
	/**
	 * <b>load all quests</b>
	 *
	 * <b>加载所有任务信息</b>
	 * 
	 * @Cache
	 * @param int $id
	 * @return array
	 */
	public function loadAllQuests(){
		$xpath = '/tns:quests//Quest';
		mb_internal_encoding("UTF-8");
		$quests = $this->dom()->xpath($xpath);
		if(empty($quests)){
			return null;
		}
		foreach ($quests as $questNode){
			$quest = $this->parseQuest($questNode);
			$this->quests[$quest->get('id')] = $quest;
		}
		return $this->quests;
	}
	
	private function parseGroup($groupNode){
		$questGroup = new QuestGroup();
		$attributes = $groupNode->attributes(); 
		foreach ($attributes as $key => $value){
			$questGroup->set($key, strval($value));
		}
		foreach ($groupNode as $type => $node){
			switch ($type){
				case 'Group':{
					$questGroup->addChildGroup($this->parseGroup($node));
					break;
				}
				case 'Quest':{
					$questGroup->addQuest($this->parseQuest($node));
					break;
				}
				default:{
					break;
				}
			}
		}
		return $questGroup;
	}
	
	private function parseQuest($questNode){
		$quest = new Quest();
		$attributes = $questNode->attributes(); 
		foreach ($attributes as $key => $value){
			$quest->set($key, strval($value));
		}
		foreach ($questNode as $key => $node){
			$quest->addElement($this->parseQuestElement($node));
		}
		return $quest;
	}
	
	private function parseQuestElement($elementNode){
		$element = new QuestElement();
		$attributes = $elementNode->attributes();
		foreach ($attributes as $key => $value){
			$element->set($key, strval($value));
		}
		return $element;
	}
	
	/**
	 * <b>get a quest group by the given group id, will cache the group data</b>
	 *
	 * <b>根据任务组id获取一个任务组的信息，会对任务组信息进行缓存</b>
	 * 
	 * @param int $id
	 * @return QuestGroup
	 */
	public function getQuestGroup($id){
		if(isset($this->groups[$id])){
			return $this->groups[$id];
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->proxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());
		$this->groups[$id] = $manager->loadGroup($id);
		return $this->groups[$id];
	}
	
	/**
	 * <b>get a quest by the given quest id, will cache the quest data</b>
	 *
	 * <b>根据任务id获取任务信息，会对任务信息进行缓存</b>
	 * 
	 * @param int $id
	 * @return Quest
	 */
	public function getQuest($id){
		if(isset($this->quests[$id])){
			return $this->quests[$id];
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->proxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());
		$this->quests[$id] = $manager->loadQuest($id);
		return $this->quests[$id];
	}
	
	/**
	 * <b>get all quests data, will cache the quest data</b>
	 *
	 * <b>获取所有任务信息，会对任务信息进行缓存</b>
	 * 
	 * @return array
	 */
	public function getAllQuests(){
		$this->loadAllQuests();
		return $this->quests;
	}
	
}