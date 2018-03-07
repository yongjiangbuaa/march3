<?php
abstract class AbstractLogger {
	protected $isLog;
	protected $target;
	protected $message;
	
	public function __construct($target, $message, $isLog) {
		if(is_null($isLog)) {
			$this->isLog = __LOG__;
		} else {
			$this->isLog = $isLog;
		}
		$this->target = $target;
		$this->message = $message;
	}
	
	public function isLog() {
		return $this->isLog;
	}
	
	abstract protected function log();
	
	/**
	 * 
	 * @param string $type
	 * @param array $params
	 */
	public function sendLog($table, $params) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		//verisign_ca.crt is the public certificate from VeriSign(It is the biggest Certificate Authority which issue ELEX client certificate)
		//verisign_ca.crt must be located at the same directory as this PHP code are.
// 		curl_setopt($ch, CURLOPT_CAINFO, XINGCLOUD_SERVICE_DIR.'/payment/service/verisign_ca.crt');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
		curl_setopt($ch, CURLOPT_URL, 'http://'.xingcloud_get("logServer_host").'/gameengine/util/logService.php');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$params = array(
				'table'=>$table,
				'values'=>$params,
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		$result = curl_exec($ch);
		curl_close($ch);
		$result = trim($result);
		return $result;
		if ($result === 'OK') return true;
		return false;
	}
}

class FileLogger extends AbstractLogger {

	public function __construct($fileName, $message, $path=null, $isLog=null) {
		$logDir = GAME_LOG_DIR;
		if($path) {
			$paths = explode('/', $path);
			foreach($paths as $pathItem) {
				$logDir = $logDir . '/'. $pathItem;
				if(!is_dir($logDir)) @mkdir($logDir, 0777);
			}
		} 
		$target = $logDir . '/' . $fileName . '.log';
		parent::__construct($target, $message, $isLog);
		if($this->message && is_array($this->message)) {
			$this->message = json_encode($this->message);
		}
	}
	
	public function log() {
		if($this->isLog) {
			file_put_contents($this->target, $this->message . "\n", FILE_APPEND);
		}
	}
}

class DBLogger extends AbstractLogger {
	
	public function __construct($tableName, $message, $isLog=null) {
		if(!is_array($message)) {
			throw new Exception('Message must be array!');
		}
		parent::__construct($tableName, $message, $isLog);
	}
	/**
	 * 输出单条LOG
	 * @see AbstractLogger::log()
	 */
	public function log() {
		if($this->isLog) {
			if (__LOGSERVER__)
			{
				$msg[] = $this->message;
				return $this->sendLog($this->target, $msg);
			}
			else
			{
				import('util.mysql.XMysql');
				return XMysql::singleton()->add($this->target, $this->message);
			}
		}
	}
	/**
	 * 输出多条log
	 */
	public function logs() {
		if($this->isLog) {
			if (__LOGSERVER__)
			{
				return $this->sendLog($this->target, $this->message);
			}
			else
			{
				import('util.mysql.XMysql');
				return XMysql::singleton()->addBatch($this->target, $this->message);
			}
		}
	}
}
?>