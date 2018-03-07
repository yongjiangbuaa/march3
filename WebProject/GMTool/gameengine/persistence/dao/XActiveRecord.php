<?php
import('util.model.XObject');
import('persistence.dao.XDAO');
/**
 * XActiveRecord
 * 
 * active record orm class
 * 
 * active record风格的持久化实体类
 * 
 * @author Tianwei
 * @package dao 
 */
class XActiveRecord extends XObject {
	
	protected $className = null;

 	/**
 	 * @Transient
 	 * @Visible(visible=false,scope=any)
 	 */
	protected $dao = null;
	
 	/**
 	 * @Transient
 	 * @Visible(visible=false,scope=any)
 	 */
	protected $saved = false;
	
	/** 
	 * <b>construct method</b>
	 * 
	 * <b>默认构造方法</b>
	 * 
	 * <b>继承本类的子类，如果要覆盖构造函数，必须是无参构造函数，并且在子类构造函数中加上</b>
	 * 
	 * <code>
	 * parent::__construct();
	 * </code>
	 * 
	 * @param XDAO $dao 实体
	 * @return XActiveRecord
	 */
	function __construct(){
		parent::__construct();
		$this->className = get_class($this);
		import('persistence.dao.XActiveRecordDAO');
	}
	
	/** 
	 * <b>dao setter</b>
	 * 
	 * <b>注入XDAO实例的方法</b>
	 * 
	 * @param XDAO $dao 实体
	 * @return XActiveRecord
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
			import('persistence.dao.XActiveRecordDAO');
			$this->dao = XActiveRecordDAO::dao($this->className);
		}
		return $this->dao;
	}
	
	/** 
	 * <b>persistent state setter</b>
	 * 
	 * <b>设置对象持久态标志，即是否在数据库中具有对应的记录,并拥有一个持久化标识</b>
	 * 
	 * <b>该标志用于标记对象的状态，除非特别用到，请不要修改该标志</b>
	 * 
	 * @param boolean $saved 是否持久化过
	 * @return XActiveRecord
	 */
	public function setSaved($saved){
		$this->saved = $saved;
		return $this;
	}
	
	/** 
	 * <b>persistent state getter</b>
	 * 
	 * <b>返回对象持久态标志，即是否在数据库中具有对应的记录,并拥有一个持久化标识</b>
	 * 
	 * @param boolean $saved 是否持久化过
	 * @return XActiveRecord
	 */
	public function isSaved(){
		return $this->saved;
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
		if($this->saved){
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
		$this->beforeCreated();
		$result = $this->dao()->save($this, $values);
		$this->saved = true;
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
		$this->saved = true;
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
			$this->saved = false;
			$this->afterRemoved();
		}
		return $result;
	}
	
	/** 
	 * <b>lazy loading getter method, supporting batch lazy loading</b>
	 * 
	 * <b>覆盖父类getter方法，支持延迟加载和批量延迟加载</b>
	 * 
	 * <b>支持延迟加载要求属性注释中同时定义引用方式保存和延迟加载读取，例如：</b>
	 * 
	 * <code>
 	 * ...
 	 * * @Save(type=reference)
 	 * * @Fetch(type=lazy)
	 * ...
	 * protected $items = array();
	 * </code>
	 * 
	 * <b>如果属性是私有属性，必须实现该属性的getter方法，不然会抛出异常</b>
	 * 
	 * <b>getter方法名必须为get + 属性名首字母大写，例如属性为name，getter方法名必须为getName()，并且无参数</b>
	 * 
	 * <b>如果传入的$key为数组，则将自动批量延迟加载，返回的结果是传入的$key数组值为键值的数据数组</b>
	 * 
	 * @param mixed $key 属性名，可以为一组属性组成的数组
	 * @return mixed
	 */
	public function get($key){
		if(!is_array($key)){
			$value = $this->$key;
			if(empty($value)
				|| is_object($value)
				|| !$this->dao()->isLazyLoad($key)){
				return $value;
			}			
			import('persistence.orm.PersistenceSession');
			$this->$key = PersistenceSession::singleton()->getStreamHelper()->keyClassSearch($value);
			return $this->$key;
		}
		$results = array();
		$gets = array();
		$lazyLoads = $this->dao()->propertiesByLazyLoad();
		foreach($key as $property){
			$value = $this->$property;
			if(empty($value)
				|| is_object($value)
				|| !isset($lazyLoads[$property])){
				$results[$property] = $value;
			}else{
				$results[$property] = null;
				$gets[$property] = $value;
			}
		}
		if(empty($gets)){
			return $results;
		}
		import('persistence.orm.PersistenceSession');
		$gets = PersistenceSession::singleton()->getStreamHelper()->keyClassSearch($gets);
		foreach($gets as $property => $get){
			$this->$property = $get;
			$results[$property] = $get;
		}
		return $results;
	}
	
	/** 
	 * <b>setter method</b>
	 * 
	 * <b>覆盖父类setter方法</b>
	 * 
	 * <b>如果属性是私有属性，必须实现该属性的setter方法，不然会抛出异常</b>
	 * 
	 * <b>setter方法名必须为set + 属性名首字母大写，例如属性为name，setter方法名必须为setName($name)，只有一个参数</b>
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XActiveRecord
	 */
	public function set($key, $value){
		if($key{0} == 'c'
			&& $key == 'className'){
			return $this;
		}
		$this->$key = $value;
		return $this;
	}
	
