<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d 00:00",time()-86400*4);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d 00:00",time());

if($_REQUEST['itemId'])
	$itemId = $_REQUEST['itemId'];

$statType_title = array('物品增加','物品减少');
foreach ($statType_title as $key=>$value){
	$options .= "<option value='$key'>$value</option>";
}

$eventOptionsget = '<option>ALL</option>';
foreach ($GoodsGetType as $eventType => $eventName) {
	$eventOptionsget .= "<option value={$eventType}>{$eventName}</option>";
}
$eventOptionscost = '<option>ALL</option>';
foreach ($GoodsUseType as $eventType => $eventName) {
	$eventOptionscost .= "<option value={$eventType}>{$eventName}</option>";
}
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

if ($type=='view') {

	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	$start = $_REQUEST['start_time'] ? date("Ymd",strtotime($_REQUEST['start_time']))  : date("Ymd",time());
	$end = $_REQUEST['end_time'] ? date("Ymd",strtotime($_REQUEST['end_time'])) : date("Ymd",time());
	$server = implode(',', $selectServerids);

	$goodsget = $_REQUEST['goodsget'];
	if($goodsget != 'ALL'){
		$whereparam1 = " and param1=$goodsget";
	}
	$goodscost = $_REQUEST['goodscost'];
	if($goodscost != 'ALL'){
		$whereparam1 = " and param1=$goodscost";
	}

	if ($_REQUEST['itemId']) {
		$sql = "select date, type,itemId,param1,sum(cost) as cost from stat_allserver.stat_log_rbi_dailygoodscost where itemId=$itemId and sid in ($server)  $whereparam1 and date >= $start and date<=$end
		group by date,type,param1";
	} else {
		$sql = "select date, type,itemId,sum(cost) as cost from stat_allserver.stat_log_rbi_dailygoodscost where sid in ($server) $whereparam1 and date >= $start  and date<=$end group by date,type,itemId";
	}

	if($goodscost != "ALL" && $goodsget != "ALL"){
		$tip = true;
		$sql = "";
	}
	if (in_array($_COOKIE['u'],$privilegeArr)) {
		$html = $sql;
	}
	$result = query_infobright( $sql);
	$lang = loadLanguage();
	$clientXml = loadXml('goods','goods');

	if($_REQUEST['itemId']) {
		$itemId = $_REQUEST['itemId'];

		$data =$datearr=$itemIdarr=$sum=array();

		foreach ($result['ret']['data'] as $curRow) {
			$type = $curRow['type'];
			$param1 = $curRow['param1'];

			$data[$curRow['date']][$type][$param1] += $curRow['cost'];
			$datearr[$curRow['date']] = $curRow['date'];
			$sum[$type][$param1] += $curRow['cost'];
			$key = $type.'_'.$param1;
			$itemIdarr[$key] = $key;
		}

		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>ID</th><th colspan='2'>合计</th>";
		foreach ($datearr as $date) {
			$html .= "<th colspan='2'>$date</th>";
		}
		$html .= "</tr></thead>";
		//副标题
		$html .= "<tr><th>---</th><th>产出</th><th>消耗</th>";
		foreach ($datearr as $date) {
			$html .= "<th>产出</th><th>消耗</th>";
		}
		$html .= "</tr><tbody id='adDataTable'>";

		sort($itemIdarr);
		foreach ($itemIdarr as $value) {
			$tmp = explode('_',$value);
			$type = $tmp[0];
			$param1 = $tmp[1];
			$value = $param1;
			if($type == 0){
				$showname =$GoodsGetType[$param1];
			}else{
				$showname =$GoodsUseType[$param1];
			}
			$sum1 = $sum[0][$value]?$sum[0][$value]:0;
			$sum2 = $sum[1][$value]?$sum[1][$value]:0;
			$htmltmp = '';
			$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$showname}</font></td><td>{$sum1}</td><td>{$sum2}</td>";
			foreach ($datearr as $date) {
				$a = $data[$date][0][$value]?$data[$date][0][$value]:0;
				$b = $data[$date][1][$value]?$data[$date][1][$value]:0;
				$htmltmp .= "<td>{$a}</td><td>{$b}</td>";
			}
			$htmltmp .= "</tr>";
			$html .= $htmltmp;
		}
		$html .= '</tbody></table></div>';

	}else{
		$data =$datearr=$itemIdarr=$sum=array();

		foreach ($result['ret']['data'] as $curRow) {
			$data[$curRow['date']][$curRow['type']][$curRow['itemId']] += $curRow['cost'];
			$datearr[$curRow['date']] = $curRow['date'];
			$itemIdarr[$curRow['itemId']] = $curRow['itemId'];
			$sum[$curRow['type']][$curRow['itemId']] += $curRow['cost'];
		}

		$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>ID</th><th colspan='2'>合计</th>";
		foreach ($datearr as $date) {
			$html .= "<th colspan='2'>$date</th>";
		}
		$html .= "</tr></thead>";
		//副标题
		$html .= "<tr><th>---</th><th>产出</th><th>消耗</th>";
		foreach ($datearr as $date) {
			$html .= "<th>产出</th><th>消耗</th>";
		}
		$html .= "</tr><tbody id='adDataTable'>";

		sort($itemIdarr);
		foreach ($itemIdarr as $value) {
			$nametmp = $lang[(int)$clientXml[$value]['name']];
			$nametmp = $nametmp.'_'.$value;
			$sum1 = $sum[0][$value]?$sum[0][$value]:0;
			$sum2 = $sum[1][$value]?$sum[1][$value]:0;
			$htmltmp = '';
			$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$nametmp}</font></td><td>{$sum1}</td><td>{$sum2}</td>";
			foreach ($datearr as $date) {
				$a = $data[$date][0][$value]?$data[$date][0][$value]:0;
				$b = $data[$date][1][$value]?$data[$date][1][$value]:0;
				$htmltmp .= "<td>{$a}</td><td>{$b}</td>";
			}
			$htmltmp .= "</tr>";
			$html .= $htmltmp;
		}
		$html .= '</tbody></table></div>';

	}
	if (count($data) >= 1) {
		$showData = true;
	} else {
		$headAlert = "查询失败";
		if($tip){
			$headAlert = "不能同时选择获取 和 消耗";
		}
	}



//	$start=date('Y-m-d H:i:s',$start);
//	$end=date('Y-m-d H:i:s',$end);
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>