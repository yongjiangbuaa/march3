<?php
date_default_timezone_set('Asia/Shanghai');
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include ADMIN_ROOT.'/menu_config.php';
include_once ADMIN_ROOT.'/servers.php';
ini_set('memory_limit', '512M');
set_time_limit(0);
global $servers;
header("Content-type:text/html;charset=utf-8");

//载入PHPMailer类 
require_once('include/class.phpmailer.php'); 
$mail = new PHPMailer(); //实例化 
$mail->IsSMTP(); // 启用SMTP 
$mail->Host = "smtp.163.com"; //SMTP服务器 以163邮箱为例子 
$mail->Port = 25;  //邮件发送端口 
$mail->SMTPAuth   = true;  //启用SMTP认证 
$mail->CharSet  = "UTF-8"; //字符集 
$mail->Encoding = "base64"; //编码方式 
$mail->Username = "cokdetect@163.com";  //你的邮箱 
$mail->Password = "hcgcok";  //你的密码 
//邮件标题 
$mail->From = "cokdetect@163.com";  //发件人地址（也就是你的邮箱） 
$mail->FromName = "COK玩家数据监测";  //发件人姓名 
$mail->IsHTML(true); //支持html格式内容 

$page = new BasePage();

$checkArr = array(
		2	=> array('ulv'=>15, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>null),
		3	=> array('ulv'=>17, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>20000),
		7	=> array('ulv'=>19, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>50000),
		14	=> array('ulv'=>21, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>100000),
		20	=> array('ulv'=>23, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>200000),
		30	=> array('ulv'=>25, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>300000),
		365	=> array('ulv'=>28, 'gold'=>100000, 'wood'=>20000000, 'food'=>20000000, 'iron'=>4000000, 'stone'=>1000000, 'soldiers'=>1000000),// >30 days
);
$itemname = array(
		'ulv'=>'领主等级',
		'blv'=>'大本等级',
		'gold'=>'金币合计',
		'gameGold'=>'游戏金币',
		'paidGold'=>'购买金币',
		'payTotal'=>'购买金币总和',
		'wood'=>'木头',
		'food'=>'粮食',
		'iron'=>'铁矿',
		'stone'=>'秘银',
		'soldiers'=>'兵力',
		'regTime'=>'注册时间',
);
//7天内登录过
$lastlogin = (time() - 7*86400) * 1000;

// userprofile
// user_resource
// user_army
// user_building

$messages = array();
foreach ($servers as $currentServer=>$content){
    $startTime = time();
    $totalNum = 0;
    $problemArr1 = array();
    $problemArr2 = array();
    $problemArr3 = array();
    $predaytime = time()*1000;
    foreach ($checkArr as $day=>$limit){
        $regtime = getRegTime($day);
        
        $sql = "select u.uid,u.name,u.regTime,u.level ulv,(u.gold+u.paidGold) gold,u.gold gameGold,u.paidGold paidGold,u.payTotal payTotal,c.stone,c.wood,c.food,c.iron
        	from userprofile u inner join user_resource c on u.uid = c.uid
        	where u.lastOnlineTime > $lastlogin and u.regTime > $regtime and u.regTime <= $predaytime and u.gmFlag !=1 and u.gmFlag !=10";
        $ret = $page->executeServer($currentServer, $sql, 3);
        $result = $ret['ret']['data'];
        $totalNum += count($result);
        echo "user_resource day $day count ".count($result).PHP_EOL;
        foreach ($result as $record){
        	$uid = $record['uid'];
            foreach ($record as $item=>$data){
                if (isset($limit[$item]) && $limit[$item]!==null) {
                	if ($data >= $limit[$item]) {
//                 	    if(!isset($problemArr1[$uid])){
                       		$messages[$item][] = array($day, $item, $currentServer, $record, $limit[$item]);
                       		echo json_encode($record).PHP_EOL;
                       		$problemArr1[$uid] = 1;
//                     	}
                	}
                }
            }
        }

        $predaytime = $regtime;
    }
    echo $currentServer.' , total:'.$totalNum.'  at '.date('Y-m-d H:i:s').' success! '.' 用时: '.(time()-$startTime).'s   |  异常总计:'.(count($problemArr1)+count($problemArr2)+count($problemArr3))."\n";
}
if (count($messages) > 0) {
	sendMail($messages);
}

exit('success over!'."\n");

