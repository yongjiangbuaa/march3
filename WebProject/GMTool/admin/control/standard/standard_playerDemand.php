<?php
!defined('IN_ADMIN') && exit('Access Denied');
$developer = in_array($_COOKIE['u'],$privilegeArr);
$showData = false;
$dbName='cokdb_admin_op';

$type = $_REQUEST['action'];
$infoList=array(
	'ALL'=>'--ALL--',
	'HS'=>'HS',
	'FB/VK/WB'=>'FB/VK/WB',
	'SURVEYMONKEY'=>'SURVEYMONKEY',
	'BBS'=>'BBS',
	'WeChat'=>'WeChat',
	'Other'=>'Other'
);
$kindList=array(
		'ALL'=>'--ALL--',
		'充值'=>'充值',
		'账号'=>'账号',
		'游戏内容'=>'游戏内容',
		'BUG"'=>'BUG'
);

if($_REQUEST['addDemand']){
	//$link = mysqli_connect('DEPLOYIP', 'root', 'DBPWD', 'cokdb_admin_op', '3306');
	$now = time();
	$info = array();
	$uuid=$_REQUEST['uuid'];
	$info['info_from'] = $_REQUEST['infoFrom'];
	$info['kind'] = $_REQUEST['classification'];
	$info['country'] = $_REQUEST['selectCountry'];
	$info['msg'] = addslashes(trim($_REQUEST['infoContents']));
	$info['keywords'] = addslashes(trim($_REQUEST['keywords']));
	$info['similarcnt'] = trim($_REQUEST['infoNum']);
	$info['author'] = $adminid;
	$info['date'] = date('Y-m-d H:i:s');
	$info['time'] = $now;
	$info['important'] = 0;
	$info['status'] = $_REQUEST['status']?$_REQUEST['status']:0;
	$info['last_reply_date'] = '';
	$info['last_reply_tIme'] = 0;
	
	$keys = array_keys ( $info );
	$vals = array_values ( $info );
	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	if ($uuid==-9){
		$sql = "insert into tbl_player_demand($fields) values($values);";
		query_deploy($sql,true,$dbName);
		
		$sql = "select * from tbl_player_demand where time=$now and author='$adminid'";
		$ret = query_deploy($sql,false,$dbName);
		$curr_pd_id = $ret['ret']['data'][0]['id'];
	}else {
		$temp='';
		foreach ($info as $key=>$val){
			$temp.="$key='$val',";
		}
		$temp=substr($temp, 0,strlen($temp)-1);
		$sql = "update tbl_player_demand set $temp where id=$uuid;";
	 	query_deploy($sql,true,$dbName);
	 	$curr_pd_id=$uuid;
	}
 	
 	if (!$curr_pd_id || $curr_pd_id==-9) {
 		exit('操作失败');
 	}
 	
 	$kwords = $_REQUEST['keywords'];
 	if ($kwords) {
 		$kwords = str_replace(array('；','，'), ',', $kwords);
 		$klist = explode(',', $kwords);
 		foreach ($klist as $word) {
 			if (empty($word)) {
 				continue;
 			}
 			$sql = "insert into tbl_player_demand_keyword values('$word',$curr_pd_id)";
 			query_deploy($sql,true,$dbName);
 		}
 	}
 	exit('操作成功');
}

if($_REQUEST['getReply']){
	$replyId=$_REQUEST['replyId'];
	if (!$replyId || $replyId==-9){
		exit('获取回复失败');
	}
	$log=array();
	$sql="select msg,author,date from tbl_player_demand_oplog where pd_id='$replyId' order by time desc;";
	$ret = query_deploy($sql,false,$dbName);
	foreach ($ret['ret']['data'] as $row){
		$temp=array();
		$row['msg']=str_replace('"', '&Prime;', $row['msg']);
		$row['msg']=str_replace("'", '&acute;', $row['msg']);
		$temp['msg']=$row['msg'];
		$temp['author']=$row['author'];
		$temp['date']=$row['date'];
		$log[]=$temp;
	}
	$html='';
	if ($log){
		$html.="<div style='text-align: left;'>";
		foreach ($log as $dbVal){
			$html .= '<div><font color="red">回复人:'.$dbVal['author']."    回复时间:".$dbVal['date']."</font><br>";
			$html .='<span>'.$dbVal['msg'].'</span></div>';
		}
		$html.="</div>";
	}
	exit($html);
}

