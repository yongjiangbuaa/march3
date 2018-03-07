<?php
import('persistence.dao.XActiveRecord');
/**
* XUnmodifiableActiveRecord
*
* unmodifiable active record orm class
*
* 不可更改的active record持久化实体类，实体创建后将不能updata和save
*
* @author Wangqi
* @package dao
*/
class XUnmodifiableActiveRecord extends XActiveRecord{
	/**
	 * save the entity
	 * 
	 * 保存实例的方法，调用该方法会抛出Exception异常
	 * 
	 * @param array $values 属性数组
	 * @throws Exception
	 */
	public function save(array $values = array()){
		if($this->saved){
			throw new Exception('save is not alowed. can not modify class '.get_class($this));
		}
		return $this->insert($values);
	}
	
	/**
	* update the entity
	*
	* 更改实例的方法，调用该方法会抛出Exception异常
	*
	* @param array $values 属性数组
	* @throws Exception
	*/
	public function update(array $values = array()){
		throw new Exception('update is not alowed. can not modify class '.get_class($this));
	}
	
	/**
	 * check whether the entity is changed, always return false
	 * 
	 * 检查entity是否被更改过，返回false
	 * 
	 * @return bool
	 */
	public function isChanged(){
		return FALSE;
	}
}