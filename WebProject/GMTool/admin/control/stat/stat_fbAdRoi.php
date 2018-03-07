<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['regStartDate']){
	$regStartDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['regEndDate'])
	$regEndDate= date("Y-m-d",time());
if(!$_REQUEST['payStartDate']){
	$payStartDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['payEndDate'])
	$payEndDate= date("Y-m-d",time());


if($_REQUEST['analyze']=='user'){
	$regStartDate = substr($_REQUEST['regStartDate'],0,10);
	$sRegDdate= date('Ymd',strtotime($regStartDate));
	$regEndDate = substr($_REQUEST['regEndDate'],0,10);
	$eRegDate =date('Ymd',strtotime($regEndDate));
	
	$payStartDate = substr($_REQUEST['payStartDate'],0,10);
	$sPayDdate= date('Ymd',strtotime($payStartDate));
	$payEndDate = substr($_REQUEST['payEndDate'],0,10);
	$ePayDate =date('Ymd',strtotime($payEndDate));
	
	$selectServer=array('s152','s188','s191','s194','s197','s199','s236','s263','s281','s289','s305');
	$sidArray=array(152,188,191,194,197,199,236,263,281,289,305);
	global $servers;
	foreach ($servers as $server=>$serverInfo){
		if(substr($server, 1)>316){
			$sidArray[]=substr($server, 1);
		}
	}
	$sids=implode(',', $sidArray);
	
	$nextDayLogin=array();
	$regData=array();
	$paydata=array();
	
	$sql="select adsrc,sum(reg_all) regNum,sum(r1) nextLogin from stat_allserver.stat_fbRoi_retention where regDate between $sRegDdate and $eRegDate and sid in($sids) group by adsrc;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		$regData[$curRow['adsrc']]+=$curRow['regNum'];
		$totalReg+=$curRow['regNum'];
		
		$nextDayLogin[$curRow['adsrc']]+=$curRow['nextLogin'];
		$totalNextDay+=$curRow['nextLogin'];
	}
	
	$sql="select adsrc,sum(payNum) paysum from stat_allserver.stat_fbRoi_pay where payDate between $sPayDdate and $ePayDate and regDate between $sRegDdate and $eRegDate and sid in($sids) group by adsrc";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		$paydata[$curRow['adsrc']]+=$curRow['paysum'];
		$totalPay+=$curRow['paysum'];
	}
	
	$html = "<table class='listTable' style='text-align:center'><thead><th>scid</th><th>注册数</th><th>次日活跃数</th><th>次日活跃率(%)</th><th>储值金额</th></thead>";
	$t=intval($totalNextDay/$totalReg*10000)/100;
	$t=number_format($t,1);
	$html .= "<tr><td>合计</td><td>$totalReg</td><td>$totalNextDay</td><td style='text-align:right;'>$t</td><td style='text-align:right;'>$totalPay</td></tr>";
	foreach ($regData as $adsrcKey=>$value){
		$r=intval($nextDayLogin[$adsrcKey]/$regData[$adsrcKey]*10000)/100;
		$r=$r?number_format($r,1):'';
		$html .= "<tr><td>$adsrcKey</td><td>".$regData[$adsrcKey]."</td><td>".$nextDayLogin[$adsrcKey]."</td><td style='text-align:right;'>".$r."</td><td style='text-align:right;'>".($paydata[$adsrcKey]?number_format($paydata[$adsrcKey],2):'')."</td></tr>";
	}
	$html .= "</table>";
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>