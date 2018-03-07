<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
$lang = loadLanguage();
$clientXml = loadXml('daily_active','daily_active');
global $servers;
$allServerFlag=true;
if($_REQUEST['allServers']){
    $allServerFlag =true;
}
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];
if ($_REQUEST ['event'] == 'platform') {
    $start = $_REQUEST['start'] ? date('Ymd', strtotime($_REQUEST['start'])) : date('Ymd', strtotime($start));
    $end = $_REQUEST['end'] ? date('Ymd', strtotime($_REQUEST['end'])) : date('Ymd', strtotime($end));
    $time = "date>=$start and date<=$end ";
    if ($_REQUEST['selectAppVersion'] && $_REQUEST['selectAppVersion'] != 'ALL') {
        $version = $_REQUEST['selectAppVersion'];
        $whereSql = " and appVersion='$version'";//得加单引号
    }

    $sids = implode(',', $selectServerids);
    if(!$allServerFlag) {
        $whereSql .= " and sid in ($sids) ";
        if ($version) {
            $sql_sum = "select * from stat_allserver.stat_log_rbi_dailyActive where   {$time} {$whereSql} group by sid,activeId,date ORDER BY date desc;";
        } else {
            $sql_sum = "select sid,activeId,date,sum(part) part,sum(complete) complete,sum(reward) reward from stat_allserver.stat_log_rbi_dailyActive where   {$time} {$whereSql} group by sid,activeId,date  ORDER BY date desc;";
        }
    }else{
        if ($version) {
            $sql_sum = "select * from stat_allserver.stat_log_rbi_dailyActive where  {$time} {$whereSql} group by activeId,date  ORDER BY date desc;";
        } else {
            $sql_sum = "select activeId,date,sum(part) part,sum(complete) complete,sum(reward) reward from stat_allserver.stat_log_rbi_dailyActive where   {$time} {$whereSql} group by activeId,date  ORDER BY date desc;";
        }
    }
//    $time = "timeStamp>=$start and timeStamp<=$end ";
//    $sql_sum = "select var_data1,type,count(DISTINCT log_rbi.userid) as `sum` from log_rbi where category=17 and {$time}  group by type,var_data1;";
    $result_sum = query_infobright($sql_sum);
    $result_sum = $result_sum['ret']['data'];
//foreach($result_sum as $item){
//    $sid =  $item['sid'];
//    $date = $item['date'];
//    $appVersion = $item['appVersion'];
//    $ones[$sid][$date][$appVersion]['name'] += $lang[(int)$clientXml[$item['activeId']]['name']];
//    $ones[$sid][$date][$appVersion]['part'] += $item['appVersion'];
//    $ones[$sid][$date][$appVersion]['complete'] +=$item['complete'];
//    $ones[$sid][$date][$appVersion]['reward'] += $item['reward'];
//
//}
    $title = array(
        '服',
        '日期',
        '版本',
        '任务ID',
        '参与的人数',
        '完成的人数',
        '领取奖励的人数',
    );
    $html .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach($result_sum as $num=>$sqlData){
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        if(!$allServerFlag) {
            $html .= "<td>" . $sqlData['sid'] . "</td>";
        }else{
            $html .= "<td>" . '合计' . "</td>";
        }
        $html .= "<td>" . $sqlData['date'] . "</td>";
        if($version){
            $html .= "<td>" . $sqlData['appVersion'] . "</td>";
        }else{
            $html .= "<td> ALL </td>";
        }
        $html .= "<td>" . $lang[(int)$clientXml[$sqlData['activeId']]['name']] . "</td>";
        $html .= "<td>" . $sqlData['part'] . "</td>";
        $html .= "<td>" . $sqlData['complete'] . "</td>";
        $html .= "<td>" . $sqlData['reward'] . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>".$pager['pager']."</div>";
//    echo $html;//也不能要
//    exit(); //用submit时不能退出
}
if($_REQUEST['event']=='output'){
    $start = $_REQUEST['start'] ? date('Ymd', strtotime($_REQUEST['start'])) : date('Ymd', strtotime($start));
    $end = $_REQUEST['end'] ? date('Ymd', strtotime($_REQUEST['end'])) : date('Ymd', strtotime($end));
    $time = "date>=$start and date<=$end ";
    if ($_REQUEST['selectAppVersion'] && $_REQUEST['selectAppVersion'] != 'ALL') {
        $version = $_REQUEST['selectAppVersion'];
        $whereSql = " and appVersion='$version'";//得加单引号
    }

    $sids = implode(',', $selectServerids);
    $whereSql.=" and sid in ($sids) ";
    if($version){
        $sql_sum = "select * from stat_allserver.stat_log_rbi_dailyActive where   {$time} {$whereSql} group by sid,activeId,date ;";
    }else{
        $sql_sum = "select sid,activeId,date,sum(part) part,sum(complete) complete,sum(reward) reward from stat_allserver.stat_log_rbi_dailyActive where   {$time} {$whereSql} group by sid,activeId,date ;";
    }
//    $time = "timeStamp>=$start and timeStamp<=$end ";
//    $sql_sum = "select var_data1,type,count(DISTINCT log_rbi.userid) as `sum` from log_rbi where category=17 and {$time}  group by type,var_data1;";
    $result_sum = query_infobright($sql_sum);
    $result_sum = $result_sum['ret']['data'];

    $title = array(
        '服',
        '日期',
        '版本',
        '任务ID',
        '参与的人数',
        '完成的人数',
        '领取奖励的人数',
    );
    //导入PHPExcel类
    require ADMIN_ROOT . "/include/PHPExcel.php";
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Set properties
    $objPHPExcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
    $titleIndex = array('A','B','C','D','E','F','G','H','I','J','K');
    //set title
    $Excel = $objPHPExcel->setActiveSheetIndex(0);
    $row = 1;
    //set data
    $line = 0;
    foreach ($title as $width=>$value){
        if(strlen($value) != mb_strlen($value)){
            $width = (strlen($value) + iconv_strlen($value))* 1.1 * 8.26/22;
            $objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setWidth($width);
        }else{
            $objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setAutoSize(true);
        }
        $Excel->setCellValue($titleIndex[$line++].''.$row,$value);
    }
    $row++;
        foreach ($result_sum as  $num=>$sqlData){
            $Excel->setCellValue($titleIndex[0].''.$row, $sqlData['sid'] );
            $Excel->setCellValue($titleIndex[1].''.$row,$sqlData['date']);
            if($version){
                $Excel->setCellValue($titleIndex[2].''.$row,$sqlData['appVersion']);
            }else{
                $Excel->setCellValue($titleIndex[2].''.$row,'ALL');
            }
            $Excel->setCellValue($titleIndex[3].''.$row,$lang[(int)$clientXml[$sqlData['activeId']]['name']]);
            $Excel->setCellValue($titleIndex[4].''.$row,$sqlData['part']);
            $Excel->setCellValue($titleIndex[5].''.$row,$sqlData['complete']);
            $Excel->setCellValue($titleIndex[6].''.$row,$sqlData['reward']);
            $row++;
        }
    //filename
    $file_name = '每日任务统计';
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle($file_name);
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    // Redirect output to a client鈥檚 web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename={$file_name}.xls");
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>