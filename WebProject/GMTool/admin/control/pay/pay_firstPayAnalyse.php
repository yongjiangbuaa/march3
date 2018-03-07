<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];
if ($_REQUEST ['analyze'] == 'platform') {
	if($_REQUEST['start']){
		$start = strtotime($_REQUEST['start'])*1000;
	}else{
		$start = strtotime($start)*1000;
	}

	if($_REQUEST['end']) {
		$end = strtotime($_REQUEST['end']) * 1000+ 86400000;
	}else{
		$end = strtotime($end) * 1000+ 86400000;
	}
	$m_total = array();
	foreach($selectServerids as $m_sid) {
		if ($m_sid == 0) continue;
		else $num = $m_sid;
		$sql = "select  $num sid,p.uid as uid,p.productId as productId ,p.spend as spend ,date_format(from_unixtime(min(p.time)/1000),'%Y-%m-%d') as buytime ,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regtime from paylog p inner join stat_reg r on r.uid=p.uid group by uid having min(p.time)>$start and min(p.time)<$end order by p.productId";

		$server = 's'.$num;
		$result = $page->executeServer($server, $sql, 3);
		foreach ($result['ret']['data'] as $disRow) {
			//一条数据
			$m_oneline = array();
			$m_oneline['sid'] = $disRow['sid'];
			$m_oneline['uid'] = $disRow['uid'];
			$m_oneline['productId'] = $disRow['productId'];
			$m_oneline['spend'] = $disRow['spend'];
			$m_oneline['buytime'] = $disRow['buytime'];
			$m_oneline['regtime'] = $disRow['regtime'];

			$m_total[] = $m_oneline;
		}
	}
//	echo "$sql";
//	print_r($m_total);
	if(!count($m_total)) {
		echo "<br/>";
		echo "没有数据";
		exit();
	}
	$disHtml1 = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$disHtml1 .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$disHtml1 .="<tr>
				<th><a href='#' onclick=\"sort_table(1, 0, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">sid</a></th>
				<th>uid</th>
				<th >礼包id</th>
				<th>花费</th>
				<th>购买时间</th>
				<th>注册时间</th>
				</tr>";
	$disHtml1 .= "<tbody id='adDataTable'>";

		foreach ($m_total as $i=>$item) {
			if(!$item['uid']){
				echo "没有数据";
				exit();
			}

			$disHtml1 .= "<tr><td>" .$item['sid']."</td><td>" .$item['uid']."</td><td>".$item['productId']."</td><td>".$item['spend']."</td><td>".$item['buytime']."</td><td>".$item['regtime']."</td></tr>";
		}

	$disHtml1 .="</tbody></table></div>";
	echo $disHtml1;
	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>