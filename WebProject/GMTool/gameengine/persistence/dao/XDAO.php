<?php
/**
 * XDAO
 * 
 * dao (Data Access Object) interface
 * 
 * 数据访问接口
 * 
 * @author Tianwei
 * @interface
 * @package dao 
 */
interface XDAO{
	/** 
	 * <b>persistent method</b>
	 * 
	 * <b>实体持久化方法</b>
	 * 
	 * @param object $model 实体对象
	 * @return mixed
	 */	
	public function save($model);
	/** 
	 * <b>removing method</b>
	 * 
	 * <b>实体删除方法</b>
	 * 
	 * @param object $model 实体对象
	 * @return mixed
	 */	
	public function remove($model);
}
?>