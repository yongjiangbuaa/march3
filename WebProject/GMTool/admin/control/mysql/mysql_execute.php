<?php
!defined('IN_ADMIN') && exit('Access Denied');
$sensitiveArray=array('INTO','OUTFILE','INFILE','GRANT','ALTER','UPDATE','INSERT','DELETE','DROP','SHOW','ADMIN','SHELL','\!','--','SHOW','DESCRIBE','EXPLAIN');

/**
 * @param $data
 */

$redisKey='mysql:task';
if (isset($_REQUEST['sql'])) {
	$html = '';
	$sqlStr = $_REQUEST['sql'];
	$sqlStr = strtoupper($sqlStr);

	$sqlArray = explode(' ', $sqlStr);
	foreach ($sqlArray as $word) {
		if (in_array($word, $sensitiveArray)) {
			$html = "请重新输入sql语句,sql中不能出现$word!";
			echo $html;
			exit();
		}
	}
	if (stripos($sqlStr, ';') !== false && stripos($sqlStr, ';') != (strlen($sqlStr) - 1)) {
		$html = ";只能出现在sql语句的末尾!";
		echo $html;
		exit();
	}

	$allServer = $_REQUEST['allServer'];
	$snapshot = $_REQUEST['snapshot'];
	$master = $_REQUEST['master'];
	$task = $_REQUEST['task'];
	if (empty($_REQUEST['selectServer']) && !$allServer) {
		$allServer = true;
	}
	if ($allServer || (!empty($_REQUEST['selectServer']))) {//
		global $servers;
		$selectedServers = array();

		if ($allServer) {
			foreach ($servers as $server => $serverInfo) {
				$selectedServers[] = $server;
			}
		} else {
			$maxServer = '';
			foreach ($servers as $server => $serverInfo) {
				if (substr($server, 0, 1) != 's') {
					continue;
				}
				$maxServer = max($maxServer, substr($server, 1));
			}

			$sttt = $_REQUEST['selectServer'];
			$sttt = str_replace('，', ',', $sttt);
			$sttt = str_replace(' ', '', $sttt);
			$tmp = explode(',', $sttt);
			foreach ($tmp as $tt) {
				$tt = trim($tt);
				if (!empty($tt)) {
					if (strstr($tt, '-')) {
						$ttArray = explode('-', $tt);
						$min = min($ttArray[1], $maxServer);
						for ($i = $ttArray[0]; $i <= $min; $i++) {
							$selectedServers[] = 's' . $i;
							$selectId[] = $i;
						}
					} else {
						if ($tt <= $maxServer) {
							$selectedServers[] = 's' . $tt;
							$selectId[] = $tt;
						}
					}
				}
			}
		}
		$sql = $_REQUEST['sql'];
		$data = array();
		$affectLines = 0;
		//硬编码去查一个服
        $selectedServers[] = 1;
		if (empty($task)) {
			if ($snapshot) {//分2种(stat_allserver查一次 或者snaphost循环查询)
				if (stripos($sql, 'stat_allserver.') !== false || stripos($sql, 'coklog_function.') !== false) {
					$result = query_infobright($sql);
					$data['all'] = $result;
				} else {
					foreach ($selectedServers as $server) {
						$sql = str_replace('$i', $server, $sql);
						$result = query_infobright($sql);
						$affectLines += $result['ret']['effect'];
						$data[$server] = $result;
					}
				}

			} else {
				if ($master == 1) {
					$type = 2;
				} else {
					$type = 3;
				}
				foreach ($selectedServers as $server) {
					$result = $page->executeServer($server, $sql, $type);
					$affectLines += $result['ret']['effect'];
					$data[$server] = $result;
				}
			}
			if ($_REQUEST['typeEvent'] == 'output') {
				//exit('ok'.print_r($result['ret']['data'],true));
				export_to_excel($data);
			}

			$html .= "<div style='float:left;width:100%;height:340px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
			$title = false;
			foreach ($data as $serverKey => $sqlDatas) {
				if ($sqlDatas['ret'] && isset($sqlDatas['ret']['data'])) {
					foreach ($sqlDatas['ret']['data'] as $sqlData) {
						if (!$title) {
							$html .= "<tr class='listTr'><th>server</th>";
							foreach ($sqlData as $key => $value)
								$html .= "<th>" . $key . "</th>";
							$html .= "</tr>";
							$title = true;
						}
						$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
						$html .= "<td>$serverKey</td>";
						foreach ($sqlData as $key => $value) {
							$html .= "<td>" . $value . "</td>";
						}
						$html .= "</tr>";
					}
				}
			}
			$html .= "</table></div><br/>";

	} else {
			if($snapshot)
			{
				echo "暂不支持离线查询统计库";
				exit();
			}
			if($master==1)
			{
				echo "暂不支持离线查询主库";
				exit();
			}
			$serverList=array();
			$count=1;
			$serverStr="";
			foreach ($selectedServers as $server) {
				$serverStr .=$server.",";
//				if($count%10==0)
//				{
//					$serverList[]=$serverStr;
//					$serverStr="";
//				}
//				$count++;
			}
			if(!empty($serverStr))
			{
				$serverList[]=$serverStr;
			}
			$user=$_COOKIE['u'];
			$date=date('Ymd_His');
			foreach ($serverList as $serverId)
			{
				$json=array();
				$json["serverId"]=$serverId;
				$json["snapshot"]=$snapshot;
				$json["master"]=$master;
				$json["sql"]=$sql;
				$json["user"]=$user;
				$json["date"]=$date;

				$page->redis(13,$redisKey,json_encode($json),'local');
			}
			$html = "已加入离线任务";
	}
}


	echo $html;
	exit();
	
	
}

function export_to_excel($data)
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
    $ret = array();

    foreach ($data as $serverKey => $sqlDatas) {
        if ($sqlDatas['ret'] && isset($sqlDatas['ret']['data'])) {
            foreach ($sqlDatas['ret']['data'] as $sqlData) {
                if (!isset($xlsTitle)) {
                    $xlsTitle[] = 'server';
                    foreach ($sqlData as $key => $value)
                        $xlsTitle[] = $key;
                }
                break;
            }
        }
    }

    //set title
    $Excel = $objPHPExcel->setActiveSheetIndex(0);
    foreach ($xlsTitle as $key => $titleName) {
        $objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($key))->setAutoSize(true);
        $Excel->setCellValue(getNameFromNumber($key) . '1', ' ' . $titleName);

    }
    //set data

    $lineIndex = 1;
    foreach ($data as $serverKey => $sqlDatas) {
        foreach ($sqlDatas['ret']['data'] as $key => $sqlData) {
            $lineIndex = $lineIndex + 1;
            $Excel->setCellValue(getNameFromNumber(0) . '' . $lineIndex, ' ' . $serverKey);
            $count = 1;
            foreach ($sqlData as $index => $value) {
                $Excel->setCellValue(getNameFromNumber($count) . '' . $lineIndex, ' ' . $value);
                $count++;
            }
        }
    }

    //filename
    $file_name = 'sqlSelect' . date('Ymd_Hi');
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

include( renderTemplate("{$module}/{$module}_{$action}") );
?>