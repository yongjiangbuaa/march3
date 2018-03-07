<?php
import('module.aop.XAOPFactory');
import('service.item.ItemSpec');
import('service.item.ItemGroup');
/**
 * ItemSpecManager
 * 
 * ItemSpec data manager, load item and item group information from ItemSpec xml
 * 
 * 物品定义管理器，从物品定义xml文件中加载物品或物品组的数据
 * 
 * @author Tianwei
 * @package item
 *
 */
class ItemSpecManager{
	private static $instances = array();
	protected $groups = array();
	protected $groupAsArrays = array();
	protected $items = array();
	protected $itemAsArrays = array();
	protected $doms = array();
	protected $lang = null;
	protected $file = null;
	
	/**
	 * <b>construce method</b>
	 *
	 * <b>构造方法</b>
	 */
	private function __construct($lang, $file){
		$this->lang = $lang;
		$this->file = $file;
	}
	
	/**
	 * <b>singleton method</b>
	 *
	 * <b>singleton方法</b>
	 *
	 * @static
	 * @param string $lang 语言种类
	 * @param string $file ItemSpec文件地址, 如果指定$file, 则$lang将被忽略
	 * @return ItemSpecManager
	 */
	static function singleton($lang='default', $file=''){
		if ($lang == 'cn') {
			import('service.action.DataClass');
			$lang = StatData::$lang;
		}
		if(empty(self::$instances) || !isset(self::$instances[$lang])){
			self::$instances[$lang] = new self($lang=='default'?'':$lang, $file);
		}
		self::$instances[$lang]->setFile($file);
		return self::$instances[$lang];
	}
	
	/**
	 * lang setter
	 * 
	 * 设置lang属性
	 * @param string $lang
	 */
	public function setLang($lang){
		$this->lang = $lang;
	}
	
	/**
	 * lang getter
	 * 
	 * 获取lang属性
	 * @return string
	 */
	public function getLang(){
		return $this->lang;
	}
	
	/**
	 * file setter
	 * 
	 * 设置file属性
	 * @param string $file
	 */
	public function setFile($file){
		if ($file)
		{
			$this->file = $file;
		}
		else
		{
			$this->file = 'item.xml';
		}
		$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language/'.((empty($this->lang)?'':('/'.$this->lang))).$this->file;
		$fullPath = str_replace('.xml', '.txt',$fullPath);
		if(file_exists($fullPath))
		{
			$this->file = str_replace('.xml', '.txt', $this->file);
		}
	}
	
	/**
	 * file getter
	 * 
	 * 获取file属性
	 * @return string
	 */
	public function getFile(){
		return $this->file;
	}
		
	/**
	 * <b>load the item.xml file</b>
	 *
	 * <b>加载item.xml文件</b>
	 *
	 * @return SimpleXML
	 */
	public function dom(){
		if(!$this->file){
			$file = ((empty($this->lang)?'':('/'.$this->lang))).'/item.xml';
			$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language'.$file;
			if(!is_file($fullPath)){
				$fullPath = XINGCLOUD_RESOURCE_DIR.'/item'.$file;
				if(!is_file($fullPath)){
					import('module.context.XException');
					throw new XException('can not find item.xml, language: '.$this->lang);
				}
			}
		}else{
			$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language/'.((empty($this->lang)?'':($this->lang.'/'))).$this->file;
			if(!is_file($fullPath)){
				import('module.context.XException');
				throw new XException('can not find '. $this->file.', language: '.$this->lang);
			}
		}
		if(array_key_exists($fullPath, $this->doms)){
			return $this->doms[$fullPath];
		}
		$this->doms[$fullPath] = simplexml_load_file($fullPath);
		return $this->doms[$fullPath];
	}
	
