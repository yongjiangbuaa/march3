<?php
/**
 * Mysql 操作类
 * @author zhaohongfu
 */
class XMySQL
{
	private $client;
	private $host;
	private $port;
	private $user;
	private $password;
	private $database;
	private $charset = 'UTF8';
	private $pconnect = false;
	/**
	 * 
	 * 构造函数
	 * @param $params
	 */
	public function __construct($params)
    {
        $this->host = $params['host'];
        $this->port = $params['port'];
        $this->user = $params['user'];
        $this->password = $params['password'];
        $this->database = $params['db'];
    }

	public function __destruct()
    {
        $this->close();
    }

    public function close(){
	    if($this->client){
	        mysqli_close($this->client);
            $this->client = null;
        }
    }
	/**
	 * 
	 * 连接数据库
	 */
	public function connect()
	{
		if($this->client){
			return $this;
		}
		if(empty($this->host)){
			$this->host = 'localhost';
		}
		$host = $this->host;
		if ($this->pconnect) {
			$host = 'p:' . $host;
		}
		$this->client = mysqli_connect($host, $this->user, $this->password, $this->database, $this->port);

		if(!$this->client){
			$this->setLog("Connection to mysql DB failed!");
			throw new Exception("Connection to mysql DB failed!".$this->host.':'.$this->port.$this->user.APP_DIR);
		}
		mysqli_query($this->client, 'SET NAMES '.$this->charset);
		return $this;
	}
	
	public function getClient(){
		return $this->client;
	}
	public function setLog($str,$logType = 0){
 		$logEnable = true;
		switch ($logType){
			case 0:
				$logName = 'mysqlErr';
				break;
			case 1:
				$logName = 'mysql';
				$logName .= date('Y-m-d H');
				break;
			default:
				$logName = 'mysqlErr';
				break;
		}
		$str .= ' host:'.$this->host.' port:'.$this->port.' user:'.$this->user.' password:'.$this->password;
		if($logEnable || $logType == 0){
			$dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
			if (is_dir($dir) || @mkdir($dir, 0777))
				file_put_contents($dir.'/'.$logName.'.log',time().' '.$str."\n",FILE_APPEND);
		}
	}
	public function writeSlowLog($sql,$sqlStart,$sqlEnd){
 		$logEnable = true;
		$slow = 1;//s
		$costTime = $sqlEnd - $sqlStart;
		if($logEnable && $costTime > $slow){
			$dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
			if (is_dir($dir) || @mkdir($dir, 0777))
				file_put_contents($dir.'/mysqlSlow.log',time().' cost:'.$costTime.'ms sql:'.$sql."\n",FILE_APPEND);
		}
	}

	/**
	 *
	 * 执行语句
	 * @param string $sql 语句
	 * @return resource
	 */
	public function execute($sql){
		$this->setLog($sql,1);
		$ret = $this->execQuery($sql);
		return $ret;
	}

	/**
	 *
	 * 执行语句并返回结果集
	 * @param string $sql 执行语句
	 * @param int $limit 返回条目
	 * @return array|null
	 */
	public function execResult($sql, $limit = 1){
		$this->setLog($sql,1);
	    $rowResults = null;
	    $rowId = 1;
	    if(stripos($sql,'limit') === false && stripos($sql,'select') !== false){
	    	$sql .= " limit $limit";
	    }
	    $result = $this->execQuery($sql);
	    if ($result) {
			while ($curRow = mysqli_fetch_assoc($result) ) {
				if ($rowId <= $limit) {
					$rowResults[] = $curRow;
				}
				else{
					break;
				}
		        $rowId ++;
		    }
	    }
	    return $rowResults;
	}

	/**
	 *
	 * 执行语句并返回结果集
	 * @param string $sql 执行语句
	 * @return array
	 */
	public function execResultWithoutLimit($sql){
		$this->setLog($sql,1);
		$rowResults = array();
		$result = $this->execQuery($sql);
		if ($result) {
			while ($curRow = mysqli_fetch_assoc($result) ) {
				$rowResults[] = $curRow;
			}
		}
		return $rowResults;
	}
	public function getTables(){
		$rowResults = null;
		$result = $this->execute("show tables");
		if ($result) {
			while ($curRow = mysqli_fetch_assoc($result) ) {
				$rowResults[] = $curRow;
			}
		}
		return $rowResults;
	}

