<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());

$payLevelArr = range(1,8);
$selectoption = "<select id='mylevel' name='mylevel'> <option>ALL</option> ";
foreach($payLevelArr as $item){
	$selectoption .= "<option> $item</option>";
}
$selectoption .= "</select>";

global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

if ($_REQUEST ['analyze'] == 'platform') {
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];

	$start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
	$end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);

	$startdate = date('Ymd',strtotime($start));
	$enddate = date('Ymd',strtotime($end));

	if (!$_REQUEST['selectCountry']) {
		$currCountry = 'ALL';
	}else{
		$currCountry = $_REQUEST['selectCountry'];
	}
	if (!$_REQUEST['selectPf']) {
		$currPf = 'ALL';
	}else{
		$currPf = $_REQUEST['selectPf'];
	}

	$wheresql1='';
	if($currPf != 'ALL'){
		$wheresql1 .= " and pf=". "'" . $currPf . "'" ;
	}
	if($currCountry != 'ALL'){
		$wheresql1 .= " and country=". "'" . $currCountry . "'" ;
	}

	if($_REQUEST['event'] != 'ALL'){
		$wheresql1  .= " and paylevel={$_REQUEST['event']}";
	}


	$serversql = '( '.implode($selectServerids,',') . ' )';

	$sql = "select date,paylevel,sum(dau) as dau,sum(money) as money,sum(users) as users from stat_allserver.pay_userdata_dau p
			where date>=$startdate and date <= $enddate and sid in $serversql  $wheresql1 group by date,paylevel order by date desc ,paylevel desc";

	$result = query_infobright($sql);
	$m_total = $datearr = $levelArr = array();
	foreach ($result['ret']['data'] as $disRow) {
		//一条数据
		$date = $disRow['date'];
		$datearr[$date] = $date;
		$paylevel = $disRow['paylevel'];

		$levelArr[$paylevel] =  $paylevel;

		$dau = $disRow['dau'];
		$money = $disRow['money'];
		$users = $disRow['users'];

		$m_total[$date][$paylevel] = array('dau'=>$dau,'money'=>$money,'users'=>$users);
	}
	$paylevelArrtip = array(0=>0,1=>'0-5',2=>'5-500',3=>'500-1000',4=>'1000-5000',5=>'5000-10000',6=>'10000-20000',7=>'20000-30000',8=>'>30000');
	$html = "";
	if(in_array($_COOKIE['u'],$privilegeArr)){
		$html .= $sql;
	}
	if(!count($m_total)) {
		$html .=  "<br/>";
		$html .=  "没有数据";
//		exit();
	}

	$html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><td>--</td>";
	foreach ($datearr as $date) {
		$html .= "<th colspan='3'>$date</th>";
	}
	$html .= "</tr></thead>";
	//副标题
	$html .= "<tr><th>等级</th>";
	foreach ($datearr as $date) {
		$html .= "<th>DAU</th><th>花费金额</th><th>付费人数</th>";
	}
	$html .= "</tr><tbody id='adDataTable'>";

	foreach ($levelArr as $key) {
		$htmltmp = '';
		$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td align='left'><font color='#0088CC'>$key Lv {$paylevelArrtip[$key]}</font></td>";
		foreach ($datearr as $date) {
			$htmltmp .= "<td>{$m_total[$date][$key]['dau']}</td><td>{$m_total[$date][$key]['money']}</td><td>{$m_total[$date][$key]['users']}</td>";
		}
		$htmltmp .= "</tr>";
		$html .= $htmltmp;
	}
	$html .= '</tbody></table></div>';

//	echo $html;
//	exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>