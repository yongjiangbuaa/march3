<?php
function x_is_dev(){
	return __DEV__;
}
function x_is_prod(){
	return !__DEV__;
}
function x_apc_file_store($key, $value){
	if(APC_ENABLED){
		apc_store(SERVER_UID.'_'.$key, serialize($value));
		return;
	}
	if(!defined('GAME_TMP_DIR')){
		return;
	}
	$path = GAME_TMP_DIR.__DS__.'config';
	if(!is_dir($path)){
		mkdir($path);
	}
	//取得APCCache版本信息
	$pathData = $path.__DS__.md5('xingcloudAPCData');
	if(is_file($pathData)){
		$cachedData = json_decode(file_get_contents($pathData),true);
	}
	$cachedData[$key] = __DBVER__;
	file_put_contents($pathData, json_encode($cachedData));
	
	$path = $path.__DS__.md5($key);
	file_put_contents($path, serialize($value));
}
function x_apc_file_fetch($key){
	$result = null;
	if(APC_ENABLED){
		//取得APCCache版本信息
// 		$cachedData = json_decode(apc_fetch('xingcloudAPCData'),true);
// 		if (!isset($cachedData) || (is_array($cachedData) && $cachedData[$key] < __DBVER__))
// 		{
// 			$cachedData[$key] = __DBVER__;
// 			//保存APCCache版本信息
// 			apc_store('xingcloudAPCData',json_encode($cachedData));
// 			return null;
// 		}
		$result = apc_fetch(SERVER_UID.'_'.$key);
	}else{
		if(!defined('GAME_TMP_DIR')){
			return null;
		}
		//取得APCCache版本信息
		$cachedPath = GAME_TMP_DIR.__DS__.'config'.__DS__.md5('xingcloudAPCData');
		if(is_file($cachedPath)){
			$cachedData = json_decode(file_get_contents($cachedPath),true);
		}
		if (!isset($cachedData) || (is_array($cachedData) && $cachedData[$key] < __DBVER__))
			return null;

		$path = GAME_TMP_DIR.__DS__.'config'.__DS__.md5($key);
		if(is_file($path)){			
			$result = file_get_contents($path);
		}
	}
	if(is_null($result)){
		return null;
	}
	return unserialize($result);
}
function import_real_class($className, $path, $register = false){
	if(class_exists($className, FALSE)
		|| interface_exists($className, FALSE)){
		return true;
	}
	require $path;
	if($register){
		register_class($className);
	}
}
function import_plugin_class($className, $path){
	if(class_exists($className)
		|| interface_exists($className)){
		return true;
	}
	require $path;
}
function register_class($class){
	XingCloudApp::singleton()->register($class);
}
function import($class, $register = false){
	static $imports = array();
	if(isset($imports[$class])){	
		return true;
	}
	$className = '';
	$pos = strripos($class, '.');
	if($pos === false){		
		$className = $class;
	}else{
		$className = substr($class, $pos + 1);
	}
	$head = $class{0};
	$middle = $class{6};
	if($head == 'm' 
		&& $middle == '.'
		&& substr($class, 0, 7) == 'module.'){
			if(defined('XINGCLOUD_FRAMEWORK_DIR')){
				import_real_class($className, XINGCLOUD_FRAMEWORK_DIR.__DS__.strtr($class, '.', __DS__).'.php', $register);
			}
	}elseif($head == 'p' 
		&& $middle == '.'
		&& substr($class, 0, 7) == 'plugin.'){
			if(defined('XINGCLOUD_FRAMEWORK_DIR')){
				import_plugin_class($className, XINGCLOUD_FRAMEWORK_DIR.__DS__.strtr($class, '.', __DS__).'.php');
			}
	}elseif($head == 's' 
		&& $middle == 'e'
		&& substr($class, 0, 8) == 'service.'){
			if(defined('XINGCLOUD_FRAMEWORK_DIR')){
				$file = XINGCLOUD_FRAMEWORK_DIR.__DS__.strtr($class, '.', __DS__).'.php';
				if(is_file($file)){
					import_real_class($className, $file, $register);
				}else{
					import_real_class($className, GAME_DIR.__DS__.strtr($class, '.', __DS__).'.php', $register);
				}
			}
	}elseif(($head == 'd' 
		&& $middle == '.'
		&& substr($class, 0, 7) == 'domain.')){
			if(defined('GAME_SERVICE_DIR')){
				import_real_class($className, GAME_SERVICE_DIR.__DS__.strtr(substr($class, 7), '.', __DS__).'.php', $register);
			}
	}else{
		$realPath = __DS__.strtr($class, '.', __DS__).'.php';
		if(defined('XINGCLOUD_SDK_DIR') && file_exists(XINGCLOUD_SDK_DIR.$realPath)){
			import_real_class($className, XINGCLOUD_SDK_DIR.$realPath, $register);
		}elseif(defined('XINGCLOUD_GAMEENGINE_DIR') && file_exists(XINGCLOUD_GAMEENGINE_DIR.$realPath)){
			import_real_class($className, XINGCLOUD_GAMEENGINE_DIR.$realPath, $register);
		}else{
			trigger_error('no path found for '.$class, E_USER_ERROR);
		}
	}
	$imports[$class] = $class;
}
/*
 * @deprecated
 * */
