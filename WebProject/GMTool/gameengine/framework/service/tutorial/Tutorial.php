<?php
import('persistence.dao.RAbstractEntityCollection');
/**
 * Tutorial
 * 
 * tutorial model class
 * 
 * 新手引导模型定义类
 * 
 * @author Tianwei
 * @package tutorial
 */
class Tutorial extends RAbstractEntityCollection{
	/**
	 * @Save(type=value)
	 */
	protected $items = array();	
	
	public function complete($uid){
		$item = $this->getItem($uid);
		if(!$item){
			return $this;
		}
		$item->setCompleted(true);
		$this->updateItem($item);
		return $this;
	}
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
	static function getWithUID($uid){
		$res = self::getOne(__CLASS__, $uid);
		if($res)
		{
			$res->unserializeProperty('items');
		}
		return $res;
	}
	public function save(){
		$this->serializeProperty('items');
		parent::save();
	}
}
?>