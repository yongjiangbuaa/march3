<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '216M');
define('AUTH_KEY', 'qucDcvk44bxfyVumPcEJkdMZKGgPmeAr');
define('AUTH_KEY2', 'XSSrmBSMgMXkknJcBsuqXJ5ufD6CfPgN');

$host = gethostbyname(gethostname());
if($host == '10.1.16.211' || $host == '127.0.0.1' || PHP_OS == 'Darwin'){
	defined('GLOBAL_REDIS_SERVER_IP') || define('GLOBAL_REDIS_SERVER_IP', '127.0.0.1');
	defined('GLOBAL_REDIS_SERVER_IP2') || define('GLOBAL_REDIS_SERVER_IP2', '127.0.0.1');
	defined('GLOBAL_REDIS_SERVER_IP2_PORT') || define('GLOBAL_REDIS_SERVER_IP2_PORT', 6379);
	$xincloud_app_config = ADMIN_ROOT . '/etc/xincloud_app_config_dev.php';
	putenv('xpath=' . $xincloud_app_config);// 设置行云应用本地开发测试机器的配置文件目录
	$GLOBAL_DB_SERVER_IP = array('127.0.0.1','127.0.0.1');//DEPLOYIP
	define("DEV_ENV",true);

}else{
	defined('GLOBAL_REDIS_SERVER_IP') || define('GLOBAL_REDIS_SERVER_IP', '10.121.248.63');
	defined('GLOBAL_REDIS_SERVER_IP2') || define('GLOBAL_REDIS_SERVER_IP2', '10.121.73.197');
	defined('GLOBAL_REDIS_SERVER_IP2_PORT') || define('GLOBAL_REDIS_SERVER_IP2_PORT', 6379);
	$xincloud_app_config = ADMIN_ROOT . '/etc/xincloud_app_config.php';
	putenv('xpath=' . $xincloud_app_config);// 设置行云应用的配置文件目录
	$GLOBAL_DB_SERVER_IP = array('127.0.0.1','127.0.0.1');//DEPLOYIP
}
$cok_ad_db_host = '127.0.0.1';
$db_username = 'march';
$db_password = '';
$global_db_name = 'march_global';
define("DEV_ENV",true);
$GLOBALS['snapshot_db'] = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);
$GLOBALS['stats_db'] = array('host'=>STATS_DB_SERVER_IP,'user'=>STATS_DB_SERVER_USER,'password'=>STATS_DB_SERVER_PWD,'port'=>5029);
$GLOBALS['deploy_db'] = array('main_deploy_db'=>array('host'=>GLOBAL_DB_SERVER_IP,'user'=>GLOBAL_DB_SERVER_USER,'password'=>GLOBAL_DB_SERVER_PWD,'dbname'=>GLOBAL_DEPLOY_DB_NAME),
	'slave_deploy_db'=>array('host'=>GLOBAL_DB_SLAVE_IP,'user'=>GLOBAL_DB_SERVER_USER,'password'=>GLOBAL_DB_SERVER_PWD,'dbname'=>GLOBAL_DEPLOY_DB_NAME));

$db_server_ip = $GLOBAL_DB_SERVER_IP;
if (is_array($db_server_ip)) {
	$idx = randItemWithWeight(array(100,100));
	$db_server_ip = $db_server_ip[$idx];
}
$cokdb_global_hostinfo1 = array(
		'host' => $db_server_ip,
		'user' => $db_username,
		'password' => $db_password,
		'db' => $global_db_name,
// 		'port' => '8066',
		'port' => '3306',
);
$cokdb_global_hostinfo2 = array(
		'host' => $db_server_ip,
		'user' => $db_username,
		'password' => $db_password,
		'db' => $global_db_name,
		'port' => '3306',
);

$cok_db_stats_global_host_info = array(
	'host' => $cok_ad_db_host,
	'user' => $db_username,
	'password' => $db_password,
	'db' => 'stats_global',
	'port' => '3306',
);

$cok_db_ad_host_info = array(
	'host' => $cok_ad_db_host,
	'user' => $db_username,
	'password' => $db_password,
	'db' => 'cokdb_ad',
	'port' => '3306',
);
/**
 * 从cobar中读取 type value对应的 所有的account列表。
 * @param string $type
 * @param string $value
 * @return array
 */
function cobar_getAllAccountList($type, $value, $gameUid=NULL){
	$allAccountArr = getRecordFromMappingDB($type, $value, $gameUid);
	if (empty($allAccountArr)) {
		return array();
	}

	$target_list = array();
	foreach ($allAccountArr as $row) {
		$target_list[$row['lastTime']] = $row;
	}
	krsort($target_list);
	$allAccountArr = array_values($target_list);

	return $allAccountArr;
}
/**
 * 从cobar中读取 type value对应的 有效的account列表。
 * @param string $type
 * @param string $value
 * @return array
 */
