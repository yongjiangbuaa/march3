<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d', time() - 86400 * 7);
$end = date('Y-m-d', time() + 86400);
global $servers;
$allServerFlag = false;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

$options2 = "<option value='0'>统计</option><option value='1'>每人详细战斗</option>";

if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start'] ? substr($_REQUEST['start'], 0, 10) : substr($start, 0, 10);
    $end = $_REQUEST['end'] ? substr($_REQUEST['end'], 0, 10) : substr($end, 0, 10);
    $Wheretime = "and date>=str_to_date('$start','%Y-%m-%d') and date<=str_to_date('$end','%Y-%m-%d') ";

    $monthArr = monthList(strtotime($start),strtotime($end));
    $searchtype = $_REQUEST['type'];

    $sids = implode(',', $selectServerids);
    $whereSql = " and server_id in ($sids) ";
    if ($_REQUEST['useruid']) {
        $strUid = trim($_REQUEST['useruid'], ',');
        $where = " and (l.userid in ('' ";
        if (strpos($strUid, ',')) {
            $UidArr = explode(',', $strUid);
            foreach ($UidArr as $Uid) {
                $where .= ",'$Uid'";
            }
        } else {
            $where .= ",'$strUid'";
        }
        $where .= ') ';
        $where .= " or l.var_data1 in ('' ";
        if (strpos($strUid, ',')) {
            $UidArr = explode(',', $strUid);
            foreach ($UidArr as $Uid) {
                $where .= ",'$Uid'";
            }
        } else {
            $where .= ",'$strUid'";
        }
        $where .= ')) ';
        $whereInfo .= $where;
    }elseif ($_REQUEST['allName']) {
        $strName = trim($_REQUEST['allName'], ',');
        $where = " and (l.var_data1 in ('' ";
        if (strpos($strName, ',')) {
            $nameArr = explode(',', $strName);
            foreach ($nameArr as $name) {
                $account_list = cobar_getValidAccountList('name', $name);
                $useruid = $account_list[0]['gameUid'];
                $where .= ",'$useruid'";
            }
        } else {
            $account_list = cobar_getValidAccountList('name', $strName);
            $useruid = $account_list[0]['gameUid'];
            $where .= ",'$useruid'";
        }
        $where .= ') ';
        $where .= "or userid in ('' ";
        if (strpos($strName, ',')) {
            $nameArr = explode(',', $strName);
            foreach ($nameArr as $name) {
                $account_list = cobar_getValidAccountList('name', $name);
                $useruid = $account_list[0]['gameUid'];
                $where .= ",'$name'";
            }
        } else {
            $account_list = cobar_getValidAccountList('name', $strName);
            $useruid = $account_list[0]['gameUid'];
            $where .= ",'$useruid'";
        }
        $where .= ')) ';
        $whereInfo = $where;
    }


    if($searchtype == 0) {//默认查询总的统计pvp

        foreach ($monthArr as $i) {
            $db_start = 'coklog_function.function_log_' . $i;
            $sql_pass = "select date,IFNULL(var_data2,0) as buildLv,count(1) times ,count(distinct userid) users  from $db_start l where l.category=9  $wheretime  $whereInfo $whereSql group by date,var_data2 ";
            if (isset($sql_sum)) {
                $sql_sum = $sql_sum . " union " . $sql_pass;
            } else {
                $sql_sum = $sql_pass;
            }
        }
        $sql_sum .= " order by date desc,var_data2 desc ";
//        $sql = "select date,coutn(1) times ,count(distinct userid) users from $db_start l where l.category=9  $wheretime  $whereInfo $whereSql group by date order by date desc";

        $result_pass = query_infobright($sql_sum);
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            $html .= $sql_sum;
        }
        $alldata = $datearr = $sum = $LvArr = array();
        foreach ($result_pass['ret']['data'] as $currow) {
            $date = $currow['date'];
            $datearr[$date] = $date;
            $LvArr[$currow['buildLv']] = $currow['buildLv'];

            $alldata[$date][$currow['buildLv']]['times'] += $currow['times'];
            $alldata[$date][$currow['buildLv']]['users'] += $currow['users'];

            $sum[$currow['buildLv']]['times'] += $currow['times'];//总和
            $sum[$currow['buildLv']]['users'] += $currow['users'];
        }

        $html .= "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center;float:left;margin-right:15px;'><thead><tr><th colspan='3'>合计</th>";
        foreach ($datearr as $date) {
            $html .= "<th colspan='2'>$date</th>";
        }
        $html .= "</tr></thead>";
        //副标题
        $html .= "<tr><th>大本等级</th><th>人数</th><th>次数</th>";
        foreach ($datearr as $date) {
            $html .= "<th>人数</th><th>次数</th>";
        }
        $html .= "</tr><tbody id='adDataTable'>";

        foreach ($LvArr as $key) {
            $htmltmp = '';
            $htmltmp = "<tr onMouseOver=\"this.style.background='lightskyblue'\"
			onMouseOut=\"this.style.background='initial';\"><td><font color='#0088CC'>{$key}</font></td><td>{$sum[$key]['users']}</td><td>{$sum[$key]['times']}</td>";
            foreach ($datearr as $date) {
                $htmltmp .= "<td>{$alldata[$date][$key]['users']}</td><td>{$alldata[$date][$key]['times']}</td>";
            }
            $htmltmp .= "</tr>";
            $html .= $htmltmp;
        }
        $html .= '</tbody></table></div>';

        echo $html;
        exit();

    }



        $limit = 100;
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "select count(1) sum from $db_start l where l.category=9  $wheretime  $whereInfo $whereSql";
        if (isset($sql_sum)) {
            $sql_sum = $sql_sum . " union " . $sql_pass;
        } else {
            $sql_sum = $sql_pass;
        }
    }
    $result = query_infobright($sql_sum);
    $count = $result['ret']['data'][0]['sum'];
    if ($count < 1) {
        exit($sql . '<h3>无数据！</h3>');
    }
    $pager = page($count, $_REQUEST['page'], $limit);
    $index = $pager['offset'];

    $sql_sum = '';
    foreach ($monthArr as $i) {
        $db_start = 'coklog_function.function_log_' . $i;
        $sql_pass = "select * from $db_start l where l.category=9  $wheretime  $whereInfo $whereSql limit $index,$limit";
        if ($sql_sum) {
            $sql_sum = $sql_sum . " union " . $sql_pass;
        } else {
            $sql_sum = $sql_pass;
        }
    }

    $result_stats = query_infobright($sql_sum);
    $result = $result_stats['ret']['data'];
    $html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array(
        'date' => '时间',
        'timeStamp' => '时间戳',
        'userid' => '攻击方id',
        'var_data1' => '被攻击方id',
        'type' => '战斗结果',
        'int_data1' => '木材',
        'int_data2' => '水晶',
        'int_data3' => '铁矿',
        'int_data4' => '粮食',
        'int_data5' => '掠夺资源5（暂无）',
        'int_data6' => '掠夺资源6（暂无）',
    );
    $html .= "<tr class='listTr'>";
    foreach ($index as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $i = 1;
    $tempBattleId = 0;
    foreach ($result as $no => $sqlData) {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($index as $key => $title) {
            $value = $sqlData[$key];
            switch ($key) {
                case 'timeStamp':
                    $html .= "<td>" . date('Y-m-d H:i:s', $value / 1000) . "</td>";
                    break;
                case 'type':
                    $html .= "<td>" . ($value == 0 ? '胜利' : '失败') . "</td>";
                    break;
                default:
                    $html .= "<td>" . $value . "</td>";
            }

        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if ($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>