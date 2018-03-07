<?
!defined('IN_ADMIN') && exit('Access Denied');
define('FILE_ROOT', realpath(dirname(__FILE__) . '/'));
/**
 * {
"appVersion" : "2.1.16",
"fromCountry" : "TW",
"pfId" : "",
"gaid" : "e976d57f-21fa-4c7c-b47a-8fe4f5ab110a",
"isHDLogin" : "0",
"gmLogin" : 0,
"cmdBaseTime" : "1507248031",
"gameUid" : "1102735838000009",
"deviceId" : "d8488246963e4f4aa9ade94151309533,SM-G610Yuniversal7870MMB29K",
"uuid" : "358022058074933-485A3F1545301460416144825",
"serverId" : "98",
"afUID" : "",
"platform" : "1",
"googlePlay" : "s0930118221@gmail.com",
"phoneDevice" : "3.18.14-11301984",
"reconnect" : false,
"device_info" : {
"operator_name" : "Chunghwa Telecom",
"screen_h" : 1440,
"screen_w" : 810,
"os_build" : "MMB29K.G610YZTU1AQD7",
"screen_density" : 360,
"os_version" : "6.0.1",
"model" : "SM-G610Y",
"network_type" : 13
},
"pf" : "market_global",
"packageName" : "com.elex.coq.gp",
"lang" : "zh_TW",
"region" : "TW",
"SecurityCode" : "802b86aeb634bddd7f9b4de47e876a55"
},
 */
