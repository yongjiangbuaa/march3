<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$langs = array(
		'ar',
		'de',
		'en',
		'es',
		'fr',
		'id',
		'it',
		'ja',
		'ko',
		'nl',
		'pl',
		'pt',
		'ru',
		'th',
		'tr',
		'zh_CN',
		'zh_TW',
);

// http://cok.eleximg.com/cok/config/1.0.86/config_1.0.1480_zh_TW.zip
$cdnbase = 'http://cok.eleximg.com/cok/config';
$cdnUploadUrl = 'http://cdn1.cok.eleximg.com/cdnupload/cok/config';
$svnpath = 'http://svn.xinggeq.com/svn/if/branches/production/docs/locale';
$client_redis_s1 = new Redis();
$s1ip = $servers['s1']['ip_inner'];
$client_redis_s1->connect($s1ip);
$client_redis_s100 = new Redis();
$s100ip = $servers['s100']['ip_inner'];
$client_redis_s100->connect($s100ip);

$subact = $_REQUEST['subact'];
$appver = $_REQUEST['appver'];
$oldconfigver = $_REQUEST['oldconfigver'];

$sql = "select * from cokdb_admin_deploy.tbl_configver";
$ret=$page->globalExecute($sql, 3);
$records = $ret['ret']['data'];

$appVers = array();
foreach ($records as $row) {
	$row['lastDate'] = str_replace(' ', '<br>', $row['lastDate']);
	$onlinever = $client_redis_s1->get("property1_realtime_app_".$row['appVer']);
	if ($onlinever) {
		$ver_tokens = explode('|', $onlinever);
		$row['status'] = $ver_tokens[0];
		$row['configVer'] = $ver_tokens[1];
	}
	$onlinever = $client_redis_s100->get("property100_realtime_app_".$row['appVer']);
	if ($onlinever) {
		$ver_tokens = explode('|', $onlinever);
		$row['configVerS100'] = $ver_tokens[1];
	}
	if ($row['status'] != 0) {
		continue;
	}
	$appVers[$row['appVer']] = $row;
}
krsort($appVers);

$filelinks = array();
foreach ($appVers as $av => $value) {
	foreach ($langs as $lang) {
		$filelinks[$av][$lang] = "$svnpath/$av/text_$lang.ini";
	}
	$filelinks[$av]['database'] = "$svnpath/$av/database.local.xml";
	$filelinks[$av]['VERSION'] = "$svnpath/$av/VERSION.txt";
}

if($subact == 'modify'){
	if (empty($_REQUEST['newconfigver'])) {// || $_REQUEST['oldconfigver'] == $_REQUEST['newconfigver']
		echo '输入有误：版本没有变化。';
		exit();
	}
	
	//调用107发布机的API，以更新config.propeties的状态
	$configVer = $_REQUEST['newconfigver'];
	$onlinestatus = 0;
	$params = array();
	$params['appVer'] = $appver;
	$params['clientVer'] = $configVer;
	$params['targetservers'] = $_REQUEST['targetservers'];
	$params['status'] = $onlinestatus;
	$qs = http_build_query($params);
	
	$display = explode('&', $qs);
	$display[] = '';
	
	$runstatus = file_get_contents('http://IPIPIP:8081/api/update_config_client_version.php?'.$qs);
	if ($runstatus=='OK') {
		$display[] = '更新成功。';
	}else{
		$display[] = 'ERROR';
	}
	echo implode("<br>", $display);
}
if($subact == 'putoffline'){
	if (empty($_REQUEST['newconfigver'])) {
		echo '输入有误：版本没有变化。';
		exit();
	}

	//调用107发布机的API，以更新config.propeties的状态
	$configVer = $_REQUEST['newconfigver'];
	$onlinestatus = $_REQUEST['status'];
	if (!in_array($onlinestatus, array(0,1,2))) {
		echo '输入有误：status。';
		exit();
	}
	$params = array();
	$params['appVer'] = $appver;
	$params['clientVer'] = $configVer;
	$params['onlinestatus'] = $onlinestatus;
	$qs = http_build_query($params);

	$display = explode('&', $qs);
	$display[] = '';

	$runstatus = file_get_contents('http://IPIPIP:8081/api/update_config_client_version.php?'.$qs);
	if ($runstatus=='OK') {
		$display[] = '更新成功。';
		
		// update tbl. set app status.
		if ($onlinestatus == 0) {
			$now = '';
		}else{
			$now = date('Y-m-d H:i:s');
		}
		$sql = "update cokdb_admin_deploy.tbl_configver set status=$onlinestatus,offlineDate='$now' where appVer='$appver'";
		$page->globalExecute($sql, 2);
	}else{
		$display[] = 'ERROR';
	}
	echo implode("<br>", $display);
}
// audit operation.
if ($subact == 'modify' || $subact == 'putoffline') {
	$now = date('Y-m-d H:i:s');
	$author = $GLOBALS['adminid'];
	$oplogsql = "insert into tbl_configver_oplog(date,author,act,appVer,configVer,status,msg) values ('$now', '$author', '$subact', '$appver', '$configVer', $onlinestatus, '$runstatus')";
	$page->globalExecute($oplogsql, 2);
	exit();
}

