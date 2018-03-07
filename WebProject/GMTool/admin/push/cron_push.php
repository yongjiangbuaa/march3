<?php
define('PUSH_ROOT', __DIR__);
require_once PUSH_ROOT.'/Push.php';

echo date('[Y-m-d H:i:s] ').__FILE__." START.\n";

// TODO 1)加入玩家所在服信息到 Parse上Installation的字段channels。
// TODO 2)各服(tbl_webserver)循环、并行处理。

$host = gethostbyname(gethostname());
if ($host == '91-87') {
	$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
	$cokdb_hostinfo = array(
			'host' => '10.43.227.11',
			'port' => '3306',
			'user' => 'gow',
			'password' => 'ZPV48MZH6q9V8oVNtu',
			'dbname' => 'cokdb1',
	);
}else{
	//***TEST***
	$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
	$cokdb_hostinfo = array(
			'host' => '10.1.16.211',
			'port' => '3306',
			'user' => 'cok',
			'password' => '1234567',
			'dbname' => 'cokdb1',
	);
	//***TEST***
}

print_r($cokdb_hostinfo);
$link = mysqli_connect($cokdb_hostinfo['host'], $cokdb_hostinfo['user'], $cokdb_hostinfo['password'], $cokdb_hostinfo['dbname'], $cokdb_hostinfo['port']);
if (!$link) {
	echo date('[Y-m-d H:i:s]')." db connect error.\n";
	exit(0);
}

$push_config = get_push_config();
check_record($push_config);

$uid = $push_config['uid'];
$message = $push_config['notification'];
$parse_push_status = $push_config['parse'];

// $message = '100 Free golds for you,update now!';

$push = new Push ( $parse_app_id, $parse_api_key );
$push->device = Push::DEVICE_TYPE_ANDROID;
$result = $push->pushToAll ( $message );

echo "call Parse: $parse_app_id\n", "msg=$message\n", "result=";
print_r ( $result );

$sql = "update server_push set parse=1 where uid='$uid';";
$ret = mysqli_query($link, $sql);
echo "$sql\n", "ret=".intval($ret)."\n\n";

echo date('[Y-m-d H:i:s] ').__FILE__." END.\n";

exit(0);

//  
function get_push_config(){
	$now = time()*1000;
	$push_online = 1413475200000;//2014-10-17
	$sql = "select * from server_push where parse=0 and state=1 and startTime<=$now and startTime>$push_online order by startTime desc;";
	echo $sql, PHP_EOL;
	$data = query_db($sql);
	if (empty($data)) {
		return false;
	}
	return $data[0];
}

function check_record($push_config){
	if (!$push_config) {
		echo date('[Y-m-d H:i:s]')." No data.\n";
		exit(0);
	}
	echo date('[Y-m-d H:i:s]')." uid={$push_config['uid']}\n";
	if (empty($push_config['notification'])) {
		echo date('[Y-m-d H:i:s]')." message is Empty.\n";
		exit(0);
	}
	if ($push_config['parse'] != 0) {
		echo date('[Y-m-d H:i:s]')." had been processed.\n";
		exit(0);
	}
}

function query_db($sql) {
	global $link;
	$result = mysqli_query($link, $sql);
	if (empty($result)) {
		return array();
	}
	$ret = array ();
	while ( $row = mysqli_fetch_assoc($result) ) {
		$ret [] = $row;
	}
	return $ret;
}


// mysql> desc server_push;
// +----------------+--------------+------+-----+---------+-------+
// | Field          | Type         | Null | Key | Default | Extra |
// +----------------+--------------+------+-----+---------+-------+
// | uid            | varchar(40)  | NO   | PRI | NULL    |       |
// | type           | int(4)       | YES  |     | 0       |       |
// | mailType       | int(11)      | YES  |     | 0       |       |
// | startTime      | bigint(20)   | YES  | MUL | NULL    |       |
// | endTime        | bigint(20)   | YES  |     | NULL    |       |
// | regStartTime   | bigint(20)   | YES  |     | NULL    |       |
// | regEndTime     | bigint(20)   | YES  |     | NULL    |       |
// | lastOnlineTime | bigint(20)   | YES  |     | NULL    |       |
// | levelMin       | int(10)      | YES  |     | NULL    |       |
// | levelMax       | int(10)      | YES  |     | NULL    |       |
// | title          | blob         | YES  |     | NULL    |       |
// | contents       | blob         | YES  |     | NULL    |       |
// | reward         | blob         | YES  |     | NULL    |       |
// | updateVersion  | varchar(40)  | YES  |     | NULL    |       |
// | state          | int(11)      | YES  |     | NULL    |       |
// | parse          | int(11)      | YES  |     | NULL    |       |
// | notification   | varchar(128) | YES  |     | NULL    |       |
// +----------------+--------------+------+-----+---------+-------+
// 17 rows in set (0.00 sec)
