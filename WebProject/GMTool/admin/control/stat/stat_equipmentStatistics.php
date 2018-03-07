<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$showData = false;
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['display']){
	$uid=$_REQUEST['uid'];
	$server=$_REQUEST['server'];
	$sql = "select u.uid uid,ue.itemId itemId from userprofile u inner join user_equip ue on u.uid=ue.uid where u.uid='$uid';";
	$lang = loadLanguage();
	$clintXml = loadXml('equipment','equipment');
	$result = $page->executeServer($server, $sql, 3);
	if(!$result['error'] && $result['ret']['data']){
		foreach ($result['ret']['data'] as $curRow){
			$uid=$curRow['uid'];
			$itemId=$curRow['itemId'];
			$equipmentName=$lang[(int)$clintXml[$itemId]['name']];
			$data[$itemId]['name']=$equipmentName;
			$data[$itemId]['level']=substr($itemId, 2,2);
			$quality=substr($itemId, strlen($itemId)-1);
			if($quality==0){
				$data[$itemId]['quality']='白';
			}else if($quality==1){
				$data[$itemId]['quality']='绿';
			}else if($quality==2){
				$data[$itemId]['quality']='蓝';
			}else if($quality==3){
				$data[$itemId]['quality']='紫';
			}else if($quality==4){
				$data[$itemId]['quality']='橙';
			}else if($quality==5){
				$data[$itemId]['quality']='金';
			}
			$numSql="select uid, itemId,count(itemId) countNums from user_equip where uid='$uid' and itemId='$itemId' group by uid, itemId;";
			$numResult = $page->executeServer($server, $numSql, 3);
			if(!$numResult['error'] && $numResult['ret']['data']){
				$data[$itemId]['num']=$numResult['ret']['data'][0]['countNums'];
			}
		}
	}
	$disHtml = "<div><table class='listTable' style='text-align:center'><thead>";
	$disHtml .="<th align='center'  width='20%'>装备id</th>
				<th align='center'  width='20%'>装备名称</th>
				<th align='center'  width='20%'>装备等级</th>
				<th align='center'  width='20%'>装备品质</th>
				<th align='center'  width='20%'>此id装备数量</th></thead>";
	foreach ($data as $itemIdKye=>$value){
		$disHtml .="<tr><td>$itemIdKye</td><td>".$value['name']."</td><td>".$value['level']."</td><td>".$value['quality']."</td><td>".$value['num']."</td></tr>";
	}
	$disHtml .="</table></div>";
	echo $disHtml;
	exit();
	
}


