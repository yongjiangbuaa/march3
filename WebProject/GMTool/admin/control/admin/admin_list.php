<?php
!defined('IN_ADMIN') && exit('Access Denied');
require_once ADMIN_ROOT . "/include/XMySQL.php";
require_once ADMIN_ROOT . "/etc/admin.group.php";
$allGroupPermission = require ADMIN_ROOT . "/etc/admin.group.php";
$op = $_REQUEST["op"];
$tablename = 'admin';
$resetAuth =( $_POST['resetAuth']=='resetAuth')?true:false;
//====================
$opt = $_REQUEST["addcomment"];
$mysql = new XMySQL($page->getMySQLInfo(true, 's1'));
if(isset($_REQUEST["addcomment"])){
	echo "<br/>";
	$uid = $_REQUEST["uid"];

	$adminuid = substr($uid,3);
	$content = $_REQUEST["content"];

	$tablename = "admin";
	$where = "uid=".$adminuid;
	$result = $mysql->put($tablename,array('uid'=>$adminuid),array('admincomment'=>$content));

	exit($result);
//				XMysql::singleton(array('mainDB'))->put($tablename, array('uid' => $result['uid']), array('lastactive' => $result['lastactive']));
}
//===========================
if($op){
	if($op == "add_submit"){
		$value = array();
		$value["username"] = $_POST["username"];
		$value["passmd5"] = md5(md5($value["username"] . $_POST["password"] . AUTH_KEY).AUTH_KEY.AUTH_KEY2);
		$value["language"] = trim($_POST["language"]);
		$value["groupid"] =intval( $_POST["admingroup"]);
		$value["auth"] = implode(",", $_POST["auth"]);
        if($resetAuth == true)
	        $value["auth"] = $allGroupPermission[$value["groupid"]]['auth'];
//		$value["groupid"] = "0";
		$value["addtime"] = time();
		$value["lastactive"] = 0;
		$result = $mysql->add($tablename, $value);
        adminLogSystem($adminid,array('add_adminuser'=>$value));
	}
	elseif($op == "delete"){
		//删除界面
		$uid_array = $_POST["uid"];
		foreach($uid_array as $uid){
			$where = array('uid' => $uid);
			$result = $mysql->del($tablename, $where);
		}
        adminLogSystem($adminid,array('delete_adminuser'=>$uid_array));
	}
	elseif($op == "edit"){
		$target_uid = $_REQUEST["adminuid"];
		$where = array('uid' => $target_uid);
		$qu_result = $mysql->get($tablename, $where);
		$admin_user = $qu_result[0];
		if(!empty($admin_user)){
			$array_auth = explode(',', $admin_user['auth']);
			$group_auth = array();
			if(!empty($allGroupPermission[$admin_user['groupid']]['auth'])){
				$group_auth =explode(',', $allGroupPermission[$admin_user['groupid']]['auth']);
			}
			$array_auth = array_merge($array_auth,$group_auth);
			global $authList;
			foreach ($array_auth as $key=>$value){
				if(!in_array($value,$authList))
					unset($array_auth[$key]);
			}
			$menu_array = array_combine($array_auth, $array_auth);
			$admin_user['auth'] = $menu_array;
		}
	}elseif ($op == "edit_submit"){
		$value = array();
		$value["username"] = $_POST["username"];
		$value["groupid"] = $_POST["admingroup"];
		$value["auth"] = implode(",", $_POST["auth"]);
        if($resetAuth == true) $value["auth"] = $allGroupPermission[$value["groupid"]]['auth'];
		$where['uid'] = $_POST["adminuid"];
		$result = $mysql->put($tablename,$where, $value);
        adminLogSystem($adminid,array('edit_adminuser'=>array('uid'=>$where,'newvalue'=>$value)));
	}
}

if($result){
	$op_msg = "action do success";
}
$defaultPermission = explode(',',$allGroupPermission[$admin_user['groupid']]['auth']);
$diff = array_diff($defaultPermission,$admin_user['auth']);
$isDefault = false;
if(count($diff) == 0)  $isDefault = true;
if(empty($op) || $result){
	//TODO查询
	$tablename = "admin";
	$where = "1=1";
	$result1 = $mysql->get($tablename, $where, null ,1000, 'order by groupid desc,username');
	$ADMINLIST = $result1;
}

include( renderTemplate("{$module}/{$module}_{$action}") );

?>