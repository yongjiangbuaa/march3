<?php
!defined('IN_ADMIN') && exit('Access Denied');
if ($_REQUEST['type']=='save') {
	$add = $_REQUEST['add'];
	$version = $_REQUEST['version'];
	$value = $_REQUEST['value'];
	if (($add == 1) || ($add == 2) ){
		if (!$value){
			exit('值不能为空!');
		}
		if (!$version){
			exit('版本不能为空!');
		}
		$sql ="REPLACE INTO app_version VALUES ('app_$version', '$value');";
		$ret=cobar_query_global_db_cobar($sql);
		if($add == 2){
			if (!$value){
				exit('值不能为空!');
			}
			$sql ="REPLACE INTO app_version VALUES ('app_version', '$version');";
			$ret=cobar_query_global_db_cobar($sql);
		}
	} else if ($add == 3) {
			$sql ="REPLACE INTO app_version VALUES ('min_app_version', '$value');";
			$ret=cobar_query_global_db_cobar($sql);
	}
	$client = new Redis();
	$client->connect(GLOBAL_REDIS_SERVER_IP);
	$client->publish("RefreshAppVersionChannel",1);
}else if ($_REQUEST['type']=='del') {
	$version_id = $_REQUEST['id'];
	if (!$version_id){
		exit('id不能为空!');
	}
	$sql ="delete from app_version where version_id='$version_id';";
	$ret=cobar_query_global_db_cobar($sql);
	$client = new Redis();
	$client->connect(GLOBAL_REDIS_SERVER_IP);
	$client->publish("RefreshAppVersionChannel",1);
	exit(0);
}
$sql="select * from app_version order by version_id desc";
$ret=cobar_query_global_db_cobar($sql);
$data = array();
foreach($ret as $row){
	$data[$row['version_id']] = $row['version_value'];
}
uksort($data,'compare2');

function compare($stra,$strb){
	$stra = trim($stra,"'");
	$strb = trim($strb,"'");

	$a = explode('.', $stra);
	$b = explode('.', $strb);

	if($a[0] > $b[0]){
		return 1;
	}
	elseif($a[0] < $b[0]){
		return -1;
	}
	elseif($a[1] > $b[1]){
		return 1;
	}
	elseif($a[1] < $b[1]){
		return -1;
	}
	elseif($a[2] > $b[2]){
		return 1;
	}
	elseif($a[2] < $b[2]){
		return -1;
	}
	elseif($a[3] > $b[3]){
		return 1;
	}
	elseif($a[3] < $b[3]){
		return -1;
	}
	return 0;
}

function compare2($stra,$strb)
{
	return -1* compare($stra,$strb);
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
