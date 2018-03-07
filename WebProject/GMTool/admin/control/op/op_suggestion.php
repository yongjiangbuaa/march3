<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59:59",time());
$selectServer = explode(':',$_REQUEST['server']);
$server = $selectServer[0];
$page = new BasePage();
if($_REQUEST['remove'] == 'all') {
	$early = (time() - 15 * 24 * 3600) * 1000;
	$sql = "delete from suggestion where createTime < {$early}";
	$result = $page->executeServer($server,$sql,2);
	$_REQUEST['analyze'] = 'user';
} else if($_REQUEST['remove'] == 'one') {
	$uid = $_REQUEST['uid'];
	$sql = "delete from suggestion where uid = '$uid'";
	$result = $page->executeServer($server,$sql,2);
	$_REQUEST['analyze'] = 'user';
} else if($_REQUEST['remove'] == 'select'){
	$uidStr = '('.trim($_REQUEST['uid'],',').')';
	$sql = "delete from suggestion where uid in $uidStr";
	$result = $page->executeServer($server,$sql,2);
	$_REQUEST['analyze'] = 'user';
}
if ($_REQUEST['analyze']=='user') {
	$start = $_REQUEST['start']?strtotime($_REQUEST['start']):0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end']):strtotime($end);
	$start *= 1000;
	$end *= 1000;
	$sql = "select count(*) DataCount from suggestion where createTime >= $start && createTime <= $end";
	$result = $page->executeServer($server, $sql, 3);
	$count = $result['ret']['data'][0]['DataCount'];
	$page_limit = 100;
	$pager = page($count, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];
	$sql = "select s.*,u.name,u.appVersion from suggestion s LEFT JOIN userprofile u on s.ownerId=u.uid where createTime >= $start && createTime <= $end order by createTime DESC limit $index,$page_limit";
	$result = $page -> executeServer($server, $sql, 3);
	$cursor = $result['ret']['data'];
	foreach ($cursor as $curRow)
	{
		$data = $curRow;
		$logItem['玩家UID'] = $data['ownerId'];
		$logItem['玩家名称'] = '<a href="javascript:void(showList(\''. $data['ownerId'].'\'))">'.$data['name'].'</a>';
		$logItem['玩家版本'] = $data['appVersion'];
		$logItem['玩家大本等级'] = $data['castlelevel'];
		$logItem['内容'] = $data['contents'];
		$logItem['创建时间'] = date('m-d H:i',$data['createTime'] / 1000);
		$logItem['回复内容'] = $data['replyContent'];
		$logItem['回复时间'] = $data['replyTime'] ? date('m-d H:i',$data['replyTime'] / 1000) : "无";
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	foreach ($log as $index => $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'>";
			if($adminid == 'liuwen' || $group_id){
				$html .= '<th>全选</th>';
			}
			$html .= '<th>编号</th>';
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "<th>" . '功能' . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr id=\"$index\" class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		if($adminid == 'liuwen' || $group_id){
			$html .= "<td><input type='checkbox' class='mailclass' name='mail$i' value=".$cursor[$index]['uid']."></input></td>";
		}		
		$html .= "<td>$i</td>";
		$i++;
		foreach ($sqlData as $key=>$value){
			if ($key == '玩家UID') {
				$html .= "<td id=\"uid\"><a href=\"javascript:void(showUid('$index', '$value', true))\">查看</a></td>";
			}else if($key == '内容' || $key == '回复内容'){
				$html .= "<td style='width:30%;' id='reply_".$cursor[$index]['uid']."'>" . $value . "</td>";
			}else {
				$html .= "<td>" . $value . "</td>";
			}
		}
		$html .= '<td><input type="button" value="回复" onclick="op_relpy(\''.$cursor[$index]['ownerId']."','".$cursor[$index]['uid']."','".'\')" /></td>';
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
if($_REQUEST['analyze']=='showList'){
	$uid = $_REQUEST['uid'];
	$sql = "select s.*,u.name,u.level,u.appVersion,u.paidGold from suggestion s LEFT JOIN userprofile u on s.ownerId=u.uid where s.ownerId = $uid order by s.createTime desc limit 999";
	$result = $page -> executeServer($server, $sql, 3);
	$cursor = $result['ret']['data'];
	$html = "<div style='float:left;width:100%;max-height:500px;text-align:center;overflow-x:auto;overflow-y:auto;'>"
			."<table class='showlistTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'><tr>"
			."<td>玩家信息".'</td><td>名称:'.$cursor[0]['name'].' &nbsp;&nbsp;&nbsp;UID:'.$cursor[0]['ownerId'].'&nbsp;&nbsp;&nbsp;级别' .$cursor[0]['level'].' &nbsp;&nbsp;&nbsp;充值金币:'.$cursor[0]['paidGold']
	."<button class='btn btn-mini btn-primary' onclick='showList()' style='float:right;' >关闭</button></td></tr>";
	foreach ($cursor as $key => $value){
		$html .='<tr><td>'.$value['name'].' '.date(' Y-m-d H:i',$value['createTime'] / 1000).'建议</td><td>'.$value['contents'].'</td></tr>';
		if($value['replyContent']){
			$html .='<tr><td>'.date('Y-m-d H:i',$value['replyTime'] / 1000).' 回复 </td><td>'.$value['replyContent'].'</td></tr>';
		}else{
			$html .='<tr><td>无回复 </td><td>无回复 </td></tr>';
		}
	}
	$html .= '</table></div>';
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>