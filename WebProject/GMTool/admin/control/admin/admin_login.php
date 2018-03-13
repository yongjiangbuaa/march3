<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(isset($_REQUEST['relogin'])){
	echo login($_REQUEST['user'],$_REQUEST['password']);
	exit();
}
if(isset($_POST['user']) && isset($_POST['password'])){
	$login = login($_POST['user'],$_POST['password']);
	if($login>0){
		$error_msg = $MALANG['login_failed_null_times'];
	}
	else{
		$prev_url = $_SESSION['prev_url'];
		if(empty($prev_url) || $prev_url == 'mod=admin&act=login'){
//			file_put_contents('/tmp/loginhis.log', 'new_url=admincp.php'."\n",FILE_APPEND);
			header('Location:admincp.php');
		}
		else{
			$new_url = array();
			$tempArr = explode('&', $prev_url);
			foreach ($tempArr as $temp)
			{
				$tmp = explode('=', $temp);
				if(in_array($tmp[0], array('mod','act'))){
					$new_url[] = $temp;
				}
			}
//			file_put_contents('/tmp/loginhis.log', 'new_url='.implode('&', $new_url)."\n",FILE_APPEND);
			header("Location:admincp.php?" . implode('&', $new_url));
		}
		exit();
	}
}else{
	$error_msg = $MALANG['login_failed_no_session'];
	if(!empty($_SERVER['QUERY_STRING'])){
		$_SESSION['prev_url']=$_SERVER['QUERY_STRING'];
	}
}
include( renderTemplate("admin/admin_login") );
?>