function import_class($class, $register = false){
	import($class, $register);
}
function import_all(){
	if(defined('XINGCLOUD_MODULE_DIR')){
		import_dir(XINGCLOUD_MODULE_DIR);
	}
	if(defined('XINGCLOUD_SERVICE_DIR')){
		import_dir(XINGCLOUD_SERVICE_DIR);
	}
	if(defined('GAME_DIR')){
		import_dir(GAME_DIR);
	}
}
function import_dir($dir){
	
	$handler = @opendir($dir);
	$files = array();
	while($file = @readdir($handler)){
		if($file{0} == '.'){
			continue;
		}
		$file = $dir.'/'.$file;
		if(is_file($file) && preg_match('/.php$/i', $file)){
			$xfile = new XFile($file);
			$file_name = $xfile->getName();
			$class_name = preg_replace('/\.php$/i', '', $file_name);
			if(preg_match('/.tpl$/i', $class_name)){
				continue;
			}
			if(class_exists($class_name, FALSE) || interface_exists($class_name, FALSE)){
				continue;
			}
			require $file;
		}else if(is_dir($file)){
			import_dir($file);
		}		
	}
}
function x_array_get(&$array, $key){
	if(!is_array($array) || !$key){
		return null;
	}
	if(isset($array[$key])){
		return $array[$key];
	}
	$splits = explode('.', $key);
	if(!is_array($splits)
		|| empty($splits)){
		return null;
	}
	$result = $array;
	foreach($splits as $split){
		if(!isset($result[$split])){
			return null;
		}
		$result = $result[$split];
	}
	return $result;
}
function x_array_fast_get(){
	$num = func_num_args();
	if($num < 2){
		return null;
	}
	$args = func_get_args();	
	$array = $args[0];
	if(empty($array)){
		return null;
	}
	for($i=1;$i<$num;$i++){
		if(!isset($array[$args[$i]])){
			return null;
		}
		$array = $array[$args[$i]];
	}
	return $array;
}
function x_isset(&$array, $key, $split = false){
	if(!$split || ($pos = strpos($key, '.')) === false){
		return isset($array[$key]);
	}
	$head = substr($key, 0 , $pos);
	$tail = substr($key, $pos+1);
	return isset($array[$head]) && x_isset($array[$head], $tail, true);	
}
function x_array_set(&$array, $key, $value, $split = false){
	if(!is_array($array) 
		|| !$key 
		|| is_null($value)){
		return $array;
	}
	if(!$split || ($pos = strpos($key, '.')) === false){
		return x_array_plain_set($array, $key, $value);
	}
	$head = substr($key, 0 , $pos);
	$tail = substr($key, $pos+1);
	if(!isset($array[$head])){
		$array[$head] = array();
	}
	return x_array_set($array[$head], $tail, $value, true);
}
function x_array_plain_set(&$array, $key, $value){
	if(!isset($array[$key])){			
		$array[$key] = $value;		
		return $array;
	}
	if($array[$key] == $value){
		return $array;
	}
	if(!is_array($array[$key])
		&& !is_array($value)){
		$array[$key] = array($array[$key], $value);
	}elseif(!is_array($array[$key])){
		$array[$key] = array_merge(array($array[$key]), $value);					
	}elseif(!is_array($value)){
		$array[$key] = array_merge($array[$key], array($value));	
	}else{
		foreach($value as $newKey => $newValue){				
			$array[$key] = x_array_set($array[$key], $newKey, $newValue, $split);
		}
	}
	return $array;	
}
function getIP(){
	$ip = "255.255.255.255";
	$unknown = 'unknown';
	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	&& $_SERVER['HTTP_X_FORWARDED_FOR']
	&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
			$unknown) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif ( isset($_SERVER['REMOTE_ADDR'])
			&& $_SERVER['REMOTE_ADDR'] &&
			strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip = str_replace(",",".", $ip);
	$ip = trim($ip);
	return $ip;
}
function getGUID() {
	$ip = "127001";
	$unknown = 'unknown';
	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	&& $_SERVER['HTTP_X_FORWARDED_FOR']
	&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
	$unknown) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} 
	elseif ( isset($_SERVER['REMOTE_ADDR'])
	&& $_SERVER['REMOTE_ADDR'] &&
	strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip = str_replace(".","", $ip);
	$ip = str_replace(",","", $ip);
	$ip = trim($ip);
	return uniqid($ip.'IK2');
}

//xml cn中{0}，{1}...替换
function xml_replace($content, $replace = array())
{
	foreach($replace as $key => $value)
		$content = str_replace("{" . $key . "}", $value, $content);

	return $content;
}
?>