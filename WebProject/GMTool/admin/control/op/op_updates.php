<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
if (array_key_exists ( 'type', $_REQUEST )) {
	$type = $_REQUEST ['type'];
}
$page = new BasePage();
$host = gethostbyname(gethostname());
if ($host == 'IPIPIP' || $host == 'IPIPIP') {
	$mysqli = new mysqli('URLIP', 'root', 'admin123', 'cokdb1');
}else{
	$mysqli = new mysqli('10.142.9.80', 'root', 'admin123', 'cokdb1');
}

if($type == 'refresh_version'){
	$sql = "select distinct(uid) from server_update order by uid desc";
	$result=$mysqli->query($sql);
	$data = array();
	if ($result) {
         if($result->num_rows>0){                                               //判断结果集中行的数目是否大于0
                  while($row =$result->fetch_array() ){                        //循环输出结果集中的记录
	    			$data[] = $row['uid'];
                  }
         }
	}
$result->free();
$mysqli->close();
	echo json_encode($data);
	return;	
}else if($type == 'refresh_items'){
	$version = $_REQUEST['version'];
	$sql = "select * from server_update where uid ='".$version."'";
	
	$result=$mysqli->query($sql);
	$data = array();
	if ($result) {
         if($result->num_rows>0){                                               //判断结果集中行的数目是否大于0
                  while($row =$result->fetch_array() ){                        //循环输出结果集中的记录
	    			$item = array();
	    			$uid=$row['uid'];
	    			$item['version'] = $uid;
	    			$col = $row['idx'];
	    			$item['idx'] = $col;
				if(!empty($row['chineseContent'])){
					$item['chineseContent'] = $row['chineseContent'];
				}
	    			$item['content'] = $row['content'];
	    			$innerResult = $mysqli->query("select count(*) as num  from server_update_suggestion where updateId='$uid' and idx=$col");
	    			$innerRow = $innerResult->fetch_array();
	    			$item['num'] = $innerRow['num'];
	    			$item['endTime'] = $row['endTime'];
	    			$data[] = $item;
                  }
         }
	}
	$result->free();
	$mysqli->close();
	echo json_encode($data);
	return;
	
}else if($type == 'suggestion'){
	$version = $_REQUEST['version'];
	$idx = $_REQUEST['idx'];
	$sql = "select * from server_update_suggestion where updateId='".$version."' and `idx` =".$idx." order by createTime";
	global $servers;
	$data = array();
	$result=$mysqli->query($sql);
	if ($result) {
         if($result->num_rows>0){                                               
                  while($row =$result->fetch_array() ){                        
	    				$item = array();
	    				$userid=$row['uid'];
	    				$item['userId'] = $userid;
	    				$item['updateId'] = $row['updateId'];
	    				$item['idx'] = $row['idx'];
	    				$item['real_name'] = $row['real_name'];
	    				$item['server'] = $row['server'];
	    				$item['content'] = $row['suggestion'];
	    				$innerSql = "select * from server_update su left join server_update_suggestion sus on su.idx = sus.idx and su.uid = sus.updateId and sus.uid='$userid' where su.uid='$version'";
	    				$innerResult=$mysqli->query($innerSql);
	    				$content = "用户UID：".$userid."\n============================================\n";
                 		if ($innerResult) {
         					if($innerResult->num_rows>0){                                               
                 				 while($row =$innerResult->fetch_array() ){                        
	    							$content.= $row['content']."\n反馈：".$row['suggestion']."\n============================================\n"; 
                  				}
         					}
						}
						$item['all'] = $content;
						$data[] = $item;
						$innerResult->free();
                  }
         }
	}
	$result->free();
	$mysqli->close();
	echo json_encode($data);
	return;
	
}else if($type == 'list_player'){
	$version=$_REQUEST['version'];
	$sql="select distinct(uid), createTime, reward_status from server_update_suggestion where updateId='$version' order by createTime desc";
	
	$result=$mysqli->query($sql);
	$data = array();
	if ($result) {
         if($result->num_rows>0){                                               //判断结果集中行的数目是否大于0
                  while($row =$result->fetch_array() ){                        //循环输出结果集中的记录
                  	$item = array();
                  	$item['uid'] = $row['uid'];
                  	$item['createTime'] = $row['createTime'];
                  	$item['reward_status'] = $row['reward_status'];
	    			$data[] = $item;
                  }
         }
	}
	$result->free();
	$mysqli->close();
	echo json_encode($data);
	return;
	
}else if($type == 'player'){
			$userid=$_REQUEST['uid'];
			$version=$_REQUEST['version'];
			$item = array();
	    	$item['userId'] = $userid;
	    	$item['updateId'] = $version;
	    	$innerSql = "select * from server_update su left join server_update_suggestion sus on su.idx = sus.idx and su.uid = sus.updateId and sus.uid='$userid' where su.uid='$version'";
	    	$innerResult = $mysqli->query($innerSql);
	    	$content = "用户UID：".$userid."\n============================================\n";
			if ($innerResult) {
        		if($innerResult->num_rows>0){                                               //判断结果集中行的数目是否大于0
                  while($row =$innerResult->fetch_array() ){                        //循环输出结果集中的记录
	    			$real_name = $item['real_name'];
	    			$real_server = $item['server'];
	    			if(empty($real_name))	$item['real_name'] = $row['real_name'];
	    			if(empty($real_server))	$item['server'] = $row['server'];
	    			$item['content'] = $row['suggestion'];
	    			$content.= $row['content']."\n反馈：".$row['suggestion']."\n============================================\n";
                  }
         		}
			}
			$item['all'] = $content;
			$innerResult->free();
			$mysqli->close();
			echo json_encode($item);
			return;
}else if($type == 'add'){
	$version = $_REQUEST['version'];
	$replaceSql = "replace into server_update (uid,idx,content,chineseContent) values ";
	$idx = array();//存储哪几个条目不变或更新了
	foreach($_REQUEST as $key=>$val){
		if(substr($key, 0, 4) == 'item' && !empty($val)){
            $itemContent = explode("|", $val);
			$english = addslashes($itemContent[0]);
            $chinese = addslashes($itemContent[1]);
			$index = substr($key, 4, 5);
			if(count($idx) > 0) $replaceSql.=",";
			$replaceSql.="('".$version."',".$index.",'".$english."','".$chinese."')";
			$idx[] = $index;
		}
	}
	if(count($idx) > 0){
		//删除没更新的条目
		$deleteSql = "delete from server_update where uid='".$version."' and idx not in (";
		for($i = 0; $i < count($idx); $i++){
			if($i == count($idx)-1){
				$deleteSql.= ($idx[$i].")");
			}else{
				$deleteSql.= ($idx[$i].",");
			} 
		}
		$mysqli->query($replaceSql);
		$mysqli->query($deleteSql);
	}else{
		$deleteSql = "delete from server_update where uid='".$version."'";
		$mysqli->query($deleteSql);
	}
	$endTime = strtotime($_REQUEST ['end_time'])*1000;
	$updateSql = "update server_update set endTime = ".$endTime." where uid='".$version."'";
	$mysqli->query($updateSql);
	$mysqli->close();
}else if($type == 'reward'){
	$toUser= $_REQUEST['userid'];
	$userName=addslashes($_REQUEST['realname']);
	$serverId = $_REQUEST['server'];
	$updateId = $_REQUEST['version'];
	$endTime = $_REQUEST['end_time'];
	$targetServer = "s".$serverId;
	$level = $_REQUEST['level'];
	if($level == 0){
		$level = 4;
	}
	$sendBy = $page->getAdmin();
	$ret = array();
		$querySql = "select * from userprofile where name='$userName'";
		$result = $page->executeServer($targetServer, $querySql, 3);
		if(!$result['ret'] || !isset($result['ret']['data'])){
			$ret['stat']="查无此人";			
		}else{
			$real_user_id=$result['ret']['data'][0]['uid'];
			$querySql = "select reward_status from server_update_suggestion where uid='$toUser' and updateId='$updateId'";
			$result = $mysqli->query($querySql);
			$reward = false;
			while($row =$result->fetch_array() ){  
				if($row['reward_status'] != 0){
					$reward = true;
					break;
				}
			}
			$result->free();
			if($reward == true){
				$ret['stat'] = "已经奖励过";
			}else{
				$sendTime = microtime(true)*1000;
				$rewardStatus = 1;
				$sql = "insert into reward_107 values ('$toUser', '$updateId', $level, 0, $endTime)";
				$page->executeServer($targetServer, $sql,2);
				$sql = "update server_update_suggestion set reward_status=$level where uid='$toUser' and updateId='$updateId'";
				$mysqli->query($sql);
				$ret['stat']="成功";			
			}
		}
		$mysqli->close();
	echo json_encode($ret);
	return;
}else if($type == 'test'){
	return;
}

include( renderTemplate("{$module}/{$module}_{$action}"));
?>
