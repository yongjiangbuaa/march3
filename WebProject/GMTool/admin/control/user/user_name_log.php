<?php
!defined('IN_ADMIN') && exit('Access Denied');
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'modify_user_nick_name_log';
$dbArray = array(
    'uid' => array('name'=>'玩家UID',),
    'oldName' => array('name'=>'曾用名',),
    'newName' => array('name'=>'newName',),
    'createTime' => array('name'=>'更改名字时间戳',),
);
//if($type){
    if($username){
        $sql = "select * from $db where oldName = '{$username}' or newName = '{$username}'";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }
    $result=$page->execute($sql,3);
    if(!$result['error']){
        $name_log = $result['ret']['data'];
    }else{
        $error_msg = search($result);
        $name_log = array();
    }
//}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>