<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
	$start = date("Y-m-d 00:00",time()-86400*6);
if(!$_REQUEST['end'])
	$end = date("Y-m-d 23:59",time());
$eventNames = $goldLink;
$eventOptions = '<option>ALL</option>';
foreach ($eventNames as $eventType => $eventName)
	$eventOptions .= "<option id={$eventType}>{$eventName}</option>";
if($_REQUEST['analyze']=='user'){
	if(empty($_REQUEST['user']) ){
		echo '<div><font color="red">请输入用户uid</font></div>';
		exit();
	}else{
		$user = $_REQUEST['user'];
	}
	$start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:0;
	$end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end)*1000;
	$user = $user?" and userId = '{$user}' ":"";
	$whereSql = "where time > $start and time < $end $user and cost != 0 ";
	//根据类型分出购买的是什么
	$paySql = "(select g.*,u.uid as userUid,u.name as userName from gold_cost_record g left join userprofile u on g.userId = u.uid $whereSql ";
	$count = 0;
	$dateEvent = $eventAll = $events = $event = array();
	//购买总人数总次数
	$sql = "select count(1) as total from $paySql"
	.($_REQUEST['event'] != null?" and type = ('{$_REQUEST['event']}')":"")
	." ) as a "
	;
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['total'];
	echo "获得数据".(int)$count."条";
			
	$page_limit = 100;
	$pager = page($count, $_REQUEST['page'], $page_limit);
	$index = $pager['offset'];
	$sql = "select * from $paySql"
			.($_REQUEST['event'] != null?" and type = ('{$_REQUEST['event']}')":"")
			." ) a ORDER BY time desc limit $index,$page_limit";
	$sendParam = array('info'=>'','data'=>$param,'mainDB'=>1);
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'];
	//语言文件
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
	foreach ($result as $curRow)
	{
		$data = $curRow;
		$logItem['时间'] = date('Y-m-d H:i:s',$data['time']/1000);
		$logItem['用户UID'] = $data['userUid'];
		$logItem['用户'] = $data['userName'];
		$logItem['金币类型'] = $curRow['goldType']?'充值金币':'赠送';
		$logItem['类型'] = $eventNames[$curRow['type']];
		if($data['param1'] && $lang[(int)$clintXml[$data['param1']]['name']]){//$data['param1'] 是100000051-55.最终取到10025517
			$logItem['参数1'] = $lang[(int)$clintXml[$data['param1']]['name']];//10025517={0}点魔法能量
			$num =  (int)$clintXml[$data['param1']]['para'];
			$logItem['参数1'] = str_replace('{0}',$num,$logItem['参数1']);
		}
		else{
			$logItem['参数1'] = $data['param1'];
			if($curRow['type'] == 8){
				$logItem['参数1'] = date('Y-m-d H:i:s',$data['param1']);
			}
		}
		
		$logItem['参数2'] = $data['param2'];
		$logItem['变化前'] = $data['originalGold'];
		$logItem['变化值'] = $data['cost'];
		$logItem['变化后'] = $data['remainGold'];
		$tmp = $data['mailSrcType']?$data['mailSrcType']:0;
		$logItem['来源'] = $MailSrcType[$tmp] ? $MailSrcType[$tmp] : 'none';
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
if (!$privileges['dropdownlist_view']) {
	$eventOptions = '<option>ALL</option>';
	$selectEventCtl = '<select id="selectEvent" onchange="" style="visibility: hidden;">
			'.$eventOptions.'
	</select><br>
	';
}else{
	$selectEventCtl = '<br>
	消费类型
	<select id="selectEvent" onchange="">
			'.$eventOptions.'
	</select>
	';
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>