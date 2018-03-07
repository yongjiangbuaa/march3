<?php
/**
 * KVStoreClient
 * 
 * The main interface class abstracting the notion of a persistence service
 * 
 * 实现数据持久化存储的核心类，提供了更新，查找，删除接口
 * 
 * @author XuZiHui
 * @final
 * @package persistence 
 * @todo 
 */

class KVStorageService{

	private $DBAdapter;
	private $instanceID;
	private static $instance = null;
	
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * @throws Exception
	 */
	static function getService() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}	
	
	private function __construct()
	{	
		require_once('SQLite3DB.php');
		require_once('MySQLDB.php');
		if ( defined('XINGCLOUD_DIR') ){
			$dbfile=XINGCLOUD_DIR."/test.db";
		}else{
			$dbfile=XINGCLOUD_CLOUD_DIR."/../test.db";
		}
		//$this->DBAdapter =  new SQLite3DB($dbfile);
		$this->DBAdapter = new MySQLDB();
	}

	public function get($className, $key){
		$className = str_replace(".","_", $className);
		if(empty($className)){
			throw new Exception("class name is null");
		}
		elseif($key!=0 && $key!="0" && empty($key)){
			throw new Exception("key is empty");
		}
		elseif(!is_array($key)){
			try{
				$propertyListString = $this->DBAdapter->getEntity($className, ((string)$key), $this->instanceID);
			}
			catch(Exception $e){
				throw $e;
			}
		}
		else{
			throw new Exception("$key is an array, use getList(..) instead.");
		}

		return $propertyListString;
	}

	public function getList($className, $keyList){
		$className = str_replace(".","_", $className);
		if(empty($className)){
			throw new Exception("class name is null");
		}
		elseif($keyList!=0 && $keyList!="0" && empty($keyList)){
			throw new Exception("key is empty");
		}
		elseif(!is_array($keyList)){
			throw new Exception("$keyList is NOT an array, use get(..) instead.");
		}
		$entityMap = array();

		try{
			$propertyListStringMap = $this->DBAdapter->getEntityList($className, $keyList, $this->instanceID);
			foreach($keyList as $oneKey){
                if( isset($propertyListStringMap[$oneKey]) ){
				    $propertyListString = $propertyListStringMap[$oneKey];
				    if(!is_null($propertyListString)){
				    	$originalKey = ($oneKey);
				    	$entityMap[$originalKey] = $propertyListString;
				    }
				}
			}
		}
		catch(Exception $e){
			throw $e;
		}

		return $entityMap;

	}


	public function del($className, $key){
		$className = str_replace(".","_", $className);
		if(empty($className)){
			throw new Exception("class name is null");
		}
		elseif($key!=0 && $key!="0" && empty($key)){
			throw new Exception("key is empty");
		}
		try{
			$this->DBAdapter->delEntity($className, $key, array(), $this->instanceID);
		}
		catch(Exception $e){
			if(strstr($e->getMessage(),"doesn't exist")==false)
				throw $e;
		}
	}
	
	public function set($className, $key, $value){
		$className = str_replace(".","_", $className);
		
		if(empty($className)){
			throw new Exception("class name is null");
		}
		elseif($key!=0 && $key!="0" && empty($key)){
			throw new Exception("key is empty");
		}
		
		$indexAttrList = array();
		$indexValueList = array();
		$indexTypeList = array();
		
		try{
			$this->DBAdapter->putEntity($className, $key, $value, $this->instanceID, $indexAttrList, $indexValueList, $indexTypeList);
		}
		catch(Exception $e){
			throw $e;
		}
		
	}

 }
 ?>
