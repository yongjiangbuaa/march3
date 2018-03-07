<?php
function xingcloud_kv_info(){
	$status = array(
		'status' => -1,
	);
	$basetime = microtime(true);
	$timestamp = 0;
	try{
		$service = KVStorageService::getService();
		$timestamp = 1000 * (microtime(true) - $basetime);
		$status['connection'] = array(
			'status' => 0,
			'error'  => '',
			'timestamp' => $timestamp,
		);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['connection'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		return $status;
	}
	$basetime = microtime(true);
	try{
		$service->set('test', 'test', 'xingcloud');
		$timestamp = 1000 * (microtime(true) - $basetime);
		$status['write'] = array(
			'status' => 0,
			'error'  => '',
			'timestamp' => $timestamp,
		);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['write'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		return $status;
	}
	$result = '';
	$basetime = microtime(true);
	try{
		$result = $service->get('test', 'test');
		$timestamp = 1000 * (microtime(true) - $basetime);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['read'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		return $status;
	}
	if(!isset($status['read'])
		&& $result != 'xingcloud'){
		$status['status'] = 1;
		$status['read'] = array(
			'status' => 1,
			'error'  => 'the values which kv service reads and writes not match',
			'timestamp' => $timestamp,
		);
		return $status;
	}
	$status['read'] = array(
		'status' => 0,
		'error'  => '',
		'timestamp' => $timestamp,
	);
	$basetime = microtime(true);
	try{
		$service->del('test', 'test');
		$timestamp = 1000 * (microtime(true) - $basetime);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['delete'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		return $status;
	}
	$status['delete'] = array(
		'status' => 0,
		'error'  => '',
		'timestamp' => $timestamp,
	);	
	$status['status'] = 0;
	return $status;
}
?>