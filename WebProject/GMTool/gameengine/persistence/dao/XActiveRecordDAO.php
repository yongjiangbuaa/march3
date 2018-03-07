<?php
import('util.annotation.XAnnotation');
import('persistence.dao.XDAO');
/**
 * XDAO
 * 
 * active record dao (Data Access Object) class
 * 
 * active record风格的数据访问接口实现类
 * 
 * @author Tianwei
 * @final
 * @package dao 
 * @todo 支持用户自定义持久化注释
 */
final class XActiveRecordDAO implements XDAO{
	private $modelName = null;
	private static $daos = array();
	private $config = array();

	private function __construct($modelName){
		$this->modelName = $modelName;
		$config = x_apc_file_fetch('dao_'.$modelName);
		if(is_array($config)){
			$this->config = $config;
			return;
		}
		$config = array();
		$config['Index'] = array();
		$config['Save'] = array();
		$config['Fetch'] = array();
		$config['properties'] = array();
		$config['visible'] = array();
		$config['selfVisible'] = array();
		$config['lazy'] = array();
		$config['reference'] = array();
		$config['value'] = array();
		
		$config['simple'] = array();

		$propertyAnnotations = XAnnotation::singleton($modelName)->propertyAnnotations();
		foreach($propertyAnnotations as $propertyName => $propertyAnnotation){
			if(!isset($propertyAnnotation['Visible'])){
				$config['visible'][$propertyName] = $propertyName;;
			}elseif(isset($propertyAnnotation['Visible']) && isset($propertyAnnotation['Visible']['visible']) && $propertyAnnotation['Visible']['visible']=="true"){
				if(!isset($propertyAnnotation['Visible']['scope']) || (isset($propertyAnnotation['Visible']['scope'])&&$propertyAnnotation['Visible']['scope']=='any')){
					$config['visible'][$propertyName] = $propertyName;
				}elseif(isset($propertyAnnotation['Visible']['scope'])&&$propertyAnnotation['Visible']['scope']=='own'){
					$config['selfVisible'][$propertyName] = $propertyName;
				}else{
					$config['visible'][$propertyName] = $propertyName;
				}
			}
			if(isset($propertyAnnotation['Transient'])){
				continue;
			}
			$config['properties'][$propertyName] = $propertyName;
			if(isset($propertyAnnotation['Id'])){				
				$config['Id'] = $propertyName;
				if(isset($propertyAnnotation['GeneratedValue'])){	
					$config['GeneratedValue'] = $propertyAnnotation['GeneratedValue'];
				}
			}
			if(isset($propertyAnnotation['Index'])){
				$config['Index'][$propertyName] = $propertyName;
			}
			if(isset($propertyAnnotation['Save'])
				&& isset($propertyAnnotation['Save']['type'])){
				$type = $propertyAnnotation['Save']['type'];

				if(!isset($config['Save'][$type])){
					$config['Save'][$type] = array();
				}
				$config['Save'][$type][$propertyName] = $propertyName;
				if($type == 'reference'){
					$config['reference'][$propertyName] = $propertyName;
				}else{
					$config['value'][$propertyName] = $propertyName;
				}
			}else{
				//$config['value'][$propertyName] = $propertyName;
				$config['simple'][$propertyName] = $propertyName;
				
			}
			if(isset($propertyAnnotation['Fetch'])
				&& isset($propertyAnnotation['Fetch']['type'])){
				$type = $propertyAnnotation['Fetch']['type'];
				if(!isset($config['Fetch'][$type])){
					$config['Fetch'][$type] = array();
				}
				$config['Fetch'][$type][$propertyName] = $propertyName;				
				if($type == 'lazy'){
					$config['lazy'][$propertyName] = $propertyName;
					if(isset($config['reference'][$propertyName])){
						unset($config['reference'][$propertyName]);
					}
				}
			}
			if(isset($propertyAnnotation['Cascade'])
				&& isset($propertyAnnotation['Cascade']['type'])){
				$type = $propertyAnnotation['Cascade']['type'];
				if(!isset($config['Cascade'][$type])){
					$config['Cascade'][$type] = array();
				}
				if($type=='save'){
					$config['Cascade']['save'][$propertyName] = $propertyName;

				}elseif($type=='remove'){
					$config['Cascade']['remove'][$propertyName] = $propertyName;

				}elseif($type=='all'){
					$config['Cascade']['save'][$propertyName] = $propertyName;
					$config['Cascade']['remove'][$propertyName] = $propertyName;

				}
				
			}
		}
		$this->config = $config;
		if(x_is_prod()){
			x_apc_file_store('dao_'.$modelName, $config);
		}
	}
	
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>单例方法，返回XActiveRecordDAO的单例，每个$model类只有一个XActiveRecordDAO实例</b>
	 * 
	 * @param string $model 实体类名
	 * @return XActiveRecordDAO
	 */
	public static function dao($model){
		if(is_object($model)){
			$model = get_class($model);
		}
		if(!isset(self::$daos[$model])) {
			self::$daos[$model] = new self($model);
		}
		return self::$daos[$model];
	}
	
