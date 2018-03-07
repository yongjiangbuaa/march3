<?php
/**
 * XEntityCollection
 * 
 * entity collection interface
 * 实体集合的接口，用来对同一个类型的数量可变的多个实体相关操作进行封装，并且支持遍历
 * @author Tianwei
 * @interface
 * @package dao 
 */
interface XEntityCollection extends Iterator{
	/** 
	 * get a entity item by uid
	 * 通过uid得到一个实体
	 * @param string $uid 实体uid
	 * @return object
	 */
	public function getItem($uid);
	/** 
	 * add a new item
	 * 添加一个实体
	 * @param object $item 实体
	 * @return XEntityCollection
	 */
	public function addItem($item);
	/** 
	 * add a set of new items
	 * 添加多个实体
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function addItems(array $items);
	/** 
	 * update an item
	 * 更新一个实体
	 * @param object $item 实体
	 * @return XEntityCollection
	 */	
	public function updateItem($item);
	/** 
	 * update a set of items
	 * 更新多个实体
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function updateItems(array $items);
	/** 
	 * remove a item
	 * 删除一个实体
	 * @param object $item 实体
	 * @return XEntityCollection
	 */	
	public function removeItem($item);
	/** 
	 * remove a set of items
	 * 删除多个实体
	 * @param array $items 实体数组
	 * @return XEntityCollection
	 */
	public function removeItems(array $items);
	/** 
	 * get the uid array of all items
	 * 取得所有实体的uid
	 * @return array
	 */
	public function getItemIds();
	/** 
	 * get a set of items by given uid array
	 * 用一组uid取得多个实体
	 * @param array $uids uid数组
	 * @return array
	 */	
	public function getItems(array $uids = array());
}
?>