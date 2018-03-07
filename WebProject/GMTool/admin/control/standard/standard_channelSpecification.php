<?php
!defined('IN_ADMIN') && exit('Access Denied');
$developer = in_array($_COOKIE['u'],$privilegeArr);
$showData = false;

$type = $_REQUEST['action'];

if($_REQUEST['addChannel']){
	//$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_op', '3306');
	$now = time();
	$info = array();
	$uuid=$_REQUEST['uuid'];
	$info['pfStr'] = addslashes($_REQUEST['channelfromAdd']);
	$info['msg'] = addslashes(trim($_REQUEST['contentsAdd']));
	$info['author'] = $adminid;
	$info['date'] = date('Y-m-d H:i:s');
	$info['time'] = $now;
	
	$keys = array_keys ( $info );
	$vals = array_values ( $info );
	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	if ($uuid==-9){
		$sql = "insert into tbl_channel_specification($fields) values($values);";
		query_deploy($sql,true);
	}else {
		$temp='';
		foreach ($info as $key=>$val){
			$temp.="$key='$val',";
		}
		$temp=substr($temp, 0,strlen($temp)-1);
		$sql = "update tbl_channel_specification set $temp where id=$uuid;";
	 	query_deploy($sql,true);
	}
 	exit("操作成功");
}

//if ($type) {
	$channelfrom=$_REQUEST['channelfrom']?$_REQUEST['channelfrom']:'';
	
	$whereSql=" where 1=1 ";
	if ($channelfrom){
		$whereSql.=" and pfStr like '%$channelfrom%' ";
	}
	
	$sql="select id,pfStr,msg from tbl_channel_specification $whereSql order by id;";
	
 	$ret = query_deploy($sql,false);
	$result = array();
	foreach ($ret['ret']['data'] as $row){
		$result[] = $row;
	}
	if ($result) {
		$showData = true;
	}
//}

include( renderTemplate("{$module}/{$module}_{$action}") );


// CREATE TABLE `tbl_channel_specification` (
//  `id` int(11) NOT NULL AUTO_INCREMENT,
//  `pfStr` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
//  `msg` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
//  `author` varchar(32) NOT NULL DEFAULT '',
//  `date` varchar(19) NOT NULL DEFAULT '',
//  `time` int(10) NOT NULL DEFAULT '0',
//  PRIMARY KEY (`id`)
//  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

?>

