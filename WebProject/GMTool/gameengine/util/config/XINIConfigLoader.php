<?php
import('util.config.XConfigLoader');
/**
 * XINIConfigLoader
 * 
 * INI Config Loader Class
 * 
 * INI配置加载类
 * 
 * @author Tianwei
 * @package util.config
 */
class XINIConfigLoader implements XConfigLoader{
	/** 
	 * load INI config file
	 * 
	 * 加载INI配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @return array
	 */		
	public function load($file){
		return @parse_ini_file($file);
	}
}
?>