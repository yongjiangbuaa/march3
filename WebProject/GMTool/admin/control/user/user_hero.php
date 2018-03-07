<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "玩家英雄";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_hero';
$dbArray = array(
    'uuid' => array('name'=>'英雄UUID',),
    'hero_id' => array('name'=>'英雄ID',),
    'name' => array('name'=>'英雄名称',),
    'level' => array('name'=>'等级','editable'=>0,),
    'star' => array('name'=>'星级','editable'=>0,),
    'exp' => array('name'=>'经验','editable'=>0,),
    'status' => array('name'=>'状态(0,空闲;1,出征;2,驻守)','editable'=>0),
    'base' => array('name'=>'基础值类型(0-4)','editable'=>0),
    'growth' => array('name'=>'成长值类型(0-4)','editable'=>0),
    'create_time' => array('name'=>'释放时间','editable'=>0),
    'next_train_available_time' => array('name'=>'驻守开始时间','editable'=>0),
    'locked' => array('name'=>'英雄锁定标识','editable'=>0),
);

if($type){
    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
        $sql = "select * from $db where uid = '$uid' ";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }
    $sql .= " order by hero_id asc";
    $result = $page->execute($sql,3);
    $items = array();
    if(!$result['error'] && $result['ret']['data']){
        $lang = loadLanguage();
        $clintXml = loadXml('hero','hero');
        $ret = $result['ret']['data'];
        foreach ($ret as $key => $item) {
            $items[$key]['uid'] = $item['uid'];
            $items[$key]['uuid'] = $item['uuid'];
            $items[$key]['hero_id'] = $item['hero_id'];
            $items[$key]['name'] = ($lang[(int)$clintXml[$item['hero_id']]['name']]);
            $items[$key]['level'] = $item['level'];
            $items[$key]['star'] = $item['star'];
            $items[$key]['exp'] = $item['exp'];
            $items[$key]['status'] = $item['status'];
            $items[$key]['base'] = $item['base'];
            $items[$key]['growth'] = $item['growth'];
            $items[$key]['create_time'] = $item['create_time'] ?date('Y-m-d H:i:s', $item['create_time']/1000):0;
            $items[$key]['next_train_available_time'] = $item['next_train_available_time'] ?date('Y-m-d H:i:s', $item['next_train_available_time']/1000):0;
            $items[$key]['locked'] = $item['locked'];
        }
    }else{
        $error_msg = search($result);
        $items = array();
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>