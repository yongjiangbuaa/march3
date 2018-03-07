<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
global $servers;
	$html;
	$enTotal;$ruTotal;$jaTotal;$zhHansTotal;
	$zhHantTotal;$koTotal;$esTotal;$msTotal;
	$frTotal;$deTotal;$itTotal;$ptTotal;$otherTotal1;
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
	$startTime = date('Ymd', $s);
	$endTime = date('Ymd', $e);
	$sttt = $_REQUEST['selectServer'];
	$serverDiv=loadDiv($sttt);
if ($_GET ['ispost']) {

	$erversAndSidsArr=getSelectServersAndSids($_REQUEST['selectServer']);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	
$sql = "select * from translation_record where date > ".$startTime." and date < ".$endTime;
	$enTotal = 0;
	$ruTotal = 0;
	$jaTotal = 0;
	$zhHansTotal = 0;
	$zhHantTotal = 0;
	$koTotal = 0;
	$esTotal = 0;	
	$msTotal = 0;
	$frTotal = 0;
	$deTotal = 0;
	$itTotal = 0;
	$ptTotal = 0;
	$otherTotal = 0;
	$fromDBTotal = 0;
	$fromMSTotal = 0;
	
	$serverStr;
	
	$redis = new Redis();
	
	$html = "<div style='float:left;width:100%;height:340px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
						  	$html .= "<th>date</th>";
						  	$html .= "<th>en</th>";
							$html .= "<th>zh-Hans</th>";
							$html .= "<th>zh-Hant</th>";
							$html .= "<th>ja</th>";
							$html .= "<th>ko</th>";
							$html .= "<th>ru</th>";
							$html .= "<th>es</th>";
							$html .= "<th>ms</th>";
							$html .= "<th>fr</th>";
							$html .= "<th>de</th>";
							$html .= "<th>it</th>";
							$html .= "<th>pt</th>";
							$html .= "<th>other</th>";
							$html .= "<th>fromDB</th>";
							$html .= "<th>fromMS</th>";
							$html .= "</tr>";
	foreach ($selectServer as $server=>$serInfo){
			$serverStr.= "server=" . $server;
			$html .= "<tr class='listTr'><th>".$server."</th></tr>";
			
			$currentIP = $servers[$server]['ip_inner'];
			$redis->connect($currentIP,6379);
			try {
				for ($date=$startTime;$date<=$endTime;){
					$ret=$redis->hGetAll($date);
					$en = $ret['en'];$enTotal += $en;
					$ru = $ret['ru']; $ruTotal += $ru;
					$ja = $ret['ja']; $jaTotal += $ja;
					$zhHans = $ret['zh-Hans']; $zhHansTotal += $zhHans;
					$zhHant = $ret['zh-Hant']; $zhHantTotal += $zhHant;
					$ko = $ret['ko']; $koTotal += $ko;
					$es = $ret['es']; $esTotal += $es;
					$ms = $ret['ms']; $msTotal += $ms;
					$fr = $ret['fr']; $frTotal += $fr;
					$de = $ret['de']; $deTotal += $de;
					$it = $ret['it']; $itTotal += $it;
					$pt = $ret['pt']; $ptTotal += $pt;
					$other = $ret['other']; $otherTotal += $other;
					$fromDB = $ret['fromDB']; $fromDBTotal += $fromDB;
					$fromMS = $ret['fromMS']; $fromMSTotal += $fromMS;
					
					$html .= "<tr class='listTr'>";
					$html .= "<th>".$date."</th>";
					$html .= "<th>".$en."</th>";
					$html .= "<th>".$zhHans."</th>";
					$html .= "<th>".$zhHant."</th>";
					$html .= "<th>".$ja."</th>";
					$html .= "<th>".$ko."</th>";
					$html .= "<th>".$ru."</th>";
					$html .= "<th>".$es."</th>";
					$html .= "<th>".$ms."</th>";
					$html .= "<th>".$fr."</th>";
					$html .= "<th>".$de."</th>";
					$html .= "<th>".$it."</th>";
					$html .= "<th>".$pt."</th>";
					$html .= "<th>".$other."</th>";
					$html .= "<th>".$fromDB."</th>";
					$html .= "<th>".$fromMS."</th>";
					$html .= "</tr>";
					
					$date=date('Ymd',strtotime($date)+86400);
				}
			} catch ( Exception $e ) {
				$html .= $e->getMessage ();
			}
			
	}
  	$html .= "</table><br/>";
	$jsonResult = array(
						"content" => $html,
						"data" => array(
							array("key" => "en", "value" => $enTotal),
							array("key" => "ru", "value" => $ruTotal),
							array("key" => "ja", "value" => $jaTotal),
							array("key" => "zh-Hans", "value" => $zhHansTotal),
							array("key" => "zh-Hant", "value" => $zhHantTotal),
							array("key" => "ko", "value" => $koTotal),
							array("key" => "es", "value" => $esTotal),
							array("key" => "ms", "value" => $msTotal),
							array("key" => "fr", "value" => $frTotal),
							array("key" => "de", "value" => $deTotal),
							array("key" => "it", "value" => $itTotal),
							array("key" => "pt", "value" => $ptTotal),
							array("key" => "other", "value" => $otherTotal),
							),
						"resource" => array(
								array("key" => "DB", "value" => $fromDBTotal),
								array("key" => "MS", "value" => $fromMSTotal)
							)			
						);
	$response = json_encode($jsonResult);
	echo $response;
	return;
}
include( renderTemplate("{$module}/{$module}_{$action}"));
?>