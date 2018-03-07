<?php
//header("HTTP/1.1 301 Moved Permanently");
//header("Location: http://gm.coq.elexapp.com/ifadmin/admin/admincp.php?" . $_SERVER['QUERY_STRING']);
//exit();
file_put_contents('/tmp/loginhis.log', "adminid=$adminid \n username=".$invalid['username']."\n groupid=".$invalid['groupid']."\n auth=".$invalid['auth']."\n",FILE_APPEND);
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED ^ E_USER_DEPRECATED);
//ini_set('display_errors',true);
//error_reporting(0);
date_default_timezone_set('UTC');


define('MAIN_PAGE_LENGTH', 2125);
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
defined("GAME_LOG_DIR") or define("GAME_LOG_DIR", ADMIN_ROOT.DIRECTORY_SEPARATOR.'log');
define('PUSH_ROOT', ADMIN_ROOT. '/push');
define('ITEM_ROOT', ADMIN_ROOT. '/xml');
define('LANG_ROOT', ADMIN_ROOT. '/xml/cn');
ini_set('mbstring.internal_encoding','UTF-8');

/*
//$GLOBALS['snapshot_db'] = array('host'=>'10.1.16.211','user'=>'cok_stat','password'=>'1234567','port'=>5029);
//$GLOBALS['stats_db'] = array('host'=>'10.1.16.211','user'=>'cok_stat','password'=>'1234567','port'=>5029);
//$GLOBALS['deploy_db'] = array('main_deploy_db'=>array('host'=>'10.1.16.211','user'=>'cok','password'=>'1234567','dbname'=>'cokdb_admin_deploy'),
//	'slave_deploy_db'=>array('host'=>'10.1.16.211','user'=>'cok','password'=>'1234567','dbname'=>'cokdb_admin_deploy'));

$GLOBALS['snapshot_db'] = array('host'=>'10.121.248.87','user'=>'root','password'=>'K2NDBm6zegpiE','port'=>5029);
$GLOBALS['stats_db'] = array('host'=>'10.121.248.87','user'=>'root','password'=>'K2NDBm6zegpiE','port'=>5029);
$GLOBALS['deploy_db'] = array('main_deploy_db'=>array('host'=>'10.82.60.173','user'=>'gow','password'=>'ZPV48MZH6q9V8oVNtu','dbname'=>'cokdb_admin_deploy'),
	'slave_deploy_db'=>array('host'=>'10.82.60.173','user'=>'gow','password'=>'ZPV48MZH6q9V8oVNtu','dbname'=>'cokdb_admin_deploy'));
*/

include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include ADMIN_ROOT.'/menu_config.php';
$clientip = get_ipxx();


$indexPageTitle = $MALANG['system_name'];
$cssVersion = '0923';
$jsVersion = '0924';
$page = new BasePage();

$module = getGPC("mod","string");

$config=get_configuration();
$opArray=array(
	'push',
	'announce_mail',
	'usermail',
	'groupMail',
);
if ($config['configuration']==1){
	global $trunkMenu,$branchMenu;
	unset($trunkMenu['standard']);
	unset($trunkMenu['modify']);
	//unset($trunkMenu['op']);
	unset($trunkMenu['server']);
	unset($trunkMenu['mysql']);
	unset($trunkMenu['admin']);
	unset($branchMenu['op']);
	$branchMenu['op']=array('fileMail'=>array('permission'=>OP_FILEMAIL,'lang'=>'menu_op_fileMail'));
}else {
	
}
$needLogin = true;
if ($needLogin) {
	$invalid = invalid();
	if($invalid===true){
		if ($module == 'standard') {
			$username = 'public';
			$password = 'cokNo.1';
			$userMd5 = md5($username . $password . AUTH_KEY);
			
			$_COOKIE['u'] = 'public';
			$_COOKIE['a1'] = $userMd5;
			$_COOKIE['b2'] = md5($userMd5.AUTH_KEY.AUTH_KEY2);
			$invalid = invalid();
			$invalid['username'] = 'public';
		}else{
			include ("./control/admin/admin_login.php");
			exit();
		}
	}
	$op_msg = '';
	$error_msg = '';
	$adminid = "admin";


	file_put_contents('/tmp/loginhis.log', "adminid=$adminid \n username=".$invalid['username']."\n groupid=".$invalid['groupid']."\n auth=".$invalid['auth']."\n",FILE_APPEND);
//	if(!$adminid){
//		include ("./control/admin/admin_login.php");
//		exit();
//	}
	$group_id =31;// intval($invalid['groupid']);

} else {
	$group_id=31;
}

