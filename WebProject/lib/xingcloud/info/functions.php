<?php
require_once XINGCLOUD_CLOUD_DIR.'/info/kv.php'; 
require_once XINGCLOUD_CLOUD_DIR.'/info/memcache.php'; 
require_once XINGCLOUD_CLOUD_DIR.'/info/mysql.php'; 
/**
 * 输出xingcloud服务的详细信息
 */
function xingcloud_info(){
	if(php_sapi_name()=='cli'){
		xingcloud_info_cli();
		return;
	}
	if(isset($_REQUEST['format'])
		&& $_REQUEST['format']=='json'){
		xingcloud_info_json();
		return;
	}
	xingcloud_info_html();
}
function xingcloud_info_cli(){
	echo "XingCloud version ".xingcloud_version()."\n";
	$time = time();
	echo 'timestamp '.date("Y-m-d H:i:s", $time).' ('.$time.")\n";
	foreach(array(
		'kv', 
		'memcache', 
		'mysql',
		) as $service){
		$func = 'xingcloud_'.$service.'_info';
		$status = $func();
		echo "\n".$service."\n";
		if(empty($status)){
			echo "status\t\t\t\t\tOFF\n";
			continue;
		}
		echo "status\t\t\t\t\t".status_text($status['status'])."\n";
		unset($status['status']);
		if(empty($status)){
			continue;
		}
		foreach($status as $key => $value){
			echo $key."\t\t\t\t\t".status_text($value['status'])."\n";
			if($value['status'] == 1){
				echo "ERROR:\t".$value['error']."\n";
			}
		}
	}
}
function xingcloud_info_json(){
	$content = array();
	foreach(array(
		'kv', 
		'memcache', 
		'mysql',
		) as $service){
		$func = 'xingcloud_'.$service.'_info';
		$content[$service] = $func();
	}
	echo json_encode($content);
}
function xingcloud_info_html(){
	$html = file_get_contents(XINGCLOUD_CLOUD_DIR.'/info/html/head.template');
	$time = time();
	$basetime = microtime(true);
	$html = str_replace('{{$xingcloud}}', 'XingCloud version '.xingcloud_version(), $html);
	$table = file_get_contents(XINGCLOUD_CLOUD_DIR.'/info/html/table.template');
	foreach(array(
		'kv', 
		'memcache', 
		'mysql',
		) as $service){
		$content = str_replace('{{$service_name}}', $service, $table);
		$func = 'xingcloud_'.$service.'_info';
		$content = str_replace('{{$table_content}}', info_formatter($func()), $content);
		$html .= $content;
	}
	$elapsed = 1000 * (microtime(true) - $basetime);
	$html .= str_replace('{{$xingcloud}}', 'page execution time '.$elapsed.' (ms)<br>'.date("Y-m-d H:i:s", $time).' ('.$time.')', file_get_contents(XINGCLOUD_CLOUD_DIR.'/info/html/tail.template'));
	echo $html;
}
function info_formatter($status){
	if(empty($status)){
		return '<tr><td class="e">status</td><td colspan="2" class="v">OFF</td></tr>';
	}
	$html = '<tr><td class="e">status</td><td colspan="2" class="v">'.status_text($status['status']).'</td></tr>';
	unset($status['status']);
	if(empty($status)){
		return $html;
	}
	foreach($status as $key => $value){
		$html .= '<tr><td class="e">'.$key.'</td><td '.(!isset($value['timestamp'])?'colspan="2" ':'')
			.'class="v">'.status_text($value['status']).'</td>'
			.(isset($value['timestamp'])?'<td class="v">'.$value['timestamp'].' (ms)</td>':'').'</tr>';
		if($value['status'] == 1){
			$html .= '<tr><td colspan="2" class="v">ERROR: '.$value['error'].'</td></tr>';
		}
	}
	return $html;
}
function status_text($status){
	switch($status){
		case 0:
			return 'OK';
		case -1:
			return 'OFF';
		case 1:
			return 'ERROR';
	}
}
function xingcloud_version(){
	$version = '';
	$filepath = XINGCLOUD_CLOUD_DIR.'/../../sdk-description.xml';
	if(is_file($filepath)){
		$xml = simplexml_load_file($filepath);
		$xml->registerXPathNamespace('sdk-description', 'http://www.w3school.com.cn');
		$nodes = $xml->xpath('//sdk-description:version');
		foreach($nodes as $node){		
			foreach($node->attributes() as $key => $value){
				if($key == 'name'){
					$version .= $value;
					break;
				}
			}
		}
	}
	$filepath = XINGCLOUD_CLOUD_DIR.'/sdk.ini';
	if(is_file($filepath)){
		$result = parse_ini_file($filepath , true);
		if(isset($result['xingcloud'])
			&& isset($result['xingcloud']['version'])){
			$version .= ' core '.$result['xingcloud']['version'];
		}
	}
	return $version;
}
?>