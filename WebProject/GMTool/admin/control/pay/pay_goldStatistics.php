<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
$developer = in_array($_COOKIE['u'],$privilegeArr);
function GetMonth($date)
{
	//切割出年份
	$tmp_year=substr($date,0,4);
	//切割出月份
	$tmp_mon =substr($date,4,2);
	//$tmp_nextmonth=mktime(0,0,0,$tmp_mon+1,1,$tmp_year);
	$tmp_forwardmonth=mktime(0,0,0,$tmp_mon-1,1,$tmp_year);
	//得到当前月的上一个月
	return $fm_forward_month=date("Ym",$tmp_forwardmonth);
}
if($_REQUEST['user'])
	$user = $_REQUEST['user'];
if(!$_REQUEST['end'] || !$_REQUEST['start']){
	$start = date("Y-m-d 00:00:00",time() -86400 * 2);
	$end = date("Y-m-d 23:59:59",time());
	$_REQUEST['start'] = $start; 
	$_REQUEST['end'] = $end;
}
$payUser=false;
$removegm=false;
$costg=false;
$addg=false;
$excRe=true;
$eventNames = $goldLink;
if(strval($_REQUEST['event']) === ''){
	$eventOptions = '<option selected></option>';
	foreach ($eventNames as $eventType => $eventName){
		if(!$eventName){
			continue;
		}
		$eventOptions .= "<option value={$eventType}>{$eventName}</option>";
	}
}else{
	$eventOptions = '<option></option>';
	foreach ($eventNames as $eventType => $eventName){
		if(!$eventName){
			continue;
		}
		if($_REQUEST['event']==$eventType){
			$eventOptions .= "<option value={$eventType} selected>{$eventName}</option>";
		}else{
			$eventOptions .= "<option value={$eventType}>{$eventName}</option>";
		}
	}
}
$eventNames['sum'] = '合计';
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on'){
// 		$selectServer[] = $server;
// 		$selectServerids[] = substr($server, 1);
// 	}
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['allServers']){
	$allServerFlag =true;
}

if($_REQUEST['display']){
	$serverDate=$_REQUEST['serverDate'];
	$sids=$_REQUEST['sids'];
	$pFlag=$_REQUEST['pFlag'];
	$typeCondition=$_REQUEST['typeCondition'];
	
	$whereSql = " where sid in ($sids) and date = $serverDate ";
	
	if($pFlag){
		$whereSql .= " and paidFlag =1 ";
	}
	$whereSql .= $typeCondition;
	$whereSql .= " and sumc != 0 ";
	$sql = "select type, date, sum(users) s_users, sum(times) s_times, sum(sumc) s_sumc from stat_allserver.pay_goldStatistics_daily_groupByType $whereSql group by type order by s_sumc;";
	$ret = query_infobright($sql);
	$typeArr=array();
	foreach ($ret['ret']['data'] as $curRow)
	{
		$type = $curRow['type'];
		$users[$curRow['date']][$type] += $curRow['s_users'];
		$times[$curRow['date']][$type] += $curRow['s_times'];
		$costs[$curRow['date']][$type] += -$curRow['s_sumc'];
		if (!in_array($type, $typeArr)){
			$typeArr[]=$type;
		}
	}
	
	$disHtml="<table class='listTable' style='text-align:center'><thead><th>日期</th><th>事件</th><th>数目</th><th>人数</th><th>次数</th>";
	foreach ($typeArr as $tValue){
		if (empty($goldLink[$tValue])){
			continue;
		}
		$disHtml.="<tr><td>$serverDate</td><td>".$goldLink[$tValue]."</td><td>".$costs[$serverDate][$tValue]."</td><td>".$users[$serverDate][$tValue]."</td><td>".$times[$serverDate][$tValue]."</td></tr>";
	}
	$disHtml .="</table>";
	
	echo $disHtml;
	exit();
}


