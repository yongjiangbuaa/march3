<?php
!defined('IN_ADMIN') && exit('Access Denied');
function loadDiv_this($defValue)
{
    $maxServer = getMaxServerNum();
    $placeholder = '默认当前服 ,当前最大服为:' . $maxServer;
    return '<div class="span11">
	服编号<input type="text" id="selectServer" name="selectServer" value="' . $defValue . '" placeholder="' . $placeholder . '" style="width: 400px;"/>
	</div>';
}

global $servers;
$sttt = $_REQUEST['selectServer'];
$serverDiv = loadDiv_this($sttt);

if (!$_REQUEST['selectPf']) {
    $currPf = 'ALL';
} else {
    $currPf = $_REQUEST['selectPf'];
}

if ($sttt == null) {
    $selectServer = array($_COOKIE['Gserver2'] => "");

} else {
    $erversAndSidsArr = getSelectServersAndSids($sttt);
    $selectServer = $erversAndSidsArr['withS'];
    if (sizeof($selectServer) > 10) {
        $alertHeader = '数据量大时,可能会超时';
    }
    $selectServerids = $erversAndSidsArr['onlyNum'];
}
if (!$_REQUEST['end']) {
    $end = date("Y-m-d 23:59:59", time());
} else {
    $end = $_REQUEST['end'];
}

if (!empty($_REQUEST['start_time'])) {
    $start = $_REQUEST['start_time'];
} else {
    $start = date('Y-m-d 00:00:00', strtotime("-1 day"));
}

if ($_REQUEST['action'] == 'show' || $_REQUEST['action'] == 'output') {
    if ($_REQUEST['start_time']) {
        $start1 = date('Ymd',strtotime($_REQUEST['start_time']) );
    }
    if ($_REQUEST['end_time']) {
        $end1 = date('Ymd',strtotime($_REQUEST['end_time']));
    }
    $result = array();
    $levelArr = array();
    $dateArr = array();
    $refArr = array();
    $group = 1;//页面用

    $sql = "select date,referrer,count(1) cnt from stat_allserver.stat_dau_daily_pf_country_referrer where date>= $start1 and date<$end1 group by date,referrer order by date,cnt desc;";

    $server_result = query_infobright($sql);
    foreach ($server_result['ret']['data'] as $curRow) {
        $levelArr[$curRow['date']][$curRow['referrer']] = $curRow['referrer']; //x轴用到
        $result[$curRow['date']][$curRow['referrer']] += $curRow['cnt'];
        $dateArr[$curRow['date']] = $curRow['date'];
        $refArr[$curRow['referrer']] = $curRow['referrer'];
    }
//echo $sql.PHP_EOL;

    if (in_array($_COOKIE['u'],$privilegeArr)) {
        echo print_r($result, true);
    }

    $title = array('渠道', '数量', '百分比');
    $htmlmy .= "<div style='float:left;width:90%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";

    $htmlmy .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $htmlmy .= "<tr class='listTr'>";
    foreach ($dateArr as $key => $value) { //日期 一级标题
        $htmlmy .= "<th colspan='3' style='text-align:center;'>" . $value . "</th>";
    }
    $htmlmy .= "</tr>";
    $htmlmy .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
    foreach ($dateArr as $item) {//二级标题
        foreach ($title as $titlevalue) {
            $htmlmy .= "<td>$titlevalue</td>";
        }
    }
    $htmlmy .= "</tr>";

    foreach ($refArr as $Lv => $name) { //内容,一共 $leveltitle.size 行数据
        $htmlmy .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($dateArr as $item) {
            $sum = array_sum($result[$item]);

            $value = $result[$item][$Lv] ? $result[$item][$Lv] : 0;
            $per = intval($value * 10000 / $sum) / 100;
            $htmlmy .= "<td>$name</td><td>$value</td><td>$per" . "%" . "</td>";
        }
        $htmlmy .= "</tr>";
    }
    $htmlmy .= "</table></div>";



    if (false && $_REQUEST['action'] == 'output') {
        $title = array('4%' => '等级', '人数', '------', '大本等级', '人数', '------', '国家分布', '人数');
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
        $titleIndex = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE');
        //set title
        $Excel = $objPHPExcel->setActiveSheetIndex(0);
        $row = 1;
        //set data
        $line = 0;
        foreach ($title as $width => $value) {
            if (strlen($value) != mb_strlen($value)) {
                $width = (strlen($value) + iconv_strlen($value)) * 1.1 * 8.26 / 22;
                $objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setWidth($width);
            } else {
                $objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setAutoSize(true);
            }
            $Excel->setCellValue($titleIndex[$line++] . '' . $row, $value);
        }
        $row++;
        $tempRow = max(count($result['mainBuilding'][0]), count($result['level'][0]), count($result['country']));
        $levelKey = array_keys($result['level'][0]);
        $mainBuildingKey = array_keys($result['mainBuilding'][0]);
        $countryKey = array_keys($result['country']);
        for ($i = 0; $i < $tempRow; $i++) {
            if ($i < count($result['level'][0])) {
                $Excel->setCellValue($titleIndex[0] . '' . $row, $levelKey[$row - 2]);
                $Excel->setCellValue($titleIndex[1] . '' . $row, $result['level'][0][$levelKey[$row - 2]]);
            } else {
                $Excel->setCellValue($titleIndex[0] . '' . $row, '');
                $Excel->setCellValue($titleIndex[1] . '' . $row, '');
            }
            $Excel->setCellValue($titleIndex[2] . '' . $row, '------');
            if ($i < count($result['mainBuilding'][0])) {
                $Excel->setCellValue($titleIndex[3] . '' . $row, $mainBuildingKey[$row - 2]);
                $Excel->setCellValue($titleIndex[4] . '' . $row, $result['mainBuilding'][0][$mainBuildingKey[$row - 2]]);
            } else {
                $Excel->setCellValue($titleIndex[3] . '' . $row, '');
                $Excel->setCellValue($titleIndex[4] . '' . $row, '');
            }
            $Excel->setCellValue($titleIndex[5] . '' . $row, '------');
            if ($i < count($result['country'])) {
                $Excel->setCellValue($titleIndex[6] . '' . $row, $countryKey[$row - 2]);
                $Excel->setCellValue($titleIndex[7] . '' . $row, $result['country'][$countryKey[$row - 2]]);
            } else {
                $Excel->setCellValue($titleIndex[6] . '' . $row, '');
                $Excel->setCellValue($titleIndex[7] . '' . $row, '');
            }
            $row++;
        }
        //filename
        $file_name = '等级分布统计';
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


}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>