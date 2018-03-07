<?php
import('util.model.XObject');
import('persistence.dao.XDAO');
/**
 * RActiveRecord
 * 
 * active record orm class
 * 
 * active record风格的持久化实体类
 * 
 * 针对ik2中的mysql关系存储实体类，而非key-value
 * 
 * @author rome
 * @package dao 
 */
class RActiveRecord extends XObject{
	protected $uid; //主键,唯一标示,若不指定将自动生成
	protected $itemId; //对应的xml ID
	
	protected $className;
	private $dao;
	protected $save = false;
	private $__original = array();
	function __construct(){
		parent::__construct();
		$this->className = get_class($this);
		import('persistence.dao.RActiveRecordDAO');
	}
	
	/** 
	 * <b>dao setter</b>
	 * 
	 * <b>注入XDAO实例的方法</b>
	 * 
	 * @param XDAO $dao 实体
	 * @return RActiveRecord
	 */	
	public function setDao(XDAO $dao){
		$this->dao = $dao;
		return $this;
	}
	
	/** 
	 * <b>dao getter</b>
	 * 
	 * <b>返回XDAO实例的方法</b>
	 * 
	 * @return XDAO
	 */	
	public function dao(){
		if(!$this->dao){
			import('persistence.dao.RActiveRecordDAO');
			$this->dao = RActiveRecordDAO::dao($this->className);
		}
		return $this->dao;
	}
	
	/**
	 *  <b>根据主键取得一条记录并返回对象实例</b>
	 *
	 * @param String $className
	 * @param String $pk
	 * @return Object
	 */
	public static function getOne($className, $uid){
		$model = new $className();
		$model->beforeCreated();
		$readCount = 1;
		$retryCount = 3;
		do {
			if($readCount > 1){//如果读取次数超过一次打记录
				$dir = GAME_LOG_DIR.'/'.date('Y-m-d').'_mysql';
				$str = "time:".time()." table: $className uid: $uid err: ".mysql_error();
				if (is_dir($dir) || @mkdir($dir, 0777))
					file_put_contents($dir.'/getOne.log',$str."\n",FILE_APPEND);
			}
			$res = $model->dao()->getOne($model, $uid);
		}while (!$res && $readCount++ < $retryCount && mysql_error());
		if(!$res) return null;
		$model = $res;
		$model->afterCreated();
		return $model;
	}
	
	/**
	 * <b>将一组数据转化为对象实例</b>
	 *
	 * @param String $className 模型类名
	 * @param array $results 结果数组
	 * @param Boolean $retArr 单条记录时，设置返回值类型，默认为对象
	 * @return  Object Or Array
	 */
	public static function toObject($className, $results = array(), $retArr = false){
		if(is_null($results) || !is_array($results)){
			return null;
		}
		$modelArr = array();
		foreach ($results as $key => $result){
			$model = new $className();
			$model->beforeCreated();
			$model->dao()->create($model, $result);
			$model->afterCreated();
			$modelArr[] = $model;
		}
		if(count($modelArr) == 1 && !$retArr){
			return $modelArr[0];
		}
		return $modelArr;
	}
	
	/** 
	 * <b>entity persistent method</b>
	 * 
	 * <b>实体持久化方法</b>
	 * 
	 * <b>该方法会根据实体持久化状态决定是进行insert还是update。建议用户只调用本方法而不是insert或update</b>
	 * 如果传入参数$values，会在持久化之前，将$values中的值按键值赋给对应的属性，效果等同于setAttrs($values)
	 * @param array $values 属性数组，默认为空数组，可以不传该参数
	 * @return mixed
	 */
	public function save(array $values = array()){
		$values['className'] = get_class($this);
		if($this->isSaved()){
			return $this->update($values);
		}
		return $this->insert($values);
	}
	
	/** 
	 * <b>entity first persistent method</b>
	 * 
	 * <b>新实体第一次持久化方法</b>
	 * 
	 * <b>save()方法会根据实体持久化状态决定是进行insert还是update。建议用户只调用save()而不是insert()或update()</b>
	 * 
	 * 在真正持久化之前，会调用beforeCreated()方法，在持久化结束之后，会调用afterCreated()方法。
	 * 
	 * 用户可以在实体类中实现这些方法实现特定逻辑。
	 * 
	 * 如果传入参数$values，会在持久化之前，将$values中的值按键值赋给对应的属性，效果等同于setAttrs($values)
	 * 
	 * @param array $values 属性数组，默认为空数组，可以不传该参数
	 * @return mixed
	 */
	public function insert(array $values = array()){
		foreach($values as $key => $value){
			$this->set($key, $value);
		}
		$this->beforeCreated();
		$result = $this->dao()->save($this);
		$this->afterCreated();
		return $result;
	}
	
