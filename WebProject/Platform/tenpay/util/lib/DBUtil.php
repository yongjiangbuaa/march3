<?php

class DBUtil 
{
	private $host;
	private $port;
	private $user;
	private $password;
	private $database;
	private $charset = 'UTF8';
	
	private $client;
	private $sQuery;
	private $bConnected = false;
	private $result;
	private $success = false;
	private $msg;
	

	public static function newInstance(){
		return new self();
	}

	public function init($params){
		$this->host 			= $params['mysql_host'];
		$this->port 			= $params['mysql_port'];
		$this->user 			= $params['mysql_user'];
		$this->password		= $params['mysql_passwd'];
		$this->database		= $params['mysql_db'];
		$this->connect();
		return $this;
	}
	
	private function Connect()
	{
		$dsn = 'mysql:host='.$this->host.';port='.$this->port.';dbname='.$this->database;
		try 
		{
			$this->client = new PDO($dsn, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));
			$this->client->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->client->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->bConnected = true;
		}
		catch (PDOException $e) 
		{
			throw new Exception($e->getMessage());
		}
	}
	
	public function isConnected(){
		return $this->bConnected;
	}

	public function disconnect()
	{
		$this->client = null;
	}
	
	private $enableLog = false;
	public function writeQureyLog($sql, $param){
		if($this->enableLog){
			$log = $sql . '	' . json_encode($param);
			$this->writeLog('all', $log);
		}
	}
	
	public function writeErrorLog($sql, $param, $error){
		$log = $error . '	' . $sql . '	' . json_encode($param);
		$this->writeLog('error', $log);
	}
	
	public function writeSlowLog($sql,$sqlStart,$sqlEnd){
		if($this->enableLog){
			$slow = 100;//ms
			$costTime = ($sqlEnd - $sqlStart)*1000;
			if($logEnable && $costTime > $slow){
				$log = $costTime . 'ms	' . $sql . '	' . json_encode($param);
				$this->writeLog('slow', $log);
			}
		}
	}
	
	private function writeLog($fileName, $log){
		$dir = LOG_DIR.'/'.date('Y-m-d').'_mysql';
		if (is_dir($dir) || @mkdir($dir, 0777)){
			file_put_contents($dir."/mysql$fileName.log",time().'	'.date('Y-m-d H:i:s',time()).'	'.$log."\n",FILE_APPEND);
		}
	}
		
	private function query($sql,$parameters = array())
	{
		$this->success = false;
		if(!$this->bConnected){
			$this->Connect();
		}
		$this->writeQureyLog($sql, $parameters);
		$sqlStart = microtime(true);
		try {
			$this->sQuery = $this->client->prepare($sql);
			//bindparam测试失败了
// 			foreach($parameters as $key=>$value){
// 				$this->sQuery->bindParam($key,$value);
// 			}
			$this->success = $this->sQuery->execute($parameters);
		}catch(PDOException $e)	{
			$this->writeErrorLog($sql, $parameters, $e->getMessage());
		}
		$sqlEnd = microtime(true);
		$this->writeSlowLog($sql, $sqlStart, $sqlEnd);
	}
	
	public function affected_rows()
	{
		return $this->sQuery->rowCount();
	}

	public function fetchOne($sql,$params = array(),$fetchmode = PDO::FETCH_ASSOC)
	{
		$this->query($sql,$params);
		if($this->success)
			return $this->sQuery->fetch($fetchmode);
	}

	public function fetchAll($sql,$params = array(),$fetchmode = PDO::FETCH_ASSOC)
	{
		$this->query($sql,$params);
		if($this->success)
			return $this->sQuery->fetchAll($fetchmode);
	}
	
	public function execute($sql,$params = array(),$fetchmode = PDO::FETCH_ASSOC){
		$this->query($sql,$params);
		return $this->success;
	}
	
	public function fetchPossibility($sql,$params = array())
	{
		$this->query($sql,$params);
		if($this->success){
			$columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
			$column = null;
			foreach($columns as $cells) {
				$column[] = $cells[0];
			}
			return $column;
		}
	}

	public function fetchSingleColumn($query,$params = array())
	{
		$this->query($query,$params);
		if($this->success)
			return $this->sQuery->fetchColumn();
	}
}
?>