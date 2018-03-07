<?php
function x_array_head($array){
	if(!is_array($array) || count($array) == 0){
		return null;
	}
	$result = array_slice($array, 0, 1);
	return array_shift($result);
}
function x_array_pick($array, $key){
	if(!is_array($array) || count($array) == 0){
		return array();
	}
	$results = array();
	foreach($array as $value){
		if(is_array($value)
			&& isset($value[$key])){			
			$results[] = $value[$key];
			continue;
		}
		if(is_object($value)){			
			$results[] = $value->$key;
		}
	}
	return $results;
}
function x_array_tail($array){
	if(!is_array($array) || count($array) == 0){
		return null;
	}
	$result = array_slice($array, count($array)-1, 1);
	return array_pop($result);
}
function x_class_name($name){
	return x_name_to_java(preg_replace("/\.php$/i", "", $name));
}
function x_name_to_c($name){	
	$str = preg_replace("/^([A-Z]+)([A-Z])/e", "strtolower('\\1').'_'.strtolower('\\2')", $name);
	$str = preg_replace("/^([A-Z])/e", "strtolower('\\1')", $str);
	$str = preg_replace("/([A-Z]+)$/e", "'_'.strtolower('\\1')", $str);
	$str = preg_replace("/([A-Z])/e", "'_'.strtolower('\\1')", $str);
	return strtolower($str);
}
function x_name_to_java($name){
	return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
}
function x_require($file){
	if(is_file($file)){
		require($file);
	}
}
function x_get_uri(){
	if(isset($_SERVER["PHP_SELF"])
                && !preg_match('/^.*\/index\.php/i', $_SERVER["PHP_SELF"])){
		return $_SERVER["REQUEST_URI"];
	}elseif(isset($_GET['uri'])){
		return $_GET['uri'];
	}else{
		return $_SERVER["REQUEST_URI"];
	}
}
function p($output){
	echo "<xmp>".var_export($output, true)."</xmp>";
}
function x_is_in($result, $container){
	if(is_array($container)){
		return in_array($result, $container);
	}
	return $result == $container;
}

function x_array_equals($array0, $array1){
	if(!is_array($array0)
		|| !is_array($array1)
		|| count($array0) != count($array1)){
		return false;
	}
	$keys0 = array_keys($array0);
	sort($keys0);
	$keys1 = array_keys($array1);
	sort($keys1);
	foreach($keys0 as $key){
		if($key != array_shift($keys1)){
			return false;
		}
	}
	foreach($array0 as $key => $value){
		if(!x_equals($value, $array1[$key])){
			return false;
		}
	}
	return true;
}
function x_equals($mixed0, $mixed1){
	if(is_object($mixed0)){
		if(!is_object($mixed1)
			|| get_class($mixed0) != get_class($mixed1)){
			return false;
		}
		if(method_exists($mixed0, 'equals')
			&& !$mixed0->equals($mixed1)){
			return false;
		}
	}elseif(is_array($mixed0)){
		if(!x_array_equals($mixed0, $mixed1)){
			return false;
		}
	}elseif($mixed0 !== $mixed1){
		return false;
	}
	return true;
}
function x_clone($source){
	if(is_object($source)){
		if(method_exists($source, 'cloneTo')){
			return $source->cloneTo();
		}
		return clone $source;
	}elseif(!is_array($source)){	
		return $source;
	}
	$clone = array();
	foreach($source as $key => $value){
		$clone[$key] = x_clone($value);
	}
	return $clone;
}
function x_array_sort_recursive(&$array, $sort){
	if(!is_array($array)){
		return $array;
	}
	foreach($array as $key => $value){
		if(is_array($value)){
			x_array_sort_recursive($array[$key], $sort);
		}
	}
	$sort($array);
}
function __autoload($classname){
	static $classes = array();
	if(empty($classes)){
		$classes = include(XINGCLOUD_FRAMEWORK_DIR.__DS__."class.php");
		$gameClasses = array();
		if(is_file(GAME_DIR.__DS__."class.php")){
			$gameClasses = include(GAME_DIR.__DS__."class.php");
		}
		$classes = array_merge(is_array($classes) ? $classes : array(), is_array($gameClasses) ? $gameClasses : array());
	}
	if(isset($classes[$classname])){
		import($classes[$classname]);
	}
}
?>