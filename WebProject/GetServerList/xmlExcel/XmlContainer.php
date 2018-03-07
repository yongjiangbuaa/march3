<?php
require_once './Config.php';

/** 
 * 
 * @author wuyuqun
 * 
 * 
 */
class XmlContainer {
	/**
	 * Xml filename with full path.
	 *
	 * @var string
	 */
	private $_xmlFile;
	
	/**
	 * Contents of current xml file.
	 *
	 * @var string
	 */
	private $_fileContents;
	
	/**
	 * Document properties
	 *
	 * @var SimpleXMLElement[]
	 */
	private $_docProperties;
	
	/**
	 * Root properties
	 *
	 * @var SimpleXMLElement[]
	 */
	private $_rootProperties;
	
	/**
	 * Data nodes
	 *
	 * @var SimpleXMLElement[]
	 */
	private $_dataNodes;
	
	/**
	 * Columns of data table
	 *
	 * @var array
	 */
	private $_dataColumns = array();
	
	/**
	 * Data nodes' count
	 *
	 * @var long
	 */
	private $_dataNodesCount;
	
	/**
	 * namespaces in rootnode
	 *
	 * @var string
	 */
	private $_docNamespace ;
	
	/**
	 * namespace for all data
	 *
	 * @var string
	 */
	private $_dataNamespace;
	
	function __construct($xmlFile) {
		$this->_xmlFile = $xmlFile;
	}
	
	/**
	 * Get document's properties
	 *
	 * @return SimpleXMLElement[]
	 */
	public function getDocProperties()
	{
		return $this->_docProperties;
	}

	/**
	 * Set document's properties
	 *
	 * @param SimpleXMLElement[] $pValue
	 */
	public function setDocProperties($pValue)
	{
		$this->_docProperties = $pValue;
	}

	/**
	 * Get properties of root node
	 *
	 * @return SimpleXMLElement[]
	 */
	public function getRootProperties()
	{
		return $this->_rootProperties;
	}

	/**
	 * Set properties of root node
	 *
	 * @param SimpleXMLElement[] $pValue
	 */
	public function setRootProperties($pValue)
	{
		$this->_rootProperties = $pValue;
	}

	/**
	 * Get data nodes' count
	 *
	 * @return long
	 */
	public function getDataNodesCount()
	{
		return count($this->_dataNodes);
	}

	/**
	 * Get data nodes
	 *
	 * @return array
	 */
	public function getDataNodes()
	{
		if (empty($this->_dataNodes)) {
			$this->loadData($this->_xmlFile);
		}
		return $this->_dataNodes;
	}

	/**
	 * Get data columns
	 *
	 * @return array
	 */
	public function getDataColumns()
	{
		if (empty($this->_dataColumns)) {
			$this->loadData($this->_xmlFile);
		}
		return $this->_dataColumns;
	}

   /**
	* 生成校验用MD5码
	*
    * @param string	$serverId       游戏服务器的ID
	* @return 当前xml文件校验用的MD5码
	*/
	function getChecksum($serverId = "1") {
		if (!$this->_fileContents) {
			$this->_fileContents = @file_get_contents($this->_xmlFile);
		}
		$md5Code = getChecksum($serverId, $this->_fileContents);
		return $md5Code;
	}

   /**
	* 生成文件MD5码
	*
	* @return 当前xml文件的MD5码
	*/
	function getMD5OfFile() {
		if (!$this->_fileContents) {
			$this->_fileContents = @file_get_contents($this->_xmlFile) or die('unable to load xml file :' . $this->_xmlFile);
		}
		$md5Code = md5( $this->_fileContents);
		return $md5Code;
	}

   /**
	* 取得 XML 文档的命名空间
	*
	* @return  XML 文档的命名空间
	*/	function getDocNamespace() {
		if (!$this->_docNamespace) {
			$this->loadData();
		}
		return $this->_docNamespace;
	}

   /**
	* 取得XML 数据的命名空间
	*
	* @return XML 数据的命名空间
	*/
	function getDataNamespace() {
		if (!$this->_dataNamespace) {
			$this->loadData();
		}
		return $this->_dataNamespace;
	}
		
