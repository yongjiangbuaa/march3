<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);

$content = file_get_contents('/data/htdocs/update_xml/last_update.txt');

$subact = $_REQUEST['subact'];
$run_status = array(
		0=>'',
		1=>'发布中',
		2=>'发布失败',
		3=>'发布成功',
		4=>'已取消',
);

if($subact == 'svnget'){
	$result='';
	$type = $_REQUEST['type'];

	if(file_exists('/data/htdocs/update_xml/update.lock')){
		$time = file_get_contents('/data/htdocs/update_xml/update.lock');
		$now = time();
		if($now - $time > 5 * 60){//上次没有拉取成功
			$cmd = "/data/htdocs/update_xml/clean_status.sh";
			system($cmd);
		}else{
			$formatTime = date('Y-m-d H:i:s', $time);
			$result = "正在使用中......==>开始时间$formatTime";
			$type = 0;
		}
	}
	if($_REQUEST['shownum']){
		$num = $_REQUEST['shownum'];
	}else{
		$num = 20;
	}
	unset($items);
	if($type == 1){ //拉取svn
//		/data/htdocs/update_xml/update.lock
		$cmd = "sh /data/htdocs/update_xml/update_xml.sh 2>&1";
		$re = system($cmd, $retval);
		file_put_contents('/tmp/loginhis.log', "re=$re\n",FILE_APPEND);
		$ifok = false;
		if($re === false || $retval == 1){
			$result=  "拉取失败";
			file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
		}else{
			if($re == 'done') {
				$result = '拉取成功';
				$ifok = true;
				file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
			}else if($re == 'using'){
				$result = '正在使用中';
				file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
			}else{
				$result = 'error';
				file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
			}

		}
		if($ifok) {
			$items = get_filenamesbydir("/data/htdocs/resource/", $num);
		}

	}elseif($type == 2){//更新全部
		$cmd = "sh /data/htdocs/update_xml/update_sfs.sh all 2>&1";
		$re = system($cmd, $retval);

		if($re === false || $retval == 1){
			$result=  "部署失败".$retval;
			file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
		}else{
			$result = '部署成功';
			file_put_contents('/tmp/loginhis.log', "result=$result\n",FILE_APPEND);
		}
		file_put_contents('/tmp/loginhis.log', 'owner='.get_current_user()."\n",FILE_APPEND);
		system("echo 'putFrom php' >> /tmp/loginhis.log 2>&1");
	}elseif($type == 3){
//		1-3;4-7;8|servers.xml;b.xml
//		servers.xml;b.xml  全服
		$content = trim($_REQUEST['contents']);
		$content = str_replace(' ', '', $content);
		$cmd = "sh /data/htdocs/update_xml/update_sfs.sh some \"$content\" ";
		$re = system($cmd, $retval);
		if($re === false || $retval == 1){
			$result=  "部署失败".$retval;
		}else{
			$result = '部署成功';
		}
	}
	if($type && $cmd && $result) {
		$detail = 'type=' . $type . ' -- ' . $cmd . " -- " . $result . ' -- ';
		adminLogSystem($adminid, $detail);
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


if($subact == 'addplan'){
	$files = implode('|', $_REQUEST['resxmls']);
	$servers = $_REQUEST['selectServer'];
	$delaymin = intval($_REQUEST['delaymin']);
	$plan_time = time() + $delaymin * 60;

	if (empty($files)) {
		$error_msg = "请选择需要发布的xml文件。";
//		goto GOTO_MARK_PAGEEND_view;
	}

	$one = array();
	$one['author'] = $adminid;
	$one['files'] = $files;
	$one['servers'] = $servers;
	$one['plan_time'] = $plan_time;
	$one['run_result'] = '';
	$one['status'] = '0';
	$one['create_time'] = time();

	$fields = implode(',', array_keys($one));
	$vals = implode("','", $one);
	$vals = "'$vals'";
	$sql = "insert into cokdb_admin_deploy.tbl_xml_publish ($fields) values ($vals)";
	$ret=$page->globalExecute($sql, 2);
}

if($subact == 'cancelplan'){
	$planid = $_REQUEST['planid'];
	$sql = "update cokdb_admin_deploy.tbl_xml_publish set status=4 where id=$planid";
	$ret=$page->globalExecute($sql, 2);
}

//GOTO_MARK_PAGEEND_view:
//
//$sql = "select * from cokdb_admin_deploy.tbl_xml_publish order by plan_time desc limit 10";
//$ret=$page->globalExecute($sql, 3);
//$xmlplanlist = $ret['ret']['data'];
//foreach ($xmlplanlist as &$row) {
//	$row['plan_time'] = date('Y-m-d H:i:s', $row['plan_time']);
//	$row['servers'] = $row['servers']?$row['servers']:'全服';
//	$row['run_time'] = $row['run_time']?date('Y-m-d H:i:s', $row['run_time']):"《未执行》";
//	$row['run_result'] = $row['run_result']?$row['run_result']:$run_status[$row['status']];
//	$row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
//}
//
//$sql = "select tx.file, tx.date, tx.time, tx.author, tx.msg
//		from cokdb_admin_deploy.tbl_xml_svnlog tx inner join
//		(select file,max(time) maxtime from cokdb_admin_deploy.tbl_xml_svnlog group by file) tx_tmp
//		on tx.file=tx_tmp.file and tx.time=tx_tmp.maxtime order by time desc limit 10;";
//$ret=$page->globalExecute($sql, 3);
//$recentxmllist = $ret['ret']['data'];
//
//GOTO_MARK_PAGEEND_renderTemplate:
include( renderTemplate("{$module}/{$module}_{$action}") );

