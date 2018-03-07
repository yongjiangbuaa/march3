<?php
class HttpPersist implements LogPersist {
	private $ch=null;
	
	
	private $url='http://analytic.337.com/index.php';
	private $param='json';
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回HttpPersist实例，每个类对应的实例有且仅有一个
	 * 
	 * @return HttpPersist
	 */
	private static $instance = null;
	static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * send log to XA server by http query
	 */
	function sendLog($log){
		if(extension_loaded('curl')){
			if(is_null($this->ch)){
				$this->ch = curl_init();
			}
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url);
			$data=array( $this->param=>$log);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS,$data);
			$result = curl_exec($this->ch);
			curl_close($this->ch);
		}
	}
}