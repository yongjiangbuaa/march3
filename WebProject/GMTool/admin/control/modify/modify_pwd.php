<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "玩家过滤设置数据";
$alert = "请慎重修改此页数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_login_check';
$dbArray = array(
    'uid' => array('name'=>'uid',),
    'check_key' => array('name'=>'key',),
    'check_value' => array('name'=>'value',),
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
        $sql_update = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '$useruid' and check_key='{$_REQUEST['ukey']}' ";
        $page->execute($sql_update);
        adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array($_REQUEST['vid']=>$_REQUEST['num']));
    }

    if($type == 2)
    {


        if($_REQUEST['mpuid'] && $_REQUEST['mpwd'] && $_REQUEST['mpkey'])
        {
            $sql_update = "INSERT INTO $db(uid,check_key,check_value) VALUES ('{$_REQUEST['mpuid']}','{$_REQUEST['mpkey']}','{$_REQUEST['mpwd']}')";
            $page->execute($sql_update);
            adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array('key'=>$_REQUEST['mpkey'],'val'=>$_REQUEST['mpwd']));
        }

    }

    if($type == 4)
    {
        if( $useruid)
        {
            $sql_update = "DELETE FROM $db where uid='$useruid' and check_key='{$_REQUEST['ukey']}' ;";
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
            $items[$item['check_key']]['uid'] = $item['uid'];
            $items[$item['check_key']]['check_key'] = $item['check_key'];
            $items[$item['check_key']]['check_value'] = $item['check_value'];
        }
    }else{
        $error_msg = search($result);
        $items = array();
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>