$allGroupPermission = require ADMIN_ROOT . "/etc/admin.group.php";
include_once ADMIN_ROOT.'/servers.php';
//TODO: 运营要求看到每个后台管理人员 最后操作时间, 点开下面每个页面都记录下
$recordModArr = array('modify','op','server','admin','user');
if(in_array($module,$recordModArr)) {
	recordOpLastActiveTime($adminid);
}

//获得必须的菜单
$auth ='9900,200,500,300,1100,400,1400,700,600,900';// $invalid['auth'];

$array_auth = explode(',', $auth);
//权限 默认添加global 和工作规范
$array_auth[]='1200';
$array_auth[]='9900';
$group_auth = array();
if(!empty($allGroupPermission[$group_id]['auth'])){
    $group_auth =explode(',', $allGroupPermission[$group_id]['auth']);
}
$array_auth = array_merge($array_auth,$group_auth);
global $authList;
foreach ($array_auth as $key=>$value){
	if(!in_array($value,$authList)){
		unset($array_auth[$key]);
	}
}
$permission = array_combine($array_auth, $array_auth);
$menu = initMenu($group_id, $permission);

$today = date("Y-m-d");
$modules = array_keys($actions);
if(!$module || !in_array($module,$modules)){
    $menuKeys = array_keys($menu);
    //reset参数是引用传递的，php 5.3以上默认只能传递具体的变量，而不能通过函数返回值
	$module = reset($menuKeys);//没有设置mod的时候调整模块
}
$action = getGPC('act',"string");
//file_put_contents(ADMIN_ROOT.'/GMUseStat.log', "$adminid|$module|$action\n",FILE_APPEND);
$paras = json_encode($_REQUEST);
$clientip = get_ipxx();
file_put_contents(ADMIN_ROOT.'/GMUseStat2.log', date('Y-m-d H:i:s')." $adminid $clientip $module $action $paras\n",FILE_APPEND);

if(!$action){
	$module_actions = array_keys($actions[$module]);
	$action = "";
	foreach ($actions[$module] as $mod=>$per){
		if($permission[$per] || $permission[$trunkMenu[$module]['permission']])
		{
			$action = $mod;
			break;
		}
	}
	if(!$action)
		$action = reset(array_keys($actions[$module]));
	if($group_id)
		$action = $module_actions[0];
}
$curTime = sprintf("%s ",date('Y-m-d H:i:s')); //在下边设置了时区?上海??
global $servers;
$resetServer = false;
if(isset($_GET['Gserver'])){
	$currentServer = $_GET['Gserver'];//getGPC('Gserver',"string");//$_REQUEST['server'];
	if($servers[$currentServer]){
		$_COOKIE['Gserver2'] = $currentServer;
		setcookie('Gserver2',$currentServer,time()+3600*8);
		date_default_timezone_set($servers[$currentServer]['timezone']);
	}else{
		$resetServer = true;
	}
}
else{	
	$currentServer = $_COOKIE['Gserver2'];//getGPC('server',"string");
	if($currentServer && $servers[$currentServer]){
		date_default_timezone_set($servers[$currentServer]['timezone']);
	}else{
		$resetServer = true;
	}
}
if($resetServer){
	$ta = array_keys($servers);
	$currentServer=reset($ta);
	$_COOKIE['Gserver2'] = $currentServer;
	setcookie('Gserver2',$currentServer,time()+3600);
	date_default_timezone_set($servers[$currentServer]['timezone']);
}

//if ((substr($currentServer, 1)>500 && substr($currentServer, 1)<1000) || substr($currentServer, 1)>900051 && substr($currentServer, 1)<900100) {
//		$GLOBALS['snapshot_db'] = array('host'=>'SNAPSHOTIP2
//','user'=>'root','password'=>'DBPWD','port'=>5029);
//}

