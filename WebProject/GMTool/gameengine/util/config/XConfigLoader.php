<?php
/**
 * XConfigLoader
 * 
 * Config Loader Interface
 * 
 * 配置加载类接口
 * 
 * @author Tianwei
 * @interface
 * @package util.config
 */
interface XConfigLoader{
	/** 
	 * load config file
	 * 
	 * 加载配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @return array
	 */	
	function load($file);
}
?>