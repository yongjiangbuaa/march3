<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/5/16
 * Time: 15:12
 */
!defined('IN_ADMIN') && exit('Access Denied');

$metric_list = array(
    'command_queue' => '命令线程池队列长度',
    'user_command_queue' => '用户队列总长度',
    'event_bus_queue' => '事件处理队列长度',
    'current_users' => '当前在线',
    'current_connections' => '当前连接数',
    'event_queue' => '事件队列长度',
    'command_threads' => '命令线程数量',
    'total_connections' => '总连接数',
    'max_users' => '最大同时在线',
    'http_conns' => 'HTTP连接数',
    'http_total_conn' => 'HTTP总连接数',

);

$time_span_list = array(
    'hour' => 'Hour',
    '2hr' => '2 Hours',
    '4hr' => '4 Hours',
    'day' => 'Day',
    'week' => 'Week',
    'month' => 'Month',
    'year' => 'Year',
);

$time_span = $_REQUEST['time_span'];
$metric_select = $_REQUEST['metric'];
$server_select = $_REQUEST['server_id'];
$image_list = array();

$server_list = get_sfs_server_list();

if($_REQUEST['view']) {
    $image_size = 'medium';
    $url_format = 'http://169.44.71.72:8088/ganglia/graph.php?r=%s&z=%s&c=coq&h=%s&jr=&js=&v=0&m=%s';
    if($server_select !== 'ALL' && !empty($server_list[$server_select])){
        $server_select_ip = $server_list[$server_select]['ip'];
        foreach ($metric_list as $metric => $metric_name) {
            $img_url = sprintf($url_format, $time_span, $image_size, $server_select_ip, $metric);
            $image_list[] = array('sid' => $metric_name,
                'title' => $metric_name . ' Last ' . $time_span_list[$time_span],
                'url' => $img_url);
        }
    }else{
        foreach ($server_list as $server_info) {
            $img_url = sprintf($url_format, $time_span, $image_size, $server_info['ip'], $metric_select);
            $sid = 'S' . $server_info['sid'];
            $image_list[] = array('sid' => $sid,
                'title' => $sid . ' Last ' . $time_span_list[$time_span],
                'url' => $img_url);
        }
    }


}
function get_sfs_server_list(){
    $xml_file = '/data/htdocs/resource/servers.xml';
    if(!file_exists($xml_file)){
        $xml_file = ADMIN_ROOT . '/../../resource/servers.xml';
    }
    $xml = simplexml_load_file($xml_file);
    if(!$xml){
        return array();
    }
    $list = $xml->xpath("Group/ItemSpec");
    $ret = array();
    foreach($list as $server){
        $is_test = strtolower(strval($server['test']));
        if($is_test == 'true' || $is_test == '1'){
            continue;
        }
        $id = strval($server['id']);
        $ret[$id] = array(
            'sid' => $id,
            'ip' => strval($server["inner_ip"]),
        );
    }
    return $ret;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
