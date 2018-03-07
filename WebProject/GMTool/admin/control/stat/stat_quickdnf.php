<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*1);
$end = date('Y-m-d',time());
$countType = array(0=>'不同难度统计',2=>'不同难度统计-allserver',1=>'购买每档能量值人数',3=>'购买每档能量值人数-allserver',4=>'参与人数',5=>'参与人数-allserver',);

foreach ($countType as $key=>$value){
	$options .= "<option id={$key}>{$value}</option>";
}
$lang = loadLanguage();
//$clientXml = loadXml('database.local','daily_active');
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];
if ($_REQUEST ['analyze'] == 'platform') {
	$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
	$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);
	$time = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
	$monthArr = monthList(strtotime($start),strtotime($end));
	$type = (int)($_REQUEST ['countType']);

	$sids = implode(',', $selectServerids);
	$whereSql=" and server_id in ($sids) ";

	if($_REQUEST['selectuseruid']){
//查询金币消耗
		$userid = $_REQUEST['selectuseruid'];
		$a= $_REQUEST['a'];
		$n= $_REQUEST['n'];
		$c= $_REQUEST['c'];

		$checkpointItems = simplexml_load_file(ITEM_ROOT.'/item.xml');
		$nodes = $checkpointItems->xpath('/tns:database//Group[@id=\'data_config\']');
		$checkpointItems = $nodes[0];
		foreach ($checkpointItems as $item)
		{
			if($item['id'] == "quickdnf_config"){
				$xml1 = $item;
				break;
			}
		}
		$sql = "select uid,costall,rewardValue from quick_dnf where uid='$userid'";
		$ret = $page->execute($sql,3);
		foreach($ret['ret']['data'] as $row){
			$costmoney = $row['uid'];
			$cul=pow(floatval($row['rewardValue'])/1000.0,floatval($n))*floatval($a)/100.0+floatval($c);
			$cul=$cul*100.0;
			//echo floatval($row['rewardValue']).' :  '.floatval($n).' :   '.pow(floatval($row['rewardValue']),floatval($n)).' :  '.floatval($a).'  :  '.pow(floatval($row['rewardValue']),floatval($n))*floatval($a);
			if($cul>100)
				$cul=100;
			echo "玩家". $costmoney."     本周消耗金币数记录为    " .$row['costall'] ."           当前获得价值为    ".$row['rewardValue']."      爆炸概率为  ".intval($cul);
			echo "<br/>";
			$html .= "<div style='float:left;width:50%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
			$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
			$html .= "<tr class='listTr'><th>消耗金币档位(".$row['costall'].")</th><th>第二关</th><th>第三关</th></tr>";
			$cost = explode('|',$xml1['cost']);
			$pri25 = explode('|',$xml1['pri25']);
			$pri50 = explode('|',$xml1['pri50']);
			$count = count($cost);
			for($i=0;$i <$count;++$i){
				$html .= "<tr class='listTr'><th>$cost[$i]</th><th>$pri25[$i]</th><th>$pri50[$i]</th></tr>";
			}

		}
		$html .= "</table></div><br/>";
		echo $html;
		exit();
	}

	if($type == 2 || $type == 3 || $type == 5){
		$whereSql=" ";
	}

	$count = 0;
	foreach ($monthArr as $i) {
		$db_start = 'coklog_function.function_log_' . $i;
		switch ($type){
			case 0: //不同难度参与人数+ 打死怪物数+ 使用免费人数+ 消耗的能量次数
				$sql_pass = "select server_id,date ,type,sum(int_data4) monster, sum(int_data1) freetimes ,sum(int_data2) energytimes from $db_start where category=18 and int_data3=0 $whereSql and $time GROUP BY server_id,date ,type ";
				break;
			case 2: //不同难度参与人数+ 打死怪物数+ 使用免费人数+ 消耗的能量次数
				$sql_pass = "select date ,type,sum(int_data4) monster, sum(int_data1) freetimes ,sum(int_data2) energytimes from $db_start where category=18 and int_data3=0 and $time GROUP BY date ,type ";
				break;


			case 1: //购买每档能量值的人次          price600 700
				$sql_pass = "select server_id,date ,int_data3,count(userid) buytimes,count(DISTINCT userid) buypeople from $db_start where category=18 and int_data3 > 0 $whereSql and $time GROUP BY server_id, date ,int_data3";
				break;
			case 3: //全服购买每档能量值的人次          price600 700
				$sql_pass = "select date ,int_data3,count(userid) buytimes,count(DISTINCT userid) buypeople from $db_start where category=18 and $time and int_data3 > 0 GROUP BY date ,int_data3 ";
				break;


			case 4:
				$sql_pass = "select server_id,date,type,count(DISTINCT userid) people from $db_start WHERE category=18 and int_data3=0 and int_data2=1 $whereSql and $time GROUP BY server_id,date,type ";
				break;
			case 5:
				$sql_pass = "select date,type,count(DISTINCT userid) people from $db_start WHERE category=18 and int_data3=0 and int_data2=1 and $time GROUP BY date,type  ";
				break;
		}
		if(isset($sql_sum)){
			if($count == 1){
				$sql_sum = ' ('.$sql_sum.') '.'union all'.' ('.$sql_pass.') ';
			}else{
				$sql_sum .= 'union all'.' ('.$sql_pass.')';
			}
//			$sql_sum = $sql_sum . " union " . $sql_pass ;
			//有这个union ,则上边sql语句不能加分号 ,order by 放最后 ,分句加括号
		}else{
			$sql_sum = $sql_pass;
			$count++;
		}
	}

	switch($type){
		case 0:
		case 4:
			$sql_sum .= "ORDER BY server_id,date,type";
			break;
		case 1:
			$sql_sum .= "ORDER BY server_id,date";
			break;
		case 2:
		case 5:
			$sql_sum .= "ORDER BY date,type";
			break;
		case 3:
			$sql_sum .= "ORDER BY date";
			break;
	}


