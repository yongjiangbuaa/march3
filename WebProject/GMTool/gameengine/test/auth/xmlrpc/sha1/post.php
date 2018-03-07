<?php
$uri = 'http://xingcloud/xmlrpc/user/user';
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
<methodCall>
  <methodName>login</methodName>
	<params>
	<param>
	 <value>
	  <struct>
	   <member>
	    <name>platform_uid</name>
	    <value>
	     <string>test</string>
	    </value>
	   </member>
	   <member>
	    <name>platform_user_uid</name>
	    <value>
	     <string>555555</string>
	    </value>
	   </member>
	  </struct>
	 </value>
	 </param>
	</params>
</methodCall>';
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
curl_setopt($curl, CURLOPT_URL, $uri);
curl_setopt($curl, CURLOPT_USERAGENT, "XingCloud OAuth Test");
curl_setopt($curl, CURLOPT_HTTPHEADER, array($authHeader, 'Content-Type: text/xml'));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $posts);
curl_exec($curl);
curl_close($curl);
?>