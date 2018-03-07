<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(isset($_REQUEST['getCount']) && $_REQUEST['getCount']){
    $sql = "select count(1) num from userprofile";
    $ret= $page->execute($sql, 3);
    $num = $ret['ret']['data'][0]['num'];
    exit("<h3>当前总人数:{$num}</h3>");
}
if(!$_REQUEST['start'])
	$regStart = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$regEnd = date("Y-m-d 23:59:59",time());



if (isset($_REQUEST['page'])) {
	try {
	    $resource1 = $resource2 = $power1 = $power2 =$solider1=$solider2 = '';
	    $where = 'where u.level >= 0 ';
	    //  
	    if($_REQUEST['showResource']){
	        $resource1 = ' inner join user_resource ur on u.uid = ur.uid ';
	        $resource2 = ' , ur.* ';
	    }
	    if($_REQUEST['showPower']){
	        $power1 = ' inner join playerinfo po on u.uid= po.uid ';
	        $power2 = ',po.power ';
	        if($_REQUEST['powermin']  > 0){
	            $where .= ' and po.power >= '.$_REQUEST['powermin'];
	        }
	        if($_REQUEST['powermax']){
	        	$where .= ' and po.power <= '.$_REQUEST['powermax'];
	        }
	    }
	    if($_REQUEST['showSolider'] ){
	        $solider1 = ' inner join (select uid, sum(free+pve+march+defence+train) soldiers from user_army group by uid)  ua on u.uid=ua.uid ';
	        $solider2 = ' ,ua.soldiers armyNum ';
	        /*if($_REQUEST['troopsmin'] > 10 && $_REQUEST['troopsmax'] ){
	        	$where .= ' and ua.soldiers <= '.$_REQUEST['troopsmax'] .' and ua.soldiers >= '.$_REQUEST['troopsmin'];
	        }*/
	        if($_REQUEST['troopsmin'] > 0){
	            $where .= ' and ua.soldiers >= '.$_REQUEST['troopsmin'];
	        }
	        if($_REQUEST['troopsmax'] ){
	        	$where .= ' and ua.soldiers <= '.$_REQUEST['troopsmax'];
	        }
	    }
		$limit = 100;
		//inner join user_world uw on u.uid = uw.uid inner join worldpoint p on uw.pointId = p.id
		$className = 'userprofile u inner join user_building ub on u.uid = ub.uid and ub.itemId = 400000 '. $resource1 . $solider1.$power1;

		$country=$_REQUEST['country'];
		$countryWhere = null;
		if ($country=="Unknown"){
			$countryWhere = " and (nationalFlag is null or nationalFlag='' or nationalFlag='Unknown') ";
		}
//		elseif($country=="GunFu"){
//			$countryWhere2 = " and p.uid not in (select r.uid from stat_reg r where 1=1 $regPf) ";
//		}
		elseif(!empty($country)&&$country!="ALL"){
			$countryWhere = " and nationalFlag ='$country' ";
		}
		$where .= $countryWhere;
		if($_REQUEST['levelMin'] > 0){
			$where .= ' and u.level >= '.$_REQUEST['levelMin'];
		}
		if($_REQUEST['levelMax'] && $_REQUEST['levelMax'] <= 99){
			$where .= ' and u.level <= '.$_REQUEST['levelMax'];
		}
		if($_REQUEST['regMin'] !== null){
			$where .= ' and u.regTime > '.strtotime($_REQUEST['regMin'])*1000;
		}
		if($_REQUEST['regMax'] !== null){
			$where .= ' and u.regTime < '.strtotime($_REQUEST['regMax'])*1000;
		}
		if($_REQUEST['ubLevelMin'] > 0){
			$where .= ' and ub.level >= '.$_REQUEST['ubLevelMin'];
		}
		if($_REQUEST['ubLevelMax'] && $_REQUEST['ubLevelMax'] <= 99){
			$where .= ' and ub.level <= '.$_REQUEST['ubLevelMax'];
		}



		$sql = "select count(1) sum from {$className} {$where}";
		$result = $page->execute($sql,3);
		$count = $result['ret']['data'][0]['sum'];
		//实现分页
		$pager = page($count, $_REQUEST['page'], $limit);
		$index = $pager['offset'];
		$order = '';
// 		if($_REQUEST['byvip'])
// 			$order .= "order by u.vip desc";
		if($_REQUEST['bylevel'])
		{
			$order .= "order by u.level desc";
		}
		elseif($_REQUEST['byblv']){
			$order .= "order by ub.level desc";
		}
		elseif($_REQUEST['byarmy']){
			$order .= "order by ua.soldiers desc";
		}
		elseif($_REQUEST['bypower']){
			$order .= "order by po.power desc";
		}
		$sql = "select u.*, ub.level ubLevel $resource2 $solider2 $power2 
		  from {$className} {$where} {$order} limit {$index},{$limit}";
		$result = $page->execute($sql,3);
		$i = 0;
		foreach ($result['ret']['data'] as $key => $curRow) {
			$temp = $curRow;
			$temp['num'] = $i++;
			$temp['soldiers'] = $soldiersList[$temp['uid']];
			$sqlDatas[] = $temp;
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
		exit();
	}
	$html = "<div style='float:left;width:100%;height:460px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
	$index = array('num'=>'编号',
			'name'=>'游戏昵称',
			'uid'=>'UID',
			'level'=>'等级',
			'ubLevel' => '大本等级',
			// 'country'=>'阵营',
			'lang' => '所用语言',
			'gold' => '当前金币',
			'gmFlag' => 'GM标记',
			'pf' => '平台',
			'armyNum' => '士兵数',
			'power' => '战斗力',
			'wood' => '木',
			'iron' => '铁',
			'food' => '粮',
			'silver' => '银',
// 			'x' => '世界X',
// 			'y' => '世界Y',
			'regTime' => '注册时间',
			// 'banTime' => '封号结束',
			'offLineTime' => '离线时间',
			'appVersion' => '游戏版本',
			);
	$gender = array('','男','女');
	$country = array('史塔克','兰尼斯特','拜拉席恩');
	$html .= "<tr class='listTr'>";
	foreach ($index as $key=>$value)
	{
		if(in_array($key, array('banTime','regTime','offLineTime')))
			$html .= "<th width=80px>" . $value . "</th>";
		elseif(in_array($key, array('name','uid')))
			$html .= "<th width=90px>" . $value . "</th>";
		else
			$html .= "<th width=40px>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($sqlDatas as $sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$user = '';
		foreach ($index as $key=>$title){
			$value = $sqlData[$key];
			switch ($key){
				case 'uid':
					$user = $value;
					$html .= "<td><a href=javascript:void(showTip('$user'))>" . $value . "</a></td>";
					break;
				case 'gender':
					$html .= "<td>" . $gender[$value] . "</td>";
					break;
				case 'country':
					$html .= "<td>" . ($value > 7999 ? $country[$value-8000] : '') . "</td>";
					break;
				case 'banTime':
				case 'offLineTime':
				case 'regTime':
					$html .= "<td>" . ($value > 0 ? date('Y-m-d H:i:s',$value/1000) : '') . "</td>";
					break;
				case 'onlineTime':
					$timeArr = array(array('秒','60'),array('分','60'),array('时','24'),array('天','365'),array('年','10'));
					$index = 0;
					$temp = '';
					while($value > 0 && $timeArr[$index]){
						$temp = $value%$timeArr[$index][1] . $timeArr[$index][0] . $temp;
						$value = intval($value/$timeArr[$index][1]);
						$index++;
					}
					$html .= "<td>" . $temp . "</td>";
					break;
				case 'gmFlag':
					$html .= "<td>" . ($value == 1 ? "<font color=red>是</font>" : '') . "</td>";
					break;
				case 'pf':
					$html .= "<td>" . $value . "</td>";
					break;
				default:
					$html .= "<td>" . $value . "</td>";
					break;
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit();
}
//获得所有item名
if($test){
	$dirName = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/game/service/item";
	$title = false;
	$dir = opendir($dirName);
	while(($file = readdir($dir))!=false){
		if ($file!="." && $file!="..") {
			echo $file."<br />";
		}
	}
	closedir($dir);
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>