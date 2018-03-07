<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*3);
$end = date('Y-m-d',time());
$battleType = array(
    1=>array('1减少孵化时间','点击次数|点击人数(去重)'),
    2=>array('2巨龙战斗','胜败(0胜利1负2平)|出战次数|人数'),  //0胜利1负2平
    3=>array('3巨龙喂养活力值','档位|点击次数|点击人数(去重)'),
    4=>array('4喂龙元素值','次数|人数|元素个数'),
    5=>array('5喂龙经验','次数|人数|道具个数'),
    6=>array('6新版喂食','次数|人数|食数量|金币数'),

    7=>array('6发起配对请求','次数|人数'),
    8=>array('7同意配对请求','次数|人数'),
    9=>array('8获取配对奖励','次数|人数'),

    104=>array('4喂龙元素详细','龙ID|龙等级|龙类型|消耗道具数量|属性ID|消耗道具ID'),
    105=>array('5喂龙经验详细','龙ID|元素等级|元素经验|消耗道具数量|龙经验|消耗道具ID'),
);
//巨龙系统:1.减少孵化次数; 2.巨龙战斗; 3.喂龙活力值; 4.喂龙元素属性; 5.喂龙经验 6 新版喂食

//巨龙系统增加打点数据】打点数据包括：1. 每条巨龙的孵化情况（包括孵化时间、减少孵化次数，孵化之后没有领取的，蓝龙没有喂满的）2.巨龙出战次数（分出胜利、失败次数、只带一个兵和一条龙战斗的次数）3.巨龙喂养活力次数统计；4.出征的时候勾选巨龙然后弹出巨龙活力不足的次数，5. 巨龙每个科技的研究次数；6统计巨龙等级=领主等级的玩家数量（领主等级＞=7）
foreach ($battleType as $key=>$value){
    $options .= "<option value='$key'>$value[0]</option>";
}
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$lang = loadLanguage();
if ($_REQUEST ['analyze'] == 'platform') {
    $start = $_REQUEST['start']?$_REQUEST['start']:$start;
    $end = $_REQUEST['end']?$_REQUEST['end']:$end;
    $monthArr = monthList(strtotime($start),strtotime($end));
    $useruid = $_REQUEST['useruid'];

    $type = (int)$_REQUEST['battleType'];
    $wheresql =" l.type='$type'";
    $wheresql .= " and l.date>=str_to_date('$start','%Y-%m-%d') and l.date<=str_to_date('$end','%Y-%m-%d') ";
    $wheresql2 = " l.date>=str_to_date('$start','%Y-%m-%d') and l.date<=str_to_date('$end','%Y-%m-%d') ";//配对用
    $server_select = implode(',',$selectServerids);
    $wheresql .= " and server_id in($server_select)";
    $wheresql2 .= " and server_id in($server_select)";//配对用
    if($type == 4|| $type == 5) {
        $wheresql .= " and userid='{$useruid}'";
    }

    if($type==1 || $type==3 ){
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date,int_data3,count(1) cnt,count(DISTINCT userid) as duser from $db_start l where category=25 and $wheresql  group by date,int_data3 ";
            if(isset($sql_sum)){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
    }else if($type == 2){
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date,int_data3,count(1) cnt,count(DISTINCT userid) as duser from $db_start l where category=25 and $wheresql  group by date ,int_data3";
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
    }else if($type == 4 || $type==5){
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date,count(1) as cnt,sum(int_data4) as num,count(DISTINCT userid) as duser from $db_start l where category=25 and $wheresql  group by date ";
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
        if(isset($useruid)){
            unset($sql_sum);
            foreach ($monthArr as $i) {
                $db_start = 'coklog_function.function_log_' . $i;
                $sql = "select from_unixtime(timeStamp/1000) as date,int_data1,int_data2,int_data3,int_data4,var_data2 from $db_start l where category=25 and $wheresql ";
                if($sql_sum){
                    $sql_sum = $sql_sum . " union " . $sql ;
                }else{
                    $sql_sum = $sql;
                }
            }
        }
    }else if($type == 6){
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date,count(1) as cnt,sum(int_data4) as num,sum(int_data5) as goldcost,count(DISTINCT userid) as duser from $db_start l where category=25 and $wheresql  group by date ";
            if($sql_sum){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
    }
//    new int[]{userDragon.getDragonId(), userDragon.getLevel(), userDragon.getType(),foodByEnergy,goldCost},

    //配对统计
    elseif($type == 7 || $type == 8 || $type == 9){
        $sqltype= $type-6;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql = "select date,count(1) cnt,count(DISTINCT userid) as duser from $db_start l where category=36 and type=$sqltype and $wheresql2  group by date ";
            if(isset($sql_sum)){
                $sql_sum = $sql_sum . " union " . $sql ;
            }else{
                $sql_sum = $sql;
            }
        }
    }

    $sql_sum .=" order by date desc;";
    if(in_array($_COOKIE['u'],$privilegeArr)) {
        echo $sql_sum . PHP_EOL;
    }
    $result =query_infobright($sql_sum);
    $result = $result['ret']['data'];

    $coun = array();
    foreach($result as $users){
        switch ($type){
            case 1:
            case 9:
            case 7:
            case 8:
                $coun[$users['date']]['cnt']=$users['cnt'];
                $coun[$users['date']]['duser']=$users['duser'];
                break;
            case 2:
            case 3:
                $coun[$users['date']]['win']=$users['int_data3'];
                $coun[$users['date']]['cnt']=$users['cnt'];
                $coun[$users['date']]['duser']=$users['duser'];
                break;
            case 5:
            case 4:
                $coun[$users['date']]['cnt']=$users['cnt'];
                $coun[$users['date']]['duser']=$users['duser'];
                $coun[$users['date']]['num']=$users['num'];
                if(isset($useruid)){
                    $type += 100;
                    $coun = $result;
                }
                break;
            case 6:
                $coun[$users['date']]['cnt']=$users['cnt'];
                $coun[$users['date']]['duser']=$users['duser'];
                $coun[$users['date']]['num']=intval($users['num']/1000000);
                $coun[$users['date']]['goldcost']=$users['goldcost'];
        }
    }
//    $html = json_encode($coun);
    $headinfo = $battleType[$type][0];//标题
    $thnameArr = explode('|',$battleType[$type][1]);//每列名字
//    $html .= print_r($thnameArr);
    $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";

    $html .= "<tr class='listTr'><th>日期</th>";
    foreach($thnameArr as $value){
        $html .= "<th style='text-align:center'>$value</th>";
    }
    $html .= "</tr>";
    foreach($coun as $date=>$item){

        $html .= "<tr class='listTr' onMouseOver=this.style.background='#E6E6FA' onMouseOut=this.style.background='#fff'>";
        switch($type){
            case 1:
            case 9:
            case 7:
            case 8:
            $html .= "<td>$date </td><td>{$item['cnt']}</td><td>{$item['duser']}</td></tr>";
            break;
            case 2:
            case 3:
                $html .= "<td>$date </td><td>{$item['win']}</td><td>{$item['cnt']}</td><td>{$item['duser']}</td></tr>";
                break;
            case 4:
            case 5:
            $html .= "<td>$date </td><td>{$item['cnt']}</td><td>{$item['duser']}</td><td>{$item['num']}</td></tr>";
                break;
            case 6:
            $html .= "<td>$date </td><td>{$item['cnt']}</td><td>{$item['duser']}</td><td>{$item['num']} M</td><td>{$item['goldcost']}</td></tr>";
                break;
            case 104:
            case 105:
                $html .= "<td>{$item['date']}</td><td>{$item['int_data1']}</td><td>{$item['int_data2']}</td><td>{$item['int_data3']}</td><td>{$item['int_data4']}</td><td>{$item['var_data2']}</td></tr>";
                break;
        }
    }
    $html .= "</table></div><br/>";
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>