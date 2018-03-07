<?php
//竞技场晋级赛

!defined('IN_ADMIN') && exit('Access Denied');
$weekScore = 350;//王者积分下限
$rewardMailId = '11843';//押注胜利邮件id

$startDate = date('Y-m-d', time() - 86400 * 3);
$endDate = date('Y-m-d');
global $servers;
$allServerFlag = false;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

$statType_title = array(0=>'竞技场晋级', 1=>"跨服竞技场");
foreach ($statType_title as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}

if($_REQUEST['dotype'] == 'search'){
    $startDate = $_REQUEST['startDate'] ? substr($_REQUEST['startDate'], 0, 10) : substr($startDate, 0, 10);
    $endDate = $_REQUEST['endDate'] ? substr($_REQUEST['endDate'], 0, 10) : substr($endDate, 0, 10);
    $monthArr = monthList(strtotime($startDate),strtotime($endDate));
    $sids = implode(',', $selectServerids);
    if (empty($_REQUEST['selectServer'])) {
        $whereSql = "";
    } else {
        $whereSql = " and server_id in ($sids) ";
    }

    $wheretime = "and date>=str_to_date('$startDate','%Y-%m-%d') and date<=str_to_date('$endDate','%Y-%m-%d') ";

    if ($_REQUEST['user_id']){//查询单个用户
        $user_id = $_REQUEST['user_id'];
        $whereSql .= " and userid='$user_id' ";
    }

    $statType = $_REQUEST['statType'];
    if($statType == 0){//竞技场晋级
        $sql;
        $weekSql;//王者段位人数
        foreach ($monthArr as $i){
            $db_start = 'coklog_function.function_log_' . $i;
            $subSql = "select server_id sid, max(int_data1) maxScore, count(1) sCount, sum(if(int_data1>=$weekScore, 1, 0)) sSum,sum(if(int_data2>0, 1, 0)) s2Sum ,sum(int_data3) s3Sum, date, type from $db_start where category=64 $wheretime $whereSql group by sid, date,type";
            if (empty($sql)) {
                $sql = $subSql;
            }else{
                $sql = $sql . " union all ".$subSql;
            }
        }

        $sql .= " order by date desc";
        $result_pass = query_infobright($sql);
        $alldata = $datearr = $serverArr = $totalArr =  array();
        foreach ($result_pass['ret']['data'] as $currow){
            $sid = "s".$currow['sid'];
            $serverArr[$currow['sid']] = $currow['sid'];
            $date = $currow['date'];
            $datearr[$date] = $date;

            if($currow['type'] == 2){//挑战(战斗次数)
                $alldata[$sid][$date]['challenge'] = $currow['sCount'];
                $totalArr[$date]['challenge'] += $currow['sCount'];
            }else if($currow['type'] == 3){//鼓舞
                $alldata[$sid][$date]['inspire'] = $currow['sCount'];
                $totalArr[$date]['inspire'] += $currow['sCount'];
            }else if($currow['type'] == 4){//刷新
                $alldata[$sid][$date]['refresh'] = $currow['sCount'];
                $alldata[$sid][$date]['goldRefresh'] = $currow['s2Sum'];
                $totalArr[$date]['refresh'] += $currow['sCount'];
                $totalArr[$date]['goldRefresh'] += $currow['s2Sum'];
            }else if($currow['type'] == 5){//积分
                if(empty($user_id)){
                    $alldata[$sid][$date]['score'] = 0;
                }else{
                    $alldata[$sid][$date]['score'] = $currow['maxScore'];
                }
                $totalArr[$date]['score'] += $alldata[$sid][$date]['score'];
            }else if ($currow['type'] == 6) {
                $alldata[$sid][$date]['weekNum'] = $currow['sSum'];//王者段位
                $totalArr[$date]['weekNum'] += $currow['sSum'];
                if(empty($user_id)){
                    $alldata[$sid][$date]['victoryNum'] = 0;
                    $totalArr[$date]['victoryNum'] += 0;
                }else{
                    $alldata[$sid][$date]['victoryNum'] = $currow['s3Sum'];//胜利次数
                    $totalArr[$date]['victoryNum'] += $currow['s3Sum'];
                }
            }
        }

        $html = "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
        $html .= "<table class='listTable' style='text-align:center'><thead><th>日期</th><th colspan='7'>合计</th>";
        sort($serverArr);
        foreach($serverArr as $ser){
            $html .="<th colspan='7'>s$ser</th>";
            $th1 .= "<th>日积分</th><th>战斗次数</th><th>胜利次数</th><th>总刷新次数</th><th>金币刷新次数</th><th>鼓舞次数</th><th>王者人数</th>";
        }

        $html .= "</thead><thead><th></th><th>日积分</th><th>战斗次数</th><th>胜利次数</th><th>总刷新次数</th><th>金币刷新次数</th><th>鼓舞次数</th><th>王者人数</th>$th1</thead>";

        rsort($datearr);
        foreach ($datearr as $date) {
            $html .="<tbody><tr><td>$date</td><td>".$totalArr[$date]['score']."</td><td>".$totalArr[$date]['challenge']."</td><td>".$totalArr[$date]['victoryNum']."</td><td>".$totalArr[$date]['refresh']."</td><td>".$totalArr[$date]['goldRefresh']."</td><td>".$totalArr[$date]['inspire']."</td><td>".$totalArr[$date]['weekNum']."</td>";
            foreach($serverArr as $ser){
                $ser = "s".$ser;
                $html .= "<td>".$alldata[$ser][$date]['score']."</td><td>".$alldata[$ser][$date]['challenge']."</td><td>".$totalArr[$date]['victoryNum']."</td><td>".$alldata[$ser][$date]['refresh']."</td><td>".$totalArr[$date]['goldRefresh']."</td><td>".$alldata[$ser][$date]['inspire']."</td><td>".$alldata[$ser][$date]['weekNum']."</td>";
            }
            $html .="</tr></tbody>";
        }
        $html .= '</tbody></table></div>';
    }else{//跨服竞技场
        $sql;
        //如果以后金币档位的值有变化，修改下面这个数组的值即可
        $goldArr = array(50, 100, 200, 400, 600, 800, 1000);
        $colspanVal = 8 + count($goldArr);
        $goldChallengeSql = "";
        $goldChallengeTh = "";
        foreach ($goldArr as $gold) {
            $goldChallengeSql .= ", sum(if(int_data3 = $gold, 1, 0)) sum$gold";
            $goldChallengeTh .= "<th>付费$gold"."战斗次数</th>";
        }
        foreach ($monthArr as $i){
            $db_start = 'coklog_function.function_log_' . $i;
            $subSql = "select server_id sid, count(1) sCount, date, type,sum(if(int_data3 > 0,1,0)) sSum, sum(if(var_data2=$rewardMailId, 1, 0)) ssSum$goldChallengeSql, sum(int_data3) int_data3_sum from $db_start where category=68 $wheretime $whereSql group by sid, date,type";
            if (empty($sql)) {
                $sql = $subSql;
            }else{
                $sql = $sql . " union all ".$subSql;
            }
            $sql .= " order by date desc";
            $result_pass = query_infobright($sql);
            $alldata = $datearr = $serverArr = $totalArr =  array();
            foreach ($result_pass['ret']['data'] as $currow){
                $sid = "s".$currow['sid'];
                $serverArr[$currow['sid']] = $currow['sid'];
                $date = $currow['date'];
                $datearr[$date] = $date;

                if ($currow['type'] == 2) {//战斗次数
                    $alldata[$sid][$date]['challenge'] = $currow['sCount'];
                    $alldata[$sid][$date]['payChallenge'] = $currow['sSum'];//付费战斗次数
                    foreach ($goldArr as $gold) {
                        //付费XX战斗次数
                        $challengeVar = "pay".$gold."Challenge";
                        $challengeValueVar = "sum".$gold;
                        $alldata[$sid][$date][$challengeVar] = $currow[$challengeValueVar];
                        $alldata[$sid][$date]['payGolds'] += $currow[$challengeValueVar] * $gold;
                        //攻击消耗总金币数
                        $totalArr[$date][$challengeVar] += $currow[$challengeValueVar];
                    }
                    $totalArr[$date]['challenge'] += $currow['sCount'];
                    $totalArr[$date]['payChallenge'] += $currow['sSum'];
                    $totalArr[$date]['payGolds'] += $alldata[$sid][$date]['payGolds'];
                }else if($currow['type'] == 5){//押注中奖次数
                    $alldata[$sid][$date]['reward'] = $currow['ssSum'];
                    $alldata[$sid][$date]['winStakeCount'] = $currow['int_data3_sum'];  //押中的投注筹码数
                    $totalArr[$date]['reward'] += $currow['ssSum'];
                    $totalArr[$date]['winStakeCount'] += $currow['int_data3_sum'];
                }else if($currow['type'] == 6){//押注次数
                    $alldata[$sid][$date]['lottery'] = $currow['sCount'];
                    $alldata[$sid][$date]['stakeCount'] = $currow['int_data3_sum']; //投注筹码数
                    $totalArr[$date]['lottery'] += $currow['sCount'];
                    $totalArr[$date]['stakeCount'] += $currow['int_data3_sum'];
                }else if ($currow['type'] == 7) {//鼓舞
                    $alldata[$sid][$date]['inspire'] = $currow['sCount'];
                    $totalArr[$date]['inspire'] += $currow['sCount'];
                }
            }

            $html = "<div style='float:left;width:90%;text-align:center;overflow-x:auto;overflow-y:auto;'>";
            $html .= "<table class='listTable' style='text-align:center'><thead><th>日期</th><th colspan='$colspanVal'>合计</th>";
            sort($serverArr);
            foreach($serverArr as $ser){
                $html .="<th colspan='$colspanVal'>s$ser</th>";
                $th1 .= "<th>战斗次数</th><th>付费战斗次数</th>$goldChallengeTh<th>攻击消耗总金币数</th><th>押注次数</th><th>押注筹码</th><th>押中人数</th><th>押中筹码</th><th>鼓舞次数</th>";
            }

            $html .= "</thead><thead><th></th><th>战斗次数</th><th>付费战斗次数</th>$goldChallengeTh<th>攻击消耗总金币数</th><th>押注次数</th><th>押注筹码</th><th>押中人数</th><th>押中筹码</th><th>鼓舞次数</th>$th1</thead>";

            rsort($datearr);
            foreach ($datearr as $date) {
                //如果没有值，赋值为0
                if (empty($totalArr[$date]['challenge'])) {
                    $totalArr[$date]['challenge'] = 0;
                }
                if (empty($totalArr[$date]['payChallenge'])) {
                    $totalArr[$date]['payChallenge'] = 0;
                }
                $goldChTotalValueTh = "";
                foreach ($goldArr as $gold) {
                    $challengeVar = "pay".$gold."Challenge";
                    if (empty($totalArr[$date][$challengeVar])) {
                        $totalArr[$date][$challengeVar] = 0;
                    }
                    $goldChTotalValueTh .= "<td>".$totalArr[$date][$challengeVar]."</td>";
                }
                if (empty($totalArr[$date]['payGolds'])) {
                    $totalArr[$date]['payGolds'] = 0;
                }
                if (empty($totalArr[$date]['lottery'])) {
                    $totalArr[$date]['lottery'] = 0;
                }
                if (empty($totalArr[$date]['reward'])) {
                    $totalArr[$date]['reward'] = 0;
                }
                if (empty($totalArr[$date]['inspire'])) {
                    $totalArr[$date]['inspire'] = 0;
                }
                if (empty($totalArr[$date]['stakeCount'])) {
                    $totalArr[$date]['stakeCount'] = 0;
                }
                if (empty($totalArr[$date]['winStakeCount'])) {
                    $totalArr[$date]['winStakeCount'] = 0;
                }

                $html .="<tbody><tr><td>$date</td><td>".$totalArr[$date]['challenge']."</td><td>".$totalArr[$date]['payChallenge']."</td>".$goldChTotalValueTh."<td>".$totalArr[$date]['payGolds']."</td><td>".$totalArr[$date]['lottery']."</td><td>".$totalArr[$date]['stakeCount']."</td><td>".$totalArr[$date]['reward']."</td><td>".$totalArr[$date]['winStakeCount']."</td><td>".$totalArr[$date]['inspire']."</td>";
                foreach($serverArr as $ser){
                    $ser = "s".$ser;

                    //如果没有值，赋值为0
                    if (empty($alldata[$ser][$date]['challenge'])) {
                        $alldata[$ser][$date]['challenge'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['payChallenge'])) {
                        $alldata[$ser][$date]['payChallenge'] = 0;
                    }

                    $goldChSerValueTh = "";
                    foreach ($goldArr as $gold) {
                        $challengeVar = "pay".$gold."Challenge";
                        if (empty($alldata[$ser][$date][$challengeVar])) {
                            $alldata[$ser][$date][$challengeVar] = 0;
                        }
                        $goldChSerValueTh .= "<td>".$alldata[$ser][$date][$challengeVar]."</td>";
                    }
                    if (empty($alldata[$ser][$date]['payGolds'])) {
                        $alldata[$ser][$date]['payGolds'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['lottery'])) {
                        $alldata[$ser][$date]['lottery'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['reward'])) {
                        $alldata[$ser][$date]['reward'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['inspire'])) {
                        $alldata[$ser][$date]['inspire'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['stakeCount'])) {
                        $alldata[$ser][$date]['stakeCount'] = 0;
                    }
                    if (empty($alldata[$ser][$date]['winStakeCount'])) {
                        $alldata[$ser][$date]['winStakeCount'] = 0;
                    }

                    $html .= "<td>".$alldata[$ser][$date]['challenge']."</td><td>".$alldata[$ser][$date]['payChallenge']."</td>".$goldChSerValueTh."<td>".$alldata[$ser][$date]['payGolds']."</td><td>".$alldata[$ser][$date]['lottery']."</td><td>".$alldata[$ser][$date]['stakeCount']."</td><td>".$alldata[$ser][$date]['reward']."</td><td>".$alldata[$ser][$date]['winStakeCount']."</td><td>".$alldata[$ser][$date]['inspire']."</td>";
                }
                $html .="</tr></tbody>";
            }
            $html .= '</tbody></table></div>';
        }
    }

    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
