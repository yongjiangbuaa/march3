<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/10
 * Time: 13:57
 */
date_default_timezone_set('UTC');

if(php_sapi_name() !== 'cli') {
    $sig = 'G4Oq3Eru';
    $sig_g = $_GET['s'];
    if ($sig !== $sig_g) {
        exit();
    }
}
error_reporting(0);
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors',1);

define('DOCS_ROOT', dirname(__DIR__));
define('SERVERS_DESC_XML', DOCS_ROOT.'/resource/servers.xml');

header("Content-Type:text/xml");
echo get_rpc_client_config(), PHP_EOL;



function get_rpc_client_config(){
    $xml_template = <<<XML
<beans xmlns="http://www.springframework.org/schema/beans"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://www.springframework.org/schema/beans
        http://www.springframework.org/schema/beans/spring-beans.xsd">

    <bean id="abstractWorldServiceClient" class="com.elex.cok.remote.spring.RPCProxyFactoryBean" abstract="true" >
        <property name="serviceInterface" value="com.elex.cok.gameengine.cross.WorldService" />
    </bean>

    {VAR_WORLD_SERVICE_BEAN_LIST}

</beans>
XML;
    $server_list = get_server_ip_list(SERVERS_DESC_XML);
    $bean_list = array();
    foreach ($server_list as $server_id => $config) {
        if(is_array($config)){
            $bean_list[] = get_service_bean_xml($server_id, $config['ip'], $config['port'], $config['type']);
        }else{
            $bean_list[] = get_service_bean_xml($server_id, $config);
        }
    }
    $beans_xml = implode("\n", $bean_list);
    $xml_content = str_replace('{VAR_WORLD_SERVICE_BEAN_LIST}', $beans_xml , $xml_template);
    return $xml_content;
}

function get_service_bean_xml($server_id,$ip_inner,$port = 1090, $type = 'rpc'){
    $service_bean_template = '
    <bean id="worldService{VAR_SERVER_ID}" parent="abstractWorldServiceClient">
        <property name="serviceUrl" value="{VAR_URL}/WorldService"/>
    </bean>
';
    if(empty($port)){
        $port = 1090;
    }
    if(empty($type)){
        $type = 'rpc';
    }
    $item = str_replace('{VAR_SERVER_ID}', $server_id, $service_bean_template);
    $item = str_replace('{VAR_URL}', "$type://$ip_inner:$port", $item);
    return $item;
}

function get_server_ip_list($xml_file){
    $xml = simplexml_load_file($xml_file);
    $list = $xml->xpath("Group/ItemSpec");
    $ret = array();
    foreach($list as $server){
        $id = strval($server['id']);
        if($server['rpc_type'] || $server['rpc_port']){
            $ret[$id] = array('ip' => strval($server["inner_ip"]),
                            'type' => strval($server['rpc_type']),
                            'port' => intval($server['rpc_port'])
            );
        }else{
            $ret[$id] = strval($server["inner_ip"]);
        }
    }
    return $ret;
}