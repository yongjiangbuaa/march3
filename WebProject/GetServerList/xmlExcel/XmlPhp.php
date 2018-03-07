<?php

require_once './Classes/PHPExcel/IOFactory.php';
require_once './Classes/PHPExcel.php';
require_once './XmlContainer.php';
require_once './XpathContainer.php';

ini_set ( "register_globals", "On" );
ini_set ( "display_errors", "On" );
ini_set ( "magic_quotes_gpc", "Off" );
error_reporting ( E_ERROR );
date_default_timezone_set ( 'Asia/Shanghai' );

class XmlPhp {
	private $_fileName = "";
	public function __construct($fileName) {
		$this->_fileName = $fileName;
	}
	
	/**
	 * Get xml data of items
	 *
	 * @return SimpleXMLElement[]
	 */
	function viewXmlDataAsHtml($xmlFilePath, $cfgSvrUriFrom = NULL, $fServerId = NULL, $cfgSvrUriTo = NULL) {
		header ( 'Content-Type: text/html; charset=utf-8' );
		
		if($cfgSvrUriFrom){
			$hrefXlsx = './ProcessRequest.php?act=download&type=xlsx&filename=' . $this->_fileName . '&cfgSvrUriFrom=' . $cfgSvrUriFrom . '&fServerId=' . $fServerId.'&cfgSvrUriTo='.$cfgSvrUriTo;
			$hrefXlsm = './ProcessRequest.php?act=download&type=xlsm&filename=' . $this->_fileName . '&cfgSvrUriFrom=' . $cfgSvrUriFrom . '&fServerId=' . $fServerId.'&cfgSvrUriTo='.$cfgSvrUriTo;
			$hrefZip = './ProcessRequest.php?act=download&type=Zip&filename=' . $this->_fileName . '&cfgSvrUriFrom=' . $cfgSvrUriFrom . '&fServerId=' . $fServerId.'&cfgSvrUriTo='.$cfgSvrUriTo;
		} else {
			$hrefXlsx = './ProcessRequest.php?act=download&type=xlsx&filename=' . $this->_fileName;
			$hrefXlsm = './ProcessRequest.php?act=download&type=xlsm&filename=' . $this->_fileName;
			$hrefZip = './ProcessRequest.php?act=download&type=Zip&filename=' . $this->_fileName;
		}
		echo "fileName: " . $this->_fileName . "<br>";
		echo "<a href='./" . $hrefXlsx . "'> 下载不可更新数据的Excel文件(2007格式)  </a>&nbsp&nbsp&nbsp&nbsp";
		echo "<a href='./" . $hrefXlsm . "'> 下载可更新数据的Excel文件(2007格式)  </a>&nbsp&nbsp&nbsp&nbsp";
		echo "<a href='./" . $hrefZip . "'> 下载压缩过的Excel文件(Zip格式)  </a><br>";
		
		$xmlContainer = new XmlContainer ( $xmlFilePath );
		$xmlContainer->loadDataAsHtml ();
	}
	
	function getMbString(&$stringTodo, $length, $left = TRUE) {
		$i = $length;
		
		$strTmp = $stringTodo;
		while ( strlen ( $strTmp ) > $length ) {
			if ($left) {
				$strTmp = mb_substr ( $strTmp, 0, $i, 'utf-8' );
			} else {
				$strTmp = mb_substr ( $strTmp, - $i, $i, 'utf-8' );
			}
			$i --;
		}
		
		if (strlen ( $stringTodo ) > $length) {
			if ($left) {
				$pos = $length;
				$stringTodo = substr ( $stringTodo, $pos );
			} else {
				$stringTodo = substr ( $stringTodo, 0, - $length );
			}
		}
		return $strTmp;
	}
	
	function getMbStringToArray($stringTodo, $length, $left = TRUE) {
		$strTmp = $stringTodo;
		$ret = array ();
		while ( strlen ( $strTmp ) > $length ) {
			$ret [] = $this->getMbString ( $strTmp, $length, $left );
		}
		if (strlen ( $strTmp ) > 0) {
			$ret [] = $strTmp;
		}
		return $ret;
	}
	
