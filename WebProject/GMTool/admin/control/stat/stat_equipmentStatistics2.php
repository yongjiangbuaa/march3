<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$showData = false;
global $servers;
$start = date('Y-m-d', time() - 86400 * 3);
$end = date('Y-m-d');

$lordlevel1 = 1;
$lordlevel2 = 60;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$paylevelArrtip = array(0=>0,1=>'1:0-5',2=>'2:5-500',3=>'3:500-1000',4=>'4:1000-5000',5=>'5:5000-10000',6=>'6:10000-20000',7=>'7:20000-30000',8=>'8:>30000');
$option = "<option value='all'>ALL</option>";
foreach($paylevelArrtip as $key=>$value){
	$option .= "<option value=$key>$value</option>";
}
if($_REQUEST['analyze']=='view') {
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];//key
	$selectServerids=$erversAndSidsArr['onlyNum'];//value

	$wheresql = ' 1=1 ';

	if($_REQUEST['user_id']){
		$uid = $_REQUEST['user_id'];
		$wheresql .= " and u.uid = '$uid'";
	}
	if($_REQUEST['itemid']){
		$itemId = $_REQUEST['itemid'];
		$itemId = escape_mysql_special_char($itemId);
		$wheresql .= " and itemId = '$itemId'";
	}

	$lang = loadLanguage();
	$equipXml_front = loadXml('equipment_new','equipment_new');
	$colorArr = array(0=>'白',1=>'绿',2=>'蓝',3=>'紫',4=>'橙',5=>'金');//目前只开了1234

	if(isset($uid)){
		$sql = "select itemId ,count(1) cnt from user_equip ue inner JOIN  userprofile u on ue.uid=u.uid where u.banTime <2422569600000 and u.gmflag!=1 and $wheresql group by itemId order by itemId desc ";
		if(in_array($_COOKIE['u'],$privilegeArr)){
			$disHtml = $sql;
		}
		$data = array();
		foreach($selectServer as $key=>$value){
			$server = $key;
			$result = $page->executeServer($server, $sql, 3);
			if(!$result['error'] && $result['ret']['data']){
				foreach ($result['ret']['data'] as $curRow){
					$itemId=$curRow['itemId'];
					$equipmentName=$lang[(int)$equipXml_front[$itemId]['name']];//多语言

					$data[$itemId]['name']=$equipmentName;
					$data[$itemId]['level']=$equipXml_front[$itemId]['level'];
					$data[$itemId]['site']=$equipXml_front[$itemId]['site'];
					$data[$itemId]['cnt'] += $curRow['cnt'];

					$quality=(int)$equipXml_front[$itemId]['color'];
					$data[$itemId]['color'] = $colorArr[$quality];
				}
			}
		}
		$disHtml .= "<table class='listTable' style='text-align:center'><thead>";
		$disHtml .="<th align='center'  width='10%'>装备id</th>
				<th align='center'  width='20%'>装备名称</th>
				<th align='center'  width='20%'>装备位置</th>
				<th align='center'  width='20%'>装备等级</th>
				<th align='center'  width='5%'>装备品质</th>
				<th align='center'  width='10%'>此id装备数量</th></thead>";
		foreach ($data as $itemIdKye=>$value){
			$disHtml .="<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td>$itemIdKye</td><td>{$value['name']}</td><td>{$value['site']}</td><td>".$value['level']."</td><td>".$value['color']."</td><td>".$value['cnt']."</td></tr>";
		}
		$disHtml .="</table>";
	}else{
		if($_REQUEST['paylevel']){
			$paylevel = $_REQUEST['paylevel'];
			if($paylevel != 'all'){
				$wheresql .= " and paylevel = $paylevel";
			}
		}
		if($_REQUEST['lordlevel1']){
			$lordlevel1 = $_REQUEST['lordlevel1'];
			$wheresql .= " and level >= $lordlevel1";
		}
		if($_REQUEST['lordlevel2']){
			$lordlevel2 = $_REQUEST['lordlevel2'];
			$wheresql .= " and level <= $lordlevel2";
		}
		$serversql = '(' .implode(',',$selectServerids) . ') ';
		$start = $_REQUEST['start_time'] ? substr($_REQUEST['start_time'], 0, 10) : substr($start, 0, 10);
		$end = $_REQUEST['end_time'] ? substr($_REQUEST['end_time'], 0, 10) : substr($end, 0, 10);
		$table_start = date('Ymd', strtotime($start));
		$table_end = date('Ymd', strtotime($end));

		$sql = "select date,itemid ,sum(cnt) as cnt ,sum(users) as users,sum(allpeople) as allpeople from stat_allserver.pay_equip_level where date>=$table_start and date <=$table_end and sid in $serversql and $wheresql group by date,itemid order by date ";
		if(in_array($_COOKIE['u'],$privilegeArr)){
			$disHtml = $sql;
		}
		$result = query_infobright($sql);

		$alldata = $datearr = $sum = array();
		foreach ($result['ret']['data'] as $currow) {
			$date = $currow['date'];
			$itemid = $currow['itemid'];
			$datearr[$date] = $date;

			$alldata[$date][$itemid]['cnt'] += $currow['cnt'];
			$alldata[$date][$itemid]['users'] += $currow['users'];
			$alldata[$date][$itemid]['allpeople'] += $currow['allpeople'];

			$sum[$itemid] = $itemid;
		}
		$disHtml .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
		$disHtml .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>--</th><th>--</th><th>--</th>";
		foreach ($datearr as $date) {
			$disHtml .= "<th colspan='3'>$date</th>";
		}
		$disHtml .= "</tr></thead>";
		//副标题
		$disHtml .= "<tr><th>装备id</th><th>名称</th><th>颜色</th>";
		foreach ($datearr as $date) {
			$disHtml .= "<th>数量</th><th>人数</th><th>覆盖率</th>";
		}
		$disHtml .= "</tr><tbody id='adDataTable'>";
		ksort($sum);
		$charArr = array();
		$i=0;
		foreach ($sum as $key=>$item) {
			//$key 装备itemid
			$equipmentName=$lang[(int)$equipXml_front[$key]['name']];//多语言
			$equipmentName = trim($equipmentName);
			$color = $colorArr[(int)$equipXml_front[$key]['color']];
			$i++;
			$htmltmp = '';
			$htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>$equipmentName</td><td>$color</td>";
			$rateArr = array();
			foreach ($datearr as $date) {
				$rate = intval($alldata[$date][$key]['users']/$alldata[$date][$key]['allpeople']*10000)/100;
				$rateArr[] = $rate;
				$htmltmp .= "<td>{$alldata[$date][$key]['cnt']}</td><td>{$alldata[$date][$key]['users']}</td><td>{$rate}".'%'."</td>";
			}
			$htmltmp .= "</tr>";
			$disHtml .= $htmltmp;
			$charArr[$key]['name'] = " '$equipmentName' ";
			$charArr[$key]['rate'] = '['.implode(',',$rateArr).']';
			$charArr[$key]['dis'] = $i>9?"false":"true";
//				$charArr[$date][$key]['dis'] = "true";
		}
		$disHtml .= '</tbody></table></div>';

		$dateStr = '['.implode(',',$datearr).']';
	}

}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>