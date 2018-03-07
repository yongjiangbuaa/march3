<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);

//$content = file_get_contents('/data/htdocs/update_xml/last_update.txt');

$subact = $_REQUEST['subact'];

if($subact == 'svnget'){
	$result='';
	$type = $_REQUEST['type'];

	if(file_exists('/data/htdocs/update_xml/front_update.lock')){

		$result = "正在使用中......==>开始时间$content";
		$type = 0;
	}
	if($_REQUEST['shownum']){
		$num = $_REQUEST['shownum'];
	}else{
		$num = 20;
	}
	unset($items);
	if($type == 1){ //拉取svn
//		/data/htdocs/update_xml/update.lock
		$cmd = "sh /data/htdocs/update_xml/front_update_xml.sh";
		$re = system($cmd, $retval);

		$ifok = false;
		if($re === false || $retval == 1){
			$result=  "拉取失败";
		}else{
			if($re == 'done') {
				$result = '拉取成功';
				$ifok = true;
			}else if($re == 'using'){
				$result = '正在使用中';
			}else{
				$result = 'error';
			}

		}
		if($ifok) {
			$items = get_filenamesbydir("/data/htdocs/ifadmin/admin/xml/front_xml/", $num);
		}
	}
}
function get_allfiles($path,&$files,$num) {
//	if(is_dir($path)){
//		$dp = dir($path);
//		while ($file = $dp ->read()){
//			if($file !="." && $file !=".."){
//				get_allfiles($path."/".$file, $files);
//			}
//		}
//		$dp ->close();
//	}
//	if(is_file($path)){
//		$files[] =  $path;
//	}
//	$items = get_filenamesbydir("/data/htdocs/resource/",$num);

	$cmd = 'ls -t1 '.$path.' | '.'head -'.$num;
	$re= exec($cmd,$array);

//	ls -t1 /data/htdocs/resource | head -10

	if (is_dir($path)) {
		if ($dh = opendir($path)) {
			$i = 0;
			foreach($array as $file){
				$files[$i]["name"] = $file;//获取文件名称
//					$files[$i]["size"] = round((filesize($file)/1024),2);//获取文件大小
				$tmp = $path.$file;
				$files[$i]["time"] = date("Y-m-d H:i:s",filemtime("$tmp"));//获取文件最近修改日期
//				$files[$i]["time"] = filemtime("$tmp");//获取文件最近修改日期
				$i++;

			}
//			while (($file = readdir($dh)) !== false) {
//				if ($file != "." && $file != ".." && $file != '.svn' && $file != 'ReadLog.java') {
//					$files[$i]["name"] = $file;//获取文件名称
////					$files[$i]["size"] = round((filesize($file)/1024),2);//获取文件大小
////					$files[$i]["time"] = date("Y-m-d H:i:s",filemtime($file));//获取文件最近修改日期
//					$files[$i]["time"] = filemtime($file);//获取文件最近修改日期
//					$i++;
//				}
//			}
		}
		closedir($dh);
		foreach($files as $k=>$v){
//			$size[$k] = $v['size'];
			$time[$k] = $v['time'];
			$name[$k] = $v['name'];
		}
		array_multisort($time,SORT_DESC,SORT_STRING, $files);//按时间排序
		//array_multisort($name,SORT_DESC,SORT_STRING, $files);//按名字排序
		//array_multisort($size,SORT_DESC,SORT_NUMERIC, $files);//按大小排序
//		print_r($files);
		$files = array_slice($files,0,$num);
	}
}

function get_filenamesbydir($dir,$num){
	$files =  array();
	get_allfiles($dir,$files,$num);
	return $files;
}

include( renderTemplate("{$module}/{$module}_{$action}") );

