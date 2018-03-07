<?php
defined('CACHE_CONFIG_DIR') or define("CACHE_CONFIG_DIR", dirname(dirname(__FILE__)).'/config');
/**
 * CacheServiceFactory
 * 
 * Cache Service Factory
 * 
 * cache服务工厂，获取cache实例
 * 
 * @author Wangqi
 * @package cache
 */
class CacheServiceFactory{
	private static $cacheServices = array();
	private static $config = array();
	
	/**
	 * get memcache client instance
	 * 
	 * 获取memcache client实例的方法，通过配置文件配置memcache信息
	 * 没有配置信息返回null
	 * 
	 * @static
	 */
	public static function getCacheService(){
		if(array_key_exists('memcacheClient', self::$cacheServices)){
			return self::$cacheServices['memcacheClient'];
		}
		require_once 'xingcloud.php';
		$memcache = new memcache();
		$memcache->addServer(xingcloud_get('memcache_host'), xingcloud_get('memcache_port'), 1);
		$memcache->setCompressThreshold(10000, 0.3);
		self::$cacheServices['memcacheClient'] = $memcache;
		return $memcache;
	}
	/**
	 * get CacheAccess instance
	 * 
	 * 获取CacheAccess实例的方法，通过配置文件配置cache信息
	 * 支持apc, memcache, 默认为获取XMemcacheCacheAccess实例
	 * 
	 * @static
	 * @param string $type
	 */
	public static function getCacheAccess($type='memcache'){
		self::load();
		if(!array_key_exists($type, self::$cacheServices)){
			return null;
		}
		return self::$cacheServices[$type];
	}
	
	private static function load(){
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		self::$cacheServices['memcache'] = $cache;
// 		if(empty(self::$config)){
// 			import('util.config.XConfig');
// 			XConfig::singleton()->addConfigDir(CACHE_CONFIG_DIR);
// 			XConfig::singleton()->load();
// 			self::$config = XConfig::singleton()->get('cache');
// 			if(!self::$config){
// 				return false;
// 			}
// 			foreach(self::$config as $key => $value){
// 				if(!isset($value['type'])){
// 					continue;
// 				}
// 				$class = "X".ucfirst(strtolower($value['type']))."CacheAccess";
// 				import('cache.module.'.$class);
// 				self::$cacheServices[$key] = new $class($key, $value);			
// 			}
// 		}
	}
}
?>