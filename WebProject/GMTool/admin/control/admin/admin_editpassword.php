<?php
$username = $adminid;
$actiondo = trim($_POST['action']);
require_once ADMIN_ROOT . "/include/XMySQL.php";
if($actiondo == 'edit'){
	$password = $_POST['password'];
	$oldpass = $_POST['oldpass'];
	if (strlen($password)<6 || strlen($password)>32){
		$error_msg = "密码须在6到32位之间！";
		GOTO PAGEEND;
	}
	if (!preg_match('/[A-Z]/',$password)){
		$error_msg = "密码必须包含大写字母！";
		GOTO PAGEEND;
	}
	if (!preg_match('/[a-z]/',$password)){
		$error_msg = "密码必须包含小写字母！";
		GOTO PAGEEND;
	}
	if (!preg_match('/[0-9]/',$password)){
		$error_msg = "密码必须包含数字！";
		GOTO PAGEEND;
	}
	if (!preg_match('/[\@\#\$\%\!\&\*]/',$password)){
		$error_msg = "密码必须至少包含!,@,#,$,%,&,*中的一个！";
		GOTO PAGEEND;
	}
	if($_POST['username'] == $username && $password != $oldpass){
		$tablename = 'admin';
		$new["passmd5"] = md5(md5($username . $password . AUTH_KEY).AUTH_KEY.AUTH_KEY2);
		$where['username'] = $_POST['username'];
		$where['passmd5'] = md5(md5($username . $oldpass . AUTH_KEY).AUTH_KEY.AUTH_KEY2);
		file_put_contents(ADMIN_ROOT.'/GMUseStat2.log', date('Y-m-d H:i:s')." $username $clientip change $oldpass {$where['passmd5']} -> $password {$new["passmd5"]} \n",FILE_APPEND);
        $mysql = new XMySQL($page->getMySQLInfo(true, 's1'));
        $old = $mysql->get($tablename, $where);
		if($old){
			$result = $mysql->put($tablename,$where, $new);
			if($result){
				$op_msg = "修改成功，新的密码为".$password;
				$newPass = $password;
			}
		}else{
			$error_msg = "密码错误！";
		}
	}elseif($password == $oldpass){
		$error_msg = "密码未改变！";
	}else{
		$error_msg = "不能修改别人的密码！";
	}
}
if($actiondo == 'continue'){
	$password = $_POST['newPass'];
	logout();
	login($username, $password);
	header("Location:admincp.php");
}
PAGEEND:
include( renderTemplate("{$module}/{$module}_{$action}") );
?>