if($_REQUEST['saveReply']){
	$replyId=$_REQUEST['replyId'];
	if (!$replyId || $replyId==-9){
		exit('回复失败');
	}
	$replyContent= addslashes(trim($_REQUEST['replyContent']));
	$date=date('Y-m-d H:i:s');
	$now = time();
	$sql="insert into tbl_player_demand_oplog(pd_id,msg,author,date,time) values($replyId,'$replyContent','$adminid','$date',$now);";
	query_deploy($sql,true,$dbName);
	$sql="update tbl_player_demand set status=1,last_reply_date='$date',last_reply_tIme=$now where id=$replyId;";
	query_deploy($sql,true,$dbName);
	exit('已回复');
}

if ($type) {
	$infoFrom=$_REQUEST['infoFrom'];
	$currCountry=$_REQUEST['selectCountry'];
	$classification=$_REQUEST['classification'];
	$keywordSearch = trim($_REQUEST['keywordsSearch']);
	
	$whereSql=" where 1=1";
	if ($infoFrom && $infoFrom!='ALL'){
		$whereSql.=" and info_from='$infoFrom' ";
	}
	if ($currCountry && $currCountry!='ALL'){
		$whereSql.=" and country='$currCountry' ";
	}
	if ($classification && $classification!='ALL'){
		$whereSql.=" and kind='$classification' ";
	}
	
	if ($keywordSearch) {
		$kwords = str_replace(array('；','，'), ',', $keywordSearch);
		$klist = explode(',', $kwords);
		$inwords = "'".implode("','", $klist)."'";
		$whereSql.=" and id in (select pd_Id from tbl_player_demand_keyword where word in ($inwords))";
	}
	
	$sql="select * from tbl_player_demand $whereSql order by important desc,last_reply_tIme desc,time asc;";
	
	if ($_COOKIE['u']=='yd'){
		echo $sql;
	}
 	$ret = query_deploy($sql,false,$dbName);
	$result = array();
	foreach ($ret['ret']['data'] as $row){
		$row['date'] = str_replace(' ', '<br>', $row['date']);
		$row['msg']=str_replace('"', '&Prime;', $row['msg']);
		$row['msg']=str_replace("'", '&acute;', $row['msg']);
		$row['status_detail'] = get_pd_state($row,$dbName);
		$result[] = $row;
	}
	if ($result) {
		$showData = true;
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );


function get_pd_state($row,$dbName){
	$status = $row['status'];
	if (0 == $status) {//初始
		$content = '';
	}
	
	$pd_id = $row['id'];
	$sql = "select * from tbl_player_demand_oplog where pd_id=$pd_id order by id desc limit 1";
	$ret=query_deploy($sql,false,$dbName);
	if (empty($ret['ret']['data'])) {
		$content = '';
	}else{
		$act = $ret['ret']['data'][0]['act'];
		if (0 == $act) {//回复
			$content = "{$ret['ret']['data'][0]['msg']}<br>-----<br>{$ret['ret']['data'][0]['author']}<br>{$ret['ret']['data'][0]['date']}";
		}
	}
	if ($content) {
		$content .= "<br>----<br>";
	}
	$content .= '<a href="javascript:void(reply('."'".$row['id']."',"."'".$row['info_from']."',"."'".$row['country']."',"."'".$row['kind']."',"."'".$row['msg']."',"."'".$row['keywords']."',"."'".$row['similarcnt']."',"."'".$row['status']."',"."'".$row['date']."'".'))">[回复]</a>';
	return $content;
}

// CREATE TABLE `tbl_player_demand` (
// `id` int(11) NOT NULL AUTO_INCREMENT,
// `info_from` varchar(32) NOT NULL,
// `kind` varchar(32) NOT NULL DEFAULT '',
// `country` varchar(32) NOT NULL DEFAULT '',
// `msg` varchar(1024) NOT NULL DEFAULT '',
// `keywords` varchar(1024) NOT NULL DEFAULT '',
// `similarcnt` tinyint(3) DEFAULT '0',
// `author` varchar(32) NOT NULL DEFAULT '',
// `date` varchar(19) NOT NULL DEFAULT '',
// `time` int(10) NOT NULL DEFAULT '0',
// `important` tinyint(3) DEFAULT '0',
// `status` tinyint(3) DEFAULT '0',
// `last_reply_date` varchar(19) DEFAULT '',
// `last_reply_tIme` int(10) NOT NULL DEFAULT '0',
// PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

// CREATE TABLE `tbl_player_demand_oplog` (
// `id` int(11) NOT NULL AUTO_INCREMENT,
// `pd_id` int(10) NOT NULL DEFAULT '0',
// `act` tinyint(3) DEFAULT '0', -- 0:reply; 1:close....
// `msg` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
// `author` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
// `date` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
// `time` int(10) NOT NULL DEFAULT '0',
// PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

// CREATE TABLE `tbl_player_demand_keyword` (
// `word` varchar(32) NOT NULL,
// `pd_Id` int(10) NOT NULL DEFAULT '0',
// PRIMARY KEY (`word`,`pd_Id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

?>

