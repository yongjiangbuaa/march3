<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
unset($type);
$type = $_REQUEST['action'];
if ($_REQUEST['username'])
    $username = $_REQUEST['username'];
if ($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$headLine = "查看玩家信息";
$headAlert = "";


function sendReward($mailUid)
{
    $page = new BasePage();
    $page->webRequest('sendmail', array('uid' => $mailUid));
}
$sid = $_COOKIE['Gserver2'];
$sid = substr($sid, 1);
$sid = str_pad($sid,6,0,STR_PAD_LEFT );
if ($type) {
    if (empty($username) && empty($useruid)) {
        $headAlert = '请输入用户名或UID';
    } else {
        if ($username) {
            $account_list = cobar_getAllAccountList('name', $username);
            $useruid = $account_list[0]['gameUid'];
            $server = 's' . $account_list[0]['server'];
            if (!$useruid) {
                $sql = "select uid from userprofile where binary name ='$username'";
                $ret = $page->executeServer($server, $sql, 3);
                $useruid = $result['ret']['data'][0]['uid'];
            }
        } else {
            $account_list = cobar_getAccountInfoByGameuids($useruid);
            $server = 's' . $account_list[0]['server'];
        }

        $sql = "select u.name, p.imageVer, u.uid from friend_circle f inner join userprofile u on f.uid = u.uid inner join user_images p on p.userUid=f.uid where p.imageType = 0 and f.uid='$useruid';";
        $result = $page->execute($sql, 3);
        $html = $sql;
        $data = array();
        if ($result['error'] || (!$result['ret']['data'])) {
            $headAlert = '没有查到数据';
        } else {
            $row = $result['ret']['data'][0];
            $data['server'] = $server;
            $data['uid'] = $row['uid'];
            $data['name'] = $row['name'];
            $data['picVer'] = ($row['imageVer']);
            $html .= "<div style='float:left;width:100%;height:560px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
            for ($i = 2; $i <= $data['picVer']; $i++) {
                $ext = ".jpg";
                $uid = $data['uid'];
                $name = str_replace("'", '&#39;', $data['name']);
                $name = str_replace(">", '&gt;', $name);
                $name = str_replace("<", '&lt;', $name);
                $seq = $data['picVer'];

                $file_folder = substr($uid, -6);
                $file_name = md5($uid . '_' . $i) . $ext;

                if(!file_exists("/data/coq_avatar/fm/" . $file_folder . '/' . $file_name)){
                    continue;
                }
                $html .= $file_name;
                $html .= "<td><label for='picture_.{$uid}_{$i}'><img src='http://coq.eleximg.com/coq/img/fm/$file_folder/$file_name'  style='width:120px;height:120px;' ></label><br>";
                $html .= '<input class="btn js-btn btn-primary" type="button" value="撤销朋友圈图片" name="btn_view" onclick="check_edit(' . "'$uid','$i'" . ')" /></td>';
            }
            $html .= "</div>";
        }
    }
}

if ($_REQUEST['event'] == 'edit') {
    $uid = $_REQUEST['uid'];
    $id = $_REQUEST['id']; //图片id 递增

    $file_folder = substr($uid, -6);
    $ext = ".jpg";
    $file_name = md5($uid . '_' . $id) . $ext;

    $index_key = "user_fm_index_".$uid;
    $currserver = $page->getAppId();
    $serverinfo = $servers[$currserver];
    if ($currserver == 'test' || $currserver == 'localhost') {
        $t = explode(':', $serverinfo['webbase']);//http://IPIPIP:8080/gameservice/
        $ip = substr($t[1], 2);
    }else{
        $ip = $serverinfo['ip_inner'];
    }
    $message_key = "user_fm_message_".$uid;
    $redis = new Redis();
    $redis->connect($ip,6379);
    $count = $redis->llen($index_key);
    $all_message = $redis->lRange($index_key, 0, $count);
    echo $count;

    //allmessage很多这种数据 [0] => {"fmServerId":1,"fmUuid":"0f083c8c2626488ebcb36df9b9a9994d","fmUid":"54908982000001"}
    foreach($all_message as $send){
        $send = json_decode($send,true);
        $fmUuid = $send['fmUuid'];//点赞消息id
        //返回名称为h的hash中key1对应的value
        $rd_json = $redis->hGet($message_key, $fmUuid);
//        "{\"uid\":\"54908982000001\",\"fmServerId\":1,\"image\":0,\"createTime\":1462879824,\"isAdmin\":false,\"lang\":\"zh_CN\",\"uuid\":\"a5752edc68a242a49cb2fc125878c993\",\"content\":\"\xe5\x8a\xa8\xe6\x80\x81\xe6\xb5\x8b\xe8\xaf\x95\"}"
        $rd_array = json_decode($rd_json,true);
        if($rd_array['image']==$id){
            $rd_array['image']=0;
            $new_json = json_encode($rd_array);
            $result = $redis->hSet($message_key, $fmUuid,$new_json);
            $redis->close();
            if(!$result) {

                break;
            }
        }
    }

//    if($result == 0){
//        exit('OK');
//    }
    $dump_file = "/data/coq_avatar/fm/" . $file_folder . '/' . $file_name;
    if (file_exists($dump_file)) {
        unlink($dump_file);
        exit('OK');
    }
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>