if (isset($_POST['username']) || isset($_POST['useruid']) || isset($_POST['userplat']))
{
	$username = trim($_POST['username']);
	setcookie('u2',$username,time()+3600);
	$useruid = trim($_POST ['useruid']);
	setcookie('u3',$useruid,time()+3600);
	$userplat = trim($_POST ['userplat']);
	setcookie('u4',$userplat,time()+3600);
}
else
{
	$username = $_COOKIE['u2'];//getGPC('server',"string");
	if(empty($username)){
		$username="";
		setcookie('u2',$username,time()+3600);
	}
	$useruid = $_COOKIE['u3'];//getGPC('server',"string");
	if(empty($useruid)){
		$useruid="";
		setcookie('u3',$useruid,time()+3600);
	}
	$userplat = $_COOKIE['u4'];//getGPC('server',"string");
	if(empty($userplat)){
		$userplat="";
		setcookie('u4',$userplat,time()+3600);
	}
}
$privileges = array(
		'dropdownlist_view' => true,
		'edit' => true,
		'search' => true,
		'export' => true,
);

if (empty($_COOKIE['u']) || strpos($_COOKIE['u'], 'COKKGGC')!==false) {
	$privileges['dropdownlist_view'] = false;
	$privileges['edit'] = false;
	$privileges['export'] = false;
}

