<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (!$_REQUEST['startDate']) {
    $startDate = date("Y-m-d", time() - 86400 * 7);
}
if (!$_REQUEST['endDate'])
    $endDate = date("Y-m-d", time());

if (!$_REQUEST['selectCountry']) {
    $currCountry = 'ALL';
} else {
    $currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
    $currPf = 'ALL';
} else {
    $currPf = $_REQUEST['selectPf'];
}

$showData = false;
$alertHeader = "";

if ($_REQUEST['event'] ) {

    $startDate = substr($_REQUEST['startDate'], 0, 10);
    $sDdate = date('Ymd', strtotime($startDate));
    $endDate = substr($_REQUEST['endDate'], 0, 10);
    $eDate = date('Ymd', strtotime($endDate) + 86400);
    $whereSqlNew = " date >=$sDdate and date <= $eDate ";

    if ($currCountry != 'ALL') {
        $whereSqlNew .= " and country='$countries' ";
    }
    if($currPf !='ALL'){
        $whereSqlNew .= " and pf='$currPf' ";
    }

    $eventAll   = array();
    $dayArr = array(1, 3, 7, 15, 30);
    foreach ($dayArr as $day) {
        $rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
    }
    $fields = implode(',', $rfields);

//    if($currCountry =='ALL'){
//        $sql = "select date,pf,sum(reg) as reg ,sum(dau) as dau,sum(pdau) as pdau,sum(pdau_move) as pdau_move,sum(paytotal) as paytotal ,sum(payusers) as payusers ,sum(firstpayusers) as firstpayusers,sum(newTotalPay) as newTotalPay ,$fields from stat_allserver.basic_operation
//			where $whereSqlNew
//			group by date ,pf order by date desc";
//    }else {
//        $sql = "select date,pf,country ,reg,dau,pdau,pdau_move,paytotal,payusers,firstpayusers,newTotalPay ,$fields from stat_allserver.basic_operation
//			where $whereSqlNew
//			group by date,pf,country order by date desc";
//    }
    $sql = "select date,sum(reg) as reg ,sum(dau) as dau,sum(pdau) as pdau,sum(pdau_move) as pdau_move,sum(paytotal) as paytotal ,sum(payusers) as payusers ,sum(firstpayusers) as firstpayusers,sum(newTotalPay) as newTotalPay ,$fields from stat_allserver.basic_operation
			where $whereSqlNew
			group by date order by date desc";

    $result = query_infobright($sql);

    foreach ($result['ret']['data'] as $curRow) {
        $dateIndesx = $curRow['date'];

        $dateArray[] = $dateIndesx;

        $eventAll[$dateIndesx]['reg'] = $curRow['reg'];
        $eventAll[$dateIndesx]['dau'] = $curRow['dau'];//日活跃
        $eventAll[$dateIndesx]['paid_dau'] = $curRow['pdau'];
        $eventAll[$dateIndesx]['pdau_relocation'] = $curRow['pdau_move'];
        $eventAll[$dateIndesx]['sdau'] = $curRow['dau'] - $curRow['reg']; //老玩家 (就是dau-新注册)
        $eventAll[$dateIndesx]['payTotle'] = $curRow['paytotal'];
        $eventAll[$dateIndesx]['payUsers'] = $curRow['payusers'];
        $eventAll[$dateIndesx]['firstPay'] = $curRow['firstpayusers'];
        $eventAll[$dateIndesx]['newTotalPay'] = $curRow['newTotalPay'];


        foreach ($dayArr as $day) {
            $count = $curRow['r' . $day] ? $curRow['r' . $day] : 0;
//            $eventAll[$dateIndesx][$day] = $count;//因为有这个导致剩下 不生效.!!!!!!!!!!!!
            $forDay = (time() - strtotime($dateIndesx)) / 86400;
            if ($forDay < $day) {
                $eventAll[$dateIndesx]['r'.$day] = "-";
            } else {
                $eventAll[$dateIndesx]['r'.$day] = intval($count/ $eventAll[$dateIndesx]['reg']  * 10000 ) / 100 ."%";
            }
        }

        $eventAll[$dateIndesx]['filter'] = intval($eventAll[$dateIndesx]['payUsers'] * 10000 / $eventAll[$dateIndesx]['dau']) / 100 . "%";

        $eventAll[$dateIndesx]['ARPU'] = intval($eventAll[$dateIndesx]['payTotle'] * 100 / $eventAll[$dateIndesx]['payUsers']) / 100;
    }
//        if ($_REQUEST['type'] == 1) {
//            $sql = "INSERT into operation_log (date,logs) VALUES (" . $_REQUEST['datekey'] . "," . "'" . $_REQUEST['num'] . "'" . ") ";
//            $sql .= " ON DUPLICATE KEY UPDATE date=" . $_REQUEST['datekey'] . " ,logs=" . "'" . $_REQUEST['num'] . "'";
//            $page->globalExecute($sql, 2);
////		$sql = "update operation_log set logs = " . $_REQUEST['num'] . " where date = " . $_REQUEST['datekey'];
////		$page->globalExecute($sql, 2);
//        }
//        $log_sql = "select * from operation_log where date >=$sDdate and date <= $eDate;";
//        $log_ret = $page->globalExecute($log_sql, 3);
//        foreach ($log_ret['ret']['data'] as $curRow) {
//            $date = $curRow['date'];
//            $num[$date] = $curRow['logs'];
//        }

        if ($eventAll && $_REQUEST['event'] == 'user') {
            $showData = true;
        } else {
            $alertHeader = '没有查询到相关数据';
        }
    if($_REQUEST['event'] == 'output'){
        $xlsTitle = array(
            'date'=>'日期',
            'reg'=>'新注册',
            'dau'=>'日活跃',
            'sdau'=>'老玩家',
            'paid_dau'=>'付费DAU',
            'pdau_relocation'=>'付费DAU(迁服)',
            'payTotle'=>'付费总值',
            'payUsers'=>'付费用户',
            'firstPay'=>'首冲用户',
            'newTotalPay'=>'首冲付费金额',
            'filter'=>'付费渗透率',
            'ARPU'=>'ARPU'
        );
        $tmpArr = array();
        foreach($dayArr as $day){
            $tmpArr['r'.$day] = $day . '日次留存';
        }
        $xlsTitle = array_merge($xlsTitle,$tmpArr);
        export_to_excel($eventAll,$xlsTitle);
    }
}

function export_to_excel($data,$xlsTitle)
{
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

    //A1 B1 C1
    //A2 B2 C2
    //set title
    $Excel = $objPHPExcel->setActiveSheetIndex(0);
    $i = 0;
    foreach ($xlsTitle as $key => $titleName) {
        $objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($i))->setAutoSize(true);
        $Excel->setCellValue(getNameFromNumber($i) .'1', ' '. $titleName); //1代表第一行 标题
        ++$i;
    }
    //set data
    $lineIndex = 1;
    foreach ($data as $date => $sqlDatas) {
        $count = 0; //第几列
        $lineIndex++; //第几行数据
        $Excel->setCellValue(getNameFromNumber($count) .$lineIndex, ' ' . $date);

        foreach ($xlsTitle as $key => $value) {
            if($count == 0 && $key == 'date') {
                $count++; //第一列 日期那列
                continue;
            }

            $tmp = $data[$date][$key];
            $Excel->setCellValue(getNameFromNumber($count) .$lineIndex, ' ' . $tmp);
            $count++;
        }
    }

    //filename
    $file_name = 'sqlSelect' . date('Ymd_His');
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
function getNameFromNumber($num)
{
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>