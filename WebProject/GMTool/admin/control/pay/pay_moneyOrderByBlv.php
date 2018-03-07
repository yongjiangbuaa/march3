<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
    $start = date("Y-m-d",time()-86400*4);
if(!$_REQUEST['end'])
    $end = date("Y-m-d",time());
$alertHead="";
$showData=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$sql="select blv,sum(spend) spend,date from PayOrderByBlv where ";
$dbCol=array(
    'blv',
    'date',
    'spend'
);
$paylv=array(
    'all',
    '0-5',
    '5-50',
    '50-200',
    '200-500',
    '501-1000',
    '1001-5000',
    '5001-10000',
    '10001+'
);

if($_REQUEST['analyze']=='user'){
    $start = $_REQUEST['start_time'];
    $end = $_REQUEST['end_time'];
    $startYmd=date('Y-m-d',strtotime($start));
    $endYmd=date('Y-m-d',strtotime($end));
    $currpaylv=$_REQUEST['paylv'];
    $sids = implode(',', $selectServerids);
    $whereSql ="date >= '$start' and date <= '$end' and serverId in ($sids) ";
    if ($_REQUEST['country'] && $_REQUEST['country']!='ALL') {
        $currCountry = trim($_REQUEST['country']);
        $whereSql .= "and country='$currCountry' ";
    }
    if ($_REQUEST['pf'] && $_REQUEST['pf']!='ALL') {
        $currPf = trim($_REQUEST['pf']);
        $whereSql .= "and platform='$currPf' ";
    }
    if($currpaylv>0){
        $whereSql .= "and paylv = $currpaylv ";
    }
    $sql .= $whereSql." group by date,blv order by blv desc,date asc;";
    
//    $link = mysqli_connect('localhost','root','password','hiveQueryResult',3306);
    $link = get_ad_connection();

    // 	echo $sql;
    $dateArray=array();
    $blvArray=array();
    $data=array();
    $res = mysqli_query($link,$sql);
    while ($row = mysqli_fetch_assoc($res)){
    		if(!in_array($row['blv'], $blvArray)){
    			$blvArray[]=$row['blv'];
    		}
    		if (!isset($dateTemp[date('Ymd',strtotime($row['date']))])){
	    		$dateTemp[date('Ymd',strtotime($row['date']))]=$row['date'];
    		}
       $data[$row['blv']][$row['date']]=$row['spend'];
    }
    rsort($blvArray);
    krsort($dateTemp);
    $dateArray=array_values($dateTemp);
    mysql_close($link);
    if ($_REQUEST['event']=='view'){
        if ($data){
            $showData=true;
        }else {
            $alertHead='没有查到相关数据';
        }
    }
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>