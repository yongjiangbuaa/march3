<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
	if (isset($_REQUEST['getData'])) {

	    $buildMin = $_REQUEST['buildMin'];
	    $buildMax = $_REQUEST['buildMax'];
	    $start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
	    $end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
	    $where = "u.regTime >= $start and u.regTime <= $end  and ub.level >= $buildMin and ub.level <= $buildMax ";

		$vipLv = $_REQUEST['viprank'];
		$where .= " and v.level = {$vipLv} ";

    	//分页
        $sql = "SELECT count(1) as sum from user_vip v LEFT JOIN userprofile u on v.uid=u.uid
            LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000 where $where";

        $result = $page->execute($sql,3);
        $count = $result['ret']['data'][0]['sum'];
//        if($count < 1){
//            exit('<h3>保留COK代码，方便日后扩展</h3>');
//        }
        $limit = 100;
        $pager = page($count, $_REQUEST['page'], $limit);
        $index = $pager['offset'];
        //详情
        $sql ="SELECT v.uid ,v.score , v.level v_level, ub.level ub_level, u.regTime
                from user_vip v INNER JOIN userprofile u on v.uid=u.uid
                LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000
                where $where  ORDER BY score LIMIT  $index,$limit";
        $result = $page->execute($sql,3);
        $result = $result['ret']['data'];
		foreach ($result as $curRow)
		{
			$data = $curRow;
			$logItem['注册时间'] = $data['regTime'];
			$logItem['玩家ID'] = $data['uid'];
			$logItem['玩家积分'] = $data['score'];
			$logItem['大本等级'] = $data['ub_level'];
			$logItem['vip等级'] = $data['v_level'];
			$log[] = $logItem;
		}
		$html .= $sql;
	$title = false;
	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$i = 1;
	foreach ($log as $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'><th>编号</th>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		if($sqlData['param3'] == 1){
		    continue;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		foreach ($sqlData as $key=>$value){
			$html .= "<td>" . $value . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
	echo $html;
	exit();
}


//if (isset($_REQUEST['getData'])) {
//		$vipLv = $_REQUEST['viprank'];
//		$where = "u.level = {$vipLv} ";
//
//	//分页
//	$sql_index = "SELECT count(1) as sum FROM user_vip u WHERE $where ";
//
//	//print_r($sql);
//	$result_index = $page->execute($sql_index,3);
//	$count_index = $result_index['ret']['data'][0]['sum'];
////	if($count_index < 1){
////		exit('<h3>没数据</h3>');
////	}
//	$limit = 100;
//	$pager = page($count_index, $_REQUEST['page'], $limit);
//	$index = $pager['offset'];
//	//详情
//	$sql_file ="SELECT uid ,score , level  FROM user_vip u WHERE  $where    ORDER BY score LIMIT  $index,$limit";
//	$result_file = $page->execute($sql_file,3);
//	$result_file = $result_file['ret']['data'];
//	foreach ($result_file as $curRow)
//	{
//		$data = $curRow;
//		$logItem['玩家id'] = $data['uid'];
//		$logItem['玩家积分'] = $data['score'];
//		$logItem['vip等级'] = $data['level'];
//		$log[] = $logItem;
//	}
//	$title = false;
//	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
//	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
//	$i = 1;
//	foreach ($log as $sqlData)
//	{
//		if(!$title)
//		{
//			$html .= "<tr class='listTr'><th>编号</th>";
//			foreach ($sqlData as $key=>$value)
//				$html .= "<th>" . $key . "</th>";
//			$html .= "</tr>";
//			$title = true;
//		}
//		if($sqlData['param3'] == 1){
//			continue;
//		}
//		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
//		$html .= "<td>$i</td>";
//		$i++;
//		foreach ($sqlData as $key=>$value){
//			$html .= "<td>" . $value . "</td>";
//		}
//		$html .= "</tr>";
//	}
//	$html .= "</table></div><br/>";
//	if($pager['pager'])
//		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
//	echo $html;
//	exit();
//}

//
//if (isset($_REQUEST['getData'])) {
//
//	$levelMin = $_REQUEST['levelMin'];
//	$levelMax = $_REQUEST['levelMax'];
//	$buildMin = $_REQUEST['buildMin'];
//	$buildMax = $_REQUEST['buildMax'];
//	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($dateMin)*1000;
//	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($dateMax)*1000;
//	$version = ($_REQUEST['selectVersion'] && $_REQUEST['selectVersion'] != 'all') ? " and u.appVersion='".$_REQUEST['selectVersion']."' " : '';
//	$where = " and l.`timeStamp` >= $start and l.`timeStamp` <= $end and u.level >= $levelMin and u.`level` <=  $levelMax
//	    and ub.`level` >= $buildMin and ub.`level` <= $buildMax ";
//	if($_REQUEST['vipaddtime']){
//		$addTime = $_REQUEST['vipaddtime'];
//		if($addTime != 124 ){
//			$where .= " and l.param2 = {$addTime} ";
//		}
//		else{
//			$where .= " and l.param2 != 1 ";
//		}
//	}
//	if($_REQUEST['viprank']){
//		$vipLv = $_REQUEST['viprank'];
//		$where .= " and l.param1 = {$vipLv} ";
//	}
//
//	//分页
//	$sql = "SELECT count(1) as sum from logrecord l INNER JOIN userprofile u on l.`user`=u.uid
//            LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000 where l.category=14
//         $where $version ";
//
//	//print_r($sql);
//	$result = $page->execute($sql,3);
//	$count = $result['ret']['data'][0]['sum'];
//	if($count < 1){
//		exit('<h3>保留COK代码，方便日后扩展</h3>');
//	}
//	$limit = 100;
//	$pager = page($count, $_REQUEST['page'], $limit);
//	$index = $pager['offset'];
//	//详情
//	$sql ="SELECT l.`timeStamp`,l.type,l.param1 level,l.param2 time,u.`name`,u.`level` ulv,ub.`level` blv,u.appVersion
//                from logrecord l INNER JOIN userprofile u on l.`user`=u.uid
//                LEFT JOIN user_building ub on ub.uid=u.uid and ub.itemId=400000
//                where l.category=14 $where  $version  ORDER BY `user` LIMIT  $index,$limit";
//	$result = $page->execute($sql,3);
//	$result = $result['ret']['data'];
//	foreach ($result as $curRow)
//	{
//		$data = $curRow;
//		$logItem['时间'] = $data['timeStamp'];
//		$logItem['操作'] = $data['type'] == 0 ? '激活' : '延长时间';
//		$logItem['VIP级别'] = $data['level'];
//		$logItem['延长时间'] = $data['time'].'h';
//		$logItem['当前等级'] = $data['ulv'];
//		$logItem['大本等级'] = $data['blv'];
//		$logItem['游戏版本'] = $data['appVersion'];
//		$log[] = $logItem;
//	}
//	$title = false;
//	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
//	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
//	$i = 1;
//	foreach ($log as $sqlData)
//	{
//		if(!$title)
//		{
//			$html .= "<tr class='listTr'><th>编号</th>";
//			foreach ($sqlData as $key=>$value)
//				$html .= "<th>" . $key . "</th>";
//			$html .= "</tr>";
//			$title = true;
//		}
//		if($sqlData['param3'] == 1){
//			continue;
//		}
//		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
//		$html .= "<td>$i</td>";
//		$i++;
//		foreach ($sqlData as $key=>$value){
//			$html .= "<td>" . $value . "</td>";
//		}
//		$html .= "</tr>";
//	}
//	$html .= "</table></div><br/>";
//	if($pager['pager'])
//		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
//	echo $html;
//	exit();
//}


global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

if($_REQUEST['dotype']=='new'){
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	
	$startDate = substr($_REQUEST['startDate'],0,10);
//	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
//	$eDate =date('Ymd',strtotime($endDate)+86400);

	$sDdate=  $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:strtotime($dateMin)*1000;
	$eDate = $_REQUEST['endDate']?strtotime($_REQUEST['endDate'])*1000:strtotime($dateMax)*1000;

	$sids=implode(',', $selectServerids);
	$sql = "select date,vipLevel,sum(untlogin) vipDau,sum(untActive) activeCount from stat_allserver.stat_vip_record where date between $sDdate and $eDate and sid in($sids) group by date,vipLevel order by date desc,vipLevel;";
	$result = query_infobright($sql);
	$vipData=array();
	foreach ($result['ret']['data'] as $curRow){
		$vipData[$curRow['date']][$curRow['vipLevel']]['vipDau']+=$curRow['vipDau'];
		$vipData[$curRow['date']][$curRow['vipLevel']]['activeCount']+=$curRow['activeCount'];
	}
	$html2 = "<table class='listTable' style='text-align:center'><thead><th></th>";
//	$sql_sum ="select count(1) sum from user_vip where level=$i;";

	for ($i=1;$i<=10;$i++){
		$sql_sum = "select count(1) sum from user_vip v LEFT JOIN userprofile u on v.uid=u.uid where v.level=$i and u.regTime>=$sDdate and u.regTime<=$eDate;";
		$result_sum = $page->execute($sql_sum,3);
//$result_sum = json_encode($result_sum);
		$html2 .="<th colspan='2'>VIP$i</th>";
		$count_sum = $result_sum['ret']['data'][0]['sum'];
		$title .="<th>人数</th><th>$count_sum</th>";
	}
	$html2 .="</thead>";
	$html2 .="<tr><th>日期</th>".$title."</tr>";
	foreach ($vipData as $dateKey=>$levelValue){
		$html2 .="<tr><td>$dateKey</td>";
		for($i=1;$i<=10;$i++){
			$html2 .="<td>".intval($levelValue[$i]['vipDau'])."</td><td>".intval($levelValue[$i]['activeCount'])."</td>";
		}
		$html2 .="</tr>";
	}
	$html2 .="</table>";

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
