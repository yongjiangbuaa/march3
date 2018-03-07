<?php
!defined('IN_ADMIN') && exit('Access Denied');
//| version_id     | version_value
$pfarray = array('android'=>'安卓','ios'=>'IOS');
$option = '';

$pf = $_REQUEST['pf'];
foreach($pfarray as $key=>$value){
	if($pf == $key){
		$add = "selected = 'selected'";
	}else{
		$add = '';
	}
	$option .= "<option value='{$key}' $add>{$value}</option>";
}

if ($_REQUEST['type']=='save') {
	$version = $_REQUEST['version'];
	$value = $_REQUEST['value'];
	if (!$version){
		exit('版本不能为空!');
	}
	if (!$value){
		exit('值不能为空!');
	}
	$version = trim($version);
	$value = trim($value);
	$sql ="REPLACE INTO lua_version VALUES ('{$pf}_$version', '$value');";
	$ret=cobar_query_global_db_cobar($sql);
	$client = new Redis();
	$client->connect(GLOBAL_REDIS_SERVER_IP);
	$client->publish("RefreshLuaVersionChannel",1);
	$detail = 'save--'.$pf.'--'.$version.'--'.$value;
	adminLogSystem($adminid,$detail);
}else if ($_REQUEST['type']=='del') {
	$version_id = $_REQUEST['id'];
	if (!$version_id){
		exit('id不能为空!');
	}
	$sql ="delete from lua_version where version_id='$version_id';";
	$ret=cobar_query_global_db_cobar($sql);
	$client = new Redis();
	$client->connect(GLOBAL_REDIS_SERVER_IP);
	$client->publish("RefreshLuaVersionChannel",1);

	$detail = 'del--'.$version_id;
	adminLogSystem($adminid,$detail);

	exit(0);
}
$sql="select * from lua_version order by version_id desc";
$ret=cobar_query_global_db_cobar($sql); //$ret 是个数组,里面一行行数据
$data = array();
foreach($ret as $row){
	$data[$row['version_id']] = $row['version_value'];
}
uksort($data,'compare2');
file_put_contents('/tmp/qinbin',print_r($ret,true)."\n",FILE_APPEND);
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