$levelArray=array('01','05','10','15','20','25');
$equNameArray=array('枫木','水晶','骨头','布料','石榴石','青铜','琥珀','兽角','钴矿石','翡翠','毛皮','草药');
if($_REQUEST['analyze']=='view'){
	
	$lang = loadLanguage();
	$clintXmlItem = loadXml('goods','goods');
	$clintXml = loadXml('equipment','equipment');
	
	$levelMin=$_REQUEST['levelMin'];
	$levelMax=$_REQUEST['levelMax'];
	$goldMin=$_REQUEST['goldMin'];
	$goldMax=$_REQUEST['goldMax'];
	$sql = "select ub.level,u.name,u.uid,u.payTotal,ur.silver from userprofile u inner join user_building ub on u.uid=ub.uid inner join user_resource ur on u.uid=ur.uid where ub.itemId=400000 and ub.level>=$levelMin and ub.level<=$levelMax and u.payTotal between $goldMin and $goldMax order by level desc;";

	//echo $sql;

	$itemDetail=array();
	$equipmentDetail=array();
	$uids=array();
	foreach ($selectServer as $server=>$sevInfo){
		$result = $page->executeServer($server, $sql, 3);
		//print_r($result);
		foreach ($result['ret']['data'] as $curRow){
			$uid=$curRow['uid'];
			for($i=0;$i<=5;$i++){
				$item[$server][$uid][$i]=0;
				foreach ($levelArray as $levKey){
					$equip[$server][$uid][$levKey][$i]=0;
				}
			}
			if(!in_array($uid, $uids)){
				$uids[]=$uid;
			}
			$uidsId=implode("','", $uids);
			$data[$server][$uid]['level']=$curRow['level'];
			$data[$server][$uid]['name']=$curRow['name'];
			$data[$server][$uid]['payTotal']=$curRow['payTotal'];
			$data[$server][$uid]['silver']=$curRow['silver'];
		}
		$equipSql="select uid, itemId,count(itemId) countNums from user_equip where uid in('$uidsId') group by uid, itemId;";
		$result = $page->executeServer($server, $equipSql, 3);
		foreach ($result['ret']['data'] as $curRow){
			$uidEquip=$curRow['uid'];
			$lev=substr($curRow['itemId'], 2,2);
			$qua=substr($curRow['itemId'], strlen($curRow['itemId'])-1);
			$equip[$server][$uidEquip][$lev][$qua]+=$curRow['countNums'];
			
			$equipmentName=$lang[(int)$clintXml[$curRow['itemId']]['name']];
			//echo $equipmentName."\n";
			$equipmentDetail[$server][$lev][$equipmentName][$qua]+=$curRow['countNums'];
		}
		$itemSql="select ownerId,itemId,count(itemId) countNums from user_item where ownerId in('$uidsId') group by ownerId, itemId;";
		$result = $page->executeServer($server, $itemSql, 3);
		//print_r($result);
		foreach ($result['ret']['data'] as $curRow){
			$uidItem=$curRow['ownerId'];
			if(substr($curRow['itemId'], 0,3)=='201'){
				$key = substr($curRow['itemId'], strlen($curRow['itemId'])-1);
				$item[$server][$uidItem][$key]+=$curRow['countNums'];
				//echo $server.','.$uidItem.','.$key.','.$item[$server][$uidItem][$key]."\n";
				$itemName=$lang[(int)$clintXmlItem[$curRow['itemId']]['name']];
				$name=substr(trim($itemName), 4);
				$itemDetail[$server][$name][$key]+=$curRow['countNums'];
			}
		}
		//print_r($item);
		foreach ($equip[$server] as $uidKey=>$value){
			$equipTotal[$server][$uidKey] =$value['01']['0']+$value['01']['1']+$value['01']['2']+$value['01']['3']+$value['01']['4']+$value['01']['5']
											+$value['05']['0']+$value['05']['1']+$value['05']['2']+$value['05']['3']+$value['05']['4']+$value['05']['5']
											+$value['10']['0']+$value['10']['1']+$value['10']['2']+$value['10']['3']+$value['10']['4']+$value['10']['5']
											+$value['15']['0']+$value['15']['1']+$value['15']['2']+$value['15']['3']+$value['15']['4']+$value['15']['5']
											+$value['20']['0']+$value['20']['1']+$value['20']['2']+$value['20']['3']+$value['20']['4']+$value['20']['5']
											+$value['25']['0']+$value['25']['1']+$value['25']['2']+$value['25']['3']+$value['25']['4']+$value['25']['5'];
			$equipStr[$server][$uidKey]['1']=$value['01']['0'].'-'.$value['01']['1'].'-'.$value['01']['2'].'-'.$value['01']['3'].'-'.$value['01']['4'].'-'.$value['01']['5'];
			$equipStr[$server][$uidKey]['5']=$value['05']['0'].'-'.$value['05']['1'].'-'.$value['05']['2'].'-'.$value['05']['3'].'-'.$value['05']['4'].'-'.$value['05']['5'];
			$equipStr[$server][$uidKey]['10']=$value['10']['0'].'-'.$value['10']['1'].'-'.$value['10']['2'].'-'.$value['10']['3'].'-'.$value['10']['4'].'-'.$value['10']['5'];
			$equipStr[$server][$uidKey]['15']=$value['15']['0'].'-'.$value['15']['1'].'-'.$value['15']['2'].'-'.$value['15']['3'].'-'.$value['15']['4'].'-'.$value['15']['5'];
			$equipStr[$server][$uidKey]['20']=$value['20']['0'].'-'.$value['20']['1'].'-'.$value['20']['2'].'-'.$value['20']['3'].'-'.$value['20']['4'].'-'.$value['20']['5'];
			$equipStr[$server][$uidKey]['25']=$value['25']['0'].'-'.$value['25']['1'].'-'.$value['25']['2'].'-'.$value['25']['3'].'-'.$value['25']['4'].'-'.$value['25']['5'];
			
		}
		//print_r($item[$server]);
		foreach ($item[$server] as $uidKey=>$value){
// 			print_r($value);
			$itemStr[$server][$uidKey]=$value[0].'-'.$value[1].'-'.$value[2].'-'.$value[3].'-'.$value[4].'-'.$value[5];
			$itemTotal[$server][$uidKey]=intval( $value[0])+intval( $value[1])+intval( $value[2])+intval( $value[3])+intval( $value[4])+intval( $value[5]);
			//echo $itemStr[$server][$uidKey];
		}
		
	}
	if($data){
		$showData=true;
	}else {
		$headAlert="装备信息查询失败";
	}

}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>