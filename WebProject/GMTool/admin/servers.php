<?php
!defined('IN_ADMIN') && exit('Access Denied');

$host = gethostbyname(gethostname());
if ($host == '10.1.16.211' || $host == '10.173.2.11') {
	defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 0);//inner test
}elseif ($host == '91-87'){
	defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 9);//online
}else{
	defined('PRODUCT_SEVER_TYPE') || define('PRODUCT_SEVER_TYPE', 1);//online test
}

if (PRODUCT_SEVER_TYPE == 0) {
	$servers = array(
			'global'=>array(
					'sig_auth_key'=>'',
					'sig_api_key'=>'ik2@global',
					'lang'=>'cn',
					'gateway'=>'http://10.1.16.211:8081/ifadmin/',
					'timezone'=>'GMT',
//					'timezone'=>'Asia/Shanghai',
					'webbase'=>'http://10.1.16.211:8000/gameservice/',
			),

//			's1'=>array(
//					'sig_auth_key'=>'',
//					'sig_api_key'=>'ik2@qq_cn_1',
//					'lang'=>'cn',
//					'gateway'=>'http://10.1.16.211:8081/ifadmin/',
//					'timezone'=>'Asia/Shanghai',
//					'webbase'=>'http://10.1.16.211:8080/gameservice/',
//					'ip_inner'=>'10.1.16.211',
//			),
// 			's2'=>array(
// 					'sig_auth_key'=>'',
// 					'sig_api_key'=>'ik2@qq_cn_1',
// 					'lang'=>'cn',
// 					'gateway'=>'http://10.1.16.211:8081/ifadmin/',
// 					'timezone'=>'Asia/Shanghai',
// 					'webbase'=>'http://10.1.16.211:8080/gameservice/',
//					'ip_inner'=>'10.1.16.211',
// 			),
	);
	$server_list = get_server_list();
	foreach ($server_list as $record) {
		$id = $record['svr_id'];
		$ip = $record['ip_pub'];
		$name = 's'.$id;
		$si = array (
			'sig_auth_key'=>'',
			'sig_api_key'=>'ik2@qq_cn_1',
			'lang' => 'cn',
			'gateway' => 'http://10.1.16.211:8081/ifadmin/',
//			'timezone' => 'Asia/Shanghai',
			'timezone' => 'GMT',
			'webbase' => "http://$ip:8080/gameservice/",
			'ip_inner' =>  $record['ip_inner'],
		);
		$si = array_merge($record, $si);
		$servers[$name] = $si;
	}
}
if (PRODUCT_SEVER_TYPE == 1) {
	$servers = array (
			'global' => array (
					'sig_auth_key' => '',
					'sig_api_key' => 'if@global',
					'lang' => 'en',
					'gateway' => 'http://aok-gm.elexapp.com/admin/',
					'timezone' => 'GMT',
					'webbase' => 'http://aok-1.elexapp.com:8080/gameservice/'
			),
//			'test' => array (
//					'sig_auth_key' => '',
//					'sig_api_key' => 'if@google_en_1',
//					'lang' => 'en',
//					'gateway' => 'http://aok-gm.elexapp.com/admin/',
//					'timezone' => 'GMT',
//					'webbase' => 'http://aok-gsl.elexapp.com/gameservice/'
//			),
			's1' => array (
					'sig_auth_key' => '',
					'sig_api_key' => 'if@google_en_1',
					'lang' => 'en',
					'gateway' => 'http://aok-gm.elexapp.com/ifadmin/',
					'timezone' => 'GMT',
					'webbase' => 'http://s1.coq.elexapp.com:8080/gameservice/'
			),
	);
}

if (PRODUCT_SEVER_TYPE == 9) {
	$servers = array (
			'global' => array (
					'lang' => 'en',
					'gateway' => 'http://p1coq.elexapp.com/ifadmin/',
					'timezone' => 'GMT',
					'webbase' => 'http://s1.coq.elexapp.com:8080/gameservice/'
			),
	);
	$server_list = get_server_list();
	foreach ($server_list as $record) {
		$id = $record['svr_id'];
		if ($id == 0) {
//			continue;
		}
// 		if ($id > 900000) {
// 			continue;
// 		}
		$ip = $record['ip_pub'];
		$name = 's'.$id;
		$si = array (
					'lang' => 'en',
					'gateway' => 'http://p1coq.elexapp.com/ifadmin/',
					'timezone' => 'GMT',
					'webbase' => "http://$ip:8080/gameservice/",
					'ip_inner' =>  $record['ip_inner'],
			);
		$si = array_merge($record, $si);
		$servers[$name] = $si;
	}
}
// file_put_contents('/tmp/changdb.log', print_r($servers,true)."\n",FILE_APPEND);


$oServers = $servers;
unset($servers['global']);
