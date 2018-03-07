<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$lang = loadLanguage();
$clintXml = loadXml('goods','goods');
$type = $_REQUEST['action'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if ($type=='view') {
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$startTime = strtotime($sDdate)*1000;
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$endTime = strtotime($eDate)*1000;
	
	$sql="select uid,goodsId,priceType,price,buyTime from hot_goods_cost_record where uid='$useruid' and buyTime between $sDdate and $eDate order by buyTime desc,priceType,price;";
	$result = $page->execute($sql, 3);
	$i=1;
	$data=array();
	$numArray=array();
	$goldArray=array();
	foreach ($result['ret']['data'] as $curRow){
		$arr = explode(";",$curRow['goodsId']);
		$priceType = formatItem($curRow['priceType']);
		$goodItem = formatItem($arr[0]);
		$goodsDesc = $goodItem."*".$arr[1];
		$data[$i]['uid']=$curRow['uid'];
		$data[$i]['goodsId']=$goodsDesc;
		$data[$i]['priceType']=$priceType;
		$data[$i]['price']=$curRow['price'];
		$data[$i]['buyTime']=$curRow['buyTime'];
		$i++;
		if(!isset($numArray[$curRow['buyTime']][$goodItem])){
			$numArray[$curRow['buyTime']][$goodItem] =$arr[1];
		}else {
			$temp=$numArray[$curRow['buyTime']][$goodItem];
			$numArray[$curRow['buyTime']][$goodItem] =$temp+$arr[1];
		}
		if($curRow['priceType']==0){
			$goldArray[$curRow['buyTime']]['wood']+=$curRow['price'];
		}else if($curRow['priceType']==1){
			$goldArray[$curRow['buyTime']]['silver']+=$curRow['price'];
		}else if($curRow['priceType']==2){
			$goldArray[$curRow['buyTime']]['iron']+=$curRow['price'];
		}else if($curRow['priceType']==3){
			$goldArray[$curRow['buyTime']]['food']+=$curRow['price'];
		}else if($curRow['priceType']==4){
			$goldArray[$curRow['buyTime']]['steel']+=$curRow['price'];
		}else if($curRow['priceType']==5){
			$goldArray[$curRow['buyTime']]['gold']+=$curRow['price'];
		}
		
		
		/* if(!isset($goldArray[$curRow['buyTime']][$priceType])){
			$goldArray[$curRow['buyTime']][$priceType]=$curRow['price'];
		}else {
			$temp=$goldArray[$curRow['buyTime']][$priceType];
			$goldArray[$curRow['buyTime']][$priceType]=$temp+$curRow['price'];
		} */
	}
	
	$sql="select uid,goodsId,priceType,price,num,refreshTime,gold from hot_info_before_refresh where uid='$useruid' and refreshTime between $startTime and $endTime";
	$result = $page->execute($sql, 3);
	$refreshGoods=array();
	$good=array();
	foreach ($result['ret']['data'] as $curRow){
		$arr = explode(";",$curRow['goodsId']);
		$priceType = formatItem($curRow['priceType']);
		$goodItem = formatItem($arr[0]);
		$goodsDesc = $goodItem."*".$arr[1];
		$refreshGoods[$curRow['refreshTime']]['date']=$curRow['refreshTime']?date('Ymd',$curRow['refreshTime']/1000):0;
		$refreshGoods[$curRow['refreshTime']]['num']=$curRow['num'];
		$good[$curRow['refreshTime']][$curRow['goodsId']]['name']=$goodsDesc;
		$good[$curRow['refreshTime']][$curRow['goodsId']]['priceType']=$priceType;
		$good[$curRow['refreshTime']][$curRow['goodsId']]['price']=$curRow['price'];
		$refreshGoods[$curRow['refreshTime']]['gold']=$curRow['gold'];
	}
	//print_r($good);
	foreach ($refreshGoods as $retimeKey=>$value){
		$i=1;
		foreach ($good[$retimeKey] as $goodsIdKey=>$goodsInfo){
			$refreshGoods[$retimeKey]['goodsName'.$i]=$goodsInfo['name'];
			$refreshGoods[$retimeKey]['goodsPriceType'.$i]=$goodsInfo['priceType'];
			$refreshGoods[$retimeKey]['goodsPrice'.$i]=$goodsInfo['price'];
			$i++;
		}
	}
	//print_r($refreshGoods);
	
	if($data || $numArray || $goldArray || $refreshGoods){
		$showData=true;
	}else {
		$headAlert="数据查询失败";
	}
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