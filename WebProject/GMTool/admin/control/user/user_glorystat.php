<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d', time() - 86400 * 2);
$end = date('Y-m-d');
global $servers;
$allServerFlag = false;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

$statType_title = array('获得', '消耗','获取等级分布');
foreach ($statType_title as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}

$costArr = array('100021'=>'商店','100022'=>'科技');
$getArr = array(
    '100011'=>'杀兵',
    '100012'=>'攻击王座',
    '100013'=>'攻击超级要塞',
    '100014'=>'攻击资源点',
    '100015'=>'攻击资源田',
    '100021'=>'打怪',
    '100031'=>'打大魔王',
    '110000'=>'道具',
);
foreach ($getArr as $key => $value) {
    $options1 .= "<option value='$key'>$value</option>";
}

if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
    $end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);

    $monthArr = monthList(strtotime($start),strtotime($end));
    $sids = implode(',', $selectServerids);
    if (empty($_REQUEST['selectServer'])) {
        $whereSql = "";
    } else {
        $whereSql = " and server_id in ($sids) ";
    }
    if ($_REQUEST['userId']) {
        $user_id = $_REQUEST['userId'];
        $whereSql .= " and userid='$user_id' ";
    }

    $html = '';
    $wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";
    $statType = $_REQUEST['statType'];
    $statgetType = $_REQUEST['statGetType'];

    if(isset($user_id)){
        $type = $statType;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            if($type == 0){
                $sql_pass = "select timeStamp,int_data1,var_data1 from $db_start where category=40 and type=$type $wheretime $whereSql and var_data1 >0 ";
            }elseif($type == 1){
                $sql_pass = "select timeStamp,int_data1,var_data1,var_data2,var_data3 from $db_start where category=40 and type=$type $wheretime $whereSql and var_data1 >0 ";
            }
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by timeStamp desc ";
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $result_pass = query_infobright($sql_sum);


        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        if($type == 0){
            $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><td>时间</td><td>类型</td><td>变化值</td></tr>";
        }elseif($type ==1){
            $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><td>时间</td><td>类型</td><td>原先值</td><td>变化值</td><td>剩余值</td></tr>";
        }

        foreach ($result_pass['ret']['data'] as $currow) {
            $date = date('Ymd H:i:s',$currow['timeStamp']/1000);
            if($type == 0){
                $html .= "<tr><td>{$date}</td><td>{$getArr[$currow['int_data1']]}</td><td>{$currow['var_data1']}</td></tr>";
            }elseif($type == 1){
                $html .= "<tr><td>{$date}</td><td>{$costArr[$currow['int_data1']]}</td><td>{$currow['var_data2']}</td><td>{$currow['var_data1']}</td><td>{$currow['var_data3']}</td></tr>";
            }
        }

        $html .= '</table></div>';

    }
    else if ($statType == 0 ) {
        $type = $statType;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date, int_data1,count(DISTINCT userid) users ,count(1) times,sum(var_data1) cnt from $db_start where category=40 and type=$type $wheretime $whereSql and var_data1 >0 group by date ,int_data1 ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= "order by date desc,int_data1 desc";
        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $indexArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $index = $currow['int_data1'];
            $indexArr[$index] = $index;

            $alldata[$date][$index]['times'] += $currow['times'];
            $alldata[$date][$index]['users'] += $currow['users'];
            $alldata[$date][$index]['cnt'] += $currow['cnt'];

            $sum[$index]['times'] += $currow['times'];//总和
            $sum[$index]['users'] += $currow['users'];
            $sum[$index]['cnt'] += $currow['cnt'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>---</th><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>类型</th><th>人数</th><th>次数</th><th>荣誉值</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th><th>荣誉值</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($indexArr as $key) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$getArr[$key]}</font></td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td><td>{$sum[$key]['cnt']}</td>";
            foreach ($datearr as $date) {
                $a =  $alldata[$date][$key]['users']?$alldata[$date][$key]['users']:0;
                $b =  $alldata[$date][$key]['times']?$alldata[$date][$key]['times']:0;
                $c =  $alldata[$date][$key]['cnt']?$alldata[$date][$key]['cnt']:0;
                $htmltmp .= "<td>{$a}</td><td>{$b}</td><td>{$c}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }else if ($statType == 1) {
        $type = $statType;
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;

            $sql_pass = "select date, int_data1 as glorycosttype ,int_data2 as buildlv  ,count(DISTINCT userid) users ,sum(var_data1) cnt from $db_start where category=40 and type=$type $wheretime $whereSql and var_data1 >0 group by date ,int_data1 ,buildlv ";

            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= " order by date desc,buildlv desc ";
        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $buildLvArr = $alldata = $datearr = $sum = $indexArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $index = $currow['glorycosttype'];
            $buildlv = $currow['buildlv'];
            $indexArr[$buildlv] = $buildlv;

            $alldata[$date][$buildlv][$index]['users'] += $currow['users'];
            $alldata[$date][$buildlv][$index]['cnt'] += $currow['cnt'];

            $alldata[$date]['sum'][$index]['users'] += $currow['users'];
            $alldata[$date]['sum'][$index]['cnt'] += $currow['cnt'];

            $sum[$buildlv][$index]['users'] += $currow['users'];
            $sum[$buildlv][$index]['cnt'] += $currow['cnt'];

            $sum['sum'][$index]['users'] += $currow['users'];
            $sum['sum'][$index]['cnt'] += $currow['cnt'];
        }

        $html .= "<div style='float:left;width:95%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>---</th><th colspan='6'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='6'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>等级</th><th>商店人均消耗</th><th>商店消耗总量</th><th>人数</th><th>科技人均消耗</th><th>科技消耗总量</th><th>人数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>商店人均消耗</th><th>商店消耗总量</th><th>人数</th><th>科技人均消耗</th><th>科技消耗总量</th><th>人数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        rsort($indexArr);
        array_unshift($indexArr,'sum');//

        foreach ($indexArr as $key) {
            $htmltmp = '';
            $shop_total = $sum[$key][100021]['cnt'];
            $shop_users = $sum[$key][100021]['users'];
            $shop_average = intval($shop_total/$shop_users);

            $science_total = $sum[$key][100022]['cnt'];
            $science_users = $sum[$key][100022]['users'];
            $science_average = intval($science_total/$science_users);

            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td>
			<td>{$shop_average}</td><td>{$shop_total}</td><td>{$shop_users}</td><td>{$science_average}</td><td>{$science_total}</td><td>{$science_users}</td>";
            foreach ($datearr as $date) {
                $a =  $alldata[$date][$key][100021]['users']?$alldata[$date][$key][100021]['users']:0;
                $c =  $alldata[$date][$key][100021]['cnt']?$alldata[$date][$key][100021]['cnt']:0;
                $b = intval($c/$a);

                $a1 =  $alldata[$date][$key][100022]['users']?$alldata[$date][$key][100022]['users']:0;
                $c1 =  $alldata[$date][$key][100022]['cnt']?$alldata[$date][$key][100022]['cnt']:0;
                $b1 = intval($c1/$a1);

                $htmltmp .= "<td>{$b}</td><td>{$c}</td><td>{$a}</td><td>{$b1}</td><td>{$c1}</td><td>{$a1}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }else if ($statType == 2) {
        $type = 0;//获取
        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;

            $sql_pass = "select date, int_data1 as gloryaddtype ,int_data2 as buildlv  ,count(DISTINCT userid) users ,sum(var_data1) cnt from $db_start where category=40 and type=$type $wheretime $whereSql and var_data1 >0 and int_data1 = '{$statgetType}' group by date ,int_data1 ,buildlv ";

            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union all " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= " order by date desc,int_data1 desc,buildlv desc ";
        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $buildLvArr = $alldata = $datearr = $sum = $indexArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;

            $index = $currow['gloryaddtype'];//只会是一个
            $buildlv = $currow['buildlv'];
            $indexArr[] = $buildlv;

            $alldata[$date][$buildlv][$index]['users'] += $currow['users'];
            $alldata[$date][$buildlv][$index]['cnt'] += $currow['cnt'];

            $alldata[$date]['sum'][$index]['users'] += $currow['users'];
            $alldata[$date]['sum'][$index]['cnt'] += $currow['cnt'];

            $sum[$buildlv][$index]['users'] += $currow['users'];
            $sum[$buildlv][$index]['cnt'] += $currow['cnt'];

            $sum['sum'][$index]['users'] += $currow['users'];
            $sum['sum'][$index]['cnt'] += $currow['cnt'];
        }

//        $html .= print_r($alldata,true);
        $html .= "<div style='float:left;width:95%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th>{$getArr[$index]}</th><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='3'>$date</th>";
        }
        $html .= "</tr></thead>";

        //副标题
        $html .= "<tr><th>等级</th><th>人均消耗</th><th>消耗总量</th><th>人数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人均消耗</th><th>消耗总量</th><th>人数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";
        rsort($indexArr);
        array_unshift($indexArr,'sum');//
        foreach ($indexArr as $key) {
            $htmltmp = '';
            $shop_total = $sum[$key][$index]['cnt'];
            $shop_users = $sum[$key][$index]['users'];
            $shop_average = intval($shop_total/$shop_users);

            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td>
			<td>{$shop_average}</td><td>{$shop_total}</td><td>{$shop_users}</td>";
            foreach ($datearr as $date) {
                $a =  $alldata[$date][$key][$index]['users']?$alldata[$date][$key][$index]['users']:0;
                $c =  $alldata[$date][$key][$index]['cnt']?$alldata[$date][$key][$index]['cnt']:0;
                $b = intval($c/$a);
                $htmltmp .= "<td>{$b}</td><td>{$c}</td><td>{$a}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';
    }

    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>