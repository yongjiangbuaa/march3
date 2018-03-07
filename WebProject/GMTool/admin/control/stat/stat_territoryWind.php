<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());
global $servers;
$allServerFlag=false;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

$where = "date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') and";

$monthArr = monthList(strtotime($start),strtotime($end));
$sids = implode(',', $selectServerids);
$whereSql=" and server_id in ($sids) ";
if ($_REQUEST ['analyze'] == 'platform') {
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql = "select date,type,count(type) conType from $db_start l where $where category=14 $whereSql group by date,type ";
        if(isset($sql_sum)){
            $sql_sum = $sql_sum . " union " . $sql ;
        }else{
            $sql_sum = $sql;
        }
    }
    if (in_array($_COOKIE['u'],$privilegeArr)) {
        echo $sql_sum.PHP_EOL;
    }
    $result =query_infobright($sql_sum);
    $result = $result['ret']['data'];
    $title = array(
        'date' => '时间',
        'type' => '建筑类型',
        'conType' => '建造次数',
    );
    $namearr = array(0=>'联盟林场',1=>'联盟勘探所',2=>'联盟精炼厂',3=>'联盟磨坊');

    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";

    if($_REQUEST['new'] == 1){
        $datearr = $data =$typearr=array();
        foreach($result as $sqlData){
            $date = $sqlData['date'];
            $datearr[$date] = $date;
            $typearr[$sqlData['type']]= $sqlData['type'];
            $data[$date][$sqlData['type']] = $sqlData['conType'];
        }

        $html .= "<tr class='listTr'><th>建造次数</th>";
        foreach ($datearr as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";


        foreach ($typearr as $key => $value) {
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            $html .= "<td>$namearr[$value]</td>";
            foreach($datearr as $date){
                $html .= "<td><a href=\"javascript:getMember('{$date}, {$sqlData['type']}');\">{$data[$date][$value]}</a></td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table></div><br/>";

    }else{
        $html .= "<tr class='listTr'>";
        foreach ($title as $key => $value) {
            $html .= "<th>" . $value . "</th>";
        }
        $html .= "</tr>";
        foreach ($result as $num => $sqlData) {
            $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
            foreach ($title as $_key => $_value) {
                switch ($_key) {
                    case 'date':
                        $html .= "<td >" . $sqlData['date'] . "</td>";
                        break;
                    case 'type':
                        $html .= "<td ><a href=\"javascript:getMember('" .$sqlData['date'].",". $sqlData['type'] . "');\">{$namearr[$sqlData['type']]}</a></td>";
                        break;
                    case 'conType':
                        $html .= "<td >" . $sqlData['conType'] . "</td>";
                        break;
                }
            }
            $html .= "</tr>";
        }
        $html .= "</table></div><br/>";
        if ($pager['pager'])
            $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    }

    echo $html;
    exit();
}
if(isset($_REQUEST['string'])){
    $string =explode(",",$_REQUEST['string']);
    $i = date('Ym',strtotime($string[0]));
    $db_start = 'coklog_function.function_log_' . $i;
    $sql = "select var_data1 allianceId from $db_start l where date=str_to_date('$string[0]','%Y-%m-%d') and type=$string[1] and category=14 ; ";
    $result =query_infobright($sql);
    $result = $result['ret']['data'];
    $title = array(
        'index' => '编号',
        'allianceId' => '联盟ID',
    );
    $index =1;
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach ($result as $num => $sqlData) {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td >" . $index . "</td>";
        $html .= "<td >" . $sqlData['allianceId'] . "</td>";
        $html .= "</tr>";
        $index++;
    }
    $html .= "</table></div><br/>";
    if ($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>