	public function filedom(){
		$fullPath = XINGCLOUD_RESOURCE_DIR.'/locale/language/'.$this->file;
		if(array_key_exists($fullPath, $this->doms)){
			return $this->doms[$fullPath];
		}
		if(APC_ENABLED){
			import('util.cache.FileApcManager');
			$apcManager = new FileApcManager();
			$fileDecodeContents = $apcManager->getJsonFileData($fullPath);
		}
		if (!$fileDecodeContents)
		{
			$fileContents = file_get_contents($fullPath);
			$fileDecodeContents = get_object_vars( json_decode($fileContents));
		}
		
		$this->doms[$fullPath] = $fileDecodeContents;
		return $this->doms[$fullPath];
	}
	/**
	 * <b>load a group by the given group id</b>
	 *
	 * <b>根据物品组id加载物品组信息</b>
	 * 
	 * @Cache
	 * @param int $id
	 * @param boolean $asArray
	 * @return ItemGroup
	 */
	public function loadGroup($id, $asArray = false){
		$nodes = $this->dom()->xpath('/tns:database//Group[@id=\''.$id.'\']');
		if(empty($nodes)){
			return null;
		}
		$group = $nodes[0];
		$group = $this->_loadGroup($group);
		if($asArray){			
			$this->groupAsArrays[$group->get('id')] = $group->attrs();
			return $this->groupAsArrays[$group->get('id')];
		}
		return $group;
	}
	public function loadFileGroup($id, $asArray = false)
	{
		$fileContents = $this->filedom();
		if(isset($fileContents[$id]))
		{
			$Contents = $fileContents[$id];
		}

		if ($asArray)
		{
			$Contents = get_object_vars($Contents);
		}
		return $Contents;
	}
	public function loadFileItem($id, $asArray = false)
	{
		$fileContents = $this->filedom();
		foreach ($fileContents as $gk => $gv)
		{
			foreach ( $gv as $ik =>$iv)
			{
				if($ik == $id) return $iv;
			}
		}
		
		return null;
	}
	private function _loadGroup($group){
		$itemGroup = new ItemGroup();
		foreach($group->attributes() as $key => $value){
			$itemGroup->set($key, $this->toJson($value));
		}
		foreach($group->children() as $item){
			if($item->getName() == 'Group'){
				$itemGroup->addItem($this->_loadGroup($item));
				continue;
			}
			if($item->getName() == 'ItemSpec'){					
				$itemSpec = new ItemSpec();
				foreach($item->attributes() as $itemKey => $itemValue){
					$itemSpec->set($itemKey, $this->toJson($itemValue));
				}
				$itemGroup->addItem($itemSpec);
				$this->items[$itemSpec->get('id')] = $itemSpec;
			}
		}
		$this->groups[$itemGroup->get('id')] = $itemGroup;
		return $itemGroup;
	}
	
	/**
	 * <b>load an item from ItemSpec xml</b>
	 *
	 * <b>从ItemSpec xml中加载物品信息</b>
	 * 
	 * @Cache
	 * @param int $id
	 * @param boolean $asArray
	 * @return ItemSpec
	 */
	public function loadItem($id, $asArray = false){
		$nodes = $this->dom()->xpath('/tns:database//ItemSpec[@id=\''.$id.'\']');
		if(empty($nodes)){
			return null;
		}
		$item = $nodes[0];
		$attrs = $item->attributes();
		$defaultClass = 'ItemSpec';
		$class = isset($attrs['class'])&&!empty($attrs['class'])?strval($attrs['class']):$defaultClass;
		if($class == $defaultClass){
			$className = $class;
		}else {
			$pos = strripos($class, '.');
			if($pos === false){
				$className = $class;
			}else{
				$className = substr($class, $pos + 1);
			}
			if(is_file(XINGCLOUD_SERVICE_DIR.__DS__.'item'.__DS__.'itemspec'.__DS__.strtr($class, '.', __DS__).'.php')){
				import('service.item.itemspec.'.$class);
			}else{
				$className = $defaultClass;
			}
		}
		$itemSpec = new $className();
		foreach($item->attributes() as $itemKey => $itemValue){
			$itemSpec->set($itemKey, $this->toJson($itemValue));
		}
//		
//		$parentNode = $item->xpath('../@id');
//		$parent = null;
//		if(!empty($parentNode) && is_array($parentNode)){
//			$parentNode = $parentNode[0];
//			$parentId = strval($parentNode);
//			if ($parentId){
//				$parent = $this->getGroup($parentId);
//			}
//		}
//		$itemSpec->setParent($parent);
		$id = $itemSpec->get('id');
		$this->items[$id] = $itemSpec;	
		if($asArray){
			$this->itemAsArrays[$id] = $itemSpec->attrs();
			return $this->itemAsArrays[$id];
		}
		return $itemSpec;
	}
	
	private function getItemIds(){
		$nodes = $this->dom()->xpath('/tns:database//ItemSpec/@id');
		$ids = array();
		foreach ($nodes as $node){
			$ids[] = strval($node);
		}
		return $ids;
	}
	
	private function getGroupIds(){
		$nodes = $this->dom()->xpath('/tns:database//Group/@id');
		$ids = array();
		foreach ($nodes as $node){
			$ids[] = strval($node);
		}
		return $ids;
	}
	
	private function toJson($value){
		$value = strval($value);
		$result = json_decode($value, TRUE);
		if(is_array($result)){
			return $result;
		}else{
			return $value;
		}
	}
	
	/**
	 * <b>groups getter</b>
	 * 
	 * <b>获取groups属性的方法</b>
	 * 
	 * @return array
	 */
	public function groups(){
		return $this->groups;
	}
	
