<?php
import('persistence.dao.RActiveRecord');
/** 
 * TutorialStep
 * 
 * tutorial step model
 * 
 * 新手引导步骤模型定义，新手引导步骤在数据库中存储的模型
 * 
 * @Entity
 * @author Tianwei
 * @package tutorial
 */
class TutorialStep extends RActiveRecord{
	/** 
	 * @Id
	 */
	protected $uid = null;
		
   /**
	* 此步属于哪个tutorial
	*/
    protected $tutorial = null;
    
    protected $time = 0;
	/**
	*此步的名字
	*/
// 	protected $name = null;
	/**
	*在tutorial里是第几步,从1开始
	*/
// 	protected $index = 0;
	/**
	*是否完成tutorial
	*/
// 	protected $completed = false;
	
	/**
	 * <b>tutorial getter</b>
	 * 
	 * <b>获取tutorial属性的方法</b>
	 * 
	 * @return string
	 */
	public function getTutorial(){
		return $this->tutorial;
	}
	
	/**
	 * <b>tutorial setter</b>
	 * 
	 * <b>设置tutorial属性的方法</b>
	 * 
	 * @param string $uid
	 * @return TutorialStep
	 */
	public function setTutorial($tutorial){
		$this->tutorial = $tutorial;
		return $this;
	}
	public function setUserUid($uid){
		$this->uid = $uid;
		return $this;
	}
	public function setTime($time){
		$this->time = $time;
		return $this;
	}
	
	/**
	 * <b>name getter</b>
	 * 
	 * <b>获取name属性的方法</b>
	 * 
	 * @return string
	 */
// 	public function getName(){
// 		return $this->name;
// 	}
	
	/**
	 * <b>name setter</b>
	 * 
	 * <b>设置name属性的方法</b>
	 * 
	 * @param string $name
	 * @return TutorialStep
	 */
// 	public function setName($name){
// 		$this->name = $name;
// 		return $this;
// 	}	
	
	/**
	 * <b>index getter</b>
	 * 
	 * <b>获取index属性的方法</b>
	 * 
	 * @return string
	 */
// 	public function getIndex(){
// 		return $this->index;
// 	}
	
	/**
	 * <b>index setter</b>
	 * 
	 * <b>设置index属性的方法</b>
	 * 
	 * @param string $index
	 * @return TutorialStep
	 */
// 	public function setIndex($index){
// 		$this->index = $index;
// 		return $this;
// 	}
	
	/**
	 * <b>check whether the tutorial step is completed</b>
	 * 
	 * <b>判断当前新手引导步骤是否已经完成</b>
	 * 
	 * @return boolean
	 */
// 	public function isCompleted(){
// 		return $this->completed;
// 	}
	
	/**
	 * <b>completed setter</b>
	 * 
	 * <b>设置completed属性的方法</b>
	 * 
	 * @param boolean $completed
	 * @return TutorialStep
	 */
// 	public function setCompleted($completed){
// 		$this->completed = $completed;
// 		return $this;
// 	}	
	
	/**
	 * 根据主键取得记录对象实例
	 *
	 * @param unknown_type $uid
	 * @return unknown
	 */
// 	static function getWithUID($uid){
// 		$res = self::getOne(__CLASS__, $uid);
// 		return $res;
// 	}
	public function save(){
		parent::save();
	}
}
?>