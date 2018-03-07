<?php
$uri = 'http://xingcloud/jsonrpc/user/user';
$oauth = array();
$oauth['realm'] = "http://xingcloud";
$oauth['oauth_consumer_key'] = "xingcloud.com";
$oauth['oauth_signature_method'] = "MD5";
$oauth['oauth_timestamp'] = time();
$oauth['oauth_nonce'] = md5(time().rand(0, 1000));
$oauth['oauth_version'] = "1.0";
$posts = array();
$posts['id'] = 1;
$posts['method'] = 'login';
$posts['params']['platform_uid'] = 'test';
$posts['params']['platform_user_uid'] = '555555';
$paramString = '';
ksort($oauth);
foreach($oauth as $key => $value){
	$paramString .= $key.'='.$value.'&';
}
$paramString = trim($paramString, '&');
$base = 'POST&'.rawurlencode($uri).'&'.rawurlencode($paramString).'&'.rawurlencode(json_encode($posts));
$signature = base64_encode(md5($base.'&'.rawurlencode("2acb57e55f4905ed666ed458ef250aea")));
$oauth['oauth_signature'] = $signature;
ksort($oauth);
$authHeader = array();
foreach($oauth as $key => $value){
	$authHeader[] = $key.'="'.$value.'"';
}
$authHeader = 'Authorization: OAuth '.implode(',', $authHeader);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $uri);
curl_setopt($curl, CURLOPT_USERAGENT, "XingCloud OAuth Test");
curl_setopt($curl, CURLOPT_HTTPHEADER, array($authHeader));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($posts));
curl_exec($curl);
curl_close($curl);
?>