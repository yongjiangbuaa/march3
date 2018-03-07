<?php
import('module.util.lock.XLocker');
/**
 * XLockable
 * 
 * Lockable Object Interface
 * 
 * 可锁对象接口
 * 
 * @author Tianwei
 * @interface
 * @package util.lock
 */
interface XLockable{
	/**
	 * get lock id of the object
	 * 
	 * 获取可锁对象的lockId
	 * 
	 * @return string
	 */
	public function getLockId();
	/**
	 * callback method when lock conflicted
	 * 
	 * 锁冲突时的回调函数
	 * 
	 * @param XLocker $locker
	 */
	public function onLockConflicted(XLocker $locker);
	/**
	 * callback method when lock released
	 * 
	 * 锁释放时的回调函数
	 * 
	 * @param XLocker $locker
	 */
	public function onLockReleased(XLocker $locker);
}
?>