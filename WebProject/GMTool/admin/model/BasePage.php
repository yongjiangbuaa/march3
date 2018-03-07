<?php 

//
// 页面基类
//

class BasePage
{
	
	const SERVICE_NAME = 'AdminService';
	
	// 此方法由派生类实现
	public function display( $param=array() )
	{
		
	}
	//请求进行加密
	private function buildBeforePost($url,$p = array(),$timeOut = 300,$original = false){
		$p['id'] = 1;
		$p['format'] = 'json';
		//根据当前时间进行一次加密算法
		$user = 'ik2Gm';
		$time = time();
		$sig = $this->generate_password(11,$time);
		$pass = md5('99ba012643db55506bac61483e1b38f0'.$sig);
		if(!$p['lang'])
			$p['lang'] = 'cn';
		$p['check'] = array('time'=>$time,'user'=>$user,'pass'=>$pass);//7649c5275c855a9b
		$p['slaveDB'] = !$p['mainDB'];
// 		$p['mainDB'] = true;//使用实际库
// 		$p['slaveDB'] = true;//使用热备库
// 	$p = array('id'=>1,'info'=>array("platformAppId"=>"","platformUserId"=>""),'data'=>$param,'format'=>'json');
		return $this->post_request($url,$p,$timeOut,$original);
	}
	private function getSqlType($type){
		switch ($type) {
			case 1://查询，自备limit
			case 2://修改  在最后会默认读取主库,即使不写maindb
				return 5;
				break;
			case 3://查询，无limit限制
				return 11;
			case 4://多条sql依次执行
				return 13;
			case 5://查询所有表
				return 9;
			case 6://从缓存中取数据
				return 17;
			default:
				return 5;
				break;
		}
	}
	public function globalExecute($sql,$type,$mainDB = false){
		return $this->executeServer('global', $sql, $type, $mainDB);
	}

	private function getRedisInfo($server = null){
		$info = array();
		$info['port'] = 6379;
		if($server === 'global'){
			$info['host'] = GLOBAL_REDIS_SERVER_IP;
		}else{
			$appCfg = $this->getAppCfg();
			$info['host'] = $appCfg['ip_inner'];
			if(!empty($appCfg['redis_port'])){
				$info['port'] = $appCfg['redis_port'];
			}
		}
		return $info;
	}

	public function getMySQLInfo($mainDB = false, $server = null){
		if($server === 'global'){
			if($mainDB){
				$host_info['host'] = GLOBAL_DB_SERVER_IP;
			}else{
				$host_info['host'] = GLOBAL_DB_SLAVE_IP;
			}
			$host_info['host'] = '127.0.0.1';
			$host_info['port'] = 3306; 
			$host_info['db'] = 'march_global'; 
			$host_info['user'] = 'march';
			$host_info['password'] = 'hdli54T5P';
		}else {
			if (!empty($server)) {
				$appid = $server;
			} else {
				$appid = $this->getAppId();
			}
			$sid = 1;//substr($appid, 1);
/**
			$db_info = get_db_info($sid);
			$host_info = array();
			if ($mainDB) {
				$host_info['host'] = $db_info['ip_inner'];
			} else {
				$host_info['host'] = $db_info['slave_ip_inner'];
			}
**/
			$host_info['host'] = '127.0.0.1';
			$host_info['port'] = 3306; 
			$host_info['db'] = 'march'; 
			$host_info['user'] = 'march';
			$host_info['password'] = 'hdli54T5P';
		}

		return $host_info;
	}

	public function getRedisClient($server = null){
		if($server === 'local')
		{
			$redisConfig['host'] = '127.0.0.1';
			$redisConfig['port'] = 6379;

		}else
		{
			$redisConfig = $this->getRedisInfo($server);
		}

		$redis = new Redis();
		$re = $redis->connect($redisConfig['host'], $redisConfig['port'], 3);
		if(!$re){
			$this->addLog(1, array('config' => $redisConfig, 'error' => 'connnect fail'), $server);
		}
		return $redis;
	}

