<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$showNum = 1000;

if( $_REQUEST['action'] == 'view') {
    $rankKey = "hero_employ_rank_activity";
    if ($_REQUEST['showNum']) {
        $showNum = intval($_REQUEST['showNum']);
    } else {
        $showNum = 0;
    }

    if ($showNum > 0) {
        //查询数据并显示
        $redis = new Redis();
        if (!$redis->connect(GLOBAL_REDIS_SERVER_IP2, GLOBAL_REDIS_SERVER_IP2_PORT)) {
            $tip = '连接redis失败';
            return;
        }
        $result = $redis->zRevRangeByScore($rankKey, 0, $showNum, array('withscores' => TRUE));
        if (count($result) > 0) {
            $items = array();
            $rank = 1;
            foreach ($result as $row) {
                $tmp = array();
                $tmp['rank'] = $rank;
                $tmp['uid'] = $row[0];
                $tmp['score'] = $row[1];
                $items[] = $tmp;
                $rank++;
            }
            $showData = true;
        } else {
            $tip = '无数据';
        }
    } else {
        //返回错误信息
        $tip = "最大数目的参数值需要大于0";
    }
}
include(renderTemplate("{$module}/{$module}_{$action}"));
?>