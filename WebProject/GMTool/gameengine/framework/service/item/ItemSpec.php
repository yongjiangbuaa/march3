<?php
import('service.item.AbstractItem');
/**
 * ItemSpec
 * 
 * itemSpec model
 * 
 * 物品模型定义，继承AbstractItem抽象类
 * 
 * @author Tianwei
 * @package item
 */
class ItemSpec extends AbstractItem{
	/**
	 * <b>check whether the item is in the given item group</b>
	 * 
	 * <b>检验一个物品是否在给定的物品组内</b>
	 * 
	 * @param string $groupId
	 * @return booolean
	 */
	public function inGroup($groupId){
		$parent = $this->parent;
		while($parent){
			if($parent->getId() == $groupId) 
				return true;
			$parent = $parent->getParent();
		}
		return false;
	}
	
	/**
	 * get the itemGroup where the itemSpec in
	 * 
	 * 获取当前itemSpec所在的itemGroup
	 * 
	 * @return ItemGroup
	 */
	public function getGroup(){
		return $this->getParent();
	}
	
	public function newOwnedItem(){
		
	}
}
?>