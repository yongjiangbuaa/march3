<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*30);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['end_time']){
	$end = date("Y-m-d",time());
}else {
	$end = $_REQUEST['end_time'];
}
$finalColimns=array(
		'account_id',
		'compaign_name',
		'compaign_id',
		'adset_id',
);
$columns=array(
		'reach',
		'impressions',
		'frequency',
		'spend',
		'unique_clicks',
		'actions',
		'regcount',
		'pay1',
		'pay3',
		'pay7',
		'pay14',
		'pay30',
		'pay60',
		'remain1',
		'remain3',
		'remain7',
		'remain14',
		'remain30',
		'remain60',
);
$roiArray=array(
		'roi1'=>'pay1',
		'roi3'=>'pay3',
		'roi7'=>'pay7',
		'roi14'=>'pay14',
		'roi30'=>'pay30',
		'roi60'=>'pay60',
);
$rateArray=array(
		'rate1'=>'remain1',
		'rate3'=>'remain3',
		'rate7'=>'remain7',
		'rate14'=>'remain14',
		'rate30'=>'remain30',
		'rate60'=>'remain60',
);
$startYmd=date('Y-m-d',strtotime($start));
$endYmd=date('Y-m-d',strtotime($end));
$link = get_ad_connection();
$sql="select * from fb_ad_result where date between '$startYmd' and '$endYmd';";
$total=array();
$dateArray=array();
$res = mysqli_query($link,$sql);
$accountArray=array();
$campaignArray=array();
$adSetAarry=array();
$creativeIdArray=array();
while ($row = mysqli_fetch_assoc($res)){
	foreach ($columns as $column){
		$temp=0;
		if ($column=='actions'){
			$jsonTemp=json_decode($row[$column],true);
			foreach ($jsonTemp as $jsonVal){
				if ($jsonVal['action_type']=='mobile_app_install' || $jsonVal['action_type']=='app_install'){
					$temp+=$jsonVal['value'];
				}
			}
		}else {
			$temp=$row[$column];
		}
		$accountArray[$row['account_id']][$column]+=$temp;
		$campaignArray[$row['account_id']][$row['compaign_id']][$column]+=$temp;
		$adSetAarry[$row['account_id']][$row['compaign_id']][$row['adset_id']][$column]+=$temp;
		$creativeIdArray[$row['account_id']][$row['compaign_id']][$row['adset_id']][$row['ad_id']][$column]+=$temp;
	}
}
mysqli_close($link);
$data=array();
$i=1;
foreach ($accountArray as $accKey=>$accVal){
	$one=array()	;
	$one['id']=$i;
	$one['name']="account_id: ".$accKey;
	$one['state']='closed';
	$one=$one+$accVal;
	//比例
	foreach ($rateArray as $k=>$v){
		$one[$k]=$one['regcount']>0? number_format($one[$v] / $one['regcount'] *100, 2) :0;
	}
	foreach ($roiArray as $rk=>$rv){
		$one[$rk]=$one['spend']>0? number_format($one[$rv] / $one['spend'] *100, 2) :0;
	}
	$tempCampaign=array();
	$i2=1;
	foreach ($campaignArray[$accKey] as $camKey=>$camVal){
		$one2=array();
		$one2['id']=$i.$i2;
		$one2['name']="compaign_id: ".$camKey;
		$one2['state']='closed';
		$one2=$one2+$camVal;
		foreach ($rateArray as $k=>$v){
			$one2[$k]=$one2['regcount']>0? number_format($one2[$v] / $one2['regcount'] *100, 2) :0;
		}
		foreach ($roiArray as $rk=>$rv){
			$one2[$rk]=$one2['spend']>0? number_format($one2[$rv] / $one2['spend'] *100, 2) :0;
		}
		$tempAdset=array();
		$i3=1;
		foreach ($adSetAarry[$accKey][$camKey] as $setKey=>$setVal){
			$one3=array();
			$one3['id']=$i.$i2.$i3;
			$one3['name']="adset_id: ".$setKey;
			$one3['state']='closed';
			$one3=$one3+$setVal;
			foreach ($rateArray as $k=>$v){
				$one3[$k]=$one3['regcount']>0? number_format($one3[$v] / $one3['regcount'] *100, 2) :0;
			}
			foreach ($roiArray as $rk=>$rv){
				$one3[$rk]=$one3['spend']>0? number_format($one3[$rv] / $one3['spend'] *100, 2) :0;
			}
			$tempCreativeId=array();
			$i4=1;
			foreach ($creativeIdArray[$accKey][$camKey][$setKey] as $idKey=>$idVal){
				$one4=array();
				$one4['id']=$i.$i2.$i3;
				$one4['name']="creative_id: ".$idKey;
				$one4=$one4+$idVal;
				foreach ($rateArray as $k=>$v){
					$one4[$k]=$one4['regcount']>0? number_format($one4[$v] / $one4['regcount'] *100, 2) :0;
				}
				foreach ($roiArray as $rk=>$rv){
					$one4[$rk]=$one4['spend']>0? number_format($one4[$rv] / $one4['spend'] *100, 2) :0;
				}
				$tempCreativeId[]=$one4;
			}
			$one3['children']=$tempCreativeId;
			$tempAdset[]=$one3;
			$i3++;
		}
		$one2['children']=$tempAdset;
		$tempCampaign[]=$one2;
		$i2++;
	}
	$one['children']=$tempCampaign;
	$data[]=$one;
	$i++;
}
$result=json_encode($data);
file_put_contents(ADMIN_ROOT."/log/treegrid_data1.json", $result);
 

include( renderTemplate("{$module}/{$module}_{$action}") );
?>