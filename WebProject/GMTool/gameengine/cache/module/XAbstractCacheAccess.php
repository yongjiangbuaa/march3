<?php
import('cache.module.XCacheAccess');
/**
 * XAbstractCacheAccess
 * 
 * Abstract Cache Access Class
 * 
 * 缓存抽象类，实现XCacheAccess接口
 * 
 * @author Tianwei
 * @package cache
 */
abstract class XAbstractCacheAccess implements XCacheAccess{
	protected $region = null;
	protected $attributes = array();
	
	/** 
	 * cache access constructer
	 * 
	 * 缓存访问类构造函数
	 * 
	 * @param string $region 区域名，每个区域对应一个cache access实例
	 * @param array $attributes 属性信息数组
	 */	
	public function __construct($region, $attributes = array()){
		$this->region = $region;
		$this->attributes = $attributes;
	}
		
	/** 
	 * cache data getter
	 * 
	 * 获取缓存数据
	 * 
	 * @param mixed $key 缓存主键
	 * @return mixed
	 */
	public function get($key){
		return null;
	}
	
	/** 
	 * cache access attributes getter
	 * 
	 * 获取缓存操作实例的配置信息
	 * 
	 * @return array
	 */	
	public function getAttributes(){
		return $this->attributes;
	}
		
	/** 
	 * cache access name getter
	 * 
	 * 获取缓存操作实例的名字，默认返回区域名
	 * 
	 * @return string
	 */	
	public function getName(){
		return $this->region;
	}
	
	/** 
	 * cache access region getter
	 * 
	 * 获取缓存操作实例的区域名
	 * 
	 * @return string
	 */	
	public function getRegion(){
		return $this->region;
	}
		
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
	public function put($key, $value, $ttl = 3600){
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
		return false;
	}
		
	/** 
	 * clear all cache data from this region
	 * 
	 * 清空缓存
	 * 
	 * @return mixed
	 */	
	public function removeAll(){
		return false;
	}
}
?>