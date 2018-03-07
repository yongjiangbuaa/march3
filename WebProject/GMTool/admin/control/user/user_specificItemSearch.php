<?php
!defined('IN_ADMIN') && exit('Access Denied');
$alertHead="";
$showData=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];



if($_REQUEST['analyze']=='user'){
	$alertHead='';
    $start = $_REQUEST['start_time'];
    $end = $_REQUEST['end_time'];
	$wheresql = ' where 1=1 ';
    if($_REQUEST['uid']){
        $curruid=$_REQUEST['uid'];
	    $wheresql.= " and ownerId=$curruid";
    }
    if($_REQUEST['itemid']) {
        $curritemid = $_REQUEST['itemid'];
	    $wheresql.= " and itemId=$curritemid";
    }else{
        $alertHead='请输入物品id';
	    $exit = 1;
    }
	if(!$exit){
	    $data=array();
		$sql = "select itemId,sum(count) cnt from user_item $wheresql group by itemId ;";
//		echo $sql;
		foreach($selectServer as $sid=>$item){
	        $res=$page->executeServer($sid,$sql,3);
	        if(!empty($res['ret']['data'])){
	            foreach($res['ret']['data'] as $dval){
		            $itemid = $dval['itemId'];
	                $data[$itemid] += $dval['cnt'];
	            }
	        }
	    }


	    if ($_REQUEST['event']=='view'){
	        if ($data){
	            $showData=true;
	        }else {
	            $alertHead='没有查到数据';
	        }
	    }
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>