//	echo $sql_sum;
	echo "<br/>";
	$result_sum = query_infobright($sql_sum);
	$result_sum = $result_sum['ret']['data'];
	$title =array();
	if($type == 0 || $type==2){
		$title = array(
			'服',
			'日期',
			'难度等级',
			'参与人数(去重)',
			'打死怪物数',
			'用免费人数',
			'消耗能量次数',
		);
	}elseif($type == 1 || $type == 3){
		$title = array(
			'服',
			'日期',
			'购买档(价钱)',
			'购买次数',
			'购买人数(去重)',
		);
	}elseif($type == 4 || $type == 5){
		$title = array(
			'服',
			'日期',
			'难度等级',
			'参与人数(仅使用能量的)',
		);
	}

	$html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
	foreach ($title as $key=>$value){
		$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";

	foreach($result_sum as $num=>$sqlData){
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		if($type == 0){
			$html .= "<td>" . $sqlData['server_id'] . "</td>";
			$html .= "<td>" . $sqlData['date'] . "</td>";
			$html .= "<td>" . $sqlData['type'] . "</td>";
//			$html .= "<td>" . $sqlData['people'] . "</td>";
			$html .= "<td>人数</td>";
			$html .= "<td>" . $sqlData['monster'] . "</td>";
			$html .= "<td>" . $sqlData['freetimes'] . "</td>";
			$html .= "<td>" . $sqlData['energytimes'] . "</td>";
		}elseif($type == 1){
			$html .= "<td>" . $sqlData['server_id'] . "</td>";
			$html .= "<td>" . $sqlData['date'] . "</td>";
//            $html .= "<td>" . $lang[(int)$clientXml[$sqlData['activeId']]['name']] . "</td>";
			$html .= "<td>" . $sqlData['int_data3'] . "</td>";
			$html .= "<td>" . $sqlData['buytimes'] . "</td>";
			$html .= "<td>" . $sqlData['buypeople'] . "</td>";
		}elseif($type == 2){
			$html .= "<td>allserver</td>";
			$html .= "<td>" . $sqlData['date'] . "</td>";
			$html .= "<td>" . $sqlData['type'] . "</td>";
//			$html .= "<td>" . $sqlData['people'] . "</td>";
			$html .= "<td>人数</td>";
			$html .= "<td>" . $sqlData['monster'] . "</td>";
			$html .= "<td>" . $sqlData['freetimes'] . "</td>";
			$html .= "<td>" . $sqlData['energytimes'] . "</td>";
		}elseif($type == 3){
			$html .= "<td>allserver</td>";
			$html .= "<td>" . $sqlData['date'] . "</td>";
//            $html .= "<td>" . $lang[(int)$clientXml[$sqlData['activeId']]['name']] . "</td>";
			$html .= "<td>" . $sqlData['int_data3'] . "</td>";
			$html .= "<td>" . $sqlData['buytimes'] . "</td>";
			$html .= "<td>" . $sqlData['buypeople'] . "</td>";
		}elseif($type == 4){
		$html .= "<td>" . $sqlData['server_id'] . "</td>";
		$html .= "<td>" . $sqlData['date'] . "</td>";
		$html .= "<td>" . $sqlData['type'] . "</td>";
		$html .= "<td>" . $sqlData['people'] . "</td>";
		}elseif($type == 5){
		$html .= "<td>allserver</td>";
		$html .= "<td>" . $sqlData['date'] . "</td>";
		$html .= "<td>" . $sqlData['type'] . "</td>";
		$html .= "<td>" . $sqlData['people'] . "</td>";

		}

		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
//	if($pager['pager'])
//		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
	echo $html;
	exit();
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>