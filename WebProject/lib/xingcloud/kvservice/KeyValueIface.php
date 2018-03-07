<?php
//The key-value DB interface
interface KeyValueDBIf 
{
	public function connect();
	public function close();
	public function putEntity($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType);
	public function putEntityList($table, $key, $value, $DB, $indexAttr, $indexValue, $indexType);
	public function getEntity($table, $key, $DB);
	public function getEntityList($table, $key, $DB);
	public function queryEntityList($table, $conditon, $DB);
	public function delEntity($table, $key, $indexAttr, $DB);
	public function countEntities($table, $indexAttr, $DB);
}

?>