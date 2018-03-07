<?php
/**
 * XServletRequest
 *
 * servlet request tool
 *
 * servlet request工具, 封装HTTP请求数据，并提供相关方法
 *
 * @author Tianwei
 * @package util.http
 */
class XServletRequest{
	private static $instance = null;
	private $headers = null;
	
	private function __construct(){
		
	}
	
	/**
	 * <b>singleton method</b>
	 * 
	 * <b>singleton方法</b>
	 * 
	 * @static
	 * @return XServletRequest
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * get a parameter from HTTP GET request
	 * 
	 * 从HTTP GET请求中获取一个key为$key的参数
	 * 
	 * @param string $key
	 * @return mixed
	 */		
	public function get($key){
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}
	
	/**
	 * get all the parameters of the HTTP GET request
	 * 
	 * 获取HTTP GET请求的所有参数
	 * 
	 * @return array
	 */		
	public function gets(){
		return $_GET;
	}
	
	/**
	 * get a parameter from HTTP POST request
	 * 
	 * 从HTTP POST请求中获取一个key为$key的参数
	 * 
	 * @param string $key
	 * @return mixed
	 */	
	public function post($key){
		return isset($_POST[$key]) ? $_POST[$key] : null;
	}

	/**
	 * get all the parameters of the HTTP POST request
	 * 
	 * 获取HTTP POST请求的所有参数
	 * 
	 * @return array
	 */	
	public function posts(){
		return $_POST;
	}
	
	/**
	 * get a parameter from HTTP request header
	 * 
	 * 从HTTP请求header中获取一个key为$key的参数
	 * 
	 * @param string $key
	 * @return mixed
	 */		
	public function header($key){
		$headers = $this->headers();
		return isset($headers[$key]) ? $headers[$key] : null;
	}
	
	/**
	 * get all the parameters of the HTTP request header
	 * 
	 * 获取HTTP请求header的所有参数
	 * 
	 * @return array
	 */		
	public function headers(){
		if($this->headers){
			return $this->headers;
		}
	    if(function_exists("apache_request_headers")){
    		$this->headers = apache_request_headers();
    		return $this->headers;
   		}
    	$this->headers = array();
    	foreach($_SERVER as $key=>$value) {
			if (substr($key,0,5)=="HTTP_") {
				$newKey=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
				$this->headers[$newKey]=$value;
			}else if($key == "CONTENT_TYPE"){
				$this->headers["Content-Type"] = $value;
			}else if($key == "CONTENT_LENGTH"){
				$this->headers["Content-Length"] = $value;
			}
		}
    	return $this->headers;
	}
	
	/**
	 * get HTTP request method
	 * 
	 * 获取HTTP请求方法，e.g. GET, POST
	 * 
	 * @return mixed
	 */	
	public function method(){
		return $_SERVER['REQUEST_METHOD'];
	}
	
	/**
	 * check whether the request is AJAX
	 * 
	 * 判断请求是否是AJAX请求
	 * 
	 * @return bool
	 */	
	public function isAjax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST';
	}
	
	/**
	 * check whether the HTTP request method is GET
	 * 
	 * 判断HTTP请求方法是否是GET
	 * 
	 * @return bool
	 */		
	public function isGet(){
		return strtoupper($this->method()) == 'GET';
	}
	
	/**
	 * check whether the HTTP request method is POST
	 * 
	 * 判断HTTP请求方法是否是POST
	 * 
	 * @return bool
	 */		
	public function isPost(){
		return strtoupper($this->method()) == 'POST';
	}	
	
	/**
	 * check whether the HTTP request method is PUT
	 * 
	 * 判断HTTP请求方法是否是PUT
	 * 
	 * @return bool
	 */		
	public function isPut(){
		return strtoupper($this->method()) == 'PUT';
	}
	
	/**
	 * get the uri of the HTTP request
	 * 
	 * 获取HTTP请求的uri
	 * 
	 * @return string
	 */		
	public function uri(){
		$uri = $_SERVER['PHP_SELF'];
		$pos = strpos($uri, '?');
		if($pos !== false){
			$uri = substr($uri, 0, $pos);
		}
		if(preg_match('/index\.php$/i', $uri)){
			if(!isset($_GET['uri'])){
				return $uri;
			}
			$uri = str_replace('index.php', "", $uri);
			$uri .= $_GET['uri'];
			$uri = str_replace('//', '/', $uri);
			return $uri;
		}
		$uri = str_replace('index.php', "", $uri);
		$uri = str_replace('//', '/', $uri);
		return $uri;
	}
	
	/**
	 * get the ip address of the server
	 * 
	 * 获取服务器IP地址
	 * 
	 * @return string
	 */		
	public function ip(){
		return $_SERVER['SERVER_ADDR'];
	}
	
	/**
	 * get the host of the server
	 * 
	 * 获取服务器的host
	 * 
	 * @return string
	 */		
	public function host(){
		return $_SERVER['HTTP_HOST'];
	}	
	
	/**
	 * get the port that the server is listening
	 * 
	 * 获取服务器监听的端口值
	 * 
	 * @return string
	 */		
	public function port(){
		return $_SERVER['SERVER_PORT'];
	}
	
	/**
	 * check whether the request is through HTTPS
	 * 
	 * 检查请求是否通过HTTPS发送
	 * 
	 * @return bool
	 */
	public function isHttps(){
		return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']);
	}
}
?>