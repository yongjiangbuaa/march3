<?php
/**
 * Created by PhpStorm.
 * User: miller
 * Date: 17/1/9
 * Time: 下午4:47
 */
define('ADMIN_ROOT', '/data/htdocs/ifadmin/admin');

include ADMIN_ROOT . '/config.inc.php';
include ADMIN_ROOT . '/admins.php';
include_once ADMIN_ROOT . '/servers.php';


$redisKey = 'admin_groupmail'; //hset
echo '----start---' . date('Ymd H:i:s').PHP_EOL;
while (true) {
    do {
        $client = new Redis();
        if (!$client->connect('127.0.0.1', 6379, 3)) {
            sleep(5);
            break;
        }

        //一般情况下是只有一个值
        $mailkeyArr = $client->hGetAll($redisKey); //array(0->123123,1->3f32323223);

        foreach ($mailkeyArr as $item_key => $item_value) {
            $keyMailUids = $item_value . '_uid'; //rpush
            $keyMailContents = $item_value . '_sql'; //set
            $ret = $client->get($keyMailContents);
            if (!$ret) {
                //TODO:没有邮件内容,删除这个key
                $client->hdel($redisKey, $item_key); //hset没有值,会自动删除 admin_groupmail key
                $client->del($keyMailContents);
                $client->del($keyMailUids);
            }
            $MailsqlArr = json_decode($ret);
            // title contents  reward adminid  time
            $mysqli = getGlobalConnect(); //global连接
            $page = new BasePage();
            //TODO: 一次执行把所有uid 全部pop完
            while ($mysqli && $client->lLen($keyMailUids)) {

                $uid = $client->lPop($keyMailUids);
                $sql = "select gameUid ,server from account_new where gameUid='{$uid}' ";
                $result = query_from_db_z($mysqli, $sql);
                foreach ($result as $row) {
                    $toUser = $row['gameUid'];
                    $serverKey = $row['server'];
                    $sendBy = $MailsqlArr['adminid'];
                    $title = $MailsqlArr['title'];
                    $contents = $MailsqlArr['contents'];
                    $reward = $MailsqlArr['reward'];

                    $uid = md5($toUser . $serverKey . uniqid() . time());
                    $time = floor(microtime(true) * 1000);

                    $rewardStatus = 1;
                    if ($reward)
                        $rewardStatus = 0;
                    $sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`, `reward`,`rewardStatus`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents', '$reward',$rewardStatus)";
//                        $page->executeServer('s' . $serverKey, $sql, 2);
                    $sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 1,4)";
                    echo $sql . PHP_EOL;
//                        $page->executeServer('s' . $serverKey, $sql, 2);
//                        sendReward2($uid, 's' . $serverKey,$page);

//                        adminLogUser($adminid, $toUser, 's' . $serverKey, array(
//                                'groupMail' => 'add',
//                                'reward' => $reward,
//                                'sendTime' => $sendTime
//                            )
//                        );
                }
            }

            $client->hdel($redisKey, $item_key); //hset没有值,会自动删除 admin_groupmail key
            $client->del($keyMailContents);
            $client->del($keyMailUids);

        }
        $client->close();

    } while (false);
    sleep(100);
}

//获取global 从库连接,不能更改数据
function getGlobalConnect()
{
    $db_info = array(
        'host' => '10.155.110.57',
        'user' => 'gow',
        'password' => 'ZPV48MZH6q9V8oVNtu',
        'db' => 'cokdb_global',
        'port' => '3306',
    );
    $mysqli = new mysqli($db_info['host'], $db_info['user'], $db_info['password'], $db_info['db'], $db_info['port']);
    if ($mysqli->connect_errno) {
        return false;
    }
    return $mysqli;
}

function query_from_db_z($mysqli, $sql)
{

    $result = $mysqli->query($sql);
    if (is_bool($result)) {
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

function sendReward2($mailUid, $serv, $page)
{
    $page->webRequest('sendmail', array('uid' => $mailUid), $serv);
}