GOTO_MARK_PAGEEND_renderTemplate:
include( renderTemplate("{$module}/{$module}_{$action}") );

////////////////////

function zip_file($filename, $files){
	$zip=new ZipArchive;
	if($zip->open($filename,ZipArchive::OVERWRITE)===TRUE){
		foreach ($files as $file) {
			$zip->addFile($file, basename($file));
		}
		$zip->close();
		return true;
	}
	return false;
}

function remote_file_exists($url) {
// 	$executeTime = ini_get('max_execution_time');
// 	ini_set('max_execution_time', 0);
	$headers = @get_headers($url);
// 	ini_set('max_execution_time', $executeTime);
	if ($headers) {
		$head = explode(' ', $headers[0]);
		if ( !empty($head[1]) && intval($head[1]) < 400) return true;
	}
	return false;
}


function download_file_via_curl($url, $tofile){
	$fp = fopen ($tofile, 'w+');
	$ch = curl_init(str_replace(" ","%20",$url));
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch); // get curl response
	curl_close($ch);
	$errno = curl_errno ( $ch );
	$error = curl_error ( $ch );
	fclose($fp);
	return "$errno-$error";
}

function upload_file_via_curl($remoteUrl, $file, $tofile){
	$filesize = filesize($file);
	$fh = fopen($file, 'r');
	
	$ch = curl_init($remoteUrl . '/' . $tofile);
	
	curl_setopt($ch, CURLOPT_PUT, true);
	curl_setopt($ch, CURLOPT_INFILE, $fh);
	curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
	$result = curl_exec($ch);
	$errno = curl_errno ( $ch );
	$error = curl_error ( $ch );
	fclose($fh);
	return "$errno-$error";
}

function download_file_via_shell_ftp(){

	// download config from cdn and zip one big file -> send to client.
	$shcmds=array();
	$shcmds[] = 'echo "';
	$shcmds[] = 'open s1.eleximg.com';
	$shcmds[] = 'prompt';
	$shcmds[] = 'user cok oAFnvlNDjSxC_W3';
	$shcmds[] = 'cd /config';
	$shcmds[] = 'cd '.$ftp_appver;
	$shcmds[] = 'lcd '.$dir_updown;
	$shcmds[] = 'binary';
	$shcmds[] = 'mget config_'.$oldconfigver.'_*.zip';
	$shcmds[] = 'close';
	$shcmds[] = 'bye';
	$shcmds[] = '"|ftp -v -n';
	$shcmds[] = '';
	
	$zipfiles = array();
	file_put_contents("$dir_updown/download_config_from_cdn.sh", implode("\n", $shcmds));
	$cmd = "sh $dir_updown/download_config_from_cdn.sh";
	$ret = @exec ( $cmd, $out, $status );
	if ($status === 0) {
		foreach ($out as $line) {
			if (strpos($line, 'local:') !== false) {
				$tmp = explode(' ', $line);
				$zipfiles[] = $dir_updown.'/'.$tmp[1];
			}
		}
	}else{
		echo 'ERROR. download failed.';
		return ;
	}
	
}

function upload_file_via_shell_ftp(){
	$shcmds=array();
	$shcmds[] = 'echo "';
	$shcmds[] = 'open s1.eleximg.com';
	$shcmds[] = 'prompt';
	$shcmds[] = 'user cok oAFnvlNDjSxC_W3';
	$shcmds[] = 'cd /config';
	$shcmds[] = 'cd '.$ftp_appver;
	if (is_dir($dir_updown .'/'. $newzipname)) {
		$shcmds[] = 'lcd '.$dir_updown .'/'. $newzipname;
	}else{
		$shcmds[] = 'lcd '.$dir_updown;
	}
	$shcmds[] = 'binary';
	$shcmds[] = 'mput config_'.$newconfigver.'_*.zip';
	$shcmds[] = 'close';
	$shcmds[] = 'bye';
	$shcmds[] = '"|ftp -v -n';
	$shcmds[] = '';
	
	file_put_contents("$dir_updown/upload_config_to_cdn.sh", implode("\n", $shcmds));
	$cmd = "sh $dir_updown/upload_config_to_cdn.sh";
	$ret = @exec ( $cmd, $out, $status );
	if ($status === 0) {
		$opmessage = "Upload successfully.<br>";
	}else{
		$opmessage = "ERROR.<br>";
	}
	$display = array();
	$m = '';
	$langfilearr = array();
	foreach ($out as $line) {
		if (strpos($line, 'local:') !== false) {
			$display[] = $m;
			$tmp = explode(' ', $line);
			$langfile = $tmp[1];
			$langfilearr[$ftp_appver][] = $langfile;
			$m = $langfile;
			$i = 1;
		}elseif ($i > 0){
			$i++;
		}
	
		if ($i == 4) {
			if('226 File receive OK.' == $line){
				$m .= ' -- OK';
			}else{
				$m .= ' -- ERROR';
			}
		}
	
		if ('221 Goodbye.' == $line) {
			$display[] = $m;
		}
	}
}

