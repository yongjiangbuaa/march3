<?php
import('cache.module.XAbstractCacheAccess');
/**
 * XFileCacheAccess
 * 
 * File Cache Access Class
 * 
 * 文件缓存访问类
 * 
 * @author Tianwei
 * @package cache
 */
class XFileCacheAccess extends XAbstractCacheAccess{
	protected $dir = null;

	/** 
	 * XFileCacheAccess constructer
	 * 
	 * 文件缓存访问类构造函数
	 * 
	 * <b>会将常量GAME_TMP_DIR所指目录下的cache目录作为文件缓存根目录，请确认GAME_TMP_DIR目录可写</b>
	 * 
	 * @param string $region 区域名，每个区域对应一个cache access实例
	 * @param array $attributes 属性信息数组
	 */	
	public function __construct($region, $attributes = array()){
		parent::__construct($region, $attributes);
		$this->dir = GAME_TMP_DIR.__DS__.'cache';
	}
	
	/** 
	 * cache data getter
	 * 
	 * 获取缓存数据
	 * 
	 * @param mixed $key 缓存主键，批量读时为数组
	 * @return mixed
	 */
	public function get($key){
		$file = $this->getFile($key);
		if(!$file->exists()){
			return null;
		}		
		import('util.io.XFileReader');
		$reader = new XFileReader($file);
		$result = unserialize($reader->readAll());
		$reader->close();
		return $result;
	}
			
	/** 
	 * cache data putter
	 * 
	 * 将数据放到缓存中
	 * 
	 * @param mixed $key 缓存主键，批量写时为数组
	 * @param mixed $value 缓存数据，批量写时为数组
	 * @param int $ttl 缓存过期时间，默认为1小时
	 * @return XFileCacheAccess
	 */
	public function put($key, $value, $ttl = 3600){
		import('util.io.XFileWriter');
		$writer = new XFileWriter($this->getFile($key));
		$writer->write(serialize($value));
		$writer->close();
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
		$file = $this->getFile($key);
		return $file->delete();
	}
		
	/** 
	 * clear all cache data from this region
	 * 
	 * 清空缓存
	 * 
	 * @return mixed
	 */	
	public function removeAll(){
		import('util.io.XFile');
		$file = new XFile($this->dir);
		$children = $file->listFiles();
		foreach($children as $child){
			$child->delete();
		}
		return true;
	}
	
	/**
	 * @return XFile
	 */
	private function getFile($key){
		$path = md5($key);
		$path = $this->dir.DIRECTORY_SEPARATOR.$path{0}.$path{1}.DIRECTORY_SEPARATOR.$path{2}.$path{3}.DIRECTORY_SEPARATOR.$path;
		$file = new XFile($path);
		return $file;
	}
}
?>