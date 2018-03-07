<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "用户过滤设置";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_login_check';
$dbArray = array(
    'uid' => array('name'=>'uid',),
    'check_key' => array('name'=>'key','editable'=>1,),
    'check_value' => array('name'=>'value','editable'=>1,),
);

if($type){


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