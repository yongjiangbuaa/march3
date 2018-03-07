<?php
function xingcloud_memcache_info(){
	$status = array(
		'status' => -1,
	);
	$memcache = null;
	$results = array();
	try{
		foreach(array(
			'memcache_host',
			'memcache_port',
			) as $var){
			$results[$var] = xingcloud_get($var);
			if(empty($results[$var])){
				$status['status'] = 1;
				$status['configuration'] = array(
					'status' => 1,
					'error'  => 'configuration '.$var.' is null',
				);
				return $stauts;
			}
		}
		$status['configuration'] = array(
			'status' => 0,
			'error'  => '',
		);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['configuration'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
		);
		return $status;
	}
	$basetime = microtime(true);
	$timestamp = 0;
	try{
		$memcache = new Memcache();
		$connection = $memcache->connect($results['memcache_host'], $results['memcache_port']);
		$timestamp = 1000 * (microtime(true) - $basetime);
		if($connection){
			$status['connection'] = array(
				'status' => 0,
				'error'  => '',
				'timestamp' => $timestamp,
			);
		}else{
			$status['connection'] = array(
				'status' => 1,
				'error'  => 'connection failed!',
				'timestamp' => $timestamp,
			);
			return $status;
		}
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
		$memcache->set('test', 'xingcloud');
		$timestamp = 1000 * (microtime(true) - $basetime);
	}catch(Exception $e){
		$status['status'] = 1;
		$status['write'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		return $status;
	}
	$status['write'] = array(
		'status' => 0,
		'error'  => '',
		'timestamp' => $timestamp,
	);
	$result = '';
	$basetime = microtime(true);
	try{
		$result = $memcache->get('test');
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
		$memcache->delete('test');
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
	$status['status'] = 0;
	$status['delete'] = array(
		'status' => 0,
		'error'  => '',
		'timestamp' => $timestamp,
	);	
	if(!is_null($memcache)){
		$memcache->close();
	}
	return $status;
}
?>