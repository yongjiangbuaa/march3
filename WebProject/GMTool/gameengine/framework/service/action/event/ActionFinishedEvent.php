<?php
import('module.event.XApplicationEvent');
/**
 * ActionFinishedEvent
 * 
 * action finished event
 * 
 * action完成事件，每个Action处理完成后会发布该事件。
 * 若一次请求中包含多个Action，则会发布多个ActionFinishedEvent。
 * ActionFinishedEvent会记录用户平台ID，用户ID和Action处理结果。
 * 
 * @author Tianwei
 * @package action
 */
class ActionFinishedEvent extends XApplicationEvent{
	protected $platformUID = null;
	protected $userUID = null;
	protected $result = null;
	
	/** 
	 * <b>platformUID setter</b>
	 * 
	 * <b>设置platformUID属性的方法</b>
	 * 
	 * @param string $platformUID
	 * @return ActionFinishedEvent
	 */	
	public function setPlatformUID($platformUID){
		$this->platformUID = $platformUID;
		return $this;
	}
	
	/** 
	 * <b>platformUID getter</b>
	 * 
	 * <b>获取platformUID属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getPlatformUID(){
		return $this->platformUID;
	}
	
	/** 
	 * <b>userUID setter</b>
	 * 
	 * <b>设置userUID属性的方法</b>
	 * 
	 * @param string $userUID
	 * @return ActionFinishedEvent
	 */	
	public function setUserUID($userUID){
		$this->userUID = $userUID;
		return $this;
	}
	
	/** 
	 * <b>userUID getter</b>
	 * 
	 * <b>获取userUID属性的方法</b>
	 * 
	 * @return string
	 */	
	public function getUserUID(){
		return $this->userUID;
	}

	/** 
	 * <b>result setter</b>
	 * 
	 * <b>设置result属性的方法</b>
	 * 
	 * @param XServiceResult $result
	 * @return ActionFinishedEvent
	 */	
	public function setResult($result){
		$this->result = $result;
		return $this;
	}
	
	/** 
	 * <b>result getter</b>
	 * 
	 * <b>获取result属性的方法</b>
	 * 
	 * @return XServiceResult
	 */	
	public function getResult(){
		return $this->result;
	}
}
?>