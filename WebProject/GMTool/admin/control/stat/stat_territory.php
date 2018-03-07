<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d', time() - 86400 * 3);
$end = date('Y-m-d');
global $servers;
$allServerFlag = false;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

$statType_title = array('联盟领地统计');

$columnName = array(
'total'=>'联盟堡垒总数',
);

if ($_REQUEST['dotype'] == 'getPageData') {
			$selectedServers = array();

			foreach ($servers as $server => $serverInfo) {
				$selectedServers[] = $server;
			}
			$sql="select count(uid) as total from alliance_territory where type=14 and num=1";
			foreach ($selectedServers as $server) {
					$result = $page->executeServer($server, $sql,3);
					$affectLines += $result['ret']['effect'];
					$data[$server] = $result;
			}


$html .= "<div style='float:left;width:100%;height:340px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
			$title = false;
			foreach ($data as $serverKey => $sqlDatas) {
				if ($sqlDatas['ret'] && isset($sqlDatas['ret']['data'])) {
					foreach ($sqlDatas['ret']['data'] as $sqlData) {
						if (!$title) {
							$html .= "<tr class='listTr'><th>服编号</th>";
							foreach ($sqlData as $key => $value)
								$html .= "<th>" . $columnName[$key] . "</th>";
							$html .= "</tr>";
							$title = true;
						}
						$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
						$html .= "<td>$serverKey</td>";
						foreach ($sqlData as $key => $value) {
							$html .= "<td>" . $value . "</td>";
						}
						$html .= "</tr>";
					}
				}
			}
			$html .= "</table></div><br/>";

	echo $html;
	exit();
}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>
