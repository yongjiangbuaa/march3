<?php
import('module.event.XApplicationEvent');
/**
 * ServiceFinishedEvent
 * 
 * service finished event
 * 
 * service完成事件
 * 
 * @author Tianwei
 * @package service
 */
class ServiceFinishedEvent extends XApplicationEvent{
	protected $result = null;
	
	/**
	 * <b>result setter</b>
	 * 
	 * <b>注入XServiceResult实例的方法</b>
	 * 
	 * @param XServiceResult $result
	 * @return ServiceFinishedEvent
	 */
	public function setResult($result){
		$this->result = $result;
		return $this;
	}
	
	/** 
	 * <b>result getter</b>
	 * 
	 * <b>返回XServiceResult实例的方法</b>
	 * 
	 * @return XServiceResult
	 */	
	public function getResult($result){
		return $this->result;
	}
}
?>