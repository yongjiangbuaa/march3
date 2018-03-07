<?php
define("ITEM_XML", "Item.xml");
define("SHEET_SPLITOR", "^Q^_SHEET_^O^");
define("DATA_SPLITOR", "^Q^_DATA_^O^");
define("ATTRIBUTE_SPLITOR", "^_ATT_^");
define("KEY_VALUE_SPLITOR", "^_KV_^");
//xml数据定义相关
define("ROOT_NODE_NAME", "Root_Node_Name");  
define("DOC_NAMESPACE", "DocNamespace");
define("DATA_NAMESPACE", "DataNamespace");
define("SERVER_ADDRESS_WITH_HOST", "SvrAdrWithHost"); 
define("SERVER_ADDRESS_WITH_IP", "SvrAddWithIP"); 

define("XML_PATH", "./xml/");
define("ITEM_XML_DATA", "./xml/Item.xml");
define("EXCEL_TEMPLATE", "./xlsmTemplate/XmlTools.xlsm"); 

define("NODE_NAME", "NodeName");

// xml保存相关的关键字
//define("XML_SAVE_URL", "http://119.254.245.120:30001/saveConfig");
//define("XML_MD5_QUERY_URL", "http://119.254.245.120:30001/queryConfigIfModified/");

define("XML_SAVE_URL", "http://10.18.138.150:30001/saveConfig");
define("XML_MD5_QUERY_URL", "http://10.18.138.150:30001/queryConfigIfModified/");

define("API_KEY", "174291103011893651EGV23");
define("SAVE_ARG_SVR_URI", "cfgSvrUriTo");
define("SAVE_ARG_SVR_ID", "fServerId");
define("SAVE_ARG_FILEMD5", "md5offile");
define("SAVE_ARG_FILENAME", "filename");



// 下载的区分
define("DOWN_METHOD", "downloadFrom");
define("DOWN_METHOD_IE", "IE");
define("DOWN_METHOD_SWF", "SWF");

// 提示消息 
define("MSG_01", "文件内容已经被修改，请重新下载新版本！");

/**
* 生成MD码
*
* @param string	$serverId       游戏服务器的ID
* @param string	$fileContents   文本
* @return 当前xml文件的MD5码
*/
function getChecksum($serverId , $fileContents) {
	$md5Code = md5($serverId . API_KEY . $fileContents);
	return $md5Code;
}

 
/**
* 确认文件是否被修改过
*
* @param string	$serverId       游戏服务器的ID
* @param string	$md5			MD码
* @return bool  true :已经被修改 
*               false:未被修改
*/
function isXmlModified($serverId , $md5codeOfFile) {
	$modified = @file_get_contents(XML_MD5_QUERY_URL . $serverId . "/" . $md5codeOfFile);
	return strtolower($modified) == "true" ? true : false;
}
?>