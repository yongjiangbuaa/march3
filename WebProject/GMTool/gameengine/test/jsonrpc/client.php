<?php
$data = json_decode(file_get_contents('php://input'),true);
$currentId = 1;
$request = array(
	'method' => $data['method'],
	'params' => $data['params'],
	'id' => $currentId,
);
$request = json_encode($request);
// performs the HTTP POST
$opts = array (
	'http' => array (
		'method'  => 'POST',
		'header'  => 'Content-type: application/json',
		'content' => $request
));
$context  = stream_context_create($opts);
if ($fp = fopen('http://'.$_SERVER['SERVER_NAME'].'/jsonrpc/'.$_GET['api'], 'r', false, $context)) {
	$response = '';
	while($row = fgets($fp)) {
		$response.= trim($row)."\n";
	}
	$response = json_decode($response,true);
} else {
	throw new Exception('Unable to connect to '.'http://'.$_SERVER['SERVER_NAME'].'/jsonrpc/'.$_GET['api']);
}
if ($response['id'] != $currentId) {
	throw new Exception('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')');
}
if (!is_null($response['error'])) {
	throw new Exception('Request error: '.$response['error']);
}
echo json_encode($response);
?>