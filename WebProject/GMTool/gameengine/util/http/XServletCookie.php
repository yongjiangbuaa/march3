<?php
/**
* XServletCookie
*
* servlet cookie tool
*
* cookie操作工具
*
* @author Tianwei
* @package util.http
*/
class XServletCookie{
	private static $instance = null;
	private $name = null;
	private $value = null;
	private $expire = null;
	private $path = null;
	private $domain = null;
	private $secure = null;
	private $httpOnly = null;
	
	private function __construct(){
		
	}

	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServletCookie
	 */		
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Set a cookie
	 * 
	 * 设置cookie
	 * 
	 * @return boolean
	 */
	public function setCookie(){
		return setcookie($this->name, $this->value, $this->expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
	}
	
	/**
	 * set the key, value and age of a cookie, and then send it
	 * 
	 * 设置cookie的键，值和过期时间，发送到客户端
	 * 
	 * @param string $key
	 * @param string $value
	 * @param int $age
	 * @return boolean
	 */
	public function set($key, $value, $age = 0){
		return setcookie($key, $value, $age, $this->path, $this->domain, $this->secure, $this->httpOnly);
	}
	
	/** 
	 * get the value of a specified key in a cookie
	 * 
	 * 获取cookie中指定key的值
	 * 
	 * @return mixed
	 */	
	public function get($key){
		return x_array_get($_COOKIE, $key);
	}
	
	/**
	 * get all contents of a cookie
	 * 
	 * 获取cookie的所有内容，即$_COOKIE
	 * 
	 * @return array
	 */		
	public function getAll(){
		return $_COOKIE;
	}
		
	/**
	 * get the name of the cookie
	 * 
	 * 获取cookie的名称
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * value getter
	 * 
	 * 获取value属性的方法
	 * 
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * expire getter
	 * 
	 * 获取expire(过期时间)属性的方法
	 * 
	 * @return int
	 */
	public function getExpire() {
		return $this->expire;
	}

	/**
	 * path getter. 
	 * 
	 * 获取path属性的方法。path为Cookie在服务器端的有效路径
	 * 
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * domain getter
	 * 
	 * 获取domain属性的方法。domain为cookie有效的域名
	 * 
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * secure getter
	 * 
	 * 获取secure属性的方法。secure指明cookie是否仅通过安全的HTTPS连接传送
	 * 
	 * @return string
	 */
	public function getSecure() {
		return $this->secure;
	}

	/**
	 * httpOnly getter
	 * 
	 * 获取httpOnly属性的方法。
	 * 
	 * @return int
	 */
	public function getHttpOnly() {
		return $this->httpOnly;
	}

	/**
	 * name setter
	 * 
	 * 设置cookie的名称
	 * 
	 * @param string $name
	 * @return XServletCookie
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * value setter
	 * 
	 * 设置cookie的值
	 * 
	 * @param string $value
	 * @return XServletCookie
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * expire setter
	 * 
	 * 设置cookie的过期时间
	 * 
	 * @param string $expire
	 * @return XServletCookie
	 */
	public function setExpire($expire) {
		$this->expire = $expire;
		return $this;
	}

	/**
	 * path setter
	 * 
	 * 设置cookie的有效路径
	 * 
	 * @param string $path
	 * @return XServletCookie
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * domain setter
	 * 
	 * 设置cookie的有效域名
	 * 
	 * @param string $domain
	 * @return XServletCookie
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}

	/**
	 * secure setter
	 * 
	 * 设置cookie的secure属性，选择是否仅通过安全的HTTPS连接传送
	 * 
	 * @param string $secure
	 * @return XServletCookie
	 */
	public function setSecure($secure) {
		$this->secure = $secure;
		return $this;
	}

	/**
	 * httpOnly setter
	 * 
	 * 设置cookie的httpOnly属性，选择是否仅通过HTTP进行访问
	 * 
	 * @param string $httpOnly
	 * @return XServletCookie
	 */
	public function setHttpOnly($httpOnly) {
		$this->httpOnly = $httpOnly;
		return $this;
	}
}
?>