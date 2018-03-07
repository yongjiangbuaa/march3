<?php
	define('SCRIPTROOT', realpath(dirname(__FILE__) . '/'));
	include SCRIPTROOT.'/util/config.php';
	include SCRIPTROOT.'/util/lib/SnsNetwork.php';
	include SCRIPTROOT.'/util/lib/DBUtil.php';
	
	$path = SCRIPTROOT.'/httplog';
	

	class payment
	{
		
		/**
		 * 初始化
		 */
		private static $instance = null;
		
		public static function singleton(){
			if(!self::$instance){
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		private $payDB;
		private $globalDB;
		function __construct(){
			$this->connectPay();
		}
		
		private function connectedPay(){
			return $this->payDB != null;
		}

		private function connectPay(){
			if($this->connectedPay())
				return;
//			$dbParam = array(
//					'mysql_host'=>'10.1.16.211',
//					'mysql_port'=>'3306',
//					'mysql_user'=>'cok',
//					'mysql_passwd'=>'1234567',
//					'mysql_db'=>'cokdb_pay',
//			);
 			$dbParam = array(
 					'mysql_host'=>'10.82.60.173',
 					'mysql_port'=>'3306',
 					'mysql_user'=>'gow',
 					'mysql_passwd'=>'ZPV48MZH6q9V8oVNtu',
 					'mysql_db'=>'cokdb_pay',
 			);
			$this->payDB = DBUtil::newInstance()->init($dbParam);
		}
		
		private function connectedGlobal(){
			return $this->globalDB != null;
		}

		private function connectGlobal(){
			if($this->connectedGlobal())
				return;
//			$dbParam = array(
//					'mysql_host'=>'10.1.16.211',
//					'mysql_port'=>'3306',
//					'mysql_user'=>'cok',
//					'mysql_passwd'=>'1234567',
//					'mysql_db'=>'cokdb_global',
//			);
 			$dbParam = array(
 					'mysql_host'=>'10.82.60.173',
 					'mysql_port'=>'3066',
 					'mysql_user'=>'gow',
 					'mysql_passwd'=>'ZPV48MZH6q9V8oVNtu',
 					'mysql_db'=>'cokdb_global',
 			);
			$this->globalDB = DBUtil::newInstance()->init($dbParam);
		}
		
		public function closeDB(){
			if($this->connectedPay())
				$this->payDB->disconnect();
			if($this->connectedGlobal())
				$this->globalDB->disconnect();
		}
		
		
		/**
		 * 业务逻辑
		 * 
		 * 新增接口需要加4个地方
		 * 1、isSuccess	判断成功的返回值
		 * 2、getOrderInfo	重新回调传入服务器端的orderId orderInfo
		 * 3、getHttpParam	重新回调生成的额外参数
		 */
		
		public function insertDataFromLog(){
			$path = SCRIPTROOT.'/sfslog';
			$redocallback = true;
			$row = 0;
			$sqlParams = array();
			$sql = "insert ignore into payrecord (`uid`,`orderId`,`method`,`request`,`postParam`,`result`,`time`) values ";
			if (is_dir($path) && is_readable($path)){
				$allfiles = scandir($path);
				foreach ($allfiles as $file) {
					if (in_array($file,array('.','..')))
						continue;
					$filePath = $path.'/'.$file;
					if (is_file($filePath)) {
						$handle = fopen($filePath, 'r');
						$lineArray = 0;
						while(!feof($handle)){
							$lineData = fgets($handle);
							$lineArray = explode('PaymentCallbackRecord ', $lineData);
							$payrecord = json_decode($lineArray[1],true);
							$orderId = '';
							$cpOrderId = '';
							$realOrderId = '';
							$this->getOrderInfo($payrecord, $orderId, $cpOrderId, $realOrderId);
							$orderInfo = explode('_',$cpOrderId);
							if($orderId){
								$sqlParams[] = 	$orderInfo[0];
								$sqlParams[] = $orderId;
								$sqlParams[] = $payrecord['method'];
								$sqlParams[] = $payrecord['request'];
								$sqlParams[] = $payrecord['postParam'];
								$sqlParams[] = $payrecord['result'];
								$sqlParams[] = floor(microtime(true)*1000);
								$row++;
								if($row > 200){
									$this->payDB->execute($sql.implode(",", array_pad(array(),$row,"(?,?,?,?,?,?,?)")), $sqlParams);
									$row = 0;
									$sqlParams = array();
								}
							}
						}
					}
				}
			}
			if($row > 0){
				$this->payDB->execute($sql.implode(",", array_pad(array(),$row,"(?,?,?,?,?,?,?)")), $sqlParams);
			}
		}
		
		public function doFirstCheck(){
			$payrecords = $this->payDB->fetchAll("select * from payrecord where checkTimes = 0 order by time asc limit 500", array());
			foreach ($payrecords as $payrecord){
				$payrecord['checkHistory'] = array();
				$payrecord['checkTimes'] = 1;
				if($this->isSuccess($payrecord['method'], $payrecord['result'])){
					$payrecord['checkResult'] = 1; 
				}
				$this->updateRow($payrecord);
			}
		}
		
		private function isSuccess($method,$result){
			$successList = array();
			$requestResult = json_decode($result,true);
			switch ($method){
				case '/cn_mi':
					$successList = array('200');
					$callbackCode = $requestResult['errcode'];
					break;
				case '/cn_xg':
					$successList = array('0','1');
					$callbackCode = $requestResult['code'];
					break;
				case '/cn_uc':
					$successList = array('SUCCESS');
					$callbackCode = trim($result);
					break;
				case '/cn_baidu':
					$successList = array('1');
					$callbackCode = $requestResult['ResultCode'];
					break;
				case '/cn_360':
					$successList = array('ok');
					$callbackCode = trim($result);
					break;
				case '/gash':
					$successList = array('success');
					$callbackCode = trim($result);
					break;
				case '/mycardbilling':
				case '/mycardingame':
				case '/mycardpoints':
					$successList = array('success');
					$callbackCode = trim($result);
					break;
				default:
					return false;
			}
			return in_array($callbackCode, $successList);
		}
		
		private function getOrderInfo($payrecord, &$orderId, &$cpOrderId, &$realOrderId){
			$requestParam = json_decode($payrecord['request'],true);
			$requestPostParam = json_decode($payrecord['postParam'],true);
			switch ($payrecord['method']){
				case '/cn_mi':
					$orderId = $requestParam['cpOrderId'];
					$cpOrderId = $requestParam['cpOrderId'];
					$realOrderId = $requestParam['orderId'];
					break;
				case '/cn_xg':
					$orderId = $requestParam['orderId'];
					$cpOrderId = $requestParam['custom'];
					$realOrderId = $requestParam['orderId'];
					break;
				case '/cn_uc':
					$orderId = $requestPostParam['data']['orderId'];
					$cpOrderId = $requestPostParam['data']['callbackInfo'];
					$realOrderId = $requestPostParam['data']['orderId'];
					break;
				case '/cn_baidu':
					$orderId = $requestParam['CooperatorOrderSerial'];
					$cpOrderId = $requestParam['CooperatorOrderSerial'];
					$realOrderId = $requestParam['OrderSerial'];
					break;
				case '/cn_360':
					$orderId = $requestParam['order_id'];
					$cpOrderId = $requestParam['app_order_id'];
					$realOrderId = $requestParam['order_id'];
					break;
				case '/gash':
					$orderId = $requestParam['tradeid'];
					$cpOrderId = $requestParam['orderInfo'];
					$realOrderId = $requestParam['tradeid'];
					break;
				case '/mycardingame':
					$orderId = $requestParam['tradeseq'];
					$cpOrderId = $requestParam['gameUid'];
					$realOrderId = $requestParam['tradeseq'];
					break;
				case '/mycardbilling':
				case '/mycardpoints':
					$orderId = $requestParam['authCode'];
					$cpOrderId = $requestParam['orderInfo'];
					$realOrderId = $requestParam['authCode'];
					break;	
				default:
					break;
			}
		}
		
		private function getHttpParam($payrecord, &$httpParam){
			$requestParam = json_decode($payrecord['request'],true);
			$requestPostParam = json_decode($payrecord['postParam'],true);
			switch ($payrecord['method']){
				case '/cn_mi':
					$httpParam['mi_uid'] = $requestParam['uid'];
					$httpParam['channel'] = $requestParam['channel'];
					$httpParam['data'] = base64_encode(json_encode($requestParam));
					break;
				case '/cn_xg':
					$httpParam['data'] = base64_encode(json_encode($requestParam));
					break;
				case '/cn_uc':
					$httpParam["sign"] = $requestPostParam['sign'];
					$httpParam["data"] = json_encode($requestPostParam['data']);
					break;
				case '/cn_baidu':
					break;
				case '/cn_360':
					$httpParam["data"] = json_encode($requestParam);
					break;
			}
		}
		
		public function getSumRecords($startDate,$endDate,$onlyFail = false){
			$start  = strtotime($startDate)*1000;
			$end  = strtotime($endDate)*1000;
			$sumSql = "select count(1) sum from payrecord where time > $start and time < $end and checkTimes > 0";
			if($onlyFail)
				$sumSql .= ' and checkResult = 0';
			$totalResult = $this->payDB->fetchOne($sumSql, array());
			$total = $totalResult['sum'];
			return $total;
		}
		
		public function getPayRecords($startDate,$endDate,$pageIndex,$pageLimit,$onlyFail = false){
			$start  = strtotime($startDate)*1000;
			$end  = strtotime($endDate)*1000;
			$sql = "select * from payrecord where time > $start and time < $end and checkTimes > 0";
			if($onlyFail)
				$sql .= ' and checkResult = 0';
			$sql .= ' order by time desc';
			$sql .= " limit $pageIndex,$pageLimit";
			$payrecords = $this->payDB->fetchAll($sql, array());
			foreach ($payrecords as $key=>$payrecord){
				$orderId = '';
				$orderInfo = '';
				$realOrderId = '';
				$this->getOrderInfo($payrecord, $orderId, $orderInfo, $realOrderId);
				$payrecords[$key]['orderInfo'] = $orderInfo;
				$payrecords[$key]['realOrderId'] = $realOrderId;
			}
			return $payrecords;
		}
		
		public function autoRedoCallback(){
			$start = strtotime(date('Y-m-d'))*1000 - 2*86400*1000;
			$end  = floor(microtime(true)*1000) - 120*1000;
			//单次最多执行100条
			$sql = "select * from payrecord where time > $start and time < $end and checkTimes > 0 and checkTimes < 5 and checkResult = 0 order by time asc limit 100";
			$payrecords = $this->payDB->fetchAll($sql, array());
			foreach ($payrecords as $payrecord){
				$callResult = $this->getNewResultFomServer($payrecord,'autocallback');
			}
		}
		
		public function redoCallback($uid, $orderId, $method, $logName='manualcallback'){
			$sql = "select * from payrecord where uid = ? and orderId = ? and method = ?";
			$sqlParams = array($uid,$orderId,$method	);
			$payrecord = $this->payDB->fetchOne($sql, $sqlParams);
			$msg = '';
			if($payrecord){
				return $this->getNewResultFomServer($payrecord,$logName);
			}else{
				$msg = "数据读取异常";
			}
			return array(0,$msg);
		}
		
		public function showDetail($uid, $orderId, $method){
			$sql = "select * from payrecord where uid = ? and orderId = ? and method = ?";
			$sqlParams = array($uid,$orderId,$method	);
			$payrecord = $this->payDB->fetchOne($sql, $sqlParams);
			return $payrecord;
		}
		
		public function runManual($uid, $orderId, $method){
			$sql = "update payrecord set checkResult = 2 where uid = ? and orderId = ? and method = ?";
			$sqlParams = array($uid,$orderId,$method	);
			$this->payDB->execute($sql, $sqlParams);
		}
		
		public function getNewResultFomServer($payrecord,$logName){
			$retObj = array();
			$requestMethod = $payrecord['method'];
			if($payrecord['checkHistory'])
				$history = json_decode($payrecord['checkHistory'],true);
			else
				$history = array();
			$successList = array();
			$callbackCode = '';
			$orderId = '';
			$cpOrderId = '';
			$realOrderId = '';
			$httpParam = array();
			$this->getOrderInfo($payrecord, $orderId, $cpOrderId, $realOrderId);
			$this->getHttpParam($payrecord, $httpParam);
			$checkResult = 'default result';
			if(!$orderId){
				$checkResult = 'no orderId';
			}else if($this->isSuccess($requestMethod, $payrecord['result'])){
				$checkResult = 'already successed';
				$payrecord['checkResult'] = 1;
			}else if($payrecord['checkResult'] == 1){
				$checkResult = 'already done';
			}else{
				$httpParam['orderId'] = $orderId;
				$orderInfo = explode('_',$cpOrderId);
				$gameUid = $orderInfo[0];
				$httpParam['uid'] = $gameUid;
				// 			$this->connectGlobal();
				$accountInfo = cobar_getAccountInfoByGameuids($gameUid);
				$server = $accountInfo[0]['server'];
				// 			$server = $orderInfo[1];
				if($server){
					$url = "http://s$server.coq.elexapp.com:8080/gameservice/paymentsendgoods$requestMethod";
					$httpResult = SnsNetwork::makeRequest($url, $httpParam, array(), 'get');
					if($this->isSuccess($requestMethod,$httpResult)){
						$payrecord['checkResult'] = 1;
					}
					$checkResult = substr($httpResult, 0, 500);
				}else{
					$checkResult = "user $gameUid not found";
				}
			}
			$history[] = array(
					'time'=>time(),
					'date'=>date('Y-m-d H:i:s',time()),
					'result'=>$checkResult,
			);
			if(count($history) > 10)
				array_shift($history);
			$payrecord['checkHistory'] = $history;
			$payrecord['checkTimes']++;
			$this->updateRow($payrecord);
			$log = array(
					$payrecord['uid'],
					$payrecord['orderId'],
					$payrecord['method'],
					$payrecord['checkResult'],
					$checkResult,
			);
			$logName .= date('Y-m-d');
			writeLog($logName,json_encode($log));
			return array($payrecord['checkResult'], $checkResult);
		}
		
		private function updateRow($payrecord){
			$sqlParams = array(
					'checkTimes'=>$payrecord['checkTimes'],
					'checkHistory'=>json_encode($payrecord['checkHistory']),
					'checkResult'=>$payrecord['checkResult'],
					'uid'=>$payrecord['uid'],
					'orderId'=>$payrecord['orderId'],
					'method'=>$payrecord['method'],
			);
			$sql = "update payrecord set checkTimes = :checkTimes, checkHistory = :checkHistory, checkResult = :checkResult where uid = :uid and orderId = :orderId and method = :method";
			$this->payDB->execute($sql, $sqlParams);
		}
		
		public function insertFiveData($sumData){
			$sqlParams = array();
			$sql = "replace into payfive (`date`,`pf`,`country`,`paysum`,`paytimes`) values ";
			$row = 0;
			foreach ($sumData as $date=>$dateData){
				foreach ($dateData as $pf=>$pfData){
					foreach ($pfData as $country=>$countryData){
						$sqlParams[] = $date;
						$sqlParams[] = $pf;
						$sqlParams[] = $country;
						$sqlParams[] = $countryData['paysum'];
						$sqlParams[] = $countryData['paytimes'];
						$row++;
						if($row > 1000){
							$this->payDB->execute($sql.implode(",", array_pad(array(),$row,"(?,?,?,?,?)")), $sqlParams);
							$row = 0;
							$sqlParams = array();
						}
					}
				}
			}
			if($row > 0){
				$this->payDB->execute($sql.implode(",", array_pad(array(),$row,"(?,?,?,?,?)")), $sqlParams);
			}
		}
		
		public function getFiveData($sql){
			return $this->payDB->fetchAll($sql, array());
		}
		
	}
	
?>