function cobar_getValidAccountList($type, $value, $gameUid=NULL){
	$allAccountArr = getRecordFromMappingDB($type, $value, $gameUid);
	if (empty($allAccountArr)) {
		return array();
	}

	$target_list = array();
	foreach ($allAccountArr as $row) {
		if ($row['active'] == 1) {
			continue;
		}
		$target_list[$row['lastTime']] = $row;
	}
	krsort($target_list);
	$validAccountArr = array_values($target_list);

	return $validAccountArr;
}
/**
 * cobar 更新用户名
 * @param string $uid
 * @param string $oldName
 * @param string $name
 * @return bool
 */
function cobar_changeUserName($uid, $oldName, $name){
	$sql = "update account_new set gameUserName='$name' where gameuid='$uid'";
	cobar_query_global_db_cobar($sql);
	//先删是怕没有这条数据,直接update会出错,先删(没有数据,也ok)
	$sql = "delete from usermapping where gameUid='$uid' and mappingType='name' and mappingValue='$oldName'";
	cobar_query_global_db_cobar($sql);
	$sql = "insert into usermapping set gameUid='$uid', mappingType='name', mappingValue='$name'";
	cobar_query_global_db_cobar($sql);
	return true;
}
/**
 * 从数据库表 usermapping/account_new 中读取$target_gameuids account数据。
 * @param multitype $target_gameuids: 使用逗号隔开的字符串。或者array
 * @return array
 */
function cobar_getAccountInfoByGameuids($target_gameuids){
	//获取mapping gameuid
	$uidlist = array();
	if (!is_array($target_gameuids)) {
		$uidlist = explode(",", $target_gameuids);
	}else{
		$uidlist = $target_gameuids;
	}
	
	foreach ($uidlist as &$uid) {
		$uid = trim($uid, "'");
	}
	
	$gameuids = "'". implode("','", $uidlist). "'";
	
	//获取所有Account数据
	$allAccountArr = cobar_query_global_db_cobar("select * from account_new where gameUid in ($gameuids);");
	if (empty($allAccountArr)) {
		return array();
	}
	return $allAccountArr;
}
/**
 * 从cobar中 删除 type value。
 * @param string $type
 * @param string $value
 * @return array
 */
function cobar_delUserMapping($type, $value, $gameUid){
	$sql = "delete from usermapping where mappingType='$type' and mappingValue = '$value' and gameUid='$gameUid';";
	return cobar_query_global_db_cobar($sql);
}
/**
 * 从数据库表 usermapping/account_new 中读取account数据。
 * @param string $type
 * @param string $value
 * @return multitype:|Ambigous <multitype:multitype: , multitype:unknown >
 */
function getRecordFromMappingDB($type, $value, $gameUid=NULL){
	//获取mapping gameuid
	if ($gameUid === NULL) {
		$sql = "select * from usermapping where mappingType='$type' and mappingValue = '$value';";
	}else{
		$sql = "select * from usermapping where mappingType='$type' and mappingValue = '$value' and gameUid='$gameUid';";
	}
	$allAccountArr = cobar_query_global_db_cobar($sql);
	if (empty($allAccountArr)) {
		return array();
	}
	$target_gameuids = array();
	foreach ($allAccountArr as $row) {
		$target_gameuids[] = "'".$row['gameUid']."'";
	}
	$gameuids = implode(',', $target_gameuids);
	//获取所有Account数据
	$allAccountArr = cobar_query_global_db_cobar("select * from account_new where gameUid in ($gameuids);");
	if (empty($allAccountArr)) {
		return array();
	}
	return $allAccountArr;
}

function cobar_query_global_db_cobar($sql) {
	return doquery('cobar', $sql);
}
function cobar_query_global_db_direct($sql) {
	return doquery('direct', $sql);
}
function doquery($type,$sql){
	global $cokdb_global_hostinfo1, $cokdb_global_hostinfo2;
	if ($type == 'cobar') {
		$cokdb_global_hostinfo = $cokdb_global_hostinfo1;
	}else{
		$cokdb_global_hostinfo = $cokdb_global_hostinfo2;
	}
	return query_from_db($cokdb_global_hostinfo, $sql);
}

function get_stats_global_connection(){
	global $cok_db_stats_global_host_info;
	return get_mysqli_connection($cok_db_stats_global_host_info);
}

function query_stats_global($sql){
	global $cok_db_stats_global_host_info;
	return query_from_db($cok_db_stats_global_host_info, $sql);
}

function query_ad_db($sql){
	global $cok_db_ad_host_info;
	return query_from_db($cok_db_ad_host_info, $sql);
}

function get_ad_connection(){
	global $cok_db_ad_host_info;
	return get_mysqli_connection($cok_db_ad_host_info);
}

