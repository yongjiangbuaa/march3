<?php
/**
 * XServletSession
 *
 * servlet session tool
 *
 * servlet session工具，提供对session的各项操作
 *
 * @author Tianwei
 * @package util.http
 */
class XServletSession{
	private static $instance = null;
	private $started = false;
	
	private function __construct(){
		
	}
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServletSession
	 */	
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * destory the session
	 * 
	 * 销毁session
	 * 
	 * @return bool
	 */	
	public function destroy(){
		if($this->started){
			$this->started = !session_destroy();
			return !$this->started;
		}
		return true;
	}
	
	/**
	 * get the value of the session by the specified key
	 * 
	 * 根据指定的key获取session中相应的值
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key){
		$this->start();
		if(is_array($_SESSION) && array_key_exists($key, $_SESSION)){
			return $_SESSION[$key];
		}
		return null;
	}
	
	/**
	 * write a value to the session
	 * 
	 * 向session中写入一个值
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return XServletSession
	 */	
	public function set($key, $value){
		$this->start();
		$_SESSION[$key] = $value;
		return $this;
	}
	
	/**
	 * get the entire session
	 * 
	 * 获取整个session
	 * 
	 * @return array
	 */	
	public function getAll(){
		$this->start();
		return $_SESSION;
	}
	
	/**
	 * start the session
	 * 
	 * 开始session记录
	 * 
	 * @return bool
	 */	
	public function start(){
		if(!$this->started){
			$this->started = session_start();
			return $this->started;
		}
		return true;
	}
	
	/**
	 * check wether the session is started
	 * 
	 * 监测session状态，是否开始了session记录
	 * 
	 * @return bool
	 */	
	public function started(){
		return $this->started;
	}
	
}
?>