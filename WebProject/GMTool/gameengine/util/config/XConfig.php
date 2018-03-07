<?php
/**
 * XConfig
 * 
 * Config Manager Class
 * 
 * 配置管理类，在程序启动时加载配置信息
 * 
 * @author Tianwei
 * @final
 * @package util.config
 */
final class XConfig{
	private static $instance = null;
	private $configDirs = array();
	private $contents = array();
	
	private function __construct() {
	}
	
	/** 
	 * Config Manager Class Singelton getter
	 * 
	 * 取得配置管理类单例的方法
	 * 
	 * @return XConfig
	 */	
	public static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * add a configuration directory, the load() method will load the config files in the specified directory
	 * 
	 * 添加配置文件目录，load()方法会自动加载指定目录下的配置文件
	 * 
	 * @param string $dir
	 */
	public function addConfigDir($dir){
		$this->configDirs[$dir] = $dir;
	}
	
	/** 
	 * loading config data
	 * 
	 * 载入配置信息
	 * 
	 * 默认的配置文件目录为常量XINGCLOUD_GAMEENGINE_DIR下的config目录
	 * 
	 * 配置加载的策略与程序index.php中声明的环境常量__DEV__相关
	 * 
	 * 如果__DEV__为true，表示程序是开发模式，会优先加载xxx.dev.php(xml/ini)
	 * 
	 * 如果没有xxx.dev.php但存在xxx.php，会加载xxx.php，但不会加载xxx.prod.php
	 * 
	 * 同理，如果__DEV__为false，表示程序是部署模式，会优先加载xxx.prod.php
	 * 
	 * 如果没有xxx.prod.php但存在xxx.php，会加载xxx.php，但不会加载xxx.dev.php
	 * 
	 * 另外，在config目录下的配置文件xxx.php，文件名会作为配置名
	 * 
	 * 比如，配置文件为config/event.php,则配置名为event
	 * 
	 * 取值时候使用XConfig::singleton()->get('event')
	 * 
	 * 最后，该方法会加载所有service目录下的默认配置文件service.properties，并指定配置名为service.xxx
	 * 
	 * 例如，service/user/service.properties的配置名为service.user
	 * 
	 * 取值时候使用XConfig::singleton()->get('service.user')
	 * 
	 * @return XConfig
	 */
	public function load(){
		$result = x_apc_file_fetch('XConfig');
		if(!empty($result) && empty($this->configDirs)){
			return;
		}
		$filterStr = 'prod';
		if(x_is_dev()){
			$filterStr = 'dev';
		}
		if(!empty($this->configDirs)){
			foreach ($this->configDirs as $dir){
				$this->_loadDir($dir, NULL, $filterStr);
				$this->_loadDir($dir);
			}
		}
		$this->loadSDKDescription();
		$this->_loadDir(GAME_CONFIG_DIR, NULL, $filterStr);
		$this->_loadDir(GAME_CONFIG_DIR);
		if(defined('XINGCLOUD_CONFIG_DIR')){
			$this->_loadDir(XINGCLOUD_CONFIG_DIR, NULL, $filterStr);
			$this->_loadDir(XINGCLOUD_CONFIG_DIR);
		}
		if(defined('XINGCLOUD_SERVICE_DIR')){
			$this->_loadServiceConfig(XINGCLOUD_SERVICE_DIR);
		}
		if(defined('GAME_SERVICE_DIR')){
			$this->_loadServiceConfig(GAME_SERVICE_DIR);
		}
		if(x_is_prod()){
			foreach($this->contents as $key => $value){
				x_apc_file_store('config_'.$key, $value);
			}
			x_apc_file_store('XConfig', 'XConfig');
		}
	}
	
	/**
	 * load module configuration from sdk-description.xml
	 * 
	 * 从sdk-description.xml中读取模块化配置信息
	 * 
	 */
	private function loadSDKDescription(){
		if(defined('XINGCLOUD_INNER_RESOURCE_DIR')){
			$this->loadConfig(XINGCLOUD_INNER_RESOURCE_DIR.__DS__.'sdk-description.xml', 'modules', FALSE);
		}
	}
	
