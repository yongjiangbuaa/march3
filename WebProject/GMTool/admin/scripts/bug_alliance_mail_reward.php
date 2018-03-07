<?php
//added by qinbin
//根据联盟id,和礼包id  补发漏下的奖励,这次估计有7万人....
// 20160712

define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '2048M');

echo '0000'.PHP_EOL;

$alliaceArr=array(
array('s34','71889','83ad6ae0121c4b0891b77ac0f7ae565e'),
array('s48','71860','5a74432c4fd54fa59e15bc3cd022fb8f'),
array('s48','71764','321c85bc1c984e1282984e5633b5bdbc'),
array('s49','71764','7a51fb2a734940ff939db49fadd5eaad'),
array('s49','71765','7a51fb2a734940ff939db49fadd5eaad'),);

$fix_mailconfig = array(
	'11500'=>array('sender'=>'3000002','title'=>'105726','message'=>'105731','type'=>'2','reward'=>'honor,0,1000|alliance_point,0,1000|goods,200301,2'),
	'11501'=>array('sender'=>'3000002','title'=>'105727','message'=>'105736','type'=>'2','reward'=>'honor,0,3000|alliance_point,0,3000|goods,200301,4'),
	'11502'=>array('sender'=>'3000002','title'=>'105728','message'=>'105737','type'=>'2','reward'=>'honor,0,5000|alliance_point,0,5000|goods,200302,2'),
	'11503'=>array('sender'=>'3000002','title'=>'105729','message'=>'105738','type'=>'2','reward'=>'honor,0,7000|alliance_point,0,7000|goods,200301,8'),
	'11504'=>array('sender'=>'3000002','title'=>'105730','message'=>'105739','type'=>'2','reward'=>'honor,0,10000|alliance_point,0,10000|goods,200303,1'),
);

$exchageXml = loadXml1('exchange','exchange');
echo '数组大小'.count($alliaceArr).PHP_EOL;

$people=array();
foreach ($alliaceArr as $item) {
	echo print_r($item,true).PHP_EOL;
	$server = $item[0];
	if($server == 's2') {
		continue;
	}
	$sql = "select uid from alliance_member where allianceId='" . $item[2] . "'";
	$resultsel = $page->executeServer($server, $sql, 3);
	$mailItem=array();
	$id = intval($exchageXml[$item[1]]['gift_mail']);
	if(in_array($id,array_keys($fix_mailconfig))){
		$mailItem = $fix_mailconfig[$id];
	}else{
		echo 'exchange表的gift_mail值不在数组内'.print_r($item,true).PHP_EOL;
		exit();
	}
	foreach ($resultsel['ret']['data'] as $cow) {

		$toUser = $cow['uid'];
		echo $server.'_'.$toUser.PHP_EOL;//输出每个人
		$lajiUser = array('10071888460000034',
			'10105686425000034',
			'10314489958000034',
			'10316148864000034',
			'10440212339000034',
			'10468916893000034',);
		if(in_array($toUser,$lajiUser) && $server='s34'){
			continue;
		}
		if ($toUser) {
			$sender = $mailItem['sender'];
			$type = $mailItem['type'];
			$reward = $mailItem['reward'];
			$sendBy = '';
			$sendTime = floor(microtime(true) * 1000);
			$title = $mailItem['title'];//
			$contents = $mailItem['message'];//
			$uid = md5($toUser . $sendBy . $sendTime . $title . $contents . $reward . time());
			if ($reward)
				$rewardStatus = 0;
//srctype
//			$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', '$sender', 0, $type, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 1, 1)";
			$insert_return = $page->executeServer($server, $sql, 2);
			if ($insert_return['error']) {
				trackLog('error_ '.$sql);
			}
			$page->webRequest('sendmail', array('uid' => $uid), $server);
		} else {
			echo 'touser read fail';
		}
	}
}

function trackLog($message){
	$file = '/tmp/sunwei_alliance_'.date('Ymd').'.log';
	file_put_contents($file, "$message"."\n", FILE_APPEND);
}
function loadXml1($xmlName,$groupName){
	$arr = array('kill_titan','exchange','quest','quest_mark','mark','general','events','item',);

	if(in_array($xmlName,$arr)){
		$path = "/data/htdocs/resource/$xmlName.xml";
		if(!file_exists($path)){
			$path = ADMIN_ROOT."/xml/$xmlName.xml";  //不能用自带是因为,admin_root 定义的item_root 没法共同使用
		}
	}else{
		$path = ADMIN_ROOT."/xml/$xmlName.xml";
	}
	$items = simplexml_load_file($path);
	if($groupName){
		$nodes = $items->xpath('/tns:database//Group[@id=\''.$groupName.'\']');
		$items = $nodes[0];
	}
	foreach ($items as $item)
	{
		$itemDetail[(int)$item['id']] = $item;
	}
	return $itemDetail;
}