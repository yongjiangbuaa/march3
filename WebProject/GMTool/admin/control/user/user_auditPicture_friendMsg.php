<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectId=$erversAndSidsArr['onlyNum'];
if($_REQUEST['analyze']=='user'){
	
		$t=array_keys($selectServer);
		$server=$t[0];
		if(substr($server, 0,1)!='s'){
			$server=$currentServer; 
		}
		
		$sql="select count(userUid) num from user_images where imageType = 0 ;";
		
		$result = $page->executeServer($server, $sql, 3);
		if($result['error'] || (!$result['ret']['data'])){
			exit();
		}
		$sum = $result['ret']['data'][0]['num'];
		$page_limit = 100;
		$pager = page($sum, $_REQUEST['page'], $page_limit);
		$index = $pager['offset'];
		//$sql = "select uid,name,picVer from userprofile where picVer>0 and picVer<1000000 limit $index,$page_limit;";
		//$sql = "select u.uid,u.name,u.picVer from userprofile u left join user_picture up on u.uid=up.uid where u.picVer>0 and u.picVer<1000000 and (u.picVer>up.picVer or up.picVer is null) limit $index,$page_limit;";
		//$sql="select uid,name,picVer,mod(picVer,1000000) modFlag from userprofile where picVer>2000000 and picVer<3000000 order by modFlag limit $index,$page_limit;";
		
		$sql="select u.name, p.imageVer, u.uid from friend_circle f inner join userprofile u on f.uid = u.uid inner join user_images p on p.userUid=f.uid where p.imageType = 0 order by f.sendTime desc limit $index,$page_limit;";

		$result = $page->executeServer($server, $sql, 3);
		if($result['error'] || (!$result['ret']['data'])){
			exit();
		}
		$sqlDatas=array();
		foreach ($result['ret']['data'] as $row){
			$te=array();
			$te['server']=$server;
			$te['uid']=$row['uid'];
			$te['name']=$row['name'];
			$te['picVer']=($row['imageVer']);
			$sqlDatas[]=$te;
		}
		//$sqlDatas = $result['ret']['data'];
// 	}
	$html .= "<div style='float:left;width:100%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$line='';
	for ($i = 0; $i < count($sqlDatas); $i++) {
		$ext = ".jpg";
		$uid=$sqlDatas[$i]['uid'];
		$name=str_replace("'", '&#39;', $sqlDatas[$i]['name']);
		$name=str_replace(">", '&gt;', $name);
		$name=str_replace("<", '&lt;', $name);
		$seq=$sqlDatas[$i]['picVer'];
		$file_folder = substr($uid, -6);
		$file_name = md5($uid . '_' . $seq) . $ext;
		
		if ($i%10 == 0) {
			//newline;
			$line.="</tr><tr>";
		}
		$line.="<td><label for='picture_".$sqlDatas[$i]['server']."_{$uid}_{$seq}'><img src='http://coq.eleximg.com/coq/img/fm/$file_folder/$file_name'  style='width:120px;height:120px;' ></label><br>
					<input class='' type='checkbox' name='".$name."' id='picture_".$sqlDatas[$i]['server'].'_'.$uid.'_'.$seq."' onClick='checkPictureAll()' />$name</td>";
	}
	$cnt = count($sqlDatas);
	if ($cnt % 10 != 0) {
		$add = 10 - $cnt%10;
		for ($j=1;$j<=$add;$j++){
			$line.="<td></td>";
		}
	}
	$line=substr($line, 5);
	$line.="</tr>";
	$line.="<tr><td colspan='10'><input type='checkbox' id='all_picture' name='all_picture'  onClick='pictureAll()'  />all &nbsp;&nbsp;<input class='btn js-btn btn-primary' type='button' onclick='picConfirm()' id='btn_confirm' name='btn_confirm' value='OK'>
						&nbsp;&nbsp;<input class='btn js-btn btn-primary' type='button' onclick='picDelete()' id='btn_delete' name='btn_delete' value='删除'></td></tr>";
	$html.=$line;
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . '<br />'.$pager['pager'] . "</div>";
	echo $html;
	exit();
}
if($_REQUEST['event']=='delete'){
	$pictureStr=$_REQUEST['pictureStr'];
	$pictureStr=trim($pictureStr,'|');
	$temp=explode('|', $pictureStr);
	foreach ($temp as $info){
		$temp2=explode('_', $info);
		$server=$temp2[0];
		$uid=$temp2[1];
		$uidArray[]=$uid;
	}
	$uids=implode(',', $uidArray);
	$returnUids = $page->webRequest ( "refreshpicver", array('uidStr' => $uids, 'gmName'=>$_COOKIE ['u'],'action'=>'reject'), $server );
	if (!$returnUids) {
		exit ( "调用Java接口删除图片失败!" );
	}
	adminLogUser($_COOKIE ['u'],$returnUids,$server,array('pictureStr'=>$pictureStr));
	
	
	$returnUidArray=explode(',', $returnUids);
	foreach ($returnUidArray as $uid){
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
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0,102)";
		$result2 = $page->executeServer ( $server, $sql, 2 );
		sendReward2 ( $mailUid, $server );
	}
	exit('审核未通过，已向玩家发送邮件通知!');
}
if($_REQUEST['event']=='confirm'){
	$pictureStr=$_REQUEST['pictureStr'];
	$pictureStr=trim($pictureStr,'|');
	$temp=explode('|', $pictureStr);
	foreach ($temp as $info){
		$temp2=explode('_', $info);
		$server=$temp2[0];
		$uid=$temp2[1];
		$picVer=$temp2[2];
		$uidArray[]=$uid;
		$upArray[$uid]=$picVer;
	}
	$uids=implode(',', $uidArray);
	$returnUids=$page->webRequest ( "refreshpicver", array('uidStr' => $uids, 'gmName'=>$_COOKIE ['u'],'action'=>'pass'), $server );
	//exit($returnUids);
	if (!$returnUids) {
		exit ( "调用Java接口审核图片失败!" );
	}
	adminLogUser($_COOKIE ['u'],$returnUids,$server,array('pictureStr'=>$upArray));
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
