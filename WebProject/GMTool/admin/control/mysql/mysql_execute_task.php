<?php
include_once(AMFPHP_BASE . "shared/util/JSON.php");
$sensitiveArray=array('INTO','OUTFILE','INFILE','GRANT','ALTER','UPDATE','INSERT','DELETE','DROP','SHOW','ADMIN','SHELL','\!','--','SHOW','DESCRIBE','EXPLAIN');

/**
 * @param $data
 */

$redisKey='mysql:task';
$logPath='/data/htdocs/bgtask/sqlTask_error.log';

define('GLOBAL_DB_SERVER_IP', '127.0.0.1');
define('GLOBAL_DB_SERVER_USER', 'march');
define('GLOBAL_DB_SERVER_PWD', 'hdli54T5P');
define('GAME_DB_SERVER_USER', 'march');
define('GAME_DB_SERVER_PWD', 'hdli54T5P');

//define('GLOBAL_DB_SERVER_IP', '10.1.16.211');
//define('GLOBAL_DB_SERVER_USER', 'cok');
//define('GLOBAL_DB_SERVER_PWD', '1234567');
//define('GAME_DB_SERVER_USER', 'cok');
//define('GAME_DB_SERVER_PWD', '1234567');

define('GLOBAL_DEPLOY_DB_NAME', 'cokdb_admin_deploy');

