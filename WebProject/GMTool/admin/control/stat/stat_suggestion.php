<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59:59",time());
$selectServer = explode(':',$_REQUEST['server']);
$server = $selectServer[0];
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
}
if ($_REQUEST['analyze']=='user') {
	$start = $_REQUEST['start']?strtotime($_REQUEST['start']):0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end']):strtotime($end);
	$start *= 1000;
	$end *= 1000;
	$sql = "select count(*) DataCount from suggestion where createTime >= $start && createTime <= $end";
	$result = $page->executeServer($server, $sql, 3);
	$count = $result['ret']['data'][0]['DataCount'];
	$page = new BasePage();
	$page_limit = 100;
	$pager = page($count, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];
	$sql = "select * from suggestion where createTime >= $start && createTime <= $end order by createTime DESC limit $index,$page_limit";
	$result = $page -> executeServer($server, $sql, 3);
	$cursor = $result['ret']['data'];
	$page = new BasePage();
	foreach ($cursor as $curRow)
	{
		$data = $curRow;
		$logItem['玩家UID'] = $data['ownerId'];
		$logItem['内容'] = $data['contents'];
		$logItem['创建时间'] = date('Y-m-d H:i:s',$data['createTime'] / 1000);
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	foreach ($log as $index => $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'><th>编号</th>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "<th>" . '功能' . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr id=\"$index\" class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		foreach ($sqlData as $key=>$value){
			if ($key == '玩家UID') {
				$html .= "<td id=\"uid\"><a href=\"javascript:void(showUid('$index', '$value', true))\">查看</a></td>";
			} else {
				$html .= "<td>" . $value . "</td>";
			}
		}
		$html .= '<td><input type="button" value="删除" onClick='."deleteOne('{$cursor[$index]['uid']}')".'></td>';
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>