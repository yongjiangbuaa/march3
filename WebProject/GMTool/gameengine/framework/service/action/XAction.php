<?php
/**
 * XAction
 * 
 * action interface
 * 
 * action接口
 * 
 * @author Tianwei
 * @interface
 * @package action 
 */
interface XAction{
	/**
	 * <b>execute an action</b>
	 * 
	 * <b>执行一个action的入口</b>
	 * 
	 * @param XActionRequest $request
	 */
	public function doExecute(XActionRequest $request);
	/** 
	 * <b>user getter</b>
	 * 
	 * <b>获取user属性的方法</b>
	 * 
	 * @return UserProfile
	 */	
	public function getUser();
}
?>