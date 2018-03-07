<?php
class DataProxyPersist implements LogPersist{
	
	private $thriftInitialed = false;
	private $client = null;
	
	/** 
	 * singleton method
	 * 
	 * 单例方法
	 * 
	 * 该方法会返回DataProxyPersist实例，每个类对应的实例有且仅有一个
	 * 
	 * @return DataProxyPersist
	 */
	private static $instance = null;
	static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function iniThriftConnection()
	{
		if($this->thriftInitialed){
			return true;
		}
		try
		{
			if(DATA_PROXY_LOCATION!==null){
				$socket = new TSocket(DATA_PROXY_LOCATION, 9091, TRUE);
				$socket->setRecvTimeout(50000);
				$socket->setSendTimeout(50000);
				$transport = new TFramedTransport($socket);
				$protocol = new TBinaryProtocol($transport);
				require_once STATISTICS_DIR.'/LogService.php';
				$this->client = new LogServiceClient($protocol);
				$transport->open();
				$this->thriftInitialed = true;
			}
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage(), $e->getCode());
		}
		
	}
	/**
	 * send log by data proxy
	 * @param string $log the log to save
	 */
	public function sendLog($log){
		$this->iniThriftConnection();
		if(is_object($this->client)){
			$this->client->log($log);
		}
	}
}