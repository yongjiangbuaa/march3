<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;
$lang = loadLanguage();
$clintXml = loadXml('goods','goods');

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$sids = implode(',', $selectServerids);
	$goods = array();
	$sql="select buyTime, goodsId, price, priceType, sum(num) numc,sum(people) peoples from stat_allserver.stat_hot_goods_cost_record2 where buyTime between $sDdate and $eDate and sid in($sids) group by buyTime,goodsId,price order by buyTime desc,priceType,price,numc desc,peoples desc;";


	$ret=query_infobright($sql);
	foreach ($ret['ret']['data'] as $corRow){
		$arr = explode(";",$corRow['goodsId']);
		$priceType = formatItem($corRow['priceType']);
		$goodItem = formatItem($arr[0]);
		$goodsId = $corRow['goodsId'].";".$corRow['price'];
		$goodsDesc = $goodItem."*".$arr[1];
		if(!isset($goods[$corRow['buyTime']][$goodsId]['goodsId'])){
			$goods[$corRow['buyTime']][$goodsId]['goodsId'] = $goodsDesc;
		}
		if(!isset($goods[$corRow['buyTime']][$goodsId]['priceType'])){
			$goods[$corRow['buyTime']][$goodsId]['priceType'] = $priceType;
		}
		if(!isset($goods[$corRow['buyTime']][$goodsId]['price'])){
			$goods[$corRow['buyTime']][$goodsId]['price'] = $corRow['price'];
		}
		$goods[$corRow['buyTime']][$goodsId]['num'] += $corRow['numc'];
		$goods[$corRow['buyTime']][$goodsId]['peopleNum'] += $corRow['peoples'];
//		$goods[$corRow['buyTime']][$goodsId]['refreshTimes'] += $corRow['refreshTimes'];
	}

	$html = "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
	$html .= "<th>日期</th>";
	$html .= "<th>商品</th>";
	$html .= "<th>价格类型</th>";
	$html .= "<th>价格</th>";
	$html .= "<th>购买数量</th>";
	$html .= "<th>购买人数</th>";
	$html .= "<th>总金额</th>";
//	$html .= "<th>刷新次数</th>";
	$html .= "</tr>";
	foreach ($goods as $timeKey=>$sqlData){
		foreach ($sqlData as $goodsIdValue=>$value){
			$html .="<tr><td>$timeKey</td>";
			$html .="<td>".$value['goodsId']."</td>";
			$html .="<td>".$value['priceType']."</td>";
			$html .="<td>".$value['price']."</td>";
			$html .="<td>".$value['num']."</td>";
			$html .="<td>".$value['peopleNum']."</td>";
			$total=intval($value['price']*$value['num']);
			$html .="<td>".$total."</td>";
//			$html .="<td>".$value['refreshTimes']."</td>";
			$html .="</tr>";
		}
	}
	$html .= "</table>";
}

function formatItem($item){
	global $lang,$clintXml;
	$goodItem = $lang[(int)$clintXml[$item]['name']];
	if((int)$item == 0 ){
		$goodItem = "木头";
	}else if((int)$item == 1){
		$goodItem = "秘银";
	}else if((int)$item == 2){
		$goodItem = "铁矿";
	}else if((int)$item == 3){
		$goodItem = "粮食";
	}else if((int)$item == 4){
		$goodItem = "钢材";
	}else if((int)$item == 5){
		$goodItem = "金币";
	}
	return $goodItem;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>