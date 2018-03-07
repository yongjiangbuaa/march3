<?php

/**
 * @package persistence
 */
class SQLite3DB {
	private $db;
	
	private $datafile;
	private $isConnected = false;
	
	function __construct($data_file){
		$this->datafile = $data_file;
	}
	
	public function connect(){
		$this->db = new SQLite3($this->datafile) or die("open database:".$this->db->lastErrorMsg());;
		if (!$this->db) {
			throw new Exception($this->db->lastErrorMsg());
		}
		
		$this->isConnected = true;
	}
	
	public function putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType){
		if(!$this->isConnected){
			$this->connect();
		}
		$createSQL = "CREATE TABLE IF NOT EXISTS ".$table. "( key_ varchar(200) not null, value_ MEDIUMBLOB, PRIMARY KEY(key_) );";
		$this->db->exec($createSQL) or die("create table:".$this->db->lastErrorMsg());

		$putSQL = "REPLACE INTO ".$table." (key_, value_) VALUES ( '". $key. "','".$this->db->escapeString($value)."')";
		$result = $this->db->exec($putSQL);
		
		if(!$result){
			$e=new Exception($this->db->lastErrorMsg());
			$this->close();
			throw $e;	
		}
		
		$this->close();			
	}
	
	public function putEntityList($table, $keys, $values, $DB, $indexAttr, $indexValues, $indexType){
		foreach($keys as $key){
			$value = current($values);
			$indexValue = current($indexValues);
			
			$this->putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType);
			 
			next($values);
			next($indexValues);
		}
		
	}
	
	
	
	public function getEntity($table, $key, $DB){
		if(!$this->isConnected){
			$this->connect();
		}
		$createSQL = "CREATE TABLE IF NOT EXISTS ".$table. "( key_ varchar(200) not null, value_ MEDIUMBLOB, PRIMARY KEY(key_) );";
		$this->db->exec($createSQL) or die("create table:".$this->db->lastErrorMsg());
		
		$getSQL = "SELECT * FROM ".$table." WHERE key_='". $key."'";
		$result = $this->db->query($getSQL);
		if(!$result){
			$this->close();
			return null;
		}
		else{
			if(  $row = $result->fetchArray()  ){
				$this->close();
				return $row["value_"];
			
			}
			$this->close();
			return "";
		}
		
	}
	
	public function getEntityList($table, $keys, $DB){
		if(!$this->isConnected){
			$this->connect();
		}
		$createSQL = "CREATE TABLE IF NOT EXISTS ".$table. "( key_ varchar(200) not null, value_ MEDIUMBLOB, PRIMARY KEY(key_) );";
		$this->db->exec($createSQL) or die("create table:".$this->db->lastErrorMsg());
		
		$keyResult = array();
		foreach($keys as $key){
			$getSQL = "SELECT * FROM ".$table." WHERE key_='". $key."'";
			
			$result = $this->db->query($getSQL);
			if(!$result)
				continue;
			else{
				if(  $row = $result->fetchArray()  ){
				
					$keyResult[$key] = $row["value_"];
				}
			}
		}
		$this->close();
		return $keyResult;
	}
	
	
	public function delEntity($table, $key, $indexAttr, $DB){
		if(!$this->isConnected)
			$this->connect();
			
		$createSQL = "CREATE TABLE IF NOT EXISTS ".$table. "( key_ varchar(200) not null, value_ MEDIUMBLOB, PRIMARY KEY(key_) );";
		$this->db->exec($createSQL) or die("create table:".$this->db->lastErrorMsg());
			
		$delSQL = "DELETE FROM ".$table." WHERE key_='". $key."'";

		$result = $this->db->exec($delSQL);
		if(!$result){
		
			$e=new Exception($this->db->lastErrorMsg());
			$this->close();
			throw $e;
		}
		
		$this->close();

	}
	
	public function delEntityList($table, $keys, $indexAttr, $DB){
		foreach($keys as $key){
			$this->delEntity($table, $key, $indexAttr, $DB);
		}
	}
	
	public function putMultiEntityList($tables, $keys, $values, $DB, $indexAttrs, $indexValues, $indexTypes){
		foreach($keys as $key){
		    $table = current($tables);
			$value = current($values);
			$indexAttr = current($indexAttrs);
 			$indexType = current($indexTypes);
			$indexValue = current($indexValues);
			
			$this->putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType);
			 
			next($tables);
			next($values);
			next($indexAttrs);
			next($indexValues);
			next($indexTypes);
		}
		

	}

	private function close(){
		if($this->isConnected){
			$this->isConnected=false;
			$this->db->close();
		}
	}

	public function queryEntityList($table, $condition, $DB){
		throw new Exception("NOT SUPPORT ANY MORE FUNCTION!!");
	}
	
	public function countEntities($table, $indexAttr, $DB){
		throw new Exception("NOT SUPPORT ANY MORE FUNCTION!!");	
	}
	
	private function deleteIndex($table, $key, $indexAttr, $DB){
		throw new Exception("NOT SUPPORT ANY MORE FUNCTION!!");	
	}
	
	private function putIndex($table, $key, $indexAttrs, $indexValues, $indexTypes){
		throw new Exception("NOT SUPPORT ANY MORE FUNCTION!!");
	}
}
?>