if($_REQUEST['analyze']=='platform'){
	$start = $_REQUEST['start']?strtotime($_REQUEST['start']):strtotime($start);
	$end = $_REQUEST['end']?strtotime($_REQUEST['end']):strtotime($end);
	$startYmd = date('Ymd',$start);
	$startYm = date('Ym',$start);
	$endYmd = date('Ymd',$end);
	$endYm = date('Ym',$end);
	$userSql = " where 1=1 ";
	if($_REQUEST['event'] != null){
		$userSql .=" and type = '{$_REQUEST['event']}' ";
	}
	/* if($_REQUEST['costGold']=='costg'){
		$costg=true;
		$whereSql .=" and type not in (9,10,11,22,47,54) ";
	} */
    $groupby = "";
    if($_REQUEST['groupbyitem'] && $_REQUEST['event'] == 12){
        $groupSelected = true;
        $groupby = " ,param1 ";
    }
    if($_REQUEST['event'] == 55 && ($_REQUEST['groupByWood'] || $_REQUEST['groupByFood'] || $_REQUEST['groupByIron'] || $_REQUEST['groupByStone'] || $_REQUEST['groupBySilver'])){
	    	$groupby = " ,param1 ";
	    	if($_REQUEST['groupByWood']){
	    		$groupWoodSelected = true;
	    	}
	    	if ($_REQUEST['groupByFood']){
	    		$groupFoodSelected = true;
	    	}
	    	if ($_REQUEST['groupByIron']){
	    		$groupIronSelected = true;
	    	}
	    	if ($_REQUEST['groupByStone']){
	    		$groupStoneSelected = true;
	    	}
	    	if ($_REQUEST['groupBySilver']){
	    		$groupSilverSelected = true;
	    	}
    }
    if($_REQUEST['event'] == 12){
        $showgroupby = true;
    }
    if($_REQUEST['event'] == 55){
    		$showResource = true;
    }
	$userSql .= " and date >= $startYmd and date <= $endYmd ";
	$userSql .= " and gmflag != 1 and gmflag != 10 ";
	if($_REQUEST['payuser']){
		$payUser=true;
		//$whereSql .= " and paidFlag =1 ";
		$userSql .= " and payTotal >0 ";
	}
	//$whereSql .= " and sumc != 0 ";
	$userSql .= " and cost !=0";
	$sids = implode(',', $selectServerids);
	$allServer =array();
	if(!$_REQUEST['event']){
		$whereSql = " where sid in ($sids) and date >= $startYmd and date <= $endYmd ";
		if($_REQUEST['costGold']=='costg'){
			$whereSql.= " and costType=1 ";
		}
		if($_REQUEST['payuser']){
			$payUser=true;
			$whereSql .= " and paidFlag =1 ";
		}
		$whereSql .= " and sumc != 0 ";
		$sql = "select sid, date, sum(users) s_users, sum(times) s_times, sum(sumc) s_sumc from stat_allserver.pay_goldStatistics_daily $whereSql group by sid, date order by date desc, sid";
		$ret = query_infobright($sql);
		foreach ($ret['ret']['data'] as $curRow)
		{
			$server = 's'.$curRow['sid'];
			$users[$server][$curRow['date']] += $curRow['s_users'];
			$times[$server][$curRow['date']] += $curRow['s_times'];
			$costs[$server][$curRow['date']] += -$curRow['s_sumc'];
			$eventAll[$curRow['date']]['users'] += $curRow['s_users'];
			$eventAll[$curRow['date']]['times'] += $curRow['s_times'];
			$eventAll[$curRow['date']]['costs'] += -$curRow['s_sumc'];
			if(in_array($server, $allServer)){
				continue;
			}
			$allServer[]=$server;
		}
	}elseif ($_REQUEST['event'] && !$groupby){
		$whereSql = " where sid in ($sids) and date >= $startYmd and date <= $endYmd and type =".$_REQUEST['event']." ";
		
		if($_REQUEST['payuser']){
			$payUser=true;
			$whereSql .= " and paidFlag =1 ";
		}
		$whereSql .= " and sumc != 0 ";
		$sql = "select sid, date, sum(users) s_users, sum(times) s_times, sum(sumc) s_sumc from stat_allserver.pay_goldStatistics_daily_groupByType $whereSql group by sid, date order by date desc, sid";
		$ret = query_infobright($sql);
		foreach ($ret['ret']['data'] as $curRow)
		{
			$server = 's'.$curRow['sid'];
			$users[$server][$curRow['date']] += $curRow['s_users'];
			$times[$server][$curRow['date']] += $curRow['s_times'];
			$costs[$server][$curRow['date']] += -$curRow['s_sumc'];
			$eventAll[$curRow['date']]['users'] += $curRow['s_users'];
			$eventAll[$curRow['date']]['times'] += $curRow['s_times'];
			$eventAll[$curRow['date']]['costs'] += -$curRow['s_sumc'];
			if(in_array($server, $allServer)){
				continue;
			}
			$allServer[]=$server;
		}
	}else if ($groupby){
		$whereSql = " where sid in ($sids) and date >= $startYmd and date <= $endYmd and type =".$_REQUEST['event']." ";
		
		if($_REQUEST['payuser']){
			$payUser=true;
			$whereSql .= " and paidFlag =1 ";
		}
		$whereSql .= " and sumc != 0 ";
		$sql = "select sid, date $groupby, sum(users) s_users, sum(times) s_times, sum(sumc) s_sumc from stat_allserver.pay_goldStatistics_daily_groupByGoodsAndResource $whereSql group by sid, date $groupby order by date desc, sid";
		$ret = query_infobright($sql);

		foreach ($ret['ret']['data'] as $curRow)
		{
			$server = 's'.$curRow['sid'];
			$users[$server][$curRow['date']][$curRow['param1']] += $curRow['s_users'];
			$times[$server][$curRow['date']][$curRow['param1']] += $curRow['s_times'];
			$costs[$server][$curRow['date']][$curRow['param1']] += -$curRow['s_sumc'];
			$eventAll[$curRow['date']]['i_'.$curRow['param1']]['users'] += $curRow['s_users'];
			$eventAll[$curRow['date']]['i_'.$curRow['param1']]['times'] += $curRow['s_times'];
			$eventAll[$curRow['date']]['i_'.$curRow['param1']]['costs'] += -$curRow['s_sumc'];
			if(in_array($server, $allServer)){
				continue;
			}
			$allServer[]=$server;
		}
		if($showgroupby){
			foreach ($eventAll as $date=>&$v1) {
				$weight=array();
				foreach ($v1 as $ite=>$v2) {
					$weight[]=$v2['costs'];
				}
				array_multisort($weight,SORT_DESC,$v1);
			}
			foreach ($eventAll as $date=>&$v1) {
				foreach ($v1 as $ite=>$v2) {
					if(strpos($ite,'i_') !== false){
						$newid = substr($ite,2);
						unset($v1[$ite]);
						$v1[$newid]=$v2;
					}
				}
			}
		}
		
		
		/*for($tempDate=$endYm;$tempDate>=$startYm;){
			$table="snapshot_allserver.gold_cost_record_full_".$tempDate;
			$sql = "select sid, date $groupby , count(distinct(userId)) s_users, count(userId) s_times, sum(cost) s_sumc from $table $userSql and sid in ($sids) group by sid, date $groupby order by date desc, sid;";
			if($developer) echo $sql;
	        $ret = query_infobright($sql);
	        //按物品group
	        if(!empty($groupby)){
	            foreach ($ret['ret']['data'] as $curRow)
	            {
	                $server = 's'.$curRow['sid'];
	                $users[$server][$curRow['date']][$curRow['param1']] += $curRow['s_users'];
	                $times[$server][$curRow['date']][$curRow['param1']] += $curRow['s_times'];
	                $costs[$server][$curRow['date']][$curRow['param1']] += -$curRow['s_sumc'];
	                $eventAll[$curRow['date']]['i_'.$curRow['param1']]['users'] += $curRow['s_users'];
	                $eventAll[$curRow['date']]['i_'.$curRow['param1']]['times'] += $curRow['s_times'];
	                $eventAll[$curRow['date']]['i_'.$curRow['param1']]['costs'] += -$curRow['s_sumc'];
	                if(in_array($server, $allServer)){
	                    continue;
	                }
	                $allServer[]=$server;
	            }
				if($showgroupby){
					foreach ($eventAll as $date=>&$v1) {
						foreach ($v1 as $ite=>$v2) {
							$weight[]=$v2['costs'];
						}
						array_multisort($weight,SORT_DESC,$v1);
					}
					foreach ($eventAll as $date=>&$v1) {
						foreach ($v1 as $ite=>$v2) {
							if(strpos($ite,'i_') !== false){
								$newid = substr($ite,2);
								$v1[$newid]=$v2;
								unset($v1[$ite]);
							}
						}
					}
				}
	        }else{
	            foreach ($ret['ret']['data'] as $curRow)
	            {
	                $server = 's'.$curRow['sid'];
	                $users[$server][$curRow['date']] += $curRow['s_users'];
	                $times[$server][$curRow['date']] += $curRow['s_times'];
	                $costs[$server][$curRow['date']] += -$curRow['s_sumc'];
	                $eventAll[$curRow['date']]['users'] += $curRow['s_users'];
	                $eventAll[$curRow['date']]['times'] += $curRow['s_times'];
	                $eventAll[$curRow['date']]['costs'] += -$curRow['s_sumc'];
	                if(in_array($server, $allServer)){
	                    continue;
	                }
	                $allServer[]=$server;
	            }
	        }
			$tempDate=GetMonth($tempDate);
		}*/
	}
// 	if($developer) print_r($eventAll);
	//语言文件
	$lang = loadLanguage();
	$clintXml = loadXml('goods','goods');
	if(!empty($groupby)){
        //print_r($sql);
        //echo "<div style='margin: 5px 0;'>获得数据".(int)$count."条"." 付费人数".(int)$payUser."人</div>";
        $html = "<table class='listTable' style='text-align:center'><thead><th></th><th colspan='4'>合计</th>";
        foreach ($selectServer as $serverKey=>$serInfo){
            $th1 .="<th colspan='4'>$serverKey</th>";
            $th2 .="<th width='100px'>物品名称</th><th>数目</th><th>人数</th><th>次数</th>";

        }
        if ($allServerFlag){
        		$th1='';
        		$th2='';
        }
        $html .=$th1 ."</thead><thead><th>日期</th><th width='100px'>物品名称</th><th>数目</th><th>人数</th><th>次数</th>" .$th2 ."</thead>";
		$style1='';
		$style2='';
		
        foreach($eventAll as $dateData=>$temptotal){
			$i=0;
            foreach($temptotal as $item=>$total){
            	if($i==0){
            		$style1='<strong>';
            		$style2='</strong>';
            	}else {
        			$style1='';
        			$style2='';
        		}
        		if ($showResource){
	        		$item=substr($item, 2);
	        		if ($item==0 && $groupWoodSelected){
	        			$reStr='木头';
	        		}elseif ($item==1 && $groupStoneSelected){
	        			$reStr='秘银';
	        		}elseif ($item==2 && $groupIronSelected){
	        			$reStr='铁矿';
	        		}elseif ($item==3 && $groupFoodSelected){
	        			$reStr='粮食';
	        		}elseif ($item==4 && $groupSilverSelected){
	        			$reStr='钢材';
	        		}else {
	        			continue;
	        		}
	            	$html .="<tbody><tr><td>$style1 $dateData $style2</td><td>".$reStr."</td><td><strong>".$total['costs']."</strong></td><td>".$total['users']."</td><td>".$total['times']."</td>";
				if (!$allServerFlag){
		            	foreach ($selectServer as $serverKey=>$serInfo){
		            		$html .="<td>".$reStr."</td><td><strong>". $costs[$serverKey][$dateData][$item] ."</strong></td><td>". $users[$serverKey][$dateData][$item] ."</td><td>". $times[$serverKey][$dateData][$item] ."</td>";
		            	}
				}
        		}elseif ($showgroupby){
        			$html .="<tbody><tr><td>$style1 $dateData $style2</td><td>".($lang[(int)$clintXml[$item]['name']]?$lang[(int)$clintXml[$item]['name']]:$item)."</td><td><strong>".$total['costs']."</strong></td><td>".$total['users']."</td><td>".$total['times']."</td>";
        			if (!$allServerFlag){
	        			foreach ($selectServer as $serverKey=>$serInfo){
	        				$html .="<td>".($lang[(int)$clintXml[$item]['name']]?$lang[(int)$clintXml[$item]['name']]:$item)."</td><td><strong>". $costs[$serverKey][$dateData][$item] ."</strong></td><td>". $users[$serverKey][$dateData][$item] ."</td><td>". $times[$serverKey][$dateData][$item] ."</td>";
	        			}
        			}
        		}
            	$html .="</tr></tbody>";
            	$i++;
            }
        }
        $html .= "</table><br>";
    }else{
        //print_r($sql);
        //echo "<div style='margin: 5px 0;'>获得数据".(int)$count."条"." 付费人数".(int)$payUser."人</div>";
        $html = "<table class='listTable' style='text-align:center'><thead><th></th><th colspan='3'>合计</th>";
        foreach ($selectServer as $serverKey=>$serInfo){
            $th1 .="<th colspan='3'>$serverKey</th>";
            $th2 .="<th>数目</th><th>人数</th><th>次数</th>";

        }
        if ($allServerFlag){
        		$th1='';
        		$th2='';
        }
        $html .=$th1 ."</thead><thead><th>日期</th><th>数目</th><th>人数</th><th>次数</th>" .$th2 ."</thead>";
        $style1='';
        $style2='';
        foreach($eventAll as $dateData=>$total){
	        $i=0;
        	if($i==0){
        		$style1='<strong>';
            	$style2='</strong>';
        	}else {
        		$style1='';
        		$style2='';
        	}
        		if (!$_REQUEST['event']){
        			$pFlag=$_REQUEST['payuser']?1:0;
        			$typeCondition='';
        			if($_REQUEST['costGold']=='costg'){
        				$typeCondition=" and type not in (9,10,11,22,47,54) ";
        			}
	        		$html .='<tbody><tr><td>'.$style1.$dateData.$style2.'</td><td id="'.$dateData.'"><a href="'.'javascript:void(edit('."'".$dateData."','".$sids."','".$pFlag."','".$typeCondition."'))".'">'.'<strong>'.$total['costs'].'</strong></a></td><td>'.$total['users'].'</td><td>'.$total['times'].'</td>';
        		}else {
	            $html .="<tbody><tr><td>$style1 $dateData $style2</td><td><strong>".$total['costs']."</strong></td><td>".$total['users']."</td><td>".$total['times']."</td>";
        		}
            if (!$allServerFlag){
	            foreach ($selectServer as $serverKey=>$serInfo){
	                $html .="<td><strong>". $costs[$serverKey][$dateData] ."</strong></td><td>". $users[$serverKey][$dateData] ."</td><td>". $times[$serverKey][$dateData] ."</td>";
	            }
            }
            $html .="</tr></tbody>";
        }
        $html .= "</table><br>";
    }

}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>