<?php
/**
 * XLock
 * 
 * Lock Manager Class
 * 
 * 锁管理类，负责初始化锁访问类，并为用户提供取得锁访问类的方法
 * 
 * @author Tianwei
 * @final
 * @package util.lock
 */
final class XLock{
	private static $lockers = array();
	private static $loaded = false;
	
	/**
	 * locker getter
	 * 
	 * 取得锁访问类的方法
	 * 
	 * 通过指定的区域名查找该区域的XLocker实例
	 * 
	 * 如果传入参数为null，则返回第一个XLocker实例
	 * 
	 * 如果没有对应的XLocker实例，返回null
	 * 
	 * @param string $region 区域名，每个区域对应一个XLocker实例，默认为null
	 * @static
	 * @return XLocker
	 */
	public static function singleton($region = null){
		if(!self::$loaded){
			self::load();
		}	
		if(is_null($region)){
			if(count(self::$lockers) == 0){
				return null;
			}
			return x_array_head(self::$lockers);
		}
		if(isset(self::$lockers[$region])){
			return self::$lockers[$region];
		}
		return null;
	}
	
	private static function load(){
		import('util.config.XConfig');
		$config = XConfig::singleton()->get('lock');
		if(!is_array($config)){
			return false;
		}
		foreach($config as $key => $value){
			if(!isset($value['type'])){
				continue;
			}
			$class = "X".x_name_to_java($value['type'])."Locker";
			import('module.util.lock.'.$class);
			self::$lockers[$key] = new $class($value['instance'], $value);	
		}
	}
}
?>