function value_idx($arr){
	$b=array_keys($arr);
	$c=array_flip($b);
	return $c;
}
function  processDay($date_str,$latest){
	$res_days=array();
	$startTime = strtotime($date_str);
	while($latest !==  0 ){
		$res_days[]=date('Ymd',$startTime);
		$startTime-=86400;
		if($latest > 0 ) $latest--;
		else $latest++;
	}
	return $res_days;
}
$title = "用户登录行为";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid']){
	$uid = $_REQUEST['useruid'];
}
if($_REQUEST['std_date']){
	$std_date= $_REQUEST['std_date'];
}
if($_REQUEST['date_range']){
	$date_range= $_REQUEST['date_range'];
}
if($type == 1) {
	if ($username) {
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sid = $account_list[0]['server'];
	} else if ($uid) {
		$account_list = cobar_getAccountInfoByGameuids($uid);
		$sid = $account_list[0]['server'];
	}

	if (empty($uid) || empty($sid)) {//TODO 判断uid和sid不存在的情况
		$alert = " s$sid $uid 用户不存在！";
	} else {
		if (empty($std_date)) $std_date = date('Ymd');
		if (empty($date_range) || $date_range > 30 || $date_range < -30 ) $date_range = 3;
		$needdays1 = processDay($std_date, $date_range);
		file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid std_date=$std_date date_range=$date_range " . var_export($needdays1, true) . "\n", FILE_APPEND);
		$check_date_sql="select distinct logdate from login_info_stats where uid='$uid'";
		$dates_res=$page->execute($check_date_sql,3);
		$exist=$dates_res['ret']['data'];
		foreach($exist as $rec ){
			$exist_days[]=strval($rec['logdate']);
		}
		$inter=array_intersect($needdays1,$exist_days);
		$not_exist=array_diff($needdays1,$inter);
		$yangbenParam = array();
		$stats = array();
		$stats2 = array();
		$features = array("deviceId","pf","googlePlay","fromCountry","lang","platform","device_info");
		//dev
		if(count($not_exist) > 0 ) 		file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid std_date=$std_date date_range=$date_range not_exist= " . var_export($not_exist, true) . "\n", FILE_APPEND);
		foreach ($not_exist as $day) {
			$format_day = date('Y-m-d',strtotime($day));
			$cmd = 'grep "LoginEventHandler'.'|'.$uid.'" /usr/local/cok/SFS2X/logs/smartfox.log.'.$format_day.'-** | awk -F"|" '."'{print $9}'";//			grep 'LoginEventHandler|10016531087000001' /usr/local/cok/SFS2X/logs/smartfox.log.2017-11-29-** |head -n1| awk -F"|" '{print $6,$7,$8,$9}'
			unset($res);
			exec($cmd, $res);
//			file_put_contents(FILE_ROOT . '/loginCheck.log',date('Ymd H:i:s') . " sid=$sid uid=$uid " . 'cmd='.$cmd.'  count=' . count($res) . ' res output to ' . FILE_ROOT . '/mongoRes.json' . "\n", FILE_APPEND);
//			file_put_contents(FILE_ROOT . '/mongoRes.json', implode("\n", $res));
			if (count($res) == 0) {
//				file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . "colName=$colName sid=$sid,uid=$uid res=null" . "\n", FILE_APPEND);
				continue;
			}
			foreach ($res as $json) {
				$json_arr = json_decode($json, true);
				if($json_arr['device_info']){
					unset($model);
					$model=$json_arr['device_info']['model'];
				}
				unset($json_arr['device_info']);
				$json_arr['device_info']=$model;
				$yangbenParam[$day][] = $json_arr;
			}
		}
		//online
		/*foreach ($not_exist as $day) {
			$colName = 'action_server' . $sid . "_$day";
			$cmd = 'mongoexport  -h 10.84.187.142  -d log -c ' . $colName . ' -q \'{"action":"LoginEventHandler","uid":"' . $uid . '"}\' ';//	mongoexport  -h 10.84.187.142  -d log -c action_server9_20171006 -q '{"action":"LoginEventHandler","uid":"1102735838000009"} ' -o mongoRes.json
			unset($res);
			exec($cmd, $res);
//			file_put_contents(FILE_ROOT . '/loginCheck.log',date('Ymd H:i:s') . " sid=$sid uid=$uid " . 'mongo searched res count=' . count($res) . ' res output to ' . FILE_ROOT . '/mongoRes.json' . "\n", FILE_APPEND);
//			file_put_contents(FILE_ROOT . '/mongoRes.json', implode("\n", $res));
			if (count($res) == 0) {
//				file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . "colName=$colName sid=$sid,uid=$uid res=null" . "\n", FILE_APPEND);
				continue;
			}
			foreach ($res as $json) {
				$json_arr = json_decode($json, true);
				if($json_arr['param']['device_info']){
					unset($model);
					$model=$json_arr['param']['device_info']['model'];
				}
				unset($json_arr['param']['device_info']);
				$json_arr['param']['device_info']=$model;
				$yangbenParam[$day][] = $json_arr['param'];
			}
		}*/

		foreach ($yangbenParam as $day => $param_arr) {
			foreach ($param_arr as $param) {
				foreach ($features as $k) {
					$yangben[$k] = $param[$k];
				}
				$device=$param['deviceId'];
				$instance = json_encode($yangben);
				$stats[$day][$device][$instance]++;
				$stats2[$device][$instance]++;
			}
		}
		foreach($needdays1 as $day){
			if(isset($stats[$day])) continue;
			$stats[$day]['none']['none']=0;//log proccessed day
		}
		foreach($stats as $day =>$device_instance_num){
			foreach($device_instance_num as $device=>$instance_num){
				foreach($instance_num as $instance=>$num){
					$md5_info = md5($instance);
					$inserted_values[] = "( '$uid','$device',$day,'$md5_info','$instance',$num)";
				}
			}
		}
		if(count($inserted_values) > 0) {
			$record_add = 'insert into login_info_stats values ' . implode(',', $inserted_values);//insert into login_info_stats values ( '123',20170910,'1','sdfsdfsdf',18),( '123',20170910,'2','sdfsdfsdf',18),( '123',20170912,'1','sdfsdfsdf',18),( '123',20170913,'2','sdfsdfsdf',18)
			$page->execute($record_add, 3);
			file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . ' record_add=' . $record_add . "\n", FILE_APPEND);
		}
//		file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . ' stats=' . var_export($stats, true) . "\n", FILE_APPEND);
//		file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . ' stats2=' . var_export($stats2, true) . "\n", FILE_APPEND);

        $begin = intval($date_range)>0 ?$needdays1[count($needdays1)-1]: $needdays1[0];
        $end=intval($date_range)>0 ?$needdays1[0]:$needdays1[count($needdays1)-1];
		$stat_query="select * from login_info_stats where uid='$uid' and logdate between {$begin} and {$end}";//select * from login_info_stats where uid='' and date between 20171128 and 20171129
		$query_result=$page->execute($stat_query,3);
        $query_result=$query_result['ret']['data'];
		file_put_contents(FILE_ROOT . '/loginCheck.log', date('Ymd H:i:s') . " sid=$sid uid=$uid " . ' stat_query=' . $stat_query .' query_result='.json_encode($query_result). "\n", FILE_APPEND);
		$stats = array();
		$stats2 = array();
		$features = array("pf","googlePlay","fromCountry","lang","platform","device_info");
		$device_map=array();
		$info_map=array();
		foreach($query_result as $record){
			if($record['deviceId'] == 'none' ) continue;
			//TODO process info for view
			$device_map1[$record['deviceId']]=1;
			$info_map1[$record['info']]=1;
			$stats[$record['logdate']][$record['deviceId']][$record['info']]+=$record['stats'];
			$stats2[$record['deviceId']][$record['info']]+=$record['stats'];
		}
		$device_map=value_idx($device_map1);
		$info_map=value_idx($info_map1);

//		$features_query = "select uid,deviceId from uid_device_features where uid='123'";
//		$all_devices=$page->execute($features_query,3);


		//1.sid uid deviceId date info  stats
		//2.sid uid deviceId deviceInfofeatures  only for android ?
		/**
		"screen_h" : 1440,
		"screen_w" : 810,
		"screen_density" : 360,
		"os_build" : "MMB29K.G610YZTU1AQD7",
		"os_version" : "6.0.1",
		"model" : "SM-G610Y",
		 */
		//3.sid uid deviceId otherFeatures  for ios? could be in step 1
		//TODO 4 add to MongoException for monitor when found android  not accompliant with features saved!!!!





	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );

