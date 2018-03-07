<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$serverArray=$erversAndSidsArr['onlyNum'];

$startTimes=(time()+1800)*1000;
$tcontent = $_REQUEST['tcontent'];
$uid = 1000001;
$push_state = 0;
$photo_seq=0;
$file = $_FILES['file'];//得到传输的数据
//得到文件名称
echo json_encode($file);
$name = $file['name'];
$size = $file['size'];
if($size>=2048000){
	$error_msg = search($size);
	return;
}
if($tcontent) {

	$type = strtolower(substr($name, strrpos($name, '.') + 1)); //得到文件类型，并且都转化成小写
	$allow_type = array('jpg', 'jpeg', 'gif', 'png'); //定义允许上传的类型
	if($size!=0) {
		$sql_select = "select max(image) photo_seq from friend_circle_system ";
		$photo_seq = $page->execute($sql_select, 3);
		$photo_seq = $photo_seq['ret']['data'][0]['photo_seq'] + 1;
		$sig_key = 'g!w1xz@s2dy#y3l';
		$name = md5('1000001_' . $photo_seq);
		$upload_path = "/data/coq_avatar/fm/000001/" . $name . ".jpg"; //上传文件的存放路径
		if (move_uploaded_file($file['tmp_name'], $upload_path)) {
			file_put_contents('/tmp/dbc.log', print_r($file, true), FILE_APPEND);
		} else {
			file_put_contents('/tmp/dbc.log', 'Failed', FILE_APPEND);
		}
	}else{
		$photo_seq=0;
	}

	$uuid = md5($sig_key . 'gameuid' . 1000001 . 'photo_seq' . $photo_seq);
	$sql_insert = "INSERT INTO `friend_circle_system` (`uuid`, `uid`, `content`, `createTime`, `image`, `push_state`)
	VALUES ('$uuid', '$uid', '$tcontent', '$startTimes', '$photo_seq', '$push_state')";

	$host = gethostbyname(gethostname());
	foreach ($selectServer as $server => $serInfo) {
		$result = $page->executeServer($server, $sql_insert, 2);
	}
	file_put_contents('/tmp/dbc.log', $sql_insert, FILE_APPEND);
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>