<?php
/**
 * Created by PhpStorm.
 * User: huangshan
 * Date: 2017/8/25
 * Time: 16:37
 */
!defined('IN_ADMIN') && exit('Access Denied');
$title = "星辰查询";
$alert = '';
$type = $_REQUEST['type'];
if($_REQUEST['username'])
    $username = $_REQUEST['username'];
if($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
$db = 'user_star_equip';
$dbArray = array(
    'uid' => array('name'=>'玩家ID',),
    'starId' => array('name'=>'星辰ID',),
    'rare'  => array('name'=>'稀有度',),
    'name' => array('name'=>'星辰名称',),
    'type' => array('name'=>'星辰功能',),
    'level' => array('name'=>'星辰等级',),
    'value' => array('name'=>'属性提升',),
    'exp' => array('name'=>'星辰经验',),
 	'position' => array('name'=> '装备位置',),
);
if($type){
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.ownerId = u.uid and u.name = '{$username}'";
// 	else
// 		$sql = "select * from $db where ownerId = '{$useruid}'";

    if($username){
        $account_list = cobar_getValidAccountList('name', $username);
        $uid = $account_list[0]['gameUid'];
        $sql = "select * from $db where uid = '{$uid}'";
    }else{
        $sql = "select * from $db where uid = '{$useruid}'";
    }

    $sql .= " order by position desc";
    $result = $page->execute($sql,3);
    if(!$result['error'] && $result['ret']['data']){
        $lang = loadLanguage();
        $clientXml = loadXml('stars','stars');
        $items = $result['ret']['data'];
        $quality = array();
        foreach ($items as $key => $item) {
            $items[$key]['name'] = $lang[(int)$clientXml[$item['starId']]['name']];
            $quality[$key] = (int)$clientXml[$item['starId']]['quality'];
            if($quality[$key] == 0){
                $quality[$key] = '白色';
            }
            if($quality[$key] == 1){
                $quality[$key] = '绿色';
            }
            if($quality[$key] == 2){
                $quality[$key] = '蓝色';
            }
            if($quality[$key] == 3){
                $quality[$key] = '紫色';
            }
            if($quality[$key] == 4){
                $quality[$key] = '金色';
            }
            $items[$key]['rare'] = $quality[$key];
            $items[$key]['type'] = $lang[(int)$clientXml[$item['starId']]['effect']];
            $items[$key]['level'] = (int)$clientXml[$item['starId']]['level'];
            $items[$key]['value'] = '+'.$clientXml[$item['starId']]['value'].'%';
            if( $items[$key]['position'] == 0){
                $items[$key]['position'] = '背包';
            }
            else{
                $items[$key]['position'] = '位置'.$items[$key]['position'];
            }
        }
    }else{
        $alert = '用户不存在或不属于当前选择的服务器！';
    }
}
include( renderTemplate("{$module}/{$module}_{$action}") );