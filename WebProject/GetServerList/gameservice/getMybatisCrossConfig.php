<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/14
 * Time: 11:07
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
$pool = $_GET['pool'];
if($pool){
    $environment_template = '
        <environment id="{VAR_SERVER_ID}">
            <transactionManager type="JDBC" />
            <dataSource type="com.elex.cok.utils.HikariDataSourceFactory">
                <property name="driverClassName" value="${local.jdbc.driver}"/>
                <property name="jdbcUrl" value="jdbc:mysql://{VAR_LOCAL_DB_IP_PORT_NAME}" />
                <property name="username" value="${local.jdbc.user}"/>
                <property name="password" value="${local.jdbc.password}"/>
                <property name="connectionTimeout" value="${bonecp.connectionTimeout}"/>
                <property name="maximumPoolSize" value="${bonecp.maxConnectionsPerPartition}" />
                <property name="minimumIdle" value="${bonecp.minConnectionsPerPartition}" />
                <property name="leakDetectionThreshold" value="30000" />
            </dataSource>
        </environment>
';
}else {
    $environment_template = '
        <environment id="{VAR_SERVER_ID}">
            <transactionManager type="JDBC" />
            <dataSource type="UNPOOLED">
                <property name="driver" value="${local.jdbc.driver}" />
                <property name="url" value="jdbc:mysql://{VAR_LOCAL_DB_IP_PORT_NAME}" />
                <property name="driver.user" value="${local.jdbc.user}" />
                <property name="driver.password" value="${local.jdbc.password}" />
                <property name="driver.connectTimeout" value="5000" />
            </dataSource>
        </environment>
';
}


$config_content = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
$config_content .= '<!DOCTYPE configuration PUBLIC "-//mybatis.org//DTD Config 3.0//EN" "http://mybatis.org/dtd/mybatis-3-config.dtd">'."\n";
$config_content .= '<configuration>'."\n";
$config_content .= '    <environments default="1">';
$config_content .= get_environments_xml(SERVERS_DESC_XML);
$config_content .= '    </environments>'."\n";
$config_content .= '    <!--{MAPPERS}-->' . "\n";
$config_content .= '</configuration>'."\n";

header("Content-Type:text/xml");
echo $config_content, PHP_EOL;


function get_environments_xml($xml_desc){
    global $environment_template;
    $db_list = get_server_db_info_list($xml_desc);
    $result = array();
    foreach ($db_list as $sid => $info) {
        $result[] = strtr($environment_template,
            array('{VAR_SERVER_ID}' => $sid,
                '{VAR_LOCAL_DB_IP_PORT_NAME}' => $info['ip'] . '/' . $info['name']));
    }
    return implode('', $result);
}

function get_server_db_info_list($xml_file){
    $xml = simplexml_load_file($xml_file);
    $list = $xml->xpath("Group/ItemSpec");
    $ret = array();
    foreach($list as $server){
        $id = strval($server['id']);
        $ret[$id] = array( 'ip' => strval($server["db_ip"]),
                        'name' => strval($server["db_name"]));
    }
    return $ret;
}