	/**
	 * Load xml file and parse the data to Excel.
	 *
	 * @param string
	 * @return array[key][]
	 */
	private function loadData($xmlfile = null)
	{
		$excelData = null;
		if ($xmlfile) {
			$this->_xmlFile = $xmlfile;
		}
		
		$doc = simplexml_load_file($this->_xmlFile) or die('unable to load xml file :' . $this->_xmlFile); 
		$dataAttributes = array();
		$dataAttributes =(array) $doc->attributes();
		$dataAttributes = $dataAttributes['@attributes'];
		$dataAttributes[ROOT_NODE_NAME]= $doc->getName();
		$docNamespaceA 	=	$doc->getDocNamespaces();
		$dataNamespaceA	=	$doc->getNamespaces();
		if (!empty($docNamespaceA)) {
			$this->_docNamespace = json_encode($docNamespaceA);
		}
		if (!empty($dataNamespaceA)) {
			$keys = array_keys($dataNamespaceA);
			$this->_dataNamespace = $keys[0];
		}		
		$this->setRootProperties($dataAttributes);
		$this->parseXmlData($doc, $this->_dataNodes , "/" );
		return TRUE;
	}
	
	// 取得xml末级节点的数据
	private function parseXmlData($node , &$dataNodes ,$xpath ) {
		$children      = $node->children();
		$childrenCount = count($children);
		$parentNode    = $node->xpath('parent::*');
	
		if ($childrenCount > 0) {
			$this->getXpath($node, $xpath);
			foreach ($children as $group) {
				$this->parseXmlData($group, $dataNodes ,$xpath);
			}
		}
		//数据节点
		else{
			// 数据的Key
			$dataKey = $xpath;
			$dataAttributes = array();
			$dataAttributes =(array) $node->attributes();
			$dataAttributes = $dataAttributes['@attributes'];
			
			if (!isset($dataAttributes[NODE_NAME])){
				$dataAttributes = array(NODE_NAME=>$node->getName()) + $dataAttributes;
			}
			
			$this->_dataNodes[$dataKey][] = $dataAttributes;
			
			foreach (array_keys($dataAttributes) as $attKey=>$attValue) {
				$this->_dataColumns[$dataKey][$attValue] = $attValue;
			} 
			
			$xpath = "";
		}
	}

	//取得该数据节点的Xpath
	private function getXpath($node , &$xpath) {
		if ($node ) {
			$xpath .= "/" . $node->getName();
			if ($node->attributes()) {
				$xpath .= "[";
				foreach ($node->attributes() as $k=>$v) {
					$xpath .= " @$k='$v' and";
				}
				$xpath = substr($xpath, 0, strlen($xpath) - 4);
				$xpath .= "]";
			}
		}
	}

	/**
	 * Load xml file and parse the data to dataNodes using Elex rule.
	 *
	 * @param string
	 */
	public function loadDataAsHtml($xmlfile = null)
	{
		if ($xmlfile) {
			$this->_xmlFile = $xmlfile;
		}
//		$this->_xmlFile = dirname (__FILE__) . "/item.xml";
		$doc = simplexml_load_file($this->_xmlFile) or die('unable to load xml file :' . $this->_xmlFile); 
		
		$this->setRootProperties($doc->attributes());
		header('Content-Type: text/html; charset=utf-8');
		$this->parseXmlData($doc, $this->_dataNodes , "/");
		foreach ($this->_dataNodes as $group=>$tables) {
			echo "<table border='1'>";
			echo "<tr>group name(id) is :" . $group . '</tr>';
			// 
			echo "<tr>";
			foreach ($this->_dataColumns[$group] as $columnName) {
				echo "<td>$columnName</td>";
		   	} 
		 	echo "</tr>";
		 	
			foreach ($tables as $id=>$attributs) { 
			 	echo "<tr>";
			   	foreach ($this->_dataColumns[$group] as $columnName) {
			   		$cellValue = isset($attributs[$columnName]) ? $attributs[$columnName] : "&nbsp&nbsp";
			   		echo "<td>$cellValue</td>";
			   	} 
			 	echo "</tr>";
		   	} 
		   	
			echo "</table> <br /> <br />";
		 	
		}
		echo "OK";
	}
}

?>