<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
$headLine = "转盘统计的个人购买记录";
$headAlert = "";
$resourceArray=array('wood','stone','iron','food','money','gold','exp','goods','general','power','honor','alliance_point','铜币','龙币');
$lang = loadLanguage();
$clintXml = loadXml('goods','goods');
$outData=array();
$inData=array();
if ($type=='view') {
	$startDate = substr($_REQUEST['startDate'],0,10);
	$endDate = substr($_REQUEST['endDate'],0,10);
	$sTime= strtotime($startDate)*1000;
	$eTime = (strtotime($endDate)+86400)*1000;
	if(!$username && (!$useruid)){
		$headAlert='请输入玩家姓名或者UID';
	}else{
		$whereSql='';
// 		if($username){
// 			$whereSql=" name='$username' ";
// 		}else {
// 			$whereSql=" uid='$useruid' ";
// 		}
		
		if($username){
			$account_list = cobar_getValidAccountList('name', $username);
			$uid = $account_list[0]['gameUid'];
			$whereSql=" uid='$uid' ";
		}else{
			$whereSql=" uid='$useruid' ";
		}
		
		//转盘转动
		$sql="select createTime,cost,result from lottery_log where $whereSql and type=1 and createTime between $sTime and $eTime order by createTime desc,cost desc;";
		$result = $page->execute($sql, 3);
		$i=1;
		foreach ($result['ret']['data'] as $curRow){
			$outData[$i]['time']=date('Y-m-d H:i:s',$curRow['createTime']/1000);
			$outData[$i]['cost']=$curRow['cost'];
			$temp=explode(':', $curRow['result']);
			if($temp[0]==100){
				$outData[$i]['name']='宝箱*'.$temp[1];
			}else if ($temp[0]<14){
				$outData[$i]['name']=$resourceArray[$temp[0]].'*'.$temp[1];
			}else{
				$outData[$i]['name']=$lang[(int)$clintXml[$temp[0]]['name']].'*'.$temp[1];
			}
			$i++;
		}
		
		//翻牌
		$sql="select createTime,lotteryId,lotteryInfo,position,cost from lottery_log where $whereSql and type=2 and createTime between $sTime and $eTime order by createTime desc,cost desc;";
		$result = $page->execute($sql, 3);
		$j=1;
		foreach ($result['ret']['data'] as $curRow){
			$inData[$j]['time']=date('Y-m-d H:i:s',$curRow['createTime']/1000);
			$inData[$j]['lotteryId']=$curRow['lotteryId'];
			$temp=explode('|', $curRow['lotteryInfo']);
			for($m=1;$m<=9;$m++){
				$tempName='';
				$temp2=explode(':', $temp[$m-1]);
				if($temp2[0]==100){
					$tempName='宝箱*'.$temp2[1];
				}else if ($temp2[0]<14){
					$tempName=$resourceArray[$temp2[0]].'*'.$temp2[1];
				}else if($temp2[0]>=200 && $temp2[0]<=205){
					switch ($temp2[0]){
						case 200:
							$tempName='2倍*'.$temp2[1];
							break;
						case 201:
							$tempName='3倍*'.$temp2[1];
							break;
						case 202:
							$tempName='5倍*'.$temp2[1];
							break;
						case 203:
							$tempName='10倍*'.$temp2[1];
							break;
						case 204:
							$tempName='15倍*'.$temp2[1];
							break;
						case 205:
							$tempName='20倍*'.$temp2[1];
							break;
					}
				}else {
					$tempName=$lang[(int)$clintXml[$temp2[0]]['name']].'*'.$temp2[1];
				}
				$inData[$j]['p'.$m]=$tempName;
			}
			$inData[$j]['position']=$curRow['position'];
			$inData[$j]['cost']=$curRow['cost'];
			$j++;
		}
	}
	if($outData || $inData){
		$showData = true;
	}else{
		$headAlert='数据查询失败';
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>