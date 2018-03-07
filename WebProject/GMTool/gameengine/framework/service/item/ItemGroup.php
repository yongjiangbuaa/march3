<?php
import('service.item.AbstractItem');
/**
 * ItemGroup
 * 
 * item group model
 * 
 * 物品组模型定义，继承AbstractItem抽象类, 实现Iterator接口
 * 
 * @author Tianwei
 * @final
 * @package item
 */
final class ItemGroup extends AbstractItem implements Iterator{
	protected $children = array();
	
	/**
	 * <b>add a child item for the item group</b>
	 * 
	 * <b>为物品组添加一个子物品</b>
	 * 
	 * @param Item $item
	 */
	public function addItem(Item $item){
		$this->children[$item->getId()] = $item;
	}
	
	/**
	 * <b>remove an item from the item group</b>
	 * 
	 * <b>从物品组中移除一个子物品</b>
	 * 
	 * @param Item $item
	 */
	public function removeItem(Item $item){
		unset($this->children[$item->getId()]);
	}
	
	/**
	 * <b>get the information of an itemSpec by item id</b>
	 * 
	 * <b>根据物品id获取一个物品定义的信息，可以选择是否在本物品组内做深度检索</b>
	 * 
	 * @param int $id
	 * @param boolean $deepSearch
	 * @return mixed
	 */
	public function getItem($id, $deepSearch = true){
		if(isset($this->children[$id])){
			return $this->children[$id];
		}
		if(!$deepSearch){
			return null;
		}
		foreach($this->children as $child){
			if($child instanceof ItemGroup){
				$item = $child->getItem($id, $deepSearch);
				if($item){
					return $item;
				}
			}
		}
		return null;
	}
	
	/**
	 * 
	 * <b>get the information of an itemSpec by item name</b>
	 * 
	 * <b>根据物品名称获取一个物品定义的信息，可以选择是否在本物品组内做深度检索</b>
	 * 
	 * @param string $name
	 * @param boolean $deepSearch
	 * @return array
	 */
	public function getItemsByName($name, $deepSearch = true){
		$items = array();
		foreach($this->children as $item){
			if($item->getName() == $name){
				$items[$item->getId()] = $item;
			}
		}
		if(!$deepSearch){
			return $items;
		}
		foreach($this->children as $child){
			if($child instanceof ItemGroup){
				$childItems = $child->getItemsByName($name, $deepSearch);
				foreach($childItems as $childItem){
					$items[$childItem->getId()] = $childItem;
				}
			}
		}
		return $items;
	}
	
	/**
	 * 
	 * <b>get the information of all itemSpecs in the group</b>
	 * 
	 * <b>获取物品组内所有物品定义的信息，可以选择是否在本物品组内做深度检索</b>
	 * 
	 * @param boolean $deepSearch
	 * @return array
	 */
	public function getAllItems($deepSearch = false){
		$results = array();
		foreach($this->children as $child){
			if(!$child instanceof ItemGroup
				|| !$deepSearch){
				$results[] = $child;
				continue;
			}
			$results[] = $child->getAllItems($deepSearch);
		}
		return $results;
	}
	
	/**
	 * 
	 * <b>get all the child groups in the group</b>
	 * 
	 * <b>获取本物品组内所有的子物品组</b>
	 * 
	 * @return array
	 */
	public function getAllGroups(){
		$results = array();
		foreach($this->children as $child){
			if($child instanceof ItemGroup){
				$results[] = $child;
			}
		}
		return $results;		
	}
	
	/**
	 * 
	 * <b>get a child groups by group id</b>
	 * 
	 * <b>根据物品组id来获取一个子物品组，可以选择是否进行深度检索</b>
	 * 
	 * @param int $id
	 * @param boolean $deepSearch
	 * @return mixed
	 */
	public function getChildGroup($id, $deepSearch = true){
		if(isset($this->children[$id])
			&& $this->children[$id] instanceof ItemGroup){
			return $this->children[$id];
		}
		if(!$deepSearch){
			return null;
		}
		foreach($this->children as $child){
			if($child instanceof ItemGroup){
				$item = $child->getChildGroup($id, $deepSearch);
				if($item){
					return $item;
				}
			}
		}
		return null;		
	}
	
	/**
	 * <b>check whether the current group contains an itemSpec</b>
	 * 
	 * <b>判断本物品组是否包含一个物品定义</b>
	 * 
	 * @param Item $item
	 * @return boolean
	 */
	public function contains(Item $item){
		return isset($this->children[$item->getId()]);
	}	
	
	/**
	 * 
	 * <b>get the size of the group</b>
	 * 
	 * <b>获取本物品组的大小，即children数组中共包含多少个元素</b>
	 * 
	 * @return int
	 */
	public function getSize(){
		return count($this->children);
	}
	
	/**
	 * <b>get the children parameter</b>
	 * 
	 * <b>获取children属性</b>
	 * 
	 * @return array
	 */
	public function children(){
		return $this->children;
	}
	/**
	 * (non-PHPdoc)
	 * @see XObject::attrs()
	 */
	public function attrs(){
		$attrs = parent::attrs();
		$children = array();
		foreach($this->children as $child){
			$children[] = $child->attrs();
		}
		$attrs['children'] = $children;
		return $attrs;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Iterator::current()
	 */
    public function current() {
        return current($this->children);
    }
 	
    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next() {
        next($this->children);   
    }
 
    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key() {
        return key($this->children);
    }
 
    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind() {
        reset($this->children);
    }
 
    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid() {                                                                              
        return ($this->current() !== FALSE);
    }
}
?>