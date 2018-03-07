<?php
if(!interface_exists('KeyValueDBIf', false)){	
	require_once(dirname(__FILE__)."/KeyValueIface.php");
}

class MySQLDB implements KeyValueDBIf
{
	private $client;
	private $host;
	private $user;
	private $password;
	private $database;
	private $isConnected = false;
	
	function __construct($config = array())
	{
		$this->host 	= xingcloud_get("mysql_host");
		$this->port 	= xingcloud_get("mysql_port");
		$this->user 	= xingcloud_get("mysql_user");
		$this->password = xingcloud_get("mysql_passwd");
		$this->database = xingcloud_get("mysql_db");	
	}
	
	public function close()
    {
		
    }	
	
	public function connect()
	{
		$this->client = mysql_pconnect($this->host.':'.$this->port, $this->user, $this->password);
		if(!$this->client)
			throw new Exception(mysql_error());
		$db_selected = mysql_select_db($this->database, $this->client);
		if (!$db_selected) 
			throw new Exception(mysql_error());
		$this->isConnected = true;
	}
	
	public function putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType)
	{
		if(!$this->isConnected)
			$this->connect();
		$putSQL = "INSERT INTO ".$table." (key_, value_) VALUES ( '". $key. "','".$value."') ON DUPLICATE KEY UPDATE value_ = '".$value."'";
		$result = mysql_query($putSQL,$this->client);
		if(!$result)
		{
			$createSQL = "CREATE TABLE ".$table. "( key_ varchar(200) not null, value_ mediumBLOB, PRIMARY KEY(key_) )";
			mysql_query($createSQL,$this->client);
			$result = mysql_query($putSQL,$this->client);
		}
		if(!$result)
			throw new Exception(mysql_error($this->client));
			
		$this->putIndex($table, $key, $indexAttr, $indexValue, $indexType);
	}
	
	public function putEntityList($table, $keys, $values, $DB, $indexAttr, $indexValues, $indexType)
	{
		if(!$this->isConnected)
			$this->connect();
		foreach($keys as $key)
		{
			$value = current($values);
			$indexValue = current($indexValues);
			
			$this->putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType);
			 
			next($values);
			next($indexValues);
		}
	}
	
	private function putIndex($table, $key, $indexAttrs, $indexValues, $indexTypes)
	{
		if(!$this->isConnected)
			$this->connect();
		foreach($indexAttrs as $indexAttr)
		{
			$indexValue = current($indexValues);
			$indexType = current($indexTypes);
			$putSQL = "INSERT INTO ".$table."_".$indexAttr." (key_, value_) VALUES ( '". $key. "','".$indexValue."') ON DUPLICATE KEY UPDATE value_ = '".$indexValue."'";
			$result = mysql_query($putSQL,$this->client);
			if(!$result)
			{
				if($indexType == "string")
					$type = "varchar(200)";
				else if($indexType == "integer")
					$type = "int";
				else if($indexType == "float")
					$type = "float";
				else if($indexType == "double")
					$type = "double";
                    
				$createSQL = "CREATE TABLE ".$table."_".$indexAttr."( key_ varchar(200) PRIMARY KEY, value_ ".$type." )";
				mysql_query($createSQL,$this->client);
				$result = mysql_query($putSQL,$this->client);
				if(!$result)
					throw new Exception(mysql_error($this->client));
			}
			next($indexValues);
			next($indexTypes);
		}
		
	}
	
	public function getEntity($table, $key, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		$getSQL = "SELECT * FROM ".$table." WHERE key_='". $key."'";
		$result = mysql_query($getSQL,$this->client);
		if(!$result)
			return null;
		else
		{
			$row = mysql_fetch_assoc($result);
			return $row["value_"];
		}
		
	}
	
	public function getEntityList($table, $keys, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		$keyResult = array();
		foreach($keys as $key)
		{
			$getSQL = "SELECT * FROM ".$table." WHERE key_='". $key."'";
			$result = mysql_query($getSQL,$this->client);
			if(!$result)
				continue;
			else
			{
				$row = mysql_fetch_assoc($result);
				$keyResult[$key] = $row["value_"];
			}
		}
		return $keyResult;
	}
	
	public function queryEntityList($table, $condition, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		if(is_null($condition->filterAttr) || $condition->filterAttr == "")
			$getListSQL = "SELECT key_ FROM ".$table."_".$condition->orderAttr;
		else 
		{
			$getListSQL = "SELECT key_ FROM ".$table."_".$condition->filterAttr;
			$getListSQL .= " WHERE value_".$condition->filterOperator.'\''.mysql_real_escape_string($condition->filterValue).'\'';
		}
		if($condition->orderDir == "-")
			$getListSQL .= " ORDER BY value_ DESC";
		if($condition->orderDir == "+")
			$getListSQL .= " ORDER BY value_ ASC";
		if($condition->limitOffset == "")
			$condition->limitOffset = 0;
		if($condition->limitRange == "")
			$condition->limitRange = 100;
			$getListSQL .= " LIMIT ".$condition->limitOffset.",".$condition->limitRange;
		$result = mysql_query($getListSQL,$this->client);
		if(!$result){
			return array();
		}
		$keyList = array();
		$jasonStringList = array();
		while ($row = mysql_fetch_assoc($result))
		{
			array_push($keyList, $row["key_"]);
		}
		$valueList = $this->getEntityList($table, $keyList, $DB);
		foreach($valueList as $key=>$jasonString)
		{
			array_push($jasonStringList, $jasonString);
		}
		return $jasonStringList;
	}
	
	private function deleteIndex($table, $key, $indexAttr, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		foreach($indexAttr as $attr)
		{
			$delIndexSQL = "DELETE FROM ".$table."_".$attr." WHERE key_='". $key."'";
			$result = mysql_query($delIndexSQL,$this->client);		
			if(!$result)
				throw new Exception(mysql_error($this->client));
		}	
	}
	public function delEntity($table, $key, $indexAttr, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		$delSQL = "DELETE FROM ".$table." WHERE key_='". $key."'";
		$result = mysql_query($delSQL,$this->client);
		if(!$result)
			throw new Exception(mysql_error($this->client));
		$this->deleteIndex($table, $key, $indexAttr, $DB);
	}
	
	public function countEntities($table, $indexAttr, $DB)
	{
		if(!$this->isConnected)
			$this->connect();
		$countSQL = "SELECT COUNT(*) FROM ".$table."_".$indexAttr;
		$result = mysql_query($countSQL,$this->client);
		if(!$result)
			throw new Exception(mysql_error($this->client));
		else
		{
			$row = mysql_fetch_assoc($result);
			return $row["COUNT(*)"];
		}
	}
}
?>