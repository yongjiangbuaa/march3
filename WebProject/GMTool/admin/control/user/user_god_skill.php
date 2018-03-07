<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "诸神技能数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_god_skill';
$dbArray = array(
    'god_id' => array('name'=>'诸神ID',),
    'name' => array('name'=>'诸神名称',),
    'skill_id' => array('name'=>'技能ID','editable'=>0,),
    'level' => array('name'=>'等级','editable'=>0,),

);

if($type){


    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
        $sql = "select * from $db where uid = '$uid' ";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }
    $sql .= " order by god_id asc";
    $result = $page->execute($sql,3);
    $items = array();
    if(!$result['error'] && $result['ret']['data']){
        $lang = loadLanguage();
        $clintXml = loadXml('deity','deity');
        $ret = $result['ret']['data'];
        foreach ($ret as $key => $item) {
            $dragonId=$item['skill_id'];
            $items[$dragonId]['uid'] = $item['uid'];
            $items[$dragonId]['god_id'] = $item['god_id'];
            $items[$dragonId]['name'] = ($lang[(int)$clintXml[$item['god_id']]['name']]);
            $items[$dragonId]['skill_id'] = $item['skill_id'];
            $items[$dragonId]['level'] = $item['level'];
        }
    }else{
        $error_msg = search($result);
        $items = array();
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>