	/** 
	 * <b>entity persistent updating method</b>
	 * 
	 * <b>实体更新持久化方法</b>
	 * 
	 * <b>该方法会根据实体持久化状态决定是进行insert还是update。建议用户只调用save()而不是insert()或update()</b>
	 * 
	 * 在真正持久化之前，会调用beforeUpdated()方法，在持久化结束之后，会调用afterUpdated()方法。
	 * 
	 * 用户可以在实体类中实现这些方法实现特定逻辑
	 * 
	 * 如果传入参数$values，会在持久化之前，将$values中的值按键值赋给对应的属性，效果等同于setAttrs($values)
	 * @param array $values 属性数组，默认为空数组，可以不传该参数
	 * @return mixed
	 */
	public function update(array $values = array()){
		foreach($values as $key => $value){
			$this->set($key, $value);
		}
		$this->beforeUpdated();
		$result = $this->dao()->save($this);
		$this->setSaved();
		$this->afterUpdated();		
		return $result;
	}
	
	/** 
	 * <b>remove the entity from persistence</b>
	 * 
	 * <b>从持久化存储中删除实体</b>
	 * 
	 * 在删除之前，会调用beforeRemoved()方法，在删除结束之后，会调用afterRemoved()方法。
	 * 
	 * 用户可以在实体类中实现这些方法实现特定逻辑
	 * 
	 * @return boolean
	 */	
	public function remove(){
		$this->beforeRemoved();
		$result = $this->dao()->remove($this);
		if($result){			
			$this->setSaved(false);
			$this->afterRemoved();
		}
		return $result;
	}
	
	/**
	 * 
	 * 设置记录持久化状态
	 * 一般不建议直接访问该方法
	 */
	public function setSaved($save = true){
		$this->save = $save;
	} 
	
	/** 
	 *判断记录是否已被持久化到数据库
	 */	
	public function isSaved(){
		return $this->save;
	}
	
	public function pk(){
		return $this->uid;
	}
	
	/**
	 * 设置记录的原始属性值
	 *
	 * @param array $original 原始属性值
	 * 
	 */
	function setOriginal(array $original) {
		$this->__original = $original;
	}
	
	/**
	 * 取得记录的原始属性值
	 *
	 * @return array
	 */
	function getOriginal(){
		return $this->__original;
	}
	
	/**
	 * 判断当前记录是脏数据（是否被修改过）
	 *
	 * @return boolean
	 */
	function isDirty() {
		return $this->attrs() != $this->getOriginal();
	}
	
	/**
	 * 给物品对象填充固定的xml属性
	 *
	 * @param unknown_type $record
	 */
	public function fillXMLProperty($xml, $itemId = null){
		if($this->itemId == NULL && $itemId == NULL)  return $this;
		import('service.item.ItemSpecManager');
		$itemId = empty($itemId) ? $this->itemId : $itemId;
		$xmlGoods = ItemSpecManager::singleton('default', $xml)->getItem($itemId);
		if(!$xmlGoods) return $this;
		$goodsArr = get_object_vars($xmlGoods);
		foreach ($goodsArr as $key => $value){
			if($this->{$key} == null){
				$this->{$key} = $value; 	
			}
		}
		return $this;
	}
	
	/**
	 * 对指定属性进行序列化
	 *
	 * @param String $property
	 * @return unknown
	 */
	public function serializeProperty($property){
		if($this->{$property} === NULL || is_string($this->{$property})) return false;
		$this->{$property} = json_encode($this->{$property});
		return true;
	}
	
	/**
	 * 对指定属性进行反序列化
	 *
	 * @param String $property
	 * @return unknown
	 */
	public function unserializeProperty($property){
		if($this->{$property} === NULL || !is_string($this->{$property})) return false;
		$this->{$property} = json_decode($this->{$property},true);
		return $this->{$property};
	}
	
	/**
	 * 属性数值自加
	 *
	 * @param String $property
	 * @param Integer $nums
	 * @return Object
	 */
	public function increase($property, $nums = 1){
		if(preg_match("/^\d+\.?\d*$/", ($this->{$property}))){
			$this->{$property} += $nums;
		}
		return $this;
	}
	
	public function beforeRemoved(){
		
	}
	
	private function afterRemoved(){
		
	}
	
	public function beforeCreated(){
		
	}
	
	public function afterCreated(){
	}
	
	public function beforeUpdated(){
		
	}
	
	public function afterUpdated(){
		
	}
	/**
	 * 取得缓存值
	 * @param string $key
	 */
	public static function getCacheValue($key)
	{
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		return $cache->get($key);
	}
	/**
	 * 设置缓存值
	 * @param string $key
	 * @param object $values
	 */
	public static function setCacheValue($key,$values)
	{
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$cache->set($key,$values,120);
	}
	/**
	 * 删除缓存值
	 * @param string $key
	 * @param object $values
	 */
	public static function delCacheValue($key)
	{
		import('util.cache.XCache');
		$cache = XCache::singleton();
		$cache->setKeyPrefix('IK2');
		$cache->delete($key);
	}
}
?>