<?php
/**
 * XSession
 * 
 * HTTP Session Manager Class
 * 
 * HTTP Session管理类，负责初始化HTTP Session，并为用户提供取得session类的方法
 * 
 * @author wangqi
 * @package util.session
 */
class XSession{
	static private $instance = NULL;
	private $sessions = array();

	/**
	* Constructor of XHttpSession
	*/
	private function __construct(){
		
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XSession
	 */
	static public function singleton(){
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * get a http session instance
	 * 
	 * 获取http session实例，例如可以指定$type=memcache来获取分布式memcache session的实例
	 * 默认会从配置文件中读取cache.memcache的配置信息
	 * 
	 * @param string $type
	 * @return XHttpSession
	 */
	public function getInstance($type=null){
		if($type==null){
			import('util.config.XConfig');
			$config = XConfig::singleton()->get('cache.memcache');
			if(!empty($config)){
				$type = 'memcache';
			}else{
				return null;
			}
		}
		if(array_key_exists($type, $this->sessions)){
			return $this->sessions[$type];
		}
		$class = 'X'.ucfirst($type).'HttpSession';
		import("module.util.session.$class");
		$this->sessions[$type] = new $class();
		return $this->sessions[$type];
	}
}
?>