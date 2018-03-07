<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
if ($_REQUEST ['user'])
	$user = $_REQUEST ['user'];
if (! $_REQUEST ['end'])
	$end = date ( "Y-m-d", time () );
if ($_REQUEST ['analyze'] == 'platform') {
	$end = $_REQUEST['end'] ? strtotime($_REQUEST ['end'])*1000 : 0;

	$result = $page->redis(1,'pve:'.$end);
	$fightData = $result['ret'];
	$result = $page->redis(1,'pvefail:'.$end);
	$failData = $result['ret'];
	$pveData = $sortRate = $sortId = array();
	foreach ($fightData as $npcId => $count) {
		$pveData[$npcId]['fight'] = $count;
	}
	foreach ($failData as $npcId => $count) {
		$pveData[$npcId]['fail'] = $count;
	}
	foreach ($pveData as $npcId=>$value){
		$winRate = $value['fight'] > 0 ? (100 - ceil($value['fail']*10000/$value['fight'])/100) : 0;
		$pveData[$npcId]['id'] = $npcId;
		$pveData[$npcId]['winRate'] = $winRate;
		$sortId[$npcId] = $npcId;
		$sortRate[$npcId] = $winRate;
	}
	array_multisort($sortId,SORT_ASC,$pveData);
	//根据胜率从低到高排序
	foreach ($pveData as $curRow)
	{
		$data = $curRow;
		$logItem['ID'] = $curRow['id'];
		$logItem['战斗次数'] = $data['fight'] ? $data['fight'] : '-';
		$logItem['失败次数'] = $data['fail'] ? $data['fail'] : '-';
		$logItem['胜率'] = $data['winRate'];
		$log[] = $logItem;
	}
	$title = false;
	$html = "<div style='float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	foreach ($log as $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($sqlData as $key=>$value){
			$html .= "<td>" . $value . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit ();
}
include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>