	/**
	 *
	 * 插入一条记录
	 * @param string $tablename 表名
	 * @param array $value 字段名（key）与值（value）
	 * @return resource
	 */
	public function add($tablename, $value){
		$fields = implode(",",array_keys($value));
		$values = "'".implode("','",array_map('addslashes',array_values($value)))."'";		
		$sql = "insert into $tablename($fields) values($values)";

		return $this->execute($sql);
	}

	/**
	 *
	 * 插入一条记录
	 * @param string $tablename 表名
	 * @param array $value 字段名（key）与值（value）
	 * @return resource
	 */
	public function addDelay($tablename, $value){
		$fields = implode(",",array_keys($value));
		$values = "'".implode("','",array_map('addslashes',array_values($value)))."'";		
		$sql = "insert into $tablename($fields) values($values)";
		return $this->execute($sql);
	}

	/**
	 *
	 * 插入多条记录
	 * @param string $tablename 表名
	 * @param array $values $value 字段名（key）与值（value）
	 * @return resource
	 * @throws Exception
	 */
	public function addBatch($tablename, array $values){
		$fields = implode(",",array_keys($values[0]));
		$sql_values = array();
		foreach($values as $value){
			$sql_values[] = "'".implode("','",array_map('addslashes',array_values($value)))."'";
		}
		$sql_values = implode("),(",$sql_values);	
		$sql = "insert into $tablename($fields) values($sql_values)";

		return $this->execute($sql);
	}

	/**
	 *
	 * 更新指定条件记录
	 * @param string $tablename 表名
	 * @param string $where 更新条件
	 * @param array $value 字段名（key）与值（value）
	 * @return null|resource
	 */
	public function put($tablename, $where, $value){
		$sql = "";
		foreach($value as $k=>$v){
			$sql.= "$k='".addslashes($v)."',";
		}
		$whereSql = "";
		if (is_array($where)) {
			foreach ((array)$where as $key=>$val) {
				if (strlen($whereSql) > 0 ) {
					$whereSql .= " and ";
				}
				$whereSql .= addslashes($key)."='".addslashes($val)."'";
			}
			if ($whereSql) {
				$whereSql =  " where " . $whereSql;
			}
			$sql = "update $tablename set ".trim($sql,",") . $whereSql;
			return $this->execute($sql);
		}
		else{
			return null;
		}
	}
	/**
	 * 
	 * 删除指定条件记录
	 */
	public function del($tablename,$where){
		$whereSql = "";
		foreach ($where as $key=>$val) {
			if (strlen($whereSql) > 0 ) {
				$whereSql .= " and ";
			}
			$whereSql .= addslashes($key)."='".addslashes($val)."'";
		}
		if ($whereSql) {
			$whereSql =  " where " . $whereSql;
		}
		$sql = "delete from $tablename $whereSql";

		return $this->execute($sql);
	}

