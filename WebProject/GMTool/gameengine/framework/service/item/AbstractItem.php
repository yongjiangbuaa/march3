<?php
import('util.model.XObject');
import('service.item.Item');
/**
 * AbstractItem
 * 
 * abstract item class
 * 
 * 物品定义抽象类，继承XObject类, 实现Item接口
 * 
 * @author Tianwei
 * @abstract
 * @package item
 */
abstract class AbstractItem extends XObject implements Item{
	protected $id;
	protected $name;
	protected $description;
	protected $parent;
	
	/** 
	 * <b>id getter</b>
	 * 
	 * <b>获取id属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getId(){
		return $this->id;
	}
	
	/** 
	 * <b>id setter</b>
	 * 
	 * <b>设置id属性的方法</b>
	 * 
	 * @param string $id
	 * @return Item
	 */	
	public function setId($id){
		$this->id = $id;
		return $this;
	}	
	
	/** 
	 * <b>name getter</b>
	 * 
	 * <b>获取name属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getName(){
		return $this->name;
	}
	
	/** 
	 * <b>name setter</b>
	 * 
	 * <b>设置name属性的方法</b>
	 * 
	 * @param string $id
	 * @return Item
	 */	
	public function setName($name){
		$this->name = $name;
		return $this;
	}
	
	/** 
	 * <b>description getter</b>
	 * 
	 * <b>获取description属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getDescription(){
		return $this->description;
	}
	
	/** 
	 * <b>description setter</b>
	 * 
	 * <b>设置description属性的方法</b>
	 * 
	 * @param string $id
	 * @return Item
	 */	
	public function setDescription($description){
		$this->description = $description;
		return $this;
	}	
	
	/** 
	 * <b>parent getter</b>
	 * 
	 * <b>获取parent属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getParent(){
		return $this->parent;
	}
	
	/** 
	 * <b>parent setter</b>
	 * 
	 * <b>设置parent属性的方法</b>
	 * 
	 * @param string $id
	 * @return Item
	 */	
	public function setParent($parent){
		$this->parent = $parent;
		return $this;
	}
}
?>