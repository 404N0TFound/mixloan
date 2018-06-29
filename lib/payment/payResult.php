<?php
define('IN_MOBILE', true);
require_once( '/www/wwwroot/addons/xuan_mixloan/lib/payment/alipaySdk/aop/AopClient.php');
$aop = new AopClient;
$aop->alipayrsaPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
$flag = $aop->rsaCheckV1($_POST, NULL, "RSA");
$con = mysqli_connect("127.0.0.1","sql450439","DCS48SD2Tk","sql450439");
if($flag){
    $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=1 WHERE notify_id='{$_POST['out_trade_no']}'";
    mysqli_query($con, $sql);
    mysqli_close($con);
    $url = "http://jrbd.tejiazu.com/app/index.php?i=191&c=entry&op=alipay_notify&do=vip&m=xuan_mixloan&notify_id={$_POST['out_trade_no']}";
    header("location:{$url}");
    echo $url;
} else {
    $sql = "UPDATE `ims_xuan_mixloan_paylog` SET is_pay=-1 WHERE notify_id='{$_POST['out_trade_no']}'";
    mysqli_query($con, $sql);
    mysqli_close($con);
    echo 'fail';
}