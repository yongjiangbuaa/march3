<?php
/**
 * XCacheable
 * 
 * Cacheable Object Interface
 * 
 * 可被方法级缓存的对象接口
 * 
 * @author Tianwei
 * @interface
 * @package cache
 */
interface XCacheable{
	/** 
	 * method cache key getter
	 * 
	 * 获取缓存主键
	 * 
	 * 实现该接口时，应该根据传入参数，生成缓存主键，并返回
	 * 
	 * @param string $method 方法
	 * @param array $args 参数
	 * @param array $config 缓存配置
	 * @return mixed
	 */
	public function getCacheKey($method, $args = array(), $config = array());
	/**
	 * callback when cacheable object is updated
	 * 
	 * 当被缓存的对象存入缓存后的回调函数
	 * 
	 * <b>注意，如果是批量缓存，该主键和数据都是数组</b>
	 * 
	 * @param mixed $cacheKey 缓存主键
	 * @param mixed $cacheValue 缓存数据
	 * @return mixed
	 */
	public function onCacheUpdated($cacheKey, $cacheValue);
}
?>