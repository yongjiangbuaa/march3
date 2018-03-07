<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
$levelMin = $_REQUEST['levelMin'];
$levelMax = $_REQUEST['levelMax'];
$buildMin = $_REQUEST['buildMin'];
$buildMax = $_REQUEST['buildMax'];
$regDate = $_REQUEST['regDate'];
if (isset($_REQUEST['getData'])) {
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	$regDateTime = (time() - $regDate*86400) * 1000;
	//总人数
	$sql = "SELECT COUNT(DISTINCT(user)) userNum ,COUNT(1) times from logstat where type =2 and ( param1=2 or param1=7 ) and `timeStamp`>= $start and  `timeStamp` <= $end";
	$result = $page->execute($sql,3);
	$totalPeople = $result['ret']['data'][0]['userNum'];
	$totalTimes = $result['ret']['data'][0]['times'];
	
	$sql = "SELECT param1,data1,COUNT(DISTINCT(user)) userNum ,COUNT(1) times from logstat 
			 where type =2 and ( param1=2 or param1=7 ) and `timeStamp`>= $start and `timeStamp` <= $end 
			 GROUP BY param1,data1 ";
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	$sql = "SELECT (productId - 8889)%11 as type,COUNT(DISTINCT(uid)) as userNum,COUNT(1) as times  from paylog where time>=$start and time<=$end and  productId in(9010,9000,9001,9002,9003,9004,9005)
 GROUP BY productId ";
	$payresult = $page->execute($sql,3);
	$payresult = $payresult['ret']['data'];
	$payInfo = array();
	foreach ($payresult as $item){
		$payInfo[$item['type']] = $item;
	}
	$GoldType = array('点击促销购买按钮','点击第一档','点击第二档','点击第三档','点击第四档','点击第五档','打开促销弹框');
	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<h3> 共 $totalPeople 人, 打开 ".$totalTimes."次！</h3>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr><td>序号</td><td>统计类型</td><td>玩家数量</td><td>次数</td><td>最终购买人数</td><td>最终购买次数</td></tr>";
	foreach ($result as $key=>$curRow)
	{
		$key++;
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html.= "<td>$key</td>";
		if($curRow['param1'] == 2){
			$type= ($curRow['data1'] - 110) %11;
		}
		elseif($curRow['param1'] == 7){
			$type = 6;
		}
		$html.= '<td>'.$GoldType[$type].'</td><td>'.$curRow['userNum'].'</td><td>'.$curRow['times'].'</td><td>'.$payInfo[$type]['userNum'].'</td><td>'.$payInfo[$type]['times'].'</td></tr>';
	}
	$html .= "</table></div><br/>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>