function query_from_db($dbInfo,$sql) {
    file_put_contents('/tmp/loginhis.log', "cobar query db :dbInfo=".var_export($dbInfo,true)." \n sql=".$sql."\n",FILE_APPEND);


    
	$mysqli = get_mysqli_connection($dbInfo);
	if(mysqli_connect_error()){
		$msg = sprintf("connect to %s:%d fail use %s. errno %s: %s", $dbInfo['host'], $dbInfo['port'], $dbInfo['user'],
			mysqli_connect_errno(), mysqli_connect_error());
		error_log($msg);
		return false;
	}
	$result = $mysqli->query($sql);
	if(is_bool($result)){
		$mysqli->close();
		return $result;
	}
	$data = array();
	if ($result && is_object($result)) {
		while ($row = $result->fetch_assoc()) {
			$data [] = $row;
		}
		$result->free();
	}
	$mysqli->close();
	return $data;

}
function query_from_db_new($mysqli,$sql) {
	$result = $mysqli->query($sql);
	if(is_bool($result)){
		$mysqli->close();
		return $result;
	}
	$data = array();
	if ($result && is_object($result)) {
		while ($row = $result->fetch_assoc()) {
			$data [] = $row;
		}
		$result->free();
	}
	$mysqli->close();
	return $data;

}

function get_mysqli_connection($db_info){
	$db_name = '';
	if($db_info['db']){
		$db_name = $db_info['db'];
	}
	if($db_info['dbname']){
		$db_name = $db_info['dbname'];
	}
	$port = 3306;
	if($db_info['port']){
		$port = $db_info['port'];
	}
	if($db_info['pass'] && !$db_info['password']){
	    $db_info['password'] = $db_info['pass'];
    }
	$mysqli = new mysqli($db_info['host'], $db_info['user'], $db_info['password'], $db_name, $port);
	return $mysqli;
}

function new_mysqli_connection($host,$db_name = '', $port = 3306){
	global $db_username, $db_password;
	$mysqli = new mysqli($host, $db_username, $db_password, $db_name, $port);
	return $mysqli;
}

function randItemWithWeight($weights){
	$weight_sum = array_sum($weights);
	$seed = 100000;
	$weight_total = $weight_sum * $seed;
	$rand = mt_rand(1, $weight_total);
	foreach ($weights as $k=>$w){
		$rand -= $w * $seed;
		if($rand <= 0){
			return $k;
		}
	}
	return null;
}


function login($username,$password){
	$clientip = get_ipxx();
//	file_put_contents('/tmp/loginhis.log', date('Y-m-d H:i:s').",$username,$password,$clientip\n",FILE_APPEND);
	$passmd5 = md5(md5($username . $password . AUTH_KEY).AUTH_KEY.AUTH_KEY2);
	$admin = getAdminByName($username, $passmd5);
/**
	if(!isset($admin)){
		return 1;
	}
	if($passmd5!=$admin['passmd5']){
		return 2;
	}**/
//	file_put_contents('/tmp/loginhis.log', 'admin='.json_encode($admin)."\n",FILE_APPEND);
	
	$userMd5 = md5($username . $password . AUTH_KEY);
	$expire = time()+360000000;

	setcookie('u',$username, $expire);
	setcookie('a1',$userMd5, $expire);
	setcookie('b2',md5($userMd5.AUTH_KEY.AUTH_KEY2), $expire);
	setcookie('u_info',json_encode($admin));
	setcookie('c3',$expire, $expire);
file_put_contents(ADMIN_ROOT.'/GMUseStat2.log', date('Y-m-d H:i:s')." $username $clientip admin login $password\n",FILE_APPEND);
	return 0;
}
function logout(){
	setcookie('u',"", time() - 3600);
	setcookie('a1',"", time() - 3600);
	setcookie('b2',"", time() - 3600);
	setcookie('u_info',"", time() - 3600);
	setcookie('c3',"", time() - 3600);
}
//验证是否非法 非法返回true 不是非法 返回false
function invalid(){
	if(!isset($_COOKIE['u']) || !isset($_COOKIE['a1']) || !isset($_COOKIE['b2'])){
		return true;
	}
	$username = $_COOKIE['u'];
	$a1 = $_COOKIE['a1'];
	$b2 = $_COOKIE['b2'];
	$b2Auth = md5($a1.AUTH_KEY.AUTH_KEY2);
	if($b2!=$b2Auth){
		return true;
	}
	$data = json_decode(stripcslashes($_COOKIE['u_info']),true);//这里会有用户的权限
	/*file_put_contents('/tmp/loginhis.log', "cookie u_info=".$_COOKIE['u_info']."\n",FILE_APPEND);
	file_put_contents('/tmp/loginhis.log', " decode=$data\n json_last_error=".json_last_error()."\n",FILE_APPEND)*/;
	//{"uid":"22","username":"testname","passmd5":"12412312321","language":"zh_CN","auth":"1200,1200,9900,200,500,300,329,333,1100,400,1400,700,600,900,903,902","groupid":"39","addtime":"1457932492","lastactive":1484113878,"admincomment":"\u540e\u53f0\u7ba1\u7406\u5f00\u53d1"}
	//密码为空
	if(md5($username . AUTH_KEY) == $a1){
		$data['auth'] = ADMIN_USER_EDITPASSWORD;
	}
//$clientip = get_ipxx();
//$datajson = json_encode($data);
//file_put_contents(ADMIN_ROOT.'/GMUseStat2.log', date('Y-m-d H:i:s')." $username $clientip admin validate $datajson\n",FILE_APPEND);

	return $data;
}
function getAdminByName($username, $passmd5)
{
    global $page;
    if($page == null){
        $page = new BasePage();
    }
    require_once ADMIN_ROOT . "/include/XMySQL.php";
	
	//TODO查询
	$tablename = "admin";
	$where = array('username' => $username, 'passmd5' => $passmd5);
    $mysql = new XMySQL($page->getMySQLInfo(true, 's1')); //此时还没有建立连接
	$result = $mysql->get($tablename, $where);
	if(!empty($result)){
		$result = $result[0];
		$result['lastactive'] = time();
        $mysql->put($tablename, array('uid' => $result['uid']), array('lastactive' => $result['lastactive']));
	}
	
	return $result;
}