do{
    try
    {
        $data=null;
        $jsonData=null;
        $server=null;
        $snapshot = null;
        $master = null;
        $user = null;
        $date = null;
        $serverStr=null;
        $serverList=null;
        $html=null;
        try{
            $redis = new Redis();
            $re = $redis->connect('127.0.0.1', 6379, 3);
            if(!$re){
                sleep(10);
                continue;
            }
            $data=$redis->rPop($redisKey);
        }catch (Exception $e)
        {
            $html=$user.$sql.$e->getMessage()."\n";
            file_put_contents($logPath,$html, FILE_APPEND);
        }
        $redis->close();
        if(empty($data))
        {
            sleep(10);
            continue;
        }

        file_put_contents($logPath,$data."\n", FILE_APPEND);
        $jsonData=json_decode($data,true);

        $sqlStr=$jsonData['sql'];
        $sqlStr=strtoupper($sqlStr);
        $sqlArray=explode(' ', $sqlStr);
        foreach ($sqlArray as $word){
            if (in_array($word, $sensitiveArray)){
                $html ="请重新输入sql语句,sql中不能出现$word!"."\n";
                break;
            }
        }
        if(!empty($html))
        {
            file_put_contents($logPath,$html, FILE_APPEND);
            continue;
        }
        if (stripos($sqlStr,';')!==false && stripos($sqlStr,';')!=(strlen($sqlStr)-1)){
            $html =";只能出现在sql语句的末尾!"."\n";
            file_put_contents($logPath,$html, FILE_APPEND);
            continue;
        }

        $snapshot = $jsonData['snapshot'];
        $master = $jsonData['master'];
        $user = $jsonData['user'];
        $date = $jsonData['date'];
        $serverStr=$jsonData['serverId'];
        $serverList=explode(',',$serverStr);
        if (!empty($serverStr)) {//
            $sql = $jsonData['sql'];
            $resultData = array();
            $affectLines = 0;
            if ($snapshot) {//分2种(stat_allserver查一次 或者snaphost循环查询)
//                if (stripos($sql, 'stat_allserver.') !== false || stripos($sql, 'coklog_function.') !== false) {
//                    $result = query_infobright($sql);
//                    $resultData['all'] = $result;
//                } else {
//                    foreach ($serverList as $server) {
//                        $sql = str_replace('$i', $server, $sql);
//                        $result = query_infobright($sql);
//                        $affectLines += $result['ret']['effect'];
//                        $resultData[$server] = $result;
//                    }
//                }
                $html='统计库暂不支持'.$user.$sql."\n";
                file_put_contents($logPath,$html, FILE_APPEND);
                continue;

            } else {
                if ($master == 1) {
                    $html='主库库暂不支持'.$user.$sql."\n";
                    file_put_contents($logPath,$html, FILE_APPEND);
                    continue;
                } else {
                    $type = 3;
                }



                //导入PHPExcel类
//                require "/data/htdocs/ifadmin/admin/include/PHPExcel.php";
                // Create new PHPExcel object


                $sheetNum=0;
                $file_name = '/data/htdocs/download/'.$user . $date.'.txt';
                foreach ($serverList as $server) {

                    if(empty($server))
                    {
                        continue;
                    }
                    $html=$sql." 执行到 ".$server."\n";
                    file_put_contents($logPath,$html, FILE_APPEND);

                    if (substr($server, 0, 1) != 's') {
                        $html='server is error '.$user.$sql.$server."\n";
                        file_put_contents($logPath,$html, FILE_APPEND);
                        continue;
                    }
                    $maxServer = max($maxServer, substr($server, 1));

                    $con = mysql_connect(GLOBAL_DB_SERVER_IP,GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD);
                    if (!$con)
                    {
                        $html ='Could not connect: ' . mysql_error()."\n";
                        echo $html;
                        file_put_contents($logPath,$html, FILE_APPEND);
                        continue;
                    }else {
                        mysql_select_db(GLOBAL_DEPLOY_DB_NAME);
                        $sql1 = "select * from tbl_db where db_id=$maxServer";
                        $result1 = mysql_query($sql1, $con);
                        while ($row = mysql_fetch_array($result1)) {
                            $slave_ip_inner= $row['slave_ip_inner'];
                            $port= $row['port'];
                            $db= $row['dbname'];
                        }
                    }
                    mysql_close($con);

                    $serverCon = mysql_connect($slave_ip_inner.":".$port,GAME_DB_SERVER_USER,GAME_DB_SERVER_PWD);
                    if (!$con)
                    {
                        $html ='Could not connect: ' . mysql_error()."\n";
                        echo $html;
                        file_put_contents($logPath,$html, FILE_APPEND);
                        continue;
                    }else {
                        mysql_select_db($db);

                        $returnData=array();
                        $result2 = mysql_query($sql, $serverCon);
                        while ($row = mysql_fetch_array($result2)) {
//                            $dataList=array();
//                            $num=0;
//                            foreach ($row as $key => $value)
//                            {
//                                if($num%2!=0)
//                                {
//                                    $dataList[$key]=$value;
//                                }
//                            }
                            $returnData[]=$row;
                        }
                    }
                    mysql_close($serverCon);



                    $result =array('ret' => array('data' => $returnData )); //$page->executeServer($server, $sql, $type);

//                    $resultData[$server] = $result;


//                    file_put_contents($logPath,$result['ret']['data'], FILE_APPEND);
                        if ($result['ret'] && isset($result['ret']['data'])) {
                            foreach ($result['ret']['data'] as $sqlData) {
                                if (!isset($xlsTitle)) {
                                    $xlsTitle[] = 'server';
                                    foreach ($sqlData as $key => $value)
                                    {
                                        $xlsTitle[] = $key;
                                    }
                                }
                                break;
                            }
                        }




//                    if($sheetNum>0)
//                    {
//                        $objPHPExcel = PHPExcel_IOFactory::createReader($file_name);
////                        $objPHPExcel = $objReader->load($file_name);
//                        $objPHPExcel->createSheet();
//                    }else{
//                        $objPHPExcel = new PHPExcel();
//                        // Set properties
//                        $objPHPExcel->getProperties()
//                            ->setCreator("Maarten Balliauw")
//                            ->setLastModifiedBy("Maarten Balliauw")
//                            ->setTitle("data")
//                            ->setSubject("Office 2007 XLSX Test Document")
//                            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
//                            ->setKeywords("office 2007 openxml php")
//                            ->setCategory("Test result file");
//                    }
//                    $Excel = $objPHPExcel->setActiveSheetIndex($sheetNum);
                    if($sheetNum<=0)
                    {
                        $strTitle="";
                        foreach ($xlsTitle as $key => $titleName) {
//                        $objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($key))->setAutoSize(true);
//                        $Excel->setCellValue(getNameFromNumber($key) . '1', ' ' . $titleName);

                            $strTitle=$strTitle."$$".$titleName;

                        }
                        file_put_contents($file_name,$strTitle."\n", FILE_APPEND);
                    }

                    $sheetNum++;


                    $lineIndex = 1;

                        foreach ($result['ret']['data'] as $key => $sqlData) {

                            $str2="";
                            $str2="$$".$server;

                            $lineIndex = $lineIndex + 1;
//                            $Excel->setCellValue(getNameFromNumber(0) . '' . $lineIndex, ' ' . $server);
                            $count = 1;
                            foreach ($sqlData as $index => $value) {
//                                $Excel->setCellValue(getNameFromNumber($count) . '' . $lineIndex, ' ' . $value);
                                $str2=$str2."$$".$value;
                                $count++;
                            }
                            file_put_contents($file_name,$str2."\n", FILE_APPEND);
                        }

//                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    //filename

                    // Rename sheet
//                $objPHPExcel->getActiveSheet()->setTitle($file_name);
                    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    // Redirect output to a client鈥檚 web browser (Excel5)
//                    header('Content-Type: application/vnd.ms-excel');
//                    header("Content-Disposition: attachment;filename={$file_name}.xls");
//                    header('Cache-Control: max-age=0');
//
//
//                    $objWriter->save('/data/htdocs/download/'.$file_name.'.xls');

                }


            }
//            export_to_excel($resultData,$user,$date);
        }

    }catch (Exception $e)
    {

        $html=$user.$sql.$e->getMessage()."\n";
        file_put_contents($logPath,$html, FILE_APPEND);

    }


}while(false);


function export_to_excel($data,$user,$date)
{

    $ret = array();



    //set title

    //set data




}
function getNameFromNumber($num) {
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}

?>