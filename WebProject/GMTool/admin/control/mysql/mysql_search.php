<?php
!defined('IN_ADMIN') && exit('Access Denied');
$sensitiveArray=array('INTO','OUTFILE','INFILE','GRANT','ALTER','UPDATE','INSERT','DELETE','DROP','SHOW','ADMIN','shell','\!','ls');
if (isset($_REQUEST['tablename'])||isset($_REQUEST['page'])) {
	try {
		if($_REQUEST['tablename']=='world_march'){
			$table=$_REQUEST['tablename'];
			$curPage=$_REQUEST['page'];
			$pagelimit=$_REQUEST['pagelimit'];
			$whereSql="where 1=1";
			if($_REQUEST['where1']){
				$where1=$_REQUEST['where1'];
				$condition1=$_REQUEST['condition1'];
				$num1=$_REQUEST['num1'];
				$whereSql .= " and $where1 $condition1 $num1 ";
			}
			if($_REQUEST['where2']){
				$where2=$_REQUEST['where2'];
				$condition2=$_REQUEST['condition2'];
				$num2=$_REQUEST['num2'];
				$whereSql .= " and $where2 $condition2 $num2 ";
			}
			$sql="select count(1) num from $table $whereSql";
			
			$sqlArray=explode(' ', $sql);
			foreach ($sqlArray as $word){
				if (in_array($word, $sensitiveArray)){
					$html="请重新输入sql语句,sql中不能出现$word!";
					echo $html;
					exit();
				}
			}
			if (stripos($sql,';')!==false && stripos($sql,';')!=(strlen($sql)-1)){
				$html=";只能出现在sql语句的末尾!";
				echo $html;
				exit();
			}
			
			
			
			$result = $page->executeServer($currentServer, $sql, 3);
			if($result['error'] || (!$result['ret']['data'])){
				exit();
			}
			$sum = $result['ret']['data'][0]['num'];
			$marchPager = page($sum, $curPage, $pagelimit);
			$index = $marchPager['offset'];
			$sql = "select uuid, ownerUid, ownerId, ownerName, allianceId, teamId, type, targetId, targetType, targetUid, marchStartTime, marchTime, marchArrivalTime, returnStartTime, arrivalTime, exploreTime, state from world_march $whereSql limit $index,$pagelimit;";
			$result = $page->executeServer($currentServer, $sql, 3);
		}else {
			$page = new BasePage();
			$param = $_REQUEST;
			foreach ($param as $pa){
				$tempArray=explode(' ', $pa);
				foreach ($tempArray as $word){
					if (in_array($word, $sensitiveArray)){
						$html="请重新输入sql语句,sql中不能出现$word!";
						echo $html;
						exit();
					}
				}
				if (stripos($pa,';')!==false){
					$html=";只能出现在sql语句的末尾!";
					echo $html;
					exit();
				}
			}
			$param["type"] = 3;
			$param = array(
					"changes"=>null,
					"params"=>$param,
				);
			$sendParam = array('info'=>'','data'=>$param);
			$result = $page->call( 'gm/gm/Mysql', $sendParam );
		}
		if ( isset( $result['error'] ) ){
			echo $result['error'];
			exit();
		}
		else {
			if($_REQUEST['type'] == 'output')
			{
				
				//exit('ok'.print_r($result['ret']['data'],true));
				
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
				$sqlDatas = $result['ret']['data'];
				foreach ($sqlDatas as $index => $sqlData)
				{
					if(!isset($xlsTitle))
					{
						foreach ($sqlData as $key=>$value)
							$xlsTitle[] = $key;
					}
					break;
				}
				$titleIndex = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE');
				//set title
				$Excel = $objPHPExcel->setActiveSheetIndex(0);
				foreach ($xlsTitle as $key=>$titleName)
				{
					$objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($key))->setAutoSize(true);
					$Excel->setCellValue(getNameFromNumber($key).'1',' '.$titleName);
				}
				//set data
				foreach ($sqlDatas as $key => $sqlData){
					$key = $key + 2;
					$count = 0;
					foreach ($sqlData as $index=>$value)
					{
						$Excel->setCellValue(getNameFromNumber($count).''.$key, ' '.$value);
						$count++;
					}
				}
				//filename
				$file_name = 'sqlData';
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
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
		exit();
	}
	$html = "<div style='float:left;width:100%;height:500px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$title = false;
	$sqlDatas = $result['ret']['data'];
	if($_REQUEST['tablename']=='world_march'){
		$pager=$marchPager;
	}else{
		$pager = $result['ret']['page'];
	}
	foreach ($sqlDatas as $sqlData)
	{
		if(!$title)
		{
			$html .= "<tr class='listTr'>";
			foreach ($sqlData as $key=>$value)
				$html .= "<th>" . $key . "</th>";
			$html .= "</tr>";
			$title = true;
		}
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($sqlData as $key=>$value){
			$html .= "<td>" . $value . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table><br/>";
	if($_REQUEST['tablename']=='world_march'){
		if($pager['pager']){
			$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . '<br />'.$pager['pager'] . "</div>";
		}
	}else{
		if($pager != null){
			$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager . "</div>";
		}
	}
	$html .= "</div>";
	echo $html;
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