	/** 
	 * <b>persistent method</b>
	 * 
	 * <b>实体持久化方法，将实体对象持久化到数据库</b>
	 * 
	 * @param object $model 实体对象
	 * @return mixed
	 */	
	public function save($model){
		import('persistence.orm.PersistenceSession');
		return PersistenceSession::singleton()->put($model);
	}
	
	/** 
	 * <b>removing method</b>
	 * 
	 * <b>实体删除方法，将实体对象从持久化数据库中删除</b>
	 * 
	 * @param object $model 实体对象
	 * @return mixed
	 */	
	public function remove($model){
		import('persistence.orm.PersistenceSession');
		return PersistenceSession::singleton()->remove($model);
	}
	
	/** 
	 * <b>primary key property name getter</b>
	 * 
	 * <b>返回该实体类定义的主键属性名，在属性注释中用@Id定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Id
	 * ...
	 * protected $uid = null;
	 * </code>
	 * 
	 * <b>如果类中没有主键属性，会抛出异常</b>
	 * 
	 * @return string
	 * @throws Exception
	 */
	public function id(){
		if(isset($this->config['Id'])){
			return $this->config['Id'];
		}
		throw new Exception("class {$this->modelName} must define an primary key");
	}
	
	/** 
	 * <b>primary key value generating strategy getter</b>
	 * 
	 * <b>返回该实体类定义的主键生成策略，在属性注释中用@GeneratedValue定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Id
	 * * @GeneratedValue(strategy=auto)
	 * ...
	 * protected $uid = null;
	 * </code>
	 * 
	 * <b>只有主键生成策略为auto时，才会在持久化时为实体自动生成主键</b>
	 * 
	 * @return mixed
	 */
	public function generatedValueStrategy(){
		if(isset($this->config['GeneratedValue'])
			&& isset($this->config['GeneratedValue']['strategy'])){
			return $this->config['GeneratedValue']['strategy'];
		}
		return null;
	}
	
	/** 
	 * <b>index property names getter</b>
	 * 
	 * <b>返回该实体类定义的所有索引属性名，在属性注释中用@Index定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Index
	 * ...
	 * protected $userId = null;
	 * </code>
	 * 
	 * @return array
	 */	
	public function indexes(){
		if(isset($this->config['Index'])){
			return $this->config['Index'];
		}
		return array();
	}
	
	/** 
	 * <b>return if the property is able to lazy load</b>
	 * 
	 * <b>返回该属性是否是延迟加载，在属性注释中用@Fetch(type='lazy')定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Save(type=reference)
	 * * @Fetch(type=lazy)
	 * ...
	 * protected $items = array();
	 * </code>
	 * 
	 * <b>延迟加载只有在声明为引用保存时才有效，即用注释@Save(type=reference)定义</b>
	 * 
	 * @return boolean
	 */
	public function isLazyLoad($propertyName){
		return isset($this->config['Fetch']) 
			&& isset($this->config['Fetch']['lazy']) 
			&& isset($this->config['Fetch']['lazy'][$propertyName]);
	}
	
	/** 
	 * <b>return if the property is saved by reference</b>
	 * 
	 * <b>返回该属性是否为引用保存，在属性注释中用@Save(type=reference)定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Save(type=reference)
	 * ...
	 * protected $items = array();
	 * </code>
	 * 
	 * @return boolean
	 */	
	public function isByReference($propertyName){
		return isset($this->config['Save'])
			&& isset($this->config['Save']['reference'])
			&& isset($this->config['Save']['reference'][$propertyName]);
	}
	
