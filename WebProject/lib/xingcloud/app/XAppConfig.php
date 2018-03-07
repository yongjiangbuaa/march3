<?php
defined('APP_DIR') or define("APP_DIR", dirname(__FILE__));
class XAppConfig {
	private static $instance = null;	
	private $parameters = array ();
	private $config = null;

	private function __construct() {
	}
	
	/** 
	 * 
	 * 取得XAppConfig类单例的方法
	 * 
	 * @return XAppConfig
	 */
	public static function singleton() {
		if (!self :: $instance) {
			self :: $instance = new self;
		}
		return self :: $instance;
	}

	public function get($key) {
		$pos = strpos($key, '.');
		$head = null;
		$tail = null;
		if ($pos === false) {
			$head = $key;
		} else {
			$head = substr($key, 0, $pos);
			$tail = substr($key, $pos +1);
		}
		if (empty ($tail)) {
			return isset($this->parameters[$head]) ? $this->parameters[$head] : null;
		}
		return $this->x_array_get($this->parameters[$head], $tail);
	}

	public function has($key) {
		$config = $this->get($key);
		return !empty ($config);
	}

	public function getConfig() {
		if(!$this->config){
			$this->config = array ();
			$this->setConfig();
		}
		return $this->config;
	}

	private function setConfig() {
		if ($this->has('mysql.host')) {
			$this->config['mysql_host'] = $this->get('mysql.host');
		}
		if ($this->has('mysql.port')) {
			$this->config['mysql_port'] = $this->get('mysql.port');
		}
		if ($this->has('mysql.user')) {
			$this->config['mysql_user'] = $this->get('mysql.user');
		}
		if ($this->has('mysql.password')) {
			$this->config['mysql_passwd'] = $this->get('mysql.password');
		}
		if ($this->has('mysql.database')) {
			$this->config['mysql_db'] = $this->get('mysql.database');
		}
		if ($this->has('mysql.instance_id')) {
			$this->config['instance_id'] = $this->get('mysql.instance_id');
		}		
		if ($this->has('sqlite.datafile')){
			$this->config['sqlite_datafile'] = $this->get('sqlite.datafile');
		}
		if ($this->has('memcache.servers')) {
			$memcach = $this->get('memcache.servers');
			foreach ($memcach as $server) {
				if (isset ($server['host'])) {
					$this->config['memcache_host'] = $server['host'];
				}
				if (isset ($server['port'])) {
					$this->config['memcache_port'] = $server['port'];
				}
			}
		}
	}
	
	public function _loadFile(){
		$filepath = APP_DIR . "/config/config.php";
		$xpath = getenv('xpath');
		if(is_file($xpath)){
			$filepath = $xpath;
		}else if(is_dir($xpath)){
			$xpath = $xpath . "/config.php";
			if(is_file($xpath)){
				$filepath = $xpath;
			}
		}
		$result = @include($filepath);
		if(!is_array($result) || empty($result)){
			return;
		}
		$this->parameters = array_merge($this->parameters, $result);
	}
	
	public function _loadYAML(){
		$xpath = getenv('xpath');
		if(is_dir($xpath)){
			$xpath = $xpath . "/app.yaml";
			if(is_file($xpath)){
				require_once "spyc.php";
				$data = Spyc::YAMLLoad($xpath);			
				$this->parameters = array_merge($this->parameters, array('app' => $data));
			}
		}
	}
	
	private function x_array_get(&$array, $key){
		if(!is_array($array) || !$key){
			return null;
		}
		if(isset($array[$key])){
			return $array[$key];
		}
		$splits = explode('.', $key);
		if(!is_array($splits)
			|| empty($splits)){
			return null;
		}
		$result = $array;
		foreach($splits as $split){
			if(!isset($result[$split])){
				return null;
			}
			$result = $result[$split];
		}
		return $result;
	}
}
?>
