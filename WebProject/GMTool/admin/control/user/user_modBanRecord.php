<?php
!defined('IN_ADMIN') && exit('Access Denied');
$key = 'BAN_RECORD';
$start = $_REQUEST ['start'];
$startTime = strtotime($start)*1000;
$manager = $_REQUEST ['gmuser'];
//$serverId = $_REQUEST ['server'];
$badboy = $_REQUEST ['banuser'];
$end = $_REQUEST ['end'];
$endTime = strtotime($end)*1000;
//$endTime = (strtotime($end)+86400)*1000;
$redis = new Redis();
global $servers;
if(!$_COOKIE['Gserver2']){
	$_COOKIE['Gserver2'] = $_GET['Gserver'];
}
$currentServer = $_COOKIE['Gserver2'];
$currentIP = $servers[$currentServer]['ip_inner'];
if($_REQUEST['page']){
	$redis->connect($currentIP,6379);
	
   	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$num = $redis->lLen($key);
            if($num){
            	$html .= '<tr class="listTr"><th>编号</th><th>管理员ID</th><th>管理员</th><th>操作</th><th>用户ID</th><th>用户名D</th><th>时间</th></tr>';
            	$result = $redis->lRange($key, 0, -1);
            	$no=0;
                foreach ($result as $value){
                	$item = json_decode($value,true);
                	$banTime = $item['banTime'];
                	$operation = $item['operation'];
                	$banUid=$item['banUid'];
                	$banUser=$item['banUser'];
                	$gmUid=$item['gmUid'];
                	$gmUser=$item['gmUser'];
                	if($badboy){
                		if($badboy != $banUser) continue;
                	}
                	if($manager){
                		if($manager != $gmUser) continue;
                	}
                	if($end){
                		if($banTime > $endTime) continue;
                	}
                	if($start){
                		if($banTime < $startTime) break;
                	}
                    $no++;
                    $formatTime = date('Y-m-d H:i:s', $banTime/1000);
                    $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>".
                    "<td>{$no}</td><td>{$gmUid}</td><td>{$gmUser}</td><td>{$operation}</td><td>{$banUid}</td><td>{$banUser}</td><td>{$formatTime}</td></tr>";

                }
                $html .= '</table></div>';
            }
            else{
               $html = '数据为空！';
            }
//	$sql = "select uid, server, banTimes, operator, opTime from banWordRecord where 1=1 ";
//	if($start != null){
//		$sql .= "and opTime >= $startTime ";
//	}
//	if($end != null){
//		$sql .= "and opTime <= $endTime ";
//	}
//	if($manager != null){
//		$sql .= "and operator = '$manager' ";
//	}
//	if($badboy != null){
//		$sql .= "and uid = '$badboy' ";
//	}
//	$sql .= " order by opTime desc";
//	$result = $page->globalExecute($sql, 3);
//	$data = $result['ret']['data'];
//	if($data != null){
//		$html .= '<tr class="listTr"><th>管理员</th><th>用户ID</th><th>服务器Id</th><th>封禁次数</th><th>操作时间</th></tr>';
//		foreach($data as $each){
//			$opTime = date("Y-m-d H:i:s", $each['opTime']/1000);
//			$banTimes = $each['banTimes'] + 1;
//			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>".
//                    "<td>{$each['operator']}</td><td>{$each['uid']}</td><td>{$each['server']}</td><td>{$banTimes}</td><td>{$opTime}</td></tr>";
//		}
//		$html .= '</table></div>';
//	}else{
//		$html .= '</table></div>';
//	}
    echo $html;
exit();
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>