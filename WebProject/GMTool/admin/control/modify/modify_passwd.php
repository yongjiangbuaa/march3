<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "玩家密码数据";
$alert = "请慎重修改此页数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_passwd';
$dbArray = array(
    'uid' => array('name'=>'uid',),
    'fail_count' => array('name'=>'fail_count',),
    'passwd' => array('name'=>'passwd',),
);


if($type){

    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $useruid=$account_list[0]['gameUid'];
    }

    if($_REQUEST['mUid'])
    {
        $useruid=$_REQUEST['mUid'];
    }

    //修改
    if($type == 3)
    {
        $pwd=substr(md5(time()), 0, 6);
        $sql_update = "update $db set fail_count = 0 , passwd='$pwd' where uid = '$useruid' ";
        $page->execute($sql_update);
        adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array($_REQUEST['vid']=>$_REQUEST['num']));
    }

    if($type == 2)
    {


        if($_REQUEST['mpuid'])
        {
            $pwd=substr(md5(time()), 0, 6);
            $sql_update = "INSERT INTO $db(uid,fail_count,passwd) VALUES ('{$_REQUEST['mpuid']}',0,'$pwd')";
            $page->execute($sql_update);
            adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array('key'=>$pwd,'mpuid'=>$_REQUEST['mpuid']));
        }

    }

    if($type == 4)
    {
        if( $useruid)
        {
            $sql_update = "DELETE FROM $db where uid='$useruid'";
            $page->execute($sql_update);
            adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array());
        }
    }


    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
        $sql = "select * from $db where uid = '$uid' ";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }
    $result = $page->execute($sql,3);
    $items = array();
    if(!$result['error'] && $result['ret']['data']){
        $ret = $result['ret']['data'];
        foreach ($ret as $key => $item) {
            $items[$item[0]]['uid'] = $item['uid'];
            $items[$item[0]]['fail_count'] = $item['fail_count'];
            $items[$item[0]]['passwd'] = $item['passwd'];
        }
    }else{
        $error_msg = search($result);
        $items = array();
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>