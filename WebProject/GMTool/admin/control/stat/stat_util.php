<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
	$uid = $_REQUEST['user'];
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59:59",time());
$eventNames = array(
		'reg' => '注册统计'
		,'firstpay' => '首次充值统计'
		,'payusercount' => '充值人数统计'
		,'goldrecord' => '导出金币变化记录'
		// ,'LoginDataOutput' => '导出登陆数据'
		// ,'PayDetail' => '导出所有支付'
		// ,'GoldLogOutput' => '导出金币消耗记录'
		// ,'UserPayedInfo' => '充值额大于5w的用户数据'
		// ,'userlevel' => '大R当前等级'
		);
$selectServer = $_REQUEST['server'];
if(!$selectServer)
	$selectServer = getCurrServer();
$eventOptions = '';
foreach ($eventNames as $eventType => $eventName)
	$eventOptions .= "<option id={$eventType}>{$eventName}</option>";
if($_REQUEST['analyze']=='platform'){
	set_time_limit(0);
	$start = $_REQUEST['start']?strtotime($_REQUEST['start']):0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end']):date("Y-m-d 23:59:59",time());
	$namLinkSortEnd = $end + 86400;//用于表头排序
	$minLevel = $_REQUEST['levelMin'];
	$maxLevel = $_REQUEST['levelMax'];
	$user = ($user && $user != 'undefined')?" and user = '{$user}'":"";
	$cPage = $_REQUEST['page']?$_REQUEST['page']:1;
	$page = new BasePage();
	$param = array();
	$param["type"] = 11;
	$nameLinkSort = $nameLink = $eventAll = $hightLight = array();
	$disableAuto = false;
	$startTime = $start * 1000;
	$endTime = $end * 1000;
	/**
	 * 显示说明
	 * $nameLink表头
	 * $nameLinkSort表头排序，默认可以不设置
	 * $eventAll表数据
	 * $nameLinkSort[0] = x1;
	 * $nameLinkSort[5] = x2;
	 * $nameLinkSort[9] = x3;
	 *	$nameLink[x1]='x1name';
	 *	$nameLink[x2]='x2name';
	 *	$nameLink[x3]='x3name';
	 *	$eventAll[y1][x1]='data11';
	 *	$eventAll[y1][x2]='data12';
	 *	$eventAll[y1][x3]='data13';
	 *	$eventAll[y2][x1]='data21';
	 *	$eventAll[y2][x2]='data22';
	 *	$eventAll[y2][x3]='data23';
	 *	yIndex只用来表明行标
	 * -----------------------------------------
	 * |	x1name	|	x2name	|	x3name	|
	 * -----------------------------------------
	 * |	data11		|	data12		|	data13		|
	 * -----------------------------------------
	 * |	data21		|	data22		|	data23		|
	 * -----------------------------------------
	 */
	$event = $_REQUEST['event'];
	switch ($event){
		case 'reg':
			$paySql = "select count(1) sum,date_format(from_unixtime(time/1000),'%Y-%m-%d') as logDate from stat_reg where time > $startTime and time < $endTime and country = 'KR' group by logDate order by time desc;";
			echo $paySql;
			global $servers;
			$nameLink['server'] = '服务器';
			$eventAll['sum']['server'] = '合计';
			$nameLinkSort = array_keys($nameLink);
			foreach ($servers as $server=>$serverInfo){
				$result = $page->executeServer($server,$paySql,3);
				//对应每条SQL的数据
				$sqlData = array();
				foreach ($result['ret']['data'] as $key=>$curRow){
					$nameLink[$curRow['logDate']] = $curRow['logDate'];
					$nameLinkSort[$namLinkSortEnd - strtotime($curRow['logDate'])] = $curRow['logDate'];
					$eventAll['sum'][$curRow['logDate']] += $curRow['sum'];
					$eventAll[$server][$curRow['logDate']] = $curRow['sum'];
				}
				$eventAll[$server]['server'] = $server;
			}
			break;
		case 'firstpay':
			$logSql = 'replace into firstpaylog select * from (select * from paylog order by time) a group by uid';
			$paySql = "select count(1) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from firstpaylog p inner join stat_reg r on p.uid = r.uid where p.time > $startTime and p.time < $endTime and r.country='KR' group by logDate order by p.time desc;";
			echo $paySql;
			global $servers;
			$nameLink['server'] = '服务器';
			$eventAll['sum']['server'] = '合计';
			$nameLinkSort = array_keys($nameLink);
			foreach ($servers as $server=>$serverInfo){
				$result = $page->executeServer($server,$paySql,3);
				//对应每条SQL的数据
				$sqlData = array();
				foreach ($result['ret']['data'] as $key=>$curRow){
					$nameLink[$curRow['logDate']] = $curRow['logDate'];
					$nameLinkSort[$namLinkSortEnd - strtotime($curRow['logDate'])] = $curRow['logDate'];
					$eventAll['sum'][$curRow['logDate']] += $curRow['sum'];
					$eventAll[$server][$curRow['logDate']] = $curRow['sum'];
				}
				$eventAll[$server]['server'] = $server;
			}
			break;
		case 'payusercount':
			$logSql = 'replace into firstpaylog select * from (select * from paylog order by time) a group by uid';
			$paySql = "select count(distinct(p.uid)) sum,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from paylog p inner join stat_reg r on p.uid = r.uid where p.time > $startTime and p.time < $endTime and r.country='KR' group by logDate order by p.time desc;";
			echo $paySql;
			global $servers;
			$nameLink['server'] = '时间';
			$eventAll['sum']['server'] = $event;
			$nameLinkSort = array_keys($nameLink);
			foreach ($servers as $server=>$serverInfo){
				$result = $page->executeServer($server,$paySql,3);
				//对应每条SQL的数据
				$sqlData = array();
				foreach ($result['ret']['data'] as $key=>$curRow){
					$nameLink[$curRow['logDate']] = $curRow['logDate'];
					$nameLinkSort[$namLinkSortEnd - strtotime($curRow['logDate'])] = $curRow['logDate'];
					$eventAll['sum'][$curRow['logDate']] += $curRow['sum'];
					$eventAll[$server][$curRow['logDate']] = $curRow['sum'];
				}
				$eventAll[$server]['server'] = $server;
			}
			break;
		case 'goldrecord':
			$table = 'gold_cost_record';
			$file = $table.'_'.date('Y-m-d_His').'.log';
			$startTime = strtotime('2014-12-01 00:00:00')*1000;
			$endTime = strtotime('2015-01-01 00:00:00')*1000;
			$sqlInfo = "from $table where time > $startTime and time < $endTime";
			$sumSql = "select count(1) sum $sqlInfo";
			global $servers;
			$nameLink['server'] = '服务器';
			$eventAll['sum']['server'] = '合计';
			$nameLinkSort = array_keys($nameLink);
			$title = array('uid','userId','goldType','type','param1','param2','originalGold','cost','remainGold','time');
			$row = "";
			foreach($title as $titileName){
				$row .= $titileName . "	";
			}
echo 1;exit;
			file_put_contents( ADMIN_ROOT .'/'.$file,$row . "\n",FILE_APPEND);
			foreach ($servers as $server=>$serverInfo){
				$result = $page->executeServer($server,$sumSql,3);
				if(!$result['ret']['data'])
				{
					echo $server.' 未取得数据<br />';
					continue;
				}
				$sum = $result['ret']['data'][0]['sum'];
				$pageLimit = 10000;
				$pages = ceil($sum/$pageLimit);
				for($pageIndex=0;$pageIndex<$pages;$pageIndex++){
					$detailSql = "select * $sqlInfo limit " .$pageIndex*$pageLimit. ",$pageLimit";
					$result = array();
					$recall = 0;
					while(!$result['ret']['data'] && $recall++<5)
					{
						$result = $page->executeServer($server,$detailSql,3);
					}
					if(!$result['ret']['data'])
					{
						echo $server.' '.$pageIndex.'页 未取得数据<br />';
					}
					foreach ($result['ret']['data'] as $key=>$curRow){
						$row = "";
						foreach($title as $titileName){
							$row .= $curRow[$titileName] . "	";
						}
						file_put_contents( ADMIN_ROOT .'/'.$file,$row . "\n",FILE_APPEND);
					}
				}
				$eventAll[$server]['server'] = $server;
				if($count++>1)
				break;
			}
			break;
		default:
			break;
	}
	if(!$disableAuto)
		printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit();
}
if($_REQUEST['analyze']=='user'){
	$start = $_REQUEST['start']?strtotime($_REQUEST['start']):0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end']):0;
	$user = $_REQUEST['user'];
	$sql = "(select qpay.* from qpay"
			." where ownerid = '$user' and sendtime > $start and sendtime < $end "
			.($_REQUEST['event'] != null?" and goodsid LIKE ('{$_REQUEST['event']}%')":"")
			." ) as a "
			;
	
	$page = new BasePage();
	$param = array();
	$param["type"] = 11;
	$param["sql"] = "select count(1) DataCount from ".$sql;
	$param = array(
			"changes"=>null,
			"params"=>$param,
	);
	$sendParam = array('info'=>'','data'=>$param);
	$result = $page->callByServer($selectServer,'gm/gm/Mysql', $sendParam);
	$count = $result['ret']['data'][0]['DataCount'];
	echo "获得数据".(int)$count."条";
	$page_limit = 100;
	$pager = page($count, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];
	$sql = "select * from ". $sql ."ORDER BY sendtime ASC limit $index,$page_limit";
	$page = new BasePage();
	$param = array();
	$param["type"] = 11;
	$param["sql"] = $sql;
	$param = array(
			"changes"=>null,
			"params"=>$param,
	);
	$sendParam = array('info'=>'','data'=>$param);
	$result = $page->callByServer($selectServer,'gm/gm/Mysql', $sendParam);
	$result = $result['ret']['data'];
	foreach ($result as $curRow)
	{
		$data = $curRow;
		$logItem['uid'] = $data['uid'];
		$logItem['时间'] = date('Y-m-d H:i:s',$data['sendtime']);
		$logItem['用户'] = $data['ownerid'];
		$logItem['支付类型'] = $data['goodsid'];
		$logItem['支付金额'] = $data['amt'];
		$logItem['代金券金额'] = $data['pubacct_payamt_coins'] * 10;
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
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
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