	/** 
	 * <b>return if the property is transient</b>
	 * 
	 * <b>返回该属性是否为瞬时态，瞬时态的属性将不会被持久化。在属性注释中用@Transient定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Transient
	 * ...
	 * protected $secret = null;
	 * </code>
	 * 
	 * @return boolean
	 */
	public function isTransient($propertyName){
		return !isset($this->config['properties'][$propertyName]);
	}
	
	/** 
	 * <b>property names getter, which cascase types are all</b>
	 * 
	 * <b>返回级联操作类型为all的所有索引属性名，在属性注释中用@Cascade(type=all)定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Cascade(type=all)
	 * ...
	 * protected $owndItems = null;
	 * </code>
	 * 
	 * <b>目前只支持级联保存，不支持级联删除，级联操作类型为all与级联操作类型为save在功能上一致</b>
	 * 
	 * @return array
	 */	
	public function propertiesByCascadeAll(){
		return $this->propertiesByAnnotation('Cascade', 'all');
	}
	
	/** 
	 * <b>property names getter, which cascase types are save</b>
	 * 
	 * <b>返回级联操作类型为all的所有索引属性名，在属性注释中用@Cascade(type=save)定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Cascade(type=save)
	 * ...
	 * protected $owndItems = null;
	 * </code>
	 * 
	 * @return array
	 */	
	public function propertiesByCascadeSave(){
		$properties = $this->propertiesByAnnotation('Cascade', 'save');
		if(empty($properties)){
			$properties = $this->propertiesByAnnotation('Cascade', 'all');
		}
		return $properties;
	}
	
	/** 
	 * <b>property names getter, which cascase types are remove</b>
	 * 
	 * <b>返回级联操作类型为remove的所有索引属性名，在属性注释中用@Cascade(type=remove)定义，例如</b>
	 * 
	 * <code>
	 * ...
	 * * @Cascade(type=remove)
	 * ...
	 * protected $owndItems = null;
	 * </code>
	 * 
	 * <b>目前尚不支持级联删除</b>
	 * 
	 * @return array
	 */	
	public function propertiesByCascadeRemove(){
		$properties = $this->propertiesByAnnotation('Cascade', 'remove');
		if(empty($properties)){
			$properties = $this->propertiesByAnnotation('Cascade', 'all');
		}
		return $properties;
	}
	
	private function propertiesByAnnotation($tag, $type = null){
		if(!$type){
			if(isset($this->config[$tag])){
				return $this->config[$tag];
			}
			return array();
		}
		if(isset($this->config[$tag])
			&& isset($this->config[$tag][$type])){
				return $this->config[$tag][$type];
		}
		return array();
	}
	
	/** 
	 * <b>property names getter, which types are not transient</b>
	 * 
	 * <b>返回所有不为瞬时态的属性名数组</b>
	 * 
	 * @return array
	 */	
	public function properties(){
		return $this->config['properties'];
	}
	
	/** 
	 * <b>visible property names getter, which types are visible</b>
	 * 
	 * <b>返回所有可见属性名的数组</b>
	 * 
	 * @return array
	 */	
	public function visibleProperties(){
		return $this->config['visible'];
	}
	
	/** 
	 * <b>self visible property names getter, which types are self-visible</b>
	 * 
	 * <b>返回只有自己才能看到的属性名的数组</b>
	 * 
	 * @return array
	 */	
	public function selfVisibleProperties(){
		return $this->config['selfVisible'];
	}
	
	/** 
	 * <b>return if a property exists and is not transient</b>
	 * 
	 * <b>返回属性是否存在且不为瞬时态</b>
	 * 
	 * @param string $property 属性名
	 * @return boolean
	 */		
	public function hasProperty($property){
		return isset($this->config['properties'][$property]);
	}
	
	/** 
	 * <b>返回simple类型的Field</b>
	 * 
	 * @return array
	 */	
	public function propertiesBySimple(){
		return $this->config['simple'];
	}

	/** 
	 * <b>返回延迟加载的属性名的数组</b>
	 * 
	 * @return array
	 */	
	public function propertiesByLazyLoad(){
		return $this->config['lazy'];
	}
	
	/** 
	 * <b>返回引用方式保存，但并不会延迟加载的属性名的数组</b>
	 * 
	 * @return array
	 */	
	public function propertiesByReference(){
		return $this->config['reference'];
	}
	
	/** 
	 * <b>返回值方式保存的属性名的数组</b>
	 * 
	 * @return array
	 */
	public function propertiesByValue(){
		return $this->config['value'];
	}
}
?>
