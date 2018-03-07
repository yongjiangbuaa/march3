<?php
$uri = 'http://xingcloud/rest/locale/style/get';
$oauth = array();
$oauth['realm'] = "http://xingcloud";
$oauth['oauth_consumer_key'] = "xingcloud.com";
$oauth['oauth_signature_method'] = "HMAC-SHA1";
$oauth['oauth_timestamp'] = time();
$oauth['oauth_nonce'] = md5(time().rand(0, 1000));
$oauth['oauth_version'] = "1.0";
$oauth['lang'] = "en";
$paramString = '';
ksort($oauth);
foreach($oauth as $key => $value){
	$paramString .= $key.'='.$value.'&';
}
$paramString = trim($paramString, '&');
$base = 'POST&'.rawurlencode($uri).'&'.rawurlencode($paramString);
$signature = base64_encode(hash_hmac("sha1", $base, "2acb57e55f4905ed666ed458ef250aea", true));
$oauth['oauth_signature'] = $signature;
ksort($oauth);
unset($oauth['lang']);
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
curl_setopt($curl, CURLOPT_POSTFIELDS, 'lang=en');
curl_exec($curl);
curl_close($curl);
?>