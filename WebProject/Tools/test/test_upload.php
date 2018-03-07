<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 15/12/10
 * Time: 11:26
 */

$url = 'http://10.1.16.211:8081/gameservice/photo.php';
//$url = 'http://p1coq.elexapp.com/gameservice/photo.php';

$file = '/downloads/2015120912444550bf5.jpg';
// initialise the curl request
$request = curl_init($url);

$gameuid = '4397745000001';
$seq = 1;
$sig_key = 'Wp2A5zvR9y1t';
$sig = strtoupper(md5($sig_key . 'gameuid' . $gameuid . 'photo_seq' . $seq));

$add_data = array('gameuid' => $gameuid,
    'photo_seq' => $seq,
    '_s' => $sig,);
$post_data = array(
    'file' => '@' . realpath($file),
    'data' => json_encode($add_data),
);
// send a file
curl_setopt($request, CURLOPT_POST, true);
curl_setopt(
    $request,
    CURLOPT_POSTFIELDS,
    $post_data
    );

// output the response
curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($request);

// close the session
curl_close($request);