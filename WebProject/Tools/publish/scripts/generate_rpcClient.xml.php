<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');
require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$worldservice_template = '
    <bean id="worldService{VAR_SERVER_ID}" parent="abstractWorldServiceClient">
        <property name="serviceUrl" value="rpc://{VAR_IP_PORT}/WorldService"/>
    </bean>
';

$xml_template = file_get_contents(TEMPLATE_CONFIG_DIR.'/template_rpcClient.xml');
$environment_list = array();

$server_list = get_server_list();
$out_dir = PACKAGE_RESOURCE_DIR;
if (isset($_REQUEST['output_config_dir'])) {
	$out_dir = $_REQUEST['output_config_dir'];
}

foreach ($server_list as $record) {
	$id = $record['svr_id'];
	if ($id == 0) {
		continue;
	}
	$ip_inner = $record['ip_inner'];
	$port = 1090;
	$item = str_replace('{VAR_SERVER_ID}', $id, $worldservice_template);
	$item = str_replace('{VAR_IP_PORT}', "$ip_inner:$port", $item);
	$environment_list[] = $item;
}

$xml_content = str_replace('{VAR_WORLDSERVICE_BEAN_LIST}', implode("\n", $environment_list), $xml_template);
file_put_contents ( $out_dir . '/rpcClient.xml', $xml_content );
echo_realtime ( "done. " . $out_dir . '/rpcClient.xml' );
