<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );

if ($_GET ['ispost']) {
	$html;
	if(!$_REQUEST['start']){
		$s = time() - 7 * 86400;
	}else{
		$s = strtotime($_REQUEST ['start']);		
	}
	if(!$_REQUEST['end']){
		$e = time();
	}else{
		$e = strtotime($_REQUEST ['end']);		
	}
	
	global $servers;
	$serverStr;
	foreach ($_REQUEST as $server=>$value)
	{   
		if($servers[$server] && $value == 'on'){
			$selectServer[] = $server;
		}
	}
	
	$html = "<div style='float:left;width:100%;height:300px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
						  	$html .= "<th>日期</th>";
						  	$html .= "<th>商品</th>";
						  	$html .= "<th>价格类型</th>";
						  	$html .= "<th>价格</th>";
						  	$html .= "<th>购买数量</th>";
						  	$html .= "<th>购买人数</th>";
							$html .= "</tr>";
	$lang = loadLanguage();
	$clientXml = loadXml('goods','goods');
for($day = $s; $day < $e; $day+=86400){
	$d = date('Ymd', $day);
	$goods = array();
	foreach ($selectServer as $server){
	        try {
				$sql = "select buyTime, goodsId, price, priceType, count(*) num from hot_goods_cost_record where buyTime = ".$d." group by goodsId order by num desc";
	        	$result = $page->executeServer($server,$sql,3);
	         	if($result['ret']&&isset($result['ret']['data'])){
	                $sqlData = $result['ret']['data'];
	                $goodsId;
	                $price;
	                foreach ($sqlData as $row){
	                		$arr = explode(";",$row['goodsId']);
	                		$priceType = formatItem($row['priceType']);
	                		$goodItem = formatItem($arr[0]); 
	                		$goodsId = $row['goodsId'].";".$row['price'];
							$goodsDesc = $goodItem."*".$arr[1];
							if(!isset($goods[$goodsId]['goodsId'])) $goods[$goodsId]['goodsId'] = $goodsDesc;
							if(!isset($goods[$goodsId]['priceType'])) $goods[$goodsId]['priceType'] = $priceType;
							if(!isset($goods[$goodsId]['price'])) $goods[$goodsId]['price'] = $row['price'];
							if(!isset($goods[$goodsId]['num'])) $goods[$goodsId]['num'] = $row['num'];
							else $goods[$goodsId]['num'] += $row['num'];
  							$sql2 = "select count(distinct uid) people from hot_goods_cost_record where buyTime=".$d." and goodsId='".$row['goodsId']."' and price=".$row['price'].";";
  							$result2 = $page->executeServer($server,$sql2,3);
  							$sqlData2 = $result2['ret']['data'];
  							$peopleNum = $sqlData2[0]['people'];
  							if(!isset($goods[$goodsId]['peopleNum'])) $goods[$goodsId]['peopleNum'] = $peopleNum;
							else $goods[$goodsId]['peopleNum'] += $row['people'];
	                 }
	             }
	        } catch ( Exception $e ) {
	            $html .= $e->getMessage ();
	        }
	}
	$html .= "<tr class='listTr'>";
							$i = 0;
							foreach($goods as $item){
								$i++;
								if($i == 1){
									$html .= "<th>".$d."</th>";								
								}else{
									$html .= "<th></th>";
								}
								$html .= "<th>".$item['goodsId']."</th>";
						  		$html .= "<th>".$item['priceType']."</th>";
						  		$html .= "<th>".$item['price']."</th>";
						  		$html .= "<th>".$item['num']."</th>";
						  		$html .= "<th>".$item['peopleNum']."</th>";
						  		$html .= "</tr>";								
							}
}
  	$html .= "</table><br/>";
	$jsonResult = array(
						"content" => $html,
						);
	$response = json_encode($jsonResult);
	echo $response;
	return;
}
else if($_GET['requestdata']){
	if(!$_REQUEST['start']){
		$s = time() - 7 * 84600;
	}else{
		$s = strtotime($_REQUEST ['start']);		
	}
	if(!$_REQUEST['end']){
		$e = time();
	}else{
		$e = strtotime($_REQUEST ['end']);		
	}
	global $servers;
	$serverStr;
	$lang = loadLanguage();
	$clientXml = loadXml('goods','goods');
	foreach ($_REQUEST as $server=>$value)
	{   
		if($servers[$server] && $value == 'on'){
			$selectServer[] = $server;
		}
	}
	$dataArr = array(); 
	$i = 0;
	foreach ($selectServer as $server){
		$data = array('server'=> $server);
			for($day = $s; $day < $e; $day+=84600){
				$d = date('Ymd', $day);
				$dataToday = array();
				//当天各种商品购买了多少次
				$sql = "select buyTime, goodsId, price, priceType, count(*) num from hot_goods_cost_record where buyTime = ".$d." group by goodsId order by num desc";
	        	$result = $page->executeServer($server,$sql,3);
	         	if($result['ret']&&isset($result['ret']['data'])){
	                $sqlData = $result['ret']['data'];
	                $goodsId;
	                $price;
	                foreach ($sqlData as $row){
	                	//对于每种商品，
	                	$sql2 = "select count(distinct uid) people from hot_goods_cost_record where buyTime=".$d." and goodsId='".$row['goodsId']."' and price=".$row['price'].";";
  						$result2 = $page->executeServer($server,$sql2,3);
  						$sqlData2 = $result2['ret']['data'];
	                		$newItem = array();
	                		$arr = explode(";",$row['goodsId']);
	                		$priceType = formatItem($row['priceType']);
	                		$goodItem = formatItem($arr[0]); 
							$goodsDesc = $goodItem."*".$arr[1].' '.$row['price'].$priceType;
							$newItem['goodsId'] = $goodsDesc;
	                		$newItem['priceType'] = $row['priceType'];
	                		$newItem['price'] = $row['price'];
	                		$newItem['num'] = $row['num'];
	                		$newItem['peopleNum'] = $sqlData2[0]['people'];	      
	                		$dataToday[] = $newItem;
	                 }
	             }
	             $data['d'.$d] = $dataToday;
			}
	    $dataArr[]=$data;
	}
	$jsonResult = array(
						"data" => $dataArr,
						);
	$response = json_encode($jsonResult);
	echo $response;
	return;
}
function formatItem($item){
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
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
include( renderTemplate("{$module}/{$module}_{$action}"));
?>