	/**
	 * Get xml data of items and write it to a excel file with micro.
	 *
	 * @param string	$cfgSvrUriFrom  取得XML文件的URI，如：http://10.18.138.150:30001/getConfig/
	 * @param string	$cfgSvrUriTo    写入XML文件的URI，如：http://10.18.138.150:30001/saveConfig
	 * @param string	$fServerId      游戏服务器的ID 
	 * @param string	$filename       Xml文件名
	 * @param string	$fileType       提示用户下载是的文件格式，有：xlsm(默认), xlsx
	 * @return Excel File
	 */
	function downloadExcelWithDataForSwf($cfgSvrUriFrom, $cfgSvrUriTo, $fServerId, $filename) {
		try {
			
			//***************************************************************************************
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel ();
			
			//***************************************************************************************
			// Read from Excel2007 (.xlsx) template
			$objReader = PHPExcel_IOFactory::createReader ( 'Excel2007' );
			
			//$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load ( EXCEL_TEMPLATE );
			
			// set attributes of out excel file
			$objPHPExcel->getProperties ()->setCreator ( "Elex" )->setLastModifiedBy ( "wuyuqun" )->setTitle ( "Item data" )->setSubject ( "Item data generated from Elex" )->setDescription ( "Item data." );
			// get items data from XML file
			if (IsXingCloudServer) {
				$xmlContainer = new XmlContainer ( $cfgSvrUriFrom . $fServerId );
			}
			else{
				$xmlContainer = new XmlContainer ( $filename);
			}
			
			$xmlData = $xmlContainer->getDataNodes ();
			$xmlColumns = $xmlContainer->getDataColumns ();
			$md5OfFile = $xmlContainer->getMD5OfFile ();
			$docNamespaceS = $xmlContainer->getDocNamespace ();
			$dataNamespaceS = $xmlContainer->getDataNamespace ();
			// set root properties to custom properties in excel file
			foreach ( $xmlContainer->getRootProperties () as $key => $val ) {
				$objPHPExcel->getProperties ()->setCustomProperty ( $key, $val );
			}
			$protocol = $_SERVER ['HTTPS'] ? "https://" : "http://";
			$pathParts = parse_url ( ($_SERVER ['REQUEST_URI']), PHP_URL_PATH );
			// 写入自定义信息到Excel中
			$requestPageWithHost = $protocol . $_SERVER ['SERVER_NAME'] . ":" . $_SERVER ['SERVER_PORT'] . $pathParts;
			$requestPageWithIp = $protocol . $_SERVER ['SERVER_ADDR'] . ":" . $_SERVER ['SERVER_PORT'] . $pathParts;
			$objPHPExcel->getProperties ()->setCustomProperty ( SERVER_ADDRESS_WITH_HOST, $requestPageWithHost );
			$objPHPExcel->getProperties ()->setCustomProperty ( SERVER_ADDRESS_WITH_IP, $requestPageWithIp );
			$objPHPExcel->getProperties ()->setCustomProperty ( SAVE_ARG_SVR_URI, $cfgSvrUriTo );
			$objPHPExcel->getProperties ()->setCustomProperty ( SAVE_ARG_SVR_ID, $fServerId );
			$objPHPExcel->getProperties ()->setCustomProperty ( SAVE_ARG_FILEMD5, $md5OfFile );
			$objPHPExcel->getProperties ()->setCustomProperty ( SAVE_ARG_FILENAME, $filename );
			$objPHPExcel->getProperties ()->setCustomProperty ( DOWN_METHOD, DOWN_METHOD_SWF );
			$objPHPExcel->getProperties ()->setCustomProperty ( DOC_NAMESPACE, $docNamespaceS );
			$objPHPExcel->getProperties ()->setCustomProperty ( DATA_NAMESPACE, $dataNamespaceS );
			
			// remove first sheet in template file
			$objPHPExcel->removeSheetByIndex ( 0 );
			
			$invalideStr = array ("[", "]", "?", "\\", "*", ":", "'" );
			$sheetId = 0;
			foreach ( $xmlData as $groupId => $tables ) {
				// org sheet name
				$prefix = sprintf ( "%d", ($sheetId + 1) );
				$cutStringLength = 30 - strlen ( $prefix );
				$sheetName = str_replace ( $invalideStr, "", $groupId );
				$sheetName = str_replace ( "/", "|", $sheetName );
				$sheetName = $this->getMbString ( $sheetName, $cutStringLength, false );
				$sheetName = $prefix . '_' . $sheetName;
				if (strlen ( $sheetName ) < 0)
					continue;
				
				// xml data to array
				$keys = $xmlColumns [$groupId];
				
				// create sheet
				$curSheet = $objPHPExcel->createSheet ( $sheetId );
				$curSheet->setTitle ( $sheetName );
				
				// add name to hide range
				$maxNameLen = 250;
				if (strlen ( $groupId ) > $maxNameLen) {
					$splitVals = $this->getMbStringToArray ( $groupId, $maxNameLen, true );
					$i = 0;
					foreach ( $splitVals as $value ) {
						$namedRange = new PHPExcel_NamedRange ( "DataXpath" . $i, $curSheet, "A1", true, null, $value );
						$objPHPExcel->addNamedRange ( $namedRange );
						$i ++;
					}
				} else {
					$namedRange = new PHPExcel_NamedRange ( "DataXpath", $curSheet, "A1", true, null, $groupId );
					$objPHPExcel->addNamedRange ( $namedRange );
				}
				
				// add attributes' name to sheet
				$columnId = "A";
				$rowId = 1;
				foreach ( $keys as $k => $v ) {
					$curSheet->getStyle ( $columnId )->getNumberFormat ()->setFormatCode ( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
					$cellPos = $columnId . $rowId;
					$curSheet->getCell ( $cellPos )->setValueExplicit ( $v, PHPExcel_Cell_DataType::TYPE_STRING );
					$columnId ++;
				}
				// add attributes' value to sheet
				$rowId = 2;
				foreach ( $tables as $id => $attributs ) {
					$columnId = "A";
					foreach ( $xmlColumns [$groupId] as $columnName ) {
						$cellPos = $columnId . $rowId;
						$v = "";
						if (isset ( $attributs [$columnName] )) {
							$v = $attributs [$columnName];
						}
						$curSheet->getCell ( $cellPos )->setValueExplicit ( $v, PHPExcel_Cell_DataType::TYPE_STRING );
						$columnId ++;
					}
					$rowId ++;
				}
				
				$sheetId ++;
			}
			ob_clean ();
			header ( 'Pragma: cache' );
			header ( 'Cache-Control: public, must-revalidate, max-age=0' );
			header ( 'Content-Transfer-Encoding: binary' );
			header ( 'Accept-Ranges: bytes' );
//			switch (strtoupper ( $fileType )) {
//				case 'XLSM' :
//					// 以Xlsm的格式下载
					header ( 'Content-Type: application/vnd.ms-excel.sheet.macroEnabled.12' );
					header ( "Content-disposition: attachment; filename=XmlData.xlsm" );
					$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel2007' );
					$objWriter->setPreCalculateFormulas ( false );
					$objWriter->setOffice2003Compatibility ( true );
					$objWriter->save ( 'php://output', true );
//					break;
//				case 'XLSX' :
//					// 以Xlsx的格式下载
//					header ( 'Content-Type: application/vnd.ms-excel.sheet.macroEnabled.main+xml' );
//					header ( "Content-disposition: attachment; filename=XmlData.xlsx" );
//					$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel2007' );
//					$objWriter->setPreCalculateFormulas ( false );
//					$objWriter->setOffice2003Compatibility ( true );
//					$objWriter->save ( 'php://output', false );
//					break;
//				default :
//					// 以Xlsm的格式下载
//					header ( 'Content-Type: application/vnd.ms-excel.sheet.macroEnabled.12' );
//					header ( "Content-disposition: attachment; filename=XmlData.xlsm" );
//					$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel2007' );
//					$objWriter->setPreCalculateFormulas ( false );
//					$objWriter->setOffice2003Compatibility ( true );
//					$objWriter->save ( 'php://output', true );
//					break;
//			}
			exit ();
		} catch ( Exception $e ) {
			$this->writeTextData ( "error.log", $e->getMessage () );
			return false;
		}
	}
	
	function writeTextData($filename, $filedata) {
		
		$handle = null;
		
		if (! $handle = fopen ( $filename, 'a+' )) {
			echo "不能打开文件 $filename";
			exit ();
		}
		fwrite ( $handle, $filedata );
		fclose ( $handle );
	}

	function parserXpath($pXpath) {
		try {
			if ($pXpath) {
				$nodeAttributeCount = 0;
				$ret = array ();
				//database[ @name='item' and @package='farm.model']/group[ @name='type']
				$patternGroup = "/\/([a-zA-Z]+[^\/^\[]*)(\[([^\]]*)\])*/";
				preg_match_all ( $patternGroup, $pXpath, $nodePaths );
				
				if ($nodePaths && ! empty ( $nodePaths [1] )) {
					$nodeLayerCount = count ( $nodePaths [1] );
				}
				$fullXpath = "/";
				for($i = 0; $i < $nodeLayerCount; $i ++) {
					$nodeXpath = $nodePaths [0] [$i];
					$nodeName = $nodePaths [1] [$i];
					$attributesStr = $nodePaths [2] [$i];
					$fullXpath .= $nodeXpath;
					
					$xpathContainer = new XpathContainer ();
					$xpathContainer->parentXpath = null;
					$xpathContainer->fullXpath = stripcslashes ( $fullXpath );
					$xpathContainer->nodeName = $nodeName;
					
					$patternAttribute = "/@([a-zA-Z]+[^=]*)='(.[^']*)'/";
					preg_match_all ( $patternAttribute, $attributesStr, $attributesAry );
					
					if ($attributesAry && ! empty ( $attributesAry [1] )) {
						$nodeAttributeCount = count ( $attributesAry [1] );
					}
					for($j = 0; $j < $nodeAttributeCount; $j ++) {
						$key = $attributesAry [1] [$j];
						$value = $attributesAry [2] [$j];
						$xpathContainer->attributes [$key] = $value;
					}
					if ($i > 0) {
						$xpathContainer->parentXpath = $ret [$i - 1];
					}
					$ret [$i] = $xpathContainer;
				}
				return $ret;
			}
		} catch ( Exception $e ) {
		}
	}
	
	/**
	 * Write xml to server.
	 *
	 * @param string	$destination    写入XML文件的URI，如：http://10.18.138.140:30001/getConfig/
	 * @param string	$fServerId      游戏服务器的ID
	 * @param string	$checksum       当前xml文件的MD5码
	 * @param string	$filename       Xml文件名
	 * @param string	$fileContents   文件内容
	 * @return MD5
	 */
	function writeXmlToServer($postData, $rootProperties, $destination, $serverId, $filename, $md5OfFileOld, $docNamespace, $dataNamespace) {
		try {
			$existsRootNode = false;
			if ($rootProperties) {
				$rootAttributes = explode ( ATTRIBUTE_SPLITOR, $rootProperties );
				foreach ( $rootAttributes as $oneAttribute ) {
					$data = explode ( KEY_VALUE_SPLITOR, $oneAttribute );
					$rootAttributes4Xml [$data [0]] = $data [1];
					if ($data [0] == ROOT_NODE_NAME) {
						$existsRootNode = true;
					}
				}
			}
			if (! $existsRootNode) {
				exit ( '上传的数据不完整，或者是从非法的Excel文件中上传数据！' );
			}
			
			//创建一个文件
			$dom = new DOMDocument ( '1.0', 'UTF-8' );
			$dom->xmlStandalone = false;
			$dom->preserveWhiteSpace = FALSE;
			$dom->formatOutput = TRUE;
			
			//开始创建文档并设置属性
			$root = $dom->createElement ( $rootAttributes4Xml [ROOT_NODE_NAME] );
			//设置xml节点属性
			foreach ( $rootAttributes4Xml as $key => $val ) {
				if ($key != ROOT_NODE_NAME) {
					$root->setAttribute ( $key, $val );
				}
			}
			$dom->appendChild ( $root );
			//解析上传的数据的内容 
			$allSheetsData = explode ( SHEET_SPLITOR, $postData );
			
			foreach ( $allSheetsData as $oneSheetData ) {
				$data = explode ( DATA_SPLITOR, $oneSheetData );
				$xpath = stripcslashes ( $data [0] );
				$sheetData = $data [1];
				//把从Excel传回的该节点的Xpath解析成对象
				$xpathInfo = $this->parserXpath ( $xpath );
				
				
				//创建节点
				foreach ( $xpathInfo as $nodeXpath ) {
					if ($nodeXpath->fullXpath) {
						$domXpath = new DOMXPath ( $dom );
						$curXmlNode = $domXpath->query ( $nodeXpath->fullXpath );
						
						// 节点不存在则创建之
						if ($curXmlNode->length <= 0) {
							$childElement = $dom->createElement ( $nodeXpath->nodeName );
							foreach ( $nodeXpath->attributes as $name => $value ) {
								$childElement->setAttribute ( $name, $value );
							}
							if (empty ( $nodeXpath->parentXpath ) || (! $nodeXpath->parentXpath->fullXpath)) {
								$parentXmlNodeList = $domXpath->query ( "//*" );
							} else {
								$parentXmlNodeList = $domXpath->query ( $nodeXpath->parentXpath->fullXpath );
							}
							if ($parentXmlNodeList->length > 0) {
								$parentXmlNode = $parentXmlNodeList->item ( 0 );
								$parentXmlNode->appendChild ( $childElement );
								$curXmlNode = $domXpath->query ( $nodeXpath->fullXpath );
							}
						}
					}
				}
				//添加数据
				$rows = explode ( "\r\n", $sheetData );
				$rowId = 0;
				$columnName = array ();
				foreach ( $rows as $rowData ) {
					if (! $rowData) {
						continue;
					}
					$cellsData = explode ( "\t", $rowData );
					// 数据行
					if ($rowId > 0 && strlen ( trim ( strval ( $cellsData ) ) ) > 0) {
						$colId = 0;
						for($colId = 0; $colId < count ( $cellsData ); $colId ++) {
							$attributeValue = trim ( strval ( $cellsData [$colId] ) );
							$attributeName = trim ( strval ( $columnName [$colId] ) );
							// 第一列是元素名
							if ($colId == 0) {
								$dataElement = $dom->createElement ( $attributeValue );
							} // 其他列是数据,有数据才输入
							elseif (strlen ( $attributeValue ) > 0 && $attributeName) {
								$dataElement->setAttribute ( $attributeName, $attributeValue );
							}
						}
						$curXmlNode = $domXpath->query ( $nodeXpath->fullXpath );
						if ($colId > 0 && $curXmlNode->length > 0) {
							$curXmlNode->item ( 0 )->appendChild ( $dataElement );
						}
					} // 第一行是列名
					else {
						$columnName = $cellsData;
					}
					$rowId ++;
				}
			}
			//取得节点名称
			if ($dataNamespace) {
				$rootWithNS = $dom->createElement ( $dataNamespace . ":" . $rootAttributes4Xml [ROOT_NODE_NAME] );
			}
			else {
				$rootWithNS = $dom->createElement ( $rootAttributes4Xml [ROOT_NODE_NAME] );
			}
			//设置xml节点属性
			foreach ( $rootAttributes4Xml as $key => $val ) {
				if ($key != ROOT_NODE_NAME) {
					$rootWithNS->setAttribute ( $key, $val );
				}
			}
			//设置命名空间属性
			if ($docNamespace) {
				$docNamespaceA = json_decode($docNamespace ,  true);
				foreach ($docNamespaceA as $nsKey => $nsVal) {
					$rootWithNS->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:' . $nsKey, $nsVal);
				}
			}
			//把不带命名空间的节点下的所有子节点搬到新的根节点下
			$rootOld = $dom->documentElement;
			$domXpath = new DOMXPath ( $dom );
			$nodeListOfRoot = $domXpath->query ( "//" . $rootAttributes4Xml [ROOT_NODE_NAME] . "/*" );
			foreach ($nodeListOfRoot as $node) {
				$rootWithNS->appendChild($node);
			}
			//添加带命名空间的根节点
			$dom->appendChild ( $rootWithNS );
			$dom->removeChild($rootOld);
			
			// xml文本
			$xmlContents = $dom->saveXml ();
			
			if (IsXingCloudServer) {
				//创建临时xml文件
				$xmlFilePath = dirname(realpath(__FILE__))."/".$filename;
				$dom->save($xmlFilePath);
				
				// 生成Md5码
				$fileMD5New = md5 ( $xmlContents );
				
				// 检查xml文件是否已经被修改了
				if (isXmlModified ( $serverId, $md5OfFileOld )) {
					exit ( MSG_01 );
				}
				// 发送数据到服务器中
				$checksum = getChecksum ( $serverId, $xmlContents );
				$fileMD5Svr = $this->postFileToServer ( $destination, $serverId, $checksum, $xmlFilePath );
				
				//删除临时xml文件
				if (file_exists ( $xmlFilePath )) {
					unlink ( $xmlFilePath );
				}
				
				// debug用的代码
	//			echo "\$destination = $destination \n";
	//			echo "\$serverId = $serverId \n";
	//			echo "\$checksum = $checksum \n";
	//			echo "\$filename = $filename \n";
	//			@file_put_contents ( "./xml/temp.xml", $xmlContents );
	//			echo "\$fileMD5Svr = $fileMD5Svr\n";
	//			echo "\$fileMD5New = $fileMD5New\n";
				// 返回是否保存结果
				return ($fileMD5Svr == $fileMD5New);
			}
			else{
				return $dom->save($filename);
			}	
		} catch ( Exception $e ) {
			echo "error\n";
			$this->writeTextData ( "error.log", $e->getMessage () );
			return false;
		}
	}
	
	/**
	 * Write it to server.
	 *
	 * @param string	$destination    写入XML文件的URI，如：http://10.18.138.140:30001/saveConfig/
	 * @param string	$fServerId      游戏服务器的ID
	 * @param string	$checksum       当前xml文件的MD5码
	 * @param string	$filename       Xml文件名,绝对路径
	 * @return MD5
	 */
	function postFileToServer($destination, $serverId, $checksum, $filename) {
		//用curl将xml文件post给服务器
		$params = array ('f_server_id' => $serverId, 'checksum' => $checksum, 'configFile' => "@$filename");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $destination);
		curl_setopt($ch, CURLOPT_POST, true );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$response = curl_exec($ch);
		return $response;
	}
}