	/** 
	 * loading config file with prefix
	 * 
	 * 载入一个指定的配置文件，并可以指定配置的前缀
	 * 
	 * 比如，指定配置文件为.../auth.php,指定前缀为auth
	 * 
	 * 则.../auth.php中的内容$data将被赋值到配置信息数组的auth主键下
	 * <code>
	 * array(
	 * 		'auth' => $data,
	 * ),
	 * </code>
	 * 取值时候使用XConfig::singleton()->get('auth')即可得到$data
	 * 
	 * @param string $filePath 配置文件完整的路径和文件名
	 * @param string $configPrefix 配置的前缀，默认为null
	 * @param boolean $update 是否更新XingCloudApp中的全局信息，默认为更新
	 */	
	public function loadConfig($filePath, $configPrefix = null, $update = true){
	    if(array_key_exists($configPrefix, $this->contents)){
			return;
		}
		import('util.config.XConfigFactory');
		$config = XConfigFactory::singleton()->load($filePath);
		if(!$configPrefix){
			$this->_add($config);
			return;
		}
		$result = array();
		$result[$configPrefix] = $config;
		$this->_add($result);
	}
	
	/** 
	 * getting config data by key
	 * 
	 * 得到指定配置名下的配置信息
	 * 
	 * 取值时候使用XConfig::singleton()->get('auth')即可得到配置名为auth的信息
	 * 
	 * @param string $key 配置名
	 * @param array
	 */	
	public function get($key){
		$pos = strpos($key, '.');
		$head = null;
		$tail = null;
		if($pos === false){
			$head = $key;
		}else{
			$head = substr($key, 0, $pos);
			$tail = substr($key, $pos + 1);
		}
		if(!isset($this->contents[$head])){
			$this->contents[$head] = x_apc_file_fetch('config_'.$head);
		}
		if(empty($tail)){
			return $this->contents[$head];
		}
		return x_array_get($this->contents[$head], $tail);
	}
	
	/** 
	 * check if config data is empty
	 * 
	 * 判断指定配置名下的配置信息是否为空
	 * 
	 * 使用XConfig::singleton()->has('auth')即可得到配置名为auth的信息是否为空
	 * 
	 * @param string $key 配置名
	 * @param boolean
	 */
	public function has($key){
		if(x_isset($this->contents, $key, true)){
			return true;
		}
		$config = $this->get($key);
		return !empty($config);
	}
	
	/** 
	 * return all the loaded config data
	 * 
	 * 返回当前已加载的所有配置信息
	 * 
	 * @param array
	 */
	public function getAll(){
		return $this->contents;
	}
	
	private function _loadDir($dir, $configPrefix = null, $filterStr = NULL){
		import('util.io.XFile');
		$root = new XFile($dir);
		$files = $root->listFiles();
		
		foreach($files as $file){
			if($file->isDirectory()){						
				$prefix = $file->getName();
				if($configPrefix){
					$prefix = $configPrefix.'.'.$prefix;
				}
				$this->_loadDir($file->getPath(), $prefix, $filterStr);
			}elseif($file->isFile()){
				if(!is_null($filterStr)){
					if(!preg_match('/\.'.$filterStr.'\.[A-Za-z]+$/i', $file->getName())){
						continue;
					}
				}else{
					if(!preg_match('/^\w+\.[A-Za-z]+$/i', $file->getName())){
						continue;
					}
				}
				$prefix = substr($file->getName(), 0, strpos($file->getName(), '.'));
				if($configPrefix){
					$prefix = $configPrefix.'.'.$prefix;
				}
				$this->loadConfig($file->getPath(), $prefix, false);
			}
		}
	}
	
	private function _loadServiceConfig($dir){
		if(!array_key_exists('service', $this->contents)){
			$this->contents['service'] = array();
		}
		import('util.io.XFile');
		$root = new XFile($dir);
		$files = $root->listFiles();
		$results = $this->contents['service'];
		foreach($files as $file){
			if(!$file->isDirectory()){		
				continue;
			}
			$configFile = $file->getPath().__DS__.'service.properties';
			if(is_file($configFile)){
				$config = XConfigFactory::singleton()->load($configFile);
				if(!empty($config)){
					if(array_key_exists($file->getName(), $this->contents['service'])){
						$results[$file->getName()] = $this->contents['service'][$file->getName()];
					}else{
						$results[$file->getName()] = array();
					}
					foreach($config as $key => $value){
						if(x_isset($results[$file->getName()], $key, true)){
							continue;
						}
						x_array_set($results[$file->getName()], $key, $value, true);
					}
				}
			}
		}	
		if(!empty($results)){
			$this->contents['service'] = $results;
		}
	}
	
	private function _set($key, $value){
		if(!is_array($value)){			
			x_array_set($this->contents, $key, $value, true);
			return;
		}
		foreach($value as $newKey => $newValue){
			x_array_set($this->contents, $key.'.'.$newKey, $newValue, true);
		}
	}
	
	private function _add($array){
		if(!is_array($array)){
			return;
		}
		foreach($array as $key => $value){
			$this->_set($key, $value);
		}
	}
}
?>