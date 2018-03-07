<?php
!defined('IN_ADMIN') && exit('Access Denied');
$sid = $_COOKIE['Gserver2'];
if ($_REQUEST['page']) {
    $showpage = $_REQUEST ['page'];
    if($_REQUEST['alliancename']){
        $alliancename = $_REQUEST['alliancename'];
        $sql="select uid from alliance where alliancename= '{$alliancename}'";
        $result=$page->execute($sql,3);
        $allianceId=$result['ret']['data'][0]['uid'];
    }
    if($_REQUEST['allianceId']){
        $allianceId=$_REQUEST['allianceId'];
    }
    if($_REQUEST['start']){
        $start=$_REQUEST['start'];
    }elseif($_REQUEST['start']==0){
        $html .="<h3>全部聊天记录</h3>";
    }
    if($_REQUEST['end']){
        $end=$_REQUEST['end'];
    }
    if($sid != null){
        $sid_num = substr($sid,1);
        if($sid_num != null){
            $inner_ip = get_server_ip_inner($sid_num);
        }
    }
    $rediskey = 'CHAT_HIS_'.$sid_num.'_ALLIANCE_'.$allianceId;
    $page_limit = 100;
    if($inner_ip != null){
        $client = new Redis();
        $client->connect($inner_ip);
        if(($_REQUEST['start']&&$_REQUEST['end'])||$start==0){
//            $count=$end-$start;
            $redis= $client->lRange($rediskey, $start-1, $end-1);
        }
        if(!isset($start)||!isset($end)){
            $count=$client->llen($rediskey );
            $pager = page($count, $showpage, $page_limit);
            $index = $pager['offset'];
            $index_end=$index+$page_limit;
            $redis= $client->lRange($rediskey, $index, $index_end);
        }
//        $pager = page($count, $showpage, $page_limit);
//        $index = $pager['offset'];
//        $index_end=$index+$page_limit;
//        $redis= $client->lRange($rediskey, $index, $index_end);
    }
        if ($redis == null) {
            exit( '<h3>无数据！</h3>');
        }
    $_index = array('编号','发送者姓名','发送者id','信息内容','语言','时间','msgarr');
        $html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
        $html .= "<tr class='listTr'>";
        foreach ($_index as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach ($redis as $no => $redisData) {
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $_redisData = json_decode($redisData, true);
            $html .= "<td>" . $_redisData['seqId'] . "</td>";
            $html .= "<td>" . $_redisData['senderName'] . "</td>";
            $html .= "<td>" . $_redisData['senderUid'] . "</td>";
            $html .= "<td>" . $_redisData['msg'] . "</td>";
            $html .= "<td>" . $_redisData['lang'] . "</td>";
            $html .= "<td>" . date('Y-m-d H:i:s',$_redisData['sendLocalTime']) . "</td>";
            if(isset($_redisData['msgarr'])){
                $html .= "<td>" . json_encode($_redisData['msgarr']) . "</td>";
            }else{
                $html .= "<td>&nbsp;</td>";
            }

            $html .= "</tr>";
        }
    echo '联盟简称：'.$_redisData['asn'];
    echo '联盟ID：'.$_redisData['allianceId'];
        $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

