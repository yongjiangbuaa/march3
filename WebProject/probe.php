<?php
// 从文件缓存中取得 各服的内网ip 等信息
// 从redis中读取服的状态，并判定返回系统状态
// redis-cli -h 10.41.163.20 get ServerStatus:S1

define('ROOT', __DIR__);
define('PATH_CACHE_FILE', '/data/htdocs/cache');
define('FILE_SERVERS_XML', '/data/htdocs/resource/servers.xml');
define('TIME_STOP_SPAN_EXTRA', 600);//seconds

date_default_timezone_set('UTC');

$serverId = getParameter("serverId");
$p_serverIp = getParameter("serverIp");
$gameuid = getParameter("gameuid");
$lang = getParameter("lang");
$deviceId = getParameter("uuid");
$gmFlagPara = getParameter("gmFlag");
$loginFlagPara = getParameter("loginFlag");
$pf = getParameter("pf");
$pfId = getParameter("pfId");
$playerCountry = getParameter("country");
$extra = getParameter("extra");

$gmFlag = ("1"==$gmFlagPara);

$code = 0;
$messageId = 0;
$timelist = 0;
$status = 0;
$redisPort = 6379;

//stopped_servers.txt 手工控制，优先级最高，用于应急。（比如sfs服宕机）
$s_parts = array();
$stopped_servers = file_get_contents('stopped_servers.txt');
$stopped_servers = trim($stopped_servers);
if (empty($stopped_servers)) {
	$s_parts[] = array(0, 0);
}else{
	$arr1 = explode(',', $stopped_servers);
	foreach ($arr1 as $part) {
		if (empty($part)) {
			continue;
		}
		$arr = explode('-', $part);
		$arr[1] = empty($arr[1])?$arr[0]:$arr[1];
		$s_parts[] = $arr;
	}
}

$p_serverIp = trim($p_serverIp);
if ($p_serverIp[0] == 's') {
	$pos = strpos($p_serverIp, '.');
	$serverName = substr($p_serverIp, 0, $pos);
	$serverId = substr($serverName, 1);
	foreach ($s_parts as $range) {
		if($serverId >= $range[0] && $serverId <= $range[1] ){
			$status = 1;
			break;
		}else{
			$status = 0;
		}
	}
	$redisip = probe_get_server_ip_inner($serverId);
	if ($redisip === null) {
		$status = 1;
	}
}else{
	$server_info = probe_get_server_info_by_serverId($serverId);
	if($server_info == null ){
		$server_info = prope_get_server_info_by_ip($p_serverIp);
		if($server_info != null){
			$serverId = $server_info["id"];
		}
	}
	$serverName = $p_serverIp;
	if($server_info != null && $server_info["ip"] == $p_serverIp){
		$redisip = $server_info["inner_ip"];
		if ($redisip == null) {
			$status = 1;
		}
	}elseif ($p_serverIp == '169.45.149.74' ){
		$serverName = $p_serverIp;
		$serverId = 1;
		$redisip = '10.121.248.40';
	}
	/*
	elseif ($p_serverIp == '10.1.16.211' || $p_serverIp == '10.1.6.72'){
		$serverName = $p_serverIp;
		$serverId = 1;
		$redisip = $p_serverIp;
	}
	*/
	else {
		$serverName = $p_serverIp;
		$data = array(
			'cokerrcode' => 0,
			'message' => 'OK.',
			'timelist' => $timelist,
		);
		$ret = json_encode($data);
		trackLog('INFO', "code=0 messageId=0 empty");
		echo $ret;
		exit(0);
	}
}

// check server status
if ($status == 0) {
	$client = new Redis();
	$r = $client->connect($redisip, $redisPort, 3);
	
	// [unreachable server. if the Redis service is down, or if the redis host is overloaded]
	if ($r === false) {
		trackLog('ERROR', "$redisip unreachable. server or redis service is down? host is overloaded?");
		$status = 2;
		$stop_starttime = time();
		$stop_endtime = 0;
	}else{
		$rediskey_status = "ServerStatus:S".$serverId;
		$rediskey_stop_starttime = "ServerStatus:S".$serverId.":StopStartTime";
		$rediskey_stop_endtime = "ServerStatus:S".$serverId.":StopEndTime";
		$redis_keys = array($rediskey_status, $rediskey_stop_starttime, $rediskey_stop_endtime);
		$redis_values = $client->mget($redis_keys);
		$status = intval($redis_values[0]);
		$stop_starttime = intval($redis_values[1]);
		$stop_endtime = intval($redis_values[2]);
	}
	
	// 容错矫正
	if ($stop_starttime <= $stop_endtime || time() < $stop_starttime) {
		$status = 0;
	}
}