function query_deploy($sql,$mainDB=false,$dbName=null,$port=3306){
	global $deploy_db;
	if ($mainDB){
		$db=$deploy_db['main_deploy_db'];
	}else {
		$db=$deploy_db['slave_deploy_db'];
	}
	if ($dbName){
		$db['dbname'] = $dbName;
	}
	$result = query_from_db($db,$sql);
	if (is_bool($result)) {
		return $result;
	}
	$ret = array();
	$ret['ret']['data'] = $result;
	return $ret;
}

function query_infobright($sql){
	global $stats_db;
	$data = query_from_db($stats_db,$sql);
	if(is_bool($data)){
		return $data;
	}
	$ret = array();
	$ret['ret']['data'] = $data;
	return $ret;
}
function query_bqresult($sql){
	$stats_db=array('host'=>new_AD_DB_SERVER_IP,'user'=>new_AD_DB_SERVER_USER,'password'=>new_AD_DB_SERVER_PWD,'database'=>new_AD_DB_SERVER_DATABASE,'port'=>3306);
	$link = mysqli_connect($stats_db['host'], $stats_db['user'], $stats_db['password'], $stats_db['database'], $stats_db['port']);
	if (!$link) {
		die("Connection failed: " . mysqli_connect_error());
	}
	$result = mysqli_query($link, $sql);
	$errno = mysqli_errno($link);
	$errmsg = mysqli_error($link);
	if(0 != $errno){
		$sql = str_replace("\n", '\n', $sql);
		return false;
	}
	if (is_bool($result)) {
		return $result;
	}
	$ret = array();
	unset($row);
	while ($row = mysqli_fetch_assoc($result)) {
		$ret[] = $row;
	}
	mysqli_close($link);
	return $ret;
}
function query_snapshot($sql){
	global $snapshot_db;
	$data = query_from_db($snapshot_db,$sql);
	if(is_bool($data)){
		return $data;
	}
	$ret = array();
	$ret['ret']['data'] = $data;
	return $ret;
}


function get_ipxx() {
	if (_valid_ipxx($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
		if (_valid_ipxx(trim($ip))) {
			return $ip;
		}
	}
	if (_valid_ipxx($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} elseif (_valid_ipxx($_SERVER["HTTP_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
	} elseif (_valid_ipxx($_SERVER["HTTP_FORWARDED"])) {
		return $_SERVER["HTTP_FORWARDED"];
	} elseif (_valid_ipxx($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}
function _valid_ipxx($ip) {
	if (!empty($ip) && ip2long($ip)!=-1) {
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);
		foreach ($reserved_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	} else {
		return false;
	}
}

function xss_replace($string){
	return htmlentities(RemoveXSS($string));
}
function RemoveXSS($val) {
	$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		$val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	}
	$ra1 = Array('script', 'alert','img','src','document','write','cookie','javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);
	$found = true;
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
					$pattern .= '|(&#0{0,8}([9][10][13]);?)?';
					$pattern .= ')?';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
			$val = preg_replace($pattern, $replacement, $val);
			if ($val_before == $val) {
				$found = false;
			}
		}
	}
	return $val;
}
function escape_mysql_special_char($val){
	$pattern = '/[\'"()*&%@+-[]|]/';
	$replacement = '\\\\${0}';
	$val = preg_replace($pattern,$replacement,$val);
	return $val;
}
?>