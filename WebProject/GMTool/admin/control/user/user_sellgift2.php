<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());
$countType = array('all',0.99,4.99,9.99,19.99,24.99,49.99,99.99);
foreach ($countType as $key=>$value){
	$options .= "<option id={$key}>{$value}</option>";
}

$lang = loadLanguage();
$exchageXml = loadXml('exchange','exchange');
$databaseXml = loadXml('goods','goods');
$exchangeName = require ADMIN_ROOT . '/etc/packageArray.php';
ksort($exchangeName);

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
	$packageId = $_REQUEST ['packageId'];
	$giftType = $_REQUEST['countType'];


//	print_r($m_dateLink);
//	echo "<br/>";
	$wherepackage = "1=1 ";
	if($packageId){
		$wherepackage .= " and productId=$packageId";
	}
	if($giftType && $giftType != 'all'){
		$wherepackage .= " and spend='$giftType' ";
	}
	$m_allserver_data=array();
	$i=0;
	foreach($selectServerids as $m_sid) {
		if($m_sid == 0) continue;
		$sql = "select productId ,count(1) num,count(DISTINCT uid) people from snapshot_s$m_sid.paylog where $wherepackage and time >= $start and time < $end group by productId ASC; ";
//		if($i==0){
//			echo $sql;
//			echo "\n";
//			++$i;
//		}
		$displayResult = query_infobright($sql);
		foreach ($displayResult['ret']['data'] as $disRow){
			if(array_key_exists($disRow['productId'],$m_allserver_data)) {
				$m_allserver_data[$disRow['productId']][0] += $disRow['num'];
				$m_allserver_data[$disRow['productId']][1] += $disRow['people'];
			}else {
				$m_allserver_data += array($disRow['productId'] => array($disRow['num'],$disRow['people']));
			}
		}
	}

	$disHtml1 = "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$disHtml1 .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$disHtml1 .="<tr>
				<th bgcolor=\"#00FF00\"><a href='#' onclick=\"sort_table(1, 0, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">礼包ID</a></th>
				<th width= 110>名称package</th>
				<th width= 110>名称client</th>
				<th bgcolor=\"#00FF00\"><a href='#' onclick=\"sort_table(1, 3, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">单价</a></th>
				<th bgcolor=\"#00FF00\"><a href='#' onclick=\"sort_table(1, 4, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">销量</a></th>
				<th bgcolor=\"#00FF00\"><a href='#' onclick=\"sort_table(1, 5, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">总额</a></th>
				<th bgcolor=\"#00FF00\" width= 80><a href='#' onclick=\"sort_table(1, 6, asc1); asc1 *= -1; asc2 = 1; asc3 = 1;\">人数(去重)</a></th>
				<th >内容</th>
				</tr>";
	$disHtml1 .= "<tbody id='adDataTable'>";
//	print_r($exchangeName);
	foreach ($m_allserver_data as $id=>$num_people) {
		$num=$num_people[0];
		$content = getContentByGiftId($id);
		$allmoney = $num* $exchangeName[$id][1];
		$clientname = $lang[(int)$exchageXml[$id]['name']];
		$disHtml1 .= "<tr><td>$id</td><td>".$exchangeName[$id][0]."</td><td>".$clientname."</td><td>".$exchangeName[$id][1]."</td><td>$num</td><td>$allmoney</td><td> $num_people[1]   </td><td>$content</td></tr>";

	}

	$disHtml1 .="</tbody></table></div>";
	echo $disHtml1;
	exit();
}


function getContentByGiftId($id){
	global $exchageXml,$databaseXml,$lang;

	$zh_CN = $exchageXml[$id]['item'];

	$goods_name = explode('|',$zh_CN);
	$items=array();
	foreach($goods_name as $goods){
		$goods = explode(';',$goods);
		$name = $goods[0];
//		$html = $databaseXml[$name];
		$items[] = $lang[(int)$databaseXml[$name]['name']]."-".$goods[1];
	}
//	echo "进来获取内容函数"."<br/>";
	$htmltmp = null;
	foreach($items as $value) {
		if($value=='-'){
			$htmltmp .=  '此礼包不包含物品 ';
			break;
		}else {
			$htmltmp .=  $value.'_' ;
		}
	}
//	foreach($goods_name as $_value){ //英文
//		if($_value==null){
//			$html .= "<td>" . '此礼包不包含物品 '. "</td>";
//		}else {
//			$html .= "<td>" . $_value . "</td>";
//		}
//	}
//	echo $htmltmp."<br/>";
	return $htmltmp;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>