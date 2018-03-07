<?php
function xingcloud_mysql_info(){
	$status = array(
		'status' => -1,
	);
	$memcache = null;
	$results = array();
	try{
		foreach(array(
			'mysql_host',
			'mysql_port',
			'mysql_user',
			'mysql_db') as $var){
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
		$results['mysql_passwd'] = xingcloud_get('mysql_passwd');
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
	$connection = null;
	$basetime = microtime(true);
	$timestamp = 0;
	try{
		$connection = mysql_connect($results['mysql_host'].':'.$results['mysql_port'], $results['mysql_user'], $results['mysql_passwd']);
		if(is_resource($connection)){
			mysql_select_db($results['mysql_db'], $connection);
		}
		$timestamp = 1000 * (microtime(true) - $basetime);
		if(($error = mysql_error()) != ''){
			$status['status'] = 1;
			$status['connection'] = array(
				'status' => 1,
				'error'  => $error,
				'timestamp' => $timestamp,
			);
			if(is_resource($connection)){
				mysql_close($connection);
			}
			return $status;
		}else{		
			$status['connection'] = array(
				'status' => 0,
				'error'  => '',
				'timestamp' => $timestamp,
			);
		}
	}catch(Exception $e){
		$status['status'] = 1;
		$status['connection'] = array(
			'status' => 1,
			'error'  => $e->getMessage(),
			'timestamp' => $timestamp,
		);
		if(is_resource($connection)){
			mysql_close($connection);
		}
		return $status;
	}
	$status['status'] = 0;
	if(is_resource($connection)){
		mysql_close($connection);
	}
	return $status;
}
?>