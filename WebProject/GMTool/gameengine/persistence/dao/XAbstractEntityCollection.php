<?php
import('persistence.dao.XEntityCollection');
import('persistence.dao.XActiveRecord');
/**
 * XAbstractEntityCollection
 * 
 * abstract entity collection class
 * 
 * <b>实现实体集合接口的抽象类，实现了实体集合操作以及遍历方法</b>
 * 
 * 用户只需要继承该类并实现自己特有方法
 * 
 * @author Tianwei
 * @abstract
 * @package dao 
 */
abstract class XAbstractEntityCollection extends XActiveRecord implements XEntityCollection{
	/** 
	 * @Id
	 * @GeneratedValue(strategy=auto)
	 */
	protected $uid = null;
		
 	/**
 	 * @Save(type=reference)
 	 * @Cascade(type=save)
 	 */
	protected $items = array();
	
	/** 
	 * get a entity item by uid, supporting lazy loading
	 * <b>通过uid得到一个实体，会自动延迟加载</b>
	 * 
	 * 传入的uid可以有两种形式
	 * 
	 * 一种是普通的uid，根据uid生成策略，由用户指定或者ORM自动生成
	 * 
	 * 另一种是ClassName:uid形式，是数据库延迟加载数据的存储形式
	 * 
	 * 这两种uid都会最终解析成普通uid，用户无需特别生成ClassName:uid形式
	 * 
	 * 如果指定uid的对应实体已经存在或为空，会直接返回
	 * 
	 * 如果需要延迟加载，会自动延迟加载该实体，并返回
	 * 
	 * <b>本类items属性默认声明为延迟加载</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param string $uid 实体uid
	 * @return object
	 */
	public function getItem($uid){
		if(empty($uid)
			&& $uid !== 0){
			return null;
		}
		$pos = strpos($uid, ':');
		if($pos !== false){
			$uid = substr($uid, $pos + 1);
		}
		if(!$this->dao()->isByReference('items')){			
			if(!isset($this->items[$uid])){
				return null;		
			}
			return $this->items[$uid];	
		}
		if(!isset($this->items[$uid])){
			return null;			
		}
		$result = $this->items[$uid];
		if(is_object($result)
			|| !$this->dao()->isLazyLoad('items')){
			return $result;
		}
		import('persistence.orm.PersistenceSession');
		$item = PersistenceSession::singleton()->getStreamHelper()->keyClassSearch($result);
		if($item){
			$this->items[$uid] = $item;
		}elseif(isset($this->items[$uid])){
			unset($this->items[$uid]);
		}
		return $item;
	}
	
	/** 
	 * <b>add a new item</b>
	 * 
	 * <b>添加一个实体</b>
	 * 
	 * 传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的save方法对实体进行保存
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 如果实体传入函数之前没有uid，会自动给实体分配uid，但不会显式保存
	 * 
	 * <b>本类items属性默认声明为引用方式</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param object $item 实体
	 * @return XEntityCollection
	 */
	public function addItem($item){
		if(!$item){
			return $this;
		}
		$pk = $item->dao()->id();
		$uid = $item->get($pk);
		$byReference = $this->dao()->isByReference('items');
		if(!$uid && !$byReference){
			import('persistence.orm.PersistenceSession');
			$uid = PersistenceSession::singleton()->getStreamHelper()->getGUID();
			$item->set($pk, $uid);
		}
		if($byReference){
			$item->save();
		}
		$this->items[$item->get($pk)] = $item;
		return $this;
	}
	
