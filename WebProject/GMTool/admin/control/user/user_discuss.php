<?php
!defined('IN_ADMIN') && exit('Access Denied');
if ($_REQUEST['username'])
    $username = $_REQUEST['username'];
if ($_REQUEST['useruid'])
    $useruid = $_REQUEST['useruid'];
//固定框架，单主键表，需修改插入部分
$dbArray = array(
    'id' => array('name' => 'ID', 'dbkey' => 'id'),
    'type' => array('name' => '类型', 'dbkey' => 'type'),
    'serverId' => array('name' => '服', 'dbkey' => 'server_id'),
    'uid' => array('name' => 'uid', 'dbkey' => 'user_uid'),
    'userName' => array('name' => '玩家名字', 'dbkey' => 'user_name'),
    'content' => array('name' => '内容', 'dbkey' => 'content'),
    'agreeCount' => array('name' => '点赞数量', 'dbkey' => 'agree_count', 'editable' => 1),
    'createTime' => array('name' => '时间', 'dbkey' => 'create_time'),
);
$type = array(
    0 => '默认',
    1 => '红龙1',
    2 => '蓝龙2',
    3 => '绿龙3',
    101 => '兵',
    201 => '英雄1海伦',
    202 => '英雄2利奥',
    203 => '英雄3莱安',
    204 => '英雄4卡萝',
    205 => '英雄5克里斯汀',
    206 => '英雄6',
    207 => '英雄7',
    208 => '艾莎公主',
    209 => '蒂那',
    4 => '黑龙',
);

$discusstype = array(0 => '热评', 1 => '普通评论');

$hotkey = 'discuss:hot:info:type:';
$normalkey = 'discuss:info:type:';
$agreeecountkey = "discuss:agree:type:";

//传过来的值 是key
$currtype = $_REQUEST['type'];
$currdiscusstype = intval($_REQUEST['discusstype']);
$operation = $_REQUEST['operation'];

do {
    if ($currtype) {
        $tip = '';
        $key = '';

        $redis = new Redis();
        if (!$redis->connect(GLOBAL_REDIS_SERVER_IP2, GLOBAL_REDIS_SERVER_IP2_PORT)) {
            $tip = '连接redis失败';
            break;
        }

        if ($operation == 'save') {
            $key = $agreeecountkey . $currtype;

            $num = $_REQUEST['num'];
            $id = $_REQUEST['id'];
            if ($id > 0 && $num > 0) {
                $sql = "update  discuss_info set agree_count=$num where id=$id";
                $result = cobar_query_global_db_cobar($sql);
                if (!$result) {
                    $tip = '操作失败';
                    break;
                }
                $original_num = $redis->hGet($key, $id);
                if ($original_num || $original_num == 0) {
                    if ($redis->hSet($key, $id, $num) == 0) {
                        //操作成功
                        $tip = '操作成功';
                    }
                }
                $cmd = '/home/elex/php/bin/php /data/htdocs/ifadmin/admin/scripts/freshHotDiscuss.php >> /data/htdocs/ifadmin/admin/scripts/freshHotDiscuss.log 2&1';
                $re = system($cmd, $retval);

                if ($re === false || $retval == 1) {
                    $tip = "运行失败";
                    break;
                }
                adminLogSystem($adminid, array('key' => $key, 'sql' => $sql, 'original' => $original_num, 'operation' => 'save'));
            }
        }
        switch ($currdiscusstype) {
            case 0:
                $key = $hotkey . $currtype;
                break;
            case 1:
                $key = $normalkey . $currtype;
                $key1 = $agreeecountkey . $currtype;
                $mark_narmal = 1; //用作标记 普通评论, 点赞数 不在同一个redis中
                break;
        }

        if ($operation == 'delete') {

            $id = $_REQUEST['id'];
            if ($id > 0) {
                $sql = "delete from discuss_info where id=$id";
                $result = cobar_query_global_db_cobar($sql);
                if (!$result) {
                    $tip = '操作失败';
                    break;
                }
                $cmd = '/home/elex/php/bin/php /data/htdocs/ifadmin/admin/scripts/freshHotDiscuss.php >> /data/htdocs/ifadmin/admin/scripts/freshHotDiscuss.log 2&1';
                $re = system($cmd, $retval);

                if ($re === false || $retval == 1) {
                    $tip = "运行失败";
                    break;
                }
                adminLogSystem($adminid, array('key' => $key, 'sql' => $sql, 'operation' => 'delete'));
            }
        }


        $result = $redis->lRange($key, 0, -1);
        if ($mark_narmal){
            $result1 = $redis->hGetAll($key1);
            if(count($result1) >0){
                $itemstmp = array();
                foreach ($result1 as $id=>$row){
                    $json_str = json_decode($row);
                    $itemstmp[$id] = $row;
                }
                $mark_narmal = 2;
            }
        }
        if ( count($result) > 0 ) {
            $items = array();

            foreach ($result as $row) {
                $json_str = json_decode($row, true);
                $json_str['createTime'] = date('Y-m-d H:i:s', $json_str['createTime'] / 1000);
                if($mark_narmal == 2) {
                    $json_str['agreeCount'] = $itemstmp[$json_str['id']];
                }
                //解码问题 中文
                $items[] = $json_str;
            }
        }else{
            $tip = '无数据';
        }
    }
} while (false);

//	if($username){
//		$account_list = cobar_getValidAccountList('name', $username);
//		$uid = $account_list[0]['gameUid'];
//		$sql = "select * from $db where ownerId = '{$uid}'";
//	}else{
//		$sql = "select * from $db where ownerId = '{$useruid}'";
//	}
//	$sql = "select * from discuss_info ";
//	$result = cobar_query_global_db_cobar($sql);

//	if(!$result['error'] && $result['ret']['data']){
//		$lang = loadLanguage();
//		$enlang = loadLanguage('en');
//		$clientXml = loadXml('goods','goods');
//		$items = $result['ret']['data'];
//		foreach ($items as $key => $item) {
//			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
//			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];
//		}
//	}else{
//		$error_msg = search($result);
//		$items = array();
//	}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>