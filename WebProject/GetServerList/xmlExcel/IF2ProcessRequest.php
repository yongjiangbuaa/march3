<?php
require_once './Config.php';
require_once './XmlPhp.php';

defined("serverIp", "http://10.1.4.120/");
defined("serverPort", 8081);
ob_clean();

// 接受从Excel中发过来的请求
if($_SERVER["REQUEST_METHOD"]=="POST")
{ 
	$postData 		= empty($_REQUEST['PostData'])         ? null : urldecode($_REQUEST['PostData']);
	$rootProperties = empty($_REQUEST['RootProperty'])     ?  ''  : strval($_REQUEST['RootProperty']);
	$md5OfFileOld	= empty($_REQUEST[SAVE_ARG_FILEMD5])   ?  ''  : strval(urldecode($_REQUEST[SAVE_ARG_FILEMD5]));
	$destination  	= empty($_REQUEST[SAVE_ARG_SVR_URI])   ?  ''  : strval(urldecode($_REQUEST[SAVE_ARG_SVR_URI]));
	$serverId  		= empty($_REQUEST[SAVE_ARG_SVR_ID])    ?  ''  : intval(urldecode($_REQUEST[SAVE_ARG_SVR_ID]));
	$filename 		= empty($_REQUEST[SAVE_ARG_FILENAME])  ?  ''  : strval($_REQUEST[SAVE_ARG_FILENAME]);
	$downMothod 	= empty($_REQUEST[DOWN_METHOD])    	   ?  ''  : strval($_REQUEST[DOWN_METHOD]);
	$docNamespace 	= empty($_REQUEST[DOC_NAMESPACE])  	   ?  ''  : strval($_REQUEST[DOC_NAMESPACE]);
	$dataNamespace 	= empty($_REQUEST[DATA_NAMESPACE])     ?  ''  : strval($_REQUEST[DATA_NAMESPACE]);
	//判断有无Post数据过来、有无数据版本号
	if (!$postData || !$filename){
		echo "发送的数据内容不完整！";
	}
	else{ 
		$xmlPhp  = new XmlPhp($filename);
		$postData = str_replace('^o^url_param_splitor^o^', '&', $postData);
		$ret = $xmlPhp->writeXmlToServer($postData, $rootProperties, $destination, $serverId, $filename, $md5OfFileOld, $docNamespace, $dataNamespace);
		
		// 返回给VBA
		echo $ret ? "OK_" : "Failed";
	}
	exit();
}
// 接受从Web中发过来的请求
else{
	$filename  		= empty($_REQUEST['filename']) 		?  'item.xml' : strval($_REQUEST['filename']);
	$FServer		= empty($_REQUEST['testserver']) 	?  '150' : strval($_REQUEST['testserver']);
	$fServerId  	= empty($_REQUEST['fserverid']) 	?  41 : strval($_REQUEST['fserverid']);
	if (IsXingCloudServer) {
		$cfgSvrUriFrom 	= "http://10.18.138.".$FServer.":30001/getConfig/";
		$cfgSvrUriTo   	= "http://10.18.138.".$FServer.":30001/saveConfig";
		$realFilename 	= $filename;
		echo $cfgSvrUriFrom."<br>";
	}
	else{
		$cfgSvrUriFrom 	= "";
		$cfgSvrUriTo   	= "";
		//$realFilename = '/IF/trunk/src/server/smartfoxserver/SFS2X/resource/'.$filename;
		$realFilename = '/usr/local/cok/SFS2X/resource/'.$filename;
		if (!file_exists($realFilename)) {
			echo "cannt open xml file, filename is [$filename]\n";
		}
		echo $realFilename."<br>";
	} 
	
	$xmlPhp  = new XmlPhp($filename);
	$xmlPhp->downloadExcelWithDataForSwf($cfgSvrUriFrom, $cfgSvrUriTo, $fServerId, $realFilename);
	$xmlPhp = null;
}
?>
