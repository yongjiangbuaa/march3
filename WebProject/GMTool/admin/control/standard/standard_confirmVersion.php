<?php
!defined('IN_ADMIN') && exit('Access Denied');
$developer = in_array($_COOKIE['u'],$privilegeArr);
$showData = false;
$isConfValue='all';

$type = $_REQUEST['action'];

$sql="select * from tbl_publish order by appVer desc limit 1;";
$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
$res = mysqli_query($link,$sql);
$row = mysqli_fetch_assoc($res);
$version_start=$row['appVer'];
mysqli_close($link);

//$version_start='1.0.91';

if($_REQUEST['version_start'])
	$version_start = $_REQUEST['version_start'];

if($_REQUEST['isConfirm']){
	$version=$_REQUEST['version'];
	$state=$_REQUEST['state'];
	$sql="update tbl_svnlog set confirm=$state where revision=$version";
//	$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
//	$res = mysqli_query($link,$sql);
//	mysqli_close($link);
	$res = query_deploy($sql);
	if (empty($res)) {
		exit('状态更新失败');
	}else {
		exit('状态更新成功');
	}
}

if($_REQUEST['addPublish']){
	$appVer=$_REQUEST['appVer'];
	$trunk_from=$_REQUEST['trunk_from'];
	$trunk_to=$_REQUEST['trunk_to'];
	$branch_from=$_REQUEST['branch_from'];
	$branch_to=$_REQUEST['branch_to'];
	$sql = "insert into tbl_publish(appVer,trunk_from,trunk_to,branch_from,branch_to) values('$appVer',$trunk_from,$trunk_to,$branch_from,$branch_to) ON DUPLICATE KEY UPDATE trunk_from=$trunk_from,trunk_to=$trunk_to,branch_from=$branch_from,branch_to=$branch_to;";
//	$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
//	$res = mysqli_query($link,$sql);
//	mysqli_close($link);
	$res = query_deploy($sql);
	if (empty($res)) {
		exit('游戏版本增加失败');
	}else {
		exit('游戏版本增加成功');
	}
}

if ($type) {
// 		$svnlog_status = parse_ini_file($status_file,false,INI_SCANNER_RAW);
// 		$sql='';
// 		foreach ($svnlog_status as $revisionKey => $statusValue){
// 			$statusValue=intval($statusValue);
// 			$sql .= "insert into tbl_svnlog(revision,confirm) values($revisionKey,$statusValue) ON DUPLICATE KEY UPDATE confirm=$statusValue;";
// 		}
// 		$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
// 		$res = mysqli_multi_query($link, $sql);
// 		mysqli_close($link);
	
	$sql="select * from tbl_publish where appVer='$version_start' limit 1;";
	$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
	$res = mysqli_query($link,$sql);
	while ($row = mysqli_fetch_assoc($res)){
		$currentAppVer=$row['appVer'];
		$trunk_from=$row['trunk_from'];
		$trunk_to=$row['trunk_to'];
		$branch_from=$row['branch_from'];
		$branch_to=$row['branch_to'];
	}
	
	$isConfValue=$_REQUEST['isConfValue'];
	$programmer=$_REQUEST['programmer'];
	$programmer=trim($programmer);
	$whereSql="";
	if($isConfValue!='all'){
		if($isConfValue==1){
			$whereSql .=" and confirm=1 ";
		}elseif ($isConfValue==0){
			$whereSql .= " and confirm=0 ";
			$isConfValue=0;
		}
	}
	if($programmer){
		$whereSql .= " and author='$programmer' ";
	}
	$sql="select * from tbl_svnlog where revision between $trunk_from and $branch_to $whereSql;";
	$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_deploy', '3306');
	$res = mysqli_query($link,$sql);
	while ($row = mysqli_fetch_assoc($res)){
		$line = array();
		$line['revision']=$row['revision'];
		$line['date']=$row['date'];
		$line['msg']=$row['msg'];
		$line['author']=$row['author'];
		$line['director']=$row['director'];
		$line['files']=$row['files'];
		$line['confirm']=$row['confirm'];
		$logs[]=$line;
	}
	mysqli_close($link);
	
	$result = array();
	foreach ($logs as $value) {
		
		$revision = $value['revision'];
		$paths = $value['files'];
		if (skip_log($paths)) {
			continue;
		}
		if ($value['author'] == 'wangxianwei' && $value['msg'] == 'merge by script.') {
			continue;
		}
		$path_desc = '';
		
		$is_trunk_pash = is_trunk_pash($paths);
		if ($is_trunk_pash) {
			if (($revision < $trunk_from || $revision > $trunk_to)) {
				continue;
			}
			$path_desc = '[trunk]<br>';
		}

		
// 		$is_subbranch_pash = is_subbranch_pash($paths);
// 		if ($is_subbranch_pash) {
// 			if ($revision < $subbranch_revision_from || $revision > $subbranch_revision_to) {
// 				continue;
// 			}
// 			$path_desc = '[Bug修复补丁]<br>';
// 		}

		
		$is_branch_pash = is_branch_pash($paths);
		if ($is_branch_pash) {
			if ($revision < $branch_from || $revision > $branch_to) {
				continue;
			}
			$path_desc = '[封版后变更]<br>';
		}

		$one = array();
		$one['status'] = intval($value['confirm']);
		$one['revision'] = $revision;
		$one['author'] = $value['author'];
		$one['date'] = str_replace('T', '<br>', substr($value['date'], 0, 19));
		$one['cehua']=$value['director'];
		$one['msg']=$value['msg'];
		$paths=explode("<br>", $paths);
		if (is_array($paths)) {
			$newnl = array();
			foreach ($paths as $p) {
				$bnn = basename($p);
				$newnl[] = $bnn;
			}
			$one['files'] = $path_desc.implode("<br>", $newnl);
		}else{
			$one['files'] = $path_desc.basename($paths);
		}
	
		$result[$revision] = $one;
	}
	if ($result) {
		$showData = true;
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );

function is_trunk_pash($paths) {
	if (is_array($paths)) {
		foreach ($paths as $value) {
			if (strpos($value, 'trunk')) {
				return true;
			}
		}
	}else{
		return strpos($paths, 'trunk');
	}
}
function is_branch_pash($paths) {
	if (is_array($paths)) {
		foreach ($paths as $value) {
			if (strpos($value, 'branches') && !strpos($value, 'sub_branches')) {
				return true;
			}
		}
	}else{
		return strpos($paths, 'branches') && !strpos($paths, 'sub_branches');
	}
}
function is_subbranch_pash($paths) {
	if (is_array($paths)) {
		foreach ($paths as $value) {
			if (strpos($value, 'sub_branches')) {
				return true;
			}
		}
	}else{
		return strpos($paths, 'sub_branches');
	}
}
function skip_log($paths) {
	if (!is_array($paths)) {
		$paths = array($paths);
	}
	foreach ($paths as $value) {
		if (strpos($value, 'src/client')) {
			return false;
		}
		if (strpos($value, 'src/server/ClashOfKingProject')) {
			return false;
		}
		if (strpos($value, 'sub_branches')) {
			return false;
		}
		
		if (strpos($value, 'src/web_client')) {
			return true;
		}
	}
	return true;
}

?>

