<?php
//added by qinbin
// 20160808

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


$alliaceArr=array(
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
    array('s13','1030174428000011','goods,200331,15|goods,200332,10|goods,200332,2'),
);

//$mailItem =array('sender'=>'3000002','title'=>'101031','message'=>'10014012','type'=>'2','reward'=>'goods,200331,15|goods,200332,10|goods,200332,2|goods,200301,15|goods,200302,10|goods,200303,2|goods,200321,15|goods,200322,10|goods,200323,2|goods,200311,15|goods,200312,10|goods,200313,2|goods,200200,10|goods,200202,1|goods,200203,30|goods,200204,10|goods,200416,2|goods,100001107,40');
$mailItem =array('sender'=>'3000002','title'=>'101031','message'=>'10014012','type'=>'2');

foreach ($alliaceArr as $item) {

	$server = $item[0];
    $toUser = $item[1];
    echo $server.'_'.$toUser.PHP_EOL;//输出每个人

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
//        $sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', '$sender', 0, $type, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 1, 1)";
        echo $sql . PHP_EOL;
//        continue;
        $insert_return = $page->executeServer($server, $sql, 2);
        if ($insert_return['error']) {
            echo 'error_ '.PHP_EOL ;
        } else {
            echo 'OK' . PHP_EOL;
        }
        $page->webRequest('sendmail', array('uid' => $uid), $server);
    } else {
        echo 'touser read fail';
    }

}
