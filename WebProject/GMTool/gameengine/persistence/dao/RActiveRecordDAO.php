<?php
import('persistence.dao.XDAO');
/**
 * XDAO
 * 
 * active record dao (Data Access Object) class
 * 
 * active record风格的数据访问接口实现类
 * 
 * 针对ik2中的mysql关系存储访问，而非key-value
 * 
 * @author rome
 * @final
 * @package dao 
 */
class RActiveRecordDAO implements XDAO{
	//容器
	private static $daos = array();
	private $table;
	private $columns = array();
	protected $modelName;
	protected $primaryKey;
	
	private function __construct($modelName){
		$this->modelName = $modelName;
		//读取配置
		$configs = x_apc_file_fetch('dao_'.$modelName);
		if(is_array($configs)){
			foreach ($configs as $key => $config){
				$this->{$key} = $config;
				self::$daos[$this->modelName] = $this;
			}
			if($this->columns)
				return;
		}
		$configs = array();
		//表名
		$this->table = strtolower($this->modelName);
		if(!in_array($this->table, array('userprofile','tutorialstep','tutorial','platformprofile')))
			$this->table = strtolower(substr($this->modelName, 0, -4));
		$configs['table'] = $this->table;
		//字段名
		import('util.mysql.XMysql');
		$mysql = XMysql::singleton()->connect();
		$fields = $mysql->describeTable($this->getTable());
		$this->columns = array_keys($fields);
		$configs['columns'] = $this->columns;
		foreach ($fields as $field) {
			if ($field["primary_key"]) {
				$this->primaryKey = $field["name"];
				$configs['primaryKey'] = $this->primaryKey;
				break;
			}
		}
		//加入容器中
		self::$daos[$this->modelName] = $this;
		if(x_is_prod()){
			x_apc_file_store('dao_'.$modelName, $configs);
		}
	}
	/** 
	 * <b>singleton method</b>
	 * 
	 * <b>单例方法，返回RActiveRecordDAO的单例，每个$model类只有一个RActiveRecordDAO实例</b>
	 * 
	 * @param string $model 实体类名
	 * @return RActiveRecordDAO
	 */
	public static function dao($model){
		if(is_object($model)){
			$modle = get_class($model);
		}
		if(!isset(self::$daos[$model])){
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
	 * @param boolean $enforce 更新操作时，是否强制不检查脏数据
	 * 
	 * @return mixed
	 */	
	public function save($model, $enforce = false){
		$mysql = XMysql::singleton();
		//更新数据
		if($model->isSaved()){
			//查找有更新的字段
			$ori = $model->getOriginal();
			$attrs = $model->attrs();
			$columns = array();
			foreach ($this->columns as $column) {
				if (!array_key_exists($column, $attrs)) {
					continue;
				}
				if (!array_key_exists($column, $ori) && is_null($attrs[$column])) {
					continue;
				}
				if ($enforce) {
					$columns[$column] = $attrs[$column];
				}
				else {
					if (array_key_exists($column, $ori)) {
						if ($ori[$column] != $attrs[$column]) {
	 						$columns[$column] = $attrs[$column];
						}
					}
					else {
						$columns[$column] = $attrs[$column];
					}
				}
			}
			if (!empty($columns)) {
				//pk value
				$pkValue = array($this->primaryKey => $model->get($this->primaryKey));
				$mysql->put($this->getTable(), $pkValue, $columns);
				$model->setOriginal($model->attrs());
			}
		}else{
			//插入数据
			$values = array();
			foreach ($this->columns as $column) {
				$value = $model->get($column);
				//未设置主键值，则自动填充
				if($this->primaryKey == $column && empty($value)){
					$uid = getGUID();
					$model->set($column, $uid);
					$values[$column] = $uid;
				}
				if (!is_null($value)) {
					$values[$column] = $value;
				}
			}
			if (empty($values)) {
				throw new Exception("Can't insert empty record, you need to specify attribute values");
				return $model;
			}
			$res = $mysql->add($this->getTable(), $values);
			if($res){
				$model->setSaved();
				$model->setOriginal($model->attrs());
			}
		}
		return $model;
	}
	
	/**
	 * <b>select method</b>
	 *
	 * <b>查询方法，根据主键取得一条记录</b>
	 * 
	 */
	public function getOne($model, $value){
		$mysql = XMysql::singleton();
		$pkValue = array($this->primaryKey => $value);
		$res = $mysql->get($this->getTable(), $pkValue);
		if(!$res){
			return null;
		}
		$this->create($model, $res[0]);
		return $model;
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
		$pkValue = array($this->primaryKey => $model->get($this->primaryKey));
		$mysql = XMysql::singleton();
		return $mysql->del($this->getTable(), $pkValue);
	}
	
	/** 
	 * <b>create method</b>
	 * 
	 * <b>将查询结果映射为模型实例</b>
	 * 
	 * @param string $model 实例对象
	 * 
	 * @param array $results 结果数组
	 * 
	 * @return RActiveRecord
	 */
	public function create($model, $results){
		$model->setAttrs($results);
		$model->setOriginal($model->attrs());
		$model->setSave(true);
	}
	
	public function getTable(){
		return $this->table;
	}
	
	public function getPk(){
		return $this->primaryKey;
	}
}
?>