	private function executeRedis($type, $key ,$index = null, $server = null){
		$redis = $this->getRedisClient($server);
		$data = array();
		switch ($type){
			case 0://获得所有键名
				$data = $redis->keys('*');
				break;
			case 1://HGETAll
				$data = $redis->hGetAll($key);
				break;
			case 2: //HGETAll 批量
				foreach ($key as $k) {
					$temp = $redis->hGetAll($k);
					if($temp)
						$data[$k] = $temp;
				}
				break;
			case 3://hKeys
				$data = $redis->hKeys($key);
				break;
			case 4: //hGet
				$data = $redis->hGet($key, $index);
				break;
			case 5: //
				$data = $redis->hMset($key,$index);
				break;
			case 6://
				$data = $redis->hDel($key,$index);
				break;
			case 7://
				$data = $redis->get($key);
				break;
			case 8://
				$data = $redis->set($key,$index);
				break;
			case 9://
				$data = $redis->del($key);
				break;
			case 10://
				$data = $redis->keys($key.'*');
				break;
			case 11://
				$data = $redis->sAdd($key,$index);
				break;
			case 12://publish 消息
				$data = $redis->publish($key, $index);
				break;
			case 13:
				$data = $redis->lPush($key,$index);
				break;
			case 14:
				$data = $redis->rPop($key);
				break;
		}
		$redis->close();
		return $data;
	}

	public function execute($sql,$type,$mainDB = false){
			file_put_contents('/tmp/loginhis.log', 'sql='.$sql."\n",FILE_APPEND);
		if($type == 2 || $type == null){
			$mainDB = true;
		}
		$mysql_info = $this->getMySQLInfo($mainDB);
		$result = query_from_db($mysql_info,$sql);

		return array('ret' => array('data' => $result ));
//		$param = array(
//			'changes'=>null,
//			'params'=>array(
//				'type' => $this->getSqlType($type),
//				'sql' => $sql,
//			)
//		);
//		$sendParam = array('info'=>'','data'=>$param,'mainDB'=>$mainDB?1:0);
//		return $this->call('gm/gm/Mysql', $sendParam );
	}
	public function executeServer($server,$sql,$type,$mainDB = false){
		if($type == 2 || $type == null){
			$mainDB = true;
		}
		$mysql_info = $this->getMySQLInfo($mainDB, $server);
		$result = query_from_db($mysql_info, $sql);
		return array('ret' => array('data' => $result ));
//		$param = array(
//			'changes'=>null,
//			'params'=>array(
//				'type' => $this->getSqlType($type),
//				'sql' => $sql,
//			)
//		);
//		$sendParam = array('info'=>'','data'=>$param,'mainDB'=>$mainDB?1:0);
//		return $this->callByServer($server,'gm/gm/Mysql', $sendParam );
	}
	public function redis($type,$key,$index = null,$server = null){
		$result = $this->executeRedis($type, $key, $index, $server);
		return $result;
//		$param = array(
//			'changes'=>null,
//			'params'=>array(
//				'type' => $type,
//				'key' => $key,
//				'index' => $index,
//			)
//		);
//		$sendParam = array('info'=>'','data'=>$param,);
//		if($server)
//			$result = $this->callByServer($server,'gm/gm/Redis',$sendParam,300,true);
//		else
//			$result = $this->call('gm/gm/Redis',$sendParam,300,true);
//		$result = json_decode($result,true);
//		return $result['data'];
	}
	public function webRequest($action,$param,$server = null){
		if($server){
			global $oServers;
			$appCfg = $oServers[$server];//提供了server s33
			if(PRODUCT_SEVER_TYPE ===0 ){
				$param["zoneId"]=substr($server,1);
			}
		}else{
			$appCfg = $this->getAppCfg();//没有提供server 默认当前页面
			if(PRODUCT_SEVER_TYPE ===0 ){
				$param["zoneId"]=substr($this->getAppId(),1);
			}
		}
		$adminPath = $appCfg['webbase'];//这里获取 具体哪个服http://$ip:8080/gameservice/",repairmulicity

		$url = $adminPath.$action;
		return $this->buildBeforePost($url,$param,300,true);
	}
	//对多个服务器进行操作
	public function callByServer($server,$action,$param,$timeOut = 300,$ori = false)
	{
		global $oServers;
		$appCfg = $oServers[$server];
		$adminPath = $appCfg['gateway'];
		$sig_api_key = $appCfg['sig_api_key'];
		$sig_api_keys = explode('_', $sig_api_key);
		$param['lang'] = $appCfg['lang'];
		$param['server'] = $server;
		if(!isset($adminPath))
			return array('error'=>$oServers,'ret'=>$server);
		$url = $adminPath.'rest/'.$action;
		return $this->buildBeforePost($url,$param,$timeOut,$ori);
	}
	public function call($action,$param,$timeOut = 300,$ori = false){
		//http://zoom.myserv.com/rest/admin/admin/GetUserProfile?format=json&id=1&param[username]=1270014f7426615996d&param[model]=UserProfile
		global $sys_error;
		$appId = $this->getAppId();
		if(empty($appId)){
			$sys_error = '服不能为空';
			return array('error'=>$sys_error,'ret'=>$sys_error);
		}
		else if($appId=="ALL"){
			$sys_error = "不能对全服使用此操作:".$appId;
			return array('error'=>$sys_error,'ret'=>$sys_error);
		}
		$appCfg = $this->getAppCfg();
		$adminPath = $appCfg['gateway'];
		$sig_api_key = $appCfg['sig_api_key'];
		$sig_api_keys = explode('_', $sig_api_key);
		$param['lang'] = $appCfg['lang'];
		$param['server'] = $appId;
		$url = $adminPath.'rest/'.$action;
		if(!$param['info'])
		{
			$appCfg = $this->getAppCfg();
			$param['info'] = array('platformAppId'=>$appCfg['sig_api_key']);
		}
		return $this->buildBeforePost($url,$param,$timeOut,$ori);
	}
	//根据gateway调用接口
	public function callByGateWay($gateWay,$action,$param,$timeOut = 300){
		if(!$gateWay)
			return array('error'=>'no gateway','ret'=>'no gateway');
		$url = $gateWay.'rest/'.$action;
		return $this->buildBeforePost($url,$param,$timeOut);
	}
	
