<?php
import('cache.module.XAbstractCacheAccess');
/**
 * XApcCacheAccess
 * 
 * APC Cache Access Class
 * 
 * APC缓存访问类
 * 
 * @author Tianwei
 * @package cache
 */
class XApcCacheAccess extends XAbstractCacheAccess{
	/** 
	 * cache data getter
	 * 
	 * 获取缓存数据
	 * 
	 * 支持批量获取，将多个主键组成一个数组传入参数。返回的结果为找到的数据的key-value对
	 * 
	 * 如果多个主键中某个主键对应的数据没有找到，则返回结果中不含有该主键
	 * 
	 * @param mixed $key 缓存主键，批量读时为数组
	 * @return mixed
	 */
	public function get($key){
		$result = @apc_fetch($key);
		if(is_string($result)){
			$result = unserialize($result);
			return $result;
		}elseif(is_array($result)){
			$results = array();
			foreach($result as $key => $value){
				if(is_string($value)){
					$results[$key] = unserialize($value);
				}else{
					$results[$key] = null;
				}
			}		
			return $results;
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
	 * @return XApcCacheAccess
	 */
	public function put($key, $value, $ttl = 0){
		if(!is_array($key)){			
			apc_store($key, serialize($value), $ttl);
			return $this;
		}
		apc_store(array_combine($key, $value), null, $ttl);
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
		return apc_delete($key);
	}

	/** 
	 * clear all cache data from this region
	 * 
	 * 清空缓存
	 * 
	 * @return mixed
	 */
	public function removeAll(){
		return apc_clear_cache('user');
	}
}
?>