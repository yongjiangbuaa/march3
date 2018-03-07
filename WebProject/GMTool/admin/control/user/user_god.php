<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "诸神数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_god';
$dbArray = array(
    'god_id' => array('name'=>'诸神ID',),
    'name' => array('name'=>'诸神名称',),
    'level' => array('name'=>'等级','editable'=>0,),
    'exp' => array('name'=>'经验','editable'=>0,),
    'star' => array('name'=>'星级','editable'=>0,),
    'energy' => array('name'=>'疲劳度','editable'=>0),
    'status' => array('name'=>'状态(0,空闲;1,驻守;2,出征;3,禁止操作)','editable'=>0),
    'energy_time' => array('name'=>'疲劳度回复开始时间','editable'=>0),
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
            $dragonId=$item['god_id'];
            $items[$dragonId]['uid'] = $item['uid'];
            $items[$dragonId]['god_id'] = $item['god_id'];
            $items[$dragonId]['name'] = ($lang[(int)$clintXml[$item['god_id']]['name']]);
            $items[$dragonId]['level'] = $item['level'];
            $items[$dragonId]['star'] = $item['star'];
            $items[$dragonId]['exp'] = $item['exp'];
            $items[$dragonId]['energy'] = $item['energy'];
            $items[$dragonId]['status'] = $item['status'];
            $items[$dragonId]['energy_time'] = $item['energy_time'] ?date('Y-m-d H:i:s', $item['energy_time']):0;
        }
    }else{
        $error_msg = search($result);
        $items = array();
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>