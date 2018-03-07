<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
// 开发者debug
set_time_limit ( 0 );
$developer = in_array($_COOKIE['u'],$privilegeArr);

if ($developer && $_REQUEST ['showrequest'] == 1) {
	print_r ( $_REQUEST );
}
$result = array ();
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);

// $serverids = array_keys($servers);
if ($developer && $_REQUEST ['showservers'] == 1) {
	print_r ( $selectServer );
}

if ($_REQUEST ['type'] == 'search') {
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];
	
	$starttime = microtime ( true );
	$count = count ( array_keys($selectServer ));
	foreach ( $selectServer as $ksid => $sk ) {
		$sid = substr ( $ksid, 1 );
		if ($ksid == 'localhost') {
			$sid = 1;
		}
		
		$rediskey = 'fight_of_king_inf' . $sid;
		$redis = new Redis ();
//		$client->connect ( GLOBAL_REDIS_SERVER_IP );

		$currentIP = $servers[$ksid]['ip_inner'];
		$redis->connect($currentIP,6379);

		$fields = $redis->hGetAll ( $rediskey );
		
		// $page = new BasePage();
		// key=fight_of_king_info1&index=1
		// $rediskey = 'fight_of_king_inf'.$sid;
		// $fields = $page->redis(3,$rediskey,null,$ksid);
		if (empty ( $fields )) {
			$html = "<div> 没有数据</div>";
			$result [$sid] = array ();
		} else {
			krsort ( $fields );
			foreach ( $fields as $k => $v ) {
				$tmpval = ( array ) json_decode ( $v );
				if ($count < 5) {
					$uids = $tmpval ['thOwnersUid'];
					$times = explode ( ";", $tmpval ['thOwnerChangeTime'] );
					if (! empty ( $uids )) {
						$uids = str_replace ( ";", "','", $uids );
						$uids = "'" . $uids."'";
						$sql = "select uid,name from userprofile where uid in ($uids)";
						$res = $page->executeServer ( $ksid, $sql, 3 );
						$uidsArr = explode ( ',', $uids );
						$nameArr = array ();
						if (! empty ( $res ['ret'] ['data'] )) {
							foreach ( $res ['ret'] ['data'] as $one ) {
								$nameArr [$one ['uid']] = $one ['name'];
							}
						}
						$newArr = array ();
						foreach ( $uidsArr as $k2 => $uid ) {
							/* if(date ( 'Y-m-d H:i:s', $times [$k2] / 1000 )=='2015-04-26 01:38:18'){
								echo date ( 'Y-m-d H:i:s', $times [$k2] / 1000 ).",".$uid.',%'.(str_replace('<', '&lt;', $nameArr [$uid]))."!!%%%"."\n";
							} */
							if (! empty ( $times [$k2] )) {
								$t = date ( 'Y-m-d H:i:s', $times [$k2] / 1000 ) . " ";
							} else {
								$t = "";
							}
							$newArr [] = ! empty ( $nameArr [$uid] ) ? ($t . (str_replace('>', '&gt;', str_replace('<', '&lt;', $nameArr [$uid])))) : ($t . $uid);
						}
					}
					//print_r($newArr);
					$tmpval ['thOwnersUid'] = implode ( ';', $newArr );
				}
				$result [$sid] [$k] = $tmpval;
			}
		}
		$redis->close();
	}
	$endtime = microtime ( true );
	
}
/*
 * "startTime" 开始时间 "endTime" 结束时间 "kingUid" 国王uid "kingName" 国王名称 "kingPic" 国王头像 "kingAlliance" 国王所属联盟全称 "kAbbr" 国王所属联盟简称 "beKingTime" 成为国王的时间 "thDC" 宫殿死兵人数 "trDC" 投石机死兵人数 "bUC" 参与玩家人数 "bAC" 参与联盟人数 "sc" 侦查次数 "thOwnerChangeTime"占领时间 "thOwnersUid" 宫殿占领者（当期国王战中所有占领过宫殿的玩家uid，以";"分割）
 */
$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
$title = false;
$sqlDatas = $result;
foreach ( $sqlDatas as $key => $value ) {
	if (! $title) {
		$html .= "<tr class='listTr'><th>服务器</th><th>第几次</th><th>开始时间</th><th>结束时间</th><th>联盟全称</th><th>联盟简称</th>
                        <th>国王名称</th><th>成为国王的时间</th><th>宫殿死兵人数</th><th>投石机死兵人数</th>
                        <th>参与玩家人数</th><th>参与联盟人数</th><th>侦查次数</th><th>宫殿占领者</th></tr>";
		$title = true;
	}
	$addkey = true;
	foreach ( $value as $f => $one ) {
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		if ($addkey == true) {
			$html .= "<td>s" . $key . "</td>";
			$addkey = false;
		} else {
			$html .= "<td></td>";
		}
		$html .= "<td>$f</td>";
		$html .= "<td>" . date ( 'Y-m-d H:i:s', intval ( $one ["startTime"] / 1000 ) ) . "</td>";
		$html .= "<td>" . date ( 'Y-m-d H:i:s', intval ( $one ["endTime"] / 1000 ) ) . "</td>";
		$html .= "<td>" . $one ["kingAlliance"] . "</td>";
		$html .= "<td>" . $one ["kAbbr"] . "</td>";
		$html .= "<td>" . $one ["kingName"] . "</td>";
		$html .= "<td>" . $one ["beKingTime"] . "</td>";
		$html .= "<td>" . intval ( $one ["thDC"] / 1000 ) . "K</td>";
		$html .= "<td>" . intval ( $one ["trDC"] / 1000 ) . "K</td>";
		$html .= "<td>" . $one ["bUC"] . "</td>";
		$html .= "<td>" . $one ["bAC"] . "</td>";
		$html .= "<td>" . $one ["sc"] . "</td>";
		
		$html .= "<td align='left'>" . $one ["thOwnersUid"] . "</td>";
		$html .= "</tr>";
	}
}
$html .= "</table><br/>";

include (renderTemplate ( "{$module}/{$module}_{$action}" ));
?>