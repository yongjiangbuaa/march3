<?php
import('util.config.XConfigLoader');
/**
 * XPHPConfigLoader
 * 
 * PHP Config Loader Class
 * 
 * PHP配置加载类
 * 
 * @author Tianwei
 * @package util.config
 */
class XPHPConfigLoader implements XConfigLoader{
	/** 
	 * load PHP config file
	 * 
	 * 加载PHP配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @return array
	 */	
	public function load($file){
		$result = @include($file);
		if(!is_array($result) || empty($result)){
			return null;
		}
		return $result;
	}
}
?>