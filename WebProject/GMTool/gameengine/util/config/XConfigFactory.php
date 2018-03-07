<?php
import('util.config.XConfigLoader');
/**
 * XConfigFactory
 * 
 * Config Loader Factory Class
 * 
 * 配置加载工厂类，加载不同文件格式的配置
 * 
 * @author Tianwei
 * @final
 * @package util.config
 */
final class XConfigFactory implements XConfigLoader{
	private static $instance = null;
	
	/** 
	 * Config Loader Factory Class Singelton getter
	 * 
	 * 取得配置加载工厂单例的方法
	 * 
	 * @return XConfigFactory
	 */
	public static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
		
	/** 
	 * load config file, supporting multiple extension
	 * 
	 * 加载多种不同格式的配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @return array
	 */
	public function load($file){
		$result = $this->loadExtensions($file, array(
			'\.php$' => 'XPHPConfigLoader',
			'\.ini$' => 'XINIConfigLoader',
			'\.properties$' => 'XINIConfigLoader',
			'sdk-description\.xml$' => 'XDescriptionXMLConfigLoader',
			'\.xml$' => 'XXMLConfigLoader'
		));
		if($result){
			return $result;
		}
		return null;
	}
	
	/** 
	 * load config file by given rules
	 * 
	 * 使用$extensions数组指定的规则加载多种不同格式的配置文件
	 * 
	 * @param string $file 配置文件完整路径名 
	 * @param array $extensions 指定的加载规则
	 * @return array
	 */	
	public function loadExtensions($file, $extensions){
		foreach($extensions as $key => $value){
			if(!preg_match("/{$key}/i", $file)){
				continue;
			}
			import('util.config.'.$value);
			$loader = new $value();
			return $loader->load($file);						
		}
		return false;	
	}
}
?>