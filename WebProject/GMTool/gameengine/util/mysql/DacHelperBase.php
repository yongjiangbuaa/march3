<?php

abstract class DacHelperBase { 
	protected $hostConfig  = null;
	protected $tableConfig = null;
	
	function __construct(){
	}

	abstract protected function add($table, $addValue);
	
	abstract protected function addBatch($table, array $addValue);
	
	abstract protected function del($table, $where);
	
	abstract protected function put($table, $where, $updateValue);
	
	abstract protected function get($table, $where, $fields=null, $limit=1);
 
}

?>