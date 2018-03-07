<?php
$uri = 'http://xingcloud/rest/user/user/platformLogin';
$oauth = array();
$oauth['realm'] = "http://xingcloud";
$oauth['oauth_consumer_key'] = "#consumer_key#";
$oauth['oauth_signature_method'] = "MD5";
$oauth['oauth_timestamp'] = time();
$oauth['oauth_nonce'] = md5(time().rand(0, 1000));
$oauth['oauth_version'] = "1.0";
$posts = array();
$posts['id'] = 1;
$posts['info'] = array('platformAppId'=>'MyApp','platformUserId'=>'1','gameUserId'=>'');
$posts['data'] = array();
$paramString = '';
ksort($oauth);
foreach($oauth as $key => $value){
	$paramString .= $key.'='.$value.'&';
}
$paramString = trim($paramString, '&');
$base = 'POST&'.rawurlencode($uri).'&'.rawurlencode($paramString).'&'.rawurlencode(json_encode($posts));
$signature = base64_encode(md5($base.'&'.rawurlencode("#secret_key#")));
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