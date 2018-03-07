<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if ($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];


//固定框架，单主键表，需修改插入部分
$db = 'user_egg_gift';
$dbArray = array(
    'item_id' => array('name' => 'ID',),
    'count' => array('name' => '数量', 'editable' => 0,),
    'available_count' => array('name' => '数量available', 'editable' => 0,),
    'item_egg' => array('name' => 'item臭鸡蛋值',),
    'total_egg' => array('name' => '总厌恶值',),
);
$typelanguage = array(
    2 => 'add', 3 => 'edit', 5 => 'delete',
);

if ($type) {
    $k = 'item_' . $typelanguage[$type];

    if ($username) {
        $account_list = cobar_getValidAccountList('name', $username);
        $useruid = $account_list[0]['gameUid'];
    }

    if($_REQUEST['modify_name'] == 'count'){
        $othername = 'available_count';
    }else if($_REQUEST['modify_name'] == 'available_count'){
        $othername = 'count';
    }
//    //修改
//    $showsql='';
//    if (false && $type == 3) {//user_egg_gift
//        $num = $_REQUEST['num'];
////        exit('error');
//        $time = floor(microtime(true)*1000);
//        $sql = "update $db set {$_REQUEST['modify_name']}='{$num}',$othername='{$num}',total_egg= item_egg*{$num} ,update_time=$time where uuid = '{$_REQUEST['uuid']}'";
//        $page->execute($sql,2,true);
//        //更新总额
//        $sql = "update user_egg set egg=(select sum(total_egg) cnt from user_egg_gift where uid='{$useruid}') where uid='{$useruid}'";
//
//        $showsql = $sql;
//        $page->execute($sql,2,true);
//
//        $loguser = !empty($useruid) ? $useruid : $username;
//        adminLogUser($adminid, $loguser, $currentServer, array($k => array($_REQUEST['modify_name'] => $_REQUEST['num'], 'uuid' => $_REQUEST['uuid'])));
//
//
//    }
//    //删除
//    if (false && $type == 5) {
//
//        $sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
//        $page->execute($sql,2,true);
//        //更新总额
//        $sql = "update user_egg set egg=(select sum(total_egg) cnt from user_egg_gift where uid='{$useruid}') where uid='{$useruid}'";
//        $page->execute($sql,2,true);
//        $loguser = !empty($useruid) ? $useruid : $username;
//        adminLogUser($adminid, $loguser, $currentServer, array($k => array('uuid' => $_REQUEST['uuid'])));
//    }

    //查看$username
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.ownerId = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where ownerId = '{$useruid}'";

    $sql = "select * from $db where uid = '{$useruid}'";

    $sql .= " order by item_id asc";
    $result = $page->execute($sql, 3, true);
    if (!$result['error'] && $result['ret']['data']) {
        $lang = loadLanguage();
        $enlang = loadLanguage('en');
        $clientXml = loadXml('goods', 'goods');
        $items = $result['ret']['data'];
        foreach ($items as $key => $item) {//$key 是0 ,1,2,3
            $items[$key]['enname'] = $enlang[(int)$clientXml[$item['item_id']]['name']];
            $items[$key]['name'] = $lang[(int)$clientXml[$item['item_id']]['name']];

            $num = (int)($clientXml[$item['item_id']]['para']);
            $items[$key]['enname'] = str_replace('{0}', $num, $items[$key]['enname']);
            $items[$key]['name'] = str_replace('{0}', $num, $items[$key]['name']);
        }

        $ret = $page->execute("select egg from user_egg where uid='{$useruid}'",3);
        $alltotal = $ret['ret']['data'][0]['egg'];
    } else {
        $error_msg = search($result);
        $items = array();
    }
}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>