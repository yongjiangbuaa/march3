<?php
!defined('IN_ADMIN') && exit('Access Denied');
if ($_REQUEST['action'] == 'modify'){
    $activityuid = $_REQUEST['activityuid'];
    $events = $_REQUEST['contents'];
    $ret = modify($activityuid, $events);
    if ($ret == 'success'){
        $headAlert = '修改成功';
    }else {
        $headAlert = '修改失败';
    }
}
$sql = 'select uid,events from server_activity order by startTime desc limit 1';
$result = $page->execute($sql, 3, true);
if (!$result['error'] && $result['ret']['data']){
    $item = $result['ret']['data'][0];
    if (empty($item['events'])) {
        $item = array();
        $headAlert = '该周不随机，不能修改';
    }
}else {
    $headAlert = '数据错误';
}
function modify($uid, $events){
    $page = new BasePage ();
    $result=$page->webRequest ('modifyRandomActivity', array (
        'uid'=>$uid,
        'events' => $events
    ) );
    return $result;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>