	/** 
	 * <b>persistent data getter method</b>
	 * 
	 * <b>以数组形式返回对象持久化数据信息</b>
	 * 
	 * <b>该函数返回的信息只包括可以持久化的属性，不包括注释为@Transient的属性</b>
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XActiveRecord
	 */
	public function attrs(){
		$attrs = array();
		$visibleProperties = array();
		if($this->isSelf()){
			$visibleProperties = array_merge($this->dao()->visibleProperties(), $this->dao()->selfVisibleProperties());
		}else{
			$visibleProperties = $this->dao()->visibleProperties();
		}
		
		foreach($visibleProperties as $propertyName){
			if(is_object($this->$propertyName)
				&& method_exists($this->$propertyName, 'attrs')){
				$attrs[$propertyName] = $this->$propertyName->attrs();
				continue;
			}
			$attrs[$propertyName] = $this->$propertyName;
		}
		return $attrs;
	}
	
	/** 
	 * <b>unique persistent lock id getter method</b>
	 * 
	 * <b>返回全局唯一的实体持久化锁id</b>
	 * 
	 * <b>该方法要求实体必须含有主键且主键必须为非空，否则会抛出异常Exception</b>
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XActiveRecord
	 * @throws Exception
	 */
	public function getLockId(){
		$lockId = $this->get($this->dao()->id());
		if(!$lockId){
			throw new Exception("class ".get_class($this)." must have valid primary key");
		}
		return 'lock_'.get_class($this).'_'.$lockId;
	}

	/** 
	 * <b>callback method called when lock is conflicted</b>
	 * 
	 * <b>锁冲突回调函数，当锁冲突时会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的锁冲突处理逻辑</b>
	 * 
	 * @param XLocker $locker 锁管理类对象
	 * @return mixed
	 */	
	public function onLockConflicted(XLocker $locker){
	}	
	
	/** 
	 * <b>callback method called when lock is released</b>
	 * 
	 * <b>锁释放回调函数，当锁释放时会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的锁释放处理逻辑</b>
	 * 
	 * @param XLocker $locker 锁管理类对象
	 * @return mixed
	 */		
	public function onLockReleased(XLocker $locker){		
	}
	
	/** 
	 * <b>return if this object is equal to the given object</b>
	 * 
	 * <b>判断当前实例是否与传入的参数相等</b>
	 * 
	 * <b>覆盖equals()方法会影响持久化及其状态维护的正确性。请确保equals()方法逻辑正确。</b>
	 * 
	 * @param object $other 要比较的对象
	 * @return boolean
	 */	
	public function equals($other){
		if(!$other instanceof XActiveRecord
			|| get_class($this) !== get_class($other)){
			return false;
		}
		$id = $this->dao()->id();
		if($this->get($id) != $other->get($id)){
			return false;
		}
		return x_array_equals($this->attrs(), $other->attrs());
	}

	/** 
	 * <b>callback method called before the entity is persistent first time</b>
	 * 
	 * <b>第一次持久化之前的回调函数，当实体对象第一次持久化之前会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */		
	protected function beforeCreated(){
		
	}
	
	/** 
	 * <b>callback method called after the entity is persistent first time</b>
	 * 
	 * <b>第一次持久化之后的回调函数，当实体对象第一次持久化之后会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */	
	protected function afterCreated(){
		
	}

	/** 
	 * <b>callback method called before the entity is updated in persistence</b>
	 * 
	 * <b>持久化更新之前的回调函数，当实体对象持久化更新之前会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */	
	protected function beforeUpdated(){
		
	}

	/** 
	 * <b>callback method called after the entity is updated in persistence</b>
	 * 
	 * <b>持久化更新之后的回调函数，当实体对象持久化更新之后会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */
	protected function afterUpdated(){
		
	}

	/** 
	 * <b>callback method called before the entity is removed from persistence</b>
	 * 
	 * <b>从持久化存储中删除实体对象之前的回调函数，当实体对象从持久化存储中删除之前会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */	
	protected function beforeRemoved(){
		
	}

	/** 
	 * <b>callback method called after the entity is removed from persistence</b>
	 * 
	 * <b>从持久化存储中删除实体对象之后的回调函数，当实体对象从持久化存储中删除之后会调用</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */		
	protected function afterRemoved(){
		
	}
	
	/** 
	 * <b>unique persistent cache key getter method</b>
	 * 
	 * <b>返回全局唯一的实体持久化缓存主键</b>
	 * 
	 * <b>该方法要求实体必须含有主键且主键必须为非空，否则会抛出异常Exception</b>
	 * 
	 * @param string $key 属性名
	 * @param mixed $value 属性值
	 * @return XActiveRecord
	 * @throws Exception
	 */	
	public function getCacheKey($method, $args = array(), $config = array()){
		$uid = $this->get($this->dao()->id());
		if(!$uid){
			throw new Exception("class ".get_class($this)." must have valid primary key");
		}
		return 'cache_'.get_class($this).'_'.$uid;
	}
	
	/** 
	 * <b>callback method called after the cache is updated</b>
	 * 
	 * <b>实体对象放入缓存之后的回调函数</b>
	 * 
	 * <b>子类覆盖该方法实现自己的处理逻辑</b>
	 * 
	 * @return mixed
	 */	
	public function onCacheUpdated($cacheKey, $cacheValue){		
	}
	
	
	public function isSelf(){
		return true;
	}
	/**
	 * 
	 * 获得指定用户的一组对象，重写此方法
	 * @param string $uid 用户uid
	 */
	public function getItems($uid)
	{
		return PersistenceSession::singleton()->get($this->className, $uid);
	}
}
?>