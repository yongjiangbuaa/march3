<?php
import('module.event.XEventListener');
/**
 * XContextCloseListener
 * 
 * context close listener
 * 
 * 框架关闭监听器
 * 
 * 监听框架关闭事件XingCloudClosedEvent
 * 
 * @EventListener(onEvent="XingCloudClosedEvent")
 * @author Wangqi
 * @package event
 */
class XContextCloseListener implements XEventListener{
	/**
	 * handle the XingCloudClosedEvent, will flush persistence data to DB, send statistics logs
	 * 
	 * 处理框架关闭事件，此方法会将持久化数据flush到数据库，发送统计分析日志数据
	 * 
	 * @param XEvent $event 事件对象
	 */
	public function onEvent(XEvent $event){
		try {
			if(isset($GLOBALS['XExceptionThrowed']) && $GLOBALS['XExceptionThrowed']){
				//do nothing currently
			}else{
				//send log
				if(class_exists('StatisticsManager', false)){
					StatisticsManager::singleton()->sendLog();
				}
				//flush DB data
				if(class_exists('PersistenceSession', false)){
					PersistenceSession::singleton()->flush();
				}
			}
		}
		catch(Exception $e) {
		}
	}
}
?>