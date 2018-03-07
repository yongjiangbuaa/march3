<?php
import('module.util.lock.XLocker');
import('module.util.lock.XLockable');
/**
 * XCacheLocker
 * 
 * cache locker
 * 
 * 缓存锁，锁信息存储在缓存中
 * 
 * @author Tianwei
 * @interface
 * @package util.lock
 */
class XCacheLocker implements XLocker{
	protected $cache = null;
	/**
	 * construct method
	 * 
	 * 构造函数，根据指定区域加载缓存访问类
	 * 
	 * @param string $region
	 * @param boolean $auto
	 * @throws XException
	 */
	function __construct($region, $auto=true){
		if($auto){
			import('cache.CacheServiceFactory');
			$this->cache = CacheServiceFactory::getCacheAccess($region);
			if(!$this->cache){
				import('module.context.XException');
				throw new XException("XCacheLocker: cache {$region} is not configured or loaded properly");
			}
		}
	}
	/**
	 * cacheAccess setter
	 * 
	 * 设置cacheAccess属性
	 * 
	 * @param XCacheAccess $cacheAccess
	 */
	public function setCache($cacheAccess){
		$this->cache = $cacheAccess;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLocker::isLocked()
	 */
	public function isLocked(XLockable $lockable){
		$lock = $this->cache->get($lockable->getLockId());
		if(!$lock){
			return false;
		}
		return true;
	}
	/**
	 * (non-PHPdoc)
	 * @see XLocker::lock()
	 */
	public function lock(XLockable $lockable, $defaultTime = 60){
		$lockId = $lockable->getLockId();
		$this->cache->set($lockId, $lockId, $defaultTime);
	}
	/**
	 * (non-PHPdoc)
	 * @see XLocker::release()
	 */
	public function release(XLockable $lockable){
		$this->cache->delete($lockable->getLockId());
		$lockable->onLockReleased($this);
		return true;
	}
}
?>