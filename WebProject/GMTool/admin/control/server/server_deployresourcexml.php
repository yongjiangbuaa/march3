<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);

$subact = $_REQUEST['subact'];
$run_status = array(
		0=>'',
		1=>'发布中',
		2=>'发布失败',
		3=>'发布成功',
		4=>'已取消',
);

if($subact == 'addplan'){
	$files = implode('|', $_REQUEST['resxmls']);
	$servers = $_REQUEST['selectServer'];
	$delaymin = intval($_REQUEST['delaymin']);
	$plan_time = time() + $delaymin * 60;

	if (empty($files)) {
		$error_msg = "请选择需要发布的xml文件。";
		goto GOTO_MARK_PAGEEND_view;
	}

	$one = array();
	$one['author'] = $adminid;
	$one['files'] = $files;
	$one['servers'] = $servers;
	$one['plan_time'] = $plan_time;
	$one['run_result'] = '';
	$one['status'] = '0';
	$one['create_time'] = time();

	$fields = implode(',', array_keys($one));
	$vals = implode("','", $one);
	$vals = "'$vals'";
	$sql = "insert into cokdb_admin_deploy.tbl_xml_publish ($fields) values ($vals)";
	$ret=$page->globalExecute($sql, 2);
}

if($subact == 'cancelplan'){
	$planid = $_REQUEST['planid'];
	$sql = "update cokdb_admin_deploy.tbl_xml_publish set status=4 where id=$planid";
	$ret=$page->globalExecute($sql, 2);
}

GOTO_MARK_PAGEEND_view:

$sql = "select * from cokdb_admin_deploy.tbl_xml_publish order by plan_time desc limit 10";
$ret=$page->globalExecute($sql, 3);
$xmlplanlist = $ret['ret']['data'];
foreach ($xmlplanlist as &$row) {
	$row['plan_time'] = date('Y-m-d H:i:s', $row['plan_time']);
	$row['servers'] = $row['servers']?$row['servers']:'全服';
	$row['run_time'] = $row['run_time']?date('Y-m-d H:i:s', $row['run_time']):"《未执行》";
	$row['run_result'] = $row['run_result']?$row['run_result']:$run_status[$row['status']];
	$row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
}

$sql = "select tx.file, tx.date, tx.time, tx.author, tx.msg 
		from cokdb_admin_deploy.tbl_xml_svnlog tx inner join 
		(select file,max(time) maxtime from cokdb_admin_deploy.tbl_xml_svnlog group by file) tx_tmp 
		on tx.file=tx_tmp.file and tx.time=tx_tmp.maxtime order by time desc limit 10;";
$ret=$page->globalExecute($sql, 3);
$recentxmllist = $ret['ret']['data'];

GOTO_MARK_PAGEEND_renderTemplate:
include( renderTemplate("{$module}/{$module}_{$action}") );

