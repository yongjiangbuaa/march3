<?php
/**
 * XCacheAccess
 * 
 * Cache Access Interface
 * 
 * 缓存接口，符合JSR-107中CacheAccess接口规范
 * 
 * @author Tianwei
 * @interface
 * @package cache
 */
interface XCacheAccess{
	/** 
	 * cache data getter
	 * 
	 * 获取缓存数据
	 * 
	 * @param mixed $key 缓存主键
	 * @return mixed
	 */
	public function get($key);
	
	/** 
	 * cache access attributes getter
	 * 
	 * 获取缓存操作实例的配置信息
	 * 
	 * @return array
	 */
	public function getAttributes();
		
	/** 
	 * cache access name getter
	 * 
	 * 获取缓存操作实例的名字，默认返回区域名
	 * 
	 * @return string
	 */
	public function getName();
	
	/** 
	 * cache access region getter
	 * 
	 * 获取缓存操作实例的区域名
	 * 
	 * @return string
	 */
	public function getRegion();
		
	/** 
	 * cache data putter
	 * 
	 * 将数据放到缓存中
	 * 
	 * @param mixed $key 缓存主键
	 * @param mixed $value 缓存数据
	 * @param int $ttl 缓存过期时间，默认为1小时
	 * @return XCacheAccess
	 */
	public function put($key, $value, $ttl = 3600);
	
	/** 
	 * removing cache data from this region
	 * 
	 * 将数据从缓存中删除
	 * 
	 * @param mixed $key 缓存主键
	 * @return mixed
	 */	
	public function remove($key);

	/** 
	 * clear all cache data from this region
	 * 
	 * 清空缓存
	 * 
	 * @return mixed
	 */		
	public function removeAll();
}
?>