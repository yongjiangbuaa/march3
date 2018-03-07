<?php
/**
 * PersistenceSession
 * 
 * The main interface class abstracting the notion of a persistence service
 * 
 * 实现数据持久化存储的核心类，提供了更新，查找，删除接口
 * 
 * @author Ninghe
 * @final
 * @package persistence 
 * @todo 
 */
include_once("xingcloud.php");

class PersistenceSession{
	private $readEntitiesMap;
	private $writeEntitiesMap;
	private $streamHelper;
	private $instanceID;
	private static $instance = null;
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>单例方法，返回PersistenceSession的单例</b>
	 * 
	 * @param 
	 * @return PersistenceSession
	 * @throws Exception
	 */
	static function singleton() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}	

	/**
	* Constructor of PersistenceSession
	*/
	private function __construct()
	{	
		$this->writeEntitiesMap = array();
		$this->readEntitiesMap = array();
		import('persistence.orm.StreamHelper');
		$this->streamHelper = new StreamHelper($this);

	}
	
	
	/** 
	 * <b>Persist the given transient instance</b>
	 * 
	 * <b>实体持久化方法，将实体对象持久化</b>
	 * <b>实体的持久化保证对同一个Session在之后的查找可见，一般在调用flush()之前，该实体对象并未持久化到数据库，而只是在内存缓存中</b>
	 * <b>如果主键生成策略为auto，则此时为该主键赋一个唯一值</b>
	 * <b>如果有标记为级联存储的属性，则级联存储这些属性的值</b>
	 *
	 * @param mixed $entity 要存储的实体对象。如果是一个数组，则分别持久化数组的每个实体对象
	 * @return 
	 * @throws Exception
	 */	
	public function put($entity){
		if(is_null($entity)){
			throw new Exception("Null object can not be put");
		}
		elseif(is_array($entity)){
			foreach($entity as $oneEntity){
				$this->putOne($oneEntity);
			}
		}
		else{
			$this->putOne($entity);
		}
	}
	
	private function putOne($entity){
		$referenceKey = $this->streamHelper->getReferenceKeyFromObject($entity, true);
		$this->streamHelper->cascadePut($entity);
		$this->writeEntitiesMap[$referenceKey] = $entity;
	}
	
	/** 
	 * <b>Return the persistent instance of the given entity class with the given key</b>
	 * 
	 * <b>实体查找方法，获取某个指定类的指定主键的对象实体</b>
	 * <b>该查找首先从所属Session的对象缓存中查找，如果没有对应的对象实体，则从数据库查找</b>
	 *
	 * @param string $className 要查找的实体的类名
	 * @param mixed $key 要查找的对象实体的主键值。如果传入的是一个数组，则批量查找每个主键对应的对象实体，并返回一个主键->对象实体的数组
	 * @return 如果传入$key参数为一个值，返回查找到的对象实体，没有查找到满足条件的值则返回null.如果传入$key参数为一个数组，则返回主键->对象实体的数组
	 * @throws Exception
	 */	
	public function get($className, $key){
		if(empty($className)){
			throw new Exception("class name is null");
		}
		elseif($key!=0 && $key!="0" && empty($key)){
			throw new Exception("key is empty");
		}
		elseif(!is_array($key)){
			return $this->getOne($className, $key);
		}
		else
			return $this->getMulti($className, $key);
	}
	
	private function getOne($className, $key){
		$referenceKey = $className.":".($key);
		if(isset($this->writeEntitiesMap[$referenceKey])){
			return $this->writeEntitiesMap[$referenceKey];
		}
		elseif(isset($this->readEntitiesMap[$referenceKey])){
			return $this->readEntitiesMap[$referenceKey];
		}
		else{
			try{	
				$tableName=$className;
				$classAnnotation=XAnnotation::singleton($className)->annotations();
				if(isset($classAnnotation['Table']) && isset($classAnnotation['Table']['name'])){
				    $tableName=$classAnnotation['Table']['name'];
                }
				$client=KVStorageService::getService();
				$propertyListString = $client->get($tableName, ((string)$key));
				
			}
			catch(Exception $e){
				throw $e;
			}
			$entity = $this->streamHelper->Json2Object($className,  $propertyListString);
			if(!is_null($entity))
				$this->readEntitiesMap[$referenceKey] = $entity;
			return $entity;
		}
	}
	
	public function getMulti($className, $keys){
		$keysNotInCache = array();
		$entityMap = array();
		foreach($keys as $index=>$oneKey){
			$referenceKey = $className.":".($oneKey);
			if(isset($this->writeEntitiesMap[$referenceKey])){
				$entityMap[$oneKey] = $this->writeEntitiesMap[$referenceKey];
			}
			elseif(isset($this->readEntitiesMap[$referenceKey])){
				$entityMap[$oneKey] = $this->readEntitiesMap[$referenceKey];
			}
			else{
				array_push($keysNotInCache, ((string)$oneKey));
			}
		}
		if(!empty($keysNotInCache)){
			try{
				$tableName=$className;
				$classAnnotation=XAnnotation::singleton($className)->annotations();
				if(isset($classAnnotation['Table']) && isset($classAnnotation['Table']['name'])){
				    $tableName=$classAnnotation['Table']['name'];
                }

				$client=KVStorageService::getService();
				$propertyListStringMap = $client->getList($tableName, $keysNotInCache);
				
				foreach($keysNotInCache as $oneKey){
					if(!array_key_exists($oneKey, $propertyListStringMap))
						$entity = null;
					else{
						$propertyListString = $propertyListStringMap[$oneKey];
						$entity = $this->streamHelper->Json2Object($className,  $propertyListString);
					}
					$referenceKey = $className.":".$oneKey;
					if(!is_null($entity))
						$this->readEntitiesMap[$referenceKey] = $entity;
					$originalKey = ($oneKey);
					$entityMap[$originalKey] = $entity;
				}
			}
			catch(Exception $e){
				throw $e;
			}
		}
		return $entityMap;
	}
	
	
	/** 
	 * <b>Remove the specified entity</b>
	 * 
	 * <b>实体删除方法，删除某个指定对象实体</b>
	 * <b>如果有标记为级联删除的属性，则级联删除这些属性的值</b>
	 * <b>删除操作将数据库和缓存里的实体记录一起删除</b>
	 *
	 * @param mixed $entity 要删除的对象实体。如果传入的是一个数组，则批量删除数组的每一个对象实体
	 * @return 
	 * @throws Exception
	 */	
	public function remove($entity){
		if(is_null($entity)){
			throw new Exception("Null object can not be removed");
		}
		elseif(is_array($entity)){
			foreach($entity as $e){
				$this->removeOne($e);
			}
		}
		else{
			$this->removeOne($entity);
		}
	}
	
	private function removeOne($entity){
		$referenceKey = $this->streamHelper->getReferenceKeyFromObject($entity, false);
		$keyClassSplit = explode(":", $referenceKey);
		$className = $keyClassSplit[0];
		$key = $keyClassSplit[1];
		try{
				$tableName=$className;
				$classAnnotation=XAnnotation::singleton($className)->annotations();
				if(isset($classAnnotation['Table']) && isset($classAnnotation['Table']['name'])){
				    $tableName=$classAnnotation['Table']['name'];
                }

			$client=KVStorageService::getService();
			$client->del($tableName, $key);
		}
		catch(Exception $e){
			if(strstr($e->getMessage(),"doesn't exist")==false)
				throw $e;
		}
		$this->streamHelper->cascadeRemove($entity);
		if(isset($this->writeEntitiesMap[$referenceKey])){
			unset($this->writeEntitiesMap[$referenceKey]);
		}
		if(isset($this->readEntitiesMap[$referenceKey])){
			unset($this->readEntitiesMap[$referenceKey]);
		}	
	}
	
	
	
	/** 
	 * <b>Flush the entities in the session to database</b>
	 * 
	 * <b>将Session的缓存同步到数据库，即将之前的更新操作应用到数据库，并清除Session缓存</b>
	 *
	 * @param 
	 * @return 
	 * @throws Exception
	 */	
	public function flush(){
		while(TRUE){
		
			if(empty($this->writeEntitiesMap)){

				return;
			}
			
			foreach($this->writeEntitiesMap as $referenceKey=>$entity){
				$returnList = $this->streamHelper->Object2Json($entity);
                $className=$returnList["ClassName"];
    			$tableName=$className;
				$classAnnotation=XAnnotation::singleton($className)->annotations();
				if(isset($classAnnotation['Table']) && isset($classAnnotation['Table']['name'])){
				    $tableName=$classAnnotation['Table']['name'];
                }

				$key=$returnList["Key"];
				$property=$returnList["PropertyListString"];
				$indexAttr=$returnList["indexAttr"];
				$indexValue=$returnList["indexValue"];
				$indexType=$returnList["indexType"];
				
				$indexArr = $this->getIndex($entity);
				unset($this->writeEntitiesMap[$referenceKey]);
				
				try{
					$client=KVStorageService::getService();
					$client->set($tableName, $key, $property);
					if($indexArr){
						$this->saveIndex($tableName, $key, $indexArr);
					}
					
				}
				catch(Exception $e){
					throw $e;
				}
			}
			
		}
			
		unset($this->readEntitiesMap);
		unset($this->writeEntitiesMap);
		$this->readEntitiesMap = array();
		$this->writeEntitiesMap = array();
	}
	
	
	
	public function getStreamHelper(){
		return $this->streamHelper;
	}
	
	public function getByIndex($className, $where){
		$tableName = $className;
		$classAnnotation=XAnnotation::singleton($className)->annotations();
		if(isset($classAnnotation['Table']) && isset($classAnnotation['Table']['name'])){
		    $tableName=$classAnnotation['Table']['name'];
        }
        $factor = explode('=', $where);
        $indexKey = $factor[0];
        $value = $factor[1];
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton();
		$result = $mysql->get($tableName . '_' . $indexKey, 'value_ =\''.$value.'\'', null, 100);
		if(!$result){
			return null;
		}
		$pkArr = array();
		foreach($result as $value){
			$pkArr[] = $value['key_'];
		}
		return current(self::singleton()->get($className, $pkArr));
	}
	
	public function getIndex($entity){
		$indexAttr = $entity->dao()->indexes();
		if(is_array($indexAttr) && empty($indexAttr)){
			return false;
		}
		$indexValue = $indexType = array();
		foreach ($indexAttr as $key => $property){
			$indexValue[$key] = $entity->get($property);
			if(is_string($indexValue[$key]) || is_int($indexValue[$key]) || is_float($indexValue[$key]) || is_double($indexValue[$key])){
				$indexType[$key] = gettype($indexValue[$key]);
			}else{
//				throw new Exception("Index property ".$property." should be a int or a string");
			}
		}
		return array('attr' => $indexAttr, 'value' => $indexValue, 'type' => $indexType);
	}
	
	private function saveIndex($tableName, $pk, $indexArr){
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		foreach ($indexArr['attr'] as $key){
			$indexValue = current($indexArr['value']);
			$indexType = current($indexArr['type']);
			$putSQL = "INSERT INTO ".$tableName."_".$key." (key_, value_) VALUES ( '". $pk. "','".$indexValue."') ON DUPLICATE KEY UPDATE value_ = '".$indexValue."'";
			$res = $mysql->execute($putSQL);
			if(!$res){
				if($indexType == "string")
					$type = "varchar(200)";
				else if($indexType == "integer")
					$type = "int";
				else if($indexType == "float")
					$type = "float";
				else if($indexType == "double")
					$type = "double";
                else 
                	$type = "varchar(200)";
				$createSQL = "CREATE TABLE ".$tableName."_".$key."( key_ varchar(200) PRIMARY KEY, value_ ".$type." )";
				$mysql->execute($createSQL);
				$res = $mysql->execute($putSQL);
				if(!$res)
					throw new XException(mysql_error($mysql->getClient()));
			}
		}
	}

 }
 ?>
