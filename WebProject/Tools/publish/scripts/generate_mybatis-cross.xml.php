<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');
require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$environment_template = '
		<environment id="{VAR_SERVER_ID}">
			<transactionManager type="JDBC" />
			<dataSource type="UNPOOLED">
				<property name="driver" value="${local.jdbc.driver}" />
				<property name="url" value="jdbc:mysql://{VAR_LOCAL_DB_IP_PORT_NAME}?characterEncoding=utf-8" />
				<property name="username" value="${local.jdbc.user}" />
				<property name="password" value="${local.jdbc.password}" />
			</dataSource>
		</environment>
';

$mybatis_cross_xml_template = file_get_contents(TEMPLATE_CONFIG_DIR.'/mybatis.xml');
$mappers_startpos = strpos($mybatis_cross_xml_template, '<mappers>');
$mappers_endpos = strpos($mybatis_cross_xml_template, '</configuration>');
$mapperlist = substr($mybatis_cross_xml_template, $mappers_startpos, $mappers_endpos - $mappers_startpos);

$environment_list = array();

$server_list = get_server_list();
$out_dir = PACKAGE_RESOURCE_DIR;
if (isset($_REQUEST['output_config_dir'])) {
	$out_dir = $_REQUEST['output_config_dir'];
}
$filename = $out_dir . '/mybatis-cross.xml';

file_put_contents ($filename, '<?xml version="1.0" encoding="UTF-8" ?>'."\n");
file_put_contents ($filename, '<!DOCTYPE configuration PUBLIC "-//mybatis.org//DTD Config 3.0//EN" "http://mybatis.org/dtd/mybatis-3-config.dtd">'."\n", FILE_APPEND);
file_put_contents ($filename, '<configuration>'."\n", FILE_APPEND);
file_put_contents ($filename, '    <environments default="development">'."\n", FILE_APPEND);
foreach ($server_list as $record) {
	$id = $record['svr_id'];
	if ($id == 0 || $id >= 999000) {
		continue;
	}
	$db_ref = $record['db_ref'];
	$item = str_replace('{VAR_SERVER_ID}', $id, $environment_template);
	$item = str_replace('{VAR_LOCAL_DB_IP_PORT_NAME}', $db_ref, $item);
	$environment_list[] = $item;
}
file_put_contents ($filename, implode("\n", $environment_list)."\n",FILE_APPEND);
file_put_contents ($filename, '    </environments>'."\n",FILE_APPEND);
file_put_contents ($filename, "    $mapperlist"."\n",FILE_APPEND);
file_put_contents ($filename, '</configuration>'."\n",FILE_APPEND);

echo_realtime ( "done. " . $filename );
