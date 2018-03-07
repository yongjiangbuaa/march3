<?php
$uri = 'http://xingcloud/rest/user/user/login';
$get = '?format=xml';
$oauth = array();
$oauth['realm'] = "http://xingcloud";
$oauth['oauth_consumer_key'] = "xingcloud.com";
$oauth['oauth_signature_method'] = "HMAC-SHA1";
$oauth['oauth_timestamp'] = time();
$oauth['oauth_nonce'] = md5(time().rand(0, 1000));
$oauth['oauth_version'] = "1.0";
$oauth['format'] = "xml";
$posts = array();
$posts = '<?xml version="1.0" encoding="utf-8" ?>
<xingcloud:request xmlns:xingcloud="http://www.iw.com/sns/request/">
<platform_uid>test</platform_uid>
<platform_user_uid>555555</platform_user_uid>
</xingcloud:request>';
$paramString = '';
ksort($oauth);
foreach($oauth as $key => $value){
	$paramString .= $key.'='.$value.'&';
}
$paramString = trim($paramString, '&');
$base = 'POST&'.rawurlencode($uri).'&'.rawurlencode($paramString).'&'.rawurlencode($posts);
$signature = base64_encode(hash_hmac("sha1", $base, "2acb57e55f4905ed666ed458ef250aea", true));
$oauth['oauth_signature'] = $signature;
ksort($oauth);
$authHeader = array();
foreach($oauth as $key => $value){
	$authHeader[] = $key.'="'.$value.'"';
}
$authHeader = 'Authorization: OAuth '.implode(',', $authHeader);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $uri.$get);
curl_setopt($curl, CURLOPT_USERAGENT, "XingCloud OAuth Test");
curl_setopt($curl, CURLOPT_HTTPHEADER, array($authHeader, 'Content-Type: text/xml'));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $posts);
curl_exec($curl);
curl_close($curl);
?>