if ($status > 0) {
	// read from file System Maintenance
	$maintenance_plan = file_get_contents('maintenance_plan.txt');//type=time
	$maint_plan = trim($maintenance_plan);
	if (!empty($maint_plan)) {
		$plandef = explode('=', $maint_plan);
		$maint_type = $plandef[0];//1:verup; 2:short maint; 3:long maint
		$maint_time_span = $plandef[1];
	}else{
		$maint_type = 1;//1:verup
		$maint_time_span = 1200;
	}
	
	$now = time();
	$ramain = ($stop_starttime + $maint_time_span - $now);
	$segs = 1;
	while ($ramain < 0) {
		$ramain += TIME_STOP_SPAN_EXTRA;
		$segs++;
	}
	
	if ($maint_type == 1) {
		$timelist = 0;
		$messageId = 1;
	}
	
	if ($maint_type == 2) {
		$timelist = $ramain;
		$messageId = 2;
	}
	
	if ($maint_type == 3) {
		$timelist = $ramain;
		$messageId = 3;
		if ($segs > 1) {
			$messageId = 4;
		}
	}
}

// 0:ok/restarted; 1:stopping; 2:stopped;3:starting
switch ($status) {
	case 0:
		$code = 0;
		break;
	case 1:
	case 2:
	case 3:
		$code = 1;
		break;
	case 9:
		$code = 9;
		break;
	default:
		$code = 0;
		break;
}

$message = get_message($messageId, $lang);
$data = array();
$data['cokerrcode'] = $code;
$data['message'] = $message;
if ($code > 0) {
	$data['timelist'] = $timelist;
}

$ret = json_encode($data);

if ($code > 0) {
	trackLog('INFO', "code=$code messageId=$messageId $timelist maintcost=".(time()-$stop_starttime));
}else{
	trackLog('INFO', "code=$code messageId=$messageId");
}

// return to client.
echo $ret;

exit(0);

//

