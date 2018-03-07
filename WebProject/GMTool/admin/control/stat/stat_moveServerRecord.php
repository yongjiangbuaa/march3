<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());

if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?strtotime($_REQUEST['start'])*1000:strtotime($start_time)*1000;
    $end = $_REQUEST['end']?strtotime($_REQUEST['end'])*1000:strtotime($end_time)*1000;

    $total_time = "time>=$start and time<=$end ";
    $sql = "select src,dst,count(1) `sum` from move_server_record where ".$total_time." group by src, dst; ";
    $result = $page->globalExecute($sql, 3);
    $result = $result['ret']['data'];
    $title = array(
        '迁出服数据',
        '迁入服数据',
        '人数',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($result as $sqlData){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            foreach($sqlData as $_key=>$_value){
                $html .= "<td>" .$_value . "</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>