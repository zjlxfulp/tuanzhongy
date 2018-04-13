<?php
/**
 * Created by PhpStorm.
 * User: fuliping
 * Date: 2017/12/12
 * Time: 16:04
 */
$signature = isset($_GET['signature'])?$_GET['signature']:'';
$timestamp = isset($_GET['timestamp'])?$_GET['timestamp']:'';
$nonce = isset($_GET['nonce'])?$_GET['nonce']:'';
$echostr = isset($_GET['echostr'])?$_GET['echostr']:'';
$token = 'tuanzhongyang';
$tmpArr = array($timestamp, $nonce,$token);
sort($tmpArr, SORT_STRING);
$tmpStr = implode( $tmpArr );
$tmpStr = sha1( $tmpStr );
if( $tmpStr == $signature ){
    echo $echostr;
    return;
}else{
    echo 111;
    return;
}