function get_message($messageId, $lang){
	$lang = strtolower($lang);
	$message_def = array(
			0=>array('en'=>'OK'),
			1=>array(
					'zh_CN' => '服务器维护中，请稍后登录！',
					'zh_TW' => '伺服器維護中，請稍後登入！',
					'en' => 'Server is undergoing maintenance. ',
					'fr' => 'Nous allons faire une pause de maintenance bientôt (elle devrait être assez courte). A très bientôt, Mon Seigneur!',
					'ru' => 'В ближайшее время у нас будет перерыв на обслуживание(должно быть довольно коротким). Скоро увидимся, мой лорд!',
					'ja' => 'メンテナンス中',
					'ko' => '서버 유지보수중이오니 잠시후 로그인해주세요!',
					'de' => 'Wir haben jetzt eine WWir haben jetzt eine Wartungspause. Bitte logge dich später erneut ein!',
					'es' => 'Próximamente llevaremos a cabo un descanso de mantenimiento (será poco tiempo). ¡Nos veremos pronto, mi señor!',
					'id' => 'Kita akan Main Tenis sebentar  gan! (Diusahakan Secepat Mungkin). Sampai ketemu secepatnya, Rajaku!',
					'tr' => 'Yakında bakım arası vereceğiz(çok kısa olacak). Çok yakında görüşürüz, Lordum!',
					'th' => 'เซิร์ฟเว่อร์กำลังปรับปรุง  อีกสักครู่ค่อยล็อคอินใหม่！',
					'pt' => 'Servidor está passando por manutenção. Por Favor faça login novamente mais tarde!',
			),
			2=>array(
					'zh_CN' => '服务器临时维护，倒计时结束后即可进入游戏',
					'zh_TW' => '伺服器臨時維護，倒計時結束後即可進入遊戲。',
					'en' => 'Our server is in temporary maintenance now. You can log in to the game when countdown clock hits zero',
					'fr' => 'la maintenance du serveur provisoire, connectez-vous après le comptage décroissant.',
					'ru' => 'компенсация за обслуживание сервера.после того вы можете заити в игру',
					'ja' => 'メンテナンス中、カウントが終了するとログインができます。',
					'ko' => ' 서버 임시 점검, 카운트다운 이후 게임 시작',
					'de' => ' Die Wartungspause beginnt. Nach dem Count-down startet das Spiel.',
					'es' => ' El servidor está temporalmente en mantenimiento. Podrás ingresar al juego cuando el temporizador llegue a cero.',
					'id' => 'Our server is in temporary maintenance now. You can log in to the game when countdown clock hits zero',
					'tr' => 'Sunucularımızı şu anda geçici bir bakım altına aldık. Oyuna geri sayım saati sıfırı vurduğu zaman tekrar girebileceksiniz',
					'th' => 'ซ่อมแซมเซิร์ฟเวอร์ชั่วคราว  สิ้นสุดนับเวลาถอยหลังจะเข้าสู่เกม',
					'pt' => 'No momento estamos passando por uma manutenção temporária em nosaos servidores. Você poderá acessar ao jogo quando nosso cronômetro chegar a zero.',				
			),
			3=>array(
					'zh_CN' => '服务器维护中，请在倒计时结束后再尝试登陆，维护期间，所有城堡都会进入保护状态！',
					'zh_TW' => '伺服器維護中，請在倒計時結束後再嘗試登入。維護期間，所有城堡都會進入保護狀態！',
					'en' => 'Our server is in maintenance now. Please try to log in to the game when countdown clock hits zero. We will open peace shield for your castle during the period of maintenance. ',
					'fr' => 'la maintenance du serveur, veuillez rejoudre après la fin du comptage décroissant.pendant la durée de la maintenance du serveur, tous les châteaux sont en état de protection!',
					'ru' => 'в течение обслуживания,подождите пожалуйста.в течение обслуживания,замок будет вступать в статус мира!',
					'ja' => 'メンテナンス中、カウントが終了してから再度お試し下さい。メンテナンス期間、全ての主城は保護状態になります。',
					'ko' => '서버 임시 점검 , 카운트다운 이후 로그인을 시도하세요. 서버 점검 시간동안 모든 캐슬은 보호상태에 있습니다.',
					'de' => 'Es ist jetzt die Wartungspause. Starte das Spiel nach dem Count-down. Während der Wartungspause werden alle Burge geschützt!',
					'es' => 'El servidor ahora está en mantenimiento. Por favor intenta ingresar al juego cuando el temporizador llegue a cero. Se activará un escudo de paz durante el mantenimiento.',
					'id' => 'Our server is in maintenance now. Please try to log in to the game when countdown clock hits zero. We will open peace shield for your castle during the period of maintenance. ',
					'tr' => 'Şuan sunucularımıza bakım uygulamaktayız. Bakım esnasında kalelerinizi korumak için Barış Kalkanlarını açtık. Lütfen geri sayım saati sıfırı vurduğunda oyuna girmeyi tekrar deneyin.',
					'th' => 'ระหว่างซ่อมแซมเซิร์ฟเวอร์ โปรดล็อกอินใหม่อีกครั้งหลังสิ้นสุดนับเวลาถอยหลัง  ช่วงเวลาปรับปรุง ทุกๆปราสาทจะเข้าสู่สถานะคุ้มครอง！',
					'pt' => 'Estamos em manutenção agora. Ativamos um Escudo de Proteção para proteger seu castelo durante a manutenção. Por favor, tente acessar novamente depois que o cronômetro chegar a zero.',				
			),
			4=>array(
					'zh_CN' => '万分抱歉，服务器升级尚未完成，请耐心等候，稍后我们会有大礼奉上！',
					'zh_TW' => '十分抱歉，伺服器升級尚未完成，請耐心等候，稍後我們會有大禮奉上！',
					'en' => 'Sorry! Our server maintenance has not finished yet. Please wait in patience. We will provide gift for you after the maintenance!',
					'fr' => 'Désolés de vous déranger pour la maintenance du serveur, veuillez-vous patienter et nous vous préparons des cadeaux bientôt!',
					'ru' => 'Пропустите пожалуйста.обслуживание ещё не окончивается.подождите пожалуйста.после того мы будем отправить награду!',
					'ja' => 'メンテナスによりご迷惑をおかけして大変申し訳ございませんでした。サービス向上のためご理解くださいますようお願い申し上げます。',
					'ko' => '너무 죄송합니다. 서버 업데이트를 아직 완성하지 못하였습니다. 잠시만 기다려주세요. 서버 점검 이후 큰 보너스를 드릴예정입니다.',
					'de' => 'Entschuldigung, dass der Server noch nicht fertig aktualisiert wird. Bitte warten Sie auf uns geduldig. Wir werden Ihnen bald eine gute Belohnung schenken!',
					'es' => 'El mantenimiento aun no ha concluido. Por favor espera con paciencia. ¡Te obsequiaremos una compensación después de terminado!',
					'id' => 'Sorry! Our server maintenance has not finished yet. Please wait in patience. We will provide gift for you after the maintenance!',
					'tr' => 'Üzgünüz! Bakım çalışmamız daha sona ermedi. Lütfen sabırla bekleyin. Sunucularımız tekrardan aktif olduğunda sizlere ücretsiz hediyeler göndereceğiz!',
					'th' => 'ขออภัยอย่างสูง เซิร์ฟเวอร์อัพเกรดยังไม่เสร็จสิ้น กรุณาอดใจรอ สักครู่พวกเรามีของขวัญตอบแทนท่าน！',
					'pt' => 'Sentimos muito! Nossa manutenção não foi concluída ainda. Por favor, seja paciente. Nós iremos enviar alguns presentes para você quando nossos servidores estiverem ativos novamente.',				
			),
			9=>array('en'=>'SYSTEM ERROR.'),
	);
	if (isset($message_def[$messageId][$lang]) && !empty($message_def[$messageId][$lang])){
		return $message_def[$messageId][$lang];
	}else{
		return $message_def[$messageId]['en'];
	}
}
function getParameter($p){
	return strval($_REQUEST[$p]);
}
function trackLog($type, $message){
	global $serverId,$serverName,$deviceId,$gmFlagPara,$loginFlagPara, $pf, $pfId, $playerCountry, $gameuid,$lang,$extra;
	$ip = get_ip();
	$format = "[%s],[%s],[serverId=%s],[serverName=%s],[gameuid=%s],[deviceId=%s],[gmFlag=%s],[loginFlag=%s],[pf=%s],[pfId=%s],[country=%s],[lang=%s],[%s],[extra=%s],[ip=%s]";
	$logmsg = sprintf($format,
			date('Y-m-d H:i:s'), $type, $serverId, $serverName, $gameuid, $deviceId, $gmFlagPara,$loginFlagPara, $pf, $pfId, $playerCountry, $lang,
			$message, $extra, $ip
	);
	$logdir = '/data/log/probe/';
	if (!file_exists($logdir)) {
		mkdir($logdir, 0777, true);
	}
	$file = $logdir.'/'.date('Ymd').'.log';
	file_put_contents($file, $logmsg."\n", FILE_APPEND);
}
function probe_get_server_ip_inner($serverId){

	$server_info = probe_get_server_info_by_serverId($serverId);
	return $server_info['inner_ip'];
}

