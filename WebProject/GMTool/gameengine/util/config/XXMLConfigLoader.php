<?php
import('util.config.XConfigLoader');
/**
 * XXMLConfigLoader
 * 
 * XML Config Loader Class
 * 
 * XML配置加载类
 * 
 * @author Tianwei
 * @package util.config
 */
class XXMLConfigLoader implements XConfigLoader{
	/** 
	 * load XML config file
	 * 
	 * 加载XML配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @return array
	 */		
	public function load($file){
		import('util.xml.XXMLUtil');
		$result = XXMLUtil::singleton()->parseFile($file, '/xingcloud:config');
		return $result[0];
	}
}
?>