	/** 
	 * <b>add new items</b>
	 * 
	 * <b>添加多个实体，会自动做批量持久化，以改善性能</b>
	 * 
	 * <b>强烈建议添加多个实体时调用此接口而不是多次调用addItem</b>
	 * 
	 * 与addItem一致，传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的save方法对实体进行保存
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 如果实体传入函数之前没有uid，会自动给实体分配uid，但不会显式保存
	 * 
	 * <b>本类items属性默认声明为引用方式</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * Code Example
	 * <code>
	 * $items = array();
	 * for($i = 0; $i < 10; $i++){
	 * 		$items[] = new OwnedItem();
	 * }
	 * $this->addItems($items);
	 * </code>
	 * 
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function addItems(array $items){
		import('persistence.orm.PersistenceSession');
		$byReference = $this->dao()->isByReference('items');
		$puts = array();
		foreach($items as $item){
			if(!$item){
				continue;
			}
			$pk = $item->dao()->id();
			$uid = $item->get($pk);
			if(!$uid && !$byReference){		
				$uid = PersistenceSession::singleton()->getStreamHelper()->getGUID();
				$item->set($pk, $uid);
			}
			if($byReference){
				$puts[] = $item;
			}
			$this->items[$item->get($pk)] = $item;
		}
		if(!empty($puts)){
			PersistenceSession::singleton()->put($puts);
		}
		return $this;
	}
	
	/** 
	 * <b>update an item</b>
	 * 
	 * <b>更新一个实体</b>
	 * 
	 * 与addItem一致，传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的save方法对实体进行保存
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 如果实体传入函数之前没有uid，会自动给实体分配uid，但不会显式保存
	 * 
	 * <b>本类items属性默认声明为引用方式</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param object $item 实体
	 * @return XEntityCollection
	 */
	public function updateItem($item){
		return $this->addItem($item);
	}
	
	/** 
	 * <b>update a set of items</b>
	 * 
	 * <b>更新多个实体，会自动做批量持久化，以改善性能</b>
	 * 
	 * <b>强烈建议添加多个实体时调用此接口而不是多次调用updateItem</b>
	 * 
	 * 与addItem一致，传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的save方法对实体进行保存
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 如果实体传入函数之前没有uid，会自动给实体分配uid，但不会显式保存
	 * 
	 * <b>本类items属性默认声明为引用方式</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function updateItems(array $items){
		return $this->addItems($items);
	}
	
	/** 
	 * <b>remove an item</b>
	 * 
	 * <b>删除一个实体</b>
	 * 
	 * <b>如果该实体没有主键，不会做实际删除操作</b>
	 * 
	 * <b>为避免出现数据不一致的情况，本方法会显式保存XAbstractEntityCollection对象。请避免多次调用本方法。</b>
	 * 
	 * 与addItem类似，传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的remove方法对实体进行删除
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 不会显式调用实体的remove方法
	 * 
	 * <b>本类items属性默认声明为引用方式<b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释<b>
	 * 
	 * @param object $item 实体
	 * @return XEntityCollection
	 */
	public function removeItem($item){
		if(!$item){
			return $this;
		}
		$pk = $item->dao()->id();
		$uid = $item->get($pk);
		if(!$uid){
			return false;
		}
		if($this->dao()->isByReference('items')){
			$item->remove();
		}
		unset($this->items[$uid]);
		$this->save();
		return $this;
	}
	
	/** 
	 * <b>remove a set of items</b>
	 * 
	 * <b>删除多个实体</b>
	 * 
	 * <b>强烈建议删除多个实体时调用此接口而不是多次调用removeItem()</b>
	 * 
	 * <b>如果该实体没有主键，不会做实际删除操作</b>
	 * 
	 * <b>为避免出现数据不一致的情况，本方法会显式保存XAbstractEntityCollection对象</b>
	 * 
	 * 与removeItem一致，传入的实体处理和持久化策略根据items属性是否以引用方式保存而不同
	 * 
	 * 引用方式：
	 * 
	 * 在items属性上声明注释@Save(type=reference)
	 * 
	 * 会显式调用实体的remove方法对实体进行删除
	 * 
	 * 非引用方式/值方式:
	 * 
	 * 在items属性上去掉注释@Save(type=reference)
	 * 
	 * 不会显式删除
	 * 
	 * <b>本类items属性默认声明为引用方式</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function removeItems(array $items){
		$byReference = $this->dao()->isByReference('items');
		foreach($items as $item){
			$pk = $item->dao()->id();
			$uid = $item->get($pk);
			if(!$uid){
				continue;
			}
			if($byReference){
				$item->remove();
			}
			unset($this->items[$uid]);
		}
		$this->save();
		return $this;
	}
	
	/** 
	 * <b>get the uid array of all items</b>
	 * 
	 * <b>取得所有实体的uid</b>
	 * 
	 * @return array
	 */	
	public function getItemIds(){
		return array_keys($this->items);
	}
	