	// 得到真正的用户名
	public function getRealUsername( $username )
	{
		$appId = $this->getAppId();
		$realUsername = $username.'_'.$appId;
		return $realUsername;
	}
	
	//获得当前选择服id appid
	public function getAppId()
	{
		return  $_COOKIE['Gserver2'];
	}
	public function getAppCfg(){
		global $servers;
		return $servers[$this-> getAppId()];
	}
	public function getAdmin(){
		return $_COOKIE['u'];
	}
	
	public function getAdminAuth(){
		$a1 = $_COOKIE['a1'];
		$b2 = $_COOKIE['b2'];
		return array('a1'=>$a1,'b2'=>$b2,'auth'=>md5($a1.'elex'.$b2));
	}

	function post_request($url, $params, 
					$timeout = 600,
					$original = false,
					$headers = array(), 
					$curlopt_header = false) {
		if(!$headers){
			$headers = array(
					"Content-Type: application/x-www-form-urlencoded",
					"Connection: keep-alive",
					"Keep-Alive: 300",
			);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		if (is_array($headers) && count($headers) > 0)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($curlopt_header)
			curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch,CURLOPT_ENCODING, "gzip,deflate");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if (is_array($params) && count($params) > 0) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		} else {
			curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($params));
		}
		if (strpos($url, 'https') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$result = curl_exec($ch);
		if($original)
			return $result;
		if ($result === false) {
			$realRet['error'] = curl_error($ch);
			curl_close($ch);
			return $realRet;
		}
		curl_close($ch);
		$ret = json_decode( $result, true );
		if(!is_array($ret)){
			if (is_array($params) && count($params) > 0) {
			file_put_contents('/tmp/curlcalllog.log', $url."\n".http_build_query($params)."\n".$result."\n", FILE_APPEND);
			}else{
			file_put_contents('/tmp/curlcalllog.log', $url."\n".urlencode($params)."\n".$result."\n", FILE_APPEND);
			}
			$realRet['error'] = 'error';
			$realRet['errorInfo'] = print_r($result,true);
			$file = 'test_'.date('Y-m-d').'.log';
			file_put_contents( ADMIN_ROOT .'/'.$file,$result . "\n",FILE_APPEND);
		}
		else{
			$realRet = array(
				'ret' => $ret['data'],
			);
		}
		if ( 200 != $ret['code'] )
		{
			$realRet = array(
				'error' => $ret['data'],
				'errorInfo' => $ret['message'],
			);
		}
		else if($ret['data'] == null || (is_array($ret['data']) && reset($ret['data']) == null))
		{
			$realRet = array(
				'error' => 'no data',
				'errorInfo' => 'no data',
			);
		}
		return $realRet;
	}
	function addLog($type,$logData,$server=null){
		if(!$server)
			$server = $this->getAppId();
		foreach ($logData as $key=>$value)
		{
			if(!$value)
				unset($logData[$key]);
		}
		file_put_contents(ADMIN_ROOT."/log/".date("Y-m-d",time()).".txt",date("Y-m-d H:i:s",time())." ".$server.":\n$type".json_encode($logData)."\n",FILE_APPEND);
	}
	function generate_password( $length = 8 ,$time = 0) {
		if(!$time) 
			$time = time();
		//密码字符集，可任意添加你需要的字符
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';//!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
		$password = '';
		$circle = strlen($chars);
		//第一个验证码在字符集的位置
		$index = $time%$circle;
		$timeLen = strlen($time);
		for ( $i = 0; $i < $length; $i++ )
		{
			// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			// $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
			//根据每一个值获得对应的验证码
			$index = ($index + substr($time,$i%$timeLen,1) * 17 + 59%$circle)%$circle;
			$password .= $chars[$index];
		}
		return $password;
	}
	function queryInfoBright($db,$sql){
	    $ret = array();
// 	    $mysql = new mysqli('URLIP', 'php', 'root', $db, '5029');
	    $mysql = new mysqli('SNAPSHOTIP1', 'root', 'DBPWD', $db, '5029');
	    if($mysql->connect_errno){
	        var_dump($mysql->connect_error);
	        exit(); 
	    }
	    $result = $mysql->query($sql);
	    if($result){
	        $ret['num'] = $result->num_rows;
	        while($row = $result->fetch_assoc()){
	            $ret['data'][] = $row;
	        }
	    }
	    $mysql->close();
	    return $ret;
	}
	function queryInfoBright2($db,$sql){
		$ret = array();
		// 	    $mysql = new mysqli('URLIP', 'php', 'root', $db, '5029');
		$mysql = new mysqli('SNAPSHOTIP2
', 'root', 'DBPWD', $db, '5029');
		if($mysql->connect_errno){
			var_dump($mysql->connect_error);
			exit();
		}
		$result = $mysql->query($sql);
		if($result){
			$ret['num'] = $result->num_rows;
			while($row = $result->fetch_assoc()){
				$ret['data'][] = $row;
			}
		}
		$mysql->close();
		return $ret;
	}
	function queryInfoBright3($db,$sql){
		$ret = array();
		// 	    $mysql = new mysqli('URLIP', 'php', 'root', $db, '5029');
		$mysql = new mysqli('SNAPSHOTIP3', 'root', 'DBPWD', $db, '5029');
		if($mysql->connect_errno){
			var_dump($mysql->connect_error);
			exit();
		}
		$result = $mysql->query($sql);
		if($result){
			$ret['num'] = $result->num_rows;
			while($row = $result->fetch_assoc()){
				$ret['data'][] = $row;
			}
		}
		$mysql->close();
		return $ret;
	}
}
?>
