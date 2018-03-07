<?php
/**
 * 
 * 文件APC共通方法
 * @author zhaohf
 *
 */
class FileApcManager {
	private $cachedData = array ();
	private static $apcCacheObj = null;
	
	private $dataKey = '';
	private $prefix = '';

	public function __construct($Prefix = "Ik2") {
		if(self::$apcCacheObj == null){
			import('util.cache.APCCache');
			self::$apcCacheObj = new APCCache ();
		}
		if (self::$apcCacheObj) {
			$this->prefix = sprintf ( "%s_", $Prefix );
			self::$apcCacheObj->setPrefix ( $this->prefix );
			$this->cachedData = $this->getCachedData ();
		}
	}
	public function getChildJsonData($FileFullname, $childName){
		$ret = null;
		if ($FileFullname) {
			$this->dataKey = sprintf ( "%s_%s", md5 ( $FileFullname ), basename ( $FileFullname ) );
			//key是正确的
			if ($this->dataKey) {
				// 该对应的数据存在
				if ($this->cachedData && isset ( $this->cachedData [$this->dataKey] )) {
					$dataLastModifiedTime = $this->cachedData [$this->dataKey]['last_modify_time'];
					if (file_exists ( $FileFullname )) {
						$dataCurModifiedTime = filemtime ( $FileFullname );
						// 数据是旧的则更新
						if ($dataCurModifiedTime > $dataLastModifiedTime) {
							$jsonText = @file_get_contents ( $FileFullname ) or die("can not open $FileFullname");
							$jsonData = json_decode ( $jsonText, true );
							self::$apcCacheObj->set ( $this->dataKey, $jsonData );
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
						//$ret = self::$apcCacheObj->get ( $this->dataKey );
					}
				} else {
					// APC中不存在缓存数据的情况
					if (file_exists ( $FileFullname )) {
						$dataCurModifiedTime = filemtime ( $FileFullname );
						$jsonText = @file_get_contents ( $FileFullname );
						$jsonData = json_decode ( $jsonText, true );
						if (self::$apcCacheObj->set ( $this->dataKey, $jsonData )) {
							//$ret = $jsonData;
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
					}
				}
				if(isset($jsonData) && !empty($jsonData)){
					$ret = $jsonData[$childName];
					self::$apcCacheObj->set ( $this->dataKey."_".$childName, $ret);
				}else{
					$ret = self::$apcCacheObj->get ( $this->dataKey."_".$childName);
					//如果为空，则插入新的数据
					if(empty($ret)){
						$allData = isset($jsonData) ? $jsonData : self::$apcCacheObj->get ( $this->dataKey );
						$ret = $allData[$childName];
						self::$apcCacheObj->set ( $this->dataKey."_".$childName, $ret);
					}
				}
				
			}
		}
		
		return $ret;
	}
	public function getJsonFileData($FileFullname) {
		$ret = null;
		if ($FileFullname) {
			$this->dataKey = sprintf ( "%s_%s", md5 ( $FileFullname ), basename ( $FileFullname ) );
			//key是正确的
			if ($this->dataKey) {
				// 该对应的数据存在
				if ($this->cachedData && isset ( $this->cachedData [$this->dataKey] )) {
					$dataLastModifiedTime = $this->cachedData [$this->dataKey]['last_modify_time'];
					if (file_exists ( $FileFullname )) {
						$dataCurModifiedTime = filemtime ( $FileFullname );
						// 数据是旧的则更新
						if ($dataCurModifiedTime > $dataLastModifiedTime) {
							$jsonText = @file_get_contents ( $FileFullname ) or die("can not open $FileFullname");
							$jsonData = get_object_vars(json_decode ( $jsonText ));
							self::$apcCacheObj->set ( $this->dataKey, $jsonData );
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
						$ret = self::$apcCacheObj->get ( $this->dataKey );
					}
				} else {
					// APC中不存在缓存数据的情况
					if (file_exists ( $FileFullname )) {
						$dataCurModifiedTime = filemtime ( $FileFullname );
						$jsonText = @file_get_contents ( $FileFullname );
						$jsonData = get_object_vars(json_decode ( $jsonText ));
// 						print_r($jsonData,true);
						if (self::$apcCacheObj->set ( $this->dataKey, $jsonData )) {
							$ret = $jsonData;
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
					}
				}
			}
		}
		
		return $ret;
	}
	
	public function getXmlTextToArray($FileFullname,$XPath=''){
		if (file_exists ( $FileFullname )) {
			mb_internal_encoding ( "UTF-8" );
			$xmlDom = @simplexml_load_file ( $FileFullname ) or die("can not open $FileFullname");
			return json_decode(json_encode($xmlDom),true);
		}
		return null;
	}

	public function getXmlFileContent($FileFullname) {
		$ret = null;
		if ($FileFullname) {
			$this->dataKey = sprintf ( "%s_%s", md5 ( $FileFullname ), basename ( $FileFullname ) );
			//key是正确的
			if ($this->dataKey) {
				// 该对应的数据存在
				if ($this->cachedData && isset ( $this->cachedData [$this->dataKey] )) {
					$dataLastModifiedTime = $this->cachedData [$this->dataKey]['last_modify_time'];
					if (file_exists ( $FileFullname )) {
						$dataCurModifiedTime = filemtime ( $FileFullname );
						// 数据是旧的则更新
						if ($dataCurModifiedTime > $dataLastModifiedTime) {
							$xmlText = @file_get_contents ( $FileFullname ) or die("can not open $FileFullname");
							self::$apcCacheObj->set ( $this->dataKey, $xmlText );
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
					}
				} else {
					// APC中不存在缓存数据的情况
					if (file_exists ( $FileFullname )) {
						$xmlText = @file_get_contents ( $FileFullname ) or die("can not open $FileFullname");
						if (self::$apcCacheObj->set ( $this->dataKey, $xmlText )) {
							$this->cachedData [$this->dataKey] = array ('last_modify_time' => $dataCurModifiedTime, 'filename' => $FileFullname );
							$this->setCachedData ();
						}
					}
				}
				$ret = self::$apcCacheObj->get ( $this->dataKey );
			}
		}
		
		return $ret;
	}
	
	private function setCachedData() {
		self::$apcCacheObj->set ( 'cachedData', $this->cachedData );
	}
	
	private function getCachedData() {
		return self::$apcCacheObj->get ( 'cachedData' );
	}
}
?>