// 根据$servers生成显示的服务器列表
// $serverinfoXml = loadXml('serverinfo','chance');
$titleServers = array();
foreach ($servers as $server=>$serverInfo){
	$serverNum = substr($server, 1);
	$sub1 = intval($serverNum/100);
	$sub1 = 100*$sub1 .'-'. (100*($sub1 + 1) - 1);
	$sub = intval($serverNum/10);
	$sub = 10*$sub .'-'. (10*($sub + 1) - 1);
	$serverInfo['ip'] = substr($serverInfo['webbase'], 7 ,strpos($serverInfo['webbase'], ':8080')-7);
	$titleServers[$sub1][$sub][$server] = $serverInfo;
}
$currentServerSub = intval(substr($currentServer, 1)/10);
$currentServerSub = 10*$currentServerSub .'-'. (10*($currentServerSub + 1) - 1);
//分页判断不存在
if(!($module=="admin" && in_array($action, array("quit","editpassword"))) && !isset($actions[$module][$action])){
	$trunckCheck = true;
}
//分页权限判断
if(!($module=="admin" && in_array($action, array("quit","editpassword")))){
	$branchCheck = true;
	//如果页面配置为隐藏，那么必须有查看隐藏分页权限才能打开
	if($branchMenu[$module][$action]['hide'])
		$hideCheck = true;
	//有主分页全部权限或者有副分页权限
	if($permission[$actions[$module][$action]] || $permission[$trunkMenu[$module]['permission']]){
		$branchCheck = false;
	}
}
$super = intval($group_id/10) == 1;
$hide = intval($group_id/10) == 2;
//最高权限组
if($super){
	$trunckCheck = false;
	$branchCheck = false;
	$hideCheck = false;
}elseif ($hide){//可查看隐藏分页的组
	$hideCheck = false;
}
if($trunckCheck){
	$error_msg = $MALANG['system_model_not_exsit'];
	include (ADMIN_ROOT.'/control/admin/admin_limiterror.php');
}
// 普通用户
elseif($branchCheck || $hideCheck){
	$error_msg = $MALANG['system_model_access_denied'];
	include (ADMIN_ROOT.'/control/admin/admin_limiterror.php');
}
// 管理员
else{
	$ctrl_file = ADMIN_ROOT."/control/{$module}/{$module}_{$action}.php";
	if(file_exists($ctrl_file)){
		try{
			include ($ctrl_file);
		}catch (Exception $e){
			$error_msg = $e->__toString();
			//$app_logger->writeError($e->getMessage());
			include (ADMIN_ROOT.'/control/admin/admin_limiterror.php');
		}
	}else{
		//echo $ctrl_file;
		$error_msg = $MALANG['system_model_not_exsit'].$ctrl_file;
		include (ADMIN_ROOT.'/control/admin/admin_limiterror.php');
	}	
}
//按照层级打印数组
function search($data,$tab=0){
	if(!is_array($data) && !is_object($data))
		return $data;
	$result = "";
	foreach ($data as $key=>$value)
	{
		if(!is_array($value))
		{
			$result .= str_repeat("&nbsp;",$tab*4).(string)$key." => ".(string)$value."<br />";
		}
		else
		{
			$result .= str_repeat("&nbsp;",$tab*4).(string)$key." => <br />";
			$result .= search($value,$tab+1);
		}
	}
	return $result;
}
function getCurrServer(){
	global $servers;
	if(!$_COOKIE['Gserver2']){
		return reset(array_keys($servers));
	}else{
		return $_COOKIE['Gserver2'];
	}
}
function page($total, $curr_page, $page_limit, $backFunction = null, $turnPage = null){
	$page = empty($curr_page) ? 1 : $curr_page;
	$last_page = ceil($total/$page_limit); //最后页，也是总页数
	$page = min($last_page, $page);
	$prepg = $page - 1; //上一页
	$nextpg = ($page == $last_page ? 0 : $page + 1); //下一页
	$offset = intval(max($page - 1, 0) * $page_limit);

	//开始分页导航条代码：
	$pagenav="显示第 <B>".($total ? ($offset + 1):0)."</B>-<B>".min($offset + $page_limit ,$total)."</B> 条记录，共 $total 条记录";
	//如果只有一页则跳出函数：
	if($last_page<=1) return array('offset' => $offset, 'pager' => NULL);
	$pagenav.=" <a href='#' onclick='getData(1)'>首页</a> ";
	if(!$backFunction)
		$backFunction = 'getData';
	if(!$turnPage)
		$turnPage = 'turnPage';
	if($prepg) $pagenav.=" <a href='#' onclick='{$backFunction}({$prepg})'>前页</a> "; else $pagenav.=" 前页 ";
	if($nextpg) $pagenav.=" <a href='#' onclick='{$backFunction}({$nextpg})'>后页</a> "; else $pagenav.=" 后页 ";
	$pagenav.=" <a href='#' onclick='{$backFunction}({$last_page})'>尾页</a> ";
	$pagenav.=" 第{$page}页  共 {$last_page} 页";
	$pagenav .= " 跳转 <input class='input-small' size=3 id='turn' onKeyUp='check(this)' value=''> <input type='button' value='go' onclick='{$turnPage}()'>";
	return array('offset' => $offset, 'pager' => $pagenav);
}
function getGUID() {
	$ip = "127001";
	$unknown = 'unknown';
	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	&& $_SERVER['HTTP_X_FORWARDED_FOR']
	&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
	$unknown) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} 
	elseif ( isset($_SERVER['REMOTE_ADDR'])
	&& $_SERVER['REMOTE_ADDR'] &&
	strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip = str_replace(".","", $ip);
	$ip = str_replace(",","", $ip);
	$ip = trim($ip);
	return uniqid($ip.'COK');
}
function addGoldLog($userId,$ori,$change,$remain,$server=''){
	$uid = getGUID();
	$time = microtime(true)*1000;
	$time = floor($time);
	$sql = "insert into gold_cost_record (uid,userId,type,param1,param2,originalGold,cost,remainGold,time) values('$uid','$userId','30','0','0','$ori','$change','$remain','$time')";
	if(!$server)
		$server = getCurrServer();
	$page = new BasePage();
	$page->executeServer($server,$sql,2,true);
}
function recordOpLastActiveTime($username)
{
	global $page;
	if($page == null){
		$page = new BasePage();
	}
	require_once ADMIN_ROOT . "/include/XMySQL.php";

	$tablename = "admin";
	$where = array('username' => $username);
	$mysql = new XMySQL($page->getMySQLInfo(true, 's1')); //此时还没有建立连接
	$result = $mysql->get($tablename, $where);
	if(!empty($result)){
		$result = $result[0];
		$result['lastactive'] = time();
		$mysql->put($tablename, array('uid' => $result['uid']), array('lastactive' => $result['lastactive']));
	}
}
?>