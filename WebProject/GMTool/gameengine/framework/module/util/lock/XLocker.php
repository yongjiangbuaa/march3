<?php
import('module.util.lock.XLockable');
/**
 * XLocker
 * 
 * locker Interface
 * 
 * 锁接口
 * 
 * @author Tianwei
 * @interface
 * @package util.lock
 */
interface XLocker{
	/**
	 * check whether the lockable object is locked
	 * 
	 * 检查可锁对象是否已经被锁住
	 * 
	 * @param XLockable $lockable
	 */
	public function isLocked(XLockable $lockable);
	/**
	 * lock the specified lockable object
	 * 
	 * 将指定的可锁对象锁住
	 * 
	 * @param XLockable $lockable
	 * @param int $defaultTime
	 */
	public function lock(XLockable $lockable, $defaultTime = 60);
	/**
	 * release the lock on the specified lockable object
	 * 
	 * 释放可锁对象上的锁
	 * 
	 * @param XLockable $lockable
	 */
	public function release(XLockable $lockable);
}
?>