function probe_get_server_info_by_serverId($serverId){
	// read from cached file. 'cache/serverinfo/$serverName'
	if(empty($serverId))
		return null;
	$cache_path = PATH_CACHE_FILE.'/serverinfo';
	if (!file_exists($cache_path)) {
		mkdir($cache_path,0777,true);
	}

	$cache_file = "$cache_path/$serverId.json";
	if(file_exists($cache_file)){
		$cache_time = filemtime($cache_file);
	}else{
		$cache_time = 0;
	}
	$xml_time = filemtime(FILE_SERVERS_XML);

	if ($cache_time < $xml_time) {
		$server_list = get_server_xml_config();
		$server_info = $server_list[$serverId];
		if (empty($server_info)) {
			return null;
		}
		file_put_contents($cache_file, json_encode($server_info), LOCK_EX);
		chmod($cache_file, 0777);
	}else{
		$cache_data = file_get_contents($cache_file);
		$server_info = json_decode($cache_data, true);
	}
	return $server_info;
}

function prope_get_server_info_by_ip($ip){
	$server_list = get_server_xml_config();
	foreach($server_list as $info){
		if($info["ip"] == $ip){
			return $info;
		}
	}
	return null;
}
function get_server_xml_config(){
	$xml = simplexml_load_file(FILE_SERVERS_XML);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);
	$spec = $array['Group']['ItemSpec'];
	if (count($spec) == 1) {
		$serverList[$spec['@attributes']['id']] = $spec['@attributes'];
	}else{
		foreach ($spec as $svr) {
			$serverList[$svr['@attributes']['id']] = $svr['@attributes'];
		}
	}
	return $serverList;
}
function get_ip() {
    if (_valid_ip($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
        if (_valid_ip(trim($ip))) {
            return $ip;
        }
    }
    if (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    } elseif (_valid_ip($_SERVER["HTTP_FORWARDED"])) {
        return $_SERVER["HTTP_FORWARDED"];
    } elseif (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
        return $_SERVER["HTTP_X_FORWARDED"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}
function _valid_ip($ip) {
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
