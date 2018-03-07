<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if (!$_REQUEST['start_time'])
    $start = date("Y-m-d", time() - 86400 * 7);
if (!$_REQUEST['end_time'])
    $end = date("Y-m-d", time());
global $servers;

$sttt = $_REQUEST['selectServer'];
$count = $_REQUEST['count'];
$itemId = $_REQUEST['item_id'];
$serverDiv = loadDiv($sttt);
$erversAndSidsArr = getSelectServersAndSids($sttt);
$selectServer = $erversAndSidsArr['withS'];
$selectServerids = $erversAndSidsArr['onlyNum'];

if ($type == 'view') {
    $start = substr($_REQUEST['start_time'], 0, 10);
    $end = substr($_REQUEST['end_time'], 0, 10);
    $startTime = date("Ymd", strtotime($start));
    $endTime = date("Ymd", strtotime($end));
    $sids = implode(',', $selectServerids);
    $sql = "select date,count(ownerId) ownerId,itemId from stat_item where date between $startTime and $endTime and sid in($sids) and itemId = $itemId and count > $count group by date,itemId;";

    $ret_global = query_stats_global($sql);
    $result['ret']['data'] = $ret_global;
    if (!$result['error'] && $result['ret']['data']) {
        $lang = loadLanguage();
        $enlang = loadLanguage('en');
        $clientXml = loadXml('goods', 'goods');
        $items = $result['ret']['data'];
        foreach ($items as $key => $item) {
            $items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
            $items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];
        }
    }
    $data = array();
    $dates = array();
    foreach ($items as $curRow) {
        $da = $curRow['date'];
        $data[$curRow['date']][$curRow['itemId']]['ownerId'] = $curRow['ownerId'];
        $data[$curRow['date']][$curRow['itemId']]['name'] = $curRow['name'];
        $data[$curRow['date']][$curRow['itemId']]['enname'] = $curRow['enname'];
        if (in_array($da, $dates)) {
            continue;
        }
        $dates[] = $da;
    }
//    print_r($data);
    if (!$result) {
        $headAlert = "数据查询失败!";
    } else {
        $html = "<br><table class='listTable' style='text-align:center'><thead><th>物品ID</th><th>物品名称</th><th>物品英文名</th><th>条件（大于）</th>";
        foreach ($dates as $daValue) {
            $html .= "<th>$daValue</th>";
        }
        $html .= "</thead>";
        foreach ($data as $key => $value) {
            foreach ($value as $itemId => $itemIdValue) {
                $html .= "<tr><td>" . $itemId . "</td><td>" . $itemIdValue['name'] . "</td><td>" . $itemIdValue['enname'] . "</td><td>" . $count . "</td>";
                $html .= "<td><font color='blue'>" . $itemIdValue['ownerId'] . "</font></td>";
                $html .= "</tr>";
            }
        }
        $html .= "</table>";
    }
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>