	/** 
	 * <b>get a set of items by given uid array</b>
	 * 
	 * <b>用一组uid取得多个实体</b>
	 * 
	 * <b>如果$uids数组为空，将以数组形式返回所有实体</b>
	 * 
	 * 与getItem一致，传入的uid可以有两种形式
	 * 
	 * 一种是普通的uid，根据uid生成策略，由用户指定或者ORM自动生成
	 * 
	 * 另一种是ClassName:uid形式，是数据库延迟加载数据的存储形式
	 * 
	 * 这两种uid都会最终解析成普通uid，用户无需特别生成ClassName:uid形式
	 * 
	 * 如果指定uid的对应实体已经存在或为空，会返回该实体或null
	 * 
	 * 如果需要延迟加载，会自动延迟加载该实体，并返回
	 * 
	 * <b>本类items属性默认声明为延迟加载</b>
	 * 
	 * <b>继承本类的子类可以重新声明items属性，使用新的注释覆盖本来的默认注释</b>
	 * 
	 * @param array $uids uid数组
	 * @return array
	 */
	public function getItems(array $uids = array()){
		if(empty($uids)){		
			return $this->get('items');
		}
		foreach($uids as $key => $uid){
			$pos = strpos($uid, ':');
			if($pos !== false){
				$uids[$key] = substr($uid, $pos + 1);
			}
		}
		$byReference = $this->dao()->isByReference('items');
		$results = array();
		if(!$byReference){
			foreach($uids as $uid){
				if(isset($this->items[$uid])){
					$results[$uid] = $uid;
				}
			}
			return $results;
		}
		$gets = array();
		foreach($uids as $uid){
			if(!isset($this->items[$uid])){
				continue;
			}
			$result = $this->items[$uid];
			if(is_object($result)){
				$results[$uid] = $result;
				continue;
			}
			$gets[$uid] = $result;
		}
		if(empty($gets)){
			return $results;
		}
		import('persistence.orm.PersistenceSession');
		$gets = PersistenceSession::singleton()->getStreamHelper()->keyClassSearch($gets);
		foreach($gets as $key => $value){
			if(!is_object($value)){
				if(isset($this->items[$key])){
					unset($this->items[$key]);
				}
				continue;
			}
			$this->items[$key] = $value;
			$results[$key] = $value;
		}
		return $results;
	}
		
	/** 
	 * <b>return the object as array</b>
	 * 
	 * <b>将对象转换为数组形式输出</b>
	 * 
	 * <b>本方法将多个实体以数组形式封装和输出，不会输出除items之外的其他属性</b>
	 * 
	 * <b>继承本类的子类可以覆盖asArray方法，使用自定义输出</b>
	 * 
	 * @return array
	 */
	public function asArray(){
		$attrs = array();
		foreach($this->getItems() as $item){
			if(is_object($item)){	
				$attrs[$item->get('uid')] = $item->attrs();
			}
		}
		return $attrs;
	}
	
	/** 
	 * <b>return the current item</b>
	 * 
	 * <b>返回实体数组的当前实体，用于遍历</b>
	 * 
	 * @return object
	 */	
    public function current() {
        return current($this->items);
    }
     
   	/** 
	 * <b>goto the next item</b>
	 * 
	 * <b>向前移动到下一个元素</b>
	 */ 
    public function next() {
        next($this->items);   
    }
 
   	/** 
	 * <b>return the current item's key</b>
	 * 
	 * <b>返回当前元素的键</b>
	 * 
	 * @return string
	 */    
    public function key() {
        return key($this->items);
    }
 
   	/** 
	 * <b>goto to the first item</b>
	 * 
	 * <b>返回到迭代器的第一个元素</b>
	 */     
    public function rewind() {
        reset($this->items);
    }
 
   	/** 
	 * <b>check if the current item is valid</b>
	 * 
	 * <b>检查当前位置是否有效</b>
	 * 
	 * @return boolean
	 */     
    public function valid() {                                                                              
        return ($this->current() !== FALSE);
    }
    
   /** 
	 * <b>返回当前集合的大小</b>
	 * 
	 * @return int
	 */  
    public function size(){
    	if(!is_array($this->items)){
    		return 0;
    	}
    	return count($this->items);
    }
}
?>