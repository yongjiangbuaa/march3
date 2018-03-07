<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*3);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
$showall = true;

global $servers;
// 1，金币；2，联盟贡献；3，国家贡献；4，军衔荣誉；5，荣誉值
$statType_title = array(1=>'商城', 5=>'荣誉商店',2=>'联盟贡献',3=>'国家贡献',4=>'军衔荣誉');
foreach ($statType_title as $key => $value) {
	$options .= "<option value='$key'>$value</option>";
}

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$showData=false;
$alertHeader='';

if ($_REQUEST['action'] == 'view') {

	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectId=$erversAndSidsArr['onlyNum'];

	$wheresql = '';
	if($_REQUEST['useruid']) {
		$useruid = trim($_REQUEST['useruid']);
		$wheresql .= " and userId= '$useruid' ";
	}

	if($_REQUEST['itemId']) {
		$itemId = trim($_REQUEST['itemId']);
		$wheresql .= " and itemId= '$itemId' ";

	}
	if($_REQUEST['statType']) {
		$statType = trim($_REQUEST['statType']);
		$wheresql .= " and (param2= $statType or param2=0 )";

	}

	$startDate = substr($_REQUEST['startDate'],0,10);
	$endDate = substr($_REQUEST['endDate'],0,10);

	$sDdate= date('Ymd',strtotime($startDate));
	$eDate =date('Ymd',strtotime($endDate)+86400);

	$start = strtotime($sDdate)*1000;
	$end = strtotime($eDate)*1000;
	//type 0才是购买 , param1 20是新商城,但是 type1时,param1 20就不是新商城了

	$data = $idarr = $datearr = $sum = array();

	if($useruid) {

		$tablename = 'goods_cost_record' . '_' . date('Ym', strtotime($startDate));
		$tablename2 = 'goods_cost_record' . '_' . date('Ym', strtotime($endDate));
		if ($tablename == $tablename2) {
			$sql = "select from_unixtime(time/1000,'%Y%m%d') as date ,itemid ,sum(cost) cnt from $tablename where type=0 and param1=20 and time >$start and time< $end $wheresql group by date,itemid ;";

		} else {
			exit('don\'t cross month !');
		}

		if (in_array($_COOKIE['u'], $privilegeArr)) {
			$html .= $sql . PHP_EOL;
		}
		foreach ($selectServer as $key => $value) {
			$result = $page->executeServer($key, $sql, 3);
			foreach ($result['ret']['data'] as $CurRow) {
				$date = $CurRow['date'];
				$datearr[$date] = $date;
				$itemId1 = $CurRow['itemid'];
				$idarr[$itemId1] = $itemId1;
				$cnt = $CurRow['cnt'];
				$data[$date][$itemId1] += $cnt;
				$sum[$itemId1] += $cnt;

			}
		}
	}else{
		$server = implode(',', $selectId);
		if ($itemId) {
			$sql = "select date,itemId,sum(cost) as cnt from stat_allserver.stat_log_rbi_dailygoodscost
			where type=0 and param1 =20 and itemId=$itemId and sid in ($server)  and date >= $sDdate and date<=$eDate
			group by date,itemId";
		} else {
			$sql = "select date,itemId,sum(cost) as cnt from stat_allserver.stat_log_rbi_dailygoodscost
			where sid in ($server) and type =0 and param1 =20  and date >= $sDdate  and date<=$eDate
			group by date,itemId";
		}
		$result = query_infobright( $sql);
		foreach ($result['ret']['data'] as $CurRow) {

			$date = $CurRow['date'];
			$datearr[$date] = $date;
			$itemId1 = $CurRow['itemId'];
			$idarr[$itemId1] = $itemId1;
			$cnt = $CurRow['cnt'];
			$data[$date][$itemId1] += $cnt;
			$sum[$itemId1] += $cnt;
		}
	}

	if ($data){ ///选了只显示合计,这也得改
		$showData=true;
		$lang = loadLanguage();
		$clientXml = loadXml('goods','goods');
	}else {
		$alertHeader="没有查询到相关数据信息";
	}
	if(in_array($_COOKIE['u'],$privilegeArr)){
		$html .= $sql;
	}
	$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='2'>合计</th>";
	sort($datearr);
	foreach ($datearr as $date) {
		$html .= "<th colspan='1'>$date</th>";
	}
	$html .= "</tr></thead>";
	//副标题
	$html .= "<tr><th>ITEMID</th><th>个数</th>";
	foreach ($datearr as $date) {
		$html .= "<th>个数</th>";
	}
	$html .= "</tr><tbody id='adDataTable'>";

	foreach ($idarr as $value) {
		$nametmp = $lang[(int)$clientXml[$value]['name']];
		$nametmp = $nametmp.'_'.$value;
		$htmltmp = '';
		$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$nametmp}</font></td><td>{$sum[$value]}</td>";
		foreach ($datearr as $date) {
			$showvalue = $data[$date][$value]>0?$data[$date][$value]:0;
			$htmltmp .= "<td>{$showvalue}</td>";
		}
		$htmltmp .= "</tr>";
		$html .= $htmltmp;
	}
	$html .= '</tbody></table></div>';
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>