function dosendMail($subject,$content){
	sleep(10);
	global $mail;
	$mail->Subject = $subject;
	$mail->AddAddress('zhengze@elex-tech.com');
	$mail->AddAddress('yutao@elex-tech.com');
	$mail->AddAddress('wangzhiyuan@elex-tech.com');
	$mail->AddAddress('liyongjun@elex-tech.com');
	$mail->AddAddress('lifangkai@elex-tech.com');
	$mail->AddAddress('zhaohongfu@elex-tech.com');
	$mail->AddAddress('wulingjiang@elex-tech.com');
	$mail->AddAddress('zhengcheng@elex-tech.com');
	$mail->AddAddress("wangxianwei@elex-tech.com");
	
	$mail->Body = $content;
	if(!$mail->Send()) {
		sleep(60);
		if(!$mail->Send()) {
			echo "Mailer Error: " . $mail->ErrorInfo .'<br />';
		}
	}
}
function sendMail($maillist){
	//$item, $server, $record
	global $itemname;
	$table = array();
	foreach ($maillist as $itemkey=>$list) {
		foreach ($list as $one) {
			$day = $one[0];
			$item = $one[1];
			$server = $one[2];
			$record = $one[3];
			$limit = $one[4];
			$pay = $record['paidGold']>0?'paid':'nopaid';
			$row = array();
			$row['服'] = $server;
			$row['注册天数(<=)'] = $day;
			$row['异常'] = $itemname[$item];
//	 		$row['说明'] = '>= '.$limit;
			foreach ($record as $key=>$val) {
				$name = isset($itemname[$key])?$itemname[$key]:$key;
				if ($key == 'regTime') {
					$val = date('Y-m-d H:i:s', intval($val/1000));
				}
				if (in_array($key, array('wood','food','iron'))) {
					$val = intval($val/1000000);
				}
				if (in_array($key, array('stone'))) {
					$val = round($val/1000000,1);;
				}
				$row[$name] = $val;
			}
			$table[] = $row;
		}
	}
	
	$subject = '异常数据-等级资源-所有服('.date('Y-m-d').')';
	$data = '';
	$tableview = arraylist_to_htmltable($table, 'No.', '', false);
	$data .= '<br>'.$tableview;
	$data .= '<br><br>----<br>'.date('Y-m-d');
	$content = $subject." <br><br> ".$data;
	dosendMail($subject, $content);
}

function getRegTime($num){
	return (time() - $num * 24 * 3600) * 1000;
}

function arraylist_to_htmltable2($array_list, $indexname='INDEX', $spacechar='', $withindex = true){
	$keys = array();
	$cellstyle = "style='border: 1px solid #BEBFB9;text-align: right;padding: 3px 3px 3px 3px;'";
	$cellstyle2 = "style='border: 1px solid #BEBFB9;padding: 3px 3px 3px 3px;'";
	//$keys = array_keys(end($array));
	foreach ($array_list as $arr) {
		$keys = array_merge($keys,array_keys($arr));
	}
	$keys = array_unique($keys);
	$row = '<tr>';
	if ($withindex) {
		$row .= "<th $cellstyle>$indexname</th>";
		$row .= "<th $cellstyle>Total</th>";
	}
	$thkeys = array_keys($array_list);
	foreach ($thkeys as $key) {
		$row .= "<th $cellstyle>$key</th>";
	}
	$row .= '</tr>';
	$rows[] = $row;
	foreach ($keys as $key) {
		$to = 0;
		$rowsp = '';
		foreach ($array_list as $k=>$arr) {
			if (isset($arr[$key])) {
				$to += $arr[$key];
				$rowsp .= "<td $cellstyle>$arr[$key]</td>";
			}else{
				$rowsp .= "<td $cellstyle>$spacechar</td>";
			}
		}
		$row = '<tr>';
		if ($withindex) {
			$row .= "<td $cellstyle2>$key</td>";
			$row .= "<td $cellstyle>$to</td>";
		}
		$row .= $rowsp;
		$row .= '</tr>';
		$rows[] = $row;
	}

	return "<table style='margin: 0px 0px 10px;border-spacing: 0;'>".implode('', $rows)."</table>";
}
/**
 * 以子数组key作为 行。
 * @param $array_list
 * @param $indexname
 * @param $spacechar
 */
function arraylist_to_htmltable($array_list, $indexname='INDEX', $spacechar='', $withindex = true){
	$keys = array();
	$cellstyle = "style='border: 1px dotted #BEBFB9;'";
	//$keys = array_keys(end($array_list));
	foreach ($array_list as $arr) {
		$keys = array_merge($keys,array_keys($arr));
	}
	$keys = array_unique($keys);
	$row = '<tr>';
	if ($withindex){
		$row .= "<th $cellstyle>$indexname</th>";
	}
	foreach ($keys as $key) {
		$row .= "<th $cellstyle>$key</th>";
	}
	$row .= '</tr>';
	$rows[] = $row;
	foreach ($array_list as $k=>$arr) {
		$row = '<tr>';
		if ($withindex){
			$row .= "<td $cellstyle>$k</td>";
		}
		foreach ($keys as $key) {
			if (isset($arr[$key])) {
				$row .= "<td $cellstyle>$arr[$key]</td>";
			}else{
				$row .= "<td $cellstyle>$spacechar</td>";
			}
		}
		$row .= '</tr>';
		$rows[] = $row;
	}

	return "<table style='margin: 0px 0px 10px;border-spacing: 0;'>".implode('', $rows)."</table>";
}
//https://basecamp.com/1792529/projects/3602492/todos/138290294