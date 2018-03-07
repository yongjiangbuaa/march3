<?php
import('cache.module.XAbstractCacheAccess');
/**
 * XMemcacheCacheAccess
 * 
 * Memcache Cache Access Class
 * 
 * Memcache缓存访问类
 * 
 * @author Tianwei
 * @package cache
 */
class XMemcacheCacheAccess extends XAbstractCacheAccess{
	protected $memcache = null;
	
	/** 
	 * XMemcacheCacheAccess constructer
	 * 
	 * memcache缓存访问类构造函数
	 * 
	 * 如果PHP没有加载memcache库，会抛出异常XException
	 * 
	 * 如果配置中没有Server信息，会抛出异常XException
	 * 
	 * @param string $region 区域名，每个区域对应一个cache access实例
	 * @param $attributes 属性信息数组
	 */
	public function __construct($region, $attributes = array()){	
		parent::__construct($region, $attributes);
		if(!extension_loaded("memcache")) {
			throw new Exception("Cannot load memcache extension. Please check your PHP configuration.");
		}
		if(!isset($this->attributes['servers'])
			|| !is_array($this->attributes['servers'])){
			throw new Exception("Cannot load memcache servers configuration for region {$region}.");
		}
	}
	
	private function getEngine(){
		if($this->memcache){
			return $this->memcache;
		}
		$this->memcache = new memcache();
		foreach($this->attributes['servers'] as $server){
			$this->memcache->addServer(isset($server["host"])?$server["host"]:'localhost', 
				isset($server["port"])?$server["port"]:11211, 
				isset($server["weight"])?$server["weight"]:1);
		}
		$this->memcache->setCompressThreshold(10000, 0.3);	
		return $this->memcache;	
	}
	
	/** 
	 * cache data getter
	 * 
	 * 获取缓存数据
	 * 
	 * 支持批量获取，将多个主键组成一个数组传入参数。返回的结果为找到的数据的key-value对
	 * 
	 * 如果多个主键中某个主键对应的数据没有找到，则返回结果中不含有该主键
	 * @param mixed $key 缓存主键，批量读时为数组
	 * @return mixed
	 */
	public function get($key){
		$result = $this->getEngine()->get($key, MEMCACHE_COMPRESSED);
		if($result){			
			return $result;
		}
		return null;
	}
		
	/** 
	 * cache data putter
	 * 
	 * 将数据放到缓存中
	 * 
	 * 支持批量写入，将多个主键及其对应数据分别组成数组传入参数
	 * 
	 * @param mixed $key 缓存主键，批量写时为数组
	 * @param mixed $value 缓存数据，批量写时为数组
	 * @param int $ttl 缓存过期时间，默认为1小时
	 * @return XMemcacheCacheAccess
	 */
	public function put($key, $value, $ttl = 3600){
		if(!is_array($key)){			
			$this->getEngine()->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
			return $this;
		}
		foreach($key as $cacheKey){
			$this->getEngine()->set($cacheKey, array_shift($value), MEMCACHE_COMPRESSED, $ttl);
		}
		return $this;
	}
	
	/** 
	 * removing cache data from this region
	 * 
	 * 将数据从缓存中删除
	 * 
	 * @param mixed $key 缓存主键
	 * @return mixed
	 */		
	public function remove($key){
		return $this->getEngine()->delete($key);
	}

	/** 
	 * clear all cache data from this region
	 * 
	 * 清空缓存
	 * 
	 * @return mixed
	 */	
	public function removeAll(){
		return $this->getEngine()->flush();
	}
}
?>