	/**
	 *
	 * 查询指定条件记录数
	 * @param string $tablename
	 * @param array $keyValues
	 * @return null
	 */
    public function exist($tablename,$keyValues){
        if(empty($keyValues) || count($keyValues) ==0 ){return null;}
         
        $idx = 0;
        foreach ($keyValues as $key=>$value){
            $sql_conditions[] = $key." = '".addslashes($value)."' ";
            $idx++;
        }
        $whereSql = implode(" and ",$sql_conditions);
		if ($whereSql) {
			$whereSql =  " where " . $whereSql;
		}    
        $sql = "select count(1) DataCount from " . $tablename . "  " .  $whereSql;
		$result = $this->execResult($sql);
        return $result[0]['DataCount'];
    } 	
    /**
     * 
     * 取得
     */
	public function get($tablename, $where, $fields = null, $limit = 1, $orderBy = null){
		// where条件中的键值不一致则返回空
		if(empty($where) || count($where) ==0 ){return null;}
		 
		$idx = 0;
		$whereSql = "";
		if (is_array($where)) {
			foreach ($where as $key=>$value){
				$sql_conditions[] = $key." = '".addslashes($value)."' ";
				$idx++;
			}
			$whereSql = implode(" and ",$sql_conditions);	
		}
		elseif (is_string($where)){
			$whereSql = $where;	
		}
		
		if(empty($fields)){
			$fields = "*";
		}
		if (is_numeric($limit)) {
			$limits = "";
			if ($limit > 0) {
				$limits = " LIMIT 0, $limit" ;
			}
		}
		if ($whereSql) {
			$whereSql =  " where " . $whereSql;
		}
		$sql = "select $fields from $tablename $whereSql $orderBy $limits";
		return $this->execResult($sql, $limit);
		
	}
	/**
	 * 
	 * 返回上次执行sql语句的影响行数
	 */
	public function affected_rows()
	{
		return mysqli_affected_rows($this->client);
	}
	
	/**
	 * 
	 * 返回上次插入数据的主键值
	 */
	public function lastInsertId(){
		return mysqli_insert_id($this->client);
	}

	/**
	 *
	 * 取得数据表信息
	 * @param $name 表名
	 * @return array
	 */
	public function describeTable($name) {
		$array = $this->execResult("SHOW COLUMNS FROM `{$name}`", 100);
		$fields = $field = array();
		foreach ($array as $row) {
			$field['name'] = $row['Field'];
			$type = $row['Type'];
			
			// split type into type(length):
			$field['scale'] = null;
			if (preg_match("/^(.+)\((\d+),(\d+)/", $type, $query_array)) {
				$field['type'] = $query_array[1];
				$field['max_length'] = is_numeric($query_array[2]) ? $query_array[2] : -1;
				$field['scale'] = is_numeric($query_array[3]) ? $query_array[3] : -1;
			} elseif (preg_match("/^(.+)\((\d+)/", $type, $query_array)) {
				$field['type'] = $query_array[1];
				$field['max_length'] = is_numeric($query_array[2]) ? $query_array[2] : -1;
			} elseif (preg_match("/^(enum)\((.*)\)$/i", $type, $query_array)) {
				$field['type'] = $query_array[1];
				$arr = explode(",",$query_array[2]);
				$field['enums'] = $arr;
				$zlen = max(array_map("strlen",$arr)) - 2; // PHP >= 4.0.6
				$field['max_length'] = ($zlen > 0) ? $zlen : 1;
			} else {
				$field['type'] = $type;
				$field['max_length'] = -1;
			}
			$field['not_null'] = ($row['Null'] != 'YES');
			$field['primary_key'] = ($row['Key'] == 'PRI');
			$field['auto_increment'] = (strpos($row['Extra'], 'auto_increment') !== false);
			$field['binary'] = (strpos($type,'blob') !== false || strpos($type,'binary') !== false);
			$field['unsigned'] = (strpos($type,'unsigned') !== false);
			$field['zerofill'] = (strpos($type,'zerofill') !== false);

			if (!$field['binary']) {
				$d = $row['Default'];
				if ($d != '' && $d != 'NULL') {
					$field['has_default'] = true;
					$field['default_value'] = $d;
				} else {
					$field['has_default'] = false;
					$field['default_value'] = 0;
				}
			}
			$fields[$field['name'] ] = $field;
		}
		return $fields;		
	}

    /**
     * @param $sql
     * @return mixed
     */
    private function execQuery($sql)
    {
        $this->connect();
        $sqlStart = microtime(true);
        $ret = mysqli_query($this->client, $sql);
        $sqlEnd = microtime(true);
        if(!$ret)
            $this->setLog('sql:'.$sql.' err:'.mysqli_error($this->client));
        $this->writeSlowLog($sql,$sqlStart,$sqlEnd);
        return $ret;
    }
}
