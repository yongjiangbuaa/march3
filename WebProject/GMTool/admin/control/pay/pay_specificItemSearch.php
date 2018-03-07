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
    $start = $_REQUEST['start_time'];
    $end = $_REQUEST['end_time'];
    if(!$_REQUEST['numb']){
        $currnum=20;
    }else{
        $currnum=$_REQUEST['numb'];
    }
    $curritemid=$_REQUEST['itemid'];
    $data=array();
    if(count($selectServer)!=1){
        $sql="select itemid,uid,name,country,num,paidgold,regtime,lastonlinetime,regpf,currpf from SpecificItem where itemid='$curritemid' order by num desc limit $currnum";

        $data = query_ad_db($sql);
//        $link = mysqli_connect('localhost','root','password','hiveQueryResult',3306);
//        $res = mysqli_query($link,$sql);
//        while ($row = mysqli_fetch_assoc($res)){
//            $data[]=$row;
//        }
//        mysql_close($link);
    }else{
        $sql="select a.itemid,a.ownerid,b.name,c.country,a.`count`,d.spend,from_unixtime(b.regTime/1000),from_unixtime(b.lastonlinetime/1000),c.pf,b.pf as cp from
(select ownerid,itemid,`count` from user_item where itemid='$curritemid' order by `count` desc limit $currnum) a
join
(select DISTINCT name,regTime,uid,pf,lastonlinetime from userprofile where from_unixtime(bantime/1000)<now() ) b
on a.ownerid=b.uid
join
(select distinct uid,pf,country from stat_reg)c
on a.ownerid=c.uid
left outer join
(select uid,sum(spend) as spend from paylog group by uid)d
on a.ownerid=d.uid
order by a.`count` desc;";
        $res=$page->executeServer("s".$selectServerids[0],$sql,3);
        if(!empty($res['ret']['data'])){
            foreach($res['ret']['data'] as $dval){
                $data[]=$dval;
            }
        }
    }


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