	/**
	 * <b>get a group by the given group id, will cache the group data</b>
	 *
	 * <b>根据物品组id获取一个物品组的信息，会对物品组信息进行缓存</b>
	 * 
	 * @param int $id
	 * @param boolean $asArray
	 * @return ItemGroup
	 */
	public function getGroup($id, $asArray = false){
		if($asArray){		
			if(isset($this->groupAsArrays[$id])){
				return $this->groupAsArrays[$id];
			}
		}else{
			if(isset($this->groups[$id])){
				return $this->groups[$id];
			}
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->proxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());
		if ( pathinfo($this->file,PATHINFO_EXTENSION) != 'txt')
		{
			$result = $manager->loadGroup($id, $asArray);
		}
		else
		{
			$result = $manager->loadFileGroup($id, $asArray);
		}
		if($asArray){
			$this->groupAsArrays[$id] = $result;
		}else{
			$this->groups[$id] = $result;
		}
		return $result;
	}
	
	/**
	 * <b>get all ItemGroup infomations, will cache the group data</b>
	 *
	 * <b>获取所有物品组信息，会对物品组信息进行缓存</b>
	 * 
	 * @return array
	 */
	public function getAllGroups(){
		$ids = $this->getGroupIds();
		return $this->getGroups($ids);
	}

	/**
	 * <b>get multi groups by the given group ids, will cache the group data</b>
	 *
	 * <b>根据物品组id获取多个物品组信息，会对物品组信息进行缓存</b>
	 * 
	 * @param array
	 * @param boolean $asArray
	 * @return array
	 */
	public function getGroups($ids, $asArray = false){
		$results = array();
		$gets = array();
		foreach($ids as $id){
			if(!$asArray && isset($this->groups[$id])){
				$results[$id] = $this->groups[$id];
			}elseif($asArray && isset($this->groupAsArrays[$id])){
				$results[$id] = $this->groupAsArrays[$id];
			}else{
				$gets[$id] = $id;
			}
		}
		if(empty($gets)){
			return $results;
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->batchProxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());
		foreach($gets as $id){
			$manager->loadGroup($id, $asArray);
		}
		$batch = $manager->batch();
		foreach($batch as $value){
			if(!$value){
				continue;
			}
			$id = null;
			if($asArray){
				$id = $value['id'];
				$this->groups[$id] = $value;
			}else{				
				$id = $value->get('id');
				$this->groupAsArrays[$id] = $value;	
			}	
			$results[$id] = $value;
		}
		return $results;
	}
	
	/**
	 * <b>get an item by the given item id, will cache the item data</b>
	 *
	 * <b>根据物品id获取物品信息，会对物品信息进行缓存</b>
	 * 
	 * @param int $id
	 * @param boolean $asArray
	 * @return ItemSpec
	 */
	public function getItem($id, $asArray = false){
		if($asArray){		
			if(isset($this->itemAsArrays[$id])){
				return $this->itemAsArrays[$id];
			}
		}else{
			if(isset($this->items[$id])){
				return $this->items[$id];
			}
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->proxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());

		if ( pathinfo($this->file,PATHINFO_EXTENSION) != 'txt')
		{
			$result = $manager->loadItem($id, $asArray);
		}
		else
		{
			$result = $manager->loadFileItem($id, $asArray);
		}
		if($asArray){
			$this->itemAsArrays[$id] = $result;
		}else{
			$this->items[$id] = $result;
		}
		return $result;
	}
	
	/**
	 * <b>get all ItemSpec infomations, will cache the ItemSpec data</b>
	 *
	 * <b>获取所有物品定义信息，会对物品定义信息进行缓存</b>
	 * 
	 * @return array
	 */
	public function getAllItemSpecs(){
		$ids = $this->getItemIds();
		return $this->getItems($ids);
	}
	
	/**
	 * <b>get multi items by the given item ids, will cache the item data</b>
	 *
	 * <b>根据物品id获取多个物品信息，会对物品信息进行缓存</b>
	 * 
	 * @param array $ids
	 * @param boolean $asArray
	 * @return array
	 */
	public function getItems(array $ids, $asArray = false){
		$results = array();
		$gets = array();
		foreach($ids as $id){
			if(!$asArray && isset($this->items[$id])){
				$results[$id] = $this->items[$id];
			}elseif($asArray && isset($this->itemAsArrays[$id])){
				$results[$id] = $this->itemAsArrays[$id];
			}else{
				$gets[$id] = $id;
			}
		}
		if(empty($gets)){
			return $results;
		}
		import('module.aop.XAOPFactory');
		$factory = XAOPFactory::singleton();
		$manager = $factory->batchProxy($this);
		import('module.aop.XCacheAspect');
		$factory->addAspect(__CLASS__, new XCacheAspect());
		foreach($gets as $id){
			$manager->loadItem($id, $asArray);
		}
		$batch = $manager->batch();
		foreach($batch as $value){
			if(!$value){
				continue;
			}
			$id = null;
			if($asArray){
				$id = $value['id'];
				$this->items[$id] = $value;
			}else{				
				$id = $value->get('id');
				$this->itemAsArrays[$id] = $value;	
			}	
			$results[$id] = $value;
		}
		return $results;
	}
}
?>