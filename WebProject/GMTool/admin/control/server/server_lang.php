<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);

// http://cok.eleximg.com/cok/config/1.0.86/config_1.0.1480_zh_TW.zip
$cdnbase = 'http://cok.eleximg.com/cok/config';
$cdnUploadUrl = 'http://cdn1.cok.eleximg.com/cdnupload/cok/config';
$subact = $_REQUEST['subact'];
$appver = $_REQUEST['appver'];
$oldconfigver = $_REQUEST['oldconfigver'];
$dir_updown = ADMIN_ROOT. '/cache/updown';
if (!file_exists($dir_updown)) {
	mkdir($dir_updown, 0777, true);
}

$langs = array(
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

// get config client version info AT 102.
$filename = '/usr/local/cok/SFS2X/extensions/COK1/config.properties';
$config_arr = parse_ini_file($filename,false,INI_SCANNER_RAW);

$notifyfile = $dir_updown.'/notify_verup.txt';
$notify_arr = parse_ini_file($notifyfile,false,INI_SCANNER_RAW);

$currVer = '';
$appVers = array();
$newappVers = array();
foreach ($config_arr as $key => $value) {
	if (strpos($key, 'realtime_app_') === false){
		continue;
	}

	if ('realtime_app_version' == $key) {
		$currVer = $value;
		continue;
	}

	$ver_tokens = explode('|', $value);
	$ain = substr($key, 13);
	$appVers[$ain] = $ver_tokens[1];
	$newappVers[$ain] = $ver_tokens[1];
	if (isset($notify_arr[$ain]) && $notify_arr[$ain] <= $ver_tokens[1]) {
		unset($notify_arr[$ain]);
	}
}

if($subact == 'modify'){
	if (empty($_REQUEST['newconfigver'])) {// || $_REQUEST['oldconfigver'] == $_REQUEST['newconfigver']
		echo '输入有误：版本没有变化。';
		exit();
	}
	
	//调用107发布机的API，以更新servers.xml的状态
	$params = array();
	$params['appVer'] = $appver;
	$params['clientVer'] = $_REQUEST['newconfigver'];
	$qs = http_build_query($params);
	
	$display = explode('&', $qs);
	$display[] = '';
	
	$status = file_get_contents('http://IPIPIP:8081/api/update_config_client_version.php?'.$qs);
	if ($status=='OK') {
		$display[] = '更新成功。';
	}else{
		$display[] = 'ERROR';
	}
	echo implode("<br>", $display);
	exit();
}

if($subact == 'upload' && !$_FILES['userfile']['error']){
	// 	$local_allinone_filepath = '/data/htdocs/ifadmin/admin/cache/updown/1.0.86_config_1.0.1484.zip';
	$local_allinone_filepath = $dir_updown .'/'. basename($_FILES['userfile']['name']);
	move_uploaded_file($_FILES['userfile']['tmp_name'], $local_allinone_filepath);
	
	$newzipname = basename($local_allinone_filepath,'.zip');
	$verinfo = explode('_', $newzipname);
	$ftp_appver = $verinfo[0];
	$newconfigver = $verinfo[2];
	
	// 不覆盖CDN
	$target_file = $cdnbase."/$ftp_appver/config_{$newconfigver}_en.zip";
	$exists = remote_file_exists($target_file);
	if ($exists) {
		$opmessages[$appver] = "ERROR<br>CDN上已经存在该版本。<br>config_{$newconfigver}_en.zip";
		goto GOTO_MARK_PAGEEND_renderTemplate;
	}
	
	$zip = new ZipArchive;
	$res = $zip->open($local_allinone_filepath);
	if ($res === TRUE) {
		$zip->extractTo($dir_updown);
		$zip->close();
	} else {
		//
	}

	if (is_dir($dir_updown .'/'. $newzipname)) {
		$file_dir = $dir_updown .'/'. $newzipname;
	}else{
		$file_dir = $dir_updown;
	}
	$file_regx = 'config_'.$newconfigver.'_*.zip';
	
	$status = 0;
	$display = array();
	foreach (glob($file_dir.'/'.$file_regx) as $lf) {
		$tofile = "$ftp_appver/".basename($lf);
		$re = upload_file_via_curl($cdnUploadUrl, $lf, $tofile);
		if ($re != '0-') {
			$display[] = $tofile." ERR $re";
		}else {
			$display[] = $tofile." OK";
		}
	}
	if ($status === 0) {
		$opmessage = "Upload successfully.<br>";
	}else{
		$opmessage = "ERROR.<br>";
	}

	$opmessage .= implode("<br>", $display);
	$opmessages[$appver] = $opmessage;
	$langfiles[$appver] = $langfilearr;
	$newappVers[$appver] = $newconfigver;
}

if($subact == 'download'){
	$onename = "{$appver}_config_{$oldconfigver}.zip";
	$local_allinone_filepath = $dir_updown.'/'.$onename;
	$ftp_appver = $appver;
	
	if (file_exists($local_allinone_filepath)) {
		unlink($local_allinone_filepath);
	}
	
	$zipfiles = array();
	foreach ($langs as $lang) {
		$url = "$cdnbase/$appver/config_{$oldconfigver}_{$lang}.zip";
		$tofile = "$dir_updown/config_{$oldconfigver}_{$lang}.zip";
		if (file_exists($tofile)) {
			unlink($tofile);
		}
		$ret = download_file_via_curl($url, $tofile);
		$zipfiles[] = $tofile;
	}
	
	// zip all lang zip files into one. 1.0.86_config_1.0.1480.zip;
	$r = zip_file($local_allinone_filepath, $zipfiles);
	if (!$r) {
		echo 'ERROR. generate zipfile failed.';
		return ;
	}
	
	foreach ($zipfiles as $tempfile) {
		unlink($tempfile);
	}
	
	$fp=fopen($local_allinone_filepath,"r");
	$file_size=filesize($local_allinone_filepath);
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length:".$file_size);
	Header("Content-Disposition: attachment; filename=".basename($local_allinone_filepath));
	$buffer=1024;
	$file_count=0;
	while(!feof($fp) && $file_count<$file_size){
		$file_con=fread($fp,$buffer);
		$file_count+=$buffer;
		echo $file_con;
	}
	fclose($fp);
	exit();
}

GOTO_MARK_PAGEEND_renderTemplate:
include( renderTemplate("{$module}/{$module}_{$action}") );

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

