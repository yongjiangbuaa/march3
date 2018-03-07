<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "查看玩家信息";
$headAlert = "";


function sendReward($mailUid){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid));
}
if ($type) {
    if (empty($username) && empty($useruid)){
   	 	$headAlert='请输入用户名或UID';
    }else {
    		/*
    		$whereSql='';
    		if ($username){
    			$whereSql=" binary u.name='$username' ";
    		}else {
    			$whereSql=" binary u.uid='$useruid' ";
    		}
    		*/
    		
    		if($username){
    			$account_list = cobar_getAllAccountList('name', $username);
    			$useruid = $account_list[0]['gameUid'];
    			$server='s'.$account_list[0]['server'];
    			if (!$useruid){
    				$sql="select uid from userprofile where binary name ='$username'";
    				$ret=$page->execute($sql, 3);
    				$useruid=$result['ret']['data'][0]['uid'];
    			}
    		}else {
    			$account_list = cobar_getAccountInfoByGameuids($useruid);
    			$server='s'.$account_list[0]['server'];
    		}
    		
	    $sql="select u.name, u.picVer, u.uid from pic_upload_record p inner join userprofile u on p.uid = u.uid where u.uid='$useruid' and u.picVer!=0 and u.picVer<3000000;";
	    $result = $page->executeServer($server, $sql, 3);
	    $data=array();
	    if($result['error'] || (!$result['ret']['data'])){
	   	 	$headAlert='没有查到数据';
	    }else {
	    	
		    $row=$result['ret']['data'][0];
		    	$data['server']=$server;
		    	$data['uid']=$row['uid'];
		    	$data['name']=$row['name'];
		    	$data['picVer']=($row['picVer']);
		    	
		    	$html = "<div style='float:left;width:100%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	    		
		    	$ext = ".jpg";
		    	$uid=$data['uid'];
		    	$name=str_replace("'", '&#39;', $data['name']);
		    	$name=str_replace(">", '&gt;', $name);
		    	$name=str_replace("<", '&lt;', $name);
		    	$seq=$data['picVer'];
		    	$file_folder = substr($uid, -6);
		    	$file_name = md5($uid . '_' . $seq) . $ext;
		    	$html .= "<label for='picture_".$sqlDatas[$i]['server']."_{$uid}_{$seq}'><img src='http://coq.eleximg.com/coq/img/$file_folder/$file_name'  style='width:120px;height:120px;' ></label><br>";
		    	$html .= '<input class="btn js-btn btn-primary" type="button" value="撤销头像" name="btn_view" onclick="check_edit('."'$uid','$server'".')" />';
		    	$html .= "</div>";
	    }
    }
}

if ($_REQUEST['event']=='edit'){
	$uid=$_REQUEST['uid'];
	$server=$_REQUEST['server'];
	
	$returnUids = $page->webRequest ( "refreshpicver", array('uidStr' => $uid, 'gmName'=>$_COOKIE ['u'],'action'=>'reject'), $server );
	if (!$returnUids) {
		exit ( "调用Java接口删除图片失败!" );
	}
	adminLogUser($_COOKIE ['u'],$returnUids,$server,array('uid'=>$returnUids));
	
	$sql="select lang from userprofile where uid='$uid'";
	$result=$page->executeServer ( $server, $sql, 3 );
	$language=$result['ret']['data'][0]['lang'];
	if($language=='CN'||$language=='zh-chs'||$language=='zh_CN'){
		$lang = loadLanguage('zh_CN');
	}else if($language=='TW'||$language=='zh-cht'||$language=='zh_TW'){
		$lang = loadLanguage('zh_TW');
	}else{
		$lang = loadLanguage($language);
	}
	$title = addslashes ($lang[105773]);
	$contents = addslashes ($lang[105774]);

	$sendTime = microtime ( true ) * 1000;
	$mailUid = md5 ( $uid . $server. time () );
	$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0,103)";
	$result2 = $page->executeServer ( $server, $sql, 2 );
	sendReward2 ( $mailUid, $server );
	exit('OK');
}

function sendReward2($mailUid, $serv) {
	$page = new BasePage ();
	$page->webRequest ( 'sendmail